# Founder Content Guide

What the founders need to provide before Hexa Terminal can go live, and how it
maps to the CMS. Read this alongside `content-import-template.md` (bulk entry
templates) and `publishing-workflow.md` (how a piece of content actually goes
live).

## How content flows

Everything below is entered in Filament at `/cms`, in **both English and
Arabic** (each translatable field has an EN and an AR tab). Nothing here is
published automatically — a record only appears on the public site once its
**Status** is set to `Published` (or `Scheduled` with a past date).

## Required before launch

These sections cannot ship empty or with placeholder text — the frontend and
completeness report (`php artisan hexa:content-report`) both flag them.

| Content type | Minimum to launch | CMS location |
|---|---|---|
| **Company Settings** | Name, tagline, email, phone/WhatsApp, at least one social link | `/cms` → Settings → Company Settings |
| **Services** | At least 3 real services with name, tagline, summary, description | `/cms/services` |
| **Systems** | At least 1 real system (or mark none `client_confidential`/private and skip the hub) | `/cms/systems` |
| **Case Studies** | At least 2, each with **real, verifiable** outcomes (see Evidence below) | `/cms/case-studies` |
| **Team** | Real team members with names, roles, photos | `/cms/team-members` |
| **FAQs** | 5–10 real buyer questions | `/cms/faq-items` |
| **SEO metadata** | Title + description (EN + AR) for every page above | Each record's "SEO" tab |

## Optional but recommended

| Content type | Why it helps | Minimum for real value |
|---|---|---|
| **Industries** | Sector-specific landing pages rank better and qualify leads faster | Only publish an industry you have **real** experience in — thin, generic industry pages hurt SEO more than they help. Leave draft/noindex until you have 1+ case study or concrete point of view for that sector. |
| **Insights / Articles** | Content marketing, internal linking, AEO (answer-engine visibility) | Real, specific articles — not generic "10 tips" filler. Assign a Category; add Tags only once you have enough articles per tag to justify an archive page. |
| **Testimonials** | Social proof on the homepage | Only real quotes from real clients, with permission. Never fabricate a name, title, or company. |

## Confidential content — what NOT to publish

- **Client names** you don't have explicit permission to disclose. Use
  `Case Study → client visibility mode` to anonymize (e.g. "a regional
  logistics company") instead of skipping the case study entirely.
- **Systems built for a specific client** under NDA — set the System's status
  to `private` or `client_project`; these are excluded from the public API,
  sitemap, and search automatically (never manually — the exclusion is a
  scope on the query layer).
- **Unverified metrics.** Every outcome/metric on a Case Study has an
  **Evidence** field — mark it `verified`, `approximate`, or `confidential`.
  Only `verified` metrics render publicly. If you don't have a client-approved
  number, don't put a number — describe the outcome qualitatively instead.

## Translation requirements

Every field marked translatable in the CMS (name, tagline, summary,
description, body, FAQs, testimonials, SEO title/description) needs **both**
an English and an Arabic version before publishing. A record missing either
locale is flagged by the completeness report (`missing EN <field>` / `missing
AR <field>`) and should stay in `draft` until both are filled in. Machine
translation is a reasonable first draft but should be reviewed by a fluent
speaker before publishing — search engines and readers both penalize
obviously-translated copy.

## Image / video requirements

| Asset | Format | Notes |
|---|---|---|
| Cover images (Services/Systems/Case Studies/Articles) | JPG/PNG/WebP, ≥1200×630 | Used for both the page hero and the social share (OG) image if no dedicated OG image is set |
| Team photos | Square JPG/PNG, ≥400×400 | Consistent framing across the team looks far more professional than mixed aspect ratios |
| Case study gallery | JPG/PNG/WebP | Real screenshots or product photos — never stock photography for a specific client's system |
| Company logo variants | SVG preferred | Used in the header, footer, and default OG image generation |

Do not upload placeholder/stock images (`placehold.co` etc.) for anything that
will go live — they are fine for internal drafts but must be replaced before
publishing.

## What happens after you publish

1. Save with Status = `Published` (or `Scheduled` + a future date/time).
2. The public API cache is invalidated automatically.
3. If on-demand revalidation is enabled (`docs/deployment/staging-deployment.md`
   §7), the live site updates within seconds. Otherwise it updates within 5
   minutes (ISR) or on the next deploy for a brand-new slug.
4. Run `php artisan hexa:content-report` any time to see what's still missing
   before the next review pass.
