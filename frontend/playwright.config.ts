import { defineConfig, devices } from "@playwright/test";

/**
 * Smoke suite config. Runs against the built production app (`npm run start`),
 * which itself needs the Laravel API reachable at API_URL. Locally: build the
 * frontend and start the seeded API first, then `npm run test:e2e`. In CI the
 * e2e job boots the API, builds, then runs this (see .github/workflows/ci.yml).
 */
const PORT = 3000;
const baseURL = process.env.NEXT_PUBLIC_SITE_URL ?? `http://localhost:${PORT}`;

export default defineConfig({
  testDir: "./e2e",
  // Serial: the smoke suite runs against `php artisan serve`, which is
  // single-threaded. Parallel workers would queue behind each SSR fetch and
  // time out. The suite is small, so serial is fast enough and deterministic.
  fullyParallel: false,
  forbidOnly: !!process.env.CI,
  retries: process.env.CI ? 1 : 0,
  workers: 1,
  reporter: process.env.CI ? [["list"], ["html", { open: "never" }]] : "list",
  use: {
    baseURL,
    trace: "on-first-retry",
  },
  projects: [{ name: "chromium", use: { ...devices["Desktop Chrome"] } }],

  /*
   * The suite needs the LARAVEL API running separately, and it must be able to
   * serve more than one request at a time. Start it like this:
   *
   *   PHP_CLI_SERVER_WORKERS=8 php artisan serve --port=8000 --no-reload
   *
   * Both parts matter. `php artisan serve` is single-worker by default, and
   * `PHP_CLI_SERVER_WORKERS` is SILENTLY IGNORED unless `--no-reload` is also
   * passed (artisan prints a warning saying so). A single-worker API
   * self-deadlocks on this app's estimator flow: the browser calls the Next
   * route handler at /api/estimates, which then calls Laravel while Laravel is
   * still busy serving that same browser request. Nothing is free to answer,
   * the fetch times out, and the page renders its error state.
   *
   * That looks exactly like an application bug and is not one --
   * e2e/pricing-estimator.spec.ts was failing on it. If estimator or lead
   * tests fail with `ECONNREFUSED`, a fetch timeout, or "Something went
   * wrong", check how the API was started before touching the app code.
   */
  webServer: {
    command: "npm run start",
    url: `http://localhost:${PORT}`,
    reuseExistingServer: !process.env.CI,
    timeout: 120_000,
    env: {
      API_URL: process.env.API_URL ?? "http://127.0.0.1:8000/api/v1/public",
      NEXT_PUBLIC_SITE_URL: baseURL,
      NEXT_PUBLIC_ALLOW_INDEXING: process.env.NEXT_PUBLIC_ALLOW_INDEXING ?? "true",
      // Enables the on-demand revalidation endpoint so e2e/revalidate.spec.ts
      // can prove auth is enforced. A throwaway value; never a real secret.
      REVALIDATE_SECRET: process.env.REVALIDATE_SECRET ?? "e2e-revalidate-secret",
    },
  },
});
