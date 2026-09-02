import "server-only";
import { connection } from "next/server";
import type {
  ApiEnvelope,
  ApiPaginatedEnvelope,
  Article,
  ArticleCategory,
  CaseStudy,
  CompanySettings,
  CostEstimateResult,
  Currency,
  EstimatorConfig,
  Faq,
  HomePayload,
  Industry,
  PricingPayload,
  SearchResults,
  Service,
  System,
  TeamMember,
  Testimonial,
  TrustPage,
} from "./types";

const API_URL = process.env.API_URL ?? "http://localhost:8000/api/v1/public";

/**
 * Temporary public portfolio curation.
 *
 * The CMS keeps the full project inventory so hidden work can be restored once
 * its approved screenshots are available. The public website, however, must
 * only expose projects whose visual proof is ready. Keeping this boundary in
 * the server-only API client makes Home, Systems, Case Studies, Search and the
 * sitemap agree without destructively deleting CMS records.
 */
const CURATED_SYSTEM_SLUGS = new Set([
  "rakez-erp",
  "dhura",
  "matjrii",
  "prospectiq",
]);

function isCuratedSystemSlug(slug: string): boolean {
  return CURATED_SYSTEM_SLUGS.has(slug);
}

function isCuratedPortfolioSlug(slug: string): boolean {
  return [...CURATED_SYSTEM_SLUGS].some(
    (systemSlug) => slug === systemSlug || slug.startsWith(`${systemSlug}-`),
  );
}

function isCuratedCaseStudy(caseStudy: CaseStudy): boolean {
  return Boolean(
    (caseStudy.system && isCuratedSystemSlug(caseStudy.system.slug)) ||
      isCuratedPortfolioSlug(caseStudy.slug),
  );
}

function singlePage<T>(data: T[]): ApiPaginatedEnvelope<T> {
  return {
    data,
    meta: {
      current_page: 1,
      last_page: 1,
      total: data.length,
    },
  };
}

/** Server Components fetch directly through this -- no client-side waterfall. */
async function apiFetch<T>(
  path: string,
  locale: string,
  init?: { revalidate?: number; searchParams?: Record<string, string> },
): Promise<T | null> {
  // CMS content belongs to the runtime request. This keeps a production build
  // independent of API availability while preserving the explicit ISR policy
  // on the fetch below once a visitor requests the page.
  await connection();

  const url = new URL(`${API_URL}${path}`);
  url.searchParams.set("locale", locale);
  for (const [key, value] of Object.entries(init?.searchParams ?? {})) {
    url.searchParams.set(key, value);
  }

  const res = await fetch(url, {
    next: { revalidate: init?.revalidate ?? 300 },
  });

  if (res.status === 404) return null;
  if (!res.ok) {
    throw new Error(`API request failed: ${res.status} ${url.toString()}`);
  }

  return res.json() as Promise<T>;
}

export async function getHome(locale: string) {
  const result = await apiFetch<ApiEnvelope<HomePayload>>("/home", locale);
  if (!result?.data) return null;

  const featuredSystems = result.data.featured_systems.filter((system) =>
    isCuratedSystemSlug(system.slug),
  );
  const featuredCaseStudies = result.data.featured_case_studies.filter(isCuratedCaseStudy);

  return {
    ...result.data,
    featured_systems: featuredSystems,
    featured_case_studies: featuredCaseStudies,
    stats: {
      ...result.data.stats,
      systems: featuredSystems.length,
      case_studies: featuredCaseStudies.length,
    },
  };
}

export async function getServices(locale: string, page = 1, perPage = 20) {
  const result = await apiFetch<ApiPaginatedEnvelope<Service>>(
    "/services",
    locale,
    { searchParams: { page: String(page), per_page: String(perPage) } },
  );
  return result ?? { data: [], meta: { current_page: 1, last_page: 1, total: 0 } };
}

export async function getService(locale: string, slug: string) {
  const result = await apiFetch<ApiEnvelope<Service>>(
    `/services/${slug}`,
    locale,
  );
  const service = result?.data ?? null;
  if (!service) return null;

  return {
    ...service,
    related_case_studies: service.related_case_studies?.filter((caseStudy) =>
      isCuratedPortfolioSlug(caseStudy.slug),
    ),
  };
}

export async function getSystems(
  locale: string,
  options?: { type?: string; featured?: boolean; page?: number; perPage?: number },
) {
  const searchParams: Record<string, string> = { page: "1", per_page: "50" };
  if (options?.type) searchParams.type = options.type;
  if (options?.featured) searchParams.featured = "1";

  const result = await apiFetch<ApiPaginatedEnvelope<System>>(
    "/systems",
    locale,
    { searchParams },
  );
  if (!result) return singlePage<System>([]);

  return singlePage(result.data.filter((system) => isCuratedSystemSlug(system.slug)));
}

export async function getSystem(locale: string, slug: string) {
  if (!isCuratedSystemSlug(slug)) return null;

  const result = await apiFetch<ApiEnvelope<System>>(`/systems/${slug}`, locale);
  return result?.data ?? null;
}

export async function getCaseStudies(
  locale: string,
  options?: { featured?: boolean; page?: number; perPage?: number },
) {
  const searchParams: Record<string, string> = { page: "1", per_page: "50" };
  if (options?.featured) searchParams.featured = "1";

  const result = await apiFetch<ApiPaginatedEnvelope<CaseStudy>>(
    "/case-studies",
    locale,
    { searchParams },
  );
  if (!result) return singlePage<CaseStudy>([]);

  return singlePage(result.data.filter(isCuratedCaseStudy));
}

export async function getCaseStudy(locale: string, slug: string) {
  const result = await apiFetch<ApiEnvelope<CaseStudy>>(
    `/case-studies/${slug}`,
    locale,
  );
  const caseStudy = result?.data ?? null;
  return caseStudy && isCuratedCaseStudy(caseStudy) ? caseStudy : null;
}

export async function getIndustries(locale: string) {
  const result = await apiFetch<ApiEnvelope<Industry[]>>("/industries", locale);
  return result?.data ?? [];
}

export async function getIndustry(locale: string, slug: string) {
  const result = await apiFetch<ApiEnvelope<Industry>>(
    `/industries/${slug}`,
    locale,
  );
  return result?.data ?? null;
}

export async function getArticles(
  locale: string,
  page = 1,
  perPage = 12,
  options?: { category?: string; tag?: string; featured?: boolean },
) {
  const searchParams: Record<string, string> = { page: String(page), per_page: String(perPage) };
  if (options?.category) searchParams.category = options.category;
  if (options?.tag) searchParams.tag = options.tag;
  if (options?.featured) searchParams.featured = "1";

  const result = await apiFetch<ApiPaginatedEnvelope<Article>>("/articles", locale, { searchParams });
  return result ?? { data: [], meta: { current_page: 1, last_page: 1, total: 0 } };
}

export async function getArticle(locale: string, slug: string) {
  const result = await apiFetch<ApiEnvelope<Article>>(`/articles/${slug}`, locale);
  return result?.data ?? null;
}

export async function getArticleCategories(locale: string) {
  const result = await apiFetch<ApiEnvelope<ArticleCategory[]>>("/article-categories", locale);
  return result?.data ?? [];
}

export async function getCompanySettings(locale: string) {
  const result = await apiFetch<ApiEnvelope<CompanySettings>>("/settings", locale);
  return result?.data ?? null;
}

/** Not cached (revalidate: 0) -- search results must always be fresh. */
export async function search(locale: string, query: string) {
  const result = await apiFetch<ApiEnvelope<SearchResults>>("/search", locale, {
    searchParams: { q: query },
    revalidate: 0,
  });
  const data = result?.data ?? { query, results: {} };

  return {
    ...data,
    results: {
      ...data.results,
      systems: data.results.systems?.filter((hit) => isCuratedSystemSlug(hit.slug)),
      case_studies: data.results.case_studies?.filter((hit) =>
        isCuratedPortfolioSlug(hit.slug),
      ),
    },
  };
}

export async function getTeam(locale: string) {
  const result = await apiFetch<ApiEnvelope<TeamMember[]>>("/team", locale);
  return result?.data ?? [];
}

export async function getTeamMember(locale: string, slug: string) {
  const result = await apiFetch<ApiEnvelope<TeamMember>>(`/team/${slug}`, locale);
  return result?.data ?? null;
}

export async function getTrustPages(locale: string) {
  const result = await apiFetch<ApiEnvelope<TrustPage[]>>(
    "/trust-pages",
    locale,
  );
  return result?.data ?? [];
}

export async function getTrustPage(locale: string, slug: string) {
  const result = await apiFetch<ApiEnvelope<TrustPage>>(
    `/trust-pages/${slug}`,
    locale,
  );
  return result?.data ?? null;
}

export interface PreviewPayload<T = unknown> {
  type: string;
  record: T;
  preview: { locale: string; expires_at: string };
}

/**
 * Fetches a secure CMS preview by token. Never cached (a preview must
 * always reflect the record's current draft state), and a 404 means the
 * token is invalid, expired, or revoked -- indistinguishable from each
 * other by design (see App\Http\Controllers\Api\V1\Public\PreviewController).
 */
export async function getPreview(token: string) {
  const url = new URL(`${API_URL}/preview/${token}`);
  const res = await fetch(url, { cache: "no-store" });
  if (res.status === 404) return null;
  if (!res.ok) {
    throw new Error(`Preview request failed: ${res.status}`);
  }
  const body = (await res.json()) as { data: PreviewPayload };
  return body.data;
}

export async function getTestimonials(locale: string, featured = false) {
  const result = await apiFetch<ApiEnvelope<Testimonial[]>>(
    "/testimonials",
    locale,
    { searchParams: featured ? { featured: "1" } : {} },
  );
  return result?.data ?? [];
}

export async function getFaqs(locale: string) {
  const result = await apiFetch<ApiEnvelope<Faq[]>>("/faqs", locale);
  return result?.data ?? [];
}

export async function getPricing(locale: string, currency: Currency = "USD") {
  const result = await apiFetch<ApiEnvelope<PricingPayload>>("/pricing", locale, {
    searchParams: { currency },
  });
  return (
    result?.data ?? {
      engagement_models: [],
      faqs: [],
      estimator_available: false,
      currency,
      currencies: ["USD", "AED", "SAR"] as Currency[],
    }
  );
}

export async function getEstimatorConfig(locale: string) {
  const result = await apiFetch<ApiEnvelope<EstimatorConfig>>("/estimator", locale);
  return result?.data ?? { available: false };
}

/**
 * Result retrieval for the shareable /estimate/{uuid} page (server-rendered).
 * Direct fetch (not apiFetch) so a 404 (unknown) or 410 (expired) both
 * resolve to null for the not-found UI instead of throwing.
 */
export async function getEstimate(locale: string, uuid: string): Promise<CostEstimateResult | null> {
  const url = new URL(`${API_URL}/estimates/${encodeURIComponent(uuid)}`);
  url.searchParams.set("locale", locale);
  try {
    const res = await fetch(url, { cache: "no-store" });
    if (!res.ok) return null;
    const body = (await res.json()) as ApiEnvelope<CostEstimateResult>;
    return body.data ?? null;
  } catch {
    return null;
  }
}
