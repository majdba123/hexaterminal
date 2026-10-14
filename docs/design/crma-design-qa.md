# CRMA case study: design QA

Route `/case-studies/crma` (en, ar) and the homepage teaser. Captures are in `docs/design/crma-qa/` (4 widths x 10 views: home teaser, hero, flow, architecture, governed AI, mobile, bilingual, evidence, CTA, Arabic bilingual).

Reviewed 2026-10-15 against the local production build. Widths: 1440, 1024, 768, 390.

## Result

No open P0 or P1 issues.

| Check | Result |
| --- | --- |
| Horizontal overflow, en/ar, 4 widths | none (0 at every width) |
| Playwright (crma-case-study, route-registry, navigation, translation-parity, accessibility/axe, technical-seo, contrast-tokens, typography-responsive) | 81 passed |
| `tsc`, eslint, `next build` | clean |
| RTL (Arabic) layout and mirrored gallery arrows | checked, correct |
| Gallery dialog: focus trap, Escape, focus returns to opener | passes (fixed this round) |
| Reduced motion | content is never parked; no transform/opacity motion applied |
| Positioning | "Engineering capability showcase · seeded demonstration data" disclosure present; no client, usage or revenue claims |

## Issues

### P0
None.

### P1
None. One fixed during QA: closing the gallery dialog lost focus (Radix controlled Dialog with no Trigger). Fixed with opener tracking and `onCloseAutoFocus`.

### P2
1. Sticky site header appears mid-image in tall element captures (governed AI, architecture, Arabic bilingual). This is a capture artifact of element screenshots, not a page defect. Not fixed.
2. Dense product screenshots at 390px are small. Each opens full size in the gallery. Accepted.
3. `e2e/home.spec.ts` fails on a "Let's build the system…" heading that does not exist in source. Predates this work and is unrelated. Not touched.

## Content review

- Copy presents CRMA as a product capability. Readiness-negative wording (pre-launch status, "not exercised live" tier, "not a client engagement") was removed. The seeded-data disclosure stays because it is accurate.
- Every figure was re-counted from the CRMA repository on 14 October 2026. UNVERIFIED claims were omitted.
- CTAs: "Need a system like this?", "Discuss Your System", "Explore More Work". No personal-brand CTAs.

## Remaining evidence gaps

- No production usage, customer, uptime or revenue evidence exists, so none is claimed.
- Live third-party integrations (Twilio, Stripe, Meta) were not exercised end to end, so they are described by design only.
- accesslint was not run; axe via Playwright passes.
