# Production Health And Deploy

HexaTerminal production currently deploys directly into `/var/www/hexaterminal`.
This task keeps that direct deployment model and adds explicit health checks to
the existing `Deploy Production` GitHub Actions workflow.

## Hardcoded workflow URLs

- Backend readiness: `https://api.hexaterminal.com/api/health/ready`
- Frontend health: `https://hexaterminal.com/api/health`
- Public smoke page: `https://hexaterminal.com/en`

These public URLs are hardcoded directly in the workflow YAML. They are not
stored in GitHub Secrets. Only SSH connection credentials remain in Secrets.

## Health endpoints

### Laravel liveness

- URL: `GET /api/health`
- Purpose: confirms the Laravel HTTP layer is running
- Auth: public
- Cache: `Cache-Control: no-store`
- Success status: `200`
- Success body:

```json
{
  "status": "ok",
  "service": "hexaterminal-backend"
}
```

This endpoint is dependency-free. It does not query the database, cache, mail,
or external services.

### Laravel readiness

- URL: `GET /api/health/ready`
- Purpose: confirms Laravel is ready for production traffic
- Auth: public
- Cache: `Cache-Control: no-store`
- Success status: `200`
- Failure status: `503`

Successful response:

```json
{
  "status": "ready",
  "service": "hexaterminal-backend",
  "checks": {
    "database": "ok",
    "cache": "ok"
  }
}
```

Failure response shape:

```json
{
  "status": "not_ready",
  "service": "hexaterminal-backend",
  "checks": {
    "database": "failed",
    "cache": "ok"
  }
}
```

Readiness checks Laravel boot completion implicitly and verifies:

- database connectivity with `select 1`
- configured cache store with a temporary non-sensitive key

Optional integrations such as mail, analytics, AI, and Turnstile are not part
of readiness.

### Next.js health

- URL: `GET /api/health`
- Purpose: confirms the Next.js server is running
- Auth: public
- Cache: `Cache-Control: no-store`
- Success status: `200`
- Success body:

```json
{
  "status": "ok",
  "service": "hexaterminal-frontend"
}
```

The frontend endpoint is an App Router route handler and does not expose API
URLs, tokens, filesystem paths, or other runtime configuration.

## Liveness vs readiness

- Liveness answers: "is the process up and responding?"
- Readiness answers: "is the service ready to handle real traffic right now?"

Because readiness includes required dependencies, it can return `503` while
liveness still returns `200`.

## Deployment checks

The workflow now performs this sequence:

1. Deploy code in `/var/www/hexaterminal`
2. Build frontend before running Laravel migrations
3. Run migrations and Laravel optimization
4. Restart queue workers with `php artisan queue:restart`
5. Restart and verify `hexa-frontend`
6. Validate Nginx with `nginx -t`
7. Reload Nginx only after validation succeeds
8. Run backend readiness, frontend health, and public-page smoke checks

If any required health check fails, the workflow fails the deployment.

## Manual verification commands

```bash
curl --fail --silent --show-error https://api.hexaterminal.com/api/health
curl --fail --silent --show-error https://api.hexaterminal.com/api/health/ready
curl --fail --silent --show-error https://hexaterminal.com/api/health
curl --fail --silent --show-error https://hexaterminal.com/en > /dev/null
```

## Services and infrastructure touched

- Systemd service checked: `hexa-frontend`
- Nginx validation command: `nginx -t`
- Nginx reload happens only if validation passes

## Explicit non-goals

This task does not add:

- backups
- SSH fingerprint validation
- release directories
- automatic rollback
- zero-downtime deployment
- Docker
