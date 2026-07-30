# Quality Gates

Every gate CI enforces, with the exact command, whether it is safe to run
locally, and how to debug it. CI runs these in `.github/workflows/ci.yml`.

## Backend (run from the repo root)

| Gate | Command | Modifies files? |
|---|---|---|
| Unit/feature tests | `vendor/bin/phpunit` | No (in-memory SQLite) |
| Style check | `vendor/bin/pint --test <paths>` | No |
| Style fix | `vendor/bin/pint <paths>` | **Yes** — rewrites files |
| Static analysis | `vendor/bin/phpstan analyse` | No |
| Route list | `php artisan route:list` | No |

### Tests

```bash
vendor/bin/phpunit
```

Uses `phpunit.xml` (in-memory SQLite, array cache/session). Read-only. 79 tests
covering security, the public API v1, content models, and the CMS.

### Pint (style)

The legacy pre-migration code carries deferred style debt, so the check is
scoped to the actively-maintained API/CMS layer plus this sprint's touched
files. The exact path list lives in `.github/workflows/ci.yml` (`PINT_PATHS`).

```bash
# Check (read-only) — mirrors CI:
vendor/bin/pint --test app/Http/Controllers/Api app/Http/Resources/V1 \
  app/Filament app/Providers/Filament \
  app/Console/Commands/MigrateLegacyContent.php \
  app/Models/Projects.php app/Models/Services.php app/Models/Imag_Progect.php \
  tests/Feature/Api tests/Feature/Cms tests/Feature/Content

# Fix (WRITES files) — run before committing if the check fails:
vendor/bin/pint <same paths>
```

> On Windows a bare `vendor/bin/pint` over the whole tree can be slow; always
> pass explicit paths.

### PHPStan / Larastan

```bash
vendor/bin/phpstan analyse --no-progress
```

Level 5 (see `phpstan.neon`). Read-only. Must report `[OK] No errors`.

## Frontend (run from `frontend/`)

| Gate | Command | Modifies files? |
|---|---|---|
| Install | `npm ci` | Writes `node_modules` only |
| Lint | `npm run lint` / `npx eslint . --max-warnings=0` | No |
| Typecheck | `npm run typecheck` / `npx tsc --noEmit` | No |
| Production build | `npm run build` | Writes `.next/` (gitignored) |
| E2E smoke | `npm run test:e2e` | Writes `playwright-report/`, `test-results/` (gitignored) |

### Lint & typecheck (read-only, no API needed)

```bash
npm ci
npm run typecheck
npx eslint . --max-warnings=0
```

### Production build (requires the API running)

The build fetches content from the Laravel API. Start a seeded API first:

```bash
# From repo root, against an isolated DB:
DB_CONNECTION=sqlite DB_DATABASE="$PWD/database/local.sqlite" \
ADMIN_EMAIL=admin@local ADMIN_PASSWORD=change-me-please \
  php artisan migrate:fresh --seed --force
php artisan hexa:migrate-legacy-content
php artisan db:seed --class=DemoContentSeeder --force   # systems/industries/articles fixtures
php artisan serve --host=127.0.0.1 --port=8000 &

# From frontend/:
API_URL=http://127.0.0.1:8000/api/v1/public \
NEXT_PUBLIC_SITE_URL=http://localhost:3000 \
NEXT_PUBLIC_ALLOW_INDEXING=true \
  npm run build
```

A compile-only pass is **not** proof of a working build — an unreachable API
fails static generation (only the legacy-redirect fetch is allowed to fail
safe).

### E2E smoke (Playwright)

```bash
# 1. Build the frontend (above) and keep the seeded API running.
# 2. Then:
API_URL=http://127.0.0.1:8000/api/v1/public \
NEXT_PUBLIC_SITE_URL=http://localhost:3000 \
  npm run test:e2e
```

`playwright.config.ts` starts the built app (`npm run start`) as its web server;
you supply the running, seeded Laravel API. The suite runs **serially**
(`workers: 1`) because `php artisan serve` is single-threaded. 13 tests cover:
EN/AR home + RTL, locale switch, theme toggle + persistence, mobile nav,
system/case-study detail rendering, showreel modal focus, lead submission, and
localized 404s.

Debugging: `npx playwright test --headed` (watch it run),
`npx playwright test --debug` (step through), `npx playwright show-report`
(open the last HTML report), `npx playwright test <file> -g "<title>"` (one test).

## Environment dependencies

- **PHP 8.2+, Composer 2** for backend gates.
- **Node 20+, npm 10+** for frontend gates.
- **SQLite** extension for the test/build database.
- **Chromium** via `npx playwright install chromium` for E2E (CI uses
  `--with-deps`).

## How CI runs them

Three jobs in `.github/workflows/ci.yml`:

1. **backend** — composer install → migrate (isolated SQLite) → PHPUnit → Pint
   check → PHPStan.
2. **frontend-checks** — `npm ci` → `tsc --noEmit` → `eslint --max-warnings=0`.
   Fast; no API.
3. **e2e** — boot the seeded Laravel API, wait for `/home` readiness (fails
   clearly if it never comes up), build the frontend against it, install
   Chromium, run Playwright. This job is the authoritative "production build
   against a running API" proof. The Playwright HTML report is uploaded as an
   artifact.

## Common failures

| Symptom | Likely cause | Fix |
|---|---|---|
| Build: `ECONNREFUSED` during static generation | API not running / wrong `API_URL` | Start the seeded API; check `API_URL`. |
| `pint --test` fails | Style drift in a governed file | Run `vendor/bin/pint <paths>` to fix, review, recommit. |
| PHPStan: `undefined property` on a model | Missing `@property` / relation generic | Annotate the model or its relation return type. |
| E2E: many tests time out | Parallel workers vs single-threaded API | Ensure `workers: 1` (already set). |
| E2E: lead-form test fails locally on reruns | API `throttle:5,1` exhausted | Wait ~1 min between full local runs. |
| E2E: unknown-slug test returns 200 not 404 | A `loading.tsx` re-introduced streaming | Keep `notFound()` paths non-streamed. |
