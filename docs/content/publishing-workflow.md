# Editorial Publishing Workflow

How content moves from a first draft to a live page, who can do what, and what
gets recorded along the way. Applies to Services, Systems, Case Studies,
Industries, and Articles (the five content types using
`App\Models\Concerns\HasEditorialWorkflow`).

## Statuses

```
draft → in_review → approved → scheduled ─┐
                                published ←┘
                                    ↓
                                archived
```

| Status | Meaning | Publicly visible? |
|---|---|---|
| `draft` | Being written; not ready for review | No |
| `in_review` | Editor believes it's ready; awaiting approval | No |
| `approved` | A reviewer signed off; not yet live | No |
| `scheduled` | Approved and `published_at` is set to a future date | No, until that date passes |
| `published` | Live | Yes |
| `archived` | Was live, intentionally retired | No |

Changing **Status** in Filament is the only supported way to move a record
through this list — there's no separate "publish" button. This one field is
also what the public API and frontend read: anything other than `published`
(or `scheduled` with a past date) is invisible to the public site, sitemap,
and search, with no separate flag to keep in sync.

## Roles

Two Filament roles exist today (`admin`, `editor` — see
`database/seeders/RolesSeeder.php`). Recommended (not yet enforced by policy)
division of responsibility:

- **Editor**: drafts content, moves `draft → in_review`.
- **Admin**: reviews, moves `in_review → approved → published`, and owns
  Company Settings, lead operations, and AI SEO approvals.

Scheduled publishing needs no cron job — the `Publishable` scope already
excludes anything with a future `published_at`, so the record simply becomes
visible the moment that timestamp passes on the next request.

## What's recorded automatically

Every record using the workflow trait carries an audit trail, visible on the
record's edit page ("Audit trail" section) and in the `content_activities`
table:

- **Created by / Updated by** — whoever last saved the record.
- **Approved by / Approved at** — stamped the moment Status becomes `approved`.
- **Published by** — stamped when Status becomes `published` or `scheduled`.
- **Activity log** — one entry per create, per status change, per field-level
  update (attribute *names* only, never the values — so drafts and lead PII
  can't leak into the log), and per delete.

None of this blocks a save — a CLI command or seeder run without an
authenticated user simply leaves these fields null, which is expected for
demo/import data.

## Cache invalidation and revalidation

Saving or deleting a record via Filament automatically:

1. Clears the relevant public API cache keys (`App\Observers\*Observer`).
2. If on-demand revalidation is enabled (see
   `docs/deployment/staging-deployment.md` §7), asks the Next.js frontend to
   rebuild the affected pages immediately. If it's disabled or the frontend is
   unreachable, the CMS save still succeeds — the page catches up within the
   5-minute ISR window instead.

## Lead scoring (a related, simpler workflow)

Leads (`ContactLead`) don't use the same draft/review pipeline — they arrive
already "live" via the public API and move through their own pipeline instead:
`new → reviewing → qualified → contacted → discovery_scheduled → proposal →
won/lost`, plus `spam`/`archived` as terminal outs. Every status change is
logged the same way (`content_activities`). See
`app/Services/LeadScoringService.php` for how the (advisory, never
authoritative) priority score is computed — an admin's manual priority always
wins over the computed score.

## AI SEO suggestions (never auto-published)

The AI SEO assistant (`/cms` → SEO → AI Generations) follows its own, stricter
state machine: `pending → generating → generated → approved|rejected|failed`.
**Approving** a suggestion only ever writes to the target's SEO title/
description fields (the two "appliable" suggestion types) — every other
suggestion type (outlines, FAQs, internal links, summaries, social snippets,
answer sections) is advisory only; approving it just records the editorial
decision, and a human copies whatever they want to use. There is no code path
that publishes AI-generated content directly to a live page.
