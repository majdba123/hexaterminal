import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getCaseStudy, getCaseStudies } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Link } from "@/i18n/navigation";
import { routing } from "@/i18n/routing";
import type { CaseStudy } from "@/lib/api/types";
import { JsonLd } from "@/components/site/json-ld";
import { breadcrumbJsonLd } from "@/lib/seo/jsonld";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { resolveRobots } from "@/lib/seo/indexing";
import { ViewTracker } from "@/components/site/view-tracker";

export async function generateStaticParams() {
  const params: { locale: string; slug: string }[] = [];
  for (const locale of routing.locales) {
    const { data } = await getCaseStudies(locale, { perPage: 50 });
    for (const caseStudy of data) {
      params.push({ locale, slug: caseStudy.slug });
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
  const caseStudy = await getCaseStudy(locale, slug);
  if (!caseStudy) return {};

  return pageMetadata({
    locale,
    path: `/case-studies/${slug}`,
    title: caseStudy.seo?.title ?? caseStudy.title,
    description: caseStudy.seo?.description ?? caseStudy.summary ?? undefined,
    canonical: caseStudy.seo?.canonical_url,
    image: caseStudy.seo?.og_image ?? caseStudy.cover_image,
    robots: resolveRobots(caseStudy.seo?.noindex),
  });
}

function narrativeBlocks(caseStudy: CaseStudy, labels: Record<string, string>) {
  return [
    { key: "context", label: labels.context, value: caseStudy.context },
    { key: "problem", label: labels.problem, value: caseStudy.problem },
    { key: "constraints", label: labels.constraints, value: caseStudy.constraints },
    { key: "solution", label: labels.solution, value: caseStudy.solution },
    { key: "architecture", label: labels.architecture, value: caseStudy.architecture },
    { key: "outcomes", label: labels.outcomes, value: caseStudy.outcomes },
    { key: "evidence", label: labels.evidence, value: caseStudy.evidence },
  ].filter((block) => Boolean(block.value));
}

export default async function CaseStudyDetailPage({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}) {
  const { locale, slug } = await params;
  setRequestLocale(locale);

  const [caseStudy, t, tSystems] = await Promise.all([
    getCaseStudy(locale, slug),
    getTranslations("caseStudies"),
    getTranslations("systems"),
  ]);

  if (!caseStudy) notFound();

  const blocks = narrativeBlocks(caseStudy, {
    context: t("context"),
    problem: t("problem"),
    constraints: t("constraints"),
    solution: t("solution"),
    architecture: t("architecture"),
    outcomes: t("outcomes"),
    evidence: t("evidence"),
  });

  return (
    <Section as="div">
      <ViewTracker event="case_study_view" slug={slug} />
      <JsonLd
        data={breadcrumbJsonLd(
          [
            { name: t("title"), path: "/case-studies" },
            { name: caseStudy.title, path: `/case-studies/${slug}` },
          ],
          locale,
        )}
      />
      <Container narrow>
        <Breadcrumb items={[{ label: t("title"), href: "/case-studies" }, { label: caseStudy.title }]} />
        {caseStudy.cover_image ? (
          <div className="relative mb-8 aspect-16/9 w-full overflow-hidden rounded-[var(--radius-xl)] border border-border">
            <Image src={caseStudy.cover_image} alt={caseStudy.cover_image_alt ?? ""} fill className="object-cover" sizes="800px" />
          </div>
        ) : null}
        {caseStudy.client_name ? (
          <span className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
            {t("client")}: {caseStudy.client_name}
          </span>
        ) : null}
        <h1 className="mt-3 text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {caseStudy.title}
        </h1>
        {caseStudy.summary ? (
          <p className="mt-3 text-lg text-muted-foreground">{caseStudy.summary}</p>
        ) : null}

        {caseStudy.industries.length > 0 ? (
          <div className="mt-6 flex flex-wrap gap-2">
            {caseStudy.industries.map((industry) => (
              <Badge key={industry.slug} variant="outline">
                {industry.name}
              </Badge>
            ))}
          </div>
        ) : null}

        {blocks.map((block) => (
          <div key={block.key} className="mt-8">
            <h2 className="text-lg font-bold text-foreground">{block.label}</h2>
            <p className="mt-2 whitespace-pre-line text-pretty text-sm leading-relaxed text-muted-foreground">
              {block.value}
            </p>
          </div>
        ))}

        {caseStudy.features ? (
          <div className="mt-8">
            <h2 className="text-lg font-bold text-foreground">{tSystems("features")}</h2>
            <p className="mt-2 whitespace-pre-line text-pretty text-sm leading-relaxed text-muted-foreground">
              {caseStudy.features}
            </p>
          </div>
        ) : null}

        <div className="mt-10 flex flex-wrap gap-4">
          {caseStudy.system ? (
            <Link
              href={`/systems/${caseStudy.system.slug}`}
              className="focus-ring rounded-[var(--radius-md)] border border-border bg-surface px-4 py-3 text-sm font-medium text-foreground hover:border-primary/40"
            >
              {t("relatedSystem")}: {caseStudy.system.name}
            </Link>
          ) : null}
          {caseStudy.service ? (
            <Link
              href={`/services/${caseStudy.service.slug}`}
              className="focus-ring rounded-[var(--radius-md)] border border-border bg-surface px-4 py-3 text-sm font-medium text-foreground hover:border-primary/40"
            >
              {t("relatedService")}: {caseStudy.service.name}
            </Link>
          ) : null}
        </div>

        {caseStudy.project_url ? (
          <div className="mt-8">
            <Button asChild variant="outline">
              <a href={caseStudy.project_url} target="_blank" rel="noopener noreferrer">
                {tSystems("viewLive")}
              </a>
            </Button>
          </div>
        ) : null}
      </Container>
    </Section>
  );
}
