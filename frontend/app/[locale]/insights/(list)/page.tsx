import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getArticles, getArticleCategories } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { ArticleCard } from "@/components/site/article-card";
import { EmptyState } from "@/components/site/empty-state";
import { Pagination } from "@/components/site/pagination";
import { Link } from "@/i18n/navigation";
import { cn } from "@/lib/utils";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "insights" });
  return pageMetadata({
    locale,
    path: "/insights",
    title: t("title"),
    description: t("subtitle"),
  });
}

export default async function InsightsPage({
  params,
  searchParams,
}: {
  params: Promise<{ locale: string }>;
  searchParams: Promise<{ page?: string; category?: string }>;
}) {
  const { locale } = await params;
  const { page, category } = await searchParams;
  setRequestLocale(locale);

  const [t, tc, articles, categories] = await Promise.all([
    getTranslations("insights"),
    getTranslations("common"),
    getArticles(locale, Number(page ?? 1), 12, { category }),
    getArticleCategories(locale),
  ]);

  // min-h-11: these chips are the primary filter control on the page, and at
  // py-1 they were ~26px tall -- well under the 44px minimum touch target.
  const chipClass = (active: boolean) =>
    cn(
      "focus-ring inline-flex min-h-11 items-center rounded-full border px-4 text-xs font-semibold tracking-wide transition-colors",
      active
        ? "border-primary/20 bg-primary/10 text-secondary"
        : "border-border bg-transparent text-muted-foreground hover:text-foreground",
    );

  return (
    <Section as="div">
      <Container>
        <Breadcrumb items={[{ label: t("title") }]} />
        <SectionHeading as="h1" align="start" title={t("title")} subtitle={t("subtitle")} />

        {categories.length > 0 ? (
          <div className="mb-8 flex flex-wrap gap-2">
            <Link href="/insights" className={chipClass(!category)}>
              {t("allCategories")}
            </Link>
            {categories.map((cat) => (
              <Link
                key={cat.slug}
                href={`/insights?category=${cat.slug}`}
                className={chipClass(category === cat.slug)}
              >
                {cat.name} ({cat.published_count})
              </Link>
            ))}
          </div>
        ) : null}

        {articles.data.length > 0 ? (
          <>
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {articles.data.map((article) => (
                <ArticleCard key={article.slug} article={article} headingLevel="h2" />
              ))}
            </div>
            <Pagination
              currentPage={articles.meta.current_page}
              lastPage={articles.meta.last_page}
              basePath="/insights"
              extraParams={{ category }}
            />
          </>
        ) : (
          <EmptyState
            title={tc("noResults")}
            description={tc("noResultsDesc")}
            action={{ href: "/start-a-project", label: tc("emptyCta") }}
          />
        )}
      </Container>
    </Section>
  );
}
