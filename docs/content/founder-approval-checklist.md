# Founder Approval Checklist

Everything below is currently in `draft`/`in_review` status or otherwise unpublished — nothing on this list is visible on the public site yet. Each item needs an explicit founder decision (approve as-is, edit, or reject) before it can be published through Filament. See `docs/content/current-content-inventory.md` for the full factual audit this checklist is based on.

## Company Settings
- [ ] Confirm company legal name, tagline, and description (drafted from the sprint's approved positioning direction — see below).
- [ ] Provide real phone number, if any is to be public.
- [ ] Provide real WhatsApp number, if offered as a contact channel.
- [ ] Provide a real booking URL (Calendly/Cal.com/etc.), if used.
- [ ] Provide real social links (LinkedIn, X/Twitter, GitHub org, etc.).
- [ ] Confirm `hello@hexaterminal.com` is the correct public contact address, or supply the real one.
- [ ] Wire WhatsApp/booking-URL CTAs into the frontend once real values exist (currently no UI consumes these fields).

## Positioning
- [ ] Approve or edit the hero direction: "We build software systems that run real businesses."
- [ ] Approve or edit the supporting line about SaaS, CRM/ERP, AI-enabled workflows, and backend infrastructure.

## Services (6 drafted pillars — status: `in_review`)
For each of `saas-platforms`, `crm-erp-systems`, `ai-enabled-workflows`, `backend-api-engineering`, `business-automation`, `custom-operational-software`:
- [ ] Confirm this is a real, currently-offered service.
- [ ] Review/edit the buyer-problem, solution-approach, and deliverables copy (EN and AR).
- [ ] Supply a real cover image (none exist yet — no stock/placeholder image was substituted).
- [ ] Approve before publishing (status must move draft → in_review → approved → published in Filament).

The 12 legacy-migrated services (garbled slugs, mis-tagged locale) were unpublished, not deleted — decide per-record whether to fix and reuse, or discard.

## Systems
- [ ] Confirm whether `hexa-crm` and `fleet-ops` (if present in your environment) are real Hexa Terminal products, client work, or founder portfolio pieces, and label accordingly (see "safe labels" in the sprint brief).
- [ ] The 10 candidate system names in the original brief (LeadScope AI, Dhura, HireLens AI, LinguaCoach AI, CareerGuide AI, Business Flow, Mytrixa, Rakez ERP, iLogistics, Avenue Food) do not exist anywhere in this codebase. For each one you want published, supply: real/anonymized name, description, features, tech stack, screenshots, and whether it's a company product, founder portfolio item, or client system.

## Case Studies
- [ ] The 8 legacy-migrated case studies (Smart Store, ProTask, SpeedEats, MedClinic, EduPro, HRFlow, DigiWallet, StayBook) were unpublished — they are fictional demo data with no client name or verified outcomes. Do not publish these as real work.
- [ ] For each real case study you want published: context, problem, solution, architecture, and a classification of every metric as verified / approximate / confidential / not-publishable (see `public-claims-register.md`).
- [ ] Confirm client-visibility permission or an approved anonymization for each.

## Industries
- [ ] Confirm the industries you have real delivery experience in, and whether existing `fintech`/`logistics` records reflect that.

## Team
- [ ] Approve or provide bios for Majd Bayer (CEO & Founder) and Mohamad Kahal (Senior Frontend Engineer) — none were written, since a real professional bio requires facts beyond role title.
- [ ] Provide LinkedIn URLs.
- [ ] Replace Google Drive photo/CV links with CMS-uploaded media.

## Testimonials
- [ ] All 5 migrated testimonials were switched to unapproved (`is_approved=false`) because no publication-permission record exists in the repository. For each one: confirm the source is real, confirm publication permission was actually obtained, and only then re-approve in Filament.

## FAQ (10 drafted entries — status: unpublished)
- [ ] Review the drafted answers (they describe how the platform/company works generically — no pricing or timeline commitments). Publish once approved.

## Legal
- [ ] Privacy Policy and Terms of Use are generic boilerplate, explicitly marked in code as not a substitute for legal review. Route to legal counsel before launch.

## Media
- [ ] Replace flaticon stock icons (services), `placehold.co` images (case studies), and Google Drive team photos with real, rights-cleared assets.

## AI SEO
- [ ] No `ANTHROPIC_API_KEY` is configured in this environment — the AI SEO provider stays safely disabled. If you want live suggestions, supply credentials and re-run the smoke test described in `docs/content/launch-content-status.md`.
