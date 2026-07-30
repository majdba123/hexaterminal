import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getSystem, getSystems } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { CaseStudyCard } from "@/components/site/case-study-card";
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
    description: system.seo?.description ?? system.short_description ?? undefined,
    canonical: system.seo?.canonical_url,
    image: system.seo?.og_image ?? system.cover_image,
    robots: resolveRobots(system.seo?.noindex),
  });
}

function detailBlocks(system: System, labels: Record<string, string>) {
  return [
    { key: "problem", label: labels.problem, value: system.problem },
    { key: "solution", label: labels.solution, value: system.solution },
    { key: "features", label: labels.features, value: system.features },
    { key: "business_outcomes", label: labels.outcomes, value: system.business_outcomes },
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

  const blocks = detailBlocks(system, {
    problem: t("problem"),
    solution: t("solution"),
    features: t("features"),
    outcomes: t("outcomes"),
  });

  return (
    <Section as="div">
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
      <Container narrow>
        <Breadcrumb items={[{ label: t("title"), href: "/systems" }, { label: system.name }]} />
        {system.cover_image ? (
          <div className="relative mb-8 aspect-16/9 w-full overflow-hidden rounded-[var(--radius-xl)] border border-border">
            <Image src={system.cover_image} alt={system.cover_image_alt ?? ""} fill className="object-cover" sizes="800px" />
          </div>
        ) : null}
        <Badge>{t(`type.${system.type}` as "type.saas_product")}</Badge>
        <h1 className="mt-3 text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {system.name}
        </h1>
        {system.tagline ? <p className="mt-3 text-lg text-muted-foreground">{system.tagline}</p> : null}
        {system.full_description ?? system.short_description ? (
          <p className="mt-6 whitespace-pre-line text-base leading-relaxed text-foreground">
            {system.full_description ?? system.short_description}
          </p>
        ) : null}

        {blocks.map((block) => (
          <div key={block.key} className="mt-8">
            <h2 className="text-lg font-bold text-foreground">{block.label}</h2>
            <p className="mt-2 whitespace-pre-line text-pretty text-sm leading-relaxed text-muted-foreground">
              {block.value}
            </p>
          </div>
        ))}

        {system.tech_stack.length > 0 ? (
          <div className="mt-8">
            <h2 className="text-lg font-bold text-foreground">{t("techStack")}</h2>
            <div className="mt-3 flex flex-wrap gap-2">
              {system.tech_stack.map((tech) => (
                <Badge key={tech} variant="outline">
                  {tech}
                </Badge>
              ))}
            </div>
          </div>
        ) : null}

        <div className="mt-8 flex flex-wrap gap-3">
          {system.live_url ? (
            <Button asChild variant="outline">
              <a href={system.live_url} target="_blank" rel="noopener noreferrer">
                {t("viewLive")}
              </a>
            </Button>
          ) : null}
          {system.demo_url ? (
            <Button asChild variant="outline">
              <a href={system.demo_url} target="_blank" rel="noopener noreferrer">
                {t("viewDemo")}
              </a>
            </Button>
          ) : null}
        </div>
      </Container>

      {system.case_studies.length > 0 ? (
        <Container className="mt-16">
          <h2 className="mb-6 text-2xl font-extrabold text-foreground">{t("relatedCaseStudies")}</h2>
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {system.case_studies.map((cs) => (
              <CaseStudyCard key={cs.slug} caseStudy={cs} />
            ))}
          </div>
        </Container>
      ) : null}
    </Section>
  );
}
