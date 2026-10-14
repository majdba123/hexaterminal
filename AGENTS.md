# AGENTS.md

Operating guide for AI agents and contributors working on HexaTerminal: issues, changes and production deployments. `frontend/AGENTS.md` adds Next.js-specific rules (this is **not** the Next.js you know; read `frontend/node_modules/next/dist/docs/` before writing frontend code).

## Repository map

| Path | What it is |
| --- | --- |
| `/` (Laravel 12, PHP) | Backend API, CMS (Filament), migrations, queue. Tests in `tests/`. |
| `frontend/` | Public website. Next.js App Router, next-intl (`en`, `ar`, RTL), Tailwind v4 tokens in `app/globals.css`. |
| `frontend/lib/routes/registry.ts` | Route source of truth. `e2e/route-registry.spec.ts` enforces its invariants. Add every new public route here. |
| `frontend/messages/{en,ar}.json` | All copy. Keep both files in key parity (`translation-parity` e2e). |
| `deploy/production/` | Production deploy helpers (`sync-revalidation-config.php`, `DEPLOY_TRIGGER.md`). |
| `deploy/staging/` | Staging Docker, nginx and systemd definitions. |
| `.github/workflows/deploy-production.yml` | The only production deploy path. |

## Working an issue

1. **Reproduce first.** Read the issue, find the code path, reproduce locally or from the CI log before editing.
2. **Branch.** Never work on `main`. Use `feat/<slug>`, `fix/<slug>` or `ci/<slug>`.
3. **Smallest safe change.** Match surrounding style. Do not refactor unrelated code.
4. **Copy is bilingual.** Any user-visible string goes in both `en.json` and `ar.json`. Check RTL.
5. **Content claims must be true.** Product and case-study copy states only what the code or repository evidence supports. No invented clients, usage, revenue or customer deployments. Present capability showcases as showcases.
6. **Verify before saying done** (see Quality gates). Report what actually ran. Say plainly what was skipped or failed.
7. **Commit.** Imperative, scoped subject (`fix:`, `feat:`, `ci:`, `docs:`). Add the attribution trailer your tooling requires.
8. **Open a PR** for anything non-trivial. Do not merge or push to `main` unless the owner asked for it in that conversation.

### Issue triage labels

`bug`, `feature`, `content`, `seo`, `a11y`, `performance`, `deploy`, `security`. Anything touching auth, tenancy, secrets or data deletion is `security`: stop and ask before acting.

### Bug report must contain

URL or command, expected vs actual, locale (`en`/`ar`), viewport, browser, and the CI run or log line when it is a deploy failure.

## Quality gates

Run from `frontend/` for frontend work:

```bash
npm run typecheck
npm run lint -- .
npm run build
npx playwright test            # full suite; or target the specs you touched
```

Backend (repo root): `php artisan test` (CI uses sqlite in-memory). CI runs all of these on every push; a local pass does not replace it.

Known unrelated failure: `e2e/home.spec.ts` expects a heading that is not in the source. Do not "fix" it as part of unrelated work.

## Deployments

### How production deploys

- A push to `main` triggers **Deploy Production** (`workflow_dispatch` also works). There is no other path. Do not SSH in and hand-edit releases.
- Job `validate`: backend install and tests, Laravel asset build, frontend typecheck, lint and build.
- Job `deploy` (only if `validate` passes): packages the validated commit, uploads it over SSH, then on the server: swaps in the release (preserving `.env`, `storage/`, `frontend/.env.production`), `composer install --no-dev`, `npm install`, frontend typecheck, lint and build, `php artisan migrate --force`, syncs revalidation config, `optimize`, restarts `hexa-frontend`, reloads PHP-FPM and nginx, then runs health checks.
- Secrets live in GitHub Actions (`SSH_HOST`, `SSH_USERNAME`, `SSH_PRIVATE_KEY`, `SSH_PORT`) and in server env files. Never print, commit or request them.
- A push can never reset CMS data. `migrate:fresh --seed` runs only via manual dispatch with `allow_fresh_seed=true`. Never enable that without the owner's explicit say-so.

### Before you push to `main`

1. Branch is rebased on the latest `origin/main` (`git fetch && git rebase origin/main`). Never force-push `main`.
2. Typecheck, lint and build pass on that rebased base.
3. The change is something the owner asked to ship now.

### Watching a deploy

```bash
gh run list --repo majdba123/hexaterminal --limit 3
gh run watch <run-id> --repo majdba123/hexaterminal --exit-status
gh run view <run-id> --repo majdba123/hexaterminal --log-failed
```

Re-trigger without code changes: bump the date in `deploy/production/DEPLOY_TRIGGER.md` and push, or use `workflow_dispatch`.

### Server-side invariants the deploy enforces

- `frontend/.env.production` must contain `API_URL=https://api.hexaterminal.com/api/v1/public` and `NEXT_PUBLIC_SITE_URL` set to `https://hexaterminal.com` **or** `https://www.hexaterminal.com`. The canonical public host in the app is `www` (`frontend/lib/seo/site.ts`).
- Backend and frontend `REVALIDATE_SECRET` must match; the backend revalidation URL is `https://hexaterminal.com/api/revalidate`.
- Health: `https://api.hexaterminal.com/api/health/ready` must return ready. Frontend `/api/health` must return 200. The deploy probes `www`, the apex, then `http://127.0.0.1:3000` and uses the first that answers.

### Failure playbook

| Symptom in the log | Likely cause | Action |
| --- | --- | --- |
| Exits right after `composer install` with no message | A frontend env guard failed. The workflow now prints which one. | Read the `frontend/.env.production:` line in the log; fix the server file or the guard. |
| `Frontend not ready yet (HTTP 000 …)` | The server cannot reach the public hostname (DNS, TLS, hairpin). | Check which base the deploy finally used; verify DNS and nginx for the host. |
| `Backend readiness failed` | Database or cache problem, or PHP-FPM not reloaded. | Read the CLI database and cache diagnostics printed after the failure. |
| `validate` fails | Real code, type, lint or build error. | Reproduce locally with the quality gates; fix the code, not the workflow. |

Fix the root cause. Do not skip hooks, weaken validation, or loosen a guard to turn a run green without telling the owner why.

### Rollback

Revert the offending commit on a branch, rebase on `origin/main`, push to `main`, and watch the run. Database migrations are not auto-reverted: ask before writing a down-migration.

## Known gaps

- Production nginx and the `hexa-frontend` systemd unit are not in this repo; only staging definitions are (`deploy/staging/`).
- After the 2026-10-14 deploy the server could reach the frontend on loopback; public-host reachability from the server was not confirmed. Confirm `https://www.hexaterminal.com/en/case-studies/crma` in a browser.
