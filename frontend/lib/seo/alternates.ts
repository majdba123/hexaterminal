import { routing } from "@/i18n/routing";

const SITE_URL = process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000";

/**
 * hreflang alternates for a locale-invariant path (every content slug is
 * shared across locales -- see docs/architecture/content-model.md), plus
 * x-default pointing at the default locale per Google's guidance for
 * language/region alternates.
 */
export function localeAlternates(path: string) {
  const languages = Object.fromEntries(
    routing.locales.map((locale) => [locale, `${SITE_URL}/${locale}${path}`]),
  );

  return {
    languages: {
      ...languages,
      "x-default": `${SITE_URL}/${routing.defaultLocale}${path}`,
    },
  };
}

export function absoluteUrl(locale: string, path: string): string {
  return `${SITE_URL}/${locale}${path}`;
}
