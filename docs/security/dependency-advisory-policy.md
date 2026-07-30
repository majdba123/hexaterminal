# Dependency Advisory Policy

How dependency advisories from `composer audit` and `npm audit` are triaged.
Advisories are **reported, not auto-gated** in CI (see the `security-audit` job)
so unrelated work is never blocked by advisory noise; a committed secret or
private key **is** gated and fails CI.

## Classification

Every advisory is placed in one bucket, with an owner and a review date:

| Class | Meaning | CI behaviour |
|-------|---------|--------------|
| **production-exploitable** | reachable from untrusted input in a deployed runtime | fix before release; may block a release gate |
| **development-only** | build/test/tooling dependency, not in the production request path | report; schedule an upgrade |
| **false-positive** | not applicable to how we use the package | document why; suppress with a note |
| **accepted-risk** | real but low-exploitability; fix deferred | record owner + review date |

Major-version upgrades are **never** applied automatically (they are breaking).
Minor/patch upgrades that clear an advisory are preferred once re-tested.

## Advisory register (verified this closure pass)

| Package | Severity | Exposure | Affected path | Installed | Fixed version | Exploitability | Mitigation | Decision | Owner | Review date |
|---------|----------|----------|----------------|-----------|----------------|-----------------|------------|----------|-------|-------------|
| `guzzlehttp/guzzle` | medium (×3) | production | `App\Services\RevalidationService` (server-to-server call to our own Next.js frontend) | ~~7.14.2~~ **7.15.1 (resolved)** | 7.15.1 | Low — no untrusted response cookie jar shared across requests | Minor bump, no code change | **RESOLVED** — applied and verified this pass (see below) | Backend | n/a (closed) |
| `guzzlehttp/psr7` | (transitive) | production | dependency of guzzle | ~~2.12.5~~ **2.13.0 (resolved)** | 2.13.0 | n/a | Minor bump alongside guzzle | **RESOLVED** | Backend | n/a (closed) |
| `next` (bundles `sharp`) | high | build/runtime (image optimisation) | `next/image` server-side transform | 16.2.10 | none yet upstream that isn't a major downgrade | Low in our topology — inputs are our own CMS-approved media, not arbitrary user uploads; `npm audit fix --force` would downgrade to `next@9.3.3`, a false remediation | none applied | **accepted-risk** | Frontend | re-check each Next 16.x patch release |
| `next` (bundles `postcss`) | moderate | build-time only (CSS stringification) | Next's internal PostCSS pipeline | 16.2.10 (bundled postcss 8.4.31; project's own `@tailwindcss/postcss` already uses safe postcss 8.5.19) | ≥8.5.10 | Very low — build-time only, not a runtime request path | none applied | **accepted-risk** | Frontend | re-check each Next 16.x patch release |

### guzzle/psr7 — RESOLVED this closure pass

Dry-run (`composer update guzzlehttp/guzzle --with-all-dependencies --dry-run`)
showed a clean, non-breaking resolution (0 installs, 2 updates, 0 removals: guzzle
7.14.2→7.15.1, psr7 2.12.5→2.13.0). Applied for real; re-verified:

- `composer audit` → **"No security vulnerability advisories found."**
- Full PHPUnit (201 tests) and PHPStan (`level 5`, `app/`) both pass unchanged.

This was a real, safe, non-breaking fix discovered during verification — not a
blind lockfile update. No major version was touched.

### next/sharp/postcss — accepted-risk, confirmed not auto-fixable safely

`npm ls next postcss sharp` confirms both vulnerable packages (`sharp@0.34.5`,
Next's bundled `postcss@8.4.31`) are internal to `next@16.2.10` itself, not
top-level project dependencies — there is no minor/patch bump available at the
project level; the fix must ship from Next.js upstream. `npm audit fix --force`
was **not** run (it proposes downgrading to `next@9.3.3`). This is unchanged
from the prior sprint's triage, now confirmed via `npm ls` and a fresh `npm ci`
audit rather than a single `npm audit` run.

## Process

1. CI runs `composer audit` and `npm audit --omit=dev` (report-only).
2. New advisories are triaged into the register above with an owner + review date.
3. `accepted-risk` items are revisited on their review date; an overdue accepted
   risk is escalated.
4. A resolvable advisory gets a real fix attempt (dry-run first) before being
   marked accepted-risk — never marked resolved while the vulnerable version
   remains installed.
