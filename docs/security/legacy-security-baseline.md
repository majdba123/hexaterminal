# Legacy Security Baseline

Source-verified inventory of the legacy Laravel surfaces and the fail-closed
isolation now applied to them. Verified against branch
`feature/hexa-legacy-security-hardening` (from
`feature/hexa-global-readiness-hardening`).

> **Closure pass — full-stack verification (implemented, not just verified).**
> A dedicated isolated sqlite database (`database/isolated/*.sqlite`, created
> and destroyed within this pass — never the dev/test/production database) was
> migrated, seeded (`DatabaseSeeder`, `hexa:migrate-legacy-content`,
> `DemoContentSeeder`, `PricingEstimatorFixtureSeeder` — the exact recipe CI's
> `e2e` job uses), and served live to prove:
>
> - legacy disabled (default): `/`, `/admin`, `/api/services/index` → `404`;
>   `/api/v1/public/*`, `/api/health*`, `/cms` → unaffected.
> - legacy enabled (compatibility mode): the same routes return `200`/`302`
>   with `X-Robots-Tag: noindex, nofollow` on every response, session cookie
>   `httponly; samesite=lax`.
> - a full `npm run build` against this live isolated API succeeded (all
>   locale/content routes prerendered), and the full Playwright suite passed
>   (44/44 after fixing one test-only defect — see below).
> - the legacy redirect pipeline was proven end-to-end — see
>   `docs/migration/legacy-redirect-map.md`.
>
> One real defect was found and fixed during this pass: the route-registry
> Playwright spec (`frontend/e2e/route-registry.spec.ts`) asserted every
> `footerGroup` has a `footer.<group>` title key, which is wrong for the
> `legal` group — `components/site/footer.tsx` renders legal links with no
> section title by design. The **test** was corrected; `footer.tsx` was not
> changed (it was already correct, proven by the passing navigation/footer
> e2e coverage).

## Isolation model (implemented)

Every legacy surface is gated by the `legacy:<surface>` middleware
(`App\Http\Middleware\LegacySurface`) reading `config/legacy.php`:

- **Disabled (default in staging/production):** controlled 404 before any
  controller runs. No Blade page renders; API returns
  `{"message":"This endpoint has been retired."}` (404). No route internals leak.
- **Enabled (explicit compatibility mode):** request proceeds and the response
  is tagged `X-Robots-Tag: noindex, nofollow`.

Flags (all default **false**, fail-closed on missing/unparseable values):
`LEGACY_PUBLIC_WEB_ENABLED`, `LEGACY_ADMIN_ENABLED`, `LEGACY_API_ENABLED`.

## Verification of prior findings

| Claim | Status | Evidence |
|-------|--------|----------|
| Legacy Blade public routes coexist with Next.js | **VERIFIED** | `routes/web.php` |
| Legacy `/admin/*` coexists with Filament `/cms` | **VERIFIED** | `routes/web.php` admin group; `CmsPanelProvider` path `cms` |
| Legacy `/api/*` coexists with `/api/v1/public/*` | **VERIFIED** | `routes/api.php` + `routes/api_v1.php` |
| Legacy routes always registered | **VERIFIED → RESOLVED** | now behind `legacy:*` gate; fail-closed |
| No `LEGACY_*` isolation controls | **VERIFIED → RESOLVED** | `config/legacy.php` added |
| Security headers partial | **VERIFIED → IMPROVED** | `SecurityHeaders` middleware + `config/security.php` |
| CSP not proven in runtime | **VERIFIED → PARTIAL** | CSP now emitted (Report-Only) and asserted by tests; enforcing blocked by Filament/Alpine `unsafe-eval` (see CSP doc) |

## Route classification

### Legacy public web (`routes/web.php`, `legacy:public_web`)

| Route | Classification | Replacement |
|-------|----------------|-------------|
| `GET /` | replaced | Next.js `/{locale}` |
| `GET /projects` | replaced | `/{locale}/case-studies` or `/systems` (founder to confirm) |
| `GET /project/{id}` | redirectable | DB Redirect table (per-record) |
| `GET /service/{id}` | redirectable | `/{locale}/services/{slug}` (DB Redirect) |
| `GET /team/{id}` | redirectable | team page/member (blocked until built) |
| `GET /api-docs` | internal-only | none public |

### Legacy admin (`routes/web.php`, `legacy:admin`)

| Route | Classification | Replacement |
|-------|----------------|-------------|
| `/admin`, `/admin/login`, `/admin/logout` | redundant | Filament `/cms` auth |
| `/admin/{dashboard,teams,...}` | redundant | Filament resources |
| `Auth::routes()` | redundant | Filament auth |

Authentication mechanism: Laravel session auth + `AdminMiddleware`
(`Auth::user()->type == 1`). Distinct from Filament, which uses spatie roles via
`User::canAccessPanel()` (`admin`/`editor`). Disabling `legacy:admin` removes the
second admin surface without touching Filament.

### Legacy API (`routes/api.php`, `legacy:api`)

| Endpoint group | Classification | Notes |
|----------------|----------------|-------|
| `GET teams/services/projects/about_us/faq/review/video …` | read, replaced | superseded by `/api/v1/public/*` |
| `POST contact_us/store`, `review/store` | public write | throttled `5,1`; superseded by v1 leads |
| `POST login` | auth | throttled `5,1` |
| `POST/PUT/DELETE store/update/delete` | authenticated write | `auth:sanctum` + `admin` |
| `GET /storage/{path}` | read (media) | already hardened (traversal + extension + containment checks); **gated with the file** — relocate to a supported route if media serving is needed post-cutover |

When `LEGACY_API_ENABLED=false` every route above is unreachable (controlled
JSON 404), including all writes and uploads.

## Rollback

Set the relevant `LEGACY_*_ENABLED=true` in the target environment and clear
config cache. No code was deleted, so rollback is configuration-only.

## Tests

`tests/Feature/Security/LegacySurfaceIsolationTest.php` covers both states for
all three surfaces plus versioned-API/health/Filament non-interference. The
suite runs with legacy surfaces enabled (compat) via `phpunit.xml`; the
disabled path is asserted per-test with `config()` overrides.
