import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getSystem, getSystems } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { CTA } from "@/components/site/cta";
import { CaseStudyCard } from "@/components/site/case-study-card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Link } from "@/i18n/navigation";
import { routing } from "@/i18n/routing";
import type { System } from "@/lib/api/types";
import { JsonLd } from "@/components/site/json-ld";
import { breadcrumbJsonLd, softwareApplicationJsonLd } from "@/lib/seo/jsonld";
import { absoluteUrl } from "@/lib/seo/alternates";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { resolveRobots } from "@/lib/seo/indexing";
import { ViewTracker } from "@/components/site/view-tracker";

export async function generateStaticParams() {
  const params: { locale: string; slug: string }[] = [];
  for (const locale of routing.locales) {
    const { data } = await getSystems(locale, { perPage: 50 });
    for (const system of data) {
      params.push({ locale, slug: system.slug });
    }
  }
  return params;
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}): Promise<Metadata> {
  const { locale, slug } = await params;
  const system = await getSystem(locale, slug);
  if (!system) return {};

  return pageMetadata({
    locale,
    path: `/systems/${slug}`,
    title: system.seo?.title ?? system.name,
    description: system.seo?.description ?? system.short_description ?? system.full_description ?? undefined,
    canonical: system.seo?.canonical_url,
    image: system.seo?.og_image ?? system.cover_image,
    robots: resolveRobots(system.seo?.noindex),
  });
}

function operationalBlocks(system: System, labels: Record<string, string>) {
  return [
    { key: "problem", label: labels.problem, value: system.problem },
    { key: "solution", label: labels.solution, value: system.solution },
    { key: "audience", label: labels.audience, value: system.target_audience },
  ].filter((block) => Boolean(block.value));
}

export default async function SystemDetailPage({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}) {
  const { locale, slug } = await params;
  setRequestLocale(locale);

  const [system, t] = await Promise.all([getSystem(locale, slug), getTranslations("systems")]);

  if (!system) notFound();

  const capabilities = system.features
    ?.split(/\r?\n/)
    .map((feature) => feature.trim())
    .filter(Boolean) ?? [];
  const operationalContext = operationalBlocks(system, {
    problem: t("problem"),
    solution: t("solution"),
    audience: t("targetAudience"),
  });

  return (
    <>
      <ViewTracker event="system_view" slug={slug} />
      <JsonLd
        data={[
          softwareApplicationJsonLd({
            name: system.name,
            description: system.short_description ?? system.tagline,
            url: absoluteUrl(locale, `/systems/${slug}`),
          }),
          breadcrumbJsonLd(
            [
              { name: t("title"), path: "/systems" },
              { name: system.name, path: `/systems/${slug}` },
            ],
            locale,
          ),
        ]}
      />

      <Section as="div" className="bg-surface">
        <Container>
          <Breadcrumb items={[{ label: t("title"), href: "/systems" }, { label: system.name }]} />
          <div className="grid items-center gap-10 lg:grid-cols-2 lg:gap-14">
            <div className="max-w-2xl">
              <div className="flex flex-wrap items-center gap-2">
                <Badge>{t(`type.${system.type}`)}</Badge>
                {system.category ? <span className="text-sm text-muted-foreground">{system.category}</span> : null}
              </div>
              <h1 className="mt-4 text-balance text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
                {system.name}
              </h1>
              {system.tagline ? (
                <p className="mt-5 text-pretty text-xl leading-relaxed text-muted-foreground">{system.tagline}</p>
              ) : null}
              {system.short_description ? (
                <p className="mt-5 text-pretty text-base leading-relaxed text-muted-foreground">
                  {system.short_description}
                </p>
              ) : null}
              <div className="mt-8">
                <Button asChild size="lg">
                  <Link href="/start-a-project">{t("heroCta")}</Link>
                </Button>
              </div>
            </div>
            {system.cover_image ? (
              <div className="relative aspect-[4/3] overflow-hidden rounded-[var(--radius-lg)] border border-border bg-muted">
                <Image
                  src={system.cover_image}
                  alt={system.cover_image_alt ?? ""}
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

      {system.full_description ? (
        <Section>
          <Container narrow>
            <SectionHeading align="start" badge={t("descriptionBadge")} title={t("descriptionTitle")} />
            <div className="prose-content whitespace-pre-line text-base leading-relaxed text-foreground">
              {system.full_description}
            </div>
          </Container>
        </Section>
      ) : null}

      {capabilities.length > 0 ? (
        <Section className="border-y border-border bg-surface">
          <Container>
            <SectionHeading align="start" badge={t("capabilitiesBadge")} title={t("capabilitiesTitle")} />
            <ul className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
              {capabilities.map((capability, index) => (
                <li
                  key={`${capability}-${index}`}
                  className="flex min-h-28 items-center rounded-[var(--radius-md)] border border-border bg-background p-5 text-pretty text-base font-medium leading-relaxed text-foreground"
                >
                  {capability}
                </li>
              ))}
            </ul>
          </Container>
        </Section>
      ) : null}

      {operationalContext.length > 0 ? (
        <Section>
          <Container>
            <SectionHeading align="start" badge={t("operationBadge")} title={t("operationTitle")} />
            <div className="grid gap-5 lg:grid-cols-3">
              {operationalContext.map((block) => (
                <section key={block.key} className="rounded-[var(--radius-md)] border border-border bg-surface p-6">
                  <h2 className="text-lg font-bold text-foreground">{block.label}</h2>
                  <p className="mt-3 whitespace-pre-line text-pretty text-sm leading-relaxed text-muted-foreground">
                    {block.value}
                  </p>
                </section>
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      {system.case_studies.length > 0 ? (
        <Section className="border-y border-border bg-surface">
          <Container>
            <SectionHeading align="start" badge={t("relatedWorkBadge")} title={t("relatedWorkTitle")} />
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {system.case_studies.map((caseStudy) => (
                <CaseStudyCard key={caseStudy.slug} caseStudy={caseStudy} headingLevel="h3" />
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      {system.tech_stack.length > 0 ? (
        <Section className="pt-0">
          <Container narrow>
            <div className="border-t border-border pt-8">
              <h2 className="text-sm font-semibold text-muted-foreground">{t("technologyLabel")}</h2>
              <div className="mt-4 flex flex-wrap gap-2">
                {system.tech_stack.map((technology) => (
                  <Badge key={technology} variant="outline">
                    {technology}
                  </Badge>
                ))}
              </div>
            </div>
          </Container>
        </Section>
      ) : null}

      <CTA
        eyebrow={t("detailCtaBadge")}
        title={t("detailCtaTitle")}
        subtitle={t("detailCtaSubtitle")}
        buttonLabel={t("detailCtaButton")}
        secondaryButtonLabel={t("detailCtaSecondaryButton")}
      />
    </>
  );
}
