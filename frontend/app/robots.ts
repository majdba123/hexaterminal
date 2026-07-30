import type { MetadataRoute } from "next";

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000";
const ALLOW_INDEXING = process.env.NEXT_PUBLIC_ALLOW_INDEXING === "true";

/**
 * Crawler policy. See docs/seo/crawler-policy.md for the reasoning behind
 * each bot's allow/disallow decision, sourced from each vendor's own docs.
 *
 * Non-production environments (anything without NEXT_PUBLIC_ALLOW_INDEXING
 * exactly "true") always serve disallow-all, so a staging/preview deploy
 * can never get indexed by accident.
 */
export default function robots(): MetadataRoute.Robots {
  if (!ALLOW_INDEXING) {
    return {
      rules: [{ userAgent: "*", disallow: "/" }],
    };
  }

  return {
    rules: [
      {
        userAgent: "*",
        allow: "/",
        disallow: ["/api/"],
      },
      // OpenAI's search-answer crawler (distinct from GPTBot, which trains
      // models) -- allowed, since being findable in ChatGPT search is a
      // discoverability channel like any search engine.
      { userAgent: "OAI-SearchBot", allow: "/" },
      // GPTBot trains OpenAI's models on crawled content. Allowed by
      // default and called out explicitly here (rather than left to the
      // wildcard rule) so this is a deliberate, easily-reversed decision --
      // flip to `disallow: "/"` if that changes.
      { userAgent: "GPTBot", allow: "/" },
    ],
    sitemap: `${SITE_URL}/sitemap.xml`,
  };
}
