import "server-only";

import { NextResponse, type NextRequest } from "next/server";

/**
 * Server-only shared secret that lets Laravel trust the visitor IP we forward
 * (App\Services\PublicClientIp). Falls back to the revalidation secret, which
 * the production deploy already provisions identically on both sides.
 */
const PROXY_SECRET = process.env.PUBLIC_PROXY_SECRET || process.env.REVALIDATE_SECRET || "";

const UPSTREAM_TIMEOUT_MS = 8000;

/**
 * Whether to trust `CF-Connecting-IP` as the visitor IP.
 *
 * OFF by default, and that default is load-bearing. The header is only
 * trustworthy for traffic that actually passed through Cloudflare, because
 * Cloudflare overwrites any client-supplied value on every request. On a
 * deployment whose origin is reachable without going through Cloudflare —
 * which includes the checked-in staging stack, where
 * `deploy/staging/nginx/frontend-staging.conf` publishes 443 directly and does
 * not strip the header — trusting it unconditionally would let a caller rotate
 * it and pick its own `throttle:public-leads` bucket, defeating the
 * per-visitor write limits.
 *
 * Set `TRUST_CLOUDFLARE_CLIENT_IP=true` only once the origin refuses traffic
 * that did not come from Cloudflare (firewall or Nginx `allow` on Cloudflare's
 * published ranges). See docs/deployment/production-deployment.md §Cloudflare.
 */
const TRUST_CLOUDFLARE_CLIENT_IP = process.env.TRUST_CLOUDFLARE_CLIENT_IP === "true";

/**
 * The real visitor IP, used to key the per-visitor throttle in Laravel
 * (App\Services\PublicClientIp).
 *
 * 1. `CF-Connecting-IP` — only when TRUST_CLOUDFLARE_CLIENT_IP is set, see
 *    above. It has to win over the two below wherever Cloudflare is in front:
 *    there `X-Real-IP` and the right-most `X-Forwarded-For` hop are both a
 *    Cloudflare edge address, so every visitor would collapse into one bucket.
 * 2. `X-Real-IP` — our own Nginx sets this from $remote_addr. Connection
 *    derived, so this is the safe default.
 * 3. Right-most `X-Forwarded-For` hop — appended by Nginx; left-most entries
 *    are client-supplied and must never be trusted.
 */
function visitorIp(request: NextRequest): string | null {
  if (TRUST_CLOUDFLARE_CLIENT_IP) {
    const cfConnectingIp = request.headers.get("cf-connecting-ip")?.trim();
    if (cfConnectingIp) return cfConnectingIp;
  }

  const realIp = request.headers.get("x-real-ip")?.trim();
  if (realIp) return realIp;

  const hops = request.headers.get("x-forwarded-for")?.split(",").map((hop) => hop.trim()).filter(Boolean);
  return hops?.at(-1) ?? null;
}

/** Headers for a server-to-server call to the Laravel public API. */
export function upstreamHeaders(request?: NextRequest, extra?: Record<string, string>): Record<string, string> {
  const headers: Record<string, string> = { "Content-Type": "application/json", ...extra };
  if (!PROXY_SECRET) return headers;

  headers["X-Hexa-Proxy-Secret"] = PROXY_SECRET;
  const ip = request ? visitorIp(request) : null;
  if (ip) headers["X-Hexa-Client-Ip"] = ip;
  return headers;
}

/** POST to Laravel with a timeout; never throws. */
export async function postUpstream(url: string, request: NextRequest, body: unknown, extraHeaders?: Record<string, string>) {
  try {
    return await fetch(url, {
      method: "POST",
      // Do not pass browser request context to Laravel. Sanctum must see this
      // public proxy request as stateless.
      headers: upstreamHeaders(request, extraHeaders),
      body: JSON.stringify(body),
      signal: AbortSignal.timeout(UPSTREAM_TIMEOUT_MS),
    });
  } catch {
    return null;
  }
}

/**
 * Relays an upstream response as JSON. A missing response or a non-JSON body
 * (e.g. an Nginx 502/429 HTML page) becomes a clean JSON error instead of an
 * unhandled 500.
 */
export async function relayJson(upstream: Response | null): Promise<NextResponse> {
  if (!upstream) {
    return NextResponse.json({ status: "error", message: "Service unavailable." }, { status: 502 });
  }

  try {
    return NextResponse.json(await upstream.json(), { status: upstream.status });
  } catch {
    const status = upstream.status >= 400 ? upstream.status : 502;
    return NextResponse.json({ status: "error", message: "Unexpected upstream response." }, { status });
  }
}

/** Parses a JSON request body; null when malformed. */
export async function readJson(request: NextRequest): Promise<unknown | null> {
  try {
    return await request.json();
  } catch {
    return null;
  }
}

export function badRequest() {
  return NextResponse.json({ status: "error", message: "Invalid request body." }, { status: 400 });
}
