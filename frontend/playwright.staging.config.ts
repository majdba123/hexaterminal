import { defineConfig, devices } from "@playwright/test";

/**
 * Smoke suite for a DEPLOYED staging environment. Unlike playwright.config.ts,
 * this does NOT build or start a local server -- it runs against a live URL you
 * supply, so nothing here may hardcode localhost.
 *
 * Required:
 *   STAGING_URL       public frontend base, e.g. https://staging.hexaterminal.com
 * Optional:
 *   STAGING_API_URL   API origin for health checks, e.g. https://api-staging.hexaterminal.com
 *                     (defaults to STAGING_URL, useful when the API is same-origin proxied)
 *   STAGING_CMS_URL   CMS login URL (defaults to `${STAGING_API_URL}/cms`)
 *
 * Run: STAGING_URL=https://staging.hexaterminal.com npm run test:e2e:staging
 */
const STAGING_URL = process.env.STAGING_URL;

if (!STAGING_URL) {
  throw new Error(
    "STAGING_URL is required for the staging smoke suite, e.g. STAGING_URL=https://staging.hexaterminal.com npm run test:e2e:staging",
  );
}

export default defineConfig({
  testDir: "./e2e-staging",
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  reporter: process.env.CI ? [["list"], ["html", { open: "never" }]] : "list",
  use: {
    baseURL: STAGING_URL,
    trace: "on-first-retry",
    ignoreHTTPSErrors: false,
  },
  projects: [{ name: "chromium", use: { ...devices["Desktop Chrome"] } }],
  // No webServer: the target is already deployed.
});
