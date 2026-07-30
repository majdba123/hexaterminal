import { test, expect } from "@playwright/test";

/**
 * Crawl-policy proof for the INDEXABLE branch. The default e2e/CI run boots
 * the built app with NEXT_PUBLIC_ALLOW_INDEXING=true (see playwright.config.ts
 * webServer env), so this asserts the production-style policy. The opposite
 * branch -- a non-indexable staging deploy defaulting to noindex -- is proven
 * against a deployed URL by e2e-staging/staging.spec.ts.
 */
test.describe("Crawl policy (indexing enabled)", () => {
  test("robots.txt allows crawling and advertises the sitemap", async ({ request }) => {
    const res = await request.get("/robots.txt");
    expect(res.ok()).toBeTruthy();
    const body = await res.text();
    expect(body).toMatch(/Allow: \//);
    expect(body).toMatch(/Sitemap:/i);
    // Must NOT be the fail-safe disallow-all page.
    expect(body).not.toMatch(/User-Agent: \*\s*\nDisallow: \/\s*$/i);
  });

  test("HTML responses do not carry a noindex X-Robots-Tag", async ({ request }) => {
    const res = await request.get("/en");
    expect(res.ok()).toBeTruthy();
    expect(res.headers()["x-robots-tag"] ?? "").not.toMatch(/noindex/i);
  });

  test("baseline security headers are present", async ({ request }) => {
    const res = await request.get("/en");
    const headers = res.headers();
    expect(headers["x-content-type-options"]).toBe("nosniff");
    expect(headers["referrer-policy"]).toBe("strict-origin-when-cross-origin");
  });
});
