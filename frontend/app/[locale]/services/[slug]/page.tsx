import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getService } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { CaseStudyCard } from "@/components/site/case-study-card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Link } from "@/i18n/navigation";
import { JsonLd } from "@/components/site/json-ld";
import { breadcrumbJsonLd, serviceJsonLd } from "@/lib/seo/jsonld";
import { absoluteUrl } from "@/lib/seo/alternates";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { resolveRobots } from "@/lib/seo/indexing";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}): Promise<Metadata> {
  const { locale, slug } = await params;
  const service = await getService(locale, slug);
  if (!service) return {};

  return pageMetadata({
    locale,
    path: `/services/${slug}`,
    title: service.seo?.title ?? service.name,
    description: service.seo?.description ?? service.summary ?? service.description ?? undefined,
    canonical: service.seo?.canonical_url,
    image: service.seo?.og_image ?? service.cover_image,
    robots: resolveRobots(service.seo?.noindex),
  });
}

export default async function ServiceDetailPage({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}) {
  const { locale, slug } = await params;
  setRequestLocale(locale);

  const [service, t] = await Promise.all([
    getService(locale, slug),
    getTranslations("services"),
  ]);

  if (!service) notFound();

  const relatedCaseStudies = service.related_case_studies ?? [];

  return (
    <>
      <JsonLd
        data={[
          serviceJsonLd({
            name: service.name,
            description: service.summary ?? service.description,
            url: absoluteUrl(locale, `/services/${slug}`),
          }),
          breadcrumbJsonLd(
            [
              { name: t("title"), path: "/services" },
              { name: service.name, path: `/services/${slug}` },
            ],
            locale,
          ),
        ]}
      />

      <Section as="div" className="bg-surface">
        <Container>
          <Breadcrumb items={[{ label: t("title"), href: "/services" }, { label: service.name }]} />
          <div className="grid items-center gap-10 lg:grid-cols-2 lg:gap-14">
            <div className="max-w-2xl">
              <h1 className="text-balance text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
                {service.name}
              </h1>
              {service.tagline ? (
                <p className="mt-5 text-pretty text-xl leading-relaxed text-muted-foreground">
                  {service.tagline}
                </p>
              ) : null}
              {service.summary ? (
                <p className="mt-5 text-pretty text-base leading-relaxed text-muted-foreground">
                  {service.summary}
                </p>
              ) : null}
              <div className="mt-8">
                <Button asChild size="lg">
                  <Link href="/start-a-project">{t("heroCta")}</Link>
                </Button>
              </div>
            </div>
            {service.cover_image ? (
              <div className="relative aspect-[4/3] overflow-hidden rounded-[var(--radius-lg)] border border-border bg-muted">
                <Image
                  src={service.cover_image}
                  alt={service.cover_image_alt ?? service.name}
                  fill
                  className="object-cover"
                  sizes="(min-width: 1024px) 50vw, 100vw"
                  priority
                />
              </div>
            ) : null}
          </div>
        </Container>
      </Section>

      {service.description ? (
        <Section>
          <Container narrow>
            <SectionHeading align="start" badge={t("approachBadge")} title={t("approachTitle")} />
            <div className="prose-content whitespace-pre-line text-base leading-relaxed text-foreground">
              {service.description}
            </div>
          </Container>
        </Section>
      ) : null}

      {service.features.length > 0 ? (
        <Section className="border-y border-border bg-surface">
          <Container>
            <SectionHeading align="start" badge={t("capabilitiesBadge")} title={t("capabilitiesTitle")} />
            <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {service.features.map((feature, index) => (
                <li
                  key={`${feature}-${index}`}
                  className="flex min-h-28 items-center rounded-[var(--radius-md)] border border-border bg-background p-5 text-pretty text-base font-medium leading-relaxed text-foreground"
                >
                  {feature}
                </li>
              ))}
            </ul>
          </Container>
        </Section>
      ) : null}

      {relatedCaseStudies.length > 0 ? (
        <Section>
          <Container>
            <SectionHeading align="start" badge={t("relatedWorkBadge")} title={t("relatedWorkTitle")} />
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {relatedCaseStudies.map((caseStudy) => (
                <CaseStudyCard key={caseStudy.slug} caseStudy={caseStudy} headingLevel="h3" />
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      {service.tech_stack.length > 0 ? (
        <Section className="pt-0">
          <Container narrow>
            <div className="border-t border-border pt-8">
              <h2 className="text-sm font-semibold text-muted-foreground">{t("technologyLabel")}</h2>
              <div className="mt-4 flex flex-wrap gap-2">
                {service.tech_stack.map((technology) => (
                  <Badge key={technology} variant="outline">
                    {technology}
                  </Badge>
                ))}
              </div>
            </div>
          </Container>
        </Section>
      ) : null}
    </>
  );
}
