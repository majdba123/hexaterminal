# Estimator → Lead Flow (Sales)

How a visitor's cost estimate becomes a qualified, attributed lead in the CMS.

## The visitor journey

1. **Pricing page** (`/pricing`) — buying guidance + engagement models. Every "Request a scoped estimate" links to the estimator.
2. **Estimator** (`/estimate`) — 8–12 branching questions (irrelevant ones are skipped), a currency selector, and a progress bar. No email is asked for.
3. **Result** (`/estimate/{uuid}`) — an indicative range, timeline, complexity, confidence, cost drivers, assumptions, a disclaimer, and a recommended engagement model. **Shown immediately, before any contact request.**
4. **Optional contact** — the visitor may choose: *Email me this estimate*, *Book a discovery call*, *Request a detailed proposal*, *Start a project*, or *Ask a question*. Only then does a short form appear.

## What lands in the CMS

On contact submission (`POST /estimates/{uuid}/lead`):

- A `ContactLead` is created with `intent = cost_estimate`.
- The `CostEstimate` is linked to it (`contact_lead_id`), and its status becomes `lead_created` / `discovery_requested` / `proposal_requested` based on the chosen action.
- `budget_range` is auto-filled from the estimate band (e.g. `USD 55,000–125,000`).
- First-touch **UTM / landing page / referrer** are preserved from the session attribution.
- The deterministic **lead score** runs with estimator signals (see below).

### Where to work it
- **Leads** → the lead appears in the normal pipeline with its score, priority, UTM, and budget band.
- **Cost Estimates** → the read-only inbox shows every estimate (anonymous and converted) with band, drivers, answers, linked lead, source, score, and status. Filter by status/complexity/currency; export CSV.

## Lead scoring signals from the estimator

Added to the existing explainable score (each factor is visible in the lead's breakdown):

| Signal | Points |
|---|---|
| Completed an estimate | +10 |
| Complexity `complex`/`enterprise` | +10 (`advanced` +6, else +3) |
| Estimate base band ≥ 25k | +8 (≥ 12k → +4) |

The score **only orders the queue** — it never rejects or hides a lead, and uses no protected traits. Admins can override priority freely.

## Anti-abuse & privacy

- Result retrieval is by high-entropy UUID; there are no sequential ids in any public URL.
- Contact capture is honeypot-protected and replay-suppressed (identical email + `cost_estimate` within 10 minutes is idempotent — no duplicate leads).
- The estimator has no free-text questions; only structured option keys are stored on the anonymous estimate. Any free-text note lives on the `ContactLead` only after the visitor submits it.
- Anonymous estimates expire (default 30 days) and then return `410`.
