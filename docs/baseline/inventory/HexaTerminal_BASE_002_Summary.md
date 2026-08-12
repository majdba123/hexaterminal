# HexaTerminal BASE-002 Route / Content Inventory Summary

Finalized as the authoritative CURRENT route/content inventory. No production crawl was run. Application code was not modified.

## Current Inventory Totals
- final current inventory records: 64
- implemented public routes: 56
- repository-only/current candidates: 19
- current HTTP UNKNOWN rows: 60
- duplicate route rows: 0
- rows missing provenance: 0

## Historical Evidence Limitation
- BASE-001B historical totals are accepted: 102 tested route entries, 86 successful localized public HTML routes, and 12 repository-only candidates.
- Exact BASE-001B 102 row identities are unavailable in current repository evidence.
- BASE-002 therefore represents the current code/content route universe, not a reconstruction of historical runtime routes.

## Current Route Groups
- current implemented routes: 56 implemented public route records from current code/route registry/sitemap/navigation evidence.
- current repository-only/future candidates: 19 candidate records retained from current repository evidence and legacy route disposition evidence.
- current unavailable/unknown routes: 60 rows have current HTTP UNKNOWN or incomplete HTTP evidence; no HTTP status was invented.

## Content-State Totals
- COMPLETE: 4
- EMPTY: 5
- MIXED_LANGUAGE: 4
- PARTIAL: 22
- PLACEHOLDER: 8
- UNAVAILABLE: 12
- UNKNOWN: 9

## Disposition Totals
- DELETE_CANDIDATE: 1
- HIDE_CANDIDATE: 14
- KEEP: 8
- KEEP_AND_IMPROVE: 37
- NEEDS_DECISION: 3
- REDIRECT_CANDIDATE: 1

## NEEDS_DECISION Items
- /project/{id}: Per-record legacy project mapping requires case-study vs system decision
- /projects: Founder decision required: projects hub maps to case studies or systems
- /team/{id}: Decision required: team page vs per-member route; current team route is content-blocked

## Evidence Handling
- Every inventory row preserves current-code provenance.
- Current HTTP status remains UNKNOWN where repository evidence does not prove a current HTTP result.
- BASE-001B is represented only as accepted summary evidence because row-level identities are not recoverable.
- EN and AR localized URLs remain separate inventory records.

## Validation
- all 64 current records retained: PASS
- every inventory row has provenance: PASS
- no duplicate route rows: PASS (0 duplicates)
- content-state totals agree across CSV/JSON/Markdown: PASS
- disposition totals agree across CSV/JSON/Markdown: PASS
- NEEDS_DECISION items clearly listed with reasons: PASS
- no production crawl run: PASS
- no application code changed by BASE-002 finalization: PASS
- no commit/push/merge performed: PASS

## Source Notes
- Current codebase and implemented Next routes: frontend/app, frontend/lib/routes/registry.ts.
- Navigation/footer/sitemap: frontend/components/site/header.tsx, footer.tsx, frontend/app/sitemap.ts.
- Current BASE-002 inventory: docs/baseline/inventory/HexaTerminal_BASE_002_Route_Content_Inventory.csv and .json.
- BASE-001B aggregate facts: accepted historical summary constraints supplied for BASE-002 finalization.
