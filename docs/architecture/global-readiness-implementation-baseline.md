# Global Readiness Implementation Baseline

Source-verified status of the Hexa Terminal platform against the global-readiness
audit findings. Every row was checked against repository source on branch
`feature/hexa-global-readiness-hardening` (forked from
`feature/hexa-pricing-estimator-growth` @ `bdcf2a8`). This document is a
verification record, not a report of prior claims.

**Classification legend**

- **VERIFIED** — finding confirmed true from source.
- **PARTIALLY VERIFIED** — partially implemented; gaps noted.
- **NO LONGER APPLICABLE** — the condition the finding described is gone.
- **NOT FOUND** — the capability the finding assumed exists does not.
- **NEW ISSUE** — discovered during this pass, not in the original findings.

---

## 1. Public routing & information architecture

| # | Finding | Status | Evidence |
|---|---------|--------|----------|
| 1.1 | Next.js App Router with `/en` `/ar` locale prefix | **VERIFIED** | `frontend/i18n/routing.ts` (`locales: ["en","ar"]`, `localePrefix: "always"`); pages under `frontend/app/[locale]/` |
| 1.2 | Route lists duplicated across nav / footer / sitemap | **VERIFIED → RESOLVED** | Was copied in `header.tsx`, `mobile-nav.tsx`, `footer.tsx`, `app/sitemap.ts`; now derived from `frontend/lib/routes/registry.ts` |
| 1.3 | No shared route source of truth | **VERIFIED → RESOLVED** | Registry added this sprint with invariant test `e2e/route-registry.spec.ts` |
| 1.4 | Trust pages (security/process/etc.) missing | **VERIFIED** | No `app/[locale]/security` etc.; recorded as `content-blocked` target-IA routes in the registry only |

## 2. Legacy surfaces

| # | Finding | Status | Evidence |
|---|---------|--------|----------|
| 2.1 | Legacy Blade public routes coexist with Next.js | **VERIFIED** | `routes/web.php`: `/`, `/projects`, `/project/{id}`, `/team/{id}`, `/service/{id}`, `/api-docs` |
| 2.2 | Legacy `/admin` coexists with Filament `/cms` | **VERIFIED** | `routes/web.php` `admin.*` route group (`App\Http\Controllers\Admin\AdminController`); Filament panel separate |
| 2.3 | Legacy `/api/*` coexists with `/api/v1/public/*` | **VERIFIED** | `routes/api.php` (7.6 KB) + `routes/api_v1.php` (`v1/public` prefix) both registered |
| 2.4 | No environment flag isolates legacy surfaces | **VERIFIED (NEW ISSUE severity)** | No `LEGACY_*_ENABLED` gate in `routes/*.php`, `config/`, `.env.example`. Legacy routes are always registered. |
| 2.5 | DB-driven legacy→new redirect map exists | **PARTIALLY VERIFIED** | `frontend/next.config.ts` `legacyRedirects()` fetches `/redirects` (Redirect model + `hexa:migrate-legacy-content`); fails open if API down. Covers per-record ids, not the static legacy hubs. |

## 3. SEO — sitemap, robots, canonical, hreflang

| # | Finding | Status | Evidence |
|---|---------|--------|----------|
| 3.1 | Single flat sitemap, published content only | **PARTIALLY VERIFIED** | `frontend/app/sitemap.ts` builds from API list endpoints (published-only server-side) + registry static paths; excludes `/estimate/{uuid}`. Approval/indexability filtering relies on the API returning only published records — not independently asserted in a test. |
| 3.2 | Robots fails closed on staging | **VERIFIED** | `frontend/app/robots.ts` disallow-all unless `NEXT_PUBLIC_ALLOW_INDEXING === "true"`; mirrored by `X-Robots-Tag` in `next.config.ts` and page metadata via `lib/seo/indexing.ts` |
| 3.3 | AI-crawler policy is explicit & configurable | **PARTIALLY VERIFIED** | `robots.ts` distinguishes `OAI-SearchBot` (allow) vs `GPTBot` (allow, explicit); documented in `docs/seo/crawler-policy.md`. Policy is hard-coded, not env-configurable. |
| 3.4 | Canonical points to current locale, x-default present | **PARTIALLY VERIFIED** | `lib/seo/alternates.ts` builds reciprocal `en`/`ar` + `x-default`; assumes every slug exists in both locales (locale-invariant slug model). No test asserting reciprocity per page type, and no guard for a genuinely missing translation. |
| 3.5 | Search/result pages excluded from index | **VERIFIED** | `/search` and `/estimate/{uuid}` are noindex; registry marks `search` `indexable:false, inSitemap:false` |

## 4. Structured data

| # | Finding | Status | Evidence |
|---|---------|--------|----------|
| 4.1 | JSON-LD emitted | **PARTIALLY VERIFIED** | `frontend/lib/seo/jsonld.ts` + `components/site/json-ld.tsx` present. Not yet a stable-`@id` entity graph (Organization/WebSite/WebPage/Breadcrumb IDs); no syntax/visibility test. Full audit deferred. |

## 5. Security

| # | Finding | Status | Evidence |
|---|---------|--------|----------|
| 5.1 | Production security hardening incomplete | **PARTIALLY VERIFIED** | Baseline headers in `next.config.ts` (`X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`, `Permissions-Policy`). Full CSP + HSTS delegated to reverse proxy per `docs/deployment/staging-deployment.md` — **not enforced in-app and not proven by a header test**. |
| 5.2 | CMS/admin login throttled | **PARTIALLY VERIFIED** | Legacy `/admin/login` has `throttle:5,1` (`routes/web.php`). Filament panel auth hardening (session, MFA) not verified in this pass. |
| 5.3 | Public API write endpoints rate-limited | **VERIFIED** | `routes/api_v1.php`: leads `5,1`, estimates `20,1`, estimate-lead `5,1`, newsletter `5,1` |
| 5.4 | No `security.txt` / responsible-disclosure surface | **NOT FOUND** | No `public/.well-known/`; no security contact route |
| 5.5 | Dependency / secret CI scanning | **NOT FULLY VERIFIED** | `.github/` present but not audited in this pass — see Remaining Limitations |

## 6. CMS, preview, editorial

| # | Finding | Status | Evidence |
|---|---------|--------|----------|
| 6.1 | Filament CMS with editorial workflow & approval | **VERIFIED** | `app/Filament/Resources/*` (17 resources), migration `..._add_editorial_workflow_to_content_tables.php`, `content_activities` table |
| 6.2 | Secure signed draft preview | **NOT FOUND** | No preview route or signed-URL preview handler in `frontend/app` or `routes/*`; only environment-level noindex references |
| 6.3 | Content-readiness tooling | **VERIFIED** | `hexa:content-report` command + `App\Services\ContentCompletenessReport` |
| 6.4 | Internal-link suggestion | **VERIFIED (needs hardening)** | `App\Services\InternalLinkSuggester`; orphan/broken-link guards not audited |

## 7. Trust content & claim guardrails

| # | Finding | Status | Evidence |
|---|---------|--------|----------|
| 7.1 | Structured trust-claim approval model | **NOT FOUND** | No `TrustPage` / claim-verification model or migration. Public-claims governance is doc-only (`docs/content/public-claims-register.md`). |
| 7.2 | Company Settings distinguishes public vs legal name, markets vs presence | **PARTIALLY VERIFIED** | `CompanySetting` model + `company_settings` migration exist; field-level distinctions (markets-served vs physical-presence) not verified — see `docs/architecture` follow-up |

## 8. Operations & delivery

| # | Finding | Status | Evidence |
|---|---------|--------|----------|
| 8.1 | Health / readiness endpoints | **VERIFIED** | `routes/health.php` → `HealthController::live/ready`; frontend `app/api/health` |
| 8.2 | Revalidation (secure) | **VERIFIED** | `App\Services\RevalidationService` + `frontend/app/api/revalidate`; e2e `revalidate.spec.ts` |
| 8.3 | Mail deliverability strategy | **NOT FOUND → ADDED** | No checklist existed; `docs/infrastructure/mail-deliverability-checklist.md` added this sprint |
| 8.4 | Media / CDN strategy | **NOT FOUND → ADDED** | `docs/infrastructure/media-cdn-strategy.md` added this sprint |
| 8.5 | Uptime / alerting plan | **NOT FOUND → ADDED** | `docs/infrastructure/monitoring-and-alerting-plan.md` added this sprint |
| 8.6 | Global-readiness aggregate command | **NOT FOUND** | No `hexa:global-readiness` / `hexa:seo-audit`; documented as recommended next increment in `docs/operations/global-launch-readiness.md` |

## 9. Analytics

| # | Finding | Status | Evidence |
|---|---------|--------|----------|
| 9.1 | Single-provider-or-none analytics | **VERIFIED** | `frontend/components/site/analytics-script.tsx` renders nothing when unconfigured; `view-tracker.tsx`, `attribution.ts` present. Full event-contract test coverage not verified. |

---

## Summary of state changes made this sprint

- **RESOLVED:** route-list duplication (§1.2, §1.3) via the route registry + invariant test.
- **ADDED (docs):** IA map, sitemap policy, hreflang/canonical policy, international-URL strategy, legacy-route retirement matrix, media/CDN strategy, mail-deliverability checklist, monitoring/alerting plan, global-launch-readiness operations.
- **UNCHANGED / DEFERRED (documented, not implemented):** legacy env isolation (§2.4), in-app CSP enforcement (§5.1), `security.txt` (§5.4), secure CMS preview (§6.2), TrustPage/claim model (§7.1), JSON-LD entity graph (§4.1), `hexa:seo-audit` / `hexa:global-readiness` commands (§8.6). See the final report's "Remaining Technical Limitations".

No business, legal, security, or commercial claim was fabricated to close any row above.
