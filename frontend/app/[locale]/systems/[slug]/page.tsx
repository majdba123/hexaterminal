import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import {
  ArrowRight,
  ArrowUpRight,
  CheckCircle2,
  Dot,
  Layers3,
  Network,
  Workflow,
} from "lucide-react";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getSystem } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { CaseStudyCard } from "@/components/site/case-study-card";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Link } from "@/i18n/navigation";
import { JsonLd } from "@/components/site/json-ld";
import { breadcrumbJsonLd, softwareApplicationJsonLd } from "@/lib/seo/jsonld";
import { absoluteUrl } from "@/lib/seo/alternates";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { resolveRobots } from "@/lib/seo/indexing";
import { ViewTracker } from "@/components/site/view-tracker";
import { cn } from "@/lib/utils";

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

function galleryLayoutClass(count: number, index: number) {
  if (count === 1) return "md:col-span-12";
  if (count === 2) return index === 0 ? "md:col-span-7" : "md:col-span-5";
  if (index === 0) return "md:col-span-7 md:row-span-2";
  if (index === 1) return "md:col-span-5";
  if (index === 2) return "md:col-span-5";
  return "md:col-span-4";
}

function galleryAspectClass(count: number, index: number) {
  if (count === 1) return "aspect-[16/10]";
  if (count === 2) return index === 0 ? "aspect-[16/11]" : "aspect-[4/3]";
  if (index === 0) return "aspect-[4/3] md:aspect-auto";
  if (index < 3) return "aspect-[16/10]";
  return "aspect-[5/4]";
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

export default async function SystemDetailPage({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}) {
  const { locale, slug } = await params;
  setRequestLocale(locale);

  const [system, t] = await Promise.all([getSystem(locale, slug), getTranslations("systems")]);

  if (!system) notFound();

  const capabilities = splitLines(system.features);
  const businessValue = splitLines(system.business_outcomes);
  const audiencePoints = splitLines(system.target_audience);
  const overviewParagraphs = storyParagraphs(system.full_description);
  const problemParagraphs = storyParagraphs(system.problem);
  const solutionParagraphs = storyParagraphs(system.solution);
  const galleryImages = system.gallery.filter(Boolean);
  const useCases = system.use_cases;
  const hasLiveUrl = Boolean(system.live_url);
  const hasStory = overviewParagraphs.length > 0 || problemParagraphs.length > 0 || solutionParagraphs.length > 0;
  const studioCapabilities = [
    { icon: Layers3, label: t("ownershipStrategy") },
    { icon: Workflow, label: t("ownershipEngineering") },
    { icon: Network, label: t("ownershipBackend") },
  ];
  const heroHighlights = [
    t("heroHighlightOperations"),
    t("heroHighlightSystem"),
    t("heroHighlightDelivery"),
  ];
  const snapshotItems = [
    { label: t("snapshotType"), value: t(`type.${system.type}`) },
    ...(system.category ? [{ label: t("snapshotCategory"), value: system.category }] : []),
    {
      label: hasLiveUrl ? t("snapshotStatus") : t("snapshotDelivery"),
      value: hasLiveUrl ? t("snapshotLive") : t("snapshotCustomBuild"),
    },
  ];

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

      <Section as="div" className="bg-surface pb-10 pt-10 sm:pb-12 sm:pt-14">
        <Container>
          <Breadcrumb items={[{ label: t("title"), href: "/systems" }, { label: system.name }]} />
          <div className="mt-6 grid gap-6 lg:grid-cols-[minmax(0,1.02fr)_minmax(360px,0.98fr)] lg:items-start">
            <div className="max-w-3xl">
              <div className="flex flex-wrap items-center gap-2">
                <Badge>{t(`type.${system.type}`)}</Badge>
                {system.category ? <span className="text-sm text-muted-foreground">{system.category}</span> : null}
              </div>
              <h1 className="mt-4 max-w-3xl text-balance text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
                {system.name}
              </h1>
              {system.tagline ? (
                <p className="mt-4 max-w-2xl text-pretty text-xl leading-relaxed text-foreground/85">
                  {system.tagline}
                </p>
              ) : null}
              {system.short_description ? (
                <p className="mt-4 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground">
                  {system.short_description}
                </p>
              ) : null}
              <ul className="mt-5 grid gap-2.5 text-sm text-foreground sm:grid-cols-3">
                {heroHighlights.map((item) => (
                  <li key={item} className="rounded-full border border-border bg-background px-3 py-2 text-center sm:text-start">
                    {item}
                  </li>
                ))}
              </ul>
              <div className="mt-7 flex flex-wrap gap-3">
                {hasLiveUrl ? (
                  <Button asChild size="lg">
                    <a href={system.live_url ?? "#"} target="_blank" rel="noreferrer">
                      {t("viewLive")}
                      <ArrowUpRight className="size-4" />
                    </a>
                  </Button>
                ) : null}
                <Button asChild size="lg" variant={hasLiveUrl ? "outline" : "primary"}>
                  <Link href="/start-a-project">
                    {t("heroCta")}
                    {!hasLiveUrl ? <ArrowRight className="size-4" /> : null}
                  </Link>
                </Button>
              </div>
              <div className="mt-5 flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
                <span className="font-medium text-foreground">{t("builtByLabel")}</span>
                {studioCapabilities.map(({ label }) => (
                  <span key={label} className="inline-flex items-center gap-2">
                    <Dot className="size-4 text-secondary" />
                    <span>{label}</span>
                  </span>
                ))}
              </div>
            </div>

            {system.cover_image ? (
              <div className="relative isolate">
                <div className="absolute inset-5 rounded-[calc(var(--radius-xl)+0.25rem)] bg-primary/8 blur-3xl" />
                <div className="relative overflow-hidden rounded-[var(--radius-xl)] border border-border/80 bg-background p-3 shadow-[0_24px_80px_rgba(15,23,42,0.12)]">
                  <div className="relative aspect-[4/3] overflow-hidden rounded-[calc(var(--radius-lg)-0.125rem)] border border-border/70 bg-muted">
                    <Image
                      src={system.cover_image}
                      alt={system.cover_image_alt ?? system.name}
                      fill
                      className="object-cover"
                      sizes="(min-width: 1024px) 42vw, 100vw"
                      priority
                    />
                  </div>
                </div>
              </div>
            ) : null}
          </div>
        </Container>
      </Section>

      <Section className="pb-8 pt-0 sm:pb-10">
        <Container>
          <div className="grid gap-3 rounded-[var(--radius-xl)] border border-border bg-background p-4 shadow-sm sm:grid-cols-2 xl:grid-cols-3 lg:p-5">
            {snapshotItems.map((item) => (
              <div key={item.label} className="rounded-[var(--radius-lg)] border border-border/70 bg-surface px-4 py-3">
                <p className="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">{item.label}</p>
                <p className="mt-1.5 text-sm font-medium leading-6 text-foreground">{item.value}</p>
              </div>
            ))}
          </div>
        </Container>
      </Section>

      {hasStory ? (
        <Section className="border-y border-border bg-surface py-10 sm:py-14">
          <Container>
            <div className="grid gap-6 lg:grid-cols-[minmax(0,0.82fr)_minmax(0,1.18fr)] lg:gap-8">
              <div className="max-w-xl lg:sticky lg:top-24 lg:self-start">
                <SectionHeading
                  align="start"
                  className="mb-4 gap-3"
                  badge={t("storyBadge")}
                  title={t("storyTitle")}
                  subtitle={t("storySubtitle")}
                />
                <div className="rounded-[var(--radius-xl)] border border-border bg-background px-5 py-4 shadow-sm">
                  <p className="text-sm leading-7 text-muted-foreground">{t("storyAside")}</p>
                </div>
              </div>
              <div className="rounded-[var(--radius-xl)] border border-border bg-background p-4 shadow-sm sm:p-5">
                {problemParagraphs.length > 0 ? (
                  <article className="rounded-[var(--radius-lg)] border border-border/80 bg-surface px-5 py-5">
                    <p className="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                      {t("challengeBadge")}
                    </p>
                    <h2 className="mt-3 text-xl font-bold tracking-tight text-foreground">{t("challengeTitle")}</h2>
                    <div className="mt-4 space-y-3.5 text-pretty text-base leading-7 text-foreground">
                      {problemParagraphs.map((paragraph, index) => (
                        <p key={`problem-${index}`} className="whitespace-pre-line">
                          {paragraph}
                        </p>
                      ))}
                    </div>
                  </article>
                ) : null}

                {(solutionParagraphs.length > 0 || overviewParagraphs.length > 0) ? (
                  <article className="mt-3 rounded-[var(--radius-lg)] border border-primary/15 bg-primary/5 px-5 py-5">
                    <p className="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">
                      {t("solutionBadge")}
                    </p>
                    <h2 className="mt-3 text-xl font-bold tracking-tight text-foreground">{t("solutionTitle")}</h2>
                    <div className="mt-4 space-y-3.5 text-pretty text-base leading-7 text-foreground">
                      {solutionParagraphs.map((paragraph, index) => (
                        <p key={`solution-${index}`} className="whitespace-pre-line">
                          {paragraph}
                        </p>
                      ))}
                    </div>
                    {overviewParagraphs.length > 0 ? (
                      <div className="mt-5 border-t border-border/70 pt-5">
                        <p className="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                          {t("overviewBadge")}
                        </p>
                        <div className="mt-3 space-y-3.5 text-pretty text-base leading-7 text-foreground">
                          {overviewParagraphs.map((paragraph, index) => (
                            <p key={`overview-${index}`} className="whitespace-pre-line">
                              {paragraph}
                            </p>
                          ))}
                        </div>
                      </div>
                    ) : null}
                  </article>
                ) : null}
              </div>
            </div>
          </Container>
        </Section>
      ) : null}

      {useCases.length > 0 ? (
        <Section className="border-y border-border bg-surface py-10 sm:py-14">
          <Container>
            <SectionHeading
              align="start"
              className="mb-8 gap-3"
              badge={t("useCasesBadge")}
              title={t("useCasesTitle")}
              subtitle={t("useCasesSubtitle")}
            />
            <div className="grid gap-4 xl:grid-cols-2">
              {useCases.map((useCase, index) => {
                const workflowSteps = splitLines(useCase.workflow);

                return (
                  <article
                    key={useCase.slug}
                    className="overflow-hidden rounded-[var(--radius-xl)] border border-border bg-background shadow-sm"
                  >
                    {useCase.image ? (
                      <div className="relative aspect-[16/10] border-b border-border bg-muted">
                        <Image
                          src={useCase.image}
                          alt={useCase.image_alt ?? useCase.title}
                          fill
                          className="object-cover"
                          sizes="(min-width: 1280px) 40vw, (min-width: 768px) 50vw, 100vw"
                        />
                      </div>
                    ) : null}
                    <div className="p-5 sm:p-6">
                      <div className="flex flex-wrap items-center gap-3">
                        <span className="inline-flex size-9 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-secondary">
                          {String(index + 1).padStart(2, "0")}
                        </span>
                        {useCase.actor ? (
                          <span className="rounded-full border border-border bg-surface px-3 py-1 text-xs font-medium text-muted-foreground">
                            {useCase.actor}
                          </span>
                        ) : null}
                      </div>
                      <h2 className="mt-4 text-xl font-bold tracking-tight text-foreground">{useCase.title}</h2>
                      {useCase.summary ? (
                        <p className="mt-3 text-pretty text-sm leading-6 text-muted-foreground">{useCase.summary}</p>
                      ) : null}

                      {workflowSteps.length > 0 ? (
                        <div className="mt-5">
                          <p className="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                            {t("useCasesWorkflowLabel")}
                          </p>
                          <ol className="mt-3 grid gap-2">
                            {workflowSteps.map((step, stepIndex) => (
                              <li key={`${useCase.slug}-${stepIndex}`} className="flex gap-3 text-sm leading-6 text-foreground">
                                <span className="text-secondary">{String(stepIndex + 1).padStart(2, "0")}</span>
                                <span className="text-pretty">{step}</span>
                              </li>
                            ))}
                          </ol>
                        </div>
                      ) : null}

                      {useCase.outcome ? (
                        <div className="mt-5 rounded-[var(--radius-lg)] border border-primary/15 bg-primary/5 px-4 py-3">
                          <p className="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">
                            {t("useCasesOutcomeLabel")}
                          </p>
                          <p className="mt-2 text-pretty text-sm leading-6 text-foreground">{useCase.outcome}</p>
                        </div>
                      ) : null}
                    </div>
                  </article>
                );
              })}
            </div>
          </Container>
        </Section>
      ) : null}

      {galleryImages.length > 0 ? (
        <Section className="py-10 sm:py-14">
          <Container>
            <SectionHeading
              align="start"
              className="mb-8 gap-3"
              badge={t("galleryBadge")}
              title={t("galleryTitle")}
              subtitle={t("gallerySubtitle")}
            />
            <div className="grid gap-4 md:grid-cols-12 md:auto-rows-[minmax(180px,1fr)]">
              {galleryImages.map((image, index) => (
                <figure
                  key={`${image}-${index}`}
                  className={cn(
                    "group relative overflow-hidden rounded-[var(--radius-xl)] border border-border bg-muted shadow-sm",
                    galleryLayoutClass(galleryImages.length, index),
                    galleryAspectClass(galleryImages.length, index),
                  )}
                >
                  <Image
                    src={image}
                    alt={system.cover_image_alt ?? system.name}
                    fill
                    className="object-cover transition-transform duration-500 group-hover:scale-[1.02]"
                    sizes={
                      galleryImages.length === 1
                        ? "100vw"
                        : galleryImages.length === 2
                          ? "(min-width: 768px) 50vw, 100vw"
                          : "(min-width: 768px) 33vw, 100vw"
                    }
                  />
                </figure>
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      {businessValue.length > 0 ? (
        <Section className="border-y border-border bg-surface py-10 sm:py-14">
          <Container>
            <div className={cn("grid gap-4", audiencePoints.length > 0 ? "xl:grid-cols-[minmax(0,1fr)_minmax(0,0.8fr)]" : "")}>
              <div className="rounded-[var(--radius-xl)] border border-border bg-background p-6 shadow-sm">
                <p className="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                  {t("businessValueBadge")}
                </p>
                <h2 className="mt-3 text-2xl font-bold tracking-tight text-foreground">{t("businessValueTitle")}</h2>
                <ul className="mt-5 grid gap-3">
                  {businessValue.map((item, index) => (
                    <li key={`${item}-${index}`} className="flex items-start gap-3">
                      <CheckCircle2 className="mt-0.5 size-5 shrink-0 text-secondary" />
                      <span className="text-pretty text-sm leading-6 text-foreground">{item}</span>
                    </li>
                  ))}
                </ul>
              </div>

              {audiencePoints.length > 0 ? (
                <div className="rounded-[var(--radius-xl)] border border-border bg-background p-6 shadow-sm">
                  <p className="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                    {t("audienceBadge")}
                  </p>
                  <h2 className="mt-3 text-xl font-bold tracking-tight text-foreground">{t("audienceTitle")}</h2>
                  <ul className="mt-5 grid gap-3">
                    {audiencePoints.map((item, index) => (
                      <li key={`${item}-${index}`} className="flex items-start gap-2 text-sm leading-6 text-foreground">
                        <Dot className="mt-0.5 size-5 shrink-0 text-secondary" />
                        <span className="text-pretty">{item}</span>
                      </li>
                    ))}
                  </ul>
                </div>
              ) : null}
            </div>
          </Container>
        </Section>
      ) : null}

      {capabilities.length > 0 ? (
        <Section className="py-10 sm:py-14">
          <Container>
            <SectionHeading
              align="start"
              className="mb-8 gap-3"
              badge={t("capabilitiesBadge")}
              title={t("capabilitiesTitle")}
              subtitle={t("capabilitiesSubtitle")}
            />
            <ol className="grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
              {capabilities.map((capability, index) => (
                <li
                  key={`${capability}-${index}`}
                  className="flex gap-4 rounded-[var(--radius-lg)] border border-border bg-surface p-4 shadow-sm"
                >
                  <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-secondary">
                    {String(index + 1).padStart(2, "0")}
                  </span>
                  <p className="pt-1 text-pretty text-sm font-medium leading-6 text-foreground">{capability}</p>
                </li>
              ))}
            </ol>
          </Container>
        </Section>
      ) : null}

      {businessValue.length === 0 && audiencePoints.length > 0 ? (
        <Section className="border-y border-border bg-surface py-10 sm:py-14">
          <Container narrow>
            <div className="rounded-[var(--radius-xl)] border border-border bg-background p-6 shadow-sm">
              <p className="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                {t("audienceBadge")}
              </p>
              <h2 className="mt-3 text-xl font-bold tracking-tight text-foreground">{t("audienceTitle")}</h2>
              <ul className="mt-5 grid gap-3">
                {audiencePoints.map((item, index) => (
                  <li key={`${item}-${index}`} className="flex items-start gap-2 text-sm leading-6 text-foreground">
                    <Dot className="mt-0.5 size-5 shrink-0 text-secondary" />
                    <span className="text-pretty">{item}</span>
                  </li>
                ))}
              </ul>
            </div>
          </Container>
        </Section>
      ) : null}

      <Section className="py-10 sm:py-14">
        <Container>
          <div className="rounded-[var(--radius-xl)] border border-border bg-surface p-6 shadow-sm sm:p-8">
            <div className="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,0.8fr)] lg:items-end">
              <div className="max-w-2xl">
                <p className="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                  {t("positioningBadge")}
                </p>
                <h2 className="mt-3 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                  {t("positioningTitle")}
                </h2>
                <p className="mt-4 text-pretty text-base leading-7 text-muted-foreground">{t("positioningBody")}</p>
              </div>
              <ul className="grid gap-3 text-sm text-foreground sm:grid-cols-2">
                {[t("positioningPoint1"), t("positioningPoint2"), t("positioningPoint3"), t("positioningPoint4")].map(
                  (item) => (
                    <li
                      key={item}
                      className="rounded-[var(--radius-lg)] border border-border bg-background px-4 py-3 leading-6"
                    >
                      {item}
                    </li>
                  ),
                )}
              </ul>
            </div>
          </div>
        </Container>
      </Section>

      {(system.tech_stack.length > 0 || system.case_studies.length > 0) ? (
        <Section className="border-y border-border bg-surface py-10 sm:py-14">
          <Container>
            {system.case_studies.length > 0 ? (
              <div>
                <SectionHeading
                  align="start"
                  className="mb-8 gap-3"
                  badge={t("relatedWorkBadge")}
                  title={t("relatedWorkTitle")}
                />
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                  {system.case_studies.map((caseStudy) => (
                    <CaseStudyCard key={caseStudy.slug} caseStudy={caseStudy} headingLevel="h3" />
                  ))}
                </div>
              </div>
            ) : null}

            {system.tech_stack.length > 0 ? (
              <div className={cn(system.case_studies.length > 0 ? "mt-8" : "")}>
                <div className="rounded-[var(--radius-xl)] border border-border bg-background p-6 shadow-sm">
                  <p className="text-xs font-semibold uppercase tracking-[0.16em] text-muted-foreground">
                    {t("technologyLabel")}
                  </p>
                  <div className="mt-4 flex flex-wrap gap-2">
                    {system.tech_stack.map((technology) => (
                      <Badge key={technology} variant="outline">
                        {technology}
                      </Badge>
                    ))}
                  </div>
                </div>
              </div>
            ) : null}
          </Container>
        </Section>
      ) : null}

      <section className="pb-12 pt-0 sm:pb-16">
        <Container>
          <div className="rounded-[var(--radius-2xl)] border border-border bg-linear-to-br from-primary/8 via-surface to-background p-6 shadow-sm sm:p-8">
            <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
              <div className="max-w-2xl">
                <p className="text-xs font-semibold uppercase tracking-[0.16em] text-secondary">{t("detailCtaBadge")}</p>
                <h2 className="mt-3 text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
                  {t("detailCtaTitle")}
                </h2>
                <p className="mt-3 text-pretty text-base leading-7 text-muted-foreground">{t("detailCtaSubtitle")}</p>
              </div>
              <div className="flex w-full flex-col gap-3 sm:w-auto sm:flex-row">
                <Button asChild size="lg">
                  <Link href="/start-a-project">{t("detailCtaButton")}</Link>
                </Button>
                <Button asChild size="lg" variant="outline">
                  <Link href="/contact">{t("detailCtaSecondaryButton")}</Link>
                </Button>
              </div>
            </div>
          </div>
        </Container>
      </section>
    </>
  );
}
