# XML Sitemap Policy

Implemented by `frontend/app/sitemap.ts`. One flat sitemap — no sitemap index —
because current URL volume (a handful of static routes plus CMS records) does
not justify segmentation. Do not add a sitemap index for appearance.

## Inclusion rules

A URL appears in the sitemap **only** if all hold:

1. **Static routes:** the route is flagged `inSitemap: true` in
   `frontend/lib/routes/registry.ts`. The registry's own invariant
   (`e2e/route-registry.spec.ts`) guarantees `inSitemap ⇒ indexable`, and that
   `content-blocked` routes are never included.
2. **Dynamic records:** returned by the public list API
   (`/api/v1/public/{services,systems,case-studies,industries,articles}`), which
   serves **published-only** records. Draft/unapproved/private records never
   reach the sitemap because they never reach the API.

## Exclusion rules (must stay excluded)

- Per-user estimate results `/estimate/{uuid}` (noindex, per-user).
- `/search` (utility, no unique indexable content).
- `/privacy`, `/terms`, and all trust routes while `content-blocked`.
- Preview URLs, `/api/*`, `/cms/*`, legacy Blade/admin routes.
- Redirect sources (they are 3xx, never canonical destinations).

## Timestamps

`lastModified` uses the most meaningful editorial timestamp available per record:

- Articles: `updated_content_at ?? published_at`.
- Other collections: `updated_at`.

**Follow-up:** prefer `content_updated_at` / `approved_at` / `published_at` over
raw DB `updated_at` for services/systems/case-studies/industries once those
columns are surfaced by the API, so a non-editorial touch (re-save, backfill)
does not bump `lastmod`.

## Removal on unpublish

Because the sitemap is built at request/revalidation time from the live API, an
unpublished record disappears automatically on the next revalidation. This
should be covered by a test asserting an unpublished record is absent
(currently **not** asserted — see limitations).

## Media (image/video) sitemap — DEFERRED

Decision: **defer**. Image/video sitemap extensions require complete, stable,
absolute public media URLs with title/description/thumbnail metadata. Current
media is a mix of `public/`, Laravel storage, and legacy placeholder/Drive
links (see `frontend/next.config.ts` remote patterns and
`docs/infrastructure/media-cdn-strategy.md`). Adding media entries now would
emit invalid or placeholder URLs. Revisit once CMS-managed media on a CDN with
stable URLs exists. Do **not** split the sitemap merely to support this.
