import { test, expect } from "@playwright/test";

test("project inquiry form validates required fields", async ({ page }) => {
  await page.goto("/en/start-a-project");

  const name = page.getByLabel("Full name");
  await expect(name).toBeVisible();
  // Required attributes are present so the browser blocks an empty submit.
  await expect(name).toHaveAttribute("required", "");
  await expect(page.getByLabel("Email", { exact: true })).toHaveAttribute("required", "");
});

test("project inquiry form submits to the API and shows the success state", async ({ page }) => {
  await page.goto("/en/start-a-project");

  await page.getByLabel("Full name").fill("Playwright Tester");
  await page.getByLabel("Email", { exact: true }).fill("playwright@example.com");
  await page.getByLabel("Tell us about the project").fill("Smoke-test lead submission.");

  await page.getByRole("button", { name: "Send", exact: true }).click();

  // Success replaces the form with a confirmation message.
  await expect(page.getByText("we've received your request", { exact: false })).toBeVisible();
});
