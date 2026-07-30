# SEO / AEO / GEO Architecture

How Hexa Terminal's public frontend is made discoverable by search engines
(SEO), answer engines / AI assistants (AEO), and generative engines (GEO). This
document describes **what is actually implemented**, with file references, not
aspirations.

## Principles

1. **Server-rendered, crawlable content.** Every SEO-critical page is a Server
   Component that fetches data server-side through
   [`frontend/lib/api/client.ts`](../../frontend/lib/api/client.ts) (`import
   "server-only"`). There is no client-side `useEffect` data fetching for
   primary content, so crawlers and AI fetchers see the full HTML.
2. **Truthful structured data only.** JSON-LD is built from real CMS fields or
   static facts about the company. We never emit `aggregateRating`, `offers`,
   `review`, pricing, or invented metrics — see
   [`frontend/lib/seo/jsonld.ts`](../../frontend/lib/seo/jsonld.ts), where
   optional fields are dropped (`clean()`) rather than fabricated.
3. **Fail-safe indexing.** Non-production environments are never indexed. See
   [crawler-policy.md](./crawler-policy.md).

## Metadata (SEO)

Implemented with the Next.js Metadata API.

- **Root/default metadata** —
  [`frontend/app/[locale]/layout.tsx`](../../frontend/app/[locale]/layout.tsx):
  `metadataBase`, title default + `%s — Hexa Terminal` template, description,
  Open Graph (`siteName`, `locale`, `url`), Twitter `summary_large_image`, icon
  references, and site-wide `alternates` (hreflang).
- **Per-page metadata** — every detail route exports `generateMetadata`:
  services, systems, case-studies, industries, insights. Each sets a unique
  title, description, canonical, hreflang alternates, `robots` (honoring the
  CMS `noindex` flag per entity), and Open Graph.

### Canonical URLs

Locale-aware. Each detail page sets:

```
canonical = seo.canonical_url (CMS override) ?? absoluteUrl(locale, "/<section>/<slug>")
```

via [`frontend/lib/seo/alternates.ts`](../../frontend/lib/seo/alternates.ts).
The canonical always includes the locale segment, so `/en/...` and `/ar/...`
each self-canonicalize while cross-referencing via hreflang.

### hreflang

`localeAlternates(path)` emits one `languages` entry per locale (`en`, `ar`)
plus an `x-default` pointing at the default locale (`en`). Slugs are
locale-invariant (one slug shared across locales — see
[content-model.md](../architecture/content-model.md)), which is what makes a
single path map cleanly to all language alternates.

## Structured data (JSON-LD) — AEO/GEO

Rendered by [`frontend/components/site/json-ld.tsx`](../../frontend/components/site/json-ld.tsx),
which serializes with `JSON.stringify` inside a `application/ld+json` script.
This is safe from injection because JSON.stringify output cannot contain
active markup.

| Schema type | Where | Source of truth |
|---|---|---|
| `Organization` | Root layout (once, site-wide) | Static company facts |
| `WebSite` | Root layout (once, per locale) | Static + locale |
| `BreadcrumbList` | Every detail page | Route hierarchy |
| `Service` | `/services/[slug]` | CMS service fields |
| `SoftwareApplication` | `/systems/[slug]` | CMS system fields |
| `BlogPosting` | `/insights/[slug]` | CMS article fields |
| `Person` | available (team) | CMS team fields |
| `VideoObject` | available (showreel) | Static media facts |
| `FAQPage` | available | Only visibly-rendered FAQs |

Scope discipline:

- **`Organization` and `WebSite` are emitted exactly once**, at the root layout
  level, never duplicated on child pages.
- **`SoftwareApplication`** is used only for `/systems/*`, which are real
  software products/systems — semantically justified. It never carries fake
  ratings or download counts.
- **`Service`** JSON-LD mirrors the visible service name/description/URL.
- **`FAQPage`** must only include FAQs that are actually rendered on the page
  (Google's guideline); the helper takes the same list the UI renders.

## Rendering strategy (see also deployment docs)

All content pages are statically generated at build time via
`generateStaticParams` and revalidated with ISR (`next: { revalidate: 300 }` in
the API client). This gives crawlers fast, cacheable, fully-rendered HTML while
keeping content fresh within 5 minutes of a CMS change. Route-by-route
classification is documented in
[../deployment/production-deployment.md](../deployment/production-deployment.md).

## Sitemap

[`frontend/app/sitemap.ts`](../../frontend/app/sitemap.ts) is a Next.js Metadata
Route emitted at `/sitemap.xml`. It enumerates:

- Static routes (home, section hubs, about, contact, start-a-project).
- Every content slug (services, systems, case-studies, industries, insights),
  paginated through the API so nothing is missed.

Each entry carries `lastModified` from the entity's real `updated_at` (or an
article's `updated_content_at ?? published_at`) and `alternates.languages` for
hreflang. Slugs are locale-invariant, so one locale's fetch builds every URL's
language alternates.

## Redirects (link equity preservation)

Legacy URLs from the old Laravel site (`/project/{id}`, `/service/{id}`,
`/team/{id}`, `/projects`) are preserved as **301s** so indexed/bookmarked links
and their link equity survive the migration.

- Backend: `Redirect` model + `active()` scope →
  [`RedirectController`](../../app/Http/Controllers/Api/V1/Public/RedirectController.php)
  serves the locale-agnostic map at `/api/v1/public/redirects`.
- Frontend: [`frontend/next.config.ts`](../../frontend/next.config.ts) fetches
  that map at build time and returns it from `redirects()`. `status_code === 301`
  → `permanent: true`. It **fails safe**: if the API is unreachable the build
  proceeds with no redirects rather than breaking.

## What we deliberately do NOT do (anti-patterns avoided)

- No fabricated ratings, reviews, pricing, awards, or business metrics in
  structured data.
- No cloaking or serving different content to crawlers vs. users.
- No keyword-stuffed hidden text or doorway pages.
- No indexing of staging/preview environments.
- No fake "AI-optimized" content hacks — AEO/GEO here means clean semantic HTML,
  honest structured data, and being crawlable, nothing more.
