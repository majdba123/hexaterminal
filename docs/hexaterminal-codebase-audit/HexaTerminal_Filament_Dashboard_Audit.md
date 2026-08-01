# HexaTerminal Filament Dashboard Audit

## 1. Panel Architecture
| Item | Verified state |
|---|---|
| Panel provider | `app/Providers/Filament/CmsPanelProvider.php` |
| Panel id | `cms` |
| Path | `/cms` |
| Login | enabled via Filament auth |
| MFA | app-authentication TOTP required for `admin` role only |
| Translation plugin | `LaraZeus\SpatieTranslatable` with `en`, `ar` |
| Navigation groups | Offerings, Proof, Content, Pricing, Leads, SEO, Trust & Governance, Settings |
| Widgets | discovered widgets plus `AccountWidget`, `FilamentInfoWidget` |

## 2. Authentication
- Middleware stack is explicitly configured in `CmsPanelProvider` and includes cookies, session, CSRF, bindings, and Filament auth middleware.
- MFA behavior is evidence-based, not assumed:
  - `FilamentMfaTest` verifies the provider is present;
  - `CmsPanelProvider` requires MFA when `Auth::user()?->hasRole('admin')` is true.

## 3. Authorization
- `AuthServiceProvider` does not declare a central policy map.
- Effective authorization is distributed across:
  - `User::canAccessPanel(...)`
  - Spatie roles/permissions
  - Filament auth middleware
  - table-action closures such as admin-only actions in `ContactLeadsTable`
  - feature tests `FilamentAuthorizationTest` and `FilamentMfaTest`
- Status: `PARTIALLY_CONFIRMED`, because route/resource access behavior is covered by tests, but a full policy-class inventory was not present to map.

## 4. Resource Inventory
| Resource | Model / table | Group | Pages | Form focus | Table focus | Tests / notes |
|---|---|---|---|---|---|---|
| `ServiceResource` | `Service` / `service_offerings` | Offerings | list, create, edit | name, tagline, summary, description, icon, features, tech stack, publishing/SEO/media | cover image, name, slug, publish state, sort order | `ServiceResourceTest`, `AllResourcesRenderTest` |
| `SystemResource` | `System` / `systems` | Offerings | list, create, edit | type, category, descriptions, problem/solution, outcomes, demo/live URLs, industries, featured | cover image, type, featured, publish state, sort order | render coverage verified |
| `IndustryResource` | `Industry` / `industries` | Offerings | list, create, edit | name, summary, description, icon | cover image, name, slug, publish state, sort order | render coverage verified |
| `CaseStudyResource` | `CaseStudy` / `case_studies` | Proof | list, create, edit | story fields, evidence/outcomes, URLs, service/system relations, industries, featured | client name, service/system columns, featured, publish state | render coverage verified |
| `TestimonialResource` | `Testimonial` / `testimonials` | Proof | list, create, edit | author/company/content/rating, approval, featured | author, company, content, rating, approval, featured | render coverage verified |
| `ArticleResource` | `Article` / `articles` | Content | list, create, edit | title, excerpt, body, author, category, tags, featured, update markers | cover image, title, author, publish state, published at | render coverage verified |
| `ArticleCategoryResource` | `ArticleCategory` / `article_categories` | Content | list, create, edit | name, description, sort order | name, slug, article count, sort order | render coverage verified |
| `ArticleTagResource` | `ArticleTag` / `article_tags` | Content | list, create, edit | name | name, slug, article count | render coverage verified |
| `FaqItemResource` | `FaqItem` / `faqs` | Content | list, create, edit | question, answer, category, publish, sort | question, category, publish state, sort | render coverage verified |
| `TeamMemberResource` | `TeamMember` / `team_members` | Content | list, create, edit | identity, bio, expertise, links, publish flag, `publication_consent`, founder/SEO flags, review dates | photo, names, specialization, email, publish/consent/founder flags | render coverage verified |
| `EngagementModelResource` | `EngagementModel` / `engagement_models` | Pricing | list, create, edit | title, summary, buyer fit, scope, duration, display mode, billing, CTA, featured/published | display mode, billing model, featured/published, sort | render coverage verified |
| `PricingProfileResource` | `PricingProfile` / `pricing_profiles` | Pricing | list, create, edit | polymorphic price target, currency, min/max, unit, assumptions, disclaimer, approvals, dates | target title, currency, amounts, approval, approver | render coverage verified |
| `EstimatorVersionResource` | `EstimatorVersion` / `estimator_versions` | Pricing | list, create, edit | key, label, status, counts, currency, floor/ceiling, rates, notes | key, status, active flag, question/estimate counts | render coverage verified |
| `ContactLeadResource` | `ContactLead` / `contact_leads` | Leads | list, create, edit | captured lead/contact/utm/scoring/status fields, assignee, follow-up, notes | intent, score, status, priority, assignee, follow-up, created at | render coverage verified; admin-only table actions present |
| `CostEstimateResource` | `CostEstimate` / `cost_estimates` | Leads | list, edit | computed estimate payload, recommended model, UTM, lead link, status | uuid, currency, amount range, complexity, confidence, linked lead | render coverage verified |
| `RedirectResource` | `Redirect` / `redirects` | SEO | list, create, edit | from/to paths, status code, active flag | hit count, last hit, active state | render coverage verified |
| `AiGenerationResource` | `AiGeneration` / `ai_generations` | SEO | list, edit | provider/model/field/locale/token-cost/review metadata | status, locale, provider, cost, latency | create page intentionally absent; covered by `AllResourcesRenderTest` |
| `TrustPageResource` | `TrustPage` / `trust_pages` | Trust & Governance | list, create, edit | type, title, summary, sections, FAQs, CTA, owner/reviewer, approval flags, review dates, nav/footer/indexing toggles | type, title, slug, publish state, founder/legal/security approvals | render coverage verified |
| `PublicClaimResource` | `PublicClaim` / `public_claims` | Trust & Governance | list, create, edit | claim text, evidence, claimable relation, verification status, confidentiality, approval, review owner, dates | category, claim, claimable type, verification, confidentiality, approval | render coverage verified |

## 5. Resource-Specific Findings
- `AiGenerationResource`: no create page, matching the test assertion that create is intentionally absent.
- `ContactLeadResource`: contains privileged actions that explicitly authorize only admins.
- `TeamMemberResource`: strongest example of governance-aware publication because both `is_published` and `publication_consent` matter.
- `TrustPageResource` and `PublicClaimResource`: the governance-heavy content model exists, but public readiness still depends on actual approved records.

## 6. Pages
- Singleton page:
  - `CompanySettingsPage`
  - path slug explicitly normalized to `company-settings`
  - stores public company facts plus non-secret operational settings such as lead recipients and analytics identifiers
- Default dashboard:
  - Filament `Dashboard`
- Resource pages:
  - every resource has `List*`;
  - every resource except `AiGeneration` has `Create*`;
  - every resource with mutable records has `Edit*`;
  - dedicated `View*` pages were not discovered in the current resource set.

## 7. Widgets
| Widget | Purpose | Static risk |
|---|---|---|
| `ContentQualityWidget` | counts drafts, schedule, missing Arabic/English, failed AI generations | live aggregate counts on dashboard load |
| `MarketingOverviewWidget` | active leads, new, qualified, overdue, spam rate, top source page | live aggregate counts and grouped queries |
| `AccountWidget` | Filament built-in account surface | low |
| `FilamentInfoWidget` | Filament built-in info widget | low |

## 8. Forms
- Shared patterns verified across resources:
  - publishing / workflow sections;
  - translatable content inputs;
  - SEO/media support;
  - slug support from shared Filament helpers;
  - approval/governance toggles on trust/team/pricing-adjacent resources.
- Strongest operational forms:
  - `ContactLeadForm` for triage workflow;
  - `TrustPageForm` for approvals and review scheduling;
  - `PricingProfileForm` for approval/effective-date control;
  - `EstimatorVersionForm` for estimator activation data.

## 9. Tables
- Exhaustive table classes were inspected in the repository; the earlier “not exhaustive” caveat is no longer valid.
- Verified notable columns/filters:
  - Services: `cover_image`, `name`, `slug`, `is_published`, `published_at`, `sort_order`; filter `is_published`
  - Systems: `cover_image`, `name`, `type`, `is_featured`, `is_published`; filters `type`, `is_published`, `is_featured`
  - Case studies: relation columns to service/system, featured/published filters
  - Contact leads: filters `status`, `intent`, `priority`, `assigned_to`, `overdue_follow_up`
  - Pricing profiles: approval and currency filters
  - Trust pages: `page_type` and `is_published` filters
  - Public claims: category, verification, approval, confidentiality filters

## 10. Content Management
- Current CMS supports structured management for offerings, proof, content, pricing, trust, claims, redirects, AI generations, and inbound leads.
- Editorial workflow appears mature in schema terms:
  - publish state;
  - review dates;
  - approval fields;
  - translatable fields;
  - SEO support;
  - preview infrastructure in the public API.
- Remaining risk is not “missing CMS infrastructure”; it is actual data completeness and governance discipline.

## 11. Localization
- CMS locale support is configured globally at the panel level for `en` and `ar`.
- Locale-quality gaps remain likely editorial/data problems:
  - English content accidentally containing Arabic text is not reproduced as a framework fallback defect;
  - widgets and reports explicitly track missing Arabic and missing English fields.

## 12. SEO Management
- SEO-related resources/surfaces:
  - `RedirectResource`
  - `AiGenerationResource`
  - polymorphic SEO fields embedded in content resources
  - `PublicClaimResource` for marketing-proof governance
  - `TrustPageResource` for trust content and indexing decisions
- Important distinction:
  - SEO architecture is implemented;
  - SEO completeness of live pages is still data-dependent.

## 13. Media Management
- Resource forms use shared upload conventions.
- Verified media-bearing resources include services, systems, case studies, articles, team members, company settings, and trust pages.
- Runtime media storage behavior was not exercised against a live disk, so storage correctness is `PARTIALLY_CONFIRMED`.

## 14. Lead Management
- `ContactLeadResource` is a real lead-inbox workflow, not a passive database dump.
- Triaging signals surfaced in CMS:
  - intent
  - score
  - priority
  - assignee
  - follow-up time
  - qualification summary
  - UTM summary
- Internal routing configuration lives in `CompanySettingsPage` via `lead_recipients`.

## 15. Company Settings Page
| Section | Verified fields |
|---|---|
| Company | company name, tagline, description, email, phone, whatsapp, address, booking URL |
| Social & Branding | social links, default OG image, footer note |
| Operational | lead recipients, analytics provider, analytics site id |
- Secrets are intentionally excluded from this page.

## 16. Publishing and Revalidation
- CMS save/update/delete behavior is tied to public-site freshness through model observers and revalidation service.
- Public preview support is implemented through `PreviewController` and preview-token services.
- Relevant tests:
  - `RevalidationTest`
  - `PreviewControllerTest`
  - `PublishedContentVisibilityTest`

## 17. Test Coverage
| Area | Evidence |
|---|---|
| Panel access | `FilamentAuthorizationTest` |
| Admin MFA | `FilamentMfaTest` |
| Broad resource rendering | `Cms/AllResourcesRenderTest` |
| Service CRUD path | `Cms/ServiceResourceTest` |
| Publication / visibility | `PublishedContentVisibilityTest`, `TrustPageVisibilityTest` |
| Revalidation | `RevalidationTest` |

## 18. Known Deficiencies
- No centrally mapped policy classes were verified.
- No dedicated content-completeness blocker prevents every incomplete record from being published.
- AI generation has no create page by design, so provenance depends on upstream generation workflows.
- Broader runtime verification is still constrained by absent `.env` and absent frontend dependencies.

## 19. Recommended Improvement
- Phase 0 should validate CMS data quality before any implementation work:
  - bilingual completeness;
  - placeholder URLs;
  - trust/team approvals;
  - claim evidence completeness.
- Only after that should UI or model changes be scheduled.

## 20. Definition of Done
- Every current resource remains mapped to model/table/pages/schema/table/tests.
- No resource is described as “unreviewed” or “not exhaustively reviewed.”
- The CMS audit accurately reflects that architecture is largely present, but content and environment verification remain blockers.
- Status for this document: `REVISION_REQUIRED`.
