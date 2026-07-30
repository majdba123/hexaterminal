import { test, expect } from "@playwright/test";

// Deterministic fixtures come from database/seeders/DemoContentSeeder.php.
test("system detail page renders server-provided content (EN and AR)", async ({ page }) => {
  await page.goto("/en/systems/demo-ledger-platform");
  await expect(page.getByRole("heading", { level: 1, name: "Ledger Platform" })).toBeVisible();

  await page.goto("/ar/systems/demo-ledger-platform");
  await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
  await expect(page.getByRole("heading", { level: 1, name: "منصة السجلات" })).toBeVisible();
});

test("case study hub links through to a real detail page", async ({ page }) => {
  await page.goto("/en/case-studies");

  const firstCaseStudy = page.locator('main a[href*="/case-studies/"]').first();
  await expect(firstCaseStudy).toBeVisible();
  await firstCaseStudy.click();

  await expect(page).toHaveURL(/\/en\/case-studies\/.+/);
  await expect(page.locator("h1").first()).toBeVisible();
});
