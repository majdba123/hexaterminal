import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getSystems } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { SystemsListingCard } from "@/components/site/systems-listing-card";
import { EmptyState } from "@/components/site/empty-state";
import { Pagination } from "@/components/site/pagination";
import { parsePageParam } from "@/lib/pagination";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "systems" });
  return pageMetadata({
    locale,
    path: "/systems",
    title: t("title"),
    description: t("subtitle"),
  });
}

export default async function SystemsPage({
  params,
  searchParams,
}: {
  params: Promise<{ locale: string }>;
  searchParams: Promise<{ page?: string }>;
}) {
  const { locale } = await params;
  const { page } = await searchParams;
  setRequestLocale(locale);

  const [t, tc, systems] = await Promise.all([
    getTranslations("systems"),
    getTranslations("common"),
    getSystems(locale, { page: parsePageParam(page) }),
  ]);

  return (
    <>
      <Section as="div" className="bg-surface">
        <Container>
          <Breadcrumb items={[{ label: t("title") }]} />
          <SectionHeading
            as="h1"
            align="start"
            badge={t("heroBadge")}
            title={t("heroTitle")}
            subtitle={t("heroSubtitle")}
          />
          {systems.data.length > 0 ? (
            <>
              <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                {systems.data.map((system) => (
                  <SystemsListingCard key={system.slug} system={system} />
                ))}
              </div>
              <Pagination
                currentPage={systems.meta.current_page}
                lastPage={systems.meta.last_page}
                basePath="/systems"
                ariaLabel={tc("pagination")}
                previousLabel={tc("previousPage")}
                nextLabel={tc("nextPage")}
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
    </>
  );
}
