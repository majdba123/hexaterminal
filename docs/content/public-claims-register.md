# Public Claims Register

Every notable claim or metric that could appear on the public site, with its verification status. Nothing in the "not publishable" rows may be shown publicly until it moves to "verified" with founder sign-off. This register should be updated every time a founder approves new case-study or system content.

| Page / record | Claim | Source | Verified status | Publication approval | Owner | Notes |
|---|---|---|---|---|---|---|
| Homepage hero | "We build software systems that run real businesses." | Sprint-supplied positioning direction | Approved direction (not a metric) | Pending founder final sign-off | Founder | Positioning statement, not a factual claim requiring evidence |
| Homepage hero stats | Team members: 2 | `team_members` table (Majd Bayer, Mohamad Kahal) | Verified | Published (real, non-zero) | — | Services/Systems/Case Studies stats correctly hidden when 0, per no-fabrication rule |
| `saas-platforms` service | "Multi-tenant data isolation, role-based access control..." (capability description) | Drafted from sprint's service-pillar description | Descriptive, not a metric | Pending founder approval (status: in_review) | Founder | No specific client outcome or number claimed |
| `crm-erp-systems` … `custom-operational-software` (5 more pillars) | Same pattern — descriptive capability copy, no numbers | Drafted from sprint's service-pillar description | Descriptive, not a metric | Pending founder approval (status: in_review) | Founder | — |
| 8 legacy-migrated case studies (Smart Store, ProTask, SpeedEats, MedClinic, EduPro, HRFlow, DigiWallet, StayBook) | Implied real client outcomes | `ProjectsSeeder.php` fictional demo data | **Not publishable** — fictional, no client name, no outcomes field populated | **Unpublished** (status: draft) | — | Must not be re-published without real replacement content |
| `hexa-crm` / `fleet-ops` systems (if present) | Implied real, live products | Manually entered in Filament, not seeded in code | Real but unverified claims/metrics within them | Founder review required per-field | Founder | No cover image, no SEO row; verify any capability claims before publishing |
| LeadScope AI, Dhura, HireLens AI, LinguaCoach AI, CareerGuide AI, Business Flow, Mytrixa, Rakez ERP, iLogistics, Avenue Food | N/A — no claims exist | Sprint brief only; zero repository evidence | **Not publishable** — no record exists | Not started | Founder | Nothing to verify until founder supplies source material |
| 5 migrated testimonials | Named client quotes, 4–5 star ratings | `ReviewSeeder.php` legacy data | **Not verified** — no publication-permission record in repo | **Unapproved** (`is_approved=false`) | Founder | Re-approve only after confirming real consent per testimonial |
| Industries (`fintech`, `logistics`, if present) | Implied real sector experience | Manually entered in Filament | Real but no linked case study evidence | Founder review required | Founder | Consider leaving draft/noindex until backed by a real case study |
| FAQ answers (10 drafted) | Descriptions of how the company operates (discovery call, tech stack, support) | Drafted from sprint brief's FAQ questions, grounded in real platform behavior (editorial workflow, lead intake) | Descriptive, non-numeric, no pricing/timeline commitment | Pending founder approval (unpublished) | Founder | Deliberately avoids overcommitting on scope/pricing per sprint instruction |

## Process
1. Any new claim or metric added to a Service, System, Case Study, or Industry record must get a row here before that record is published.
2. "Verified" requires a founder-confirmed source (a real client conversation, a real internal metric, a signed testimonial release, etc.) — not an inference or an estimate.
3. "Approximate" and "confidential" classifications are allowed on the case-study page itself (per the sprint brief) but must still be labeled as such in the UI copy, not stated as bare fact.
4. `php artisan hexa:content-report` flags unpublished/incomplete records mechanically, but does **not** verify claims — this register is the manual, human-owned control for that.
