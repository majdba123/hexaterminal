import { test, expect } from "@playwright/test";
import { buildCspHeader } from "../lib/csp";
import { THEME_INIT_SCRIPT } from "../lib/theme-init-script";
import { createHash } from "node:crypto";

/**
 * Pure-logic assertions for the CSP builder (lib/csp.ts) -- no browser
 * involved, mirrors e2e/route-registry.spec.ts's style. Verifies the
 * fail-safe defaults (report-only unless explicitly enforced, no
 * unsafe-inline/unsafe-eval for scripts in production) and that the
 * theme-init script hash actually matches the literal script that ships
 * in app/[locale]/layout.tsx.
 */

test.describe("Content Security Policy", () => {
  test("defaults to Report-Only when not explicitly enforced", () => {
    const header = buildCspHeader({ enforce: false, reportUri: "/api/csp-report", isDev: false });
    expect(header.key).toBe("Content-Security-Policy-Report-Only");
  });

  test("switches to enforced only when explicitly requested", () => {
    const header = buildCspHeader({ enforce: true, reportUri: "/api/csp-report", isDev: false });
    expect(header.key).toBe("Content-Security-Policy");
  });

  test("production script-src never contains unsafe-inline or unsafe-eval", () => {
    const header = buildCspHeader({ enforce: true, reportUri: "/api/csp-report", isDev: false });
    const scriptSrc = header.value.split(";").find((d) => d.trim().startsWith("script-src"));
    expect(scriptSrc).toBeDefined();
    expect(scriptSrc).not.toContain("unsafe-inline");
    expect(scriptSrc).not.toContain("unsafe-eval");
  });

  test("dev script-src allows unsafe-eval (React dev-mode error reconstruction) but never unsafe-inline", () => {
    const header = buildCspHeader({ enforce: false, reportUri: "/api/csp-report", isDev: true });
    const scriptSrc = header.value.split(";").find((d) => d.trim().startsWith("script-src"));
    expect(scriptSrc).toContain("unsafe-eval");
    expect(scriptSrc).not.toContain("unsafe-inline");
  });

  test("script-src hash matches the actual theme-init script content", () => {
    const header = buildCspHeader({ enforce: true, reportUri: "/api/csp-report", isDev: false });
    const expectedHash = `'sha256-${createHash("sha256").update(THEME_INIT_SCRIPT, "utf8").digest("base64")}'`;
    expect(header.value).toContain(expectedHash);
  });

  test("includes the analytics origin in script-src and connect-src only when configured", () => {
    const withAnalytics = buildCspHeader({
      enforce: true,
      analyticsSrc: "https://plausible.example.com/js/script.js",
      reportUri: "/api/csp-report",
      isDev: false,
    });
    expect(withAnalytics.value).toContain("https://plausible.example.com");

    const withoutAnalytics = buildCspHeader({ enforce: true, reportUri: "/api/csp-report", isDev: false });
    expect(withoutAnalytics.value).not.toContain("plausible");
  });

  test("always sets object-src none, frame-ancestors none, and a report-uri", () => {
    const header = buildCspHeader({ enforce: true, reportUri: "/api/csp-report", isDev: false });
    expect(header.value).toContain("object-src 'none'");
    expect(header.value).toContain("frame-ancestors 'none'");
    expect(header.value).toContain("report-uri /api/csp-report");
  });

  test("upgrade-insecure-requests only in production", () => {
    const prod = buildCspHeader({ enforce: true, reportUri: "/api/csp-report", isDev: false });
    const dev = buildCspHeader({ enforce: true, reportUri: "/api/csp-report", isDev: true });
    expect(prod.value).toContain("upgrade-insecure-requests");
    expect(dev.value).not.toContain("upgrade-insecure-requests");
  });

  /**
   * Browsers IGNORE `upgrade-insecure-requests` in a report-only policy and
   * log a console warning for it. Since Report-Only is the default, emitting
   * it there produced a console error on every production page load (it failed
   * e2e/home.spec.ts's "no serious console errors" assertion).
   */
  test("upgrade-insecure-requests is omitted from a report-only policy", () => {
    const reportOnly = buildCspHeader({
      enforce: false,
      reportUri: "/api/csp-report",
      isDev: false,
    });
    expect(reportOnly.key).toBe("Content-Security-Policy-Report-Only");
    expect(reportOnly.value).not.toContain("upgrade-insecure-requests");
  });

  /**
   * `remotePatterns`/`img-src` is an SSRF boundary: `/_next/image?url=...`
   * makes the SERVER fetch anything the allowlist permits, and Next treats an
   * omitted `port`/`pathname` as "any". An `http://localhost` entry therefore
   * exposes every internal service on the Next host, so it must be
   * development-only. See lib/image-hosts.ts.
   */
  test("production img-src never allows localhost", () => {
    for (const enforce of [false, true]) {
      const header = buildCspHeader({ enforce, reportUri: "/api/csp-report", isDev: false });
      expect(header.value).not.toContain("localhost");
    }
  });
});
