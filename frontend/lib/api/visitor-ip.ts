/**
 * Visitor-IP resolution for the public API proxy.
 *
 * Lives apart from `upstream.ts` so it can be unit tested: `upstream.ts`
 * imports `server-only`, which throws outside a Next server context. This
 * module deliberately has no imports at all.
 */

/**
 * Whether `CF-Connecting-IP` may be trusted as the visitor IP.
 *
 * OFF by default, and that default is load-bearing. The header is only
 * trustworthy for traffic that actually passed through Cloudflare, because
 * Cloudflare overwrites any client-supplied value on every request. On a
 * deployment whose origin is reachable without going through Cloudflare —
 * which includes the checked-in staging stack, where
 * `deploy/staging/nginx/frontend-staging.conf` publishes 443 directly —
 * trusting it unconditionally would let a caller rotate the header and pick
 * its own `throttle:public-leads` bucket, defeating the per-visitor write
 * limits.
 *
 * Set `TRUST_CLOUDFLARE_CLIENT_IP=true` only once the origin refuses traffic
 * that did not come from Cloudflare (firewall or Nginx `allow` on Cloudflare's
 * published ranges). See docs/deployment/production-deployment.md §Cloudflare.
 */
export function trustsCloudflareClientIp(): boolean {
  return process.env.TRUST_CLOUDFLARE_CLIENT_IP === "true";
}

/**
 * The real visitor IP, used to key the per-visitor throttle in Laravel
 * (App\Services\PublicClientIp).
 *
 * 1. `CF-Connecting-IP` — only when trust is enabled, see above. It has to win
 *    over the two below wherever Cloudflare is in front: there `X-Real-IP` and
 *    the right-most `X-Forwarded-For` hop are both a Cloudflare edge address,
 *    so every visitor would collapse into one bucket.
 * 2. `X-Real-IP` — our own Nginx sets this from $remote_addr. Connection
 *    derived, so this is the safe default.
 * 3. Right-most `X-Forwarded-For` hop — appended by Nginx; left-most entries
 *    are client-supplied and must never be trusted.
 *
 * `trustCloudflare` is injectable so both branches are testable without
 * mutating the environment.
 */
export function resolveVisitorIp(
  headers: Pick<Headers, "get">,
  { trustCloudflare = trustsCloudflareClientIp() }: { trustCloudflare?: boolean } = {},
): string | null {
  if (trustCloudflare) {
    const cfConnectingIp = headers.get("cf-connecting-ip")?.trim();
    if (cfConnectingIp) return cfConnectingIp;
  }

  const realIp = headers.get("x-real-ip")?.trim();
  if (realIp) return realIp;

  const hops = headers
    .get("x-forwarded-for")
    ?.split(",")
    .map((hop) => hop.trim())
    .filter(Boolean);

  return hops?.at(-1) ?? null;
}
