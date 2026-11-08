import { defineConfig } from "@playwright/test";

/**
 * Pure unit tests, run on Playwright's test runner because the repository
 * already depends on it. No browser, no dev server, no Laravel API — so these
 * are fast enough to run on every pull request.
 *
 * Kept in its own config because `playwright.config.ts` boots `npm run start`
 * via `webServer`, which a unit test must not need.
 *
 * Modules under test must not import `server-only`; see lib/api/visitor-ip.ts.
 */
export default defineConfig({
  testDir: "./unit",
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: 0,
  reporter: process.env.CI ? "list" : "list",
  // No `projects` and no `use.browserName`: these tests never open a page, so
  // there is nothing to install a browser for.
});
