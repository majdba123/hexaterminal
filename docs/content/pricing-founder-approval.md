# Pricing — Founder Approval Required

The pricing/estimator feature is fully built, but **no commercial number or promise is published** until a founder approves it here. Public pricing fails closed: until you act, the site shows honest "request a scoped estimate" guidance and the estimator's ranges are labeled indicative and non-binding.

Nothing in this list has been invented. Each item needs a real, founder-confirmed value or wording.

## 1. Engagement models
Six models are drafted as **content** (no prices): Discovery & Architecture Sprint, MVP / Focused Product Release, Custom Business System, Dedicated Engineering Capacity, Modernization & Integration, Support & Continuous Improvement.
- [ ] Approve or edit each model's title, summary, and "suitable buyer" copy (EN + AR).
- [ ] Decide the `pricing_display_mode` per model: `hidden` / `request_quote` / `starting_from` / `indicative_range` / `fixed_package`.
- [ ] Supply deliverables / included / excluded lists if you want them shown.

## 2. Public prices (only if you want numbers on the pricing page)
For any model set to `starting_from` / `indicative_range` / `fixed_package`:
- [ ] Provide the **starting range** per currency (USD / AED / SAR) — founder-approved bands, not conversions.
- [ ] Confirm the billing model (`fixed_project` / `milestone_based` / `monthly_retainer` / `discovery_sprint` / `dedicated_team` / `support_plan`).
- [ ] Then create the `PricingProfile` and use the **Approve** action in Filament. Until approved, no number shows.

## 3. Estimator rule figures
The active estimator version (`v1`) ships with fixture numbers so the tool works.
- [ ] Review the base bands and add-on/multiplier figures in `PricingEstimatorFixtureSeeder` (or clone `v1` in Filament and edit).
- [ ] Confirm the guardrail floor/ceiling.
- [ ] Re-activate the founder-approved version before treating estimator ranges as reflecting real pricing.

## 4. Currencies
- [ ] Confirm USD / AED / SAR are the currencies to offer. (AED 3.6725 and SAR 3.75 are fixed USD pegs; no live FX is used.)

## 5. Commercial wording (currently review-only drafts)
The financial FAQs (`category = pricing`, 12 entries, **unpublished**) include placeholder wording on the money questions. Approve real wording for:
- [ ] Payment / milestone model (e.g. deposit split, milestone triggers).
- [ ] Discovery fee, if any.
- [ ] Maintenance / support pricing model.
- [ ] Code / IP ownership terms.
- [ ] Tax wording.
- [ ] Estimate disclaimer (a generic one ships; confirm it's acceptable).
- [ ] Estimate expiry window (default 30 days).
- [ ] Response-time promise (used in CTAs/copy).
- [ ] Booking URL (Company Settings `booking_url`) for "Book a discovery call".

## 6. Publish
Once approved:
- [ ] Publish the financial FAQs (`is_published = true`) via the FAQ resource (category `pricing`).
- [ ] Approve any `PricingProfile` numbers.
- [ ] Activate the approved estimator version.

Until every relevant box is checked, the platform behaves honestly: models show "request a scoped estimate", the estimator shows disclaimered indicative ranges, and no unapproved price or commercial promise appears anywhere public.
