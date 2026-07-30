# Production Deployment

How to build and run Hexa Terminal in production: a Laravel 12 API/CMS plus a
Next.js 16 App Router frontend. Every step here has been exercised locally
against a running API (see [Rendering strategy](#rendering-strategy) for how the
build depends on the API).

## Required versions

| Tool | Version | Notes |
|---|---|---|
| PHP | 8.2+ | Laravel 12 requires 8.2 minimum |
| Composer | 2.x | |
| Node.js | 20.x or 22.x LTS | Next.js 16 requires Node 20+ |
| npm | 10+ | ships with Node |
| Database | SQLite / MySQL 8 / PostgreSQL 14+ | dev uses SQLite; production your choice |

## Environment variables

### Backend (`.env`, see `.env.example`)

- `APP_KEY` — generate with `php artisan key:generate`.
- `APP_ENV=production`, `APP_DEBUG=false`.
- `APP_URL` — public API origin, e.g. `https://api.hexaterminal.com`.
- `DB_CONNECTION`, `DB_DATABASE`, `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`.
- `ADMIN_EMAIL`, `ADMIN_PASSWORD` — required to seed the CMS admin. Password
  must be ≥ 12 characters (enforced by `UsersTableSeeder`). No default admin is
  ever created.

### Frontend (`frontend/.env`, see `frontend/.env.example`)

- `API_URL` — server-side base URL for the public API, e.g.
  `https://api.hexaterminal.com/api/v1/public`. Used by the API client and by
  `next.config.ts` at build time.
- `NEXT_PUBLIC_SITE_URL` — public frontend origin, e.g.
  `https://hexaterminal.com`. Used for canonicals, hreflang, sitemap, OG.
- `NEXT_PUBLIC_ALLOW_INDEXING` — set to exactly `"true"` **only** on production.
  Any other value serves a disallow-all robots.txt. See
  [../seo/crawler-policy.md](../seo/crawler-policy.md).

## Database setup and migration order

```bash
# 1. Install backend deps (no dev deps in production)
composer install --no-dev --optimize-autoloader

# 2. App key (first deploy only)
php artisan key:generate

# 3. Run schema migrations
php artisan migrate --force

# 4. Seed baseline data (roles, admin user, and legacy source rows)
php artisan db:seed --force          # requires ADMIN_EMAIL / ADMIN_PASSWORD

# 5. Populate the unified content models the public API serves,
#    from the legacy tables (services, projects, team, reviews, contacts)
php artisan hexa:migrate-legacy-content
```

After this, the public API at `/api/v1/public/*` returns published content.
Ongoing content is managed in the Filament CMS at `/cms`; `hexa:migrate-legacy-content`
is a one-time backfill, not a recurring step.

> Migration behavior: the legacy migration is idempotent (`updateOrCreate` keyed
> on `legacy_*_id`) and only backfills Services, Team, Testimonials, Contact
> leads, Case Studies, and legacy→new redirects. Systems, Industries, and
> Articles have no legacy source and start empty — they are authored in the CMS.

## CMS setup

- Filament panel: `/cms`. Log in with `ADMIN_EMAIL` / `ADMIN_PASSWORD`.
- Roles/permissions are seeded by `RolesSeeder`.
- Content is drafted and published there; the public API only exposes
  published, non-future-dated entries.

## Rendering strategy

The Next.js frontend is **statically generated with ISR**, and its build
**requires the Laravel API to be reachable**. This is by design — SEO-critical
content is server-rendered into static HTML at build time, not fetched on the
client.

Verified classification from `next build` (66 static pages):

| Route | Strategy | Why |
|---|---|---|
| `/[locale]` (home) | SSG + ISR (revalidate 300s) | Content-driven, changes rarely |
| `/[locale]/services/[slug]` | SSG + ISR | Pre-rendered from `generateStaticParams`; edits refresh every 5 min |
| `/[locale]/systems/[slug]`, `case-studies/[slug]`, `industries/[slug]`, `insights/[slug]` | SSG + ISR | Same |
| `/[locale]/services`, `systems`, `case-studies`, `insights` (hubs) | Dynamic SSR | Read `searchParams` for pagination |
| `/[locale]/about`, `contact`, `start-a-project` | Static | No per-request data |
| `/robots.txt`, `/sitemap.xml`, `/icon`, `/apple-icon`, `/opengraph-image` | Static (generated) | Metadata routes |
| `/api/leads` | Dynamic | Proxies POST to Laravel |

Notes:
- Detail routes use the default `dynamicParams` (true): editing existing content
  refreshes within the ISR window; **newly published** entries appear after the
  next build/deploy (or on-demand render). Unknown slugs return a real **404**
  with the localized `not-found.tsx` UI.
- There is intentionally **no `loading.tsx`** at the `[locale]` level: it
  introduced a streaming Suspense boundary that downgraded `notFound()`
  responses to a soft 200. Removing it restores correct 404 status codes, and
  the SSG pages load instantly so a skeleton added little.
- The build **fails safe on redirects only**: if the API is unreachable when
  `next.config.ts` fetches the legacy redirect map, the build proceeds with no
  redirects. All other content fetches are required — an unreachable API fails
  the build rather than shipping empty pages.

## Build and start (reproducible procedure)

This is the exact sequence proven locally; CI runs the same shape (see
`.github/workflows/ci.yml`).

```bash
# --- Backend: start the API against a real, seeded database ---
php artisan migrate --force
php artisan db:seed --force
php artisan hexa:migrate-legacy-content
php artisan serve --host=127.0.0.1 --port=8000    # or php-fpm behind nginx

# --- Frontend: build against the running API ---
cd frontend
npm ci
API_URL="http://127.0.0.1:8000/api/v1/public" \
NEXT_PUBLIC_SITE_URL="https://hexaterminal.com" \
NEXT_PUBLIC_ALLOW_INDEXING="true" \
  npm run build

# --- Start the built app ---
npm run start        # serves on :3000
```

Health of the build was verified with:
`/en` & `/ar` → 200, `<html lang="ar" dir="rtl">` present, `/robots.txt` and
`/sitemap.xml` correct, real detail slug → 200, unknown slug → 404, legacy
`/project/1` → 308 permanent redirect, `/icon` & `/opengraph-image` → 200 PNG.

## Reverse proxy / domain strategy

- `hexaterminal.com` → Next.js (`:3000` or platform edge).
- `api.hexaterminal.com` → Laravel (php-fpm behind nginx/Apache), or same origin
  under a path prefix. The frontend only needs `API_URL` to resolve server-side.
- Terminate TLS at the proxy. Forward `X-Forwarded-*` so Laravel builds correct
  absolute URLs.

## Storage / media

- Laravel `storage/app/public` must be linked: `php artisan storage:link`.
- Public media is served through the hardened storage route (traversal/dotfile/
  extension checks — see `tests/Feature/Security/StorageRouteTest.php`).
- Frontend `next.config.ts` `images.remotePatterns` whitelists the media hosts;
  add your production media/CDN host there before switching real image URLs.

## Queues

No queue worker is required for current functionality (lead submission is
synchronous; `QUEUE_CONNECTION=sync` is acceptable). If you later move image
processing (`ProcessProjectImage`) or mail to a queue, run `php artisan queue:work`
under a supervisor.

## Caching and invalidation

- The public API sends `Cache-Control: public, max-age=300, etag` and uses an
  internal response cache keyed per resource/locale; content edits invalidate it
  (covered by `tests/Feature/Api/V1/CacheInvalidationTest.php`).
- The frontend layers ISR (`revalidate: 300`) on top, so a CMS edit is visible
  within ~5 minutes without a redeploy.
- To force fresh content immediately, redeploy the frontend (rebuild) or clear
  the API cache (`php artisan cache:clear`).

## Indexing safety

- Staging/preview: leave `NEXT_PUBLIC_ALLOW_INDEXING` unset/false → disallow-all
  robots.txt. Verify with `curl https://staging.example/robots.txt`.
- Production: set `NEXT_PUBLIC_ALLOW_INDEXING=true`, rebuild, and verify the
  robots.txt shows the allow policy and the sitemap line.

## Health checks

- Backend: `GET /api/v1/public/home?locale=en` → 200 JSON.
- Frontend: `GET /en` → 200; `GET /robots.txt` → 200.
- Use these as load-balancer readiness probes.

## Rollback

- Frontend: redeploy the previous build artifact / previous image tag. Builds
  are immutable and self-contained.
- Backend: migrations are forward-only; keep a database backup before
  `migrate --force`. Roll back by restoring the DB snapshot and redeploying the
  previous release. `hexa:migrate-legacy-content` is idempotent and safe to
  re-run.

## Known deployment dependencies

1. **The frontend build needs the API up** for all content routes (redirects
   fail safe; content does not).
2. `ADMIN_EMAIL` / `ADMIN_PASSWORD` must be set before `db:seed`, or the admin
   user is skipped (throws in `production`).
3. `NEXT_PUBLIC_SITE_URL` must be the real origin or canonicals/sitemap/OG URLs
   will be wrong.
4. `storage:link` must be run for media to resolve.
