import { readFileSync } from "node:fs";
import { join } from "node:path";
import { expect, test } from "@playwright/test";
import { parsePageParam } from "../lib/pagination";
import { localeAlternates } from "../lib/seo/alternates";
import { serializeJsonLd } from "../lib/seo/jsonld";
import { pageMetadata } from "../lib/seo/page-metadata";
import { SITE_URL } from "../lib/seo/site";
import { ROUTES, sitemapStaticPaths } from "../lib/routes/registry";

test("canonical metadata uses the configured production origin and bilingual alternates", () => {
  expect(SITE_URL).toBe("https://www.hexaterminal.com");

  const metadata = pageMetadata({
    locale: "ar",
    path: "/services/custom-erp",
    title: "Custom ERP",
    description: "Connected business workflows.",
  });

  expect(metadata.alternates?.canonical).toBe(
    "https://www.hexaterminal.com/ar/services/custom-erp",
  );
  expect(localeAlternates("/services/custom-erp").languages).toEqual({
    en: "https://www.hexaterminal.com/en/services/custom-erp",
    ar: "https://www.hexaterminal.com/ar/services/custom-erp",
    "x-default": "https://www.hexaterminal.com/en/services/custom-erp",
  });
  expect(metadata.openGraph?.locale).toBe("ar_SA");
});

test("noindex route policy keeps unfinished Case Studies and Insights out of the sitemap", () => {
  const sitemapPaths = new Set(sitemapStaticPaths());

  for (const id of ["case-studies", "insights", "privacy", "terms"]) {
    const route = ROUTES.find((candidate) => candidate.id === id);
    expect(route?.indexable, id).toBe(false);
    expect(sitemapPaths, id).not.toContain(route?.path);
  }
});

test("sitemap source excludes noindex Case Studies and Insights API collections", () => {
  const sitemap = readFileSync(join(process.cwd(), "app/sitemap.ts"), "utf8");

  expect(sitemap).toContain("if (!INDEXING_ENABLED) return []");
  expect(sitemap).not.toContain("getCaseStudies");
  expect(sitemap).not.toContain("getArticles");
});

test("pagination accepts only positive integer page values", () => {
  expect(parsePageParam(undefined)).toBe(1);
  expect(parsePageParam("0")).toBe(1);
  expect(parsePageParam("-2")).toBe(1);
  expect(parsePageParam("2.5")).toBe(1);
  expect(parsePageParam("2")).toBe(2);
});

test("JSON-LD serialisation remains valid JSON", () => {
  expect(() => JSON.parse(serializeJsonLd({ "@context": "https://schema.org", name: "Hexa" }))).not.toThrow();
});
