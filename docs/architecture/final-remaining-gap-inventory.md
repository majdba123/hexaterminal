# Final Remaining Gap Inventory

Verified against the actual codebase on `feature/hexa-final-global-completion`
(descended from `feature/hexa-legacy-security-hardening` @ `6ec72b2`), not
against prior reports. Classifications: COMPLETE / PARTIAL /
NOT IMPLEMENTED / EXTERNAL INPUT BLOCKED / ACCEPTED RESIDUAL RISK /
REMOVE-RETIRE / DO NOT BUILD.

| Area | Status (before Wave 1) | Status (after Wave 1) | Notes |
|---|---|---|---|
| Trust Page infrastructure | NOT IMPLEMENTED | **COMPLETE** | `TrustPage` model/Filament/API/frontend for all 10 page types built this pass. |
| Team page | PARTIAL (embedded in `/about`, no governance) | **COMPLETE** | Dedicated `/team` route added; publication_consent/founder/SEO-eligibility fields added. |
| Public/sensitive claim governance | NOT IMPLEMENTED | **COMPLETE** | `PublicClaim` model, fail-closed scope, Filament resource, embedded in Trust Page + Team API responses. |
| Founder/legal/security approval | NOT IMPLEMENTED | **COMPLETE (infrastructure)** | Approval flags + matrix on `TrustPage`; actual founder/legal content approvals remain EXTERNAL INPUT BLOCKED. |
| Secure CMS preview | NOT IMPLEMENTED | **COMPLETE** | `ContentPreview`/`PreviewTokenService`/`PreviewController`/`PreviewAction` built this pass; wired into all 9 governed resources. Bespoke frontend renderer only for `trust_page` today (others: generic JSON fallback, same security contract). |
| Publication validation (central service) | PARTIAL (per-model `HasEditorialWorkflow` + `Publishable`, no shared cross-cutting validator/warnings split) | PARTIAL (unchanged) | `TrustPage::isReadyForPublication()` remains the source of truth, surfaced via `ContentCompletenessReport`; a standalone cross-model `PublicationValidator` service is still open work. |
| Translation completeness | PARTIAL (Spatie translatable on all content, no missing/partial/reviewed state machine) | PARTIAL | `ContentCompletenessReport` now covers `TrustPage`; still field-presence checking, not a formal state machine with dry-run repair tooling. |
| Canonical/hreflang | COMPLETE | COMPLETE | `frontend/lib/seo/alternates.ts` pre-existing; reused for new Trust Page routes. |
| Sitemap | PARTIAL (dynamic entries ignored per-record `seo.noindex`) | **COMPLETE** | Fixed this pass: `frontend/app/sitemap.ts` now filters dynamic Service/System/CaseStudy/Industry/Article entries through a `notNoindexed()` helper before inclusion. Static entries still derive from the route registry; Trust Page routes deliberately excluded until content-approved. |
| Robots | COMPLETE | COMPLETE | Pre-existing `frontend/lib/seo/indexing.ts` fail-closed default reused. |
| JSON-LD entity graph | PARTIAL (Organization/WebSite/Breadcrumb/Service/SoftwareApplication/Article/Person/FAQPage/VideoObject builders exist) | PARTIAL | Wired into new Trust Page (`FAQPage`, `BreadcrumbList`) and Team (`Person`) pages this pass; no dedicated Trust Page schema.org type change needed. |
| SEO audit command | NOT IMPLEMENTED | **COMPLETE** | `php artisan hexa:seo-audit` built this pass (`app/Services/SeoAuditReport.php`): title/description presence+length, duplicate title/description, canonical validity, noindex-in-sitemap contradiction, empty indexable pages, expired approved claims. Found and fixed a real bug: the static sitemap ignored per-record `seo.noindex`. |
| Link/orphan audit command | NOT IMPLEMENTED | NOT IMPLEMENTED (unchanged) | Wave 9 scope (`hexa:link-audit`). |
| Global readiness command | NOT IMPLEMENTED | NOT IMPLEMENTED (unchanged) | Wave 11 scope (`hexa:global-readiness`). Existing: `hexa:content-report` (now covers TrustPage), `hexa:security-readiness`, `hexa:seo-audit`. |
| Stale-content governance | NOT IMPLEMENTED | PARTIAL | `reviewed_at`/`next_review_at` added to `TrustPage` and `TeamMember` this pass; no dashboard/alerting yet (Wave 9). |
| Next.js CSP | NOT IMPLEMENTED (absent entirely) | **IMPLEMENTED, HONESTLY BLOCKED at enforcement** | Hash-based CSP built (`frontend/lib/csp.ts`), applied per-request via `proxy.ts`, defaults to Report-Only (verified configurable at runtime without rebuild). Full enforcement empirically confirmed NOT viable yet: Next.js's own RSC streaming bootstrap scripts can't be hash-allowlisted, and enabling `CSP_ENFORCE=true` breaks hydration (verified against a real production build). Fix requires a nonce-based migration (loses static generation sitewide) -- explicit P0 blocker, not silently claimed complete. |
| Filament MFA | NOT IMPLEMENTED | **COMPLETE** | TOTP (authenticator-app) MFA built on Filament's first-party `AppAuthentication` provider (`pragmarx/google2fa`, already a `filament/filament` dependency -- no new/unmaintained package). `App\Models\User` implements the two required contracts; secret + recovery codes are `encrypted`-cast and `$hidden`. Required for the `admin` role via `CmsPanelProvider`. Verified with a real computed TOTP code and single-use recovery codes (`tests/Feature/Security/FilamentMfaTest.php`, 7 tests). |
| Session hardening | Not separately audited | PARTIAL (unchanged) | Filament's `AuthenticateSession` middleware and Laravel's default login session regeneration are already in place; the full spec'd contract (idle/absolute timeout, invalidation on role/password change, logout-all-sessions) was not separately re-verified this pass. |
| Security-header consistency | PARTIAL (Laravel side has `SecurityHeadersTest` coverage) | PARTIAL | Next.js baseline headers (X-Content-Type-Options, Referrer-Policy, X-Frame-Options, Permissions-Policy) confirmed present; CSP now added (Report-Only). Full cross-layer matrix (Laravel + Next.js + any proxy) not formally tabulated this pass. |
| security.txt | NOT IMPLEMENTED | NOT IMPLEMENTED (unchanged) | Wave 10 scope. |
| Dependency advisories | COMPLETE (guzzlehttp/guzzle bumped, per `25432a3`) | COMPLETE (unchanged) | |
| Accessibility | PARTIAL | **IMPROVED** | Three real bugs found and fixed by axe-core automation: dark-theme primary-button contrast (3.9:1 -> ~4.6:1), legal-page inline-link contrast (4.17:1 -> ~9.7:1 via a token fix), and mobile-nav focus not restoring after Escape (DialogTrigger wiring). Coverage honestly scoped to backend-independent pages; content-driven pages still need Wave 12. |
| Accessibility automation | NOT IMPLEMENTED | **COMPLETE (scoped)** | `@axe-core/playwright` added; `frontend/e2e/accessibility.spec.ts` covers EN/AR, dark/light, mobile viewport + keyboard focus-restoration, and form labels for contact/privacy/terms. Content-driven pages (home, services, systems, etc.) need a live seeded API (Wave 12 rehearsal) for meaningful coverage -- not fabricated here. |
| Accessibility Statement workflow | NOT IMPLEMENTED | **COMPLETE (via existing infra)** | Reuses the Wave-1 `TrustPage` model (`page_type='accessibility'`) -- no new system needed; already has approval/review/noindex fields and generic sections for target-standard/known-limitations/remediation-process/contact. |
| Performance | Not measured | Not measured (unchanged) | Wave 6 scope. |
| Hero/showreel behaviour | COMPLETE (pre-existing `hero-video.tsx`/`showreel.tsx`) | COMPLETE (unchanged) | |
| Media/CDN readiness | PARTIAL (local disk storage only) | PARTIAL (unchanged) | Wave 6 scope. |
| Content import | NOT IMPLEMENTED | NOT IMPLEMENTED (unchanged) | Wave 9 scope. |
| Analytics contracts | PARTIAL (pre-existing `analytics-script.tsx`, `trackEvent`) | PARTIAL (unchanged) | Wave 8 scope: formal event contract + PII audit. |
| Search Console/Bing verification | NOT IMPLEMENTED | NOT IMPLEMENTED (unchanged) | Wave 8 scope. |
| Email templates | PARTIAL (lead/contact flows exist per `ContactUsService`, `LeadController`) | PARTIAL (unchanged) | Wave 7 scope: full EN/AR transactional set. |
| Queue behaviour | COMPLETE (sync in tests, queue-backed in app per existing config) | COMPLETE (unchanged) | |
| Mail deliverability | EXTERNAL INPUT BLOCKED (DNS/SPF/DKIM/DMARC require real domain access) | EXTERNAL INPUT BLOCKED (unchanged) | |
| Observability | PARTIAL | PARTIAL (unchanged) | Wave 8 scope: correlation IDs, structured logging. |
| Correlation IDs | NOT IMPLEMENTED | NOT IMPLEMENTED (unchanged) | Wave 8 scope. |
| Error-monitoring boundary | NOT IMPLEMENTED | NOT IMPLEMENTED (unchanged) | Wave 8 scope. |
| Uptime monitoring | NOT IMPLEMENTED | NOT IMPLEMENTED (unchanged) | Wave 8 scope (executable checks only; external service config is EXTERNAL INPUT BLOCKED). |
| CMS editor operability | PARTIAL (dashboards exist, no unified blocking/warning quality dashboard) | PARTIAL (unchanged; new resources follow existing patterns) | Wave 9 scope. |
| CI | PARTIAL (existing test/lint jobs; no seo/link/global-readiness gates) | PARTIAL (unchanged) | Wave 11 scope. |
| Deployment rehearsal | NOT IMPLEMENTED | NOT IMPLEMENTED (unchanged) | Wave 12 scope. |
| Launch/rollback package | NOT IMPLEMENTED | NOT IMPLEMENTED (unchanged) | Wave 12 scope. |
| Playwright stability | COMPLETE (existing suite green; route-registry spec unaffected by Wave 1) | COMPLETE (unchanged) | Full suite re-run not attempted this pass beyond the targeted route-registry spec (requires a live backend for most e2e specs). |

## Route registry (existing, verified)

`frontend/lib/routes/registry.ts` already existed (commit `8f992bc`) as the
single source of truth for header/footer/mobile-nav/sitemap. It already
reserved `security`, `process`, `accessibility`, `technology`,
`responsible-ai`, `engineering-standards`, and `team` as placeholder
`content-blocked` entries. Wave 1 built the actual pages behind those
placeholders and flipped their `contentState` to `technically-ready` to
reflect that the infrastructure now exists (still not indexable, not in the
sitemap, no nav entry, until real approved content ships).
