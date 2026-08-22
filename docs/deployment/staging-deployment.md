# Staging Deployment

How to stand up and verify a **staging** deployment of Hexa Terminal. Staging
mirrors production topology but is **non-indexable**, uses a **dedicated
database**, and may hold demo fixtures. Production deployment is out of scope
here (see `production-deployment.md`); the cutover from the legacy Blade site is
in `../migration/frontend-cutover-plan.md`; recovery is in `rollback-plan.md`.

> This project runs the **legacy Laravel HTTP-kernel skeleton** on Laravel 12,
> so there is **no framework-default `/up` route**. Health is served by the
> endpoints added in `routes/health.php` (see [Health checks](#health-checks)).

## 1. Topology

| Concern | Host (example) | Served by |
|---|---|---|
| Public frontend | `staging.hexaterminal.com` | Next.js (`next start`, :3000) behind Nginx |
| Public API | `api-staging.hexaterminal.com/api/v1/public/*` | Laravel (PHP-FPM) behind Nginx |
| Legacy API | `api-staging.hexaterminal.com/api/*` | Laravel (unchanged) |
| CMS | `api-staging.hexaterminal.com/cms` | Filament (Laravel) |
| Media | `api-staging.hexaterminal.com/storage/*` | Laravel public disk |
| Database | private | MySQL 8 (`hexa_terminal_staging`) |
| Cache/queue/session | private | Redis (file/sync also work) |

Domains are **examples** and fully env-driven — nothing assumes they exist. A
single-origin variant (everything on one host, `/api` + `/cms` → Laravel, the
rest → Next.js) is the eventual production shape and is encoded, dormant, in
`deploy/staging/nginx/frontend-staging.conf`.

Deployment options, pick one:
- **A — bare VPS**: Nginx + PHP-FPM + a `next start` process under systemd
  (`deploy/staging/systemd/hexa-frontend.service`).
- **B — split**: Laravel on a VPS/container, Next.js on a Node host/Vercel,
  separate API domain.
- **C — Docker Compose**: `deploy/staging/docker-compose.staging.yml` (MySQL,
  Redis, Laravel+PHP-FPM, Nginx, Next.js), fully self-contained.

## 2. Configuration

Copy the templates and fill real values (never commit a filled `.env`):
- Backend: `.env.staging.example` → `.env`
- Frontend: `frontend/.env.staging.example` → `frontend/.env`

Critical staging values:

| Variable | Value | Why |
|---|---|---|
| `APP_ENV` | `staging` | Enables the demo-seeder guard; non-prod robots. |
| `APP_DEBUG` | `false` | Never leak stack traces off local. |
| `APP_VERSION` | deploy commit SHA | Surfaced by health endpoints for correlation. |
| `CORS_ALLOWED_ORIGINS` | `https://staging.hexaterminal.com` | Explicit allow-list — never `*` off local. |
| `SESSION_SECURE_COOKIE` | `true` | HTTPS-only CMS session cookie. |
| `SESSION_DOMAIN` | the CMS host | Scopes the session cookie. |
| `TRUSTED_PROXIES` | LB IP/CIDR (or `*`) | Correct client IP + scheme behind TLS edge. |
| `NEXT_PUBLIC_ALLOW_INDEXING` | `false` (or unset) | **Fail-safe noindex** — see §5. |
| `API_URL` (frontend, server-only) | internal API base | Not exposed to the browser. |
| `REVALIDATION_SECRET` / `REVALIDATE_SECRET` | matching random 32-byte hex | On-demand revalidation (see §7). |

The Next.js `API_URL` and `REVALIDATE_SECRET` are **server-only** (no
`NEXT_PUBLIC_` prefix) and never reach the browser bundle.

## 3. Deployment order (must not vary)

The frontend build performs SSG against the live API, so ordering is load-bearing
— **a build against an unreachable API fails, and a failed build must stop the
deploy** (never publish empty pages):

1. Deploy backend code.
2. **Back up the staging DB** (see `rollback-plan.md` §Backups).
3. `php artisan migrate --force` (see §6).
4. Cache config/routes: `php artisan config:cache route:cache view:cache`.
5. **Verify API readiness**: `GET /api/health/ready` → `200 {"status":"ok"}`.
6. Build the frontend against the reachable API (`API_URL=…`, `npm run build`).
7. Start/restart the frontend (`next start` / systemd / compose service).
8. Run the staging smoke suite (§8).

Compose enforces the first half automatically: `frontend` `depends_on` `api_web`
being `service_healthy`, and `api_web`'s healthcheck hits `/api/health`.

## 4. Rendering strategy (what needs what)

| Route | Strategy | API at build? | Refresh path |
|---|---|---|---|
| `/[locale]` (home) | SSG + ISR 300s | Yes | ISR, or revalidate `home` |
| `/[locale]/services|systems|case-studies|industries|insights` (lists) | dynamic where they read `searchParams`, else SSG+ISR | Yes | ISR / revalidate resource |
| `…/[slug]` detail pages | SSG + ISR 300s, `dynamicParams` default | Yes (via `generateStaticParams`) | ISR / revalidate resource+slug |
| `/[locale]/{about,contact,start-a-project}` | static | No | redeploy |
| `robots.ts`, `sitemap.ts`, `icon`, `apple-icon`, `opengraph-image` | generated static | sitemap: yes | redeploy / revalidate `/sitemap.xml` |
| `/api/health`, `/api/revalidate`, `/api/leads` | dynamic route handlers | n/a | n/a |

**Newly created slugs**: with `dynamicParams` at its default, an unknown slug is
rendered on first request and then cached — but to appear in lists/sitemap
promptly, trigger on-demand revalidation (§7) or redeploy. **Edits to existing
pages** refresh within the 300s ISR window automatically.

## 5. Indexing safety (defence in depth)

Staging must never be indexed. Four independent layers, all defaulting to
**noindex** when `NEXT_PUBLIC_ALLOW_INDEXING` is anything other than the exact
string `"true"` (including absent):

1. **`app/robots.ts`** — serves `Disallow: /` for all agents; no sitemap line.
2. **Page metadata** — `lib/seo/indexing.ts::resolveRobots()` feeds
   `robots: noindex,nofollow` into the root layout (inherited by every page)
   and each detail page.
3. **HTTP header** — `next.config.ts` adds `X-Robots-Tag: noindex, nofollow`
   to every response.
4. **Edge** — `deploy/staging/nginx/frontend-staging.conf` also sets
   `X-Robots-Tag` (remove at production cutover).

Proven by `frontend/e2e/robots.spec.ts` (indexable branch, in CI) and
`frontend/e2e-staging/staging.spec.ts` (noindex branch, against the deployed
URL). A missing env var can only ever make the site *more* private.

## 6. Database & migrations

- **Dedicated DB** (`hexa_terminal_staging`); never point staging at production.
- **Back up before every migration** (`mysqldump`), retain per `rollback-plan.md`.
- Run non-interactively: `php artisan migrate --force`. Verify:
  `php artisan migrate:status`.
- **Seed policy**: base content via `php artisan db:seed --force`; frontend-only
  demo fixtures via `php artisan db:seed --class=DemoContentSeeder --force`.
  `DemoContentSeeder` **refuses to run when `APP_ENV=production`** (guarded;
  override only with `ALLOW_DEMO_SEED=true`, which you should never do in prod).
  Covered by `tests/Feature/SeederGuardTest.php`.
- **Rollback limits**: not every migration is reversible; treat the pre-migration
  backup as the source of truth (see `rollback-plan.md`).

## 7. On-demand revalidation

Publishing in the CMS clears the API cache (existing observers) **and**, when
enabled, pings the frontend to rebuild affected pages immediately instead of
waiting out the 300s ISR window.

- **Flow**: model saved/deleted → `App\Observers\ClearsPublicApiCache` →
  `App\Services\RevalidationService` → `POST {frontend}/api/revalidate`
  (`x-revalidate-secret` header) → `revalidatePath()` for the resource's list +
  detail + home + sitemap, per locale.
- **Security**: shared secret (constant-time compare), disabled/`503` if unset,
  rate-limited, best-effort replay window, secret never in client JS or logs.
- **Fail-safe**: a slow/unreachable/`500` frontend **never breaks a CMS save**
  (swallowed + logged). Enable with `REVALIDATION_ENABLED=true` +
  `REVALIDATION_URL` + `REVALIDATION_SECRET` (matching the frontend).
- **Tests**: `tests/Feature/RevalidationTest.php` (backend, `Http::fake`),
  `frontend/e2e/revalidate.spec.ts` (endpoint auth 401/400/200).

## 8. Verifying CMS → API → frontend

After deploy, walk one item of each type through the chain (use staging/demo
data — never fabricate production claims, never persist test fixtures to a real
prod DB):

1. In `/cms`, create + publish a Service / System / Case Study / Industry /
   Article, filling **both** EN and AR (Spatie translatable).
2. Confirm it appears in the public API:
   `GET api-staging…/api/v1/public/systems/<slug>?locale=en` (and `?locale=ar`).
3. Confirm the frontend renders it: `staging…/en/systems/<slug>` and `/ar/...`.
4. Edit a field → within 300s (or immediately with revalidation) the API + page
   reflect it (cache invalidation is proven in `tests/Feature/Api/V1`).
5. Update the entry's SEO fields / a Redirect → re-check metadata / the redirect.
6. **Unpublish** → the item 404s on the API and disappears from lists (the
   `Publishable` scope filters unpublished/future-dated content).

## 9. Health checks

| Endpoint | Type | Checks | Codes |
|---|---|---|---|
| `GET /api/health` | Laravel liveness | process only | always `200` |
| `GET /api/health/ready` | Laravel readiness | DB + cache reachable | `200` / `503` |
| `GET /api/health` (frontend) | Next liveness | process; reports API reachability | `200` |

Responses carry only booleans, environment name, and `APP_VERSION` — never
secrets, credentials, or traces (`tests/Feature/HealthTest.php`). Health routes
bypass the `throttle:api` limiter so aggressive probing can't cause a false
outage. Point the load balancer at `/api/health/ready` for the API and
`/api/health` for the frontend.

## 10. Storage & media

- Run `php artisan storage:link` (symlinks `public/storage` → `storage/app/public`).
- Filament uploads land on the `public` disk. **Media is served by Nginx as
  static files from `/storage/*`** (see the table in §1) — it does *not* go
  through Laravel, so the `/api/storage/{path}` route in `routes/api.php` is
  **not** the media path and its hardening does not apply here. That route is
  a legacy surface, disabled by default via `LEGACY_API_ENABLED`.
  Because `/storage/*` is raw static serving of attacker-influenced content,
  the protection lives in two places and **both** are required:
  - `deploy/staging/nginx/api-staging.conf` — the `location ^~ /storage/`
    block refuses to hand anything under that prefix to PHP-FPM, and sets
    `Content-Security-Policy: default-src 'none'; sandbox` so an uploaded
    `.svg`/`.html` cannot execute script in this origin (which also serves
    `/cms`).
  - `App\Filament\Support\Uploads` — every CMS upload field uses an explicit
    MIME allowlist. Never use bare `FileUpload::make()` or `->image()`:
    `->image()` accepts `image/*`, which includes `image/svg+xml`, and
    Filament preserves the client-supplied file extension.
- In compose, `api_web` must mount **both**:
  `../../public:/var/www/hexa/public:ro` and
  `storage_app:/var/www/hexa/public/storage:ro`. This direct mount is
  intentional: `public/storage` is gitignored, so the Nginx container must
  not depend on the symlink created inside the separate Laravel container.
  This is load-bearing: Nginx serves `/storage/*` directly from that mounted
  public-media volume, so it has to be the same persisted volume Laravel writes.
  Never copy uploads into image layers or into a second volume during deploys.
- Uploads are **not** committed; in compose they persist on the `storage_app`
  volume.
- Real assets in `frontend/public/media` (logo, hero/showreel video + posters,
  OG image, generated icons) ship with the frontend build; verify they load
  over HTTPS from the staging origin (`staging.spec.ts` checks for broken images).
- `next.config.ts` `images.remotePatterns` must include the staging media host
  before CMS-uploaded images will optimize (currently `**.hexaterminal.com`).

## 11. CORS, cookies, security headers

- **CORS**: `config/cors.php` reads `CORS_ALLOWED_ORIGINS` (comma-separated,
  no `*` off local), applies to `api/*` + `sanctum/csrf-cookie`; credentials off
  by default (token/stateless public API). Filament is same-origin, so CORS
  doesn't gate it.
- **Cookies**: `SESSION_SECURE_COOKIE=true`, `SESSION_DOMAIN` host-scoped,
  `SameSite=lax`. CSRF stays on for the web/CMS group.
- **Headers**: baseline (`X-Content-Type-Options`, `Referrer-Policy`,
  `X-Frame-Options`, `Permissions-Policy`) from `next.config.ts`; the stronger
  edge headers (`Strict-Transport-Security`, `Content-Security-Policy`
  `frame-ancestors 'self'`) live in the Nginx configs so the inline theme-init
  script isn't broken by an app-level CSP.

## 12. Observability

- **Laravel logs**: `LOG_LEVEL=info` on staging (`debug` leaks detail); errors
  reach the `stack` channel. CMS publish/revalidation failures log at `warning`
  with resource/slug/status only (never the secret).
- **Next.js logs**: `next start` stdout/stderr (captured by systemd/compose).
- **Version/commit**: set `APP_VERSION` at deploy; both health endpoints echo it.
- **Failed lead submissions**: surfaced by the `/api/v1/public/leads` throttle +
  validation responses; the Next `/api/leads` proxy forwards upstream status.
- **What never to log**: passwords, tokens, API secrets, session cookies, full
  lead payloads.

## 13. Quality gates before promoting a build

Run everything in `../testing/quality-gates.md`, plus the staging-specific
additions: `HealthTest`, `RevalidationTest`, `SeederGuardTest`,
`e2e/robots.spec.ts`, `e2e/revalidate.spec.ts`, and the deployed
`npm run test:e2e:staging` against the staging URL. `.github/workflows/
staging-verify.yml` runs the last one manually against a supplied URL — it never
deploys.
