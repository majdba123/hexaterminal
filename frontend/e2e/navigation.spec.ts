import { test, expect } from "@playwright/test";

// Mobile viewport so the mobile menu button is shown.
test.use({ viewport: { width: 390, height: 844 } });

test("mobile navigation opens, navigates, and closes accessibly", async ({ page }) => {
  await page.goto("/en");

  await page.getByRole("button", { name: "Open menu" }).click();

  const dialog = page.getByRole("dialog");
  await expect(dialog).toBeVisible();

  // Navigate via a link inside the menu.
  await dialog.getByRole("link", { name: "Services", exact: true }).click();
  await expect(page).toHaveURL(/\/en\/services$/);

  // Menu closes on navigation.
  await expect(page.getByRole("dialog")).toBeHidden();
});

test("mobile menu closes on Escape", async ({ page }) => {
  await page.goto("/en");
  await page.getByRole("button", { name: "Open menu" }).click();
  await expect(page.getByRole("dialog")).toBeVisible();

  await page.keyboard.press("Escape");
  await expect(page.getByRole("dialog")).toBeHidden();
});
