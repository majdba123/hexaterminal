# Content Import Templates

Reference field lists for handing real content to whoever enters it into
Filament. There is currently **no bulk CSV/JSON importer** — the content
volume at launch (a handful of services, systems, case studies, team members)
does not justify building one; entering directly in `/cms` is faster and lets
each translatable field get its EN/AR tabs filled in correctly. These tables
are the handoff format founders can fill in (a spreadsheet, a doc, whatever is
easiest) before someone transcribes it into the CMS.

If a future import volume genuinely justifies a CSV importer, build it against
the `required` columns below (Filament import actions can be added later
without a data-model change — the `slug` column staying unique is the only
constraint that matters).

Every row below needs an **English** and an **Arabic** version unless marked
`(EN only)`. **Do not overwrite existing published content** — if a row
already exists (matched by slug), treat it as an edit and confirm with the
founder before replacing real, already-approved copy.

## Service

| Field | Required | Notes |
|---|---|---|
| slug | ✅ | URL segment, e.g. `crm-implementation` |
| name | ✅ | |
| tagline | | One line |
| summary | | 1–2 sentences, used in list views |
| description | | Full body |
| icon | | Icon identifier, not raw HTML/SVG |
| features | | One capability per line |
| tech_stack | | Comma-separated tags |
| cover_image | | See founder-content-guide.md §Image requirements |
| seo_title / seo_description | | Both locales |

## System

| Field | Required | Notes |
|---|---|---|
| slug | ✅ | |
| type | ✅ | One of: `saas_product`, `business_system`, `client_system`, `internal_product`, `platform`, `ai_system` |
| name | ✅ | |
| tagline / short_description / full_description | | |
| problem / solution | | The buyer-facing narrative |
| features / business_outcomes | | One per line |
| target_audience | | Who this is for |
| tech_stack | | Comma-separated |
| demo_url / live_url | | Only if publicly shareable |
| industries | | Which Industry records this relates to (must already exist) |
| **confidential?** | | If yes: set status to `private` or `client_project` instead of `published` — never publish client-confidential systems |

## Case Study

| Field | Required | Notes |
|---|---|---|
| slug | ✅ | |
| title | ✅ | |
| client_name | | Or leave blank + describe the sector for anonymized case studies |
| context / problem / constraints | | |
| solution / architecture | | |
| outcomes | ✅ (with evidence) | Each metric needs an evidence label: `verified`, `approximate`, or `confidential` — only `verified` renders publicly |
| evidence | ✅ | Matches outcomes 1:1 |
| related service / system | | Must already exist in the CMS |
| industries | | |
| gallery | | Real screenshots only |
| **Never fabricate** | | Client names, revenue figures, percentages, timelines, or awards that aren't confirmed by the client |

## Industry

| Field | Required | Notes |
|---|---|---|
| slug | ✅ | |
| name | ✅ | |
| summary / description | | Common operational problems + how Hexa Terminal solves them |
| **Only publish** | | Sectors with real delivered work — a thin, generic industry page (no supporting case study) should stay `draft`/noindex |

## Article (Insights)

| Field | Required | Notes |
|---|---|---|
| slug | ✅ | |
| title | ✅ | |
| excerpt | ✅ | Shown in list views and social previews |
| body | ✅ | Full article |
| category | | Must already exist (`/cms/article-categories`) |
| tags | | Only add a tag once several articles will use it |
| author | | Must be an existing CMS user |
| cover_image / og_image | | |
| is_featured | | Surfaces on the homepage Insights section |

## Team Member

| Field | Required | Notes |
|---|---|---|
| full_name | ✅ | |
| position | ✅ | |
| bio | | |
| photo | ✅ | Square, ≥400×400 |
| linkedin_url / github_url | | Optional |

## Testimonial

| Field | Required | Notes |
|---|---|---|
| author_name / author_title / company | ✅ | Real, with permission |
| content | ✅ | Real quote — never paraphrase into something the client didn't say |
| rating | | Only if the client actually gave a rating |
| is_featured | | Surfaces on the homepage |

## Company Settings (single record, `(EN only)` for operational fields)

| Field | Required | Notes |
|---|---|---|
| company_name / tagline / description | ✅ | |
| email / phone / whatsapp | ✅ | At least one contact channel |
| social_links | | `{platform: url}` pairs |
| booking_url | | If you use a scheduling tool |
| lead_recipients | (EN only) | Comma-separated internal emails — never exposed publicly |
| default_og_image | | Fallback social image |
| analytics_provider / analytics_site_id | (EN only) | Only if you've chosen an analytics provider — see `docs/architecture` for the boundary; leave blank to run with no analytics |
