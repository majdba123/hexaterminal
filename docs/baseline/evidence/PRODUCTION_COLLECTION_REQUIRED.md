# Production Collection Required

The repository baseline for `BASE-001A` is complete, but the production-server
evidence file has not been collected from the live host yet.

## Required production command

Run the following on the production server from `/var/www/hexaterminal`:

```bash
bash scripts/baseline/collect-production-environment.sh
```

## Expected output file

If the command succeeds, it writes:

```text
docs/baseline/evidence/production-environment-output.txt
```

## What the script collects

- timestamp
- hostname
- operating-system summary
- current directory
- git branch
- git commit
- git working-tree status
- PHP version
- Composer version
- Laravel version
- Node version
- npm version
- Nginx version
- Nginx configuration-test result
- `hexa-frontend` ActiveState and SubState
- whitelisted Laravel driver configuration
- backend readiness HTTP status
- frontend health HTTP status
- public English-page HTTP status
- known log locations and safe read-only commands

## Safety guarantees

The script is read-only and does not:

- read raw `.env`
- print secrets
- install packages
- run migrations
- restart services
- clear caches
- deploy code
