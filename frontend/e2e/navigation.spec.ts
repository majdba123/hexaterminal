import { test, expect } from "@playwright/test";

test.describe("desktop about navigation", () => {
  test.use({ viewport: { width: 1600, height: 960 } });

  test("about trigger opens a submenu without navigating, then closes accessibly", async ({ page }) => {
    await page.goto("/en");

    const aboutTrigger = page.getByRole("button", { name: "About" });
    await expect(aboutTrigger).toHaveAttribute("aria-expanded", "false");

    await aboutTrigger.click();
    await expect(page).toHaveURL(/\/en$/);
    await expect(aboutTrigger).toHaveAttribute("aria-expanded", "true");

    const menu = page.getByRole("menu", { name: "About" });
    await expect(menu).toBeVisible();
    await expect(menu.getByRole("menuitem", { name: "About Us", exact: true })).toBeVisible();
    await expect(menu.getByRole("menuitem", { name: "FAQ", exact: true })).toBeVisible();

    await page.keyboard.press("Escape");
    await expect(menu).toBeHidden();

    await aboutTrigger.click();
    await expect(menu).toBeVisible();
    await page.mouse.click(20, 20);
    await expect(menu).toBeHidden();
  });

  test("keyboard flow opens the about submenu and navigates through a child item", async ({ page }) => {
    await page.goto("/en");

    const aboutTrigger = page.getByRole("button", { name: "About" });
    await aboutTrigger.focus();
    await expect(aboutTrigger).toBeFocused();

    await page.keyboard.press("ArrowDown");
    await expect(page.getByRole("menuitem", { name: "About Us", exact: true })).toBeFocused();

    await page.keyboard.press("Enter");
    await expect(page).toHaveURL(/\/en\/about$/);
  });
});

test.describe("mobile navigation", () => {
  test.use({ viewport: { width: 390, height: 844 } });

  test("mobile about group expands instead of navigating and child links close the menu", async ({ page }) => {
    await page.goto("/en");

    await page.getByRole("button", { name: "Open menu" }).click();

    const dialog = page.getByRole("dialog");
    await expect(dialog).toBeVisible();

    const aboutTrigger = dialog.getByRole("button", { name: "About" });
    await expect(aboutTrigger).toHaveAttribute("aria-expanded", "false");

    await aboutTrigger.click();
    await expect(page).toHaveURL(/\/en$/);
    await expect(aboutTrigger).toHaveAttribute("aria-expanded", "true");
    await expect(dialog.getByRole("link", { name: "About Us", exact: true })).toBeVisible();
    await expect(dialog.getByRole("link", { name: "FAQ", exact: true })).toBeVisible();

    await dialog.getByRole("link", { name: "FAQ", exact: true }).click();
    await expect(page).toHaveURL(/\/en\/about\/faq$/);
    await expect(page.getByRole("dialog")).toBeHidden();
  });

  test("mobile menu closes on Escape", async ({ page }) => {
    await page.goto("/en");
    await page.getByRole("button", { name: "Open menu" }).click();
    await expect(page.getByRole("dialog")).toBeVisible();

    await page.keyboard.press("Escape");
    await expect(page.getByRole("dialog")).toBeHidden();
  });
});
