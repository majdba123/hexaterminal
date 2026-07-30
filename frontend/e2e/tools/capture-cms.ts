import { test, expect, type Page } from "@playwright/test";
import fs from "node:fs";
import path from "node:path";

/**
 * Capture every page of the Filament CMS to docs/cms-screens/.
 *
 * NOT part of the normal suite -- it lives under e2e/tools/ and the config's
 * testMatch does not pick it up. Run it explicitly:
 *
 *   1. Create an admin account (once). The seeder refuses to invent
 *      credentials, so set them yourself in .env first:
 *        ADMIN_EMAIL=you@example.com
 *        ADMIN_PASSWORD=<at least 12 characters>
 *      then:  php artisan db:seed --class=RolesSeeder
 *             php artisan db:seed --class=UsersTableSeeder
 *
 *   2. Log in once and save the session. This opens a browser; sign in
 *      (including the TOTP step, which is REQUIRED for admin accounts), then
 *      close the window:
 *        npx playwright open --save-storage=e2e/tools/cms-auth.json http://127.0.0.1:8010/cms
 *
 *   3. Capture:
 *        npx playwright test e2e/tools/capture-cms.ts --config=e2e/tools/capture-cms.config.ts
 *
 * The route list is DISCOVERED from the sidebar rather than hard-coded, so it
 * cannot drift out of sync with the panel: anything registered in navigation
 * gets captured, and anything captured is by definition reachable. Each
 * resource contributes its list page and its create form.
 */

const CMS = process.env.CMS_URL ?? "http://127.0.0.1:8010/cms";
const OUT = path.resolve(__dirname, "../../../docs/cms-screens");

function slug(url: string): string {
  const p = new URL(url).pathname.replace(/^\/cms\/?/, "") || "dashboard";
  return p.replace(/\//g, "__").replace(/[^a-z0-9_-]/gi, "-");
}

async function shoot(page: Page, url: string, name: string) {
  await page.goto(url, { waitUntil: "networkidle" });
  // Filament tables and forms stream in via Livewire; wait for the shell then
  // let the panel settle so the capture is not of a skeleton.
  await page.waitForSelector(".fi-main, .fi-body", { timeout: 15_000 }).catch(() => {});
  await page.waitForTimeout(700);
  await page.screenshot({ path: path.join(OUT, `${name}.png`), fullPage: true });
}

test("capture every CMS screen", async ({ page }) => {
  fs.mkdirSync(OUT, { recursive: true });

  await page.goto(CMS, { waitUntil: "networkidle" });

  // If the session file was stale we land on the login form -- fail loudly
  // rather than filling docs/ with screenshots of a login page.
  expect(
    await page.locator('input[type="password"]').count(),
    "Not authenticated: re-run the `playwright open --save-storage` step",
  ).toBe(0);

  await shoot(page, CMS, "00-dashboard");

  const hrefs = await page.$$eval(".fi-sidebar a[href], nav a[href]", (as) =>
    Array.from(new Set(as.map((a) => (a as HTMLAnchorElement).href))).filter((h) =>
      /\/cms(\/|$)/.test(h),
    ),
  );

  const manifest: { screen: string; url: string }[] = [];
  let i = 1;

  for (const href of hrefs.sort()) {
    const name = `${String(i).padStart(2, "0")}-${slug(href)}`;
    await shoot(page, href, name);
    manifest.push({ screen: `${name}.png`, url: href });
    i++;

    // Resource list pages also have a create form worth capturing.
    const createUrl = `${href.replace(/\/$/, "")}/create`;
    const res = await page.request.get(createUrl).catch(() => null);
    if (res && res.ok()) {
      const createName = `${String(i).padStart(2, "0")}-${slug(createUrl)}`;
      await shoot(page, createUrl, createName);
      manifest.push({ screen: `${createName}.png`, url: createUrl });
      i++;
    }
  }

  fs.writeFileSync(
    path.join(OUT, "manifest.json"),
    JSON.stringify({ capturedFrom: CMS, screens: manifest }, null, 2) + "\n",
  );

  expect(manifest.length, "expected the sidebar to yield screens").toBeGreaterThan(10);
});
