const PRODUCTION_SITE_URL = "https://www.hexaterminal.com";

function normalizeSiteUrl(value: string): string {
  try {
    return new URL(value).origin;
  } catch {
    return PRODUCTION_SITE_URL;
  }
}

const configuredSiteUrl = normalizeSiteUrl(
  process.env.NEXT_PUBLIC_SITE_URL ?? PRODUCTION_SITE_URL,
);

/**
 * Public-origin authority for canonicals, metadata, feeds, and crawler
 * directives. SEO artifacts must never inherit a local, preview, HTTP, or
 * non-www origin from a misconfigured public environment variable.
 */
export const SITE_URL = configuredSiteUrl === PRODUCTION_SITE_URL
  ? configuredSiteUrl
  : PRODUCTION_SITE_URL;
