import { expect, test } from "@playwright/test";

const widths = [390, 430, 768, 1024, 1280, 1440] as const;

async function assertNoHorizontalOverflow(page: import("@playwright/test").Page) {
  const metrics = await page.evaluate(() => ({
    innerWidth: window.innerWidth,
    scrollWidth: document.documentElement.scrollWidth,
  }));

  expect(metrics.scrollWidth).toBeLessThanOrEqual(metrics.innerWidth);
}

test("service detail presents a stronger business-story structure and related work remains conditional", async ({ page }) => {
  await page.goto("/en/services/custom-erp-crm-systems");

  await expect(page.getByRole("heading", { level: 1, name: "Custom ERP & CRM Systems" })).toBeVisible();
  await expect(page.getByText("Software Engineering Service")).toBeVisible();
  await expect(page.getByRole("heading", { level: 2, name: "Why businesses outgrow generic service delivery." })).toBeVisible();
  await expect(page.getByRole("heading", { level: 2, name: "A service designed around the way your business works." })).toBeVisible();
  await expect(page.getByRole("heading", { level: 2, name: "Capabilities shaped around your requirements." })).toBeVisible();
  await expect(page.getByRole("heading", { level: 2, name: "See how this type of solution takes shape." })).toHaveCount(0);
  await expect(page.getByRole("heading", { level: 2, name: "Technical Foundation" })).toHaveCount(0);

  await page.goto("/en/services/ecommerce-business-websites");

  const relatedWorkHeading = page.getByRole("heading", { level: 2, name: "See how this type of solution takes shape." });
  const capabilitiesHeading = page.getByRole("heading", { level: 2, name: "Capabilities shaped around your requirements." });

  await expect(relatedWorkHeading).toBeVisible();
  await expect(page.locator('main a[href*="/case-studies/"]').first()).toBeVisible();

  const [relatedY, capabilitiesY] = await Promise.all([
    relatedWorkHeading.evaluate((node) => node.getBoundingClientRect().top),
    capabilitiesHeading.evaluate((node) => node.getBoundingClientRect().top),
  ]);

  expect(relatedY).toBeLessThan(capabilitiesY);
});

test("service detail preserves RTL rendering", async ({ page }) => {
  await page.goto("/ar/services/custom-erp-crm-systems");
  await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
  await expect(page.getByRole("heading", { level: 1 })).toBeVisible();
  await expect(page.getByRole("link", { name: /ابدأ مشروعك/ }).first()).toBeVisible();
});

for (const width of widths) {
  test(`service detail has no horizontal overflow at ${width}px in EN and AR`, async ({ page }) => {
    await page.setViewportSize({ width, height: 900 });

    for (const route of [
      "/en/services/custom-erp-crm-systems",
      "/en/services/ecommerce-business-websites",
      "/ar/services/custom-erp-crm-systems",
      "/ar/services/ecommerce-business-websites",
    ]) {
      await page.goto(route);
      await expect(page.locator("main")).toBeVisible();
      await assertNoHorizontalOverflow(page);
    }
  });
}
