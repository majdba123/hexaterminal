# Global Information Architecture

The target public IA for Hexa Terminal as a bilingual (EN/AR) B2B software
platform, and the current build/launch state of each route. The machine-readable
source of truth is `frontend/lib/routes/registry.ts`; this document is its human
companion and adds launch-phase context.

## Principles

- One primary domain, locale prefix **always** (`/en/...`, `/ar/...`). No
  country/city folders or regional subdomains — see
  `docs/seo/international-url-strategy.md`.
- A route is **only** advertised (nav/footer) and indexed when it has
  founder-approved substantive content. Everything else stays
  `content-blocked`: not in nav, not indexable, not in the sitemap.
- Detail collections (`/services/{slug}`, `/insights/{slug}`, …) are built from
  the CMS; only their parent hub appears in the registry.

## Route matrix

| Route | Purpose | Locale | Indexable | Sitemap | Structured data | Required content | Launch phase |
|-------|---------|--------|-----------|---------|-----------------|------------------|--------------|
| `/` | Home | en, ar | Yes | Yes | WebPage | Positioning, pillars (approved) | **current** |
| `/services` | Services hub | en, ar | Yes | Yes | CollectionPage | ≥1 approved Service | **current** |
| `/services/{slug}` | Service detail | en, ar | Per-record | Dynamic | Service | Approved Service | current (content-gated) |
| `/systems` | Systems hub | en, ar | Yes | Yes | CollectionPage | ≥1 public System | **current** |
| `/systems/{slug}` | System detail | en, ar | Per-record | Dynamic | SoftwareApplication | Public System | current (content-gated) |
| `/case-studies` | Case studies hub | en, ar | Yes | Yes | CollectionPage | ≥1 approved case study | **current (empty until content)** |
| `/case-studies/{slug}` | Case study detail | en, ar | Per-record | Dynamic | Article/CaseStudy | Founder-approved proof | content-blocked at record level |
| `/industries` | Industries hub | en, ar | Yes | Yes | CollectionPage | ≥1 industry | **current** |
| `/industries/{slug}` | Industry detail | en, ar | Per-record | Dynamic | WebPage | Approved industry copy | current (content-gated) |
| `/insights` | Articles hub | en, ar | Yes | Yes | CollectionPage | ≥1 published article | **current** |
| `/insights/{slug}` | Article detail | en, ar | Per-record | Dynamic | BlogPosting | Published article | current (content-gated) |
| `/pricing` | Pricing | en, ar | Yes | Yes | WebPage | Approved pricing profiles | **current** |
| `/estimate` | Cost estimator | en, ar | Yes | Yes | WebPage | Estimator config | **current** |
| `/estimate/{uuid}` | Estimate result | en, ar | No | No | — | Per-user | current (never indexed) |
| `/about` | About | en, ar | Yes | Yes | AboutPage | Approved company facts | **current** |
| `/contact` | Contact | en, ar | Yes | Yes | ContactPage | Approved contact data | **current** |
| `/start-a-project` | Lead conversion | en, ar | Yes | Yes | WebPage | Form copy | **current** |
| `/search` | Site search | en, ar | No | No | SearchResultsPage | — | **current (utility, noindex)** |
| `/privacy` | Privacy policy | en, ar | No | No | WebPage | **Legal review** | content-blocked |
| `/terms` | Terms | en, ar | No | No | WebPage | **Legal review** | content-blocked |
| `/team` | Team | en, ar | No | No | AboutPage | Approved bios + consent | content-blocked (infra only) |
| `/security` | Security trust | en, ar | No | No | WebPage | Verified security facts | content-blocked (infra only) |
| `/process` | Delivery process | en, ar | No | No | WebPage | Approved process copy | content-blocked (infra only) |
| `/accessibility` | Accessibility statement | en, ar | No | No | WebPage | **A11y audit + legal** | content-blocked (infra only) |
| `/technology` | Technology | en, ar | No | No | WebPage | Approved tech copy | content-blocked (infra only) |
| `/responsible-ai` | Responsible AI | en, ar | No | No | WebPage | Approved AI disclosure | content-blocked (infra only) |
| `/engineering-standards` | Engineering standards | en, ar | No | No | WebPage | Approved standards copy | content-blocked (infra only) |

### Retired / legacy (not in the new IA)

`/projects`, `/project/{id}`, `/service/{id}`, `/team/{id}`, `/api-docs`,
`/admin/*` — see `docs/migration/legacy-route-retirement-matrix.md`.

## Legend

- **current** — live route with approved (or intentionally minimal) content.
- **content-gated** — route is live; individual records publish only when
  approved (CMS editorial workflow).
- **content-blocked** — infrastructure/target only; must not be exposed until
  founder/legal-approved content exists. Flip `contentState` to `current` in
  the registry when content lands.

## How to launch a content-blocked route

1. Build the page under `frontend/app/[locale]/<path>/`.
2. Add approved EN + AR content (and any sensitive-claim approvals).
3. In `registry.ts`: set `contentState: "current"`, `indexable: true`,
   `inSitemap: true`, add `navKey`/`footerGroup` if it belongs in nav/footer,
   and add the `messages.nav` / `messages.legal` label keys in both locales.
4. The invariant test (`e2e/route-registry.spec.ts`) will fail until the label
   keys exist in both locales — this is the intended guard.
