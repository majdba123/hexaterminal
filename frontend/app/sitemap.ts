import type { MetadataRoute } from "next";
import { routing } from "@/i18n/routing";
import { routeByPath, sitemapStaticPaths } from "@/lib/routes/registry";
import {
  getServices,
  getSystems,
  getCaseStudies,
  getIndustries,
  getArticles,
} from "@/lib/api/client";

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000";

type Entry = { path: string; lastModified?: string | null };

/** Content list slugs are locale-invariant (EN-based, unique), so one
 * locale's fetch is enough to build every URL's language alternates. */
async function collectAllPages<T>(
  fetchPage: (page: number) => Promise<{ data: T[]; meta: { current_page: number; last_page: number } }>,
): Promise<T[]> {
  const first = await fetchPage(1);
  let items = first.data;
  for (let page = 2; page <= first.meta.last_page; page++) {
    const next = await fetchPage(page);
    items = items.concat(next.data);
  }
  return items;
}

/**
 * Percent-encode a slug before it is interpolated into a sitemap URL.
 *
 * Next builds sitemap XML by bare string concatenation -- `content +=
 * \`<loc>${item.url}</loc>\`` -- with no escaping of its own (see
 * node_modules/next/dist/build/webpack/loaders/metadata/resolve-route-data.js).
 * A slug containing `</loc></url><url><loc>...` would therefore inject
 * attacker-chosen entries into a sitemap served from the verified production
 * domain, or malform the document and void it entirely.
 *
 * `App\Filament\Support\Slugs` validates the format on write, but that cannot
 * retroactively clean slugs already in the database (legacy-migrated content),
 * so the output side is escaped too. For a well-formed slug this is a no-op:
 * `encodeURIComponent` leaves lowercase alphanumerics and `-` untouched.
 */
function slugSegment(slug: string): string {
  return encodeURIComponent(slug);
}

/**
 * Most recent of a set of timestamps, or undefined if none are usable.
 *
 * Parsed rather than string-compared: these come from the API as ISO 8601 but
 * a mixed offset would make lexicographic ordering silently wrong.
 */
function mostRecent(dates: (string | null | undefined)[]): string | undefined {
  let best: number | undefined;
  let bestRaw: string | undefined;

  for (const date of dates) {
    if (!date) continue;
    const parsed = Date.parse(date);
    if (Number.isNaN(parsed)) continue;
    if (best === undefined || parsed > best) {
      best = parsed;
      bestRaw = date;
    }
  }

  return bestRaw;
}

function localizedEntry(path: string, lastModified?: string | null): MetadataRoute.Sitemap[number] {
  const languages = Object.fromEntries(
    routing.locales.map((locale) => [locale, `${SITE_URL}/${locale}${path}`]),
  );

  return {
    url: `${SITE_URL}/${routing.defaultLocale}${path}`,
    lastModified: lastModified ?? undefined,
    alternates: {
      languages: {
        ...languages,
        // Must match what the HTML emits (lib/seo/alternates.ts). Without it
        // the same URL advertised two different alternate sets depending on
        // whether a crawler read the page or the sitemap. Next writes these
        // keys straight into `hreflang`, so "x-default" passes through as-is.
        "x-default": `${SITE_URL}/${routing.defaultLocale}${path}`,
      },
    },
  };
}

export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const locale = routing.defaultLocale;

  // NOTE: individual /estimate/{uuid} result pages are per-user and noindex --
  // they are deliberately never added to the sitemap (registry: no `estimate`
  // detail route exists, and content-blocked/utility routes set inSitemap:false).

  const [services, systems, caseStudies, industries, articles] = await Promise.all([
    collectAllPages((page) => getServices(locale, page, 50)),
    collectAllPages((page) => getSystems(locale, { page, perPage: 50 })),
    collectAllPages((page) => getCaseStudies(locale, { page, perPage: 50 })),
    getIndustries(locale),
    collectAllPages((page) => getArticles(locale, page, 50)),
  ]);

  // A record's own `seo.noindex` override must exclude it from the sitemap
  // -- otherwise a published-but-noindexed page still gets submitted to
  // search engines, which is exactly the contradiction
  // `php artisan hexa:seo-audit` flags as `noindex_in_sitemap`.
  const notNoindexed = <T extends { seo: { noindex: boolean } | null }>(items: T[]) =>
    items.filter((item) => !item.seo?.noindex);

  // A listing page changes exactly when the newest record it lists changes, so
  // these are real observed dates -- not a build timestamp. That distinction
  // matters: Google ignores `lastmod` wholesale once it looks unreliable, and
  // stamping every page with the deploy time is the classic way to trigger
  // that. Routes with no content-derived signal (/about, /contact,
  // /start-a-project, /estimate, /pricing -- the API exposes no `updated_at`
  // for team or pricing records) deliberately emit NO `lastmod` rather than a
  // fabricated one.
  const listLastModified: Record<string, string | undefined> = {
    "/services": mostRecent(services.map((s) => s.updated_at)),
    "/systems": mostRecent(systems.map((s) => s.updated_at)),
    "/case-studies": mostRecent(caseStudies.map((c) => c.updated_at)),
    "/industries": mostRecent(industries.map((i) => i.updated_at)),
    "/insights": mostRecent(articles.map((a) => a.updated_content_at ?? a.published_at)),
  };

  // The home page surfaces every content type, so it is as fresh as the
  // freshest thing on it.
  listLastModified[""] = mostRecent(Object.values(listLastModified));

  // Static entries come from the single-source-of-truth route registry
  // (lib/routes/registry.ts): exactly the routes flagged `inSitemap`, which the
  // registry's own invariants guarantee are also `indexable`. This keeps the
  // sitemap, navigation, and footer from drifting apart.
  const staticEntries: Entry[] = sitemapStaticPaths().map((path) => ({
    path,
    lastModified: listLastModified[path],
  }));

  const dynamicEntries: Entry[] = [
    ...notNoindexed(services).map((s) => ({ path: `/services/${slugSegment(s.slug)}`, lastModified: s.updated_at })),
    ...notNoindexed(systems).map((s) => ({ path: `/systems/${slugSegment(s.slug)}`, lastModified: s.updated_at })),
    ...(routeByPath("/case-studies")?.indexable
      ? notNoindexed(caseStudies).map((c) => ({
          path: `/case-studies/${slugSegment(c.slug)}`,
          lastModified: c.updated_at,
        }))
      : []),
    ...notNoindexed(industries).map((i) => ({ path: `/industries/${slugSegment(i.slug)}`, lastModified: i.updated_at })),
    ...(routeByPath("/insights")?.indexable
      ? notNoindexed(articles).map((a) => ({
          path: `/insights/${slugSegment(a.slug)}`,
          lastModified: a.updated_content_at ?? a.published_at,
        }))
      : []),
  ];

  return [...staticEntries, ...dynamicEntries].map((entry) =>
    localizedEntry(entry.path, entry.lastModified),
  );
}
