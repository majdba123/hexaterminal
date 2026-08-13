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
    await expect(
      page.getByRole("heading", {
        level: 1,
        name: "Custom software systems built around how your business actually works.",
      }),
    ).toBeVisible();
    await expect(page.getByRole("link", { name: "Start a Project", exact: true }).first()).toHaveAttribute(
      "href",
      "/en/start-a-project",
    );
    await expect(page.getByRole("link", { name: "Explore Our Work", exact: true })).toHaveAttribute(
      "href",
      "/en/case-studies",
    );
    await expect(page.getByRole("navigation", { name: "Main" })).toBeVisible();

    // Ignore benign resource-load noise (e.g. favicon/media); fail on real errors.
    const serious = errors.filter((e) => !/favicon|net::ERR|Failed to load resource/i.test(e));
    expect(serious, serious.join("\n")).toHaveLength(0);
  });

  test("Arabic homepage is RTL with lang=ar", async ({ page }) => {
    await page.goto("/ar");
    await expect(page.locator("html")).toHaveAttribute("lang", "ar");
    await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
    await expect(
      page.getByRole("heading", {
        level: 1,
        name: "أنظمة برمجية مخصصة تُبنى حول طريقة عمل شركتك فعليًا.",
      }),
    ).toBeVisible();
    await expect(page.getByRole("link", { name: "ابدأ مشروعك", exact: true }).first()).toHaveAttribute(
      "href",
      "/ar/start-a-project",
    );
    await expect(page.getByRole("link", { name: "استكشف أعمالنا", exact: true })).toHaveAttribute(
      "href",
      "/ar/case-studies",
    );
  });
});
