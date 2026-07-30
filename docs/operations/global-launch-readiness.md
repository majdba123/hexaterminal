# Global Launch Readiness — Operations

How launch readiness is measured, and the categories the (recommended)
`php artisan hexa:global-readiness` aggregate command should score. The command
itself is **not yet implemented** — this document is its specification and the
current manual assessment. The current real dataset is expected to remain
**NOT READY** until founder content and legal review land, and that is correct.

## Readiness categories

### Content (founder-supplied — currently blocking)

- [ ] Required company facts approved (public vs legal name, positioning).
- [ ] ≥1 approved Service, System, Case Study per hub that claims content.
- [ ] EN **and** AR translations complete for every indexable record.
- [ ] Media approved (no placeholder/Drive URLs).
- [ ] Per-record SEO (title/description/OG) present.

### Technical (mostly achievable without founder facts)

- [ ] Legacy public routes disabled or redirected (`LEGACY_PUBLIC_WEB_ENABLED`).
- [ ] Legacy admin disabled (`LEGACY_ADMIN_ENABLED`), Filament authoritative.
- [ ] Legacy write API disabled (`LEGACY_API_ENABLED`).
- [x] Sitemap consistency (registry invariant test).
- [~] Canonical/hreflang consistency (helper exists; missing-translation guard + per-page tests deferred).
- [~] Security headers (baseline in app; full CSP/HSTS at proxy — not enforced in-app; not header-tested).
- [ ] Accessibility status (automated a11y scan in CI — deferred).
- [ ] Legal-review status (privacy/terms/accessibility statement approved).
- [ ] Pricing approval status (founder sign-off).
- [ ] Booking URL configured.
- [ ] Mail configuration ready (see mail-deliverability-checklist).
- [~] Analytics configuration status (single-provider-or-none design present).
- [ ] Monitoring configuration status (plan exists; monitors not wired).

Legend: `[x]` done · `[~]` partial · `[ ]` not done.

## Recommended command contract (`hexa:global-readiness`)

- Inputs: current environment + DB state.
- Outputs: human-readable table, `--json`, optional `--csv`; a score per
  category; explicit **P0/P1** blocker list.
- Exit code: **non-zero** while any launch blocker remains.
- Do **not** mark missing optional growth features as P0.
- Tests to add with the command:
  1. incomplete environment → NOT READY, correct P0 list;
  2. technically complete but content-incomplete fixture → NOT READY on content;
  3. fully-approved deterministic fixture → technically READY.

A companion `hexa:seo-audit` (missing/duplicate titles & descriptions, invalid
canonical/hreflang, noindex-in-sitemap, indexable-absent-from-sitemap, broken
internal links, orphan pages) should feed the technical score. Both are the
highest-value next backend increment; they are specified but unbuilt.

## Current verdict

**NOT READY for international launch** — blocked primarily on founder content and
legal review, plus the technical items marked `[ ]`/`[~]` above. This is the
expected and honest state; empty content infrastructure is not launch readiness.
