import { test, expect } from "@playwright/test";
import en from "../messages/en.json";
import ar from "../messages/ar.json";
import {
  ROUTES,
  primaryNavRoutes,
  footerRoutes,
  sitemapStaticPaths,
} from "../lib/routes/registry";

/**
 * Structural invariants for the single-source-of-truth route registry
 * (lib/routes/registry.ts). These are pure-logic assertions -- they do not
 * touch the browser -- guaranteeing navigation, footer, breadcrumbs, and the
 * sitemap stay consistent and that no content-blocked page can be advertised
 * or indexed.
 */

type Messages = Record<string, Record<string, string>>;
const enM = en as unknown as Messages;
const arM = ar as unknown as Messages;

test.describe("Route registry consistency", () => {
  test("ids and paths are unique", () => {
    const ids = ROUTES.map((r) => r.id);
    const paths = ROUTES.map((r) => r.path);
    expect(new Set(ids).size).toBe(ids.length);
    expect(new Set(paths).size).toBe(paths.length);
  });

  test("every nav key resolves in both en and ar", () => {
    for (const r of ROUTES) {
      if (!r.navKey) continue;
      expect(enM.nav?.[r.navKey], `en nav.${r.navKey}`).toBeTruthy();
      expect(arM.nav?.[r.navKey], `ar nav.${r.navKey}`).toBeTruthy();
    }
  });

  test("every breadcrumb key resolves in nav or legal in both locales", () => {
    for (const r of ROUTES) {
      if (!r.breadcrumbKey) continue;
      const key = r.breadcrumbKey;
      const enHas = Boolean(enM.nav?.[key] ?? enM.legal?.[key]);
      const arHas = Boolean(arM.nav?.[key] ?? arM.legal?.[key]);
      expect(enHas, `en breadcrumb ${key}`).toBeTruthy();
      expect(arHas, `ar breadcrumb ${key}`).toBeTruthy();
    }
  });

  test("footer groups match message footer keys", () => {
    // "legal" is a real footerGroup but footer.tsx renders it with no section
    // title (see components/site/footer.tsx) -- its links use the `legal`
    // message namespace directly, not a `footer.legal` title key. Every other
    // group renders under a `footer.<group>` title and must have one.
    for (const r of ROUTES) {
      if (!r.footerGroup || r.footerGroup === "legal") continue;
      expect(enM.footer?.[r.footerGroup], `footer.${r.footerGroup}`).toBeTruthy();
    }
  });

  test("routes shown in primary navigation are content 'current'", () => {
    for (const r of ROUTES) {
      if (r.navKey) {
        expect(r.contentState, `${r.id} advertised but not current`).toBe("current");
      }
    }
  });

  test("primary navigation matches the approved information architecture", () => {
    expect(primaryNavRoutes().map((r) => r.id)).toEqual([
      "home",
      "services",
      "systems",
      "case-studies",
      "about",
      "contact",
    ]);
  });

  test("secondary routes remain registered but outside primary navigation", () => {
    const hiddenRouteIds = ["industries", "insights", "pricing", "privacy", "terms"];
    const primaryIds = new Set(primaryNavRoutes().map((r) => r.id));

    for (const id of hiddenRouteIds) {
      const route = ROUTES.find((r) => r.id === id);
      expect(route, `${id} route must remain registered`).toBeTruthy();
      expect(primaryIds, `${id} exposed in primary navigation`).not.toContain(id);
    }
  });

  test("footer retains secondary and legal destinations without exposing insights", () => {
    expect(footerRoutes("quickLinks").map((r) => r.id)).toEqual([
      "services",
      "systems",
      "case-studies",
      "industries",
      "pricing",
    ]);
    expect(footerRoutes("legal").map((r) => r.id)).toEqual(["privacy", "terms"]);
    expect(
      (["quickLinks", "company", "legal"] as const).flatMap((group) =>
        footerRoutes(group).map((r) => r.id),
      ),
    ).not.toContain("insights");
  });

  test("unfinished routes are non-indexable and absent from the static sitemap", () => {
    const unfinishedRouteIds = ["case-studies", "insights", "privacy", "terms"];
    const sitemapPaths = new Set(sitemapStaticPaths());

    for (const id of unfinishedRouteIds) {
      const route = ROUTES.find((r) => r.id === id);
      expect(route, `${id} route must remain registered`).toBeTruthy();
      expect(route?.indexable, `${id} must be noindex`).toBe(false);
      expect(sitemapPaths, `${id} exposed in static sitemap`).not.toContain(route?.path);
    }
  });

  test("case studies are exposed in navigation while remaining noindex", () => {
    const route = ROUTES.find((r) => r.id === "case-studies");

    expect(route, "case-studies route must remain registered").toBeTruthy();
    expect(primaryNavRoutes().map((r) => r.id)).toContain("case-studies");
    expect(footerRoutes("quickLinks").map((r) => r.id)).toContain("case-studies");
    expect(route?.indexable).toBe(false);
    expect(sitemapStaticPaths()).not.toContain("/case-studies");
  });

  test("content-blocked routes are never indexable or in the sitemap", () => {
    for (const r of ROUTES) {
      if (r.contentState === "content-blocked") {
        expect(r.indexable, `${r.id} content-blocked but indexable`).toBe(false);
        expect(r.inSitemap, `${r.id} content-blocked but in sitemap`).toBe(false);
      }
    }
  });

  test("every sitemap route is also indexable", () => {
    for (const r of ROUTES) {
      if (r.inSitemap) {
        expect(r.indexable, `${r.id} in sitemap but not indexable`).toBe(true);
      }
    }
  });

  test("selectors return non-empty, coherent sets", () => {
    expect(primaryNavRoutes().length).toBeGreaterThan(0);
    expect(footerRoutes("quickLinks").length).toBeGreaterThan(0);
    expect(footerRoutes("company").length).toBeGreaterThan(0);
    // Home ("") must be present in the sitemap paths.
    expect(sitemapStaticPaths()).toContain("");
  });
});
