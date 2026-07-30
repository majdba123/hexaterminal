import { test, expect } from "@playwright/test";
import fs from "node:fs";
import path from "node:path";

/**
 * Turns the HTML dumped by tests/Feature/Cms/CaptureCmsHtmlTest.php into PNGs.
 *
 *   1. CAPTURE_CMS=1 php artisan test --filter=CaptureCmsHtmlTest
 *   2. npx playwright test e2e/tools/screenshot-cms-html.ts \
 *        --config=e2e/tools/capture-cms.config.ts
 *
 * The Laravel app must be running at CAPTURE_CMS_URL (default
 * http://127.0.0.1:8010).
 *
 * The saved HTML is served BACK from that origin through a route interception
 * rather than opened as a file://. That matters: Filament's markup is full of
 * root-relative URLs (`/livewire/livewire.js`, `/js/filament/...`,
 * `/fonts/filament/...`), and from a file:// page those resolve against the
 * local disk and 404. Losing Alpine to that is not a cosmetic problem -- the
 * panel's main content is `x-cloak`-hidden until Alpine boots, so the first
 * version of this script produced 38 screenshots of a sidebar next to an empty
 * white page.
 *
 * Livewire's own update endpoint is blocked. It cannot work against static HTML
 * (no component snapshot on the server), and letting it retry only adds delay
 * and console noise. Everything server-rendered is already in the markup.
 */

const HTML_DIR = path.resolve(__dirname, "../../../docs/cms-screens/html");
const OUT = path.resolve(__dirname, "../../../docs/cms-screens");
const ORIGIN = (process.env.CAPTURE_CMS_URL ?? "http://127.0.0.1:8010").replace(/\/$/, "");
const VIRTUAL = `${ORIGIN}/__cms-capture__`;

test("screenshot every captured CMS screen", async ({ page }) => {
  expect(
    fs.existsSync(HTML_DIR),
    `No captured HTML at ${HTML_DIR} -- run the CAPTURE_CMS=1 artisan step first`,
  ).toBe(true);

  const files = fs.readdirSync(HTML_DIR).filter((f) => f.endsWith(".html")).sort();
  expect(files.length, "expected captured HTML files").toBeGreaterThan(30);

  let html = "";
  await page.route(VIRTUAL, (route) =>
    route.fulfill({ status: 200, contentType: "text/html; charset=utf-8", body: html }),
  );
  await page.route("**/livewire/update", (route) => route.abort());

  const missingAssets = new Set<string>();
  page.on("requestfailed", (r) => {
    if (/\.(css|js|woff2?)(\?|$)/.test(r.url())) missingAssets.add(r.url());
  });

  const done: string[] = [];

  for (const file of files) {
    html = fs.readFileSync(path.join(HTML_DIR, file), "utf8");

    // `load`, not domcontentloaded: with domcontentloaded the stylesheet was
    // not always applied by screenshot time and the body came out blank even
    // though the DOM reported it visible. Waiting for subresources is slower
    // but it is the difference between a usable capture and an empty one.
    await page.goto(VIRTUAL, { waitUntil: "load", timeout: 45_000 });
    // NOTE: page BODIES do not render here -- see the header comment. Only
    // the panel chrome is faithful.
    // fonts.ready can hang if a face never resolves, so cap it rather than
    // letting one bad font stall the whole capture.
    await page
      .evaluate(
        () =>
          Promise.race([
            document.fonts.ready.then(() => true),
            new Promise((r) => setTimeout(() => r(false), 3000)),
          ]),
      )
      .catch(() => false);
    await page.waitForTimeout(1200);

    const png = file.replace(/\.html$/, ".png");
    await page.screenshot({ path: path.join(OUT, png), fullPage: true });
    done.push(png);
  }

  fs.writeFileSync(
    path.join(OUT, "manifest.json"),
    JSON.stringify(
      {
        source: "tests/Feature/Cms/CaptureCmsHtmlTest.php",
        assetsFrom: ORIGIN,
        count: done.length,
        screens: done,
      },
      null,
      2,
    ) + "\n",
  );

  expect(done.length).toBe(files.length);
  expect(
    [...missingAssets],
    "CSS/JS/font that failed to load -- screenshots would be unstyled or cloaked",
  ).toEqual([]);
});
