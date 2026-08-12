import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getCaseStudies } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { resolveRobots } from "@/lib/seo/indexing";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { CaseStudyCard } from "@/components/site/case-study-card";
import { EmptyState } from "@/components/site/empty-state";
import { Pagination } from "@/components/site/pagination";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "caseStudies" });
  return pageMetadata({
    locale,
    path: "/case-studies",
    title: t("title"),
    description: t("subtitle"),
    robots: resolveRobots(true),
  });
}

export default async function CaseStudiesPage({
  params,
  searchParams,
}: {
  params: Promise<{ locale: string }>;
  searchParams: Promise<{ page?: string }>;
}) {
  const { locale } = await params;
  const { page } = await searchParams;
  setRequestLocale(locale);

  const [t, tc, caseStudies] = await Promise.all([
    getTranslations("caseStudies"),
    getTranslations("common"),
    getCaseStudies(locale, { page: Number(page ?? 1) }),
  ]);

  return (
    <Section as="div">
      <Container>
        <Breadcrumb items={[{ label: t("title") }]} />
        <SectionHeading as="h1" align="start" title={t("title")} subtitle={t("subtitle")} />
        {caseStudies.data.length > 0 ? (
          <>
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {caseStudies.data.map((caseStudy) => (
                <CaseStudyCard key={caseStudy.slug} caseStudy={caseStudy} headingLevel="h2" />
              ))}
            </div>
            <Pagination
              currentPage={caseStudies.meta.current_page}
              lastPage={caseStudies.meta.last_page}
              basePath="/case-studies"
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
