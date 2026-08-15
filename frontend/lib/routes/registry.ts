/**
 * Global public-route registry — the single source of truth for the site's
 * information architecture.
 *
 * Before this file, the same route lists were copied by hand into
 * `components/site/header.tsx`, `footer.tsx`, `mobile-nav.tsx`, and
 * `app/sitemap.ts`. Drift between those copies is exactly the class of bug
 * this registry removes: navigation, footer, breadcrumbs, and the sitemap now
 * all derive from one typed table with explicit indexability and inclusion
 * flags.
 *
 * IMPORTANT — this registry describes STATIC, locale-invariant public routes
 * only. Dynamic detail collections (`/services/{slug}`, `/insights/{slug}`, …)
 * are content-driven and are still built from the API in `app/sitemap.ts`;
 * their parent hub lives here, the individual entries do not.
 *
 * Content safety: routes whose page does not yet have founder-approved
 * substantive content are recorded here with `contentState: "content-blocked"`,
 * `indexable: false`, `inSitemap: false`, and no `navKey`/`footerGroup`. They
 * document the *target* architecture (see
 * docs/architecture/global-information-architecture.md) WITHOUT exposing an
 * empty page in navigation, the sitemap, or search. No such route is made
 * public merely by appearing here.
 */

/** Broad page archetype — drives default structured-data selection and docs. */
export type PageType =
  | "home"
  | "hub" // a listing/index page for a content collection
  | "detail" // an individual content record (dynamic; parents only live here)
  | "conversion" // lead / estimate / start-a-project
  | "utility" // search, and other functional non-content pages
  | "trust" // security, process, accessibility, … (target IA)
  | "legal"; // privacy, terms, and future legal documents

/**
 * Where a route sits on the launch runway.
 * - `current`            live today with approved (or intentionally minimal) content
 * - `technically-ready`  infrastructure exists; awaiting founder content to go live
 * - `content-blocked`    must not be exposed until real content is approved
 * - `deferred`           intentionally not built in this phase
 */
export type ContentState =
  | "current"
  | "technically-ready"
  | "content-blocked"
  | "deferred";

/** schema.org type the page's primary JSON-LD node should declare. */
export type StructuredDataType =
  | "WebPage"
  | "CollectionPage"
  | "AboutPage"
  | "ContactPage"
  | "SearchResultsPage";

/** Footer column a route belongs to, matching `messages.*.footer` group keys. */
export type FooterGroup = "quickLinks" | "company" | "legal";

export interface RouteDefinition {
  /** Stable identifier, independent of the localized path. */
  readonly id: string;
  /**
   * Locale-invariant path with a leading slash; `""` is the home route.
   * The rendered URL is always `/{locale}${path}` (localePrefix "always").
   */
  readonly path: string;
  /** `messages.*.nav` key. Present ⇒ shown in the primary navigation. */
  readonly navKey?: string;
  /** Footer column, if the route appears in the footer. */
  readonly footerGroup?: FooterGroup;
  /** `messages.*.nav` (or legal) key used as the breadcrumb label. */
  readonly breadcrumbKey?: string;
  readonly pageType: PageType;
  /**
   * Whether an indexable *environment* should let search engines index this
   * page. This is the route-level policy; per-record CMS `seo.noindex` and the
   * environment kill-switch in `lib/seo/indexing.ts` still apply on top.
   */
  readonly indexable: boolean;
  /** Whether the route contributes a static entry to the XML sitemap. */
  readonly inSitemap: boolean;
  readonly contentState: ContentState;
  readonly structuredData: StructuredDataType;
  /**
   * Legacy Laravel/Blade paths that should 301 to this route once the legacy
   * surface is retired. Kept here so the redirect map, retirement matrix, and
   * router stay in agreement. Dynamic legacy routes with per-record ids
   * (`/project/{id}`) are handled by the DB-driven Redirect table instead —
   * see next.config.ts and docs/migration/legacy-route-retirement-matrix.md.
   */
  readonly legacyPaths?: readonly string[];
}

/**
 * The registry. Order within groups is the display order for nav and footer.
 *
 * Invariants enforced by e2e/route-registry.spec.ts:
 *  - `id` and `path` are unique
 *  - every `navKey`/`breadcrumbKey` exists in both en.json and ar.json
 *  - every `footerGroup` matches a `messages.footer` group key
 *  - an entry in primary navigation is `contentState: "current"`
 *  - footer legal links may remain available while awaiting legal approval
 *  - a `content-blocked` route is never `indexable` and never `inSitemap`
 *  - every `inSitemap` route is also `indexable`
 */
export const ROUTES: readonly RouteDefinition[] = [
  // ── Core (current) ────────────────────────────────────────────────
  {
    id: "home",
    path: "",
    navKey: "home",
    pageType: "home",
    indexable: true,
    inSitemap: true,
    contentState: "current",
    structuredData: "WebPage",
  },
  {
    id: "services",
    path: "/services",
    navKey: "services",
    footerGroup: "quickLinks",
    breadcrumbKey: "services",
    pageType: "hub",
    indexable: true,
    inSitemap: true,
    contentState: "current",
    structuredData: "CollectionPage",
    legacyPaths: ["/service/:id"],
  },
  {
    id: "systems",
    path: "/systems",
    navKey: "systems",
    footerGroup: "quickLinks",
    breadcrumbKey: "systems",
    pageType: "hub",
    indexable: true,
    inSitemap: true,
    contentState: "current",
    structuredData: "CollectionPage",
  },
  {
    id: "case-studies",
    path: "/case-studies",
    navKey: "caseStudies",
    footerGroup: "quickLinks",
    breadcrumbKey: "caseStudies",
    pageType: "hub",
    indexable: false,
    inSitemap: false,
    contentState: "current",
    structuredData: "CollectionPage",
    legacyPaths: ["/projects", "/project/:id"],
  },
  {
    id: "industries",
    path: "/industries",
    navKey: "industries",
    footerGroup: "quickLinks",
    breadcrumbKey: "industries",
    pageType: "hub",
    indexable: true,
    inSitemap: true,
    contentState: "current",
    structuredData: "CollectionPage",
  },
  {
    id: "pricing",
    path: "/pricing",
    // Intentionally outside the primary IA for now, but retained as a useful
    // high-intent footer destination.
    footerGroup: "quickLinks",
    breadcrumbKey: "pricing",
    pageType: "hub",
    indexable: true,
    inSitemap: true,
    contentState: "current",
    structuredData: "WebPage",
  },
  {
    id: "insights",
    path: "/insights",
    breadcrumbKey: "insights",
    pageType: "hub",
    indexable: false,
    inSitemap: false,
    contentState: "current",
    structuredData: "CollectionPage",
  },
  {
    id: "about",
    path: "/about",
    navKey: "about",
    footerGroup: "company",
    breadcrumbKey: "about",
    pageType: "hub",
    indexable: true,
    inSitemap: true,
    contentState: "current",
    structuredData: "AboutPage",
  },
  {
    id: "contact",
    path: "/contact",
    navKey: "contact",
    footerGroup: "company",
    breadcrumbKey: "contact",
    pageType: "conversion",
    indexable: true,
    inSitemap: true,
    contentState: "current",
    structuredData: "ContactPage",
  },

  // ── Conversion (current) ──────────────────────────────────────────
  {
    id: "start-a-project",
    path: "/start-a-project",
    footerGroup: "company",
    breadcrumbKey: "startProject",
    pageType: "conversion",
    indexable: true,
    inSitemap: true,
    contentState: "current",
    structuredData: "WebPage",
  },
  {
    id: "estimate",
    path: "/estimate",
    breadcrumbKey: "pricing",
    pageType: "conversion",
    indexable: true,
    inSitemap: true,
    contentState: "current",
    structuredData: "WebPage",
  },

  // ── Utility (current, intentionally not indexed / not in sitemap) ──
  {
    id: "search",
    path: "/search",
    breadcrumbKey: "search",
    pageType: "utility",
    // Search-results pages carry no unique indexable content — kept out of the
    // index and sitemap by policy (see docs/seo/crawler-policy.md).
    indexable: false,
    inSitemap: false,
    contentState: "current",
    structuredData: "SearchResultsPage",
  },

  // ── Legal (current pages, awaiting legal review before indexing) ──
  // Privacy/Terms render today but are deliberately excluded from the sitemap
  // and left to per-page CMS/noindex control until legal review completes.
  {
    id: "privacy",
    path: "/privacy",
    footerGroup: "legal",
    breadcrumbKey: "privacyTitle",
    pageType: "legal",
    indexable: false,
    inSitemap: false,
    contentState: "content-blocked",
    structuredData: "WebPage",
  },
  {
    id: "terms",
    path: "/terms",
    footerGroup: "legal",
    breadcrumbKey: "termsTitle",
    pageType: "legal",
    indexable: false,
    inSitemap: false,
    contentState: "content-blocked",
    structuredData: "WebPage",
  },

  // ── Target IA: Trust pages (infrastructure built; awaiting approved content) ──
  // Documented here so navigation, sitemap, and docs share one target map.
  // The TrustPage model, Filament resource, public API, and frontend route
  // for each of these now exist (see App\Models\TrustPage and
  // components/site/trust-page-view.tsx) -- `contentState` reflects that as
  // `technically-ready`. They stay out of nav/sitemap/indexing
  // (no navKey, indexable: false, inSitemap: false) until a founder/legal/
  // security-approved TrustPage record with real content is published; the
  // route itself already 404s for any slug without one (fail-closed, see
  // TrustPage::isReadyForPublication). Flipping an individual entry to
  // `current` (once approved) is tracked in
  // docs/architecture/global-information-architecture.md.
  trustRoute("security", "/security"),
  trustRoute("process", "/process"),
  trustRoute("accessibility", "/accessibility"),
  trustRoute("technology", "/technology"),
  trustRoute("responsible-ai", "/responsible-ai"),
  trustRoute("engineering-standards", "/engineering-standards"),
  {
    id: "team",
    path: "/team",
    pageType: "trust",
    indexable: false,
    inSitemap: false,
    contentState: "technically-ready",
    structuredData: "AboutPage",
    legacyPaths: ["/team/:id"],
  },
];

function trustRoute(id: string, path: string): RouteDefinition {
  return {
    id,
    path,
    pageType: "trust",
    indexable: false,
    inSitemap: false,
    contentState: "technically-ready",
    structuredData: "WebPage",
  };
}

// ── Selectors — the only API consumers should use ────────────────────

/** Primary navigation, in declared order. */
export function primaryNavRoutes(): RouteDefinition[] {
  return ROUTES.filter((r) => r.navKey);
}

/** Footer routes for a given column, in declared order. */
export function footerRoutes(group: FooterGroup): RouteDefinition[] {
  return ROUTES.filter((r) => r.footerGroup === group);
}

/** Locale-invariant paths that contribute a static sitemap entry. */
export function sitemapStaticPaths(): string[] {
  return ROUTES.filter((r) => r.inSitemap).map((r) => r.path);
}

/** Look up a route by its stable id. */
export function routeById(id: string): RouteDefinition | undefined {
  return ROUTES.find((r) => r.id === id);
}

/** Look up a route by its locale-invariant path. */
export function routeByPath(path: string): RouteDefinition | undefined {
  return ROUTES.find((r) => r.path === path);
}
