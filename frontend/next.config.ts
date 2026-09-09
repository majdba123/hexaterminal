import type { NextConfig } from "next";
import path from "node:path";
import createNextIntlPlugin from "next-intl/plugin";
import { routing } from "./i18n/routing";
import { REMOTE_IMAGE_HOSTS } from "./lib/image-hosts";
import { ROUTES } from "./lib/routes/registry";
import { SITE_URL } from "./lib/seo/site";

const withNextIntl = createNextIntlPlugin("./i18n/request.ts");

const API_URL = process.env.API_URL ?? "http://localhost:8000/api/v1/public";
const ALLOW_INDEXING = process.env.NEXT_PUBLIC_ALLOW_INDEXING === "true";

type LegacyRedirect = {
  source: string;
  destination: string;
  permanent: boolean;
};

/**
 * Recovery redirects for URLs already observed in search indexes. They are
 * duplicated into the Redirect table by a corrective data migration, but stay
 * here as build-safe fallbacks so an API/cache/deploy ordering issue cannot
 * temporarily bring an indexed legacy URL back as a 404.
 */
const SEO_RECOVERY_REDIRECTS: readonly LegacyRedirect[] = [
  {
    source: "/en/services/ttoyr-ttbykat-aloyb",
    destination: "/en/services/web-platforms-mobile-applications",
    permanent: true,
  },
];

/**
 * Dynamic public families whose canonical URLs always carry the default-locale
 * prefix when a visitor/crawler arrives without one. Static paths come from the
 * central route registry below; these are the content-driven detail routes that
 * cannot be represented there individually.
 */
const LOCALELESS_DYNAMIC_ROUTES = [
  "/services/:slug",
  "/systems/:slug",
  "/case-studies/:slug",
  "/industries/:slug",
  "/insights/:slug",
  "/about/team/:slug",
] as const;

function defaultLocaleRedirects(): LegacyRedirect[] {
  const exact = ROUTES
    .filter((route) => route.path !== "")
    .map((route) => ({
      source: route.path,
      destination: `${SITE_URL}/${routing.defaultLocale}${route.path}`,
      permanent: true,
    }));

  const dynamic = LOCALELESS_DYNAMIC_ROUTES.map((source) => ({
    source,
    destination: `${SITE_URL}/${routing.defaultLocale}${source.replace(":slug", ":slug")}`,
    permanent: true,
  }));

  return [...exact, ...dynamic];
}

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
 * docs/migration/legacy-redirect-map.md. Runs once at build/dev-server start,
 * not per-request. Known indexed recovery mappings remain available even if
 * the API is unreachable or temporarily stale; every other redirect still
 * fails open so a redirect API problem can never block a deploy.
 */
async function legacyRedirects(): Promise<LegacyRedirect[]> {
  try {
    const res = await fetch(`${API_URL}/redirects?locale=en`);
    if (!res.ok) throw new Error(`redirects fetch failed: ${res.status}`);
    const { data } = (await res.json()) as {
      data: { from_path: string; to_path: string; status_code: number }[];
    };

    const apiRedirects: LegacyRedirect[] = data.map((r) => ({
      source: r.from_path,
      destination: r.to_path,
      permanent: r.status_code === 301,
    }));
    const apiSources = new Set(apiRedirects.map((redirect) => redirect.source));

    return [
      ...apiRedirects,
      ...SEO_RECOVERY_REDIRECTS.filter((redirect) => !apiSources.has(redirect.source)),
    ];
  } catch (error) {
    console.warn(
      `[next.config] Redirect API unavailable -- keeping only indexed recovery redirects; ${API_URL}/redirects:`,
      error instanceof Error ? error.message : error,
    );
    return [...SEO_RECOVERY_REDIRECTS];
  }
}

const nextConfig: NextConfig = {
  redirects: async () => [
    // `/` must not remain a third, locale-less indexable entry point. The
    // default-locale URL is also our x-default hreflang target, so make the
    // relationship explicit with a permanent redirect instead of relying on
    // locale negotiation that can vary by crawler headers.
    {
      source: "/",
      destination: `${SITE_URL}/${routing.defaultLocale}`,
      permanent: true,
    },
    // Stable public routes without a locale must converge on the same default
    // locale that canonical/hreflang/x-default advertise. These explicit
    // patterns intentionally avoid legacy singular routes such as /service/:id
    // and /project/:id, which keep their record-specific DB mappings.
    ...defaultLocaleRedirects(),
    // Canonicals, hreflang, sitemap and Open Graph all advertise the www host.
    // This rule comes after default-locale redirects so a bare-host `/services`
    // can go straight to `www/.../en/services` in one hop.
    {
      source: "/:path*",
      has: [{ type: "host", value: "hexaterminal.com" }],
      destination: `${SITE_URL}/:path*`,
      permanent: true,
    },
    ...(await legacyRedirects()),
  ],
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
