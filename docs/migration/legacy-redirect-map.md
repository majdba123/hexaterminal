# Legacy Redirect Map

Deterministic mapping of retired legacy public URLs to their current
destinations, and the layer that owns each redirect.

> **Verified end-to-end (legacy-security closure pass)** against an isolated
> seeded database and a `npm run build && npm run start` production build:
>
> - `GET /project/1` (a mapped legacy id) on the **Next.js edge** →
>   `308 Permanent Redirect` to `/en/case-studies/{localized-slug}` in one hop;
>   the destination itself resolves `200`.
> - `GET /project/1?utm_source=test` → the query string is preserved onto the
>   redirect destination (`...?utm_source=test`).
> - `GET /project/999999` (an **unknown** legacy id, not in the Redirect table)
>   → Next's i18n middleware 307s it to the locale-prefixed path, which then
>   resolves an honest `404` — no fake redirect to the homepage, no soft-404
>   `200`.
> - The **same path requested directly against the Laravel origin**
>   (`GET http://<laravel-origin>/project/1`, `LEGACY_PUBLIC_WEB_ENABLED=false`)
>   → `404` immediately (the `legacy:public_web` gate), proving Laravel never
>   competes with the edge's redirect.
> - `sitemap.xml` contains zero occurrences of `project/1` or any redirect
>   source path — confirmed by direct inspection of the built sitemap.
>
> **Clarification:** a direct request to the Laravel origin for a legacy path is
> *expected* to fail closed (404) — it is not a bug that Laravel does not also
> redirect. The Next.js edge is the only layer end users and search engines
> reach directly; Laravel is never the public entry point for these paths.

## Design decision: redirects live at the Next.js edge

The public canonical host is the **Next.js** app. The Laravel origin serves the
API and the CMS (`/cms`), not the public site. Therefore:

- **Public URL redirects are owned by the Next.js layer** — `frontend/next.config.ts`
  `legacyRedirects()`, sourced from the DB `Redirect` table (populated by
  `php artisan hexa:migrate-legacy-content`). One hop, at the edge, to the
  correct localized destination.
- **The Laravel legacy web surface fails closed** when disabled
  (`LEGACY_PUBLIC_WEB_ENABLED=false`): a controlled 404, not a redirect. This
  avoids a second, competing redirect layer and makes redirect loops impossible
  by construction (only one layer ever redirects).

This is why the Laravel side does not itself 301 `/` → the Next home: doing so
would be a cross-origin redirect from the API host and risk double-hops with the
edge layer.

## Mapping table (edge layer)

| Legacy URL | Destination | Hop type | Notes |
|------------|-------------|----------|-------|
| `/` | `/{locale}` (Next home) | 301 | locale via Next middleware |
| `/service/{id}` | `/{locale}/services/{slug}` | 301 | per-record via Redirect table |
| `/project/{id}` | case study **or** system detail | 301 | **founder must confirm** projects→which model; do not fabricate |
| `/team/{id}` | team page/member | 302 | temporary until team page is built |
| `/projects` | `/{locale}/case-studies` (or `/systems`) | 302 | temporary until mapping confirmed |
| `/api-docs` | — | 404/410 | internal-only; no public replacement |

## Rules enforced

- Exactly one redirect hop (single layer).
- **301 (permanent)** only where the mapping is stable (service/home).
- **302 (temporary)** where the target is unconfirmed or unbuilt (team,
  projects).
- No blanket "redirect everything to the homepage" fallback — unmapped legacy
  IDs resolve honestly (the DB Redirect table only contains real mappings;
  unknown ids fall through to the localized 404).
- Redirect sources are excluded from the sitemap (they are 3xx).
- Query parameters are preserved only when safe (UTM pass-through), never for
  routes where they would leak internal ids.

## Cache invalidation

The edge redirect list is read at Next build / dev-server start from
`/api/v1/public/redirects`. Updating the `Redirect` table and rebuilding (or
revalidating) the frontend refreshes the map; the fetch fails open (no
redirects, build proceeds) if the API is unreachable, so a redirect change can
never block a deploy.

## Open founder confirmations

- `/projects` and `/project/{id}` → **case studies** or **systems**?
- `/team/{id}` → team page vs per-member route.

Until confirmed, those mappings stay temporary (302) and are not added to the
permanent Redirect table.
