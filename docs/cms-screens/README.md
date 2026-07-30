# CMS screen captures

Two ways to capture the Filament panel at `/cms`. **Read the limitation on the
first one before relying on it.**

---

## A. Static capture (no login) — panel chrome only

```bash
# 1. app must be running:  php artisan serve --port=8010 --no-reload
CAPTURE_CMS=1 CAPTURE_CMS_URL=http://127.0.0.1:8010 php artisan test --filter=CaptureCmsHtmlTest
# 2. then:
cd frontend && npx playwright test e2e/tools/screenshot-cms-html.ts --config=e2e/tools/capture-cms.config.ts
```

Step 1 (`tests/Feature/Cms/CaptureCmsHtmlTest.php`) renders all 38 screens
server-side via Laravel's `actingAs()` — no credentials needed — and writes the
real server HTML to `html/`. **That HTML is fully faithful** and is the more
useful artifact of the two: it is exactly what the panel returns, and diffing it
is how the two create-page 500s (`pricing-profiles`, `estimator-versions`) were
found.

Step 2 turns those files into PNGs.

### What the PNGs do and do not show

| Faithful | Not rendered |
| --- | --- |
| Sidebar: groups, order, icons, labels | Table rows and column headers |
| Topbar, search, user avatar | Form fields inside each create page |
| Overall layout, theme, typography | Anything needing a Livewire round trip |

**Why.** Filament v4 reveals its main region through Alpine, and Alpine ships
*inside* Livewire — which cannot boot against static HTML because there is no
live component on the server. The markup is all present (`wire:snapshot`
contains the rendered table, `h1` reads correctly, the region even reports a
936px height), but it stays visually transparent. Forcing it visible was tried
and rejected: stripping `x-cloak` made closed dropdown menus render open, i.e.
it fabricated UI state that does not exist.

So these PNGs are good for reviewing **navigation and layout** and nothing more.
They were enough to verify the sidebar grouping and the per-resource icons, and
they are not a substitute for B.

---

## B. Live capture (one interactive login) — the real thing

This drives the actual panel, so tables, forms, and every interactive state are
real.

### 1. Create an admin account (first time only)

`database/seeders/UsersTableSeeder.php` deliberately refuses to invent
credentials — there are no default email/password pairs in this repo. Set your
own in `.env`:

```
ADMIN_EMAIL=you@example.com
ADMIN_PASSWORD=at-least-twelve-characters
```

```bash
php artisan db:seed --class=RolesSeeder && php artisan db:seed --class=UsersTableSeeder
```

### 2. Save a session (interactive, once)

Opens a browser. Sign in — including the TOTP step, which is **required** for
admin accounts (`CmsPanelProvider::multiFactorAuthentication`) — then close the
window.

```bash
cd frontend && npx playwright open --save-storage=e2e/tools/cms-auth.json http://127.0.0.1:8010/cms
```

`cms-auth.json` is a **live admin session** and is gitignored. Never commit it.

### 3. Capture

```bash
cd frontend && npx playwright test e2e/tools/capture-cms.ts --config=e2e/tools/capture-cms.config.ts
```

Routes are discovered from the sidebar at runtime, so the set cannot drift out
of sync with the panel. Fails fast if the session has expired rather than
filling this folder with screenshots of the login page.

---

## Known content gap

`DemoContentSeeder` creates `Article`, `CaseStudy`, `Industry` and `System`
records but **no `Service`** — so the Services list is empty in every capture,
and on the public site too. That is also why `php artisan hexa:seo-audit`
reports `service: 100/100`: there are zero published services to audit.
