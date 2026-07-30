import { getArticles } from "@/lib/api/client";
import { routing } from "@/i18n/routing";

/**
 * Locale-agnostic file path (rss.xml sits outside [locale], like
 * sitemap.xml/robots.txt) but locale-aware content: `?locale=ar` for the
 * Arabic feed, defaults to `en`. Lists the most recent published articles.
 * Not indexable itself -- it's a feed, not a page -- but harmless either way
 * since it carries no HTML/robots meta.
 */
const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000";

function escapeXml(value: string): string {
  return value
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&apos;");
}

export async function GET(request: Request) {
  const { searchParams } = new URL(request.url);
  const requestedLocale = searchParams.get("locale") ?? routing.defaultLocale;
  const locale = routing.locales.includes(requestedLocale as (typeof routing.locales)[number])
    ? requestedLocale
    : routing.defaultLocale;

  const { data: articles } = await getArticles(locale, 1, 30);

  const items = articles
    .map((article) => {
      const url = `${SITE_URL}/${locale}/insights/${article.slug}`;
      const pubDate = article.published_at ? new Date(article.published_at).toUTCString() : undefined;

      return `<item>
  <title>${escapeXml(article.title)}</title>
  <link>${escapeXml(url)}</link>
  <guid isPermaLink="true">${escapeXml(url)}</guid>
  ${pubDate ? `<pubDate>${pubDate}</pubDate>` : ""}
  ${article.excerpt ? `<description>${escapeXml(article.excerpt)}</description>` : ""}
</item>`;
    })
    .join("\n");

  const xml = `<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
<channel>
  <title>Hexa Terminal — Insights</title>
  <link>${SITE_URL}/${locale}/insights</link>
  <description>Notes on building and running production software.</description>
  <language>${locale}</language>
  ${items}
</channel>
</rss>`;

  return new Response(xml, {
    headers: {
      "Content-Type": "application/rss+xml; charset=utf-8",
      "Cache-Control": "public, max-age=300",
    },
  });
}
