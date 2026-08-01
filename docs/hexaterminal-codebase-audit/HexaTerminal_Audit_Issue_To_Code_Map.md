# HexaTerminal Audit Issue To Code Map

Status vocabulary used here:
- `CONFIRMED_CODE_DEFECT`
- `PARTIALLY_CONFIRMED`
- `CODE_PATH_VALID_DATA_UNVERIFIED`
- `NOT_REPRODUCED_IN_CODE`
- `REQUIRES_PRODUCTION_DATA_VERIFICATION`
- `NOT VERIFIED`

## Finding 1. Empty Systems page
- Original externally observed behavior: systems listing appeared empty.
- Repository verification status: `CODE_PATH_VALID_DATA_UNVERIFIED`
- CMS/data verification status: `NOT VERIFIED`
- Production verification status: `REQUIRES_PRODUCTION_DATA_VERIFICATION`
- Root cause, if proven: none proven in code; page renders data returned by `SystemController@index`.
- Alternative possible causes: no published systems, wrong publication flags, locale-specific missing records.
- Backend files/symbols: `SystemController@index`, `System::published`
- Frontend files/symbols: `systems/(list)/page.tsx`
- Filament files/symbols: `SystemResource`, `SystemForm`, `SystemsTable`
- Database: `systems`
- Tests: API V1 suite broad coverage
- Missing tests: seeded non-empty list assertions in both locales
- Dependencies / phase / risk / DoD: content readiness; Phase 0 data audit; low risk; page must render intended published records

## Finding 2. Empty Industries page
- Original externally observed behavior: industries listing appeared empty.
- Repository verification status: `CODE_PATH_VALID_DATA_UNVERIFIED`
- CMS/data verification status: `NOT VERIFIED`
- Production verification status: `REQUIRES_PRODUCTION_DATA_VERIFICATION`
- Root cause: no code defect proven; listing is data driven.
- Alternative causes: unpublished records, missing locale content.
- Backend: `IndustryController@index`, `Industry::published`
- Frontend: `industries/(list)/page.tsx`
- Filament: `IndustryResource`
- Database: `industries`
- Tests: API V1 broad coverage
- Missing tests: locale-specific fixture coverage
- Dependencies / phase / risk / DoD: CMS content audit; Phase 0; low; published industries available or route intentionally hidden

## Finding 3. Empty Insights page
- Original externally observed behavior: insights/blog listing appeared empty.
- Repository verification status: `CODE_PATH_VALID_DATA_UNVERIFIED`
- CMS/data verification status: `NOT VERIFIED`
- Production verification status: `REQUIRES_PRODUCTION_DATA_VERIFICATION`
- Root cause: none proven in code; article listing depends on published article records.
- Alternative causes: no published articles, tag/category filters excluding content.
- Backend: `ArticleController@index`
- Frontend: `insights/(list)/page.tsx`
- Filament: `ArticleResource`, `ArticleCategoryResource`, `ArticleTagResource`
- Database: `articles`, `article_categories`, `article_tags`
- Tests: `ArticleCategoryTest`, `LocaleAndPaginationTest`
- Missing tests: seeded public article rendering in both locales
- Dependencies / phase / risk / DoD: content seeding or editorial publishing; Phase 0; low; at least one intended article renders

## Finding 4. Empty Team section
- Original externally observed behavior: team area looked empty or missing.
- Repository verification status: `PARTIALLY_CONFIRMED`
- CMS/data verification status: `NOT VERIFIED`
- Production verification status: `REQUIRES_PRODUCTION_DATA_VERIFICATION`
- Root cause: publication requires both `is_published` and `publication_consent`; route registry marks `/team` technically-ready rather than fully current.
- Alternative causes: no founder-approved bios, route intentionally withheld from navigation.
- Backend: `TeamMember::published`, `TeamMemberController@index`
- Frontend: `about/page.tsx`, `team/page.tsx`, `routes/registry.ts`
- Filament: `TeamMemberResource`
- Database: `team_members`
- Tests: `PublishedContentVisibilityTest`
- Missing tests: full `/team` page seeded fixture
- Dependencies / phase / risk / DoD: content/governance approvals; Phase 0; medium; approved public team records appear intentionally

## Finding 5. Arabic content appearing under `/en`
- Original externally observed behavior: English route showed Arabic content.
- Repository verification status: `NOT_REPRODUCED_IN_CODE`
- CMS/data verification status: `NOT VERIFIED`
- Production verification status: `REQUIRES_PRODUCTION_DATA_VERIFICATION`
- Root cause: not proven in code; fallback locale is `en`, not `ar`.
- Alternative causes: bilingual data entry issue, import/migration content contamination, missing English values entered as Arabic text.
- Backend: `config/app.php`, `SetApiLocale`
- Frontend: `layout.tsx`, `api/client.ts`
- Filament: translatable resource workflow
- Database: translatable JSON columns across content tables
- Tests: content model/i18n tests broadly relevant
- Missing tests: seeded bilingual assertions for sampled public pages
- Dependencies / phase / risk / DoD: CMS data audit; Phase 0; medium; English pages render English content only

## Finding 6. Transliteration-based English slugs
- Original externally observed behavior: English URLs used transliterated Latin slugs.
- Repository verification status: `PARTIALLY_CONFIRMED`
- CMS/data verification status: `NOT VERIFIED`
- Production verification status: `REQUIRES_PRODUCTION_DATA_VERIFICATION`
- Root cause: centralized slug generation policy appears to keep stable Latin URLs.
- Alternative causes: manual slug entry on some records.
- Backend: `HasAutoSlug`
- Frontend: all slug-driven detail pages
- Filament: shared slug support
- Database: slug columns on public content tables
- Tests: slug/security coverage
- Missing tests: explicit slug-policy acceptance test
- Dependencies / phase / risk / DoD: IA decision; Phase 0; medium; slug policy explicitly accepted or migrated with redirects

## Finding 7. Duplicate case studies in the listing
- Original externally observed behavior: duplicates visible in case-study hub.
- Repository verification status: `NOT_REPRODUCED_IN_CODE`
- CMS/data verification status: `NOT VERIFIED`
- Production verification status: `REQUIRES_PRODUCTION_DATA_VERIFICATION`
- Root cause: list query is a normal paginated published query; no duplicate-producing code path identified.
- Alternative causes: duplicate data rows, duplicate imported slugs, stale frontend content.
- Backend: `CaseStudyController@index`
- Frontend: `case-studies/(list)/page.tsx`
- Filament: `CaseStudyResource`
- Database: `case_studies`
- Tests: API V1 suite broad coverage
- Missing tests: seeded uniqueness assertion
- Dependencies / phase / risk / DoD: data audit; Phase 0; low; duplicates absent in source data and rendered output

## Finding 8. Incorrect related-service mappings
- Original externally observed behavior: a case study pointed to the wrong related service.
- Repository verification status: `PARTIALLY_CONFIRMED`
- CMS/data verification status: `NOT VERIFIED`
- Production verification status: `REQUIRES_PRODUCTION_DATA_VERIFICATION`
- Root cause: relationship is data-driven via foreign keys, so wrong mapping would come from record assignment, not computed business logic.
- Alternative causes: stale content cache or bad migrated relation ids.
- Backend: `CaseStudy` relations
- Frontend: `case-studies/[slug]/page.tsx`
- Filament: `CaseStudyForm`
- Database: `case_studies.service_offering_id`
- Tests: relation-aware coverage is limited
- Missing tests: seeded relation assertions
- Dependencies / phase / risk / DoD: CMS correction; Phase 0; low; each case study points to intended service

## Finding 9. `example.com` live-project URLs
- Original externally observed behavior: placeholder URLs leaked publicly.
- Repository verification status: `PARTIALLY_CONFIRMED`
- CMS/data verification status: `NOT VERIFIED`
- Production verification status: `REQUIRES_PRODUCTION_DATA_VERIFICATION`
- Root cause: placeholder URLs exist in seed/legacy data and detail pages will render URLs when present.
- Alternative causes: imported demo content or unpublished legacy content copied into modern models.
- Backend: `ProjectsSeeder`, case-study model/rendering paths
- Frontend: `case-studies/[slug]/page.tsx`
- Filament: `CaseStudyResource`
- Database: `projects`, `case_studies.project_url`
- Tests: none targeted
- Missing tests: published-content placeholder-domain guard
- Dependencies / phase / risk / DoD: content cleanup; Phase 0; medium; no placeholder domains in public records

## Finding 10. Missing or unclear project classification
- Original externally observed behavior: project categorization felt unclear.
- Repository verification status: `PARTIALLY_CONFIRMED`
- CMS/data verification status: `NOT VERIFIED`
- Production verification status: `REQUIRES_PRODUCTION_DATA_VERIFICATION`
- Root cause: taxonomy is split between Services and Systems with legacy overlap.
- Alternative causes: copy/design not explaining categories clearly.
- Backend: `Service`, `System`, `CaseStudy`
- Frontend: systems and services hubs, route registry
- Filament: `ServiceResource`, `SystemResource`, `CaseStudyResource`
- Database: `service_offerings`, `systems`, `case_studies`
- Tests: none targeted
- Missing tests: taxonomy/filter expectations
- Dependencies / phase / risk / DoD: IA clarification; Phase 0; medium; consistent business classification across hubs/detail pages

## Finding 11. Thin service-page content
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: current model/template supports a leaner content shape than a richer proof-led service page.
- Backend: `Service`
- Frontend: `services/[slug]/page.tsx`
- Filament: `ServiceForm`
- Database: `service_offerings`
- Missing tests: detail rendering for future richer sections
- Phase / risk / DoD: later model enhancement; Phase 1+ after baseline; medium; intended service-page sections exist or thinness is accepted

## Finding 12. Missing dedicated ERP page
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: no verified dedicated ERP route; content appears to rely on generic service taxonomy.
- Backend/frontend/Filament/database: service detail stack
- Missing tests: dedicated ERP slug coverage if adopted
- Phase / risk / DoD: IA/content decision; Phase 0; medium; dedicated ERP surface exists or is intentionally not required

## Finding 13. Missing dedicated CRM page
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: same pattern as ERP.
- Stack: service detail stack
- Phase / risk / DoD: IA/content decision; Phase 0; medium; dedicated CRM surface exists or is intentionally not required

## Finding 14. Overly broad service taxonomy
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: broad offering buckets are intentional in current model design.
- Stack: `Service`, `System`, related hubs
- Phase / risk / DoD: taxonomy strategy; Phase 0; medium; taxonomy aligns with positioning and route architecture

## Finding 15. Pricing estimator text without an estimator
- Repository verification status: `NOT_REPRODUCED_IN_CODE`
- Root cause: pricing page checks `estimator_available` before showing estimator CTA.
- Backend: `PricingController`, `EstimatorController`
- Frontend: `pricing/page.tsx`
- Filament: `EstimatorVersionResource`
- Tests: `PricingApiTest`, `EstimatorApiTest`
- DoD: CTA matches backend estimator availability

## Finding 16. Contact and Start-a-Project overlap
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: both pages share `LeadForm` and `LeadController@store`.
- Frontend: `contact/page.tsx`, `start-a-project/page.tsx`, `lead-form.tsx`
- Backend: `LeadController@store`
- Filament: `ContactLeadResource`
- Tests: `LeadIntentAndAttributionTest` partly relevant
- DoD: flows are intentionally distinct or intentionally merged

## Finding 17. Generic `Send` form CTA
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: shared translation copy for the shared lead form.
- Frontend: `lead-form.tsx`, locale message files
- Missing tests: copy snapshot assertions
- DoD: CTA copy communicates action clearly

## Finding 18. Missing next-step and response-SLA messaging
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: limited success/follow-up messaging in shared lead flow.
- Frontend: `contact/page.tsx`, `start-a-project/page.tsx`, `lead-form.tsx`
- DoD: both flows state timeline and expected next step explicitly

## Finding 19. Missing or incomplete success state
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: success state is simple and does not expose richer follow-up info.
- Stack: `LeadController`, `lead-form.tsx`, `ContactLeadResource`
- DoD: success state includes acknowledgement and next-step detail

## Finding 20. Missing form confirmation email
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: internal team notification exists; no user-facing confirmation mailer was verified.
- Backend: `LeadController`, `NewLeadNotification`
- Missing tests: user confirmation email tests
- DoD: explicit decision plus implementation if user mail is required

## Finding 21. Missing lead reference ID
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: backend returns an id, but frontend success state does not clearly expose a user-safe reference.
- Stack: `LeadController@store`, `lead-form.tsx`
- DoD: safe user reference is displayed or intentionally suppressed

## Finding 22. Missing spam protection or rate limiting
- Repository verification status: `NOT_REPRODUCED_IN_CODE`
- Root cause: protections exist: throttle, honeypot, dedupe, optional Turnstile.
- Tests: rate limiting and replay/security tests
- DoD: protections remain active in deployed environment

## Finding 23. Missing analytics funnel events
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: some events exist, full funnel coverage not fully verified.
- Frontend: `analytics-script.tsx`, `lead-form.tsx`, `showreel.tsx`, `view-tracker.tsx`
- Filament: `CompanySettingsPage`
- DoD: agreed funnel events exist for key acquisition steps

## Finding 24. Weak or missing SEO metadata
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: helper architecture exists, but record-level completeness depends on content.
- Stack: `SeoMeta`, page metadata helpers, page `generateMetadata`
- Tests: SEO audit related feature tests
- DoD: all indexable pages have complete metadata or intentional defaults

## Finding 25. Missing or incorrect hreflang
- Repository verification status: `NOT_REPRODUCED_IN_CODE`
- Root cause: alternates helper and sitemap architecture exist.
- Frontend: `alternates.ts`, `page-metadata.ts`, `sitemap.ts`
- DoD: alternates remain correct for static and dynamic public routes

## Finding 26. Weak language and direction handling
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: framework wiring exists, but final quality depends on data and page-level QA.
- Stack: `layout.tsx`, `routing.ts`, `SetApiLocale`
- DoD: all sampled English and Arabic routes show correct direction and language content

## Finding 27. Missing structured data
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: structured-data helpers exist, but not every route was fully runtime-verified.
- Stack: `jsonld.ts`, `JsonLd` usage in pages
- DoD: each strategic public route emits intended schema nodes

## Finding 28. Video or showreel performance risk
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: code mitigates risk with poster-first modal loading, but transfer/runtime not measured.
- Frontend: `showreel.tsx`
- DoD: acceptable production performance under measurement

## Finding 29. Client-side loading or hydration risks
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: server components reduce risk, but build/runtime verification is blocked because frontend deps are missing.
- Stack: App Router pages and `api/client.ts`
- DoD: clean build plus no major hydration issues in dependency-installed environment

## Finding 30. Accessibility risks in navigation and forms
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: accessibility patterns and tests exist, but full route-by-route runtime verification was not done.
- Stack: layout, nav components, form components, Playwright accessibility suite
- DoD: agreed accessibility baseline passes in rendered build

## Finding 31. Missing security headers
- Repository verification status: `NOT_REPRODUCED_IN_CODE`
- Root cause: baseline security-header surfaces exist on Laravel and Next.js.
- Backend: `SecurityHeaders`
- Frontend: next config / proxy / CSP report route
- Tests: `SecurityHeadersTest`
- DoD: headers verified in deployed environment too

## Finding 32. Missing company/team trust information
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: route and model infrastructure exist, but public readiness depends on approved content.
- Stack: `TrustPage`, `TeamMember`, route registry, company settings
- DoD: approved trust/team evidence is actually published

## Finding 33. Unsupported or unverified statistics and claims
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: some public proof statements live in translations rather than being fully governed through `PublicClaim`.
- Stack: `PublicClaim`, message files, homepage components
- DoD: public claims become sourced, reviewed, and traceable

## Finding 34. Missing support and maintenance content
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: support content is implied in pricing/trust architecture but not verified as a current approved public page.
- Stack: pricing page, `TrustPage`, route registry
- DoD: support/maintenance information is published intentionally

## Finding 35. Missing real case-study outcomes
- Repository verification status: `PARTIALLY_CONFIRMED`
- Root cause: model supports outcomes/evidence, but content completeness depends on records.
- Stack: `CaseStudy`, `CaseStudyForm`, `case-studies/[slug]/page.tsx`, `ContentCompletenessReport`
- Tests: completeness report tests are relevant
- DoD: published case studies contain substantive outcomes/evidence or are withheld
