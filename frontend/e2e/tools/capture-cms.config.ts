import { defineConfig } from "@playwright/test";
import fs from "node:fs";
import path from "node:path";

/**
 * Standalone config for the CMS capture tool (e2e/tools/capture-cms.ts).
 *
 * Separate from playwright.config.ts on purpose:
 *   - no `webServer`: the target is the LARAVEL app (php artisan serve), not
 *     the Next.js frontend, and it must already be running
 *   - `storageState`: replays the session saved by the one-time
 *     `playwright open --save-storage` login
 *   - a tall desktop viewport, because these are documentation screenshots
 *
 * The main suite never picks this file up -- it is not under the default
 * testDir glob and it is invoked with --config.
 */
export default defineConfig({
  testDir: path.resolve(__dirname),
  testMatch: /(capture-cms|screenshot-cms-html)\.ts$/,
  // One test walks ~38 pages, so this is a whole-run budget.
  timeout: 600_000,
  retries: 0,
  workers: 1,
  reporter: "line",
  use: {
    baseURL: process.env.CMS_URL ?? "http://127.0.0.1:8010/cms",
    // Only the live-panel capture needs a session; the HTML
    // screenshotter reads from disk. Absent file -> skip it.
    ...(fs.existsSync(path.resolve(__dirname, "cms-auth.json"))
      ? { storageState: path.resolve(__dirname, "cms-auth.json") }
      : {}),
    viewport: { width: 1600, height: 1000 },
    deviceScaleFactor: 2,
    colorScheme: "light",
  },
});
