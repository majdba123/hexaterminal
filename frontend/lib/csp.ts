import { createHash } from "node:crypto";
import { imageHostOrigins } from "./image-hosts";
import { THEME_INIT_SCRIPT } from "./theme-init-script";

/**
 * Content Security Policy for the public Next.js origin. Called from
 * proxy.ts (per-request) rather than next.config.ts's `headers()` (baked
 * into the build manifest at `next build` time) so that CSP_ENFORCE is a
 * runtime rollout decision, not a rebuild-required one.
 *
 * script-src is hash-based rather than nonce-based, to preserve static
 * generation: a nonce requires every page to opt into dynamic rendering
 * (see node_modules/next/dist/docs/01-app/02-guides/content-security-policy.md,
 * "Dynamic Rendering Requirement"), which would be a severe regression for
 * this site's Services/Systems/Case Studies/Articles pages. The app's own
 * one inline script (the no-flash theme-init script in
 * app/[locale]/layout.tsx) is hashed here and allowed explicitly.
 *
 * *** KNOWN LIMITATION -- DO NOT SET CSP_ENFORCE=true YET ***
 * Next.js's App Router itself injects multiple inline
 * `(self.__next_f=self.__next_f||[]).push([...])` bootstrap scripts per
 * page to stream RSC payload data to the client. Their content is
 * per-page/per-chunk, so they cannot be covered by a static hash allowlist,
 * and there is no way to distinguish "Next's required scripts" from
 * "an attacker's injected script" under `script-src` without either (a)
 * per-request nonces (see above -- breaks static generation) or (b)
 * `'unsafe-inline'` (defeats CSP's actual XSS protection for injected
 * inline scripts). Verified empirically on 2026-07-22: with
 * `CSP_ENFORCE=true` against a production build, the browser blocks these
 * scripts (`disposition: "enforce"` in the violation report) and the page
 * fails to hydrate. Report-Only mode (the default) is fully safe and
 * useful today -- it never blocks anything, and /api/csp-report gives real
 * violation data to plan the eventual nonce migration. Full enforcement
 * remains an explicit, documented P0 blocker until that migration happens;
 * see docs/architecture/final-remaining-gap-inventory.md.
 *
 * `style-src` keeps a narrow, documented `unsafe-inline`: two components
 * (cost-estimator.tsx progress bar, ui/accordion.tsx open-height animation)
 * use inline `style={{}}` for values that must be computed per-render.
 * There is no hash equivalent for the `style` attribute, and moving both to
 * CSS custom properties is out of this pass's scope -- tracked as a residual
 * risk, not silently dropped.
 */

function themeScriptHash(): string {
  const hash = createHash("sha256").update(THEME_INIT_SCRIPT, "utf8").digest("base64");
  return `'sha256-${hash}'`;
}

/** Origin only (scheme + host [+ port]), or null if the URL is invalid/absent. */
function originOf(url: string | undefined): string | null {
  if (!url) return null;
  try {
    return new URL(url).origin;
  } catch {
    return null;
  }
}

export interface CspOptions {
  /** Enforce (`Content-Security-Policy`) vs report-only (`-Report-Only`). */
  enforce: boolean;
  /** Analytics script origin, if an analytics provider is configured. */
  analyticsSrc?: string;
  /** Where the browser should POST violation reports. */
  reportUri: string;
  isDev: boolean;
}

export function buildCspHeader(options: CspOptions): { key: string; value: string } {
  const analyticsOrigin = originOf(options.analyticsSrc);

  const scriptSrc = ["'self'", themeScriptHash(), analyticsOrigin]
    .filter((v): v is string => Boolean(v))
    .join(" ");

  const connectSrc = ["'self'", analyticsOrigin].filter((v): v is string => Boolean(v)).join(" ");

  const directives = [
    "default-src 'self'",
    // 'unsafe-eval' is required by React's dev-mode error reconstruction
    // only; production never needs it (see Next.js CSP guide).
    `script-src ${scriptSrc}${options.isDev ? " 'unsafe-eval'" : ""}`,
    // See file docblock: two components need inline style={{}} and there is
    // no hash/nonce equivalent for the style attribute.
    "style-src 'self' 'unsafe-inline'",
    `img-src 'self' blob: data: ${imageHostOrigins().join(" ")}`,
    "font-src 'self'",
    `connect-src ${connectSrc}`,
    "media-src 'self'",
    "object-src 'none'",
    "base-uri 'self'",
    "form-action 'self'",
    "frame-ancestors 'none'",
    `report-uri ${options.reportUri}`,
  ];

  // Only meaningful in an ENFORCED policy: browsers ignore
  // `upgrade-insecure-requests` in a report-only policy and log a console
  // warning for it, which is noise on every production page load while the
  // Report-Only rollout is still in progress.
  if (!options.isDev && options.enforce) {
    directives.push("upgrade-insecure-requests");
  }

  return {
    key: options.enforce ? "Content-Security-Policy" : "Content-Security-Policy-Report-Only",
    value: directives.join("; "),
  };
}
