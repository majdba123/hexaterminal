import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getServices } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { ServiceCard } from "@/components/site/service-card";
import { EmptyState } from "@/components/site/empty-state";
import { Pagination } from "@/components/site/pagination";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "services" });
  return pageMetadata({
    locale,
    path: "/services",
    title: t("title"),
    description: t("subtitle"),
  });
}

export default async function ServicesPage({
  params,
  searchParams,
}: {
  params: Promise<{ locale: string }>;
  searchParams: Promise<{ page?: string }>;
}) {
  const { locale } = await params;
  const { page } = await searchParams;
  setRequestLocale(locale);

  const [t, tc, services] = await Promise.all([
    getTranslations("services"),
    getTranslations("common"),
    getServices(locale, Number(page ?? 1)),
  ]);

  return (
    <Section as="div">
      <Container>
        <Breadcrumb items={[{ label: t("title") }]} />
        <SectionHeading as="h1" align="start" title={t("title")} subtitle={t("subtitle")} />
        {services.data.length > 0 ? (
          <>
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {services.data.map((service) => (
                <ServiceCard key={service.slug} service={service} headingLevel="h2" />
              ))}
            </div>
            <Pagination
              currentPage={services.meta.current_page}
              lastPage={services.meta.last_page}
              basePath="/services"
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
