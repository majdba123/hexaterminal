/**
 * Typed JSON-LD builders (schema.org). Every field here is either a static
 * fact about Hexa Terminal itself or comes straight from a CMS-entered
 * field -- never fabricated. Fields we don't have real data for (ratings,
 * pricing, awards) are simply omitted rather than invented.
 */

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000";
const ORG_NAME = "Hexa Terminal";
const ORG_LOGO_URL = `${SITE_URL}/logo.svg`;

type JsonLdNode = Record<string, unknown>;

/**
 * Serialise a JSON-LD payload for embedding in a `<script>` element.
 *
 * `JSON.stringify` escapes quotes, backslashes and control characters -- it
 * does NOT escape `<`. Every string in these payloads is unsanitised CMS text,
 * so a title of `Update</script><img src=x onerror=...>` would otherwise close
 * the script element early and turn the remainder into live DOM: stored XSS on
 * a public page.
 *
 * `<` is a valid JSON escape that parses back to `<`, so crawlers still
 * read the original string. Required by Next's own JSON-LD guide -- see
 * node_modules/next/dist/docs/01-app/02-guides/json-ld.md.
 *
 * This is the single chokepoint: components must not call `JSON.stringify`
 * on JSON-LD directly.
 */
export function serializeJsonLd(data: object | object[]): string {
  return JSON.stringify(data).replace(/</g, "\\u003c");
}

/** Drops undefined/null keys so optional CMS fields don't emit `"x": null`. */
function clean<T extends JsonLdNode>(node: T): T {
  return Object.fromEntries(
    Object.entries(node).filter(([, value]) => value !== undefined && value !== null),
  ) as T;
}

export function organizationJsonLd(): JsonLdNode {
  return clean({
    "@context": "https://schema.org",
    "@type": "Organization",
    name: ORG_NAME,
    url: SITE_URL,
    logo: ORG_LOGO_URL,
  });
}

export function websiteJsonLd(locale: string): JsonLdNode {
  return clean({
    "@context": "https://schema.org",
    "@type": "WebSite",
    name: ORG_NAME,
    url: `${SITE_URL}/${locale}`,
    inLanguage: locale,
  });
}

export function breadcrumbJsonLd(items: { name: string; path: string }[], locale: string): JsonLdNode {
  return {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    itemListElement: items.map((item, index) => ({
      "@type": "ListItem",
      position: index + 1,
      name: item.name,
      item: `${SITE_URL}/${locale}${item.path}`,
    })),
  };
}

export function articleJsonLd(article: {
  title: string;
  description?: string | null;
  url: string;
  image?: string | null;
  datePublished?: string | null;
  dateModified?: string | null;
  authorName?: string | null;
}): JsonLdNode {
  return clean({
    "@context": "https://schema.org",
    "@type": "BlogPosting",
    headline: article.title,
    description: article.description,
    url: article.url,
    image: article.image,
    datePublished: article.datePublished,
    dateModified: article.dateModified ?? article.datePublished,
    author: article.authorName ? { "@type": "Person", name: article.authorName } : undefined,
    publisher: {
      "@type": "Organization",
      name: ORG_NAME,
      logo: { "@type": "ImageObject", url: ORG_LOGO_URL },
    },
  });
}

export function serviceJsonLd(service: {
  name: string;
  description?: string | null;
  url: string;
}): JsonLdNode {
  return clean({
    "@context": "https://schema.org",
    "@type": "Service",
    name: service.name,
    description: service.description,
    url: service.url,
    provider: { "@type": "Organization", name: ORG_NAME, url: SITE_URL },
  });
}

/**
 * Only real, verifiable facts (name/description/URL/category). Never emits
 * aggregateRating, offers, or downloadCount -- Hexa Terminal has no real
 * data source for those.
 */
export function softwareApplicationJsonLd(system: {
  name: string;
  description?: string | null;
  url: string;
  applicationCategory?: string;
}): JsonLdNode {
  return clean({
    "@context": "https://schema.org",
    "@type": "SoftwareApplication",
    name: system.name,
    description: system.description,
    url: system.url,
    applicationCategory: system.applicationCategory ?? "BusinessApplication",
  });
}

export function personJsonLd(person: {
  name: string;
  jobTitle?: string | null;
  image?: string | null;
  sameAs?: string[];
}): JsonLdNode {
  return clean({
    "@context": "https://schema.org",
    "@type": "Person",
    name: person.name,
    jobTitle: person.jobTitle,
    image: person.image,
    worksFor: { "@type": "Organization", name: ORG_NAME },
    sameAs: person.sameAs?.length ? person.sameAs : undefined,
  });
}

export function videoObjectJsonLd(video: {
  name: string;
  description: string;
  thumbnailUrl: string;
  contentUrl: string;
}): JsonLdNode {
  return clean({
    "@context": "https://schema.org",
    "@type": "VideoObject",
    name: video.name,
    description: video.description,
    thumbnailUrl: video.thumbnailUrl,
    contentUrl: video.contentUrl,
  });
}

/** Only pass FAQs that are actually rendered visibly on the page. */
export function faqPageJsonLd(faqs: { question: string; answer: string }[]): JsonLdNode {
  return {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    mainEntity: faqs.map((faq) => ({
      "@type": "Question",
      name: faq.question,
      acceptedAnswer: { "@type": "Answer", text: faq.answer },
    })),
  };
}
