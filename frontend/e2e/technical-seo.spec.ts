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

test("routing consolidates locale-less and bare-host URLs onto the canonical origin", () => {
  const config = readFileSync(join(process.cwd(), "next.config.ts"), "utf8");
  const productionEnv = readFileSync(join(process.cwd(), ".env.production"), "utf8");

  expect(productionEnv).toContain(
    "NEXT_PUBLIC_SITE_URL=https://www.hexaterminal.com",
  );
  expect(config).toContain('source: "/"');
  expect(config).toContain('destination: `${SITE_URL}/${routing.defaultLocale}`');
  expect(config).toContain('type: "host", value: "hexaterminal.com"');
  expect(config).toContain('destination: `${SITE_URL}/:path*`');
});

test("case studies are indexable while unfinished routes remain out of the sitemap", () => {
  const sitemapPaths = new Set(sitemapStaticPaths());
  const caseStudies = ROUTES.find((candidate) => candidate.id === "case-studies");

  expect(caseStudies?.indexable).toBe(true);
  expect(sitemapPaths).toContain("/case-studies");

  for (const id of ["insights", "privacy", "terms"]) {
    const route = ROUTES.find((candidate) => candidate.id === id);
    expect(route?.indexable, id).toBe(false);
    expect(sitemapPaths, id).not.toContain(route?.path);
  }
});

test("sitemap includes curated Case Studies but still excludes Insights", () => {
  const sitemap = readFileSync(join(process.cwd(), "app/sitemap.ts"), "utf8");

  expect(sitemap).toContain("if (!INDEXING_ENABLED) return []");
  expect(sitemap).toContain("getCaseStudies");
  expect(sitemap).toContain("/case-studies/${slugSegment(c.slug)}");
  expect(sitemap).not.toContain("getArticles");
});

test("case study metadata follows environment and CMS noindex controls", () => {
  const listing = readFileSync(
    join(process.cwd(), "app/[locale]/case-studies/(list)/page.tsx"),
    "utf8",
  );
  const detail = readFileSync(
    join(process.cwd(), "app/[locale]/case-studies/[slug]/page.tsx"),
    "utf8",
  );

  expect(listing).toContain("robots: resolveRobots(),");
  expect(detail).toContain("robots: resolveRobots(caseStudy.seo?.noindex),");
  expect(detail).not.toContain("robots: resolveRobots(true),");
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
