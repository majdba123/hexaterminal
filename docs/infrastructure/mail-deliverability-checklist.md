# Mail Deliverability Checklist

Prerequisites for reliable transactional email (lead notifications, lead/estimate
confirmations, newsletter double-opt-in). **No DNS records are fabricated here** —
this lists exactly what to configure and how to validate it.

## Application-side requirements (verify in code before go-live)

- [ ] Lead persistence succeeds **even if email sending fails** (email is
      best-effort; never block or fake a "sent" status).
- [ ] Mail runs on a **queue** with retries and failure visibility (failed jobs
      surfaced, not silently dropped).
- [ ] EN/AR templates with correct **RTL rendering** for Arabic and a
      **plain-text** fallback.
- [ ] Idempotency: a retried job does not double-send.
- [ ] Safe **disabled mode** for non-production (log/scratch mailer; never sends
      to real recipients in staging or tests).
- [ ] No credentials committed; no sensitive payloads (full lead body, PII) in
      logs.

## DNS / domain authentication (placeholders — fill with real values)

| Record | Purpose | Placeholder | Validate with |
|--------|---------|-------------|---------------|
| SPF (`TXT` @ root) | authorise sending IPs | `v=spf1 include:<provider> -all` | `dig TXT hexaterminal.com` |
| DKIM (`TXT` selector) | sign messages | `<selector>._domainkey` from provider | `dig TXT <selector>._domainkey.hexaterminal.com` |
| DMARC (`TXT` `_dmarc`) | policy + reports | `v=DMARC1; p=quarantine; rua=mailto:<dmarc@...>` | `dig TXT _dmarc.hexaterminal.com` |
| Return-Path / bounce domain | alignment | provider CNAME | provider dashboard |
| Custom tracking domain | link/open tracking | provider CNAME | provider dashboard |

## Provider & alignment

- [ ] Choose one transactional provider (SES / Postmark / etc.).
- [ ] `From` domain aligned with DKIM/Return-Path (DMARC alignment).
- [ ] `From` name and address approved by founder; `Reply-To` set.
- [ ] Bounce + complaint handling wired (suppression list, webhook).

## Cutover validation

- [ ] Staging sends only to an allow-listed internal recipient.
- [ ] Send a production test to mail-tester.com / Gmail+Outlook; SPF, DKIM,
      DMARC all pass; not in spam.
- [ ] Confirm bounce and complaint webhooks fire and are recorded.

Env surface stays in `.env`/secrets manager only (see `.env.example`).
