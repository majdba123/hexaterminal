import type { Metadata } from "next";

/**
 * Single source of truth for whether this deployment may be indexed.
 *
 * Fail-safe by design: indexing is enabled ONLY when NEXT_PUBLIC_ALLOW_INDEXING
 * is exactly the string "true". Any other value -- and critically, an *absent*
 * variable -- resolves to `false`. A staging or preview environment that
 * forgets to set the flag therefore defaults to noindex and can never be
 * accidentally exposed to crawlers.
 *
 * This mirrors the same guard in app/robots.ts and app/api/health/route.ts;
 * they must stay in agreement.
 */
export const INDEXING_ENABLED = process.env.NEXT_PUBLIC_ALLOW_INDEXING === "true";

const NOINDEX: Metadata["robots"] = {
  index: false,
  follow: false,
  googleBot: { index: false, follow: false },
};

/**
 * Resolve the `robots` metadata for a page.
 *
 * - Non-indexable environment (staging/preview) -> always noindex, nofollow,
 *   regardless of the content's own SEO settings.
 * - Indexable environment -> honour the CMS per-entry `seo.noindex` flag,
 *   otherwise inherit the site default (return `undefined`).
 *
 * Passing `undefined` lets Next.js fall back to the parent layout's robots
 * metadata, which itself is noindex in a non-indexable environment.
 */
export function resolveRobots(seoNoindex?: boolean | null): Metadata["robots"] {
  if (!INDEXING_ENABLED) return NOINDEX;
  return seoNoindex ? NOINDEX : undefined;
}
