# Production Security Checklist

Run `php artisan hexa:security-readiness` (exits non-zero on any P0). This
checklist is the human companion; the command is the automated gate.

## P0 — must pass before production

- [ ] `APP_DEBUG=false`.
- [ ] `LEGACY_PUBLIC_WEB_ENABLED=false`.
- [ ] `LEGACY_ADMIN_ENABLED=false`.
- [ ] `LEGACY_API_ENABLED=false`.
- [ ] `SESSION_SECURE_COOKIE=true`.
- [ ] `CORS_ALLOWED_ORIGINS` is an explicit allow-list (never `*`).
- [ ] No `.env` or private key committed (CI-gated).

## P1 — required for a healthy production

- [ ] `APP_URL` set to the real production URL.
- [ ] `HSTS_ENABLED=true` (HTTPS production) — emitted only on secure requests.
- [ ] `REVALIDATION_SECRET` set when `REVALIDATION_ENABLED=true`.
- [ ] `session.http_only` true; `SameSite` not `none` unless `secure`.
- [ ] Trusted proxy configured so `isSecure()` is correct behind the LB.

## P2 — tracked, not release-blocking

- [ ] CSP enforced (currently Report-Only on the Laravel origin; **the Next.js
      public origin ships no CSP at all** — verified this closure pass. See
      `docs/security/content-security-policy.md` for the effective header
      matrix and the Option B decision).
- [ ] CMS MFA (currently not implemented — see
      `docs/security/cms-mfa-readiness.md`).
- [x] `guzzlehttp/guzzle`/`psr7` advisories — **resolved** this closure pass
      (minor bump, `composer audit` now clean). `next`-bundled `sharp`/`postcss`
      advisories remain accepted-risk (see
      `docs/security/dependency-advisory-policy.md`).

## Security headers (config/security.php)

Applied to every Laravel-origin response by `SecurityHeaders` middleware:
`X-Content-Type-Options`, `Referrer-Policy`, `X-Frame-Options`,
`Permissions-Policy`, `Cross-Origin-Opener-Policy`,
`Cross-Origin-Resource-Policy`, CSP (Report-Only), HSTS (secure prod only).
Avoid setting the same headers again at the reverse proxy to prevent duplicates.

## HSTS deployment requirement

- Only enable behind verified end-to-end HTTPS.
- Do not add `includeSubDomains` until every subdomain is HTTPS-ready.
- Do not add `preload` without explicit approval (hard to reverse).

## Cutover sequence (legacy retirement)

1. Confirm Next.js frontend + Filament `/cms` fully cover public + admin needs.
2. Keep `LEGACY_*_ENABLED=false` in staging/production (default).
3. Enable a single legacy surface locally only if compatibility work is needed.
4. Redirects for replaced public URLs are owned by the Next.js edge
   (`frontend/next.config.ts` + DB Redirect table) — see
   `docs/migration/legacy-redirect-map.md`.
5. Rollback is configuration-only (flip the flag); no code was deleted.
