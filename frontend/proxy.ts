import type { NextRequest } from "next/server";
import createMiddleware from "next-intl/middleware";
import { routing } from "./i18n/routing";
import { buildCspHeader } from "./lib/csp";

const intlMiddleware = createMiddleware(routing);

/**
 * CSP is applied here (not next.config.ts's `headers()`) specifically so
 * CSP_ENFORCE is a per-request, runtime decision -- process.env is read
 * fresh on every request against the running server process, so an
 * operator can flip Report-Only -> enforced by changing the env var and
 * restarting the process, with NO rebuild required. See lib/csp.ts for the
 * hash-based (not nonce-based) policy this builds.
 */
export default function proxy(request: NextRequest) {
  const response = intlMiddleware(request);

  const { key, value } = buildCspHeader({
    enforce: process.env.CSP_ENFORCE === "true",
    analyticsSrc: process.env.NEXT_PUBLIC_ANALYTICS_SRC,
    reportUri: "/api/csp-report",
    isDev: process.env.NODE_ENV === "development",
  });
  response.headers.set(key, value);

  return response;
}

export const config = {
  // Exclude API, Next internals, files with an extension (robots.txt,
  // sitemap.xml, media), AND the extension-less metadata routes
  // (icon/apple-icon/opengraph-image) so next-intl doesn't locale-redirect
  // them into 404s. These excluded paths do NOT get the CSP header (JSON
  // API responses and static assets don't execute scripts/styles, so a
  // page-oriented CSP doesn't apply); they still get the baseline
  // X-Content-Type-Options/Referrer-Policy/etc. headers from
  // next.config.ts's securityHeaders(), which matches every path.
  matcher: ["/((?!api|_next|_vercel|icon|apple-icon|opengraph-image|.*\\..*).*)"],
};
