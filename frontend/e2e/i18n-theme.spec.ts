import { test, expect } from "@playwright/test";

test("locale switcher moves between equivalent EN and AR routes", async ({ page }) => {
  await page.goto("/en/services");
  await page.getByRole("button", { name: "Switch language" }).click();

  await expect(page).toHaveURL(/\/ar\/services$/);
  await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
  await expect(page.locator("html")).toHaveAttribute("lang", "ar");
});

test("theme toggle switches and persists across reload", async ({ page }) => {
  await page.goto("/en");
  const html = page.locator("html");

  const initial = await html.getAttribute("data-theme");
  await page.getByRole("button", { name: "Toggle theme" }).click();

  const toggled = initial === "light" ? "dark" : "light";
  await expect(html).toHaveAttribute("data-theme", toggled);

  // Persisted via localStorage and re-applied by the no-flash init script.
  await page.reload();
  await expect(html).toHaveAttribute("data-theme", toggled);
});
