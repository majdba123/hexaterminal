import { test, expect } from "@playwright/test";

test("unknown slug returns 404 and renders the localized not-found page", async ({ page }) => {
  const response = await page.goto("/en/services/this-slug-does-not-exist");
  expect(response?.status()).toBe(404);
  await expect(page.getByRole("heading", { name: "Page not found" })).toBeVisible();
  await expect(page.getByRole("link", { name: "Back to home" })).toBeVisible();
});

test("unknown slug renders the Arabic not-found page under /ar", async ({ page }) => {
  const response = await page.goto("/ar/services/this-slug-does-not-exist");
  expect(response?.status()).toBe(404);
  await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
  await expect(page.getByRole("heading", { name: "الصفحة غير موجودة" })).toBeVisible();
});
