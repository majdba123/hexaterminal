import { expect, test } from "@playwright/test";
import { teamLayoutMode } from "../lib/about-layout";
import { groupFaqs } from "../lib/faq-groups";

test("about page uses the featured single-member layout and keeps team before the final delivery section", async ({ page }) => {
  await page.goto("/en/about");

  await expect(
    page.getByRole("heading", { level: 1, name: "We build software around how businesses actually operate." }),
  ).toBeVisible();

  await expect(page.getByTestId("team-featured")).toBeVisible();
  await expect(page.getByTestId("team-grid")).toHaveCount(0);

  const teamTop = await page.getByTestId("about-team").evaluate((element) => element.getBoundingClientRect().top);
  const processTop = await page.getByTestId("about-process").evaluate((element) => element.getBoundingClientRect().top);
  expect(teamTop).toBeLessThan(processTop);

  const profileLink = page.locator('a[href*="/about/team/"]').first();
  await expect(profileLink).toBeVisible();
  await profileLink.click();

  await expect(page).toHaveURL(/\/en\/about\/team\/majd-bayer$/);
  await expect(page.getByRole("heading", { level: 1, name: "Majd Bayer" })).toBeVisible();
});

test("faq single-category layout removes sidebar navigation and keeps the primary column wide", async ({ page }) => {
  await page.goto("/en/about/faq");

  await expect(page.getByRole("heading", { level: 1, name: "Frequently Asked Questions" })).toBeVisible();
  await expect(page.getByTestId("faq-category-nav")).toHaveCount(0);
  await expect(page.getByRole("heading", { level: 2, name: "General" })).toHaveCount(0);
  await expect(page.getByRole("button").filter({ hasText: "How much does a custom software project cost?" })).toBeVisible();
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
  await expect(page.getByTestId("faq-category-nav")).toHaveCount(0);
});

test("layout helpers keep the about and faq branching logic stable", async () => {
  expect(teamLayoutMode(1)).toBe("featured");
  expect(teamLayoutMode(2)).toBe("grid");
  expect(teamLayoutMode(4)).toBe("grid");

  expect(groupFaqs([], "General")).toEqual([]);
  expect(
    groupFaqs(
      [
        { question: "Q1", answer: "A1", category: null },
        { question: "Q2", answer: "A2", category: "" },
      ],
      "General",
    ),
  ).toHaveLength(1);

  const grouped = groupFaqs(
    [
      { question: "Q1", answer: "A1", category: "General" },
      { question: "Q2", answer: "A2", category: "Delivery" },
      { question: "Q3", answer: "A3", category: "Delivery" },
    ],
    "General",
  );

  expect(grouped).toHaveLength(2);
  expect(grouped[0].label).toBe("General");
  expect(grouped[1].label).toBe("Delivery");
  expect(grouped[1].items).toHaveLength(2);
});
