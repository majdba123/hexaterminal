import { test, expect } from "@playwright/test";

/**
 * Auth contract for the on-demand revalidation endpoint. The built app under
 * test runs with REVALIDATE_SECRET set (see playwright.config.ts). We prove
 * the endpoint refuses unauthenticated callers and accepts the correct secret;
 * we do NOT assert on cache side effects (that needs a content round-trip).
 */
const SECRET = process.env.REVALIDATE_SECRET ?? "e2e-revalidate-secret";

test.describe("On-demand revalidation endpoint", () => {
  test("rejects requests with no secret (401)", async ({ request }) => {
    const res = await request.post("/api/revalidate", {
      data: { resource: "systems", slug: "demo" },
    });
    expect(res.status()).toBe(401);
  });

  test("rejects requests with a wrong secret (401)", async ({ request }) => {
    const res = await request.post("/api/revalidate", {
      headers: { "x-revalidate-secret": "not-the-secret" },
      data: { resource: "systems", slug: "demo" },
    });
    expect(res.status()).toBe(401);
  });

  test("rejects an unknown resource with the correct secret (400)", async ({ request }) => {
    const res = await request.post("/api/revalidate", {
      headers: { "x-revalidate-secret": SECRET },
      data: { resource: "not-a-real-resource" },
    });
    expect(res.status()).toBe(400);
  });

  test("accepts a valid, authenticated revalidation (200)", async ({ request }) => {
    const res = await request.post("/api/revalidate", {
      headers: { "x-revalidate-secret": SECRET },
      data: { resource: "systems", slug: "demo-ledger-platform", ts: Math.floor(Date.now() / 1000) },
    });
    expect(res.status()).toBe(200);
    const body = await res.json();
    expect(body.revalidated).toBe(true);
    expect(Array.isArray(body.paths)).toBe(true);
    expect(body.paths).toContain("/en/systems/demo-ledger-platform");
  });

  test("revalidates both homepage locales for a Case Study", async ({ request }) => {
    const res = await request.post("/api/revalidate", {
      headers: { "x-revalidate-secret": SECRET },
      data: {
        resource: "case-studies",
        slug: "vetora-specialized-marketplace-operations",
        ts: Math.floor(Date.now() / 1000),
      },
    });

    expect(res.status()).toBe(200);
    const body = await res.json();
    expect(body.paths).toEqual(expect.arrayContaining([
      "/en",
      "/ar",
      "/en/case-studies",
      "/ar/case-studies",
      "/en/case-studies/vetora-specialized-marketplace-operations",
      "/ar/case-studies/vetora-specialized-marketplace-operations",
      "/sitemap.xml",
    ]));
  });
});
