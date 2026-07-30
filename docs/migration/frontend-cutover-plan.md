# Frontend Cutover Plan — Legacy Blade → Next.js

**Status: PLANNED, NOT EXECUTED.** This documents the future, explicitly-
approved switch of the public site from the legacy Laravel Blade frontend to the
Next.js app. **Nothing here has been run.** The legacy Blade frontend and legacy
admin **remain live and unmodified** until sign-off. No production DNS has been
changed; no routes have been retired.

## 1. What changes and what does not

| Surface | Today (legacy) | After cutover | Retired? |
|---|---|---|---|
| Public pages (`/`, `/projects`, `/project/{id}`, `/service/{id}`, `/team/{id}`) | Blade via `WebsiteController` (`routes/web.php`) | Next.js localized routes (`/en/...`, `/ar/...`) | Legacy paths **redirect**, not deleted |
| Legacy admin (`/admin/*`) | Blade `AdminController` | unchanged | **No** — kept as fallback |
| CMS (`/cms`) | Filament | unchanged | No |
| Public API (`/api/v1/public/*`) | Laravel | unchanged | No |
| Legacy API (`/api/*`) | Laravel | unchanged | No |
| Media (`/storage/*`) | Laravel public disk | unchanged | No |

The cutover is a **routing change at the edge**, not a code deletion. The legacy
Blade controllers stay in the codebase as an instant rollback target.

## 2. Route ownership

**Before:** one origin; Laravel owns everything.

**After (single-origin target):** the reverse proxy (see
`deploy/staging/nginx/frontend-staging.conf`, cutover blocks) splits by path:

- `/api/*`, `/cms`, `/storage/*`, `/livewire/*`, Filament assets → **Laravel**
- everything else → **Next.js** (`next start`)

`/admin/*` continues to resolve to Laravel (kept as a fallback admin surface).

## 3. Legacy URL redirects

The legacy→new URL map already exists: `php artisan hexa:migrate-legacy-content`
populates the `Redirect` table from the old `web.php` routes; the frontend loads
it at build time via `GET /api/v1/public/redirects` in `next.config.ts`
(`legacyRedirects()`), emitting `308`/`301` redirects. Verified today: `/project/1`
→ permanent redirect into the migrated case study.

At cutover, confirm every legacy public path (`/project/{id}`, `/service/{id}`,
`/team/{id}`, `/projects`, `/`) either renders in Next.js or 308-redirects to its
new home — no legacy public URL should dead-end.

## 4. Reverse-proxy / API / CMS routing

Use the dormant location blocks in `frontend-staging.conf`:

```nginx
location /cms      { proxy_pass https://laravel_upstream; }
location /api      { proxy_pass https://laravel_upstream; }
location /storage  { proxy_pass https://laravel_upstream; }
location /         { proxy_pass http://nextjs_upstream; }
```

- Preserve `Host` + `X-Forwarded-*` so Laravel builds correct absolute URLs and
  sees the real client IP (`TRUSTED_PROXIES`).
- Keep the CMS un-framable (`Content-Security-Policy: frame-ancestors 'self'`).
- **Remove the staging `X-Robots-Tag: noindex` header** and set
  `NEXT_PUBLIC_ALLOW_INDEXING=true` for the production build so the site becomes
  indexable (this is the single biggest "did we flip it" checklist item).

## 5. Asset paths

- Next.js build assets under `/_next/static/*` — long-cache immutable at the edge.
- Uploaded media stays under `/storage/*` on Laravel; ensure the production media
  host is in `next.config.ts` `images.remotePatterns` before cutover, or images
  won't optimize.
- Generated brand assets (icons/OG) ship with the Next build.

## 6. DNS / TTL preparation

1. **Days before**: lower the apex/`www` record TTL to 300s so the cutover and
   any rollback propagate fast.
2. **Cutover**: repoint the public origin to the reverse proxy fronting Next.js
   (or flip the proxy's upstream if the origin IP is unchanged — preferred, as
   it avoids DNS entirely and makes rollback instant).
3. **After stable**: raise TTL back to normal.

Prefer a **proxy-upstream flip over a DNS change** — it's reversible in seconds.

## 7. Downtime expectation

Near-zero. The Next.js app is built and running (validated by smoke tests)
**before** the proxy flip; the flip is atomic. If DNS is involved, "downtime" is
just cache/TTL convergence, mitigated by the pre-lowered TTL.

## 8. Cache invalidation

- Flip → purge any CDN/edge cache for HTML so clients stop getting Blade output.
- Warm key Next.js routes (home EN/AR, top systems/case studies) via the smoke
  suite so first real visitors hit warm ISR entries.
- Confirm on-demand revalidation is enabled and pointed at the **production**
  frontend URL + secret.

## 9. Smoke test checklist (run immediately after flip)

Point the env-driven suite at production and run
`STAGING_URL=https://hexaterminal.com npm run test:e2e:staging` (rename var
aside, the suite is URL-agnostic), then manually confirm:

1. `/en` and `/ar` homepages render (RTL for `ar`).  2. Theme toggle persists.
3. Services list + a system / case study / industry / article detail render.
4. Logo + media load; showreel modal opens/closes.  5. Lead form submits.
6. `/cms` login reachable.  7. `/api/health` + `/api/health/ready` OK.
8. **`robots.txt` now ALLOWS crawling and lists the sitemap** (inverse of staging).
9. `sitemap.xml` valid with hreflang.  10. Unknown slug → 404.
11. Every legacy URL (`/project/1`, `/service/1`, `/team/1`, `/projects`) resolves
    or 308-redirects.  12. No serious console errors.

## 10. Rollback trigger & procedure

**Triggers**: broken/again-Blade public pages, 5xx spikes, health failing,
missing/incorrect content, legacy redirects dead-ending, or the site indexable
before intended.

**Procedure** (see `../deployment/rollback-plan.md` for full detail): revert the
reverse-proxy upstream to Laravel (or the DNS record). Because the legacy Blade
frontend was never removed, this restores the previous site **immediately** with
no code deploy. Then diagnose the Next.js issue out of the hot path.

## 11. Remaining approvals before executing

- [ ] Product/stakeholder sign-off to switch the public site.
- [ ] Real CMS content authored for Systems/Industries/Articles (no legacy source).
- [ ] Production infra provisioned (API host, frontend host, TLS, Redis).
- [ ] Production `.env` set (indexing ON, CORS locked, revalidation secret).
- [ ] A green production build + smoke run against a production-like environment.
- [ ] DNS TTL lowered and a scheduled, monitored cutover window agreed.
