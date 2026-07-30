# Security Hardening — Hexa Terminal

Status: **Stage 0 complete**, now running on **Laravel 12** (Phase 1 upgrade) on branch `feature/hexa-nextjs-professional-platform`.
Every fix below is regression-tested in `tests/Feature/Security/`.

## Fixed Vulnerabilities

### 1. Default admin credentials (CRITICAL)
- **Was:** `database/seeders/UsersTableSeeder.php` created `admin@example.com` / `password` (type=1) and printed the credentials to the console.
- **Root cause:** convenience seeding with hardcoded secrets.
- **Fix:** credentials now come from `ADMIN_EMAIL` / `ADMIN_PASSWORD` env vars (exposed via `config/app.php` so `config:cache` works). No defaults exist; production seeding **throws** if unset; passwords under 12 chars are rejected; nothing is echoed.
- **Tests:** `AdminSeederTest` (no user without config, old default never created, short password rejected, config-driven creation works).

### 2. Review moderation bypass → public stored XSS (CRITICAL)
- **Was:** `GET /api/review/index|show` returned **all** reviews regardless of `is_approved` (`ReviewController`), and the public store accepted `is_approved` from the request body — an anonymous submitter could self-approve. Combined with unescaped `innerHTML` rendering (below), this was an unauthenticated stored-XSS chain against every visitor.
- **Fix:**
  - Public reads now use `Review::approved()` (both list, paginated list, and show).
  - Admin moderation still works: requests carrying a valid Sanctum bearer token for a `type=1` user see all reviews (resolved via `auth('sanctum')` on the public route).
  - `is_approved` removed from public store validation and force-set to `false` server-side.
- **Tests:** `ReviewModerationTest` (6 tests covering public exclusion, pagination, show 404, admin visibility, self-approve prevention, anonymous write denial).

### 3. Unescaped `innerHTML` rendering (CRITICAL chain / HIGH standalone)
- **Was:** every dynamic renderer interpolated API data into `innerHTML` with zero escaping. Attack surfaces:
  - Public homepage reviews (anonymous input → all visitors).
  - `/admin/contacts` + `/admin/reviews` (anonymous input rendered in the admin panel → **admin token theft** from `localStorage`).
  - All public pages rendering admin-authored content (post-compromise persistence).
- **Fix:** `esc()` / `escUrl()` helpers added to both layouts (`resources/views/layouts/website.blade.php`, `layouts/admin.blade.php`). `escUrl` additionally rejects `javascript:`/`data:`/`vbscript:` URIs. Applied to:
  - `website/components/{reviews,team,services,projects,about,videos,faq}.blade.php`
  - `website/{projects,project-detail,team-detail,service-detail}.blade.php`
  - `admin/contacts.blade.php`, `admin/reviews.blade.php` (anonymous-input surfaces)
  - toast helpers in both layouts (API error messages).
  - IDs used in URLs are `encodeURIComponent()`-wrapped.
- **Known remaining (accepted, documented):** `admin/{teams,services,projects,faq,videos,about,dashboard}.blade.php` still render **admin-authored** content unescaped back to the admin. Risk tier: requires an already-authenticated admin author; surface is replaced by Filament in Phase 3 and retired at cutover.
- **Note:** `service.icon` in the services component intentionally remains raw — it is admin-authored icon *markup* by design; will become a constrained icon-name enum in the new content model.

### 4. Missing rate limiting (HIGH)
- **Was:** no throttle on any public write or auth endpoint.
- **Fix:** `throttle:5,1` (5/min/IP) on `POST /api/review/store`, `POST /api/contact_us/store`, `POST /api/login`, `POST /admin/login`.
- **Tests:** `RateLimitingTest` (4 endpoints assert 429 on the 6th request).

### 5. Exception detail leakage (MEDIUM)
- **Was:** ~22 handlers returned `'error' => $e->getMessage()` in 500 JSON bodies (SQL errors, paths); `Registration/RegisterController` echoed raw exception messages.
- **Fix:** all `$e->getMessage()` fragments removed from response bodies (kept in `Log::error`). RegisterController returns a generic message and logs the detail.
- **Tests:** `ErrorLeakageTest` (forces a DB failure, asserts generic body, no `SQLSTATE`, no `error` key).

### 6. Unhardened public file-serving route (MEDIUM)
- **Was:** `GET /api/storage/{path}` with `.*` wildcard served any file the public disk resolved.
- **Fix (defense-in-depth):** rejects `..`, null bytes, and dotfiles before touching the filesystem; whitelists static media extensions (`jpg jpeg png gif webp avif svg ico mp4 webm pdf`); resolves `realpath` and verifies containment under the public disk root; adds cache headers.
- **Tests:** `StorageRouteTest` (serves valid media; blocks traversal, dotfiles, `.php`/other extensions).

### 7. Missing `.env.example` (MEDIUM)
- **Fix:** created with all required variables including the new `ADMIN_*` block; `APP_DEBUG` documented; no secrets committed.

### 8. Response-cache authentication bypass (HIGH — found during Stage 0 review, not in the original audit)
- **Was:** `App\Http\Middleware\ResponseCache` was registered in the **global `web` middleware group** (`app/Http/Kernel.php:37`), which executes *before* route-level `auth`/`admin` middleware in Laravel's pipeline. It cached any 200 response (any HTTP method — it never checked for GET) keyed only by `md5($request->fullUrl())`, with no variance by auth state. A cache **hit** returned immediately without ever calling `$next($request)`. Net effect: once a real admin's page (e.g. `/admin/dashboard`) was cached, any unauthenticated visitor requesting the same URL within the TTL (default 60s) received the cached admin HTML **without the `auth`/`admin` middleware ever running** — a genuine authorization-boundary bypass, not just a UI-hiding issue.
- **Fix:** `isUncacheable()` guard added — caching (both read and write) is skipped for non-GET/HEAD requests, any request carrying an `Authorization` header/bearer token, any request where `Auth::check()` is true, and any route whose middleware list includes `auth` or `admin` (checked via `$route->gatherMiddleware()`, available before the pipeline runs because routing resolves before middleware execution).
- **Tests:** `ResponseCacheAuthBypassTest` (an authenticated admin's dashboard visit does not populate the cache store; a subsequent unauthenticated request is redirected to login, never served a cached 200).

## Still Open (addressed in later phases)

| Item | Phase |
|---|---|
| `type == 1` magic-integer authorization → roles/policies (spatie/laravel-permission) | Phase 2/3 |
| Legacy admin panel replaced by Filament (policies, audit log, moderation workflow) | Phase 3 |
| Laravel 10 EOL → upgrade to Laravel 12 | Phase 1 |
| Upload validation depth (type/size/dimension enforcement in CMS) | Phase 3 |
| Honeypot/turnstile on lead forms | Phase 4/5 |
| Removal of dead social-login controllers & `laravel/ui` | Phase 1/8 |
