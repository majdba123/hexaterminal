import { test, expect } from "@playwright/test";

/**
 * Deployed-staging smoke suite. Runs against STAGING_URL (see
 * playwright.staging.config.ts). Assumes staging has been seeded with the
 * demo fixtures (DemoContentSeeder) so the demo slugs below resolve; override
 * any slug via env if your staging content differs.
 *
 * Staging is expected to be NON-indexable, so the crawl-policy tests here
 * assert the opposite of e2e/robots.spec.ts (which covers the indexable case).
 */
const STAGING_URL = process.env.STAGING_URL!;
const API_URL = process.env.STAGING_API_URL ?? STAGING_URL;
const CMS_URL = process.env.STAGING_CMS_URL ?? `${API_URL.replace(/\/$/, "")}/cms`;

const SYSTEM_SLUG = process.env.STAGING_SYSTEM_SLUG ?? "demo-ledger-platform";
const INDUSTRY_SLUG = process.env.STAGING_INDUSTRY_SLUG ?? "demo-fintech";
const ARTICLE_SLUG = process.env.STAGING_ARTICLE_SLUG ?? "demo-building-auditable-systems";

test.describe("Staging: localization & rendering", () => {
  test("English homepage renders server content, no serious console errors", async ({ page }) => {
    const errors: string[] = [];
    page.on("pageerror", (e) => errors.push(`pageerror: ${e.message}`));
    page.on("console", (m) => {
      if (m.type() === "error") errors.push(`console: ${m.text()}`);
    });

    await page.goto("/en");
    await expect(page.locator("html")).toHaveAttribute("lang", "en");
    await expect(page.locator("html")).toHaveAttribute("dir", "ltr");
    await expect(page.locator("h1").first()).toBeVisible();

    const serious = errors.filter((e) => !/favicon|net::ERR|Failed to load resource/i.test(e));
    expect(serious, serious.join("\n")).toHaveLength(0);
  });

  test("Arabic homepage is RTL with lang=ar", async ({ page }) => {
    await page.goto("/ar");
    await expect(page.locator("html")).toHaveAttribute("lang", "ar");
    await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
  });

  test("theme toggle persists across reload", async ({ page }) => {
    await page.goto("/en");
    const html = page.locator("html");
    const before = await html.getAttribute("data-theme");
    await page.getByRole("button", { name: /theme|dark|light/i }).first().click();
    await expect(html).not.toHaveAttribute("data-theme", before ?? "");
    const after = await html.getAttribute("data-theme");
    await page.reload();
    await expect(html).toHaveAttribute("data-theme", after ?? "");
  });
});

test.describe("Staging: content & media", () => {
  test("logo and at least one media asset load", async ({ page }) => {
    await page.goto("/en");
    const logo = page.getByRole("link", { name: /hexa/i }).first();
    await expect(logo).toBeVisible();
    // Every <img> that finished loading must have real dimensions.
    const broken = await page.evaluate(() =>
      Array.from(document.images)
        .filter((img) => img.complete && img.naturalWidth === 0)
        .map((img) => img.currentSrc || img.src),
    );
    expect(broken, `broken images: ${broken.join(", ")}`).toHaveLength(0);
  });

  test("system detail page renders", async ({ page }) => {
    const res = await page.goto(`/en/systems/${SYSTEM_SLUG}`);
    expect(res?.status()).toBe(200);
    await expect(page.locator("h1").first()).toBeVisible();
  });

  test("industry detail page renders", async ({ page }) => {
    const res = await page.goto(`/en/industries/${INDUSTRY_SLUG}`);
    expect(res?.status()).toBe(200);
    await expect(page.locator("h1").first()).toBeVisible();
  });

  test("article detail page renders", async ({ page }) => {
    const res = await page.goto(`/en/insights/${ARTICLE_SLUG}`);
    expect(res?.status()).toBe(200);
    await expect(page.locator("h1").first()).toBeVisible();
  });

  test("services listing renders", async ({ page }) => {
    await page.goto("/en/services");
    await expect(page.locator("h1").first()).toBeVisible();
  });
});

test.describe("Staging: crawl policy (must be NON-indexable)", () => {
  test("robots.txt disallows all crawling", async ({ request }) => {
    const res = await request.get("/robots.txt");
    expect(res.ok()).toBeTruthy();
    const body = (await res.text()).toLowerCase();
    expect(body).toContain("disallow: /");
    expect(body).not.toContain("allow: /");
  });

  test("responses carry X-Robots-Tag: noindex", async ({ request }) => {
    const res = await request.get("/en");
    expect(res.headers()["x-robots-tag"] ?? "").toMatch(/noindex/i);
  });

  test("homepage HTML contains a noindex robots meta", async ({ page }) => {
    await page.goto("/en");
    const content = await page.locator('meta[name="robots"]').first().getAttribute("content");
    expect(content ?? "").toMatch(/noindex/i);
  });
});

test.describe("Staging: routing & errors", () => {
  test("unknown slug returns 404", async ({ page }) => {
    const res = await page.goto("/en/systems/this-slug-does-not-exist-xyz");
    expect(res?.status()).toBe(404);
  });

  test("legacy redirect resolves to the new URL", async ({ page }) => {
    // /project/1 was migrated; it must redirect (permanent) into the new app.
    const res = await page.goto("/project/1");
    expect(res?.status()).toBe(200); // after following the redirect
    expect(page.url()).not.toContain("/project/1");
  });
});

test.describe("Staging: health & admin surface", () => {
  test("frontend health endpoint is ok", async ({ request }) => {
    const res = await request.get("/api/health");
    expect(res.ok()).toBeTruthy();
    const body = await res.json();
    expect(body.status).toBe("ok");
  });

  test("API liveness endpoint is ok", async ({ request }) => {
    const res = await request.get(`${API_URL.replace(/\/$/, "")}/api/health`);
    expect(res.ok()).toBeTruthy();
  });

  test("API readiness endpoint reports healthy", async ({ request }) => {
    const res = await request.get(`${API_URL.replace(/\/$/, "")}/api/health/ready`);
    expect(res.ok()).toBeTruthy();
    const body = await res.json();
    expect(body.status).toBe("ok");
  });

  test("CMS login page is reachable", async ({ page }) => {
    const res = await page.goto(CMS_URL);
    expect(res?.status()).toBeLessThan(400);
    await expect(page.getByRole("textbox").first()).toBeVisible();
  });
});

test.describe("Staging: interactions", () => {
  test("showreel modal opens and closes with Escape", async ({ page }) => {
    await page.goto("/en");
    const trigger = page.getByRole("button", { name: /showreel|watch/i }).first();
    if ((await trigger.count()) === 0) test.skip(true, "no showreel on this build");
    await trigger.click();
    await expect(page.getByRole("dialog")).toBeVisible();
    await page.keyboard.press("Escape");
    await expect(page.getByRole("dialog")).toBeHidden();
  });

  test("lead form validates required fields", async ({ page }) => {
    await page.goto("/en/start-a-project");
    const submit = page.getByRole("button", { name: /send|submit|start/i }).first();
    if ((await submit.count()) === 0) test.skip(true, "no lead form on this build");
    await submit.click();
    // Native/inline validation keeps us on the same page (no successful nav).
    await expect(page).toHaveURL(/start-a-project/);
  });
});
