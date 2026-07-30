# CMS MFA Readiness

**Status: NOT IMPLEMENTED — documented production requirement (Option B).**

Multi-factor authentication for the Filament CMS (`/cms`) is not implemented in
this sprint. Implementing TOTP enrolment, confirmation, recovery codes, a
required-for-privileged-users policy, and a reset workflow safely is
disproportionate to a single hardening sprint and must not be faked. This
document records the recommended architecture and the residual risk instead.

## Current authentication posture (verified)

- Filament v4 panel at `/cms`, session auth with `AuthenticateSession`
  (session-fixation protection on login).
- Authorization enforced by `User::canAccessPanel()` → spatie roles
  (`admin`/`editor`). Authenticated ≠ authorized; covered by
  `tests/Feature/Security/FilamentAuthorizationTest.php` and
  `tests/Feature/Cms/ServiceResourceTest.php`.
- Filament login is rate-limited by the framework default.
- Session cookies: `http_only` true, `SameSite=lax`, `secure` via
  `SESSION_SECURE_COOKIE` (true in staging/production examples).

## Recommended MFA architecture

- **Mechanism:** TOTP (RFC 6238) — no SMS. Use a maintained, widely-used package
  compatible with Filament v4 (evaluate `filament/breezy` or an equivalent
  actively-maintained plugin) rather than hand-rolling. Do **not** add an
  abandoned package.
- **Enrolment:** authenticated user generates a secret, scans a QR, confirms one
  code before MFA is marked active.
- **Recovery codes:** one-time codes issued at enrolment, hashed at rest,
  regenerable.
- **Policy:** `MFA_REQUIRED=true` forces enrolment for privileged roles (`admin`)
  before they can use the panel; a grace period for existing users.
- **Reset/recovery:** admin-assisted reset with audit-logged action; lost-device
  path via recovery codes.
- **Tests:** enrolment, confirmation, challenge on login, recovery-code
  consumption, required-policy enforcement.

## Readiness configuration (added)

`MFA_REQUIRED` is defined as a readiness flag so its absence is a visible,
tracked production gap rather than a silent one. It is surfaced by the
global-readiness / security-readiness reporting as an unresolved requirement
until MFA ships. (This sprint does not enable enforcement — no MFA exists yet.)

## Residual risk

Privileged CMS access currently relies on password + rate limiting + role
authorization + (production) secure session cookies, without a second factor.
This is an accepted, documented risk until MFA is implemented. Owner and review
date to be assigned by the founder/engineering lead.
