import "server-only";

import { NextResponse, type NextRequest } from "next/server";

import { resolveVisitorIp } from "./visitor-ip";

/**
 * Server-only shared secret that lets Laravel trust the visitor IP we forward
 * (App\Services\PublicClientIp). Falls back to the revalidation secret, which
 * the production deploy already provisions identically on both sides.
 */
const PROXY_SECRET = process.env.PUBLIC_PROXY_SECRET || process.env.REVALIDATE_SECRET || "";

const UPSTREAM_TIMEOUT_MS = 8000;

/** Headers for a server-to-server call to the Laravel public API. */
export function upstreamHeaders(request?: NextRequest, extra?: Record<string, string>): Record<string, string> {
  const headers: Record<string, string> = { "Content-Type": "application/json", ...extra };
  if (!PROXY_SECRET) return headers;

  headers["X-Hexa-Proxy-Secret"] = PROXY_SECRET;
  const ip = request ? resolveVisitorIp(request.headers) : null;
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
