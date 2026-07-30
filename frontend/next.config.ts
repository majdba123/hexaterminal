import type { NextConfig } from "next";
import path from "node:path";
import createNextIntlPlugin from "next-intl/plugin";
import { REMOTE_IMAGE_HOSTS } from "./lib/image-hosts";

const withNextIntl = createNextIntlPlugin("./i18n/request.ts");

const API_URL = process.env.API_URL ?? "http://localhost:8000/api/v1/public";
const ALLOW_INDEXING = process.env.NEXT_PUBLIC_ALLOW_INDEXING === "true";

/**
 * Baseline response headers safe for Next.js, streamed HTML, and remote
 * images/video, PLUS the indexing kill-switch (defence in depth alongside
 * robots.ts and page metadata): when this deployment is not indexable,
 * every response also carries `X-Robots-Tag: noindex, nofollow`. Because it
 * defaults to the non-indexable branch, a staging deploy that forgets the
 * flag is still protected at the header level even for non-HTML responses.
 *
 * Content-Security-Policy is deliberately NOT set here -- `headers()` runs
 * once at `next build` time and is baked into the build manifest, so a
 * CSP_ENFORCE flag read here could only ever be toggled by a full rebuild,
 * defeating the point of a "flip it after observing Report-Only" rollout.
 * CSP is applied per-request in proxy.ts instead, where env vars are read
 * fresh on every request against the running (not just the built) process.
 * HSTS is intentionally not set anywhere in this app: it is only safe once
 * the deployment is confirmed to always serve HTTPS, which is an
 * infrastructure/DNS decision outside this repo -- see
 * docs/deployment/staging-deployment.md. Applying it prematurely could lock
 * out an HTTP-only preview environment for the HSTS max-age duration.
 */
async function securityHeaders() {
  const baseline = [
    { key: "X-Content-Type-Options", value: "nosniff" },
    { key: "Referrer-Policy", value: "strict-origin-when-cross-origin" },
    { key: "X-Frame-Options", value: "SAMEORIGIN" },
    {
      key: "Permissions-Policy",
      value: "camera=(), microphone=(), geolocation=(), browsing-topics=()",
    },
  ];

  const headers = ALLOW_INDEXING
    ? baseline
    : [...baseline, { key: "X-Robots-Tag", value: "noindex, nofollow" }];

  return [{ source: "/:path*", headers }];
}

/**
 * Real legacy -> new URL map, sourced from the Redirect table (populated by
 * `php artisan hexa:migrate-legacy-content` from routes/web.php's old
 * /project/{id}, /service/{id}, /team/{id}, /projects routes). See
 * docs/migration/legacy-to-nextjs.md. Runs once at build/dev-server start,
 * not per-request. Fails safe (no redirects, build still proceeds) if the
 * API is unreachable -- this must never block a deploy.
 */
async function legacyRedirects() {
  try {
    const res = await fetch(`${API_URL}/redirects?locale=en`);
    if (!res.ok) throw new Error(`redirects fetch failed: ${res.status}`);
    const { data } = (await res.json()) as {
      data: { from_path: string; to_path: string; status_code: number }[];
    };
    return data.map((r) => ({
      source: r.from_path,
      destination: r.to_path,
      permanent: r.status_code === 301,
    }));
  } catch (error) {
    console.warn(
      `[next.config] Skipping legacy redirects -- could not reach ${API_URL}/redirects:`,
      error instanceof Error ? error.message : error,
    );
    return [];
  }
}

const nextConfig: NextConfig = {
  redirects: legacyRedirects,
  headers: securityHeaders,
  // The frontend is nested in a Laravel repo that also has a lockfile at the
  // root; pin the tracing root to this app so build output/file tracing and
  // deployment bundling resolve correctly.
  outputFileTracingRoot: path.join(__dirname),
  images: {
    // Shared with lib/csp.ts's img-src so next/image and CSP never drift
    // apart -- see lib/image-hosts.ts.
    remotePatterns: [...REMOTE_IMAGE_HOSTS],
  },
};

export default withNextIntl(nextConfig);
