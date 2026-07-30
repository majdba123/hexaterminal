# Current Content Inventory

Snapshot date: 2026-07-17. Source: direct inspection of models/migrations/seeders, the local `database/database.sqlite`, and `php artisan hexa:content-report` (read-only). This is a factual audit, not a plan — nothing here has been rewritten or invented.

Classification legend: REAL AND APPROVED · REAL BUT NEEDS REVIEW · DEMO/PLACEHOLDER · INCOMPLETE · MISSING TRANSLATION · MISSING MEDIA · MISSING SEO · PRIVATE/CONFIDENTIAL · MUST NOT PUBLISH

## Company Settings
- Table `company_settings` — **MISSING**. Migration is pending on this DB copy; no seeder exists; zero rows. Filament page `app/Filament/Pages/CompanySettingsPage.php` exists and is ready to receive input.
- Frontend falls back to hardcoded `hello@hexaterminal.com` / "Hexa Terminal" on Privacy/Terms pages only.
- **Founder input required**: company legal name, tagline, description, address, footer note, real support email, phone, WhatsApp, social links, booking URL, lead-notification recipients, default OG image.

## Services (`service_offerings`, model `App\Models\Service`)
12 rows, all migrated from the legacy `services` table (`ServicesSeeder.php`).

| slug | status |
|---|---|
| `ttoyr-ttbykat-aloyb` (web dev) | REAL BUT NEEDS REVIEW / MISSING TRANSLATION / MISSING SEO / MISSING MEDIA |
| `hlol-althkaaa-alastnaaay` (AI solutions) | same |
| `anthm-alhgz-oalmoaaayd` (booking systems) | same |
| + 9 more legacy-migrated rows | same |

Issues common to all 12: garbled transliterated slugs (not launchable URLs), Arabic copy mistakenly stored under the `en` locale key (no real English copy exists), `tagline`/`summary`/`features`/`tech_stack`/`icon` all NULL, cover image is a generic flaticon stock icon, zero `seo_meta` rows.
**Founder input required**: confirm which of these 6 pillars (SaaS Platforms, CRM & ERP, AI-Enabled Workflows, Backend & API, Business Automation, Custom Operational Software) map to real Hexa Terminal offerings, supply real EN copy, and approve clean slugs.

## Systems (`systems`)
Only 2 rows exist, both manually entered in Filament (no seeder in the repo creates them):

| slug | name | status |
|---|---|---|
| `hexa-crm` | Hexa CRM | REAL BUT NEEDS REVIEW / MISSING MEDIA / MISSING SEO |
| `fleet-ops` | FleetOps | REAL BUT NEEDS REVIEW / MISSING MEDIA / MISSING SEO |

The demo fixture `demo-ledger-platform` (from `DemoContentSeeder.php`) is **not** in this DB and must never enter production regardless.

**The 10 candidate names in the sprint brief — LeadScope AI, Dhura, HireLens AI, LinguaCoach AI, CareerGuide AI, Business Flow, Mytrixa, Rakez ERP, iLogistics, Avenue Food — do not exist anywhere in this repository** (models, seeders, docs, or config). They are **MISSING** entirely, not merely unpublished. Nothing about them can be verified, classified as company-vs-client work, or attributed without the founder supplying source material.

## Case Studies (`case_studies`)
8 rows, all migrated from the legacy `ProjectsSeeder.php` fictional-demo dataset:

`mns-almtgr-alalktrony-althky` (Smart Store), `ttbyk-adar-almsharyaa-protask` (ProTask), `ttbyk-tosyl-altaaam-speedeats` (SpeedEats), `ntham-adar-alaayadat-altby` (MedClinic), `mns-altaalm-alalktrony-edupro` (EduPro), `ntham-adar-almoard-albshry-hrflow` (HRFlow), `ttbyk-almhfth-alrkmy-digiwallet` (DigiWallet), `mns-hgz-alfnadk-staybook` (StayBook).

**Classification: DEMO/PLACEHOLDER — MUST NOT PUBLISH as real client work.** `client_name` and `outcomes` are empty on every row; galleries are `placehold.co` generated images; no verifiable evidence or client-approval trail exists in the repo. These read as fictional demo SaaS products, not documented Hexa Terminal engagements.

**Founder input required**: for each real case study, context/problem/solution/architecture, a classification of every metric as verified/approximate/confidential/not-publishable, and explicit confirmation the client (or an approved anonymization) may be shown publicly.

## Industries (`industries`)
2 rows, manually entered: `fintech` ("Fintech"), `logistics` ("Logistics"). Status: REAL BUT NEEDS REVIEW / MISSING MEDIA / MISSING SEO. No case study is currently linked to either.

## Articles / Categories / Tags
**MISSING entirely.** `articles` table has 0 rows; `article_categories`/`article_tags` tables are on a pending migration not yet applied to this DB. The only article that would exist is the `DemoContentSeeder` fixture (`demo-building-auditable-systems`), which is not wired into the default seeder and is not present here.

## Team (`team_members`, legacy `teams`)
2 real records, migrated from `TeamSeeder.php`:

| name | role | status |
|---|---|---|
| Majd Bayer | CEO & Founder | REAL BUT NEEDS REVIEW — real email/GitHub present, `bio` empty, `linkedin_url` empty, photo/CV are Google Drive share links (not CMS media) |
| Mohamad Kahal | Senior Frontend Engineer | same gaps |

**Founder input required**: approved bios, LinkedIn URLs, and CMS-uploaded (not Drive-linked) photos.

## Testimonials (`testimonials`, legacy `Review`)
5 rows migrated from `ReviewSeeder.php` — Arabic client quotes with named individuals/companies, ratings 4–5, `is_approved=1` in the DB flag, but **no verifiable publication-permission record exists in the repo**. `author_title`/`company`/`company_logo` are empty; company name is baked into free-text `author_name`.

**Classification: REAL BUT NEEDS REVIEW → treat as MUST NOT PUBLISH until the founder confirms each one has actual source permission.** Do not treat the `is_approved=1` DB flag as proof of real-world consent.

## Legal pages
Privacy and Terms pages are self-documented in code comments as generic structurally-complete boilerplate, explicitly "not a substitute for legal review" — see `frontend/app/[locale]/privacy/page.tsx` and `terms/page.tsx`. **Classification: DEMO/PLACEHOLDER**, pending legal sign-off.

## Media
- Real, purpose-built assets: `frontend/public/media/hero-intro.mp4` + poster, `showreel.mp4` + poster, `logo.svg`.
- Placeholder sources still wired into `next.config.ts` `remotePatterns` (intentionally, for legacy data): `cdn-icons-png.flaticon.com` (all 12 services), `placehold.co` (all 8 case studies), `drive.google.com` (2 team photos, interim).
- No cover image exists for either System or either Industry.
- **Classification: MISSING MEDIA** across services, case studies, systems, industries; team photos are interim Drive links.

## SEO metadata
`seo_meta` table (polymorphic, backs every content type) has **0 rows**. `php artisan hexa:content-report` confirms all 24 currently-published records (12 services, 2 systems, 8 case studies, 2 industries) are flagged `missing SEO metadata row`. **Classification: MISSING SEO, 100% of published content.**

## Contact / CTA wiring
Only working contact channel is the lead form → `ContactLead` model → `LeadController`. A generic `mailto:` exists on legal pages only (not marketing pages). `whatsapp` and `booking_url` fields exist on Company Settings but have **no frontend consumer anywhere** — filling them in Filament today would not surface a CTA on any page.

## `hexa:content-report` baseline (current DB)
`findings: 88, unpublished: 0, missing_arabic: 40, missing_english: 20, missing_seo: 24, failed_ai_generations: 0`

---

**Bottom line**: the platform has real, named team members and industry-plausible systems/settings scaffolding, but almost no content in this repository is launch-ready without founder-supplied facts — positioning copy, verified service descriptions, real systems/case-study evidence, bios, testimonial permission, legal review, and media. Per this sprint's own constraint, none of the above will be fabricated; the next step is collecting that real input from the founder.
