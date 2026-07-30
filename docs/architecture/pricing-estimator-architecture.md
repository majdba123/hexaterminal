# Pricing & Estimator Architecture

How the pricing page and deterministic cost estimator are built, and the invariants that keep them safe.

## Data model (6 tables)

| Table | Purpose |
|---|---|
| `engagement_models` | Commercial engagement shapes (Discovery Sprint, MVP, Custom System, Dedicated Team, Modernization, Support). Content + a pricing **display mode**. |
| `pricing_profiles` | Approval-gated numeric price bands, attached polymorphically to an engagement model (or service). One row per currency. |
| `estimator_versions` | Immutable-after-activation snapshot of the estimator's questions + rules. Exactly one active at a time. |
| `estimator_questions` | Questions in a version. **Options are inline JSON** (`[{key,label:{en,ar}}]`) — value objects with no independent lifecycle, so no separate table (avoids table explosion). Branching via declarative `show_if`. |
| `estimator_rules` | Declarative cost contributions (`base`/`add`/`multiply`). Never stored code. |
| `cost_estimates` | Computed results, addressed by high-entropy `public_uuid`; optional `contact_lead_id`. |

`CostEstimate` links to `ContactLead` (intent `cost_estimate`) only when the user opts to submit contact details.

## Rule engine (`app/Services/Estimator/EstimatorEngine.php`)

Deterministic and backend-authoritative. The frontend never computes a price.

1. **Filter visible answers** — recompute which questions are visible from the answers (evaluating `show_if`), and discard answers to hidden questions so a client can't force hidden-branch rules.
2. **Base + add** (fixed order by `sort_order`) — sum `amount_min/max`, `weeks_min/max`, `complexity_weight` for every rule whose `(question_key, option_key)` matches an answer (a null `question_key` is an always-on base).
3. **Multiply** — apply each matching `multiply` rule's `factor` to the running min/max (money only, never timeline).
4. **Guardrails** — clamp min/max to `[floor_min, ceiling_max]`; ensure `min ≤ max`.
5. **Honest rounding** — round to bands (step 500/1000/5000 by magnitude) so output is `USD 15,000–24,000`, never `USD 18,347.62`.
6. **Currency** — multiply the USD base by a **fixed peg** (`AED 3.6725`, `SAR 3.75` — both central-bank pegs, not live FX) and round again.
7. **Classify** — complexity (`standard/advanced/complex/enterprise`) from the complexity score; confidence (`low/medium/high`) from stage + complexity.
8. **Recommend** — an engagement-model slug as a pure function of `build` + `stage`.

Identical inputs against the same version always yield identical output. There is **no `eval`, no stored code, no AI** in the price path.

### Cost drivers
The public breakdown carries a localized **label** and a qualitative **weight** (`low/medium/high`) per driver. Raw amounts, factors, and margin are never exposed — only that a driver contributes minor/moderate/significant cost.

## Versioning & immutability

- One `estimator_version` is `is_active = true` / `status = active`. `activate()` is atomic: it archives any other active version.
- Version meta is editable only while `draft`; once active/archived, the CMS form locks it (clone to change).
- Every `CostEstimate` stores its `estimator_version_id`. Activating a newer version **does not** change historical estimates — they remain reproducible against the version they used.

## Fail-closed pricing

`PricingProfile::scopeApprovedForDisplay()` is the single source of truth: a number is public only when `approved_for_publication = true`, `approved_at` is set, and `effective_date` has passed (or is null). `EngagementModel::publicPricingProfile()` additionally returns null for `hidden`/`request_quote` display modes. If nothing qualifies, the public surface shows **"request a scoped estimate"** — never a fabricated or unapproved number.

## Public API

| Route | Notes |
|---|---|
| `GET /v1/public/pricing` | Engagement models + published pricing FAQs + `estimator_available`. Cached. |
| `GET /v1/public/estimator` | Active version's questions + currencies. Cached. |
| `POST /v1/public/estimates` | Compute + persist; returns the result **without an email**. Throttled 20/min. |
| `GET /v1/public/estimates/{uuid}` | Revisit; `404` unknown, `410` expired. Uncached. |
| `POST /v1/public/estimates/{uuid}/lead` | Optional contact capture. Throttled 5/min, honeypot, replay-safe. |

Public resources whitelist result-only fields — no `base_amount_*`, no `estimator_version_id`, no `session_id`, no lead notes.

## Frontend

- `/pricing` — server component; indexable only when published content exists; FAQPage + hreflang.
- `/estimate` — progressive branching wizard (client). Answers live in in-memory, session-scoped React state (no cookies, no fingerprint). On finish → `POST /api/estimates` → redirect to the result page.
- `/estimate/{uuid}` — server-rendered result + optional lead form. **Always noindex**; excluded from sitemap/search/RSS.

## Activating the first approved production version

1. Review the fixture rules in `PricingEstimatorFixtureSeeder` (or clone `v1` in Filament) and adjust numbers to founder-approved figures.
2. In **Estimator Versions**, confirm the draft has questions + rules, then **Activate** (admin only). This archives the previous active version; historical estimates keep theirs.
3. Separately, create approved `PricingProfile` rows and use the **Approve** action for any fixed public prices you want shown on the pricing page.
