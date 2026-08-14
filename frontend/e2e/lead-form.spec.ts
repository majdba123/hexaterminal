import { test, expect, type Page } from "@playwright/test";

async function fillEnglishLead(page: Page, summary: string) {
  await page.getByLabel("Full name").fill("Playwright Tester");
  await page.getByLabel("Email", { exact: true }).fill("playwright@example.com");
  if (summary) await page.getByLabel("Tell us about the project").fill(summary);
}

async function submitNativeLead(page: Page, path: string, javaScriptEnabled: boolean | undefined) {
  const stamp = Date.now();
  const name = `Native Tester ${stamp}`;
  const email = `native-${stamp}@example.com`;
  const summary = `Native lead summary ${stamp}`;

  expect(javaScriptEnabled).toBe(false);

  await page.goto(path);
  await page.locator('input[name="name"]').fill(name);
  await page.locator('input[name="email"]').fill(email);
  await page.locator('textarea[name="summary"]').fill(summary);

  const requestPromise = page.waitForRequest(
    (request) => request.url().includes("/api/leads") && request.method() === "POST",
  );
  await page.locator('input[name="email"]').press("Enter");
  const request = await requestPromise;
  const response = await request.response();
  const submitted = new URLSearchParams(request.postData() ?? "");

  expect(submitted.get("name")).toBe(name);
  expect(submitted.get("email")).toBe(email);
  expect(submitted.get("summary")).toBe(summary);
  expect(response?.status()).toBe(303);
  const redirect = new URL(response?.headers().location ?? "");
  expect(`${redirect.pathname}${redirect.search}`).toBe(`${path}?lead=success`);
  await expect
    .poll(() => new URL(page.url()).search)
    .toBe("?lead=success");

  const finalUrl = new URL(page.url());
  expect(finalUrl.search).toBe("?lead=success");
  expect(finalUrl.href).not.toContain(name);
  expect(finalUrl.href).not.toContain(email);
  expect(finalUrl.href).not.toContain(summary);
  expect(finalUrl.href).not.toContain(encodeURIComponent(summary));
}

test("project inquiry form exposes required semantics for name, email, and summary", async ({ page }) => {
  await page.goto("/en/start-a-project");

  await expect(page.getByLabel("Full name")).toHaveAttribute("required", "");
  await expect(page.getByLabel("Email", { exact: true })).toHaveAttribute("required", "");
  await expect(page.getByLabel("Tell us about the project")).toHaveAttribute("required", "");
  await expect(page.getByLabel("Tell us about the project")).toHaveAttribute("minlength", "10");
});

test("client validation blocks an empty English summary and focuses it without sending", async ({ page }) => {
  let requestCount = 0;
  await page.route("**/api/leads", async (route) => {
    requestCount += 1;
    await route.fulfill({ status: 201, contentType: "application/json", body: JSON.stringify({ status: "success", id: 1 }) });
  });

  await page.goto("/en/start-a-project");
  await fillEnglishLead(page, "");
  await page.getByRole("button", { name: "Send Project Details", exact: true }).click();

  const summary = page.getByLabel("Tell us about the project");
  await expect(summary).toHaveAttribute("aria-invalid", "true");
  await expect(summary).toBeFocused();
  expect(requestCount).toBe(0);
});

test("client validation blocks a summary shorter than ten trimmed characters", async ({ page }) => {
  let requestCount = 0;
  await page.route("**/api/leads", async (route) => {
    requestCount += 1;
    await route.fulfill({ status: 201, contentType: "application/json", body: JSON.stringify({ status: "success", id: 1 }) });
  });

  await page.goto("/en/start-a-project");
  await fillEnglishLead(page, "   short   ");
  await page.getByRole("button", { name: "Send Project Details", exact: true }).click();

  await expect(page.getByLabel("Tell us about the project")).toHaveAttribute("aria-invalid", "true");
  expect(requestCount).toBe(0);
});

test("Arabic project inquiry blocks an empty summary before submission", async ({ page }) => {
  let requestCount = 0;
  await page.route("**/api/leads", async (route) => {
    requestCount += 1;
    await route.fulfill({ status: 201, contentType: "application/json", body: JSON.stringify({ status: "success", id: 1 }) });
  });

  await page.goto("/ar/start-a-project");
  await page.getByLabel("الاسم الكامل").fill("مختبر بلايرايت");
  await page.getByLabel("البريد الإلكتروني", { exact: true }).fill("playwright-ar@example.com");
  await page.getByRole("button", { name: "إرسال", exact: true }).click();

  const summary = page.getByLabel("أخبرنا عن المشروع");
  await expect(summary).toHaveAttribute("aria-invalid", "true");
  await expect(summary).toBeFocused();
  expect(requestCount).toBe(0);
});

test("HTTP 422 field errors map to the summary instead of the generic request error", async ({ page }) => {
  await page.route("**/api/leads", async (route) => {
    await route.fulfill({
      status: 422,
      contentType: "application/json",
      body: JSON.stringify({ status: "error", errors: { summary: ["The summary field is required."] } }),
    });
  });

  await page.goto("/en/start-a-project");
  await fillEnglishLead(page, "A valid project summary for the request.");
  await page.getByRole("button", { name: "Send Project Details", exact: true }).click();

  const summary = page.getByLabel("Tell us about the project");
  await expect(summary).toHaveAttribute("aria-invalid", "true");
  await expect(summary).toBeFocused();
  await expect(page.getByText("Something went wrong. Please try again.")).toHaveCount(0);
});

test("HTTP 500 remains a generic request failure and does not invent field errors", async ({ page }) => {
  await page.route("**/api/leads", async (route) => {
    await route.fulfill({ status: 500, contentType: "application/json", body: JSON.stringify({ status: "error" }) });
  });

  await page.goto("/en/start-a-project");
  await fillEnglishLead(page, "A valid project summary for the request.");
  await page.getByRole("button", { name: "Send Project Details", exact: true }).click();

  await expect(page.getByText("Something went wrong. Please try again.")).toBeVisible();
  await expect(page.getByLabel("Tell us about the project")).not.toHaveAttribute("aria-invalid", "true");
});

test("valid project inquiry still submits and shows the success state", async ({ page }) => {
  await page.route("**/api/leads", async (route) => {
    await route.fulfill({ status: 201, contentType: "application/json", body: JSON.stringify({ status: "success", id: 123 }) });
  });

  await page.goto("/en/start-a-project");
  await fillEnglishLead(page, "Smoke-test lead submission.");
  await page.getByRole("button", { name: "Send Project Details", exact: true }).click();

  await expect(page.getByText("we received your project details", { exact: false })).toBeVisible();
});

test("contact uses the same lead form with general communication copy", async ({ page }) => {
  await page.goto("/en/contact");

  await expect(page.getByRole("heading", { name: "Let's talk about what you're trying to solve." })).toBeVisible();
  await expect(page.getByRole("button", { name: "Send Message", exact: true })).toBeVisible();
});

test.describe("native lead submission without JavaScript", () => {
  test.use({ javaScriptEnabled: false });

  test("English project inquiry posts natively and redirects without PII", async ({ page, javaScriptEnabled }) => {
    await submitNativeLead(page, "/en/start-a-project", javaScriptEnabled);
  });

  test("Arabic project inquiry posts natively and redirects without PII", async ({ page, javaScriptEnabled }) => {
    await submitNativeLead(page, "/ar/start-a-project", javaScriptEnabled);
  });

  test("English contact form posts natively and redirects without PII", async ({ page, javaScriptEnabled }) => {
    await submitNativeLead(page, "/en/contact", javaScriptEnabled);
  });

  test("Arabic contact form posts natively and redirects without PII", async ({ page, javaScriptEnabled }) => {
    await submitNativeLead(page, "/ar/contact", javaScriptEnabled);
  });
});
