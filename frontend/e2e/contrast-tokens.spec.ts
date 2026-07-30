import { test, expect } from "@playwright/test";

/**
 * Design-token contrast contract.
 *
 * WHY THIS EXISTS SEPARATELY FROM e2e/accessibility.spec.ts: that suite runs
 * axe on the three pages that render without a live backend (contact, privacy,
 * terms), and none of them renders a badge, chip, pill, or eyebrow. So the
 * entire tinted-brand-text surface -- Badge, SectionHeading's badge, the
 * insights category chips, the pricing "featured" pill, the estimator result
 * eyebrow -- was invisible to CI, and shipped at ~3.2:1 in the default dark
 * theme (text-primary on bg-primary/10, both well below the 4.5:1 AA floor).
 *
 * This suite measures the TOKENS rather than pages, so it needs no backend and
 * no seeded content: it composites the same colour pairs the components use
 * and asserts the ratio. It runs against both themes because dark is the
 * default and the two palettes fail differently.
 *
 * What this does NOT prove: that a given component actually uses the pair
 * asserted here. Wiring the right token into the right component is still on
 * the author (and on axe, once the content pages are exercisable). What it does
 * prove is that no one can quietly re-lighten a token back under the bar.
 */

type Rgb = [number, number, number];

/** WCAG 2.x relative luminance. */
function luminance([r, g, b]: Rgb): number {
  const channel = (value: number) => {
    const c = value / 255;
    return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
  };
  return 0.2126 * channel(r) + 0.7152 * channel(g) + 0.0722 * channel(b);
}

function contrast(a: Rgb, b: Rgb): number {
  const [light, dark] = [luminance(a), luminance(b)].sort((x, y) => y - x);
  return (light + 0.05) / (dark + 0.05);
}

/** Flattens `color` at `alpha` over `base` -- what bg-primary/10 actually paints. */
function composite(base: Rgb, over: Rgb, alpha: number): Rgb {
  return base.map((c, i) => Math.round(c * (1 - alpha) + over[i] * alpha)) as Rgb;
}

/** Reads a resolved custom property off <html> as an [r,g,b] triple. */
async function token(
  page: import("@playwright/test").Page,
  name: string,
): Promise<Rgb> {
  const value = await page.evaluate((property) => {
    const probe = document.createElement("span");
    probe.style.color = `var(${property})`;
    document.documentElement.appendChild(probe);
    const resolved = getComputedStyle(probe).color;
    probe.remove();
    return resolved;
  }, name);

  const match = value.match(/rgba?\(([^)]+)\)/);
  expect(match, `token ${name} did not resolve to a colour (got "${value}")`).toBeTruthy();
  const [r, g, b] = match![1].split(/[,\s/]+/).map(Number);
  return [r, g, b];
}

const AA_NORMAL = 4.5;
const AA_LARGE = 3;

for (const theme of ["dark", "light"] as const) {
  test.describe(`design tokens -- ${theme} theme`, () => {
    test.beforeEach(async ({ page }) => {
      // /contact needs no backend (see accessibility.spec.ts) and inherits the
      // full token set from the root layout.
      await page.goto("/en/contact");
      await page.evaluate((next) => {
        document.documentElement.setAttribute("data-theme", next);
      }, theme);
      await expect(page.locator("html")).toHaveAttribute("data-theme", theme);
    });

    test("body text on the page background clears AA", async ({ page }) => {
      const [fg, bg] = [await token(page, "--color-foreground"), await token(page, "--color-background")];
      expect(contrast(fg, bg)).toBeGreaterThanOrEqual(AA_NORMAL);
    });

    test("muted text on the page and surface backgrounds clears AA", async ({ page }) => {
      const muted = await token(page, "--color-muted-foreground");
      for (const name of ["--color-background", "--color-surface"]) {
        expect(contrast(muted, await token(page, name)), `muted on ${name}`).toBeGreaterThanOrEqual(
          AA_NORMAL,
        );
      }
    });

    test("primary button label clears AA on its own background", async ({ page }) => {
      const [fg, bg] = [
        await token(page, "--color-primary-foreground"),
        await token(page, "--color-primary"),
      ];
      expect(contrast(fg, bg)).toBeGreaterThanOrEqual(AA_NORMAL);
    });

    test("secondary button label clears AA on its own background", async ({ page }) => {
      const [fg, bg] = [
        await token(page, "--color-secondary-foreground"),
        await token(page, "--color-secondary"),
      ];
      expect(contrast(fg, bg)).toBeGreaterThanOrEqual(AA_NORMAL);
    });

    // The regression this file was written for. --color-secondary is the
    // brand blue tuned for TEXT; --color-primary is tuned as a BACKGROUND and
    // must not be swapped back in here (see app/globals.css).
    test("badge/chip text clears AA on a primary tint over both backgrounds", async ({ page }) => {
      const text = await token(page, "--color-secondary");
      const primary = await token(page, "--color-primary");

      for (const name of ["--color-background", "--color-surface"]) {
        const tinted = composite(await token(page, name), primary, 0.1);
        expect(contrast(text, tinted), `badge text on primary/10 over ${name}`).toBeGreaterThanOrEqual(
          AA_NORMAL,
        );
      }
    });

    test("badge text clears AA on its own secondary tint", async ({ page }) => {
      const text = await token(page, "--color-secondary");
      const tinted = composite(await token(page, "--color-background"), text, 0.1);
      expect(contrast(text, tinted)).toBeGreaterThanOrEqual(AA_NORMAL);
    });

    test("inline links and link buttons clear AA on both backgrounds", async ({ page }) => {
      const link = await token(page, "--color-secondary");
      for (const name of ["--color-background", "--color-surface"]) {
        expect(contrast(link, await token(page, name)), `link on ${name}`).toBeGreaterThanOrEqual(
          AA_NORMAL,
        );
      }
    });

    // success and warning are rendered as text on a 10% tint of themselves
    // (the form success panel; the Badge success/warning variants). This pair
    // measured 3.07:1 and 3.37:1 in the light theme before the tokens were
    // darkened -- the same class of defect as the badge text above.
    test("success and warning text clear AA on their own tint", async ({ page }) => {
      for (const name of ["--color-success", "--color-warning"]) {
        const text = await token(page, name);
        for (const surface of ["--color-background", "--color-surface"]) {
          const tinted = composite(await token(page, surface), text, 0.1);
          expect(contrast(text, tinted), `${name} on its own tint over ${surface}`).toBeGreaterThanOrEqual(
            AA_NORMAL,
          );
        }
      }
    });

    // destructive is only ever used as plain text (validation errors, request
    // failures), never on a tint -- so that is what is asserted.
    test("error text clears AA on both backgrounds", async ({ page }) => {
      const text = await token(page, "--color-destructive");
      for (const name of ["--color-background", "--color-surface"]) {
        expect(contrast(text, await token(page, name)), `destructive on ${name}`).toBeGreaterThanOrEqual(
          AA_NORMAL,
        );
      }
    });

    // Documents the exemption rather than hiding it: display numerals (Metric,
    // the 404 code) are the only place --color-primary is allowed as text, and
    // only because they clear the 3:1 large-text bar. It clears 4.5:1 in the
    // light theme and does not in the dark one, which is precisely why the
    // rule is "large text only" rather than "light theme only".
    test("display numerals in primary clear the large-text bar", async ({ page }) => {
      const [primary, bg] = [
        await token(page, "--color-primary"),
        await token(page, "--color-background"),
      ];
      expect(contrast(primary, bg)).toBeGreaterThanOrEqual(AA_LARGE);
    });

    test("the focus ring is distinguishable from both backgrounds", async ({ page }) => {
      const ring = await token(page, "--color-ring");
      for (const name of ["--color-background", "--color-surface"]) {
        expect(contrast(ring, await token(page, name)), `ring on ${name}`).toBeGreaterThanOrEqual(
          AA_LARGE,
        );
      }
    });
  });
}
