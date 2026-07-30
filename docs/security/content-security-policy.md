# Content-Security-Policy

Applied by `App\Http\Middleware\SecurityHeaders` from `config/security.php` to
every Laravel-origin response. The Next.js public frontend has its own header
layer (`frontend/next.config.ts`); this document governs the Laravel origin
(CMS `/cms`, legacy surfaces, API).

## Closure-pass decision (verified end-to-end): Option B — Report-Only globally

A live compatibility check was run against the Next.js public site, Filament
login/dashboard, theme initialization, and legacy compatibility pages (see
`docs/security/legacy-security-baseline.md` for the isolated-environment setup).
Findings:

- **Laravel origin** (CMS, API, legacy surfaces): emits
  `Content-Security-Policy-Report-Only` with the directive set below, verified
  present on `/api/v1/public/*`, `/api/health`, `/cms/login`, and legacy
  compatibility responses. Never emits an enforcing `Content-Security-Policy`
  header by default.
- **Next.js public origin**: emits its own baseline headers
  (`X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`,
  `Permissions-Policy` — see `frontend/next.config.ts`) but **currently ships no
  CSP header at all, Report-Only or enforcing**. This is a real, newly-verified
  gap, not a claim of coverage. The two origins never emit a contradictory
  pair — one origin is silent on CSP, the other is Report-Only — so there is no
  header conflict, but the public site currently has zero CSP protection.

**Decision: keep CSP Report-Only on the Laravel origin (Option B)**, and do not
add an enforcing (or Report-Only) CSP to the Next.js public origin in this
closure pass. Reasoning: this pass is a verification/closure sprint, not a new
security-architecture sprint (out of scope per its own instructions); adding a
public-site CSP is a net-new control, not a fix to a discovered defect, and
deserves its own reviewed change with its own compatibility check (showreel/video
embeds, analytics when configured, Turnstile when configured, theme-init inline
script). This is recorded as a **residual risk**, not silently deferred — see
`docs/security/production-security-checklist.md`.

CSP is shipped as **`Content-Security-Policy-Report-Only`** by default on the
Laravel origin. It is **not enforced** anywhere. Enforcement is opt-in via
`CSP_ENFORCE=true` and remains blocked by the Filament/Alpine issues below.

This is a deliberate, documented state — not a claim of completed enforcement.

## Directives

```
default-src 'self';
script-src 'self' 'unsafe-eval' 'unsafe-inline';
style-src 'self' 'unsafe-inline';
img-src 'self' data: blob: https:;
font-src 'self' data:;
connect-src 'self';
media-src 'self' https:;
object-src 'none';
base-uri 'self';
form-action 'self';
frame-ancestors 'self';
frame-src 'none';
upgrade-insecure-requests   (production + HTTPS only)
```

No wildcard `default-src *`. `object-src`/`frame-src` are `'none'`; `base-uri`
and `form-action` are restricted to `'self'`.

## Why enforcement is blocked (the exact remaining work)

1. **`'unsafe-eval'` — Filament/Alpine.js.** Alpine evaluates directive
   expressions with `new Function()`, which CSP treats as eval. Enforcing
   without `'unsafe-eval'` breaks the entire CMS. Removing it requires either
   Alpine's CSP-friendly build (proven against Filament v4) or serving the CMS
   under a separate, looser policy from the public API.
2. **`'unsafe-inline'` in `script-src`/`style-src` — Filament bootstrap + Blade.**
   Filament injects inline scripts/styles without nonces. A nonce/hash strategy
   must be threaded through Filament's rendering before this can be dropped.

Until (1) and (2) are resolved, enforcing the policy would break the CMS, so we
collect violations in Report-Only mode instead.

## Rollout plan

1. **Now:** Report-Only in all environments; `hexa:security-readiness` reports
   CSP-not-enforced as an informational **P2** (never a launch P0).
2. **Next:** wire a `report-uri`/`report-to` endpoint and observe real
   violations from the CMS and public flows.
3. **Then:** resolve the Alpine `unsafe-eval` and inline-script blockers
   (CSP-friendly Alpine or per-surface policy split), tighten directives, and
   only then set `CSP_ENFORCE=true`.

## Effective header matrix (verified live, isolated environment)

| Surface | Header owner | Effective CSP | Verified result |
|---------|-------------|---------------|------------------|
| Next.js public page (`/en`) | `frontend/next.config.ts` | **none** | curl: no `Content-Security-Policy*` header present |
| Laravel versioned API (`/api/v1/public/*`) | `SecurityHeaders` middleware | Report-Only | header present, directives as documented |
| Laravel health (`/api/health`) | `SecurityHeaders` middleware | Report-Only | header present |
| Filament login (`/cms/login`) | `SecurityHeaders` middleware | Report-Only | header present; session cookie `httponly; samesite=lax` |
| Legacy compatibility page (enabled) | `SecurityHeaders` + `LegacySurface` | Report-Only | header present alongside `X-Robots-Tag: noindex, nofollow` |
| Legacy route (disabled) | `SecurityHeaders` (still runs pre-abort) | Report-Only | header present on the 404 response |
| Estimate result page (`/en/estimate/{uuid}`) | Next.js (`lib/seo/indexing.ts`) | none (indexing controlled via `<meta name="robots">`, not a header, since `NEXT_PUBLIC_ALLOW_INDEXING=true` globally) | verified: `<meta name="robots" content="noindex, nofollow"/>` present in body |

No contradictory headers were found between the two origins (they are separate
hosts in this deployment topology; the reverse proxy is not yet in the picture
for this local verification). HSTS was not asserted here since it requires a
genuinely secure request (see `production-security-checklist.md`).
Cross-Origin-Opener-Policy/Cross-Origin-Resource-Policy (`same-origin`) did not
break the versioned API or Filament in this check; no legitimate cross-origin
media/API consumption was exercised against `same-origin` CORP in this pass —
flagged as worth an explicit check before enabling a CDN media origin (see
`docs/infrastructure/media-cdn-strategy.md`).

## Tests

`tests/Feature/Security/SecurityHeadersTest.php` asserts: Report-Only by
default, no enforcing header by default, enforce-mode flips the header via
config, and the directive set contains the hardening directives and no wildcard.
