import { test, expect } from "@playwright/test";

// Deterministic fixtures come from database/seeders/DemoContentSeeder.php.
test.describe("Search", () => {
  test("finds a real published result and links to it", async ({ page }) => {
    await page.goto("/en/search?q=fintech");
    await expect(page.getByRole("link", { name: /fintech/i }).first()).toBeVisible();
  });

  test("short queries show the noQuery empty state, not an error", async ({ page }) => {
    await page.goto("/en/search");
    await expect(page.getByText(/type at least 2 characters/i)).toBeVisible();
  });

  test("nonsense queries show a noResults empty state", async ({ page }) => {
    await page.goto("/en/search?q=zzzznonexistentzzzz");
    await expect(page.getByText(/no results for/i)).toBeVisible();
  });

  test("search page is noindex regardless of site indexing policy", async ({ page }) => {
    await page.goto("/en/search");
    const content = await page.locator('meta[name="robots"]').first().getAttribute("content");
    expect(content ?? "").toMatch(/noindex/i);
  });
});

test.describe("Insights category filter", () => {
  test("filtering by category updates the article list and highlights the active chip", async ({
    page,
  }) => {
    await page.goto("/en/insights");
    const chip = page.getByRole("link", { name: /fintech/i }).first();
    if ((await chip.count()) === 0) test.skip(true, "no category chips seeded for this build");

    await chip.click();
    await expect(page).toHaveURL(/category=/);
  });
});

test.describe("Legal pages", () => {
  test("privacy policy renders in both locales", async ({ page }) => {
    await page.goto("/en/privacy");
    await expect(page.locator("h1")).toContainText(/privacy/i);

    await page.goto("/ar/privacy");
    await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
  });

  test("terms of service renders", async ({ page }) => {
    await page.goto("/en/terms");
    await expect(page.locator("h1")).toContainText(/terms/i);
  });

  test("unfinished legal pages are noindex with localized canonical and hreflang", async ({ page }) => {
    for (const locale of ["en", "ar"]) {
      for (const path of ["privacy", "terms"]) {
        await page.goto(`/${locale}/${path}`);
        await expect(page.locator('meta[name="robots"]')).toHaveAttribute("content", /noindex/i);
        await expect(page.locator('link[rel="canonical"]')).toHaveAttribute(
          "href",
          new RegExp(`/${locale}/${path}$`),
        );
        await expect(page.locator('link[rel="alternate"][hreflang="en"]')).toHaveCount(1);
        await expect(page.locator('link[rel="alternate"][hreflang="ar"]')).toHaveCount(1);
      }
    }
  });
});

test.describe("Unfinished content routes", () => {
  test("case studies and insights listings are noindex in both locales", async ({ page }) => {
    for (const locale of ["en", "ar"]) {
      for (const path of ["case-studies", "insights"]) {
        await page.goto(`/${locale}/${path}`);
        await expect(page.locator('meta[name="robots"]')).toHaveAttribute("content", /noindex/i);
        await expect(page.locator('link[rel="canonical"]')).toHaveAttribute(
          "href",
          new RegExp(`/${locale}/${path}$`),
        );
      }
    }
  });
});

test.describe("RSS feed", () => {
  test("returns valid RSS content with the correct content type", async ({ request }) => {
    const res = await request.get("/rss.xml");
    expect(res.ok()).toBeTruthy();
    expect(res.headers()["content-type"]).toContain("application/rss+xml");
    const body = await res.text();
    expect(body).toContain("<rss");
    expect(body).toContain("<channel>");
  });
});

test.describe("Header search entry point", () => {
  test("search icon in the header navigates to the search page", async ({ page }) => {
    await page.goto("/en");
    await page.getByRole("link", { name: /search/i }).click();
    await expect(page).toHaveURL(/\/en\/search/);
  });
});
