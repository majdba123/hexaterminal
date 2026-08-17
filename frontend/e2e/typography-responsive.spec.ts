import { expect, test } from "@playwright/test";

const widths = [390, 430, 768, 1024, 1280, 1440] as const;

async function assertNoHorizontalOverflow(page: import("@playwright/test").Page) {
  const metrics = await page.evaluate(() => ({
    innerWidth: window.innerWidth,
    scrollWidth: document.documentElement.scrollWidth,
  }));

  expect(metrics.scrollWidth).toBeLessThanOrEqual(metrics.innerWidth);
}

test("custom bilingual fonts attach to the document", async ({ page }) => {
  await page.goto("/en/about");

  const enFont = await page.locator("body").evaluate((element) => getComputedStyle(element).fontFamily);
  expect(enFont).toContain("Manrope");

  await page.goto("/ar/about");
  await expect(page.locator("html")).toHaveAttribute("dir", "rtl");

  const arFont = await page.locator("body").evaluate((element) => getComputedStyle(element).fontFamily);
  expect(arFont).toContain("IBM Plex Sans Arabic");
});

for (const width of widths) {
  test(`no horizontal overflow at ${width}px in EN and AR`, async ({ page }) => {
    await page.setViewportSize({ width, height: 900 });

    for (const route of [
      "/en/about",
      "/en/about/faq",
      "/en/about/team/majd-bayer",
      "/en/systems/demo-ledger-platform",
      "/ar/about",
      "/ar/about/faq",
      "/ar/about/team/majd-bayer",
      "/ar/systems/demo-ledger-platform",
    ]) {
      await page.goto(route);
      await expect(page.locator("main")).toBeVisible();
      await assertNoHorizontalOverflow(page);
    }
  });
}
