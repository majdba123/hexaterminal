# Content & Marketing Gap Audit

Code-verified audit performed at the start of the Content, Marketing, and
Product Completion Sprint (branch `feature/hexa-content-marketing-completion`,
base `5d61d88`). Every claim cites a repository path. Classifications:
COMPLETE / PARTIAL / NOT STARTED / LEGACY ONLY / BLOCKED.

## Content models

| Entity | Status | Evidence |
|---|---|---|
| Service | **COMPLETE** (base) | `app/Models/Service.php` → `service_offerings` (`database/migrations/2026_07_15_070000_*`): EN/AR name/tagline/summary/description, features, tech_stack, icon, cover, publish, sort |
| System (unified, typed) | **COMPLETE** | `app/Models/System.php`: 6 controlled types, problem/solution/features/outcomes/audience/tech, gallery, demo/live URLs, featured, industries pivot |
| CaseStudy | **COMPLETE** | `app/Models/CaseStudy.php`: context/problem/constraints/solution/architecture/outcomes/**evidence** labels, client_name nullable (anonymizable), relations, legacy id |
| Industry | **COMPLETE** | `app/Models/Industry.php` + pivots to systems/case studies |
| Article | **PARTIAL** | `app/Models/Article.php`: title/excerpt/body/cover/author/publish only. **Missing: category, tags, OG image, featured flag, related content, reading time** |
| ArticleCategory / ArticleTag | **NOT STARTED** | no models/migrations exist |
| Author/TeamMember | **COMPLETE** | `app/Models/TeamMember.php`; Article author → `users` FK |
| Testimonial | **COMPLETE** | `app/Models/Testimonial.php` (approved + featured scopes) |
| FaqItem | **COMPLETE** | `app/Models/FaqItem.php` |
| ContactLead | **PARTIAL** | `app/Models/ContactLead.php`: single "start a project" intake. **Missing: intent/form_type, WhatsApp, company size, role, requested service/system relations, consent, landing page, first touch, follow-up, assignment, spam/archived states, scoring** |
| SeoMeta | **COMPLETE** (base) | `app/Models/SeoMeta.php` polymorphic: EN/AR title/description, canonical, og_image, noindex/nofollow. No editorial validation warnings |
| Redirect | **COMPLETE** | `app/Models/Redirect.php` + `hexa:migrate-legacy-content` populator |
| MediaAsset | **PARTIAL (deliberate)** | one coherent approach exists: Filament `FileUpload` → `public` disk, served by hardened `/api/storage/{path}` (`routes/api.php`). No separate media library model — acceptable; a second system will not be built |
| AiGeneration | **PARTIAL** | table + model + Filament resource exist (`2026_07_15_071100_*`, `app/Filament/Resources/AiGenerations`) with provenance/cost/status. **No generator, no provider abstraction, no locale/latency columns, no review UX** |
| CompanySetting | **NOT STARTED** | company constants live in translations/env |
| NewsletterSubscriber | **NOT STARTED** | — |
| UseCase (standalone) | **REJECTED by design** | use cases embed into Systems (`target_audience`, `business_outcomes`) and Industries — standalone model adds no commercial value yet |

## Filament CMS

- 11 resources exist (`app/Filament/Resources/{AiGenerations,Articles,CaseStudies,ContactLeads,FaqItems,Industries,Redirects,Services,Systems,TeamMembers,Testimonials}`) — **COMPLETE** as basic CRUD, translatable via LaraZeus plugin (`CmsPanelProvider`).
- Publishing controls are a bare `Toggle('is_published')` (e.g. `Articles/Schemas/ArticleForm.php`) — **PARTIAL**: no draft→review→approve workflow, no audit columns.
- **Dashboards/widgets: NOT STARTED** — `app/Filament/Widgets` does not exist; panel registers only the stock Account/FilamentInfo widgets.
- **Lead operations: PARTIAL** — table + form exist, but statuses are limited (`new/contacted/qualified/won/lost`), no assignment, follow-up, spam/archive, export, or scoring.
- Roles: `admin`, `editor` via spatie (`database/seeders/RolesSeeder.php`) — **COMPLETE** (base).

## Public API (v1)

- **COMPLETE** for existing entities: `routes/api_v1.php` — home, services, systems, case-studies, industries, articles, team, testimonials, faqs, redirects, leads; published-only via `Publishable` scope; typed Resources in `app/Http/Resources/V1/Public`; caching + observer invalidation (`app/Observers/ClearsPublicApiCache.php`); secure revalidation (`app/Services/RevalidationService.php`).
- **NOT STARTED**: search endpoint, public company-settings endpoint, article category/tag filtering, newsletter endpoint, RSS feed.

## Frontend

- **COMPLETE**: 14 routes (`frontend/app/[locale]/**/page.tsx`): home, services hub+detail, systems, case-studies, industries, insights, about, contact, start-a-project; localized 404/error; EN/AR + RTL; dark/light; SEO/JSON-LD/sitemap/robots/redirects; homepage sections (hero, services, systems, showreel, case studies, industries, process, insights, testimonials, FAQ, CTA) are CMS-driven (`frontend/app/[locale]/page.tsx`).
- **NOT STARTED**: search page, insights category filtering/archives, RSS, privacy policy, terms, analytics integration.
- **PARTIAL**: lead form (`frontend/components/site/lead-form.tsx`) posts core fields but **never sends UTM/attribution** despite the API accepting `utm`; single intent only.

## Cross-cutting

| Area | Status | Evidence |
|---|---|---|
| Localization | **COMPLETE** | spatie translatable everywhere; `api.locale` middleware |
| Publishing workflow | **PARTIAL** | `app/Models/Concerns/Publishable.php`: is_published + published_at only; scheduled works implicitly (future `published_at` hidden by scope); no review states, no auditability |
| Revision/audit log | **NOT STARTED** | no activity table, no created_by/updated_by columns |
| Media validation | **PARTIAL** | Filament `FileUpload->image()` on covers; no explicit size/dimension rules, alt text only on legacy `Imag_Progect` |
| SEO controls | **PARTIAL** | SeoMeta CRUD present; no quality warnings/validation |
| AI SEO | **PARTIAL** | provenance store only; no provider, no generation, no review flow |
| Attribution | **PARTIAL** | API accepts `source_page/referrer/utm` (`LeadController`); frontend never captures/sends UTM |
| Lead anti-spam | **PARTIAL** | honeypot + `throttle:5,1` (`LeadController`, `routes/api_v1.php`); no duplicate/replay guard |
| Notifications | **NOT STARTED** | no mailables/notifications for leads |
| Search | **NOT STARTED** | — |
| Analytics | **NOT STARTED** | — |
| Content completeness reporting | **NOT STARTED** | — |
| Tests | **PARTIAL** | 91 backend tests + 20 Playwright cover existing surface; nothing for the gaps above |

## Sprint implementation order

1. Content models + editorial workflow (migrations, traits, activity log)
2. Filament completion (workflow controls, taxonomies, lead ops, AI review, settings, dashboards)
3. Marketing/lead system (intents, attribution, scoring, notifications, newsletter)
4. AI SEO provider + internal links + completeness report
5. Public frontend + API completion (search, categories, RSS, legal pages, analytics boundary)
6. Tests, gates, founder documentation
