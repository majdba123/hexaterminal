import { routing } from "../../i18n/routing";
import { SITE_URL } from "../seo/site";
import { ROUTES } from "./registry";

export type LocaleRedirect = {
  source: string;
  destination: string;
  permanent: boolean;
};

/**
 * Content-driven detail families that do not have one static entry per URL in
 * the route registry. Legacy singular paths such as `/service/:id` and
 * `/project/:id` are intentionally absent because they have record-specific
 * Redirect-table mappings.
 */
export const LOCALELESS_DYNAMIC_ROUTES = [
  "/services/:slug",
  "/systems/:slug",
  "/case-studies/:slug",
  "/industries/:slug",
  "/insights/:slug",
  "/about/team/:slug",
] as const;

/**
 * Permanently converge public URLs without a locale prefix onto the same
 * default locale advertised by canonical/hreflang/x-default metadata.
 */
export function defaultLocaleRedirects(): LocaleRedirect[] {
  const exact = ROUTES
    .filter((route) => route.path !== "")
    .map((route) => ({
      source: route.path,
      destination: `${SITE_URL}/${routing.defaultLocale}${route.path}`,
      permanent: true,
    }));

  const dynamic = LOCALELESS_DYNAMIC_ROUTES.map((source) => ({
    source,
    destination: `${SITE_URL}/${routing.defaultLocale}${source}`,
    permanent: true,
  }));

  return [...exact, ...dynamic];
}
