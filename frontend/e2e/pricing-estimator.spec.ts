import { test, expect } from "@playwright/test";

// Fixtures come from database/seeders/PricingEstimatorFixtureSeeder.php.
test.describe("Pricing page", () => {
  test("renders engagement models in EN with request-quote CTAs (no fabricated price)", async ({
    page,
  }) => {
    await page.goto("/en/pricing");
    await expect(page.locator("h1")).toContainText(/pricing/i);
    await expect(page.getByText(/Discovery & Architecture Sprint/i)).toBeVisible();
    // Fail-closed: no approved price seeded, so every model shows the estimate CTA.
    await expect(page.getByRole("link", { name: /request a scoped estimate/i }).first()).toBeVisible();
  });

  test("renders in Arabic with RTL", async ({ page }) => {
    await page.goto("/ar/pricing");
    await expect(page.locator("html")).toHaveAttribute("dir", "rtl");
    await expect(page.locator("html")).toHaveAttribute("lang", "ar");
  });

  test("pricing page is indexable when it has content", async ({ page }) => {
    await page.goto("/en/pricing");
    const robots = await page.locator('meta[name="robots"]').count();
    // No noindex meta => indexable.
    if (robots > 0) {
      const content = await page.locator('meta[name="robots"]').first().getAttribute("content");
      expect(content ?? "").not.toMatch(/noindex/i);
    }
  });
});

test.describe("Cost estimator", () => {
  test("progressive flow branches, shows a range without an email, and revisits the result", async ({
    page,
  }) => {
    const consoleErrors: string[] = [];
    page.on("console", (msg) => {
      if (msg.type() === "error") consoleErrors.push(msg.text());
    });

    await page.goto("/en/estimate");
    await expect(page.getByText(/Step 1 of/i)).toBeVisible();

    // Step 1: build
    await page.getByRole("radio", { name: "SaaS platform", exact: true }).check();
    await page.getByRole("button", { name: /^Next$/ }).click();

    // Step 2: stage = idea (this should BRANCH OUT the migration question)
    await expect(page.getByText(/Step 2 of/i)).toBeVisible();
    await page.getByRole("radio", { name: "Just an idea", exact: true }).check();
    await page.getByRole("button", { name: /^Next$/ }).click();

    // Walk the remaining visible questions by always picking the first option.
    for (let i = 0; i < 12; i++) {
      const seeEstimate = page.getByRole("button", { name: /see my estimate/i });
      if (await seeEstimate.isVisible().catch(() => false)) {
        await seeEstimate.click();
        break;
      }
      // Pick the first option in the fieldset, then advance. The options are
      // native radios/checkboxes (see components/site/cost-estimator.tsx).
      await page.locator("fieldset input").first().check();
      await page.getByRole("button", { name: /^Next$/ }).click();
    }

    // Result: a range appears, no email was required to see it.
    await expect(page).toHaveURL(/\/en\/estimate\/[0-9a-f-]{36}/);
    await expect(page.getByText(/estimated range/i)).toBeVisible();
    await expect(page.getByText(/USD/).first()).toBeVisible();
    await expect(page.getByText(/not a binding quotation/i)).toBeVisible();

    // Result page must be noindex and out of indexing.
    const robots = await page.locator('meta[name="robots"]').first().getAttribute("content");
    expect(robots ?? "").toMatch(/noindex/i);

    // The result URL can be revisited directly.
    const url = page.url();
    await page.goto(url);
    await expect(page.getByText(/estimated range/i)).toBeVisible();

    expect(consoleErrors.filter((e) => !e.includes("favicon"))).toEqual([]);
  });

  test("optional lead capture appears only after the result", async ({ page }) => {
    await page.goto("/en/estimate");
    await page.getByRole("radio", { name: "API / backend", exact: true }).check();
    await page.getByRole("button", { name: /^Next$/ }).click();

    for (let i = 0; i < 12; i++) {
      const seeEstimate = page.getByRole("button", { name: /see my estimate/i });
      if (await seeEstimate.isVisible().catch(() => false)) {
        await seeEstimate.click();
        break;
      }
      await page.locator("fieldset input").first().check();
      await page.getByRole("button", { name: /^Next$/ }).click();
    }

    await expect(page).toHaveURL(/\/estimate\/[0-9a-f-]{36}/);
    // The lead form is revealed by choosing an action, not gating the result.
    await page.getByRole("button", { name: /book a discovery call/i }).click();
    await expect(page.getByLabel(/email/i)).toBeVisible();
  });

  test("options are a real radio group, not independent toggle buttons", async ({ page }) => {
    await page.goto("/en/estimate");

    // One grouped set: picking a second option must clear the first, and the
    // group must be reachable and operable from the keyboard alone. As
    // aria-pressed buttons none of this held -- a screen reader announced five
    // unrelated toggles and arrow keys did nothing.
    const options = page.getByRole("radio");
    await expect(options.first()).toBeVisible();
    const count = await options.count();
    expect(count).toBeGreaterThan(1);

    await options.nth(0).check();
    await expect(options.nth(0)).toBeChecked();

    await options.nth(1).check();
    await expect(options.nth(1)).toBeChecked();
    await expect(options.nth(0)).not.toBeChecked();

    // Arrow-key navigation within the group comes free with native radios.
    await options.nth(1).focus();
    await page.keyboard.press("ArrowDown");
    await expect(options.nth(2)).toBeChecked();
    await expect(options.nth(1)).not.toBeChecked();
  });

  test("unknown estimate id shows the not-found state", async ({ page }) => {
    await page.goto("/en/estimate/11111111-1111-1111-1111-111111111111");
    await expect(page.getByText(/estimate not found/i)).toBeVisible();
  });
});
