import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import { ArrowUpRight, CirclePlay, Layers3, Network, Workflow } from "lucide-react";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { Container } from "@/components/site/container";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { JsonLd } from "@/components/site/json-ld";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { ViewTracker } from "@/components/site/view-tracker";
import { Button } from "@/components/ui/button";
import { Badge } from "@/components/ui/badge";
import { Link } from "@/i18n/navigation";
import { getCaseStudy } from "@/lib/api/client";
import type { CaseStudy } from "@/lib/api/types";
import { cn } from "@/lib/utils";
import { resolveRobots } from "@/lib/seo/indexing";
import { breadcrumbJsonLd } from "@/lib/seo/jsonld";
import { pageMetadata } from "@/lib/seo/page-metadata";

type ContextLink = {
  label: string;
  name: string;
  href: string;
};

function splitLines(value: string | null) {
  return value
    ?.split(/\r?\n/)
    .map((item) => item.trim())
    .filter(Boolean) ?? [];
}

function splitParagraphs(value: string | null) {
  return value
    ?.split(/\n\s*\n/)
    .map((item) => item.trim())
    .filter(Boolean) ?? [];
}

function storyParagraphs(value: string | null) {
  const paragraphs = splitParagraphs(value);
  return paragraphs.length > 0 ? paragraphs : splitLines(value);
}

function contextLinks(caseStudy: CaseStudy, labels: Record<string, string>) {
  return [
    caseStudy.service
      ? { label: labels.service, name: caseStudy.service.name, href: `/services/${caseStudy.service.slug}` }
      : null,
    caseStudy.system
      ? { label: labels.system, name: caseStudy.system.name, href: `/systems/${caseStudy.system.slug}` }
      : null,
  ].filter((link): link is ContextLink => Boolean(link));
}

function proofLayoutClass(index: number) {
  if (index === 0) return "md:col-span-7 md:row-span-2";
  if (index === 1) return "md:col-span-5";
  if (index === 2) return "md:col-span-5";
  return "md:col-span-4";
}

function proofAspectClass(count: number, index: number) {
  if (count === 1) return "aspect-[16/10]";
  if (count === 2) return index === 0 ? "aspect-[16/10]" : "aspect-[4/3]";
  if (index === 0) return "aspect-[16/10] md:aspect-auto";
  if (index < 3) return "aspect-[4/3]";
  return "aspect-[16/11]";
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
    description: caseStudy.seo?.description ?? caseStudy.summary ?? caseStudy.context ?? undefined,
    canonical: caseStudy.seo?.canonical_url,
    image: caseStudy.seo?.og_image ?? caseStudy.cover_image,
    robots: resolveRobots(true),
  });
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

  const contextParagraphs = storyParagraphs(caseStudy.context);
  const problemParagraphs = storyParagraphs(caseStudy.problem);
  const constraintPoints = splitLines(caseStudy.constraints);
  const solutionParagraphs = storyParagraphs(caseStudy.solution);
  const architecturePoints = splitLines(caseStudy.architecture);
  const capabilityPoints = splitLines(caseStudy.features);
  const outcomePoints = splitLines(caseStudy.outcomes);
  const evidencePoints = splitLines(caseStudy.evidence);
  const proofImages = caseStudy.gallery.filter(Boolean);
  const featuredProofImages = proofImages.slice(0, 4);
  const remainingProofImages = proofImages.slice(4);
  const links = contextLinks(caseStudy, {
    service: t("relatedService"),
    system: t("relatedSystem"),
  });
  const heroMeta = [
    caseStudy.client_name ? { label: t("client"), value: caseStudy.client_name } : null,
    caseStudy.project_classification
      ? { label: t("projectType"), value: t(`classification.${caseStudy.project_classification}`) }
      : null,
  ].filter((item): item is { label: string; value: string } => Boolean(item));
  const snapshotItems = [
    caseStudy.client_name ? { label: t("snapshotClient"), value: caseStudy.client_name } : null,
    caseStudy.project_classification
      ? { label: t("snapshotType"), value: t(`classification.${caseStudy.project_classification}`) }
      : null,
    caseStudy.project_url
      ? { label: t("snapshotStatus"), value: t("snapshotLive") }
      : caseStudy.video_url
        ? { label: t("snapshotStatus"), value: t("snapshotPreview") }
        : null,
    caseStudy.system ? { label: t("snapshotSystem"), value: caseStudy.system.name } : null,
    caseStudy.service ? { label: t("snapshotService"), value: caseStudy.service.name } : null,
  ].filter((item): item is { label: string; value: string } => Boolean(item));
  const studioCapabilities = [
    { icon: Workflow, label: t("trustOperations") },
    { icon: Layers3, label: t("trustArchitecture") },
    { icon: Network, label: t("trustEngineering") },
  ];
  const hasContextSection = contextParagraphs.length > 0 || problemParagraphs.length > 0 || constraintPoints.length > 0;
  const hasEngineeringSection = solutionParagraphs.length > 0 || architecturePoints.length > 0;
  const hasProofSection = proofImages.length > 0 || Boolean(caseStudy.video_url);
  const hasOutcomeSection = outcomePoints.length > 0 || evidencePoints.length > 0;
  const hasRelatedContext = links.length > 0 || caseStudy.industries.length > 0;

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

      <Section as="div" className="overflow-hidden bg-surface">
        <Container>
          <Breadcrumb items={[{ label: t("title"), href: "/case-studies" }, { label: caseStudy.title }]} />
          <div
            className={cn(
              "grid items-start gap-8 lg:gap-12",
              caseStudy.cover_image ? "lg:grid-cols-[minmax(0,1.05fr)_minmax(0,0.95fr)]" : "",
            )}
          >
            <div className={cn("max-w-3xl", !caseStudy.cover_image ? "lg:max-w-4xl" : "")}>
              {caseStudy.project_classification ? (
                <Badge>{t(`classification.${caseStudy.project_classification}`)}</Badge>
              ) : null}
              <h1 className="mt-4 max-w-4xl text-balance text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
                {caseStudy.title}
              </h1>
              {caseStudy.summary ? (
                <p className="mt-5 max-w-3xl text-pretty text-lg leading-8 text-muted-foreground sm:text-xl">
                  {caseStudy.summary}
                </p>
              ) : null}
              {heroMeta.length > 0 || caseStudy.industries.length > 0 ? (
                <div className="mt-6 flex flex-wrap items-center gap-3 text-sm text-muted-foreground">
                  {heroMeta.map((item) => (
                    <span
                      key={item.label}
                      className="rounded-full border border-border/70 bg-background/60 px-3 py-1.5"
                    >
                      <span className="text-foreground">{item.value}</span>
                      <span className="mx-1.5 text-border">/</span>
                      <span>{item.label}</span>
                    </span>
                  ))}
                  {caseStudy.industries.map((industry) => (
                    <span key={industry.slug} className="rounded-full border border-border/70 px-3 py-1.5">
                      {industry.name}
                    </span>
                  ))}
                </div>
              ) : null}
              <div className="mt-8 flex flex-wrap gap-3">
                {caseStudy.project_url ? (
                  <Button asChild size="lg">
                    <a href={caseStudy.project_url} target="_blank" rel="noreferrer">
                      {t("viewLiveProject")}
                      <ArrowUpRight aria-hidden="true" />
                    </a>
                  </Button>
                ) : null}
                {caseStudy.video_url ? (
                  <Button asChild variant="outline" size="lg">
                    <a href={caseStudy.video_url} target="_blank" rel="noreferrer">
                      {t("watchVideo")}
                      <CirclePlay aria-hidden="true" />
                    </a>
                  </Button>
                ) : null}
                <Button asChild variant={caseStudy.project_url ? "outline" : "primary"} size="lg">
                  <Link href="/start-project">{t("startProject")}</Link>
                </Button>
              </div>
              <div className="mt-6 inline-flex max-w-full flex-wrap items-center gap-3 rounded-[var(--radius-lg)] border border-border/70 bg-background/70 px-4 py-3 text-sm text-muted-foreground">
                <span className="font-semibold text-foreground">{t("builtByLabel")}</span>
                {studioCapabilities.map((item) => {
                  const Icon = item.icon;
                  return (
                    <span key={item.label} className="inline-flex items-center gap-2">
                      <Icon aria-hidden="true" className="size-4 text-secondary" />
                      <span>{item.label}</span>
                    </span>
                  );
                })}
              </div>
            </div>

            {caseStudy.cover_image ? (
              <div className="relative overflow-hidden rounded-[calc(var(--radius-lg)+0.25rem)] border border-border/70 bg-[radial-gradient(circle_at_top,#1e293b_0%,#0b1120_55%,#050816_100%)] p-3 sm:p-4">
                <div className="relative aspect-[16/10] overflow-hidden rounded-[var(--radius-lg)] border border-white/10 bg-background/90 shadow-[0_24px_60px_rgba(0,0,0,0.35)]">
                  <Image
                    src={caseStudy.cover_image}
                    alt={caseStudy.cover_image_alt ?? caseStudy.title}
                    fill
                    className="object-contain p-2 sm:p-3"
                    sizes="(min-width: 1024px) 46vw, 100vw"
                    priority
                  />
                </div>
              </div>
            ) : null}
          </div>
        </Container>
      </Section>

      {snapshotItems.length > 0 ? (
        <Section className="border-y border-border/70 bg-background py-8">
          <Container>
            <div className="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
              {snapshotItems.map((item) => (
                <div
                  key={item.label}
                  className="rounded-[var(--radius-md)] border border-border/70 bg-surface px-4 py-3"
                >
                  <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                    {item.label}
                  </p>
                  <p className="mt-1 text-sm font-medium leading-6 text-foreground">{item.value}</p>
                </div>
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      {hasContextSection ? (
        <Section>
          <Container>
            <div className="grid gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(18rem,0.9fr)] lg:gap-10">
              <div className="rounded-[var(--radius-lg)] border border-border/70 bg-surface px-6 py-7 sm:px-8">
                <SectionHeading
                  align="start"
                  badge={t("contextBadge")}
                  title={t("contextTitle")}
                  subtitle={t("contextSubtitle")}
                  className="mb-8"
                />
                <div className="space-y-6">
                  {contextParagraphs.length > 0 ? (
                    <div className="space-y-4">
                      {contextParagraphs.map((paragraph, index) => (
                        <p key={`${paragraph}-${index}`} className="text-pretty text-base leading-8 text-foreground">
                          {paragraph}
                        </p>
                      ))}
                    </div>
                  ) : null}
                  {problemParagraphs.length > 0 ? (
                    <div className="rounded-[var(--radius-md)] border border-border/70 bg-background/80 p-5 sm:p-6">
                      <p className="text-xs font-semibold uppercase tracking-[0.18em] text-secondary">
                        {t("problem")}
                      </p>
                      <div className="mt-3 space-y-4">
                        {problemParagraphs.map((paragraph, index) => (
                          <p key={`${paragraph}-${index}`} className="text-pretty text-base leading-8 text-foreground">
                            {paragraph}
                          </p>
                        ))}
                      </div>
                    </div>
                  ) : null}
                </div>
              </div>

              {constraintPoints.length > 0 ? (
                <aside className="rounded-[var(--radius-lg)] border border-border/70 bg-background px-6 py-7">
                  <p className="text-xs font-semibold uppercase tracking-[0.18em] text-secondary">
                    {t("constraints")}
                  </p>
                  <ul className="mt-5 space-y-3">
                    {constraintPoints.map((point, index) => (
                      <li key={`${point}-${index}`} className="flex gap-3 text-sm leading-7 text-muted-foreground">
                        <span className="mt-2 h-2 w-2 shrink-0 rounded-full bg-secondary" />
                        <span>{point}</span>
                      </li>
                    ))}
                  </ul>
                </aside>
              ) : null}
            </div>
          </Container>
        </Section>
      ) : null}

      {hasEngineeringSection ? (
        <Section className="border-y border-border/70 bg-surface">
          <Container>
            <div className="grid gap-8 lg:grid-cols-[minmax(0,1.1fr)_minmax(18rem,0.9fr)] lg:gap-10">
              <div className="rounded-[var(--radius-lg)] border border-border/70 bg-background px-6 py-7 sm:px-8">
                <SectionHeading
                  align="start"
                  badge={t("engineeringBadge")}
                  title={t("engineeringTitle")}
                  subtitle={t("engineeringSubtitle")}
                  className="mb-8"
                />
                <div className="space-y-4">
                  {solutionParagraphs.map((paragraph, index) => (
                    <p key={`${paragraph}-${index}`} className="text-pretty text-base leading-8 text-foreground">
                      {paragraph}
                    </p>
                  ))}
                </div>
              </div>

              {architecturePoints.length > 0 ? (
                <aside className="rounded-[var(--radius-lg)] border border-border/70 bg-background px-6 py-7">
                  <p className="text-xs font-semibold uppercase tracking-[0.18em] text-secondary">
                    {t("architecture")}
                  </p>
                  <ul className="mt-5 space-y-3">
                    {architecturePoints.map((point, index) => (
                      <li key={`${point}-${index}`} className="flex gap-3 text-sm leading-7 text-muted-foreground">
                        <span className="mt-2 h-2 w-2 shrink-0 rounded-full bg-secondary" />
                        <span>{point}</span>
                      </li>
                    ))}
                  </ul>
                </aside>
              ) : null}
            </div>
          </Container>
        </Section>
      ) : null}

      {hasProofSection ? (
        <Section>
          <Container>
            <SectionHeading
              align="start"
              badge={t("proofBadge")}
              title={t("proofTitle")}
              subtitle={t("proofSubtitle")}
            />
            {caseStudy.video_url ? (
              <div className="mb-6 flex flex-wrap gap-3">
                <Button asChild variant="outline">
                  <a href={caseStudy.video_url} target="_blank" rel="noreferrer">
                    {t("watchVideo")}
                    <CirclePlay aria-hidden="true" />
                  </a>
                </Button>
              </div>
            ) : null}
            {proofImages.length > 0 ? (
              <>
                <div className="grid gap-4 md:auto-rows-fr md:grid-cols-12">
                  {featuredProofImages.map((image, index) => (
                    <a
                      key={`${image}-${index}`}
                      href={image}
                      target="_blank"
                      rel="noreferrer"
                      aria-label={`${t("openImage")} ${index + 1}`}
                      className={cn(
                        "group relative overflow-hidden rounded-[calc(var(--radius-lg)+0.125rem)] border border-border/70 bg-[linear-gradient(180deg,#0f172a_0%,#020617_100%)] p-3 transition-colors hover:border-primary/30",
                        proofLayoutClass(index),
                        proofAspectClass(featuredProofImages.length, index),
                      )}
                    >
                      <div className="relative h-full min-h-[16rem] overflow-hidden rounded-[var(--radius-lg)] border border-white/10 bg-background/95 shadow-[0_20px_40px_rgba(0,0,0,0.28)]">
                        <Image
                          src={image}
                          alt={`${caseStudy.title} ${t("galleryImageAlt")} ${index + 1}`}
                          fill
                          className={cn(
                            "object-contain p-2 transition-transform duration-300 group-hover:scale-[1.01] sm:p-3",
                            featuredProofImages.length === 1 ? "object-top" : "",
                          )}
                          sizes={
                            featuredProofImages.length === 1
                              ? "100vw"
                              : index === 0
                                ? "(min-width: 768px) 58vw, 100vw"
                                : "(min-width: 768px) 38vw, 100vw"
                          }
                        />
                      </div>
                    </a>
                  ))}
                </div>
                {remainingProofImages.length > 0 ? (
                  <div className="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                    {remainingProofImages.map((image, index) => (
                      <a
                        key={`${image}-${index + 4}`}
                        href={image}
                        target="_blank"
                        rel="noreferrer"
                        aria-label={`${t("openImage")} ${index + 5}`}
                        className="group relative overflow-hidden rounded-[calc(var(--radius-lg)+0.125rem)] border border-border/70 bg-[linear-gradient(180deg,#0f172a_0%,#020617_100%)] p-3 transition-colors hover:border-primary/30"
                      >
                        <div className="relative aspect-[16/11] overflow-hidden rounded-[var(--radius-lg)] border border-white/10 bg-background/95 shadow-[0_20px_40px_rgba(0,0,0,0.28)]">
                          <Image
                            src={image}
                            alt={`${caseStudy.title} ${t("galleryImageAlt")} ${index + 5}`}
                            fill
                            className="object-contain p-2 transition-transform duration-300 group-hover:scale-[1.01] sm:p-3"
                            sizes="(min-width: 1280px) 30vw, (min-width: 768px) 45vw, 100vw"
                          />
                        </div>
                      </a>
                    ))}
                  </div>
                ) : null}
              </>
            ) : null}
          </Container>
        </Section>
      ) : null}

      {capabilityPoints.length > 0 ? (
        <Section className="border-y border-border/70 bg-surface">
          <Container>
            <SectionHeading
              align="start"
              badge={t("capabilitiesBadge")}
              title={t("capabilitiesTitle")}
              subtitle={t("capabilitiesSubtitle")}
            />
            <ol className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
              {capabilityPoints.map((capability, index) => (
                <li
                  key={`${capability}-${index}`}
                  className="rounded-[var(--radius-md)] border border-border/70 bg-background px-5 py-5"
                >
                  <p className="text-xs font-semibold uppercase tracking-[0.18em] text-secondary">
                    {t("capabilityLabel", { number: index + 1 })}
                  </p>
                  <p className="mt-3 text-pretty text-base leading-7 text-foreground">{capability}</p>
                </li>
              ))}
            </ol>
          </Container>
        </Section>
      ) : null}

      {hasOutcomeSection ? (
        <Section>
          <Container>
            <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.95fr)] lg:gap-10">
              {outcomePoints.length > 0 ? (
                <div className="rounded-[var(--radius-lg)] border border-border/70 bg-surface px-6 py-7 sm:px-8">
                  <SectionHeading
                    align="start"
                    badge={t("outcomeBadge")}
                    title={t("outcomeTitle")}
                    subtitle={t("outcomeSubtitle")}
                    className="mb-8"
                  />
                  <ul className="space-y-4">
                    {outcomePoints.map((point, index) => (
                      <li key={`${point}-${index}`} className="flex gap-3 text-base leading-8 text-foreground">
                        <span className="mt-3 h-2 w-2 shrink-0 rounded-full bg-secondary" />
                        <span>{point}</span>
                      </li>
                    ))}
                  </ul>
                </div>
              ) : null}

              {evidencePoints.length > 0 ? (
                <div className="rounded-[var(--radius-lg)] border border-border/70 bg-background px-6 py-7">
                  <p className="text-xs font-semibold uppercase tracking-[0.18em] text-secondary">
                    {t("evidence")}
                  </p>
                  <ul className="mt-5 space-y-4">
                    {evidencePoints.map((point, index) => (
                      <li key={`${point}-${index}`} className="text-sm leading-7 text-muted-foreground">
                        {point}
                      </li>
                    ))}
                  </ul>
                </div>
              ) : null}
            </div>
          </Container>
        </Section>
      ) : null}

      {hasRelatedContext ? (
        <Section className="pt-0">
          <Container>
            <div className="rounded-[var(--radius-lg)] border border-border/70 bg-surface px-6 py-7 sm:px-8">
              <SectionHeading
                align="start"
                badge={t("relatedBadge")}
                title={t("relatedTitle")}
                subtitle={t("relatedSubtitle")}
                className="mb-8"
              />
              <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                {links.map((link) => (
                  <Link
                    key={link.href}
                    href={link.href}
                    className="focus-ring rounded-[var(--radius-md)] border border-border/70 bg-background px-4 py-4 text-sm text-foreground transition-colors hover:border-primary/30"
                  >
                    <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                      {link.label}
                    </p>
                    <p className="mt-1 font-medium leading-6">{link.name}</p>
                  </Link>
                ))}
                {caseStudy.industries.map((industry) => (
                  <div
                    key={industry.slug}
                    className="rounded-[var(--radius-md)] border border-border/70 bg-background px-4 py-4"
                  >
                    <p className="text-[11px] font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                      {t("industry")}
                    </p>
                    <p className="mt-1 text-sm font-medium leading-6 text-foreground">{industry.name}</p>
                  </div>
                ))}
              </div>
            </div>
          </Container>
        </Section>
      ) : null}
    </>
  );
}
