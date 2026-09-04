import type { NextRequest } from "next/server";
import createMiddleware from "next-intl/middleware";
import { routing } from "./i18n/routing";
import { buildCspHeader } from "./lib/csp";

const intlMiddleware = createMiddleware(routing);

/**
 * CSP is applied here (not next.config.ts's `headers()`) so CSP rollout is a
 * per-request runtime decision and never requires rebuilding the frontend.
 *
 * Next.js App Router streams response-specific inline RSC bootstrap scripts.
 * The current static hash policy cannot safely allow those scripts, while
 * `unsafe-inline` would defeat the XSS protection we want from CSP. Therefore:
 *
 * - normal production traffic emits no page CSP header;
 * - `CSP_REPORT_ONLY=true` explicitly enables audit/reporting mode;
 * - `CSP_ENFORCE=true` is honored only outside production until the app moves
 *   to a request-bound nonce strategy.
 *
 * This keeps production console output clean without weakening the policy or
 * forcing dynamic rendering just to support nonces. See lib/csp.ts for the
 * current hash-based policy and its documented RSC limitation.
 */
export default function proxy(request: NextRequest) {
  const response = intlMiddleware(request);

  const isProduction = process.env.NODE_ENV === "production";
  const enforceCsp = process.env.CSP_ENFORCE === "true" && !isProduction;
  const reportOnlyCsp = process.env.CSP_REPORT_ONLY === "true";

  if (enforceCsp || reportOnlyCsp) {
    const { key, value } = buildCspHeader({
      enforce: enforceCsp,
      analyticsSrc: process.env.NEXT_PUBLIC_ANALYTICS_SRC,
      reportUri: "/api/csp-report",
      isDev: process.env.NODE_ENV === "development",
    });
    response.headers.set(key, value);
  }

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
