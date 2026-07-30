# Monitoring & Alerting Plan

Operational monitoring for the Hexa Terminal platform. **No external monitor is
configured here** (that needs credentials); this defines what to watch, severity,
and ownership so it can be wired up at deploy time.

## Monitors

| Monitor | Signal | Severity | Owner | Notes |
|---------|--------|----------|-------|-------|
| Frontend liveness | `GET /{locale}` 200 | P1 | on-call | Next app up |
| Backend liveness | `GET /api/health` 200 | P1 | on-call | `HealthController@live` |
| Backend readiness | `GET /api/health/ready` 200 | P1 | on-call | DB + cache reachable (`@ready`) |
| Lead endpoint smoke | `POST /api/v1/public/leads` (synthetic) | P1 | on-call | conversion path — highest business impact |
| Estimate endpoint smoke | `POST /api/v1/public/estimates` (synthetic) | P2 | on-call | estimator path |
| CMS login reachable | Filament login 200 | P2 | ops | editors can work |
| Revalidation health | revalidate endpoint success rate | P2 | ops | stale content risk |
| Queue failures | failed-jobs count / rate | P2 | ops | email + revalidation depend on it |
| Email failure rate | provider bounce/complaint + job failures | P2 | ops | see mail checklist |
| TLS certificate expiry | cert `notAfter` < 21 days | P1 | ops | prevents outage |
| Domain expiry | registrar expiry < 30 days | P1 | ops | prevents loss |

## Severity definitions

- **P1** — user-facing outage or imminent outage (site down, readiness failing,
  lead capture broken, cert/domain expiring). Page immediately.
- **P2** — degraded but not down (editor tooling, queue backlog, elevated email
  failures). Notify during working hours; escalate if sustained.

## Process

- **Escalation:** P1 → on-call → engineering lead after 15 min unacknowledged.
- **Notification owner:** named per monitor above (assign real people at setup).
- **False positives:** synthetic checks must use a dedicated test lead/estimate
  path so monitoring traffic never pollutes real lead/analytics data.
- **Maintenance windows:** silence P2 (not P1) during announced deploys.

## Application observability prerequisites (recommended, partly deferred)

- Expose app version / commit SHA on a status endpoint for correlation.
- Structured logs (Laravel + Next) with a request/correlation ID.
- Never log passwords, tokens, cookies, full lead bodies, AI secrets, or
  sensitive estimate details.
- A single configurable error-monitoring integration boundary (do not hardwire a
  paid provider).
