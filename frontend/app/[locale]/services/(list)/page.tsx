import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getServices } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { ServicesListingCard } from "@/components/site/services-listing-card";
import { EmptyState } from "@/components/site/empty-state";
import { Pagination } from "@/components/site/pagination";
import { CTA } from "@/components/site/cta";
import { parsePageParam } from "@/lib/pagination";

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
    getServices(locale, parsePageParam(page)),
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
          {services.data.length > 0 ? (
            <>
              <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                {services.data.map((service) => (
                  <ServicesListingCard key={service.slug} service={service} />
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
      <CTA
        eyebrow={t("finalCtaBadge")}
        title={t("finalCtaTitle")}
        subtitle={t("finalCtaSubtitle")}
        buttonLabel={t("finalCtaButton")}
        secondaryButtonLabel={t("finalCtaSecondaryButton")}
      />
    </>
  );
}
