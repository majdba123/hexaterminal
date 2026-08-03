# HexaTerminal Environment Baseline

## 1. Executive summary

`BASE-001A` established a read-only repository and deployment baseline for
HexaTerminal at commit `73e8e1b441cf365615dbc88cdcc1f5f987bbfd18` on branch
`main`. Repository identity, framework/package versions, deployment workflow,
health architecture, runtime driver defaults, and public health/page checks were
documented from repository files and safe read-only commands. Production-host
runtime evidence still requires manual execution of the committed collector
script on `/var/www/hexaterminal`.

## 2. Baseline scope

- In scope: repository identity, manifests, committed deployment files, health
  architecture, runtime driver defaults, production verification procedure.
- Out of scope: deployment changes, production fixes, route auditing, CMS data,
  content review, Lighthouse, screenshots, form testing, migrations, restarts.

## 3. Repository identity

- Git top-level: `C:/Users/impos/Desktop/Projects/hexaterminal`
- Current branch: `main`
- Current commit: `73e8e1b441cf365615dbc88cdcc1f5f987bbfd18`
- Working tree during baseline generation: clean
- Remote `origin`: `https://github.com/majdba123/hexaterminal.git`
- Latest commit metadata:
  - Author: `Majd Bayer <137396563+majdba123@users.noreply.github.com>`
  - AuthorDate: `2026-08-03 21:50:48 +0300`
  - Commit subject: `hjk`
- Current commit equals known production baseline commit `73e8e1b`: yes

## 4. Known production identity

Owner-confirmed production facts, not independently derived from private server
state:

- Production branch: `main`
- Production repository path: `/var/www/hexaterminal`
- Production frontend path: `/var/www/hexaterminal/frontend`
- Production frontend origin: `https://hexaterminal.com`
- Production API origin: `https://api.hexaterminal.com`
- Next.js systemd service: `hexa-frontend`
- Successful GitHub Actions run: `Run #11`
- Successful deployment commit: `73e8e1b`
- Successful run duration: `4m 6s`
- Validation job succeeded: owner-confirmed
- Deployment job succeeded: owner-confirmed
- Working public URLs confirmed by owner:
  - `https://hexaterminal.com/en`
  - `https://hexaterminal.com/api/health`
  - `https://api.hexaterminal.com/api/health/ready`
- Existing non-blocking GitHub Actions warning: Node.js runtime deprecation for
  action internals

## 5. Application architecture

- Backend: Laravel 12 application at repository root
- Frontend: Next.js 16 App Router application under `frontend/`
- CMS: Filament 4 mounted under `/cms`
- Public versioned API: `routes/api_v1.php` served under `/api/v1/public/*`
- Legacy API surface: `routes/api.php` served under `/api/*`, guarded by
  `legacy:api`
- Health routes: `routes/health.php` served under `/api/health` and
  `/api/health/ready`
- Route registration: `app/Providers/RouteServiceProvider.php`
- Revalidation: Laravel `App\Services\RevalidationService` calls Next.js
  `frontend/app/api/revalidate/route.ts`

## 6. Technology versions

### Repository-declared requirements

- PHP requirement: `^8.2` from `composer.json`
- Laravel framework package: `^12.0` from `composer.json`
- Filament package: `^4.0` from `composer.json`
- Sanctum: `^4.0`
- Predis: `^3.0`
- Laravel UI: `^4.6`
- PHPUnit: `^11.0`
- Larastan: `^3.0`
- Pint: `^1.0`
- Node requirement in docs: `20.x or 22.x LTS`
- npm requirement in docs: `10+`

### Locked or package versions from manifests

- Laravel runtime: `12.64.0` from `php artisan --version`
- Next.js: `16.2.10` from `frontend/package.json`
- React: `19.2.4` from `frontend/package.json`
- React DOM: `19.2.4` from `frontend/package.json`
- Filament package line: `filament/filament:^4.0`
- Frontend i18n: `next-intl:^4.13.2`
- Frontend lint stack: `eslint:^9`, `eslint-config-next:16.2.10`
- Frontend typecheck: `typescript:^5`
- Root build tool: `vite:^5.0.0`, `laravel-vite-plugin:^1.0.0`, `sass:^1.56.1`
- Frontend CSS build tool: `@tailwindcss/postcss:^4`, `tailwindcss:^4`
- Frontend optional Linux binaries explicitly present in manifest:
  `@tailwindcss/oxide-linux-x64-gnu:4.3.2`,
  `lightningcss-linux-x64-gnu:1.32.0`
- Frontend helper pin: `@swc/helpers:0.5.23`

### Locally installed runtime versions

- PHP: `8.3.30`
- Composer: `2.10.0`
- Node.js: `24.11.0`
- npm: `11.6.1`

### Production runtime versions

- PHP runtime version: `NOT VERIFIED`
- Composer runtime version: `NOT VERIFIED`
- Node runtime version: `NOT VERIFIED`
- npm runtime version: `NOT VERIFIED`
- Nginx runtime version: `NOT VERIFIED`

## 7. CI/CD architecture

- Workflow file path: `.github/workflows/deploy-production.yml`
- Workflow name: `Deploy Production`
- Automatic trigger branch: `main`
- Manual trigger: `workflow_dispatch`
- Permissions: `contents: read`
- Concurrency group: `hexaterminal-production-deploy`
- Cancel in progress: `false`
- Jobs:
  - `validate`
  - `deploy`
- Deployment dependency: `deploy.needs: validate`
- PHP setup action: `shivammathur/setup-php@v2`
- PHP version in workflow: `8.3`
- Node setup action: `actions/setup-node@v4`
- Node version in workflow: `22`
- SSH deployment action: `appleboy/ssh-action@v1.2.0`

## 8. Deployment sequence

Repository-verified current sequence from `deploy-production.yml`:

1. Validate branch push or manual dispatch.
2. Install backend dependencies in CI.
3. Generate a temporary CI `.env`.
4. Install root Node dependencies and build Laravel Vite assets.
5. Run database setup in CI.
6. Run Laravel validation suite in CI.
7. Install frontend dependencies in CI.
8. Start local Laravel validation server in CI.
9. Run frontend typecheck, lint, and build in CI.
10. SSH to production.
11. Change to `/var/www/hexaterminal`.
12. Mark the repo as a safe Git directory.
13. Fetch and hard-reset to `origin/main`.
14. Run Composer production install.
15. Change to `/var/www/hexaterminal/frontend`.
16. Verify `package.json`, `package-lock.json`, and `.env.production`.
17. Verify `.env.production` contains the expected production API/site URLs.
18. Copy `.env.production` to `.env`.
19. Install frontend dependencies and build.
20. Return to Laravel root.
21. Run `php artisan migrate --force`.
22. Run `php artisan optimize:clear`, `php artisan optimize`,
    `php artisan queue:restart`.
23. Restart `hexa-frontend`.
24. Verify `hexa-frontend` is active.
25. Run `nginx -t`.
26. Reload Nginx.
27. Check backend readiness URL.
28. Check frontend health URL.
29. Check public English page URL.

## 9. Health-check architecture

### Laravel liveness

- Route: `GET /api/health`
- Source route file: `routes/health.php`
- Route registration: `RouteServiceProvider` under `/api`
- Handler: `App\Http\Controllers\HealthController::live`
- Response body shape:

```json
{"status":"ok","service":"hexaterminal-backend"}
```

- Expected status: `200`
- Cache control: `no-store`
- Dependency checks: none
- Sensitive data exclusion: explicit minimal payload only
- Existing tests: `tests/Feature/HealthTest.php`

### Laravel readiness

- Route: `GET /api/health/ready`
- Source route file: `routes/health.php`
- Handler: `App\Http\Controllers\HealthController::ready`
- Success response shape:

```json
{"status":"ready","service":"hexaterminal-backend","checks":{"database":"ok","cache":"ok"}}
```

- Failure response shape:

```json
{"status":"not_ready","service":"hexaterminal-backend","checks":{"database":"failed","cache":"ok|failed"}}
```

- Expected status: `200` or `503`
- Cache control: `no-store` (observed live response also included `private`)
- Dependency checks:
  - database via `select 1`
  - cache put/get/forget cycle using a temporary key
- Existing tests: `tests/Feature/HealthTest.php`
- Sensitive data exclusion: no env values, keys, credentials, or traces in
  response payload

### Next.js health

- Route: `GET /api/health`
- Source handler: `frontend/app/api/health/route.ts`
- Handler type: App Router route handler
- Response body shape:

```json
{"status":"ok","service":"hexaterminal-frontend"}
```

- Expected status: `200`
- Cache control: `no-store`
- Dependency checks: none
- Sensitive data exclusion: no env values, URLs, tokens, or filesystem paths
- Existing automated coverage: no dedicated frontend unit test found; route is
  validated indirectly by build/typecheck and live GET

### Public GET verification performed

- `https://hexaterminal.com/api/health`
  - Status: `200`
  - Final URL: unchanged
  - Content type: `application/json`
  - Response time: `17621 ms`
  - Sanitized body: `{"status":"ok","service":"hexaterminal-frontend"}`
- `https://api.hexaterminal.com/api/health/ready`
  - Status: `200`
  - Final URL: unchanged
  - Content type: `application/json`
  - Response time: `15518 ms`
  - Sanitized body:
    `{"status":"ready","service":"hexaterminal-backend","checks":{"database":"ok","cache":"ok"}}`
- `https://hexaterminal.com/en`
  - Status: `200`
  - Final URL: unchanged
  - Content type: `text/html; charset=utf-8`
  - Response time: `10904 ms`

## 10. Non-secret runtime configuration matrix

Repository-default runtime driver names:

| Concern | Repository default | Source |
|---|---|---|
| Database default driver | `mysql` | `config/database.php` |
| Supported DB drivers | `sqlite`, `mysql`, `pgsql`, `sqlsrv` | `config/database.php` |
| Redis client | `predis` | `config/database.php` |
| Cache default driver | `file` | `config/cache.php` |
| Queue default driver | `sync` | `config/queue.php` |
| Session default driver | `file` | `config/session.php` |
| Mail default driver | `smtp` | `config/mail.php` |
| Filesystem default disk | `local` | `config/filesystems.php` |
| Logging default channel | `stack` | `config/logging.php` |
| Broadcasting default driver | `null` | `config/broadcasting.php` |
| Security headers config source | `config/security.php` | repository |
| CORS config source | `config/cors.php` | repository |
| Revalidation config source | `config/revalidation.php` | repository |

Whitelisted production collector keys:

- `APP_ENV`
- `APP_DEBUG`
- `database.default`
- `cache.default`
- `queue.default`
- `session.driver`
- `mail.default`
- `filesystems.default`
- `logging.default`

## 11. Production services

- Frontend service name: `hexa-frontend`
- Read-only systemd query to use later:

```bash
systemctl show hexa-frontend --property=ActiveState --property=SubState --property=FragmentPath --no-pager
```

- Committed service example: `deploy/staging/systemd/hexa-frontend.service`
- Committed Nginx examples:
  - `deploy/staging/nginx/frontend-staging.conf`
  - `deploy/staging/nginx/api-staging.conf`

## 12. Known log locations and commands

- Laravel application log path: `storage/logs/laravel.log`
- Safe later commands:

```bash
journalctl -u hexa-frontend --since "30 minutes ago" --no-pager
tail -n 100 storage/logs/laravel.log
```

No application log content was collected for this baseline.

## 13. Verified facts

Facts verified directly from repository files or safe local/public commands:

- Repository branch is `main`
- Current commit is `73e8e1b441cf365615dbc88cdcc1f5f987bbfd18`
- Working tree was clean during execution
- Remote origin is GitHub without embedded credentials
- Laravel runtime available locally is `12.64.0`
- PHP available locally is `8.3.30`
- Composer available locally is `2.10.0`
- Node available locally is `24.11.0`
- npm available locally is `11.6.1`
- Only workflow in `.github/workflows/` is `deploy-production.yml`
- Current production workflow includes validation and deployment jobs
- Current deployment workflow targets `/var/www/hexaterminal`
- Current deployment workflow targets `/var/www/hexaterminal/frontend`
- Current deployment workflow hardcodes:
  - `https://api.hexaterminal.com/api/health/ready`
  - `https://hexaterminal.com/api/health`
  - `https://hexaterminal.com/en`
- Laravel health routes exist and are registered without the general API
  throttle
- Next.js health route exists at `frontend/app/api/health/route.ts`
- Revalidation mechanism exists in both Laravel and Next.js code
- Public GET requests to all three owner-confirmed URLs returned `200`

## 14. Repository-only facts

- Repository declares Laravel 12, Filament 4, Next.js 16, React 19
- Root app uses Vite for Laravel assets
- Frontend build uses Tailwind CSS 4 and `@tailwindcss/postcss`
- Queue restart is part of the production workflow
- Nginx validation is part of the production workflow
- Production deploy currently uses `npm install --include=optional`, not
  `npm ci`
- CI validation currently creates a temporary `.env` file instead of copying
  `.env.example`
- Committed staging docs still describe an older frontend health contract that
  reported API reachability; the current repository handler is now a minimal
  liveness response

## 15. Owner-confirmed facts

- Production frontend origin is `https://hexaterminal.com`
- Production API origin is `https://api.hexaterminal.com`
- Production baseline commit is `73e8e1b`
- Successful GitHub Actions run is `Run #11`
- Run #11 status was success
- Run #11 total duration was `4m 6s`
- Validation and deployment jobs succeeded
- The three public production URLs are working
- There is a non-blocking GitHub Actions warning regarding Node.js runtime
  deprecation for action internals

## 16. Production checks still required

- Production-host PHP version
- Production-host Composer version
- Production-host Node version
- Production-host npm version
- Production-host Nginx version and current `nginx -t` output
- Production-host `hexa-frontend` ActiveState/SubState/FragmentPath
- Production-host whitelisted Laravel runtime driver values
- Production-host git branch/commit/working-tree status
- Production-host known log file presence

These require manual execution of:

```bash
bash scripts/baseline/collect-production-environment.sh
```

from `/var/www/hexaterminal`.

## 17. Unknowns

- Exact production OS distribution and version
- Exact production PHP/Composer/Node/npm runtime versions
- Exact production Nginx version
- Exact production `hexa-frontend` unit file path and live state
- Whether production currently has any local uncommitted changes
- Whether production currently has cached Laravel configuration or routes
- Exact GitHub Actions warning text and affected action versions

## 18. Risks

- Public readiness and health URLs are slow enough in the current sampled GETs
  to merit monitoring, even though they returned `200`
- Repository docs and current workflow/health implementation are not perfectly
  synchronized in all places
- Production runtime facts remain partially unverified until the collector is
  run on the live server
- Current workflow uses `npm install --include=optional`; that is repository
  fact, but it is less reproducible than a fully lockfile-stable `npm ci`
  baseline

## 19. Commands executed

```bash
git rev-parse --show-toplevel
git branch --show-current
git rev-parse HEAD
git status --short
git remote -v
git log -1 --date=iso --pretty=fuller
php --version
composer --version
php artisan --version
node --version
npm --version
Invoke-WebRequest https://hexaterminal.com/api/health
Invoke-WebRequest https://api.hexaterminal.com/api/health/ready
Invoke-WebRequest https://hexaterminal.com/en
```

Additional repository file inspections were performed read-only.

## 20. Files created or modified

Created for `BASE-001A`:

- `docs/baseline/HexaTerminal_Environment_Baseline.md`
- `docs/baseline/HexaTerminal_Environment_Baseline.json`
- `docs/baseline/evidence/PRODUCTION_COLLECTION_REQUIRED.md`
- `scripts/baseline/collect-production-environment.sh`

No application code, routes, workflows, or deployment files were modified in
this task.

## 21. Completion checklist

- Repository identity documented
- Technology versions documented
- CI/CD architecture documented
- Deployment sequence documented
- Health architecture documented
- Runtime driver defaults documented
- Public GET verification recorded
- Production collector script created
- Manual production evidence instructions created
- Machine-readable JSON created
- Task remains open

## 22. Final verdict for `BASE-001A`

`READY_FOR_OWNER_REVIEW`

Repository evidence is complete enough for owner review. Production-host facts
that cannot be verified from repository files are clearly separated and require
manual collector execution.
