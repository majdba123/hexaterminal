# Legacy Route Retirement Matrix

Inventory of every legacy surface still registered alongside the Next.js
frontend, Filament CMS, and versioned public API. **No route has been deleted** —
this sprint maps dependencies and prescribes a reversible isolation strategy;
deletion happens later behind tested flags.

> **Update (legacy-security-hardening sprint):** the reversible isolation
> strategy prescribed below is now **implemented and tested**. All three
> surfaces are gated by `config/legacy.php` + the `legacy:*` middleware and
> fail closed by default. See `docs/security/legacy-security-baseline.md` for
> the implementation and `docs/security/production-security-checklist.md` for
> the cutover sequence. The `LEGACY_*_ENABLED` flags below are live.

## Legacy public web (`routes/web.php`, Blade + Axios)

| Legacy route | Handler | Classification | Replacement | Action |
|--------------|---------|----------------|-------------|--------|
| `GET /` | `WebsiteController@index` | replaceable | `/{locale}` (Next home) | redirect once Next is authoritative |
| `GET /projects` | `WebsiteController@projects` | replaceable | `/{locale}/case-studies` (or `/systems`) | redirect — **mapping needs founder confirmation** (projects → case studies vs systems) |
| `GET /project/{id}` | `WebsiteController@projectDetail` | replaceable (per-record) | case-study/system detail | DB Redirect table (`hexa:migrate-legacy-content`) |
| `GET /service/{id}` | `WebsiteController@serviceDetail` | replaceable (per-record) | `/{locale}/services/{slug}` | DB Redirect table |
| `GET /team/{id}` | `WebsiteController@teamDetail` | replaceable (per-record) | `/{locale}/team` (or member) | DB Redirect table; blocked until team page is built |
| `GET /api-docs` | `LandingController@index` | internal-only | none (public) | 404/410 publicly, or restrict to internal |

## Legacy admin (`routes/web.php`, `admin.*`)

| Legacy route | Classification | Replacement | Action |
|--------------|----------------|-------------|--------|
| `GET /admin` + `/admin/login` | redundant | Filament panel | disable outside local/test via `LEGACY_ADMIN_ENABLED` (fail-closed) |
| `POST /admin/login`, `/admin/logout` | redundant | Filament auth | disable with the above; keep throttle while enabled |
| `GET /admin/{dashboard,teams,services,projects,about,faq,reviews,videos,contacts}` | redundant | Filament resources | disable with the above |

Legacy admin must **not** remain a second uncontrolled admin surface once
Filament is authoritative.

## Legacy API (`routes/api.php`)

| Surface | Classification | Replacement | Action |
|---------|----------------|-------------|--------|
| Legacy read endpoints | replaceable | `/api/v1/public/*` | isolate behind `LEGACY_API_ENABLED`; frontend already consumes v1 only |
| Legacy write/upload/store endpoints | unsafe if exposed | Filament + v1 write endpoints | **disable by default**; enable only in local/test |
| Legacy auth endpoints | redundant | Filament / v1 | disable by default |

## Prescribed isolation strategy (reversible — not yet implemented)

Environment flags, all **fail-closed** (default disabled in staging/production):

- `LEGACY_PUBLIC_WEB_ENABLED`
- `LEGACY_ADMIN_ENABLED`
- `LEGACY_API_ENABLED`

Rules:

1. Local/test may enable any surface for migration work.
2. While a legacy public surface is enabled, it must send `noindex`.
3. Replaced legacy public URLs 301 to the correct EN/AR Next destination.
4. Unmapped deprecated public routes return a controlled 404/410 **only when
   safe** (no dependency).
5. Legacy write APIs and legacy admin stay disabled unless explicitly enabled.
6. Rollback = flip the flag; **no destructive code deletion in this sprint.**

## Open mapping questions for the founder

- `/projects` and `/project/{id}` → **case studies** or **systems**? (Determines
  redirect targets; do not fabricate.)
- `/team/{id}` → team page vs per-member route (team page not yet built).
