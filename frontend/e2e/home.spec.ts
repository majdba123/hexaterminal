import { test, expect } from "@playwright/test";

test.describe("Homepage", () => {
  test("English homepage renders with server content and no serious console errors", async ({
    page,
  }) => {
    const errors: string[] = [];
    page.on("pageerror", (e) => errors.push(`pageerror: ${e.message}`));
    page.on("console", (msg) => {
      if (msg.type() === "error") errors.push(`console: ${msg.text()}`);
    });

    await page.goto("/en");

    await expect(page.locator("html")).toHaveAttribute("lang", "en");
    await expect(page.locator("html")).toHaveAttribute("dir", "ltr");
    // Hero heading is server-rendered.
    await expect(page.locator("h1").first()).toBeVisible();
    await expect(page.getByRole("navigation", { name: "Main" })).toBeVisible();

    // Ignore benign resource-load noise (e.g. favicon/media); fail on real errors.
    const serious = errors.filter((e) => !/favicon|net::ERR|Failed to load resource/i.test(e));
    expect(serious, serious.join("\n")).toHaveLength(0);
  });

  test("Arabic homepage is RTL with lang=ar", async ({ page }) => {
    await page.goto("/ar");
    await expect(page.locator("html")).toHaveAttribute("lang", "ar");
    await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
    await expect(page.locator("h1").first()).toBeVisible();
  });
});
