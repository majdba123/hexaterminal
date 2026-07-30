import "server-only";
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

/** Server Components fetch directly through this -- no client-side waterfall. */
async function apiFetch<T>(
  path: string,
  locale: string,
  init?: { revalidate?: number; searchParams?: Record<string, string> },
): Promise<T | null> {
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
  return result?.data ?? null;
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
  return result?.data ?? null;
}

export async function getSystems(
  locale: string,
  options?: { type?: string; featured?: boolean; page?: number; perPage?: number },
) {
  const searchParams: Record<string, string> = {};
  if (options?.type) searchParams.type = options.type;
  if (options?.featured) searchParams.featured = "1";
  if (options?.page) searchParams.page = String(options.page);
  if (options?.perPage) searchParams.per_page = String(options.perPage);

  const result = await apiFetch<ApiPaginatedEnvelope<System>>(
    "/systems",
    locale,
    { searchParams },
  );
  return result ?? { data: [], meta: { current_page: 1, last_page: 1, total: 0 } };
}

export async function getSystem(locale: string, slug: string) {
  const result = await apiFetch<ApiEnvelope<System>>(`/systems/${slug}`, locale);
  return result?.data ?? null;
}

export async function getCaseStudies(
  locale: string,
  options?: { featured?: boolean; page?: number; perPage?: number },
) {
  const searchParams: Record<string, string> = {};
  if (options?.featured) searchParams.featured = "1";
  if (options?.page) searchParams.page = String(options.page);
  if (options?.perPage) searchParams.per_page = String(options.perPage);

  const result = await apiFetch<ApiPaginatedEnvelope<CaseStudy>>(
    "/case-studies",
    locale,
    { searchParams },
  );
  return result ?? { data: [], meta: { current_page: 1, last_page: 1, total: 0 } };
}

export async function getCaseStudy(locale: string, slug: string) {
  const result = await apiFetch<ApiEnvelope<CaseStudy>>(
    `/case-studies/${slug}`,
    locale,
  );
  return result?.data ?? null;
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
  return result?.data ?? { query, results: {} };
}

export async function getTeam(locale: string) {
  const result = await apiFetch<ApiEnvelope<TeamMember[]>>("/team", locale);
  return result?.data ?? [];
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
