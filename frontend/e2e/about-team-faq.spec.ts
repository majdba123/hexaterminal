import { expect, test } from "@playwright/test";

test("about page links to the published team profile and FAQ groups null-category items under General", async ({ page }) => {
  await page.goto("/en/about");

  await expect(
    page.getByRole("heading", { level: 1, name: "We build software around how businesses actually operate." }),
  ).toBeVisible();

  const profileLink = page.locator('a[href*="/about/team/"]').first();
  await expect(profileLink).toBeVisible();
  await profileLink.click();

  await expect(page).toHaveURL(/\/en\/about\/team\/majd-bayer$/);
  await expect(page.getByRole("heading", { level: 1, name: "Majd Bayer" })).toBeVisible();

  await page.goto("/en/about/faq");
  await expect(page.getByRole("heading", { level: 1, name: "Frequently Asked Questions" })).toBeVisible();
  await expect(page.getByRole("navigation", { name: "Browse by category" })).toContainText("General");
  await expect(page.getByRole("heading", { level: 2, name: "General" })).toBeVisible();
});

test("team detail 404s for an unknown slug and Arabic about/faq pages preserve RTL", async ({ page }) => {
  const response = await page.goto("/en/about/team/does-not-exist");
  expect(response?.status()).toBe(404);
  await expect(page.getByRole("heading", { name: "Page not found" })).toBeVisible();

  await page.goto("/ar/about");
  await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
  await expect(page.getByRole("heading", { level: 1 })).toBeVisible();

  await page.goto("/ar/about/faq");
  await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
  await expect(page.getByRole("heading", { level: 1 })).toBeVisible();
});
