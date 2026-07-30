# Content Model — Hexa Terminal

Phase 2 of the transformation. This document describes the new content-model
tables/models introduced alongside (not replacing) the legacy schema, why they're
separate, and how data flows from old to new.

## Why new tables instead of altering the legacy ones

The legacy Blade frontend and `/admin` panel stay live and functional until the
Next.js/Filament cutover (Stage 20). Altering legacy tables in place would risk
breaking that frontend for zero benefit, since there is no real production data
at stake in this environment (verified: no reachable database was configured
when this phase started). Every new entity therefore gets its own table, and a
one-way, idempotent data-migration command copies existing legacy rows across.
The one exception considered and rejected was renaming `services` → the new
`Service` model's table: `services` carries a real foreign key from
`projects.service_id`, so touching it added risk for no gain. The new `Service`
model instead uses table `service_offerings`.

**Windows/macOS filesystem gotcha:** `Faq.php` and the legacy `FAQ.php`
resolve to the same file on case-insensitive filesystems. The new FAQ
entity's class is therefore named `FaqItem` (table `faqs`), not `Faq`.

## Entities

| Model | Table | Translatable fields | Notes |
|---|---|---|---|
| `Service` | `service_offerings` | name, tagline, summary, description | Commercial offerings (SaaS, CRM, ERP, AI, backend, automation) |
| `System` | `systems` | name, tagline, short/full description, problem, solution, features, business_outcomes, target_audience | Unified catalog — one model with a `type` enum (`saas_product`, `business_system`, `client_system`, `internal_product`, `platform`, `ai_system`) instead of separate overlapping models |
| `CaseStudy` | `case_studies` | title, summary, context, problem, constraints, solution, architecture, outcomes, evidence, features | Consolidates legacy `Projects` + `Imag_Progect` + `Fetures_Project` |
| `Industry` | `industries` | name, summary, description | M2M with `System` and `CaseStudy` |
| `Article` | `articles` | title, excerpt, body | Blog; `author_id` → `users` |
| `TeamMember` | `team_members` | position, bio | Successor to legacy `Team`; `first_name`/`last_name`/`specialization` stay plain (not translatable — proper names and job titles don't need it) |
| `Testimonial` | `testimonials` | content | Successor to legacy `Review`; keeps the same moderation pattern (`is_approved`, `scopeApproved`) |
| `FaqItem` | `faqs` | question, answer | See filesystem note above |
| `ContactLead` | `contact_leads` | — | Successor to legacy `Contact_Us`; richer fields for the single "Start a Project" flow (Stage 17): company, country, project_type, budget_range, timeline, UTM, status/priority/notes |
| `SeoMeta` | `seo_meta` | title, description | Polymorphic (`seoable_type`/`seoable_id`) per-page SEO override; every content model exposes a `seo()` morphOne relation. Optional — pages render fine without a row here |
| `Redirect` | `redirects` | — | `from_path` → `to_path`, `status_code` (301/302), hit tracking |
| `AiGeneration` | `ai_generations` | — | Stage 14 provenance/audit trail. See docs/architecture (Phase 7) |

Relationships: `System belongsToMany Industry` (pivot `industry_system`);
`CaseStudy belongsToMany Industry` (pivot `case_study_industry`); `CaseStudy
belongsTo Service` (nullable, `service_offering_id`); `CaseStudy belongsTo
System` (nullable); `System hasMany CaseStudy`; `Industry hasMany Article`.

## Shared behavior

- `App\Models\Concerns\Publishable` — `scopePublished()` (used by Service,
  System, CaseStudy, Industry, Article; TeamMember and FaqItem have their own
  simpler published scope since they don't schedule via `published_at`).
- `App\Models\Concerns\HasAutoSlug` — generates a unique slug from a
  model-defined source attribute on creation if none was given explicitly.
  Slugs are always derived from the English text so URLs stay stable and
  Latin regardless of which locale content was entered in first.
- `spatie/laravel-translatable` (`HasTranslations`) — translatable attributes
  are JSON columns; untranslated locales fall back to `config('app.fallback_locale')`
  (`en`). AR content is entered by the content team over time — there is no
  fake machine translation seeded.

## Legacy data migration

`php artisan hexa:migrate-legacy-content [--dry-run]`
(`app/Console/Commands/MigrateLegacyContent.php`)

One-way, additive, idempotent: legacy tables/models are never modified or
deleted. Idempotency is enforced via unique `legacy_*_id` columns
(`testimonials.legacy_review_id`, `contact_leads.legacy_contact_id`,
`case_studies.legacy_project_id`) or by slug lookup (Service, TeamMember),
so running the command repeatedly updates existing migrated rows rather than
duplicating them.

Mapping:
- `Services` → `Service`: `title`→`name` (en), `description`→`description` (en),
  `image_path`→`cover_image`, published immediately (legacy rows are already live).
- `Team` → `TeamMember`: direct field copy; `position`→`position` (en, translatable).
- `Review` → `Testimonial`: `name`→`author_name`, `content`→`content` (en),
  **existing `is_approved` value preserved exactly** — a testimonial approved
  under the old system stays approved, nothing is silently re-exposed or hidden.
- `Contact_Us` → `ContactLead`: `subject`+`message` combined into `summary`;
  `status` mapped `pending→new`, `in_progress→contacted`, `completed→qualified`
  (deliberately not `won`, since "completed" only means the message was
  handled, not that a deal was closed).
- `Projects` (+ `Imag_Progect` images + `Fetures_Project` features) → `CaseStudy`:
  `title`→`title` (en), `description`→`context` (en), image paths → `gallery`
  array (+ first image → `cover_image`), feature texts → `features` (en, array),
  `service_id` resolved to the matching migrated `Service` by slug.
  `problem`/`constraints`/`solution`/`architecture`/`outcomes`/`evidence` are
  left blank for CMS editors to fill in — legacy project records don't have
  this structured, outcome-focused data.

Run this command against a real database only during actual cutover
(Stage 20), after content editors have reviewed what it produces — it publishes
migrated rows immediately by default (`is_published = true`) since they mirror
already-live legacy content.

## Deferred / not built in this phase

- `Video` model/table: left untouched. Its role (showreel/media references)
  will be settled once the Next.js media integration (Stage 8) is designed;
  folding it into `System`/`CaseStudy` galleries or keeping it standalone are
  both plausible and shouldn't be decided prematurely.
- Roles/permissions (`spatie/laravel-permission`, installed in this phase):
  wired into Filament in Phase 3. The legacy `type == 1` check keeps working
  until then.
- Filament resources, the versioned public API, and Next.js consumption of
  these models are Phases 3–5.

## Known static-analysis debt (Larastan, not fixed in this phase)

Four findings remain, all the same category — dynamic property access on
legacy Eloquent models that don't declare `@property` PHPDoc annotations
(`Projects::$title`, `Imag_Progect::$image_path` via `ProjectService`, and
`User`'s `$fillable`/`$hidden` PHPDoc covariance against the base Model class).
Fixing these means touching legacy model files, which this phase deliberately
avoids per the "legacy stays untouched until cutover" principle above. They're
tracked here rather than silently suppressed in `phpstan.neon`.
