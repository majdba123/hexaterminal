import { expect, test } from "@playwright/test";
import en from "../messages/en.json";
import ar from "../messages/ar.json";

/**
 * CRMA engineering capability showcase (/case-studies/crma): a static,
 * code-owned page. Covers structure, the capability-not-client framing, the
 * accessible module tablist and gallery dialog, RTL, and the teaser
 * placements on the home, work and services indexes.
 */

const PATH = "/case-studies/crma";
const SECTION_IDS = [
  "os",
  "flow",
  "logic",
  "workflow",
  "governed-ai",
  "knowledge",
  "architecture",
  "security",
  "integrations",
  "mobile",
  "bilingual",
  "evidence",
  "screens",
  "services",
];

async function noHorizontalOverflow(page: import("@playwright/test").Page) {
  const overflow = await page.evaluate(() => document.documentElement.scrollWidth - window.innerWidth);
  expect(overflow).toBeLessThanOrEqual(1);
}

test("renders every section in order with one h1 (EN)", async ({ page }) => {
  await page.goto(`/en${PATH}`);

  await expect(page.locator("html")).toHaveAttribute("dir", "ltr");
  await expect(page.getByRole("heading", { level: 1 })).toHaveCount(1);
  await expect(page.getByRole("heading", { level: 1 })).toHaveText(en.crma.hero.title);

  const ids = await page.locator("main section[id]").evaluateAll((els) => els.map((el) => el.id));
  expect(ids).toEqual(SECTION_IDS);

  await expect(page.getByRole("heading", { level: 2, name: en.crma.ai.title1, exact: false })).toBeVisible();
  // Framed as capability, never as client delivery.
  await expect(page.getByText(en.crma.hero.disclosure)).toBeVisible();

  const cta = page.locator("main").getByRole("link", { name: en.crma.cta.primary });
  await expect(cta).toHaveAttribute("href", "/en/start-a-project");
});

test("every image has an alt attribute", async ({ page }) => {
  await page.goto(`/en${PATH}`);
  const missing = await page.locator("main img:not([alt])").count();
  expect(missing).toBe(0);
});

test("module viewer is a keyboard-operable tablist", async ({ page }) => {
  await page.goto(`/en${PATH}`);
  const tabs = page.getByRole("tablist", { name: en.crma.os.tabsLabel }).getByRole("tab");
  await expect(tabs).toHaveCount(en.crma.os.modules.length);

  await tabs.first().focus();
  await page.keyboard.press("ArrowRight");
  await expect(tabs.nth(1)).toHaveAttribute("aria-selected", "true");
  await expect(tabs.nth(1)).toBeFocused();
  await page.keyboard.press("End");
  await expect(tabs.last()).toHaveAttribute("aria-selected", "true");
  await expect(page.getByRole("tabpanel").getByRole("img")).toHaveAttribute(
    "alt",
    en.crma.os.modules[en.crma.os.modules.length - 1].alt,
  );
});

test("gallery opens an accessible dialog with keyboard navigation", async ({ page }) => {
  await page.goto(`/en${PATH}`);
  const items = en.crma.gallery.items;
  const first = page.getByRole("button", { name: `${en.crma.gallery.open}: ${items[0].caption}` });
  await first.click();

  const dialog = page.getByRole("dialog");
  await expect(dialog).toBeVisible();
  await expect(dialog.getByRole("heading", { name: items[0].caption })).toBeVisible();
  await page.keyboard.press("ArrowRight");
  await expect(dialog.getByRole("heading", { name: items[1].caption })).toBeVisible();
  await page.keyboard.press("Escape");
  await expect(dialog).toBeHidden();
  await expect(first).toBeFocused();
});

test("renders in Arabic with RTL intact", async ({ page }) => {
  await page.goto(`/ar${PATH}`);
  await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
  await expect(page.getByRole("heading", { level: 1 })).toHaveText(ar.crma.hero.title);
  await expect(page.getByText(ar.crma.hero.disclosure)).toBeVisible();
});

for (const width of [1440, 1024, 768, 390]) {
  test(`no horizontal overflow at ${width}px`, async ({ page }) => {
    await page.setViewportSize({ width, height: 900 });
    for (const locale of ["en", "ar"]) {
      await page.goto(`/${locale}${PATH}`);
      await noHorizontalOverflow(page);
    }
  });
}

test("teaser links to the case study from home, work and services", async ({ page }) => {
  for (const path of ["/en", "/en/systems", "/en/services", "/en/case-studies"]) {
    await page.goto(path);
    const teaser = page.locator('article[aria-labelledby="crma-teaser-title"]');
    await expect(teaser, path).toHaveCount(1);
    await expect(teaser.getByRole("link", { name: en.crma.teaser.cta })).toHaveAttribute("href", `/en${PATH}`);
  }
});
