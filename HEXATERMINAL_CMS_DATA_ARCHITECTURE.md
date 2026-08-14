# HexaTerminal CMS Data Architecture

**Repository snapshot:** current working tree, documented from migrations, Eloquent models, Filament resources/forms, public API controllers/resources, and Next.js consumers. This document does not infer runtime database state. `NOT VERIFIED` means the repository does not prove the point.

## Architecture Rules

- **CMS:** Filament panel at `/cms`, configured in `app/Providers/Filament/CmsPanelProvider.php`; the translation plugin declares `en` and `ar`.
- **Translations:** content models use `spatie/laravel-translatable` (`HasTranslations`). A public request has `?locale=en|ar`; `app/Http/Middleware/SetApiLocale.php` sets the application locale, then resources read translated attributes. Slugs are shared Latin URL identifiers, not localized.
- **Slugs:** `app/Models/Concerns/HasAutoSlug.php` derives an absent slug from English content; `app/Filament/Support/Slugs.php` permits only lowercase alphanumeric hyphen segments and forms additionally enforce uniqueness. Explicit valid slugs are retained.
- **Editorial publishing:** `app/Models/Concerns/HasEditorialWorkflow.php` uses `draft`, `in_review`, `approved`, `scheduled`, `published`, `archived`. Status syncs `is_published`; `published` gets `published_at` when missing. `app/Models/Concerns/Publishable.php` exposes only `is_published=true` and `published_at <= now` (or null). The shared Admin fields are in `app/Filament/Support/PublishingSection.php`.
- **Public API:** `routes/api_v1.php` exposes published/approved records under `/api/v1/public`; resource classes are in `app/Http/Resources/V1/Public`. Next server components call them from `frontend/lib/api/client.ts`.

## IMAGE / MEDIA RULES

| Rule | Verified repository behavior |
|---|---|
| Image inputs | `app/Filament/Support/Uploads.php`: JPEG, PNG, WebP, AVIF, GIF only. SVG is intentionally rejected. |
| Documents | PDF only through `Uploads::document()`. |
| Size limits | No per-field max size is configured in the inspected form helpers. Livewire default cited by the helper is 12 MB; **NOT VERIFIED** as an enforced application-level maximum. |
| Disk/path | CMS image fields use Filament upload handling and named directories such as `service-offerings`, `systems`, `case-studies`, `articles`, `team`, `company`. The public filesystem disk root is `storage/app/public` (`config/filesystems.php`). |
| URL serving | `routes/api.php` serves allowlisted public-disk paths at `/api/storage/{path}`; extensions include raster formats and PDF. `Storage::url()` is not used by the new public resources. Stored CMS values are filesystem paths unless a legacy model/service stores a URL. |
| Replacement/deletion | New Filament models do not define file-delete observers. **NOT VERIFIED** whether FileUpload removes superseded files automatically. Legacy `Services`, `Team`, `Projects`, and related legacy services have independent delete logic and are not the current CMS content path. |
| Aspect ratio/dimensions | No enforced dimensions or aspect-ratio rules found. Frontend presentation defines aspect ratios (for example 16:9 cards); upload validation does not. |
| Seeder imports | `database/seeders/ServicesSeeder.php` safely imports packaged local PNGs to `Storage::disk('public')` using deterministic `service-offerings/<filename>.png` paths and stores only the relative path. |

## DATA FLOW MAP

`Filament Admin form -> Eloquent model/table -> Public controller/resource -> Next.js consumer`

| Entity | Flow |
|---|---|
| Services | `Services/Schemas/ServiceForm.php -> Service / service_offerings -> ServiceController / ServiceResource -> frontend/app/[locale]/services/*`, home sections |
| Systems | `Systems/Schemas/SystemForm.php -> System / systems -> SystemController / SystemResource -> frontend/app/[locale]/systems/*`, home |
| Case studies | `CaseStudies/Schemas/CaseStudyForm.php -> CaseStudy / case_studies -> CaseStudyController / CaseStudyResource -> frontend/app/[locale]/case-studies/*`, home |
| Industries | `Industries/Schemas/IndustryForm.php -> Industry / industries -> IndustryController / IndustryResource -> frontend/app/[locale]/industries/*` |
| Articles | `Articles/Schemas/ArticleForm.php -> Article / articles -> ArticleController / ArticleResource -> frontend/app/[locale]/insights/*`, home, RSS |
| Team | `TeamMembers/Schemas/TeamMemberForm.php -> TeamMember / team_members -> TeamMemberController / TeamMemberResource -> frontend/app/[locale]/about`, `/team` |
| Testimonials | `Testimonials/Schemas/TestimonialForm.php -> Testimonial / testimonials -> TestimonialController / TestimonialResource -> home payload` |
| FAQs | `FaqItems/Schemas/FaqItemForm.php -> FaqItem / faqs -> FaqController / FaqResource -> home and PricingController` |
| Engagement/pricing | `EngagementModels/PricingProfiles forms -> engagement_models/pricing_profiles -> PricingController / EngagementModelResource -> frontend/app/[locale]/pricing` |
| Estimator | `EstimatorVersions form -> estimator_versions (+ questions/rules) -> EstimatorController / EstimateResource -> frontend estimator and /estimate/[uuid]` |
| Settings | `CompanySettingsPage -> CompanySetting / company_settings -> SettingsController whitelist -> contact, privacy, terms, footer consumers` |
| Trust | `TrustPages/PublicClaims forms -> trust_pages/public_claims -> TrustPageController/Resource and embedded claims -> trust-page view` |
| Leads | public forms -> `LeadController` -> `ContactLead / contact_leads`; CMS is operations-only and has no public read resource |
| Redirects | `RedirectForm -> Redirect / redirects -> RedirectController / RedirectResource -> frontend/next.config.ts build redirect fetch` |

## Entity Reference

### Services

- **Files:** model `app/Models/Service.php`; schema `database/migrations/2026_07_15_070000_create_service_offerings_table.php` plus alt text migration `2026_07_26_090000_add_image_alt_text_to_content_tables.php`; Admin `app/Filament/Resources/Services/Schemas/ServiceForm.php`; API `app/Http/Controllers/Api/V1/Public/ServiceController.php`, `app/Http/Resources/V1/Public/ServiceResource.php`.
- **Table/columns:** `service_offerings`: `id`; unique required `slug`; required JSON `name`; nullable JSON `tagline`, `summary`, `description`, `features`, `tech_stack`, `cover_image_alt`; nullable strings `icon`, `cover_image`; `is_published` boolean default false; nullable timestamp `published_at`; unsigned `sort_order` default 0; editorial `status` default `draft`, audit FKs/timestamp, timestamps.
- **Casts/localization:** JSON translations: `name`, `tagline`, `summary`, `description`, `cover_image_alt`, `features`; arrays: `features`, `tech_stack`; booleans/datetime as model casts. `features` is localized as `{en: string[], ar: string[]}`; `tech_stack` is a nonlocalized `string[]`.
- **Admin mapping:** text inputs for name/tagline/icon; textareas for summary/description; `TagsInput` for features/tech_stack; image upload `cover_image` to `service-offerings`; alt text to `cover_image_alt`; shared status/date/sort. Required: name, slug, status, sort order. The form has no `details` field.
- **Relations/order:** `hasMany CaseStudy`; `morphOne SeoMeta`. `coreServices()` pins the three approved core slugs in business order; public listing then uses `sort_order`.
- **Seeder:** `database/seeders/ServicesSeeder.php`, called by `DatabaseSeeder`; source JSON/assets are under `database/seeders/data` and `database/seeders/assets/services`.

### Systems

- **Files:** `app/Models/System.php`; `database/migrations/2026_07_15_070200_create_systems_table.php`; `app/Filament/Resources/Systems/Schemas/SystemForm.php`; `SystemController.php`, `SystemResource.php`; frontend `frontend/app/[locale]/systems/(list)/page.tsx`, `[slug]/page.tsx`.
- **Columns:** unique `slug`; required enum `type` (`saas_product`, `business_system`, `client_system`, `internal_product`, `platform`, `ai_system`); nullable category; translated JSON narrative fields (`name` required, tagline, short/full description, problem, solution, features, business outcomes, target audience); JSON `tech_stack`, `gallery`; strings `cover_image`, `demo_url`, `live_url`; JSON `cover_image_alt`; booleans featured/published default false; nullable published timestamp; sort default 0; editorial columns/audit FKs.
- **Admin:** select type; text/category and narrative inputs; `TagsInput tech_stack`; image/gallery uploads to `systems` and `systems/gallery`; URL validation for demo/live; multi-select industries through pivot `industry_system`; featured and publishing section.
- **Relations:** many-to-many industries; has-many case studies; one SEO meta. Public controller filters `published`, supports type/featured filtering, sorts by `sort_order`.
- **Seeder:** `WebsitePreviewSeeder` creates current preview systems.

### Case Studies

- **Files:** `app/Models/CaseStudy.php`; `2026_07_15_070300_create_case_studies_table.php`, `2026_08_13_000000_add_project_classification_to_case_studies_table.php`; form `app/Filament/Resources/CaseStudies/Schemas/CaseStudyForm.php`; `CaseStudyController.php`, `CaseStudyResource.php`; frontend `frontend/app/[locale]/case-studies/(list)/page.tsx`, `[slug]/page.tsx`.
- **Columns:** unique `slug`; required JSON `title`; nullable translated JSON `summary`, `context`, `problem`, `constraints`, `solution`, `architecture`, `outcomes`, `evidence`, `features`, `cover_image_alt`; nullable strings client name/project/video URL/cover image; JSON gallery; nullable `service_offering_id` and `system_id` FKs with `nullOnDelete`; nullable indexed `project_classification`; featured/published default false, published timestamp, sort default 0, unique nullable `legacy_project_id`, editorial columns.
- **Classification vocabulary:** `custom_erp_crm`, `web_mobile_platform`, `ecommerce_business_website`; nullable and validated in the Admin select. It does not replace service/system/industry relations.
- **Admin:** textareas map directly to narrative JSON translations; multi-line `features` is one translated string; industry multi-select syncs `case_study_industry`; URLs use URL validation; uploads use `case-studies` and `case-studies/gallery`; service/system selects; featured plus shared publishing fields.
- **Relations:** belongs to Service/System; belongs-to-many Industries; one SEO meta. Public listing/detail returns only published rows, ordered by sort; featured home data is limited to six.
- **Seeder:** `WebsitePreviewSeeder` creates preview rows and attaches approved services/systems/industries.

### Industries

- **Files:** `app/Models/Industry.php`; `2026_07_15_070100_create_industries_table.php`; `Industries/Schemas/IndustryForm.php`; `IndustryController.php`, `IndustryResource.php`; frontend `frontend/app/[locale]/industries/*`.
- **Columns/casts:** unique slug, required translated JSON name; nullable translated summary/description/cover alt; nullable icon/cover image; published default false, nullable published date, sort default 0, editorial columns. No model array casts beyond publication datetime/boolean.
- **Admin:** name required, slug required/unique, summary/description/icon, cover upload `industries`, alt text, shared publishing. Relations are systems and case studies through pivots; model also declares `hasMany Article`, but the inspected articles migration uses `article_category_id`, not `industry_id`: **NOT VERIFIED / model-schema mismatch**.
- **Seeder:** `WebsitePreviewSeeder`.

### Articles, categories, tags

- **Files:** models `Article.php`, `ArticleCategory.php`, `ArticleTag.php`; schema `2026_07_15_070400_create_articles_table.php`, `2026_07_16_100000_create_article_taxonomies.php`; forms under `Articles`, `ArticleCategories`, `ArticleTags`; API Article/ArticleCategory controllers/resources; frontend `frontend/app/[locale]/insights/*`, `frontend/app/rss.xml/route.ts`.
- **Article columns:** unique slug; required translated JSON title; nullable translated excerpt/body/cover alt; nullable cover/OG image; nullable author user FK and article category FK; featured/published booleans default false; nullable published and updated-content dates; editorial audit/status. Tags are many-to-many `article_article_tag`.
- **Admin:** title/slug required; textarea excerpt/body; uploads `articles` and `articles/og`; author/category selects, tag multi-select, featured toggle, updated date, shared publishing (no sort field).
- **Category/tag:** category has required translated name, unique slug, nullable translated description, sort default 0. Tag has required translated name and unique slug. Their forms enforce name/slug; category exposes description/sort. Category API includes only categories with published articles.
- **Seeder:** none.

### Team Members

- **Files:** `app/Models/TeamMember.php`; migrations `2026_07_15_070500_create_team_members_table.php`, `2026_07_22_140200_add_governance_fields_to_team_members_table.php`, alt migration; form `TeamMembers/Schemas/TeamMemberForm.php`; `TeamMemberController.php`, `TeamMemberResource.php`; frontend `/about`, `/team`.
- **Columns:** unique slug; required first name, nullable last name; translated JSON position/bio/photo alt; nullable specialization, email, phone, image/link/document strings; JSON arrays expertise/languages; location; publication/consent/founder/SEO/Person-JSON-LD booleans (all default false except `is_published` true); review dates; sort default 0.
- **Public rule:** `TeamMember::published()` requires both `is_published` and `publication_consent`. Person JSON-LD further requires eligibility/name/English bio. Admin collects image `team`, CV PDF `team/cv`, tags arrays, URLs and governance toggles.
- **Seeder:** none.

### Testimonials and FAQs

- **Testimonials:** `Testimonial.php`; migration `2026_07_15_070600_create_testimonials_table.php`; form `Testimonials/Schemas/TestimonialForm.php`; public `TestimonialController/Resource`; home payload. Required author name and translated JSON content; rating 1-5 form select, DB unsigned tiny int default 5; nullable title/company/logo/date; approval/featured false by default; unique nullable legacy review ID. Logo upload directory `testimonials`. Public scope requires approval; featured home list sorts given date descending. Seeder: none.
- **FAQs:** `FaqItem.php` maps `faqs`; migration `2026_07_15_070700_create_faqs_table.php`; form `FaqItems/Schemas/FaqItemForm.php`; `FaqController/FaqResource`; home and pricing API. Required translated JSON question/answer; nullable category; published default true; sort default 0. Admin fields map directly; public list filters published then sort. Seeder: none.

### Engagement Models, Pricing Profiles, Estimator, and Cost Estimates

- **Engagement model:** `EngagementModel.php`, migration `2026_07_19_100000_create_engagement_models_table.php`, form `EngagementModels/Schemas/EngagementModelForm.php`, `PricingController/EngagementModelResource`, frontend pricing. Slug unique; translated JSON commercial copy; display enum `hidden|request_quote|starting_from|indicative_range|fixed_package`; billing enum `fixed_project|milestone_based|monthly_retainer|discovery_sprint|dedicated_team|support_plan`; booleans false and sort 0. The form uses textareas for translated list-like fields, so DB values are localized strings, not repeaters. Has polymorphic pricing profiles and SEO meta.
- **Pricing profile:** `PricingProfile.php`, migration `2026_07_19_100100_create_pricing_profiles_table.php`, form `PricingProfiles/Schemas/PricingProfileForm.php`. Morphs to priceable; Admin restricts form default to `EngagementModel::class`. Currency USD/AED/SAR, nullable min/max unsigned integers, unit/billing strings, translated JSON display label/assumptions/exclusions/disclaimer, approval FKs/timestamps and effective/review dates. Unique per priceable/currency. Public profile is fail-closed: approved and effective only, and hidden/request-quote modes return no numeric profile.
- **Estimator version:** `EstimatorVersion.php` and related Question/Rule models; migrations `2026_07_19_100200` through `100500`; `EstimatorVersions/Schemas/EstimatorVersionForm.php`; public `EstimatorController/EstimateResource`; frontend estimator/API routes. Version key unique, status draft/active/archived, single active flag, base currency, JSON rates, guardrails, notes and activator/creator FKs. Questions/rules are related tables and configured by relation managers: **NOT VERIFIED** because no separate Admin resource form was found. Cost estimates are generated/triaged records, not public CMS content; `CostEstimates/Schemas/CostEstimateForm.php` is mostly read-only and exposes only status select.
- **Seeders:** none.

### Company Settings, navigation, footer, homepage

- **Files:** `app/Models/CompanySetting.php`; migration `2026_07_16_100300_create_company_settings_table.php`; Admin page `app/Filament/Pages/CompanySettingsPage.php`; public `SettingsController.php`; frontend contact/privacy/terms and site layout components.
- **One-row behavior:** `CompanySetting::current()` uses `firstOrCreate([])` and a five-minute cache. Columns: translated JSON company name/tagline/description/address/footer note; nullable contact strings, booking URL, default OG image; JSON social links; internal `lead_recipients`, analytics provider/site ID; timestamps. Public controller whitelists public fields and does not expose recipients/analytics.
- **Navigation/footer:** route metadata is code-managed in `frontend/lib/routes.ts` (and related site navigation components), not CMS tables. Trust pages additionally contain `show_in_nav` and `show_in_footer`; this is separate from main route registry behavior. **NOT VERIFIED** that current frontend renders those dynamic Trust page flags.
- **Home dynamic data:** `HomeController.php` supplies published core services, six featured systems, six featured case studies, approved featured testimonials, and calculated counts. `frontend/app/[locale]/page.tsx` combines that with articles and FAQs. Hero/navigation copy remains translation/code-driven unless supplied by the API above.

### Leads / Contact / Start a Project

- **Files:** `ContactLead.php`; migrations `2026_07_15_070800_create_contact_leads_table.php`, `2026_07_16_100500_extend_contact_leads_table.php`; Admin `ContactLeads/Schemas/ContactLeadForm.php`; public `LeadController.php`; Next proxy `frontend/app/api/leads/route.ts`, pages `/contact` and `/start-a-project`.
- **Storage:** identity/project/contact/attribution input plus JSON UTM, requested service/system nullable FKs, consent, locale, lifecycle status/priority, assignment/follow-up and deterministic score/breakdown. Public controller validates submission and resolves supplied slugs to internal IDs. CMS leaves incoming fields read-only; operations may edit qualification summary, status, priority, assignee, follow-up, notes.
- **Status/options:** original enum migration lists `new/contacted/qualified/won/lost`, but later migration changes `status` to string. Exact current Admin options are defined in `ContactLeadForm.php`; see that file as source of truth. No public read API. Seeder: none.

### SEO, Trust, Claims, Redirects, AI

- **SEO meta:** `SeoMeta.php`, `2026_07_15_070900_create_seo_meta_table.php`, and polymorphic `seo()` relations on content models. Columns translated JSON title/description, canonical, OG image, noindex/nofollow default false. There is no standalone Filament SEO resource; SEO is managed through the model relation/AI approval path. **NOT VERIFIED** whether every content edit exposes SEO fields directly in Admin.
- **Trust pages:** `TrustPage.php`, `2026_07_22_140000_create_trust_pages_table.php`, `TrustPages/Schemas/TrustPageForm.php`, controller/resource, `components/site/trust-page-view.tsx`. Required type/title/slug; translated JSON summary/sections/faqs/CTA; governance FKs/approvals; standard editorial state; noindex default true; nav/footer flags; review dates. It can publish only with sections/title and required founder/legal/security approvals per page type.
- **Public claims:** `PublicClaim.php`, `2026_07_22_140100_create_public_claims_table.php`, form `PublicClaims/Schemas/PublicClaimForm.php`, resource. Polymorphic optional attachment, locale string, category vocabulary and verification status (`unverified|pending|verified|rejected`), evidence/internal notes, approvals/review metadata. Public scope requires verified, approved, non-confidential and unexpired.
- **Redirects:** `Redirect.php`, `2026_07_15_071000_create_redirects_table.php`, `Redirects/Schemas/RedirectForm.php`, controller/resource. Required unique from path/to path; status default 301; active default true; operational hit fields. Frontend obtains active redirects at Next config startup.
- **AI generations:** `AiGeneration.php`, `2026_07_15_071100_create_ai_generations_table.php`, `AiGenerations/Schemas/AiGenerationForm.php`. Audit rows only; form makes provenance/status read-only and permits editing output for review. Migration proves pending/generated/reviewed/approved/rejected/failed; model additionally defines `generating`. Model/form include `system_prompt_id`, `locale`, `latency_ms`, `error_category`, but no inspected migration creates them: **NOT VERIFIED / schema mismatch**. It has no public frontend API.

### Admin users, roles, and permissions

- **Files:** `app/Models/User.php`; migrations `0001_01_01_000000_create_users_table.php`, `2026_07_15_063250_create_permission_tables.php`, `2026_07_24_030000_add_app_authentication_to_users_table.php`; bootstrap `database/seeders/UsersTableSeeder.php`.
- **User fields/casts:** name/email/password/type/phone and Laravel timestamps; password hashed, email verification datetime, TOTP secret encrypted, recovery codes encrypted array and hidden. The current Filament panel allows `admin` or `editor` Spatie roles. Legacy `type=1` remains for legacy admin only. MFA is required for admins by CMS provider configuration.
- **Admin management UI:** no Filament User/Role resource was found. Users/roles are bootstrap/security-managed rather than a documented CMS CRUD surface.

## SEEDER MAP

| Seeder | Populates / behavior | Execution |
|---|---|---|
| `UsersTableSeeder.php` | bootstrap Admin user and roles from configuration; does not print credential values | first in `DatabaseSeeder` |
| `ServicesSeeder.php` | exactly the three approved `service_offerings`, public-disk service images | second in `DatabaseSeeder`; deterministic by slug |
| `WebsitePreviewSeeder.php` | preview industries, systems, case studies, related SEO; obtains approved services and only invokes ServicesSeeder if they are missing | third in `DatabaseSeeder`; placeholder/demo preview content |
| `DatabaseSeeder.php` | calls Users, Services, WebsitePreview in that order | `php artisan db:seed` / fresh seed |

No other Seeder classes are present. Production suitability depends on deployment policy and seed flag; the repository cannot prove which environment will execute a fresh seed.

## CMS FIELD MATRIX

| Entity | Admin field | DB column | Type | Required | Localized | Seeder format | Frontend usage |
|---|---|---|---|---|---|---|---|
| Service | name/tagline/summary/description | same | JSON | name | yes | EN/AR maps | services/home |
| Service | features/tech stack | same | JSON arrays | no | features yes, stack no | arrays | service detail |
| Service | cover/alt | cover_image/cover_image_alt | string/JSON | no | alt yes | public-disk path | cards/detail |
| System | content/type/category | narrative/type/category | JSON/enum/string | name/type | narrative yes | preview | systems/home |
| System | industries/media/tech | pivots/images/arrays | pivot/string/JSON | no | alt yes | preview | detail/cards |
| Case study | narrative/classification | JSON/string | JSON/string | title | narrative yes | preview | listing/detail/home |
| Case study | service/system/industries | FKs/pivot | FK/pivot | no | no | preview | context links |
| Industry | content/media | name/summary/description/images | JSON/string | name | yes except icon | preview | industries |
| Article | content/category/tags/media | JSON/FK/pivot/string | mixed | title | text/alt yes | none | insights/RSS |
| Team | profile/governance/media | mixed | mixed | first name/slug/sort | position/bio/alt | none | about/team |
| Testimonial | author/content/rating | mixed | string/JSON/int | author/content/rating | content | none | home |
| FAQ | question/answer/category/order | mixed | JSON/string/int | question/answer | yes | none | home/pricing |
| Engagement | commercial copy/modes | mixed | JSON/string/bool | title/slug | copy yes | none | pricing |
| Price | band/currency/approval | mixed | morph/string/int/date | model/currency | copy yes | none | gated pricing |
| Estimator | version guardrails/rates | mixed | string/int/JSON | key/label | labels elsewhere | none | estimator |
| Settings | company/contact/social | mixed | JSON/string | none | selected text | none | footer/contact/legal |
| Lead | submitted fields/triage | contact_leads | mixed | API-dependent | no | none | CMS operations only |
| Trust | sections/approvals/visibility | trust_pages | JSON/FK/bool | type/title/slug | sections etc. yes | none | trust pages |
| Claim | proof/governance | public_claims | morph/string/bool | category/claim | locale row | none | approved embeds |
| Redirect | source/target/status | redirects | strings/int/bool | source/target | no | none | Next redirects |
| AI | provenance/review output | ai_generations | mixed | system generated | locale field | none | no public UI |

## Cross-check Findings

1. The current CMS is bilingual for explicitly `HasTranslations` attributes only; not every JSON column is necessarily localized.
2. The Admin form may show generic publishing fields that are added by the editorial migration; publication is controlled by status, not a bare public toggle for those models.
3. Legacy models/tables (`Services`, `Projects`, `Team`, `FAQ`, etc.) remain in the repository. They are not the current Filament/public v1 CMS architecture and should not be confused with `Service`, `CaseStudy`, `TeamMember`, `FaqItem`.
4. The model/schema mismatches marked above require a migration-history/runtime-schema review before relying on those fields in a new environment.
