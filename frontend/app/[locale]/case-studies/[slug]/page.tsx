import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getCaseStudy } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { Badge } from "@/components/ui/badge";
import { Link } from "@/i18n/navigation";
import type { CaseStudy } from "@/lib/api/types";
import { JsonLd } from "@/components/site/json-ld";
import { breadcrumbJsonLd } from "@/lib/seo/jsonld";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { resolveRobots } from "@/lib/seo/indexing";
import { ViewTracker } from "@/components/site/view-tracker";

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
    description: caseStudy.seo?.description ?? caseStudy.summary ?? caseStudy.context ?? undefined,
    canonical: caseStudy.seo?.canonical_url,
    image: caseStudy.seo?.og_image ?? caseStudy.cover_image,
    robots: resolveRobots(true),
  });
}

function contextLinks(caseStudy: CaseStudy, labels: Record<string, string>) {
  return [
    caseStudy.service ? { label: labels.service, name: caseStudy.service.name, href: `/services/${caseStudy.service.slug}` } : null,
    caseStudy.system ? { label: labels.system, name: caseStudy.system.name, href: `/systems/${caseStudy.system.slug}` } : null,
  ].filter((link): link is { label: string; name: string; href: string } => Boolean(link));
}

export default async function CaseStudyDetailPage({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}) {
  const { locale, slug } = await params;
  setRequestLocale(locale);

  const [caseStudy, t] = await Promise.all([getCaseStudy(locale, slug), getTranslations("caseStudies")]);

  if (!caseStudy) notFound();

  const capabilities = caseStudy.features
    ?.split(/\r?\n/)
    .map((feature) => feature.trim())
    .filter(Boolean) ?? [];
  const links = contextLinks(caseStudy, {
    service: t("relatedService"),
    system: t("relatedSystem"),
  });

  return (
    <>
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

      <Section as="div" className="bg-surface">
        <Container>
          <Breadcrumb items={[{ label: t("title"), href: "/case-studies" }, { label: caseStudy.title }]} />
          <div className="grid items-center gap-10 lg:grid-cols-2 lg:gap-14">
            <div className="max-w-2xl">
              {caseStudy.project_classification ? (
                <Badge>{t(`classification.${caseStudy.project_classification}`)}</Badge>
              ) : null}
              <h1 className="mt-4 text-balance text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
                {caseStudy.title}
              </h1>
              {caseStudy.summary ? (
                <p className="mt-5 text-pretty text-xl leading-relaxed text-muted-foreground">{caseStudy.summary}</p>
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
            </div>
            {caseStudy.cover_image ? (
              <div className="relative aspect-[4/3] overflow-hidden rounded-[var(--radius-lg)] border border-border bg-muted">
                <Image
                  src={caseStudy.cover_image}
                  alt={caseStudy.cover_image_alt ?? caseStudy.title}
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

      {caseStudy.context ? (
        <Section>
          <Container narrow>
            <SectionHeading align="start" badge={t("contextBadge")} title={t("contextTitle")} />
            <div className="prose-content whitespace-pre-line text-base leading-relaxed text-foreground">
              {caseStudy.context}
            </div>
          </Container>
        </Section>
      ) : null}

      {caseStudy.problem || caseStudy.constraints ? (
        <Section className="border-y border-border bg-surface">
          <Container narrow>
            <SectionHeading align="start" badge={t("challengeBadge")} title={t("challengeTitle")} />
            {caseStudy.problem ? (
              <p className="whitespace-pre-line text-pretty text-base leading-relaxed text-foreground">{caseStudy.problem}</p>
            ) : null}
            {caseStudy.constraints ? (
              <div className="mt-6 border-s border-border ps-5">
                <h3 className="text-sm font-semibold text-foreground">{t("constraints")}</h3>
                <p className="mt-2 whitespace-pre-line text-pretty text-sm leading-relaxed text-muted-foreground">
                  {caseStudy.constraints}
                </p>
              </div>
            ) : null}
          </Container>
        </Section>
      ) : null}

      {caseStudy.solution || caseStudy.architecture ? (
        <Section>
          <Container narrow>
            <SectionHeading align="start" badge={t("solutionBadge")} title={t("solutionTitle")} />
            {caseStudy.solution ? (
              <p className="whitespace-pre-line text-pretty text-base leading-relaxed text-foreground">{caseStudy.solution}</p>
            ) : null}
            {caseStudy.architecture ? (
              <div className="mt-6 border-s border-border ps-5">
                <h3 className="text-sm font-semibold text-foreground">{t("architecture")}</h3>
                <p className="mt-2 whitespace-pre-line text-pretty text-sm leading-relaxed text-muted-foreground">
                  {caseStudy.architecture}
                </p>
              </div>
            ) : null}
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

      {caseStudy.outcomes ? (
        <Section>
          <Container narrow>
            <SectionHeading align="start" badge={t("outcomeBadge")} title={t("outcomeTitle")} />
            <p className="whitespace-pre-line text-pretty text-base leading-relaxed text-foreground">{caseStudy.outcomes}</p>
          </Container>
        </Section>
      ) : null}

      {links.length > 0 || caseStudy.industries.length > 0 ? (
        <Section className="pt-0">
          <Container narrow>
            <div className="border-t border-border pt-8">
              <h2 className="text-sm font-semibold text-muted-foreground">{t("relatedContext")}</h2>
              <div className="mt-4 flex flex-wrap gap-3">
                {links.map((link) => (
                  <Link
                    key={link.href}
                    href={link.href}
                    className="focus-ring rounded-[var(--radius-md)] border border-border bg-surface px-4 py-3 text-sm font-medium text-foreground hover:border-primary/40"
                  >
                    {link.label}: {link.name}
                  </Link>
                ))}
                {caseStudy.industries.map((industry) => (
                  <Badge key={industry.slug} variant="outline">
                    {industry.name}
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
