# Current System Audit — Hexa Terminal (pre-transformation baseline)

Audited: 2026-07-14, commit `958a31d` (baseline import). This is the reality the transformation started from.

## Verdict
Production/company readiness at baseline: **38/100** — a competent portfolio site, not a company platform.

## Stack (verified)
- **Laravel 10** (`composer.json`: `laravel/framework ^10.10`), PHP `^8.1` (local: 8.2.12)
- Blade + Bootstrap 5 + custom CSS design system inlined in `resources/views/layouts/website.blade.php` (~1,190 lines)
- Vite 5 (`vite.config.js`), but public site loads only `resources/css/website.css`; Axios + AOS vendored in `public/vendor/`
- **Rendering model:** SSR shells + client-side Axios fetch from public API routes; all content client-rendered via `innerHTML`
- Sanctum tokens + session auth; custom `admin` middleware = `type == 1` check (`app/Http/Middleware/AdminMiddleware.php`)
- Custom admin panel at `/admin` (Blade + Axios CRUD over the same public API)
- EN/AR client-side dictionary i18n with RTL (`layouts/website.blade.php:936-1099`); no server locale, no hreflang
- Dark/light theme via CSS variables + no-flash bootstrap script (high quality — preserved)

## Content models (baseline)
`Team`, `Services`, `Projects`, `About_Us`, `Contact_Us`, `FAQ`, `Review`, `Video`, `Fetures_Project` (sic), `Imag_Progect` (sic), `User`. Single-language columns, ID-based URLs, no slugs, no publish state, no SEO fields.

## Key gaps found (all verified with file evidence — see git history of this branch)
1. **Security (fixed in Stage 0):** default admin credentials in seeder; unmoderated public review reads + self-approvable store + unescaped `innerHTML` = stored XSS; no rate limiting; exception details in 500 bodies; unhardened `.*` storage route; no `.env.example`. See `docs/security/security-hardening.md`.
2. **SEO:** no canonical/OG/Twitter/JSON-LD/sitemap/hreflang; single hardcoded meta description; ID URLs; client-rendered content invisible to crawlers; `robots.txt` allows all but names no sitemap.
3. **CMS:** hand-rolled admin with no roles, media library, moderation workflow, drafts, or audit trail.
4. **Content architecture:** portfolio-shaped (Team/Projects/Reviews/FAQ), cannot represent SaaS/CRM/ERP/AI offers as sellable entities.
5. **Branding:** site built on gold (`--accent: #D4AF37`) contradicting the official blue/charcoal logo (`icons/logo.svg`: `#3663D8`, `#77BEFF`, `#00D1FF`).
6. **Hygiene:** committed ThemeForest template (`code_k/`), dead social-login controllers, `laravel/ui` scaffolding, misspelled models, junk root files (`it`, `php`, `your`, `satisfiable`, `pie.phar`), stub-only tests.

## Media assets (verified with ffprobe)
- `Hexa Terminal Intro.mp4` — 1920×1080, 16:9, 8.0s, H.264 30fps, 2.7 MB, **silent**
- `Hexa Terminal Reels.mp4` — 1080×1920, 9:16, 29.0s, H.264 30fps, 5.7 MB, **silent**
- Logo — blue rounded-hex `>_` mark + charcoal wordmark; SVG variant at `icons/logo.svg`

## What was worth preserving
Theme system, EN/AR dictionary content, controller→service layer, response caching patterns, `Review::scopeApproved`, zero-CDN asset policy, perf-conscious CSS (`content-visibility`, rAF scroll handler, reduced-motion).

## Git reality at start
The project was **not a git repository** (untracked directory inside the user's home-dir repo). A nested repo was initialized; the entire pre-existing state is preserved in baseline commit `958a31d` on `main`; all transformation work happens on `feature/hexa-nextjs-professional-platform`.
