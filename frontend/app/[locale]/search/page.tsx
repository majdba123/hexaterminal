import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { search } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { EmptyState } from "@/components/site/empty-state";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Link } from "@/i18n/navigation";
import type { SearchResults } from "@/lib/api/types";

const GROUPS: (keyof SearchResults["results"])[] = [
  "services",
  "systems",
  "case_studies",
  "industries",
  "articles",
];

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "search" });
  return pageMetadata({
    locale,
    path: "/search",
    title: t("title"),
    description: t("subtitle"),
    // Search result pages are never indexable -- the query is user input, not
    // curated content, so this stays noindex regardless of the site policy.
    robots: { index: false, follow: false },
  });
}

export default async function SearchPage({
  params,
  searchParams,
}: {
  params: Promise<{ locale: string }>;
  searchParams: Promise<{ q?: string }>;
}) {
  const { locale } = await params;
  const { q } = await searchParams;
  setRequestLocale(locale);

  const t = await getTranslations("search");
  const tc = await getTranslations("common");
  const query = (q ?? "").trim();
  const results = query.length >= 2 ? await search(locale, query) : { query, results: {} };
  const hasAnyResults = GROUPS.some((group) => (results.results[group]?.length ?? 0) > 0);

  return (
    <Section as="div">
      <Container narrow>
        <Breadcrumb items={[{ label: t("title") }]} />
        <h1 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {t("title")}
        </h1>
        <p className="mt-3 max-w-xl text-pretty text-base leading-relaxed text-muted-foreground">
          {t("subtitle")}
        </p>

        <form action={`/${locale}/search`} method="get" className="mt-8 flex gap-3">
          <Input
            type="search"
            name="q"
            defaultValue={query}
            placeholder={t("placeholder")}
            aria-label={t("title")}
            className="flex-1"
          />
          <Button type="submit">{t("button")}</Button>
        </form>

        <div className="mt-10">
          {query.length < 2 ? (
            // No action here on purpose: the next step is the search field
            // immediately above, and a competing CTA would only distract.
            <EmptyState title={t("title")} description={t("noQuery")} />
          ) : !hasAnyResults ? (
            <EmptyState
              title={t("title")}
              description={t("noResults", { query })}
              action={{ href: "/systems", label: tc("emptyBrowseSystems") }}
            />
          ) : (
            <div className="flex flex-col gap-10">
              <p className="text-sm text-muted-foreground">{t("resultsFor", { query })}</p>
              {GROUPS.map((group) => {
                const hits = results.results[group];
                if (!hits || hits.length === 0) return null;

                return (
                  <div key={group}>
                    <h2 className="text-lg font-semibold text-foreground">{t(group)}</h2>
                    <ul className="mt-3 flex flex-col gap-3">
                      {hits.map((hit) => (
                        <li key={hit.path}>
                          <Link
                            href={hit.path}
                            className="focus-ring block rounded-[var(--radius-md)] border border-border p-4 transition-colors hover:border-primary/40"
                          >
                            <p className="font-medium text-foreground">{hit.label}</p>
                            {hit.excerpt ? (
                              <p className="mt-1 line-clamp-2 text-sm text-muted-foreground">
                                {hit.excerpt}
                              </p>
                            ) : null}
                          </Link>
                        </li>
                      ))}
                    </ul>
                  </div>
                );
              })}
            </div>
          )}
        </div>
      </Container>
    </Section>
  );
}
