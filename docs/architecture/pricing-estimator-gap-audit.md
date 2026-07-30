# Pricing & Cost Estimator — Gap Audit

Snapshot date: 2026-07-19. Read-only inspection of the existing codebase before the pricing/estimator sprint. Classifies each capability the sprint needs: **COMPLETE** · **PARTIAL** · **NOT STARTED** · **BLOCKED BY FOUNDER APPROVAL**.

## Existing infrastructure this sprint builds on

| Capability | Status | Evidence / notes |
|---|---|---|
| Lead model (`ContactLead`) | COMPLETE | `app/Models/ContactLead.php` — has `intent`, `status`, `score`, `score_breakdown`, `utm`, `requested_service_id`, `requested_system_id`, activity logging. **No `cost_estimate` intent yet, no estimate link.** |
| Lead intake API | COMPLETE | `LeadController@store` — honeypot, optional Turnstile (fail-open), 10-min replay suppression, deterministic scoring, queued notify. Reusable for estimate→lead. |
| Deterministic lead scoring | COMPLETE | `LeadScoringService` — explainable 0–100, never auto-rejects, no protected traits. **Needs estimator signals added.** |
| UTM / first-touch attribution | COMPLETE | `frontend/lib/attribution.ts` (session-scoped, no cookies/fingerprint) + `ContactLead.utm`. Reuse verbatim. |
| Privacy-conscious analytics | COMPLETE | `analytics-script.tsx` `trackEvent()` — single-provider-or-none, no PII. Add new event names only. |
| Filament CMS pattern | COMPLETE | v4-style resources (`Schemas/Tables/Pages` folders). Template: `FaqItems`. Reuse for pricing resources. |
| Company Settings | PARTIAL | `CompanySetting` already has `booking_url`, `lead_recipients`. **No currency default, no response-time promise, no discovery-fee field.** |
| FAQ model | PARTIAL | `FaqItem` has a free-text `category` — financial FAQs can use `category = 'pricing'`. Editorial `is_published` gate exists. No founder-approval flag distinct from publish. |
| Next.js public routes | COMPLETE | `/services`, `/systems`, etc. + `/api/leads` proxy. Pattern reusable for `/pricing`, `/estimate`. |
| i18n EN/AR routing | COMPLETE | `next-intl` always-prefix, `messages/{en,ar}.json`. Add `pricing`/`estimator` namespaces. |

## Net-new capabilities this sprint must build

| Capability | Status | Plan |
|---|---|---|
| Service / project pricing fields | NOT STARTED | New `PricingProfile` (per-service or per-engagement-model), pricing display modes, currency bands, founder-approval gating. |
| Engagement models | NOT STARTED | New `EngagementModel` entity (title, summary, buyer fit, scope, deliverables, duration, pricing mode, SEO). |
| Estimator versioning | NOT STARTED | `EstimatorVersion` + `EstimatorQuestion` + `EstimatorOption` + `EstimatorRule` (versioned, immutable after activation). |
| Deterministic rule engine | NOT STARTED | `EstimatorEngine` service — base bands + controlled multipliers/additions + min/max guardrails, explainable, no eval, backend-authoritative. |
| Cost estimate records | NOT STARTED | `CostEstimate` — public UUID, version, locale, currency, answers, min/max, timeline, complexity, confidence, drivers, recommended model, optional `contact_lead_id`. |
| Estimate→lead conversion | NOT STARTED | New public API endpoints; reuse `ContactLead` with `intent = cost_estimate`; link estimate; preserve UTM; replay-safe. |
| Currencies (USD/AED/SAR) | NOT STARTED | Founder-approved bands per currency; **no live FX**. Session-persisted selection. |
| Pricing page (EN/AR) | NOT STARTED | `/en/pricing`, `/ar/pricing` — philosophy, models, guidance, FAQ, estimator CTA. Fail-closed when no approved price. |
| Estimator UX (EN/AR) | NOT STARTED | `/en/estimate` progressive 8–12 Q branching flow; range result without email gate. |
| Shareable result page | NOT STARTED | `/en/estimate/{public_uuid}` — noindex, no PII in URL, excluded from sitemap/search/RSS. |
| Filament pricing/estimator mgmt | NOT STARTED | Resources for engagement models, pricing, estimator versions/questions/options/rules, estimate submissions, financial FAQs. Clone-before-edit, activate-one-version. |
| Financial FAQ drafts | NOT STARTED / BLOCKED BY FOUNDER APPROVAL | Draft the 12 questions in `is_published = false`; wording that promises payment terms/ownership stays review-only. |
| Estimator analytics events | NOT STARTED | `pricing_page_view`, `estimator_started`, `estimator_step_completed`, `estimator_completed`, `estimate_result_viewed`, `estimate_email_requested`, `discovery_call_clicked`, `proposal_requested` — no PII. |

## Blocked by founder approval (build now, publish fail-closed)

- Real numeric prices / starting ranges per service and engagement model.
- Currency bands (USD/AED/SAR amounts).
- Payment/milestone model wording, discovery-fee amount, maintenance pricing.
- Code/IP ownership wording, tax wording, estimate disclaimer/expiry, response-time promise, booking URL.

All of the above are captured in `docs/content/pricing-founder-approval.md`. Until approved, the public surface shows honest **"Request a scoped estimate"** guidance instead of any number, and `CostEstimate` uses **deterministic test-fixture rule versions only** (never seeded as approved production pricing).

## Guiding constraints (carried into implementation)

- Backend (Laravel) is the **authoritative** pricing engine; frontend JS never computes the price.
- Rule versions are **immutable once activated**; historical estimates remain reproducible against their original version.
- Public visibility **fails closed**: no `approved_for_publication` → no number.
- Estimate result pages are **noindex** and excluded from sitemap/search/RSS/JSON-LD.
- No live FX; no AI in the authoritative price path; no fabricated prices or commercial promises.
