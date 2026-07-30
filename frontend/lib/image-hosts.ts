/**
 * Single source of truth for approved remote image hosts -- consumed by
 * next.config.ts's `images.remotePatterns` (what next/image is allowed to
 * optimize) and lib/csp.ts's `img-src` (what the browser is allowed to
 * load at all). Keeping one list prevents the two from drifting apart,
 * which would otherwise let a host through next/image but silently get
 * blocked by CSP, or vice versa widen CSP beyond what next/image permits.
 *
 * SECURITY: `remotePatterns` is an SSRF boundary, not just a convenience
 * allowlist. `/_next/image?url=...` makes the SERVER fetch whatever the
 * pattern permits, so anything matchable here is reachable from inside the
 * deployment. Next matches omitted fields as wildcards -- no `port` means ANY
 * port, no `pathname` means ANY path (see
 * node_modules/next/dist/shared/lib/match-remote-pattern.js) -- so an
 * unqualified `http://localhost` entry lets an attacker probe every internal
 * service on the Next host, including the Laravel API on :8000. The upstream
 * guidance is "be as specific as possible to prevent malicious usage"
 * (node_modules/next/dist/docs/01-app/01-getting-started/12-images.md).
 *
 * Hence: `port: ""` (default port only) on every production host, and
 * localhost is DEVELOPMENT-ONLY -- it must never be matchable in a production
 * build.
 */

type RemoteImageHost = {
  protocol: "http" | "https";
  hostname: string;
  /** `""` = default port only. Omitted = any port (dev convenience only). */
  port?: string;
};

const PRODUCTION_IMAGE_HOSTS: readonly RemoteImageHost[] = [
  { protocol: "https", hostname: "**.hexaterminal.com", port: "" },
  { protocol: "https", hostname: "cdn-icons-png.flaticon.com", port: "" },
  // Placeholder image hosts referenced by the legacy-migrated seed data
  // (database/seeders/ProjectsSeeder.php, ServicesSeeder.php) -- not used
  // by real CMS-entered content.
  { protocol: "https", hostname: "placehold.co", port: "" },
  // Real team member photos entered as Google Drive share links
  // (legacy-migrated TeamMember data) until CMS-uploaded media replaces them.
  // No `search: ""` here: Drive share URLs carry query parameters.
  { protocol: "https", hostname: "drive.google.com", port: "" },
];

/**
 * Local Laravel media during `next dev` only. Port is intentionally left open
 * because the local API port varies between setups; that is acceptable on a
 * developer machine and is precisely what must not ship to production.
 */
const DEVELOPMENT_IMAGE_HOSTS: readonly RemoteImageHost[] = [
  { protocol: "http", hostname: "localhost" },
];

export const REMOTE_IMAGE_HOSTS: readonly RemoteImageHost[] =
  process.env.NODE_ENV === "development"
    ? [...PRODUCTION_IMAGE_HOSTS, ...DEVELOPMENT_IMAGE_HOSTS]
    : PRODUCTION_IMAGE_HOSTS;

/**
 * CSP img-src origins: next/image's `**.` wildcard becomes CSP's `*.`.
 *
 * Port handling mirrors next/image's matching semantics exactly, so the two
 * layers cannot disagree: a CSP host-source with no port matches only the
 * scheme's default port, so `port: ""` emits no suffix while an omitted port
 * (any port, dev only) must emit an explicit `:*`.
 */
export function imageHostOrigins(): string[] {
  return REMOTE_IMAGE_HOSTS.map(({ protocol, hostname, port }) => {
    const host = hostname.replace(/^\*\*\./, "*.");
    const suffix = port === undefined ? ":*" : port === "" ? "" : `:${port}`;

    return `${protocol}://${host}${suffix}`;
  });
}
