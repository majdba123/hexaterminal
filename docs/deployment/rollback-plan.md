# Rollback Plan

Recovery procedures for a Hexa Terminal deploy that goes wrong — staging now,
and the future production cutover. Principle: **the legacy Blade frontend and
legacy admin are never deleted**, so the fastest rollback is almost always a
routing revert, not a code redeploy.

## Backups (prerequisite for every deploy)

- **Database**: before any migration, `mysqldump` the target DB
  (`hexa_terminal_staging` on staging) to timestamped storage. Retain at least
  the last 7 staging / 30 production dumps.
  ```bash
  mysqldump --single-transaction --routines --triggers \
    -h "$DB_HOST" -u "$DB_USERNAME" -p "$DB_DATABASE" \
    > backups/$DB_DATABASE-$(date +%Y%m%d-%H%M%S).sql
  ```
- **Code**: every deploy is a specific commit SHA (`APP_VERSION`). Keep the
  previous release available (previous image tag, or a `releases/<sha>` dir with
  a `current` symlink for bare-VPS deploys) so a revert is a symlink/tag swap.
- **Uploaded media**: on the `public` disk / `storage_app` volume — snapshot or
  sync before destructive changes; never rely on it living in git.

## Decision guide

| Symptom | Rollback |
|---|---|
| Public pages broken after cutover | **A. Revert routing** (proxy upstream / DNS) |
| Frontend build/deploy bad, API fine | **B. Revert frontend release** |
| Backend release bad, schema unchanged | **C. Revert backend release** |
| Bad migration / data corruption | **D. Restore DB from backup** |
| Content wrong (not code) | **E. Fix in CMS** (no rollback) |

## A. Revert routing (fastest — cutover safety net)

**Trigger**: after a legacy→Next.js cutover, public pages are broken/5xx, health
fails, or the site is indexable before intended.

1. In the reverse proxy, point the public origin's upstream **back to Laravel**
   (Blade `WebsiteController`) — or revert the DNS record if DNS was used.
2. Reload Nginx (`nginx -s reload`). The legacy site returns immediately; **no
   code deploy needed** because Blade was never removed.
3. Purge CDN/edge HTML cache.
4. Confirm the legacy site via the smoke checklist, then debug Next.js off the
   hot path.

Proxy-upstream flips are seconds and TTL-free — prefer them over DNS reverts.

## B. Revert the frontend release (API healthy)

**Trigger**: bad Next.js build/deploy; API is fine.

- **Compose**: redeploy the previous frontend image tag; recreate only the
  `frontend` service.
- **systemd/bare**: repoint `current` → previous release dir; `systemctl restart
  hexa-frontend`.
- **Vercel/split**: promote the previous deployment.

No DB action. Verify `/api/health` (frontend) + a couple of pages.

## C. Revert the backend release (schema unchanged)

**Trigger**: bad backend release but migrations did **not** change.

1. Deploy the previous backend SHA/image.
2. `php artisan config:cache route:cache view:cache`.
3. `GET /api/health/ready` → `200`.
4. Because SSG pages read the API, revalidate or rebuild the frontend if API
   response **shapes** changed.

## D. Restore the database (bad migration / data loss)

**Trigger**: a migration corrupted data or is not cleanly reversible.

> Laravel `migrate:rollback` only works if every migration in the batch has a
> correct `down()`. **Do not assume it does.** The pre-migration dump is the
> source of truth.

1. Put the app in maintenance: `php artisan down`.
2. Try `php artisan migrate:rollback --step=1 --force` **only** if the batch is
   known-reversible; otherwise skip to 3.
3. Restore the dump:
   ```bash
   mysql -h "$DB_HOST" -u "$DB_USERNAME" -p "$DB_DATABASE" < backups/<dump>.sql
   ```
4. Deploy the matching (pre-migration) backend SHA.
5. `php artisan migrate:status` to confirm state; `php artisan up`.
6. Rebuild/revalidate the frontend if content changed.

**Limitation**: restoring the dump discards data written between backup and
restore. On production, quantify that window (last dump time vs. now) and
communicate it before restoring.

## E. Content fix (no rollback)

Wrong/incomplete content is **not** a deploy problem: fix or unpublish in `/cms`.
Unpublishing removes it from the API + frontend (the `Publishable` scope); if
revalidation is enabled the page updates at once, otherwise within the 300s ISR
window.

## Post-rollback

1. Announce restored state + the rollback taken.
2. Capture the failing `APP_VERSION`, logs, and the failing health/smoke output.
3. Reproduce on staging, fix, re-run all quality gates + the staging smoke suite
   before re-attempting.
4. Note any data-loss window from a DB restore.

## Guardrails (unchanged by any rollback)

- **main/master is never touched** by this work — all of it lives on the staging
  feature branch.
- The legacy Blade frontend and legacy admin stay deployed until an explicit,
  separate cutover approval.
- No production DNS/deploy is performed as part of the staging sprint.
