# Case Study Indexation Policy

Case studies are public, indexable portfolio proof only when all existing publication and curation gates pass.

## Rules

- The `/case-studies` hub is indexable in production and included in the XML sitemap.
- Detail URLs are discoverable only through the existing curated portfolio allowlist in `frontend/lib/api/client.ts`.
- A curated case study with `seo.noindex = true` remains noindex and is excluded from the sitemap.
- Non-production environments remain protected by `NEXT_PUBLIC_ALLOW_INDEXING`; this change does not weaken the global indexing kill-switch.
- Insights, legal pages awaiting approval, previews, search results, and other noindex surfaces remain excluded.

This keeps search visibility aligned with the same founder-approved portfolio surface visitors can already browse, without opening the entire CMS catalogue to indexing.
