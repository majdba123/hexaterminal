import { expect, test } from "@playwright/test";

test("case study detail follows the proof-first buyer journey in EN", async ({ page }) => {
  await page.goto("/en/case-studies/malik-group-furniture-catalog");

  await expect(
    page.getByRole("heading", {
      level: 1,
      name: /Turning Social Media Product Enquiries into a Structured Furniture Catalog/i,
    }),
  ).toBeVisible();
  await expect(page.locator("main").getByRole("link", { name: /Start a Project/i })).toBeVisible();
  await expect(page.getByRole("img", { name: /Malik Group furniture catalog interface/i })).toBeVisible();
  await expect(page.getByRole("link", { name: /View Live Project/i })).toHaveCount(0);

  const headings = await page.locator("main h2").allTextContents();
  expect(headings).toEqual([
    "The operational situation that made the project necessary.",
    "How the product and system response were structured.",
    "Screens from the delivered product.",
    "What the delivered solution covers.",
    "What the solution is designed to make possible.",
    "Where this case study sits in the broader delivery context.",
  ]);

  const proofHeading = page.getByRole("heading", { level: 2, name: "Screens from the delivered product." });
  await expect(proofHeading).toBeVisible();
  await expect(page.locator('main a[href$=".png"]').filter({ has: page.locator("img") })).toHaveCount(4);
  await expect(page.getByRole("link", { name: /Related service E-commerce & Business Websites/i })).toBeVisible();
  await expect(page.getByRole("link", { name: /Related system Malik Group Furniture Catalog/i })).toBeVisible();
});

test("case study detail renders in AR with RTL intact", async ({ page }) => {
  await page.goto("/ar/case-studies/malik-group-furniture-catalog");

  await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
  await expect(page.getByRole("heading", { level: 1 })).toBeVisible();
  await expect(page.getByRole("heading", { level: 2, name: "شاشات من المنتج الذي تم تسليمه." })).toBeVisible();
  await expect(page.getByText("تم تطويره بواسطة HexaTerminal")).toBeVisible();
});
