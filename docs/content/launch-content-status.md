# Launch Content Status

Snapshot date: 2026-07-18. Companion to `current-content-inventory.md` (what exists), `founder-approval-checklist.md` (what needs a founder decision), and `public-claims-register.md` (what claims are verified).

## What changed this sprint
- Populated `CompanySetting` with safe, non-fabricated values (company name, tagline/description matching the sprint's approved positioning, existing email fallback). Phone/WhatsApp/booking URL/social links intentionally left blank.
- Unpublished the 12 legacy-migrated `service_offerings` rows (mis-tagged locale, garbled slugs, no tagline/summary) rather than presenting them as complete.
- Drafted 6 new service records for the pillars named in the sprint brief (SaaS Platforms, CRM & ERP Systems, AI-Enabled Workflows, Backend & API Engineering, Business Automation, Custom Operational Software), each with EN/AR copy — all in `in_review` status, **none published**.
- Unpublished all 8 legacy-migrated case studies (fictional demo SaaS products with `placehold.co` imagery and no client data) so they cannot appear as real client work.
- Un-approved all 5 migrated testimonials pending verified publication permission.
- Drafted 10 FAQ entries answering the sprint's listed questions, in an unpublished state.
- Added a real, clearly-labeled demo case study (`demo-ledger-platform-rollout`) to the existing `DemoContentSeeder` fixture set so the e2e suite keeps a stable, non-fictional-looking record to test against.
- Fixed the homepage hero stats row (`frontend/components/site/hero.tsx`) to hide zero-valued metrics instead of displaying "0+" — verified visually against a freshly seeded, mostly-empty database.
- Confirmed every other homepage section (services, systems, case studies, industries, testimonials, insights, FAQ) already hides itself cleanly when its backing content is empty, and hub pages (Services, Case Studies, etc.) render a proper "Nothing here yet" empty state rather than broken UI.

## AI SEO live smoke
No `ANTHROPIC_API_KEY` is configured in this environment (`.env.example` ships it blank, and it was not set locally). Per the sprint's own instruction, the provider stays safely disabled rather than faking output. Verified: `hexa:content-report` shows `failed_ai_generations: 0`, and no `AiGeneration` rows exist. If real credentials become available, re-run this smoke test against one draft Service and one draft Article, and update this section with the resulting provenance/model/token/cost/latency figures — never commit the key itself.

## Quality gates run this sprint

**Backend**
- `vendor/bin/pint --test $PINT_PATHS` (governed paths, including the new seeder and test) → passed
- `vendor/bin/phpstan analyse --no-progress` → 0 errors
- `vendor/bin/phpunit` (full suite, 150 tests including the 7 new `FounderContentSeederTest` cases) → OK
- `php artisan hexa:content-report` → run and reviewed manually (88 → 118 findings; increase is expected, since more content now exists in a deliberately unpublished/draft state pending review, not a regression)

**Frontend**
- `npx tsc --noEmit` → clean
- `npx eslint . --max-warnings=0` → clean
- `npm run build` against a live Laravel API seeded with `migrate:fresh --seed`, `hexa:migrate-legacy-content`, `FounderContentSeeder`, and `DemoContentSeeder` → succeeded (33 routes)
- `npx playwright test` → 30 passed, 1 intentionally skipped (category-filter test skips when no category chips are seeded), 0 failed

## What is still required before this can go live
See `founder-approval-checklist.md` for the itemized list. In summary: real contact channels, founder sign-off on positioning and the 6 drafted service pillars, real systems/case-study evidence (the 10 named systems in the original brief do not exist in this repo at all), team bios, verified testimonial permission, legal review of Privacy/Terms, and real media to replace stock icons/placeholder images/Drive-linked photos.

## Readiness assessment
The platform's editorial workflow (draft → in_review → approved → published) was used throughout this sprint specifically so that nothing here reaches the public site without a human approval step. Zero pieces of new or corrected content were auto-published. The site currently shows only what is genuinely real and non-zero (2 team members); every other section cleanly hides itself pending founder-approved content.
