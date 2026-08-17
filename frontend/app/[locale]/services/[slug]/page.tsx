import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import {
  ArrowRight,
  Layers3,
  Network,
  ServerCog,
  Workflow,
  Wrench,
} from "lucide-react";
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
import { cn } from "@/lib/utils";

function splitParagraphs(value: string | null) {
  return value
    ?.split(/\n\s*\n/)
    .map((item) => item.trim())
    .filter(Boolean) ?? [];
}

function splitSentences(value: string | null) {
  return value
    ?.split(/(?<=[.!?])\s+/)
    .map((item) => item.trim())
    .filter(Boolean) ?? [];
}

function descriptionParagraphs(value: string | null) {
  const paragraphs = splitParagraphs(value);
  return paragraphs.length > 0 ? paragraphs : value ? [value.trim()] : [];
}

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
  const descriptionParts = descriptionParagraphs(service.description);
  const introParagraph = descriptionParts[0] ?? null;
  const remainingParagraphs = descriptionParts.slice(1);
  const supportBlocksSource = remainingParagraphs.length > 0
    ? remainingParagraphs
    : splitSentences(service.description).slice(1);
  const supportBlocks = supportBlocksSource
    .slice(0, 3)
    .map((body, index) => ({
      title: t(`contextBlock${index + 1}Title` as "contextBlock1Title"),
      body,
    }));
  const approachSteps = [
    {
      key: "operations",
      icon: Workflow,
      title: t("approachStep1Title"),
      body: t("approachStep1Body"),
    },
    {
      key: "architecture",
      icon: Network,
      title: t("approachStep2Title"),
      body: t("approachStep2Body"),
    },
    {
      key: "product",
      icon: Layers3,
      title: t("approachStep3Title"),
      body: t("approachStep3Body"),
    },
    {
      key: "backend",
      icon: ServerCog,
      title: t("approachStep4Title"),
      body: t("approachStep4Body"),
    },
    {
      key: "maintainability",
      icon: Wrench,
      title: t("approachStep5Title"),
      body: t("approachStep5Body"),
    },
  ] as const;
  const heroHighlights = [t("heroHighlight1"), t("heroHighlight2"), t("heroHighlight3")];

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

      <Section as="div" className="bg-surface pb-10 pt-10 sm:pb-12 sm:pt-14">
        <Container>
          <Breadcrumb items={[{ label: t("title"), href: "/services" }, { label: service.name }]} />
          <div
            className={cn(
              "mt-6 grid gap-8",
              service.cover_image ? "lg:grid-cols-[minmax(0,1.06fr)_minmax(320px,0.94fr)] lg:items-start" : "",
            )}
          >
            <div className="max-w-3xl">
              <div className="flex flex-wrap items-center gap-2">
                <Badge>{t("serviceEyebrow")}</Badge>
                <span className="text-sm text-muted-foreground">{t("heroContext")}</span>
              </div>
              <h1 className="mt-4 max-w-4xl text-balance text-4xl font-bold tracking-tight text-foreground sm:text-5xl">
                {service.name}
              </h1>
              {service.tagline ? (
                <p className="ui-copy mt-4 max-w-3xl text-pretty text-xl leading-relaxed text-foreground/85">
                  {service.tagline}
                </p>
              ) : null}
              {service.summary ? (
                <p className="ui-copy mt-4 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground">
                  {service.summary}
                </p>
              ) : null}
              <ul className="mt-5 grid gap-2.5 text-sm text-foreground sm:grid-cols-3">
                {heroHighlights.map((item) => (
                  <li
                    key={item}
                    className="rounded-full border border-border bg-background px-3 py-2 text-center sm:text-start"
                  >
                    {item}
                  </li>
                ))}
              </ul>
              <div className="mt-7">
                <Button asChild size="lg">
                  <Link href="/start-a-project">
                    {t("heroCta")}
                    <ArrowRight className="size-4 rtl:rotate-180" />
                  </Link>
                </Button>
              </div>
            </div>

            {service.cover_image ? (
              <div className="relative isolate mx-auto w-full max-w-xl lg:mx-0">
                <div className="absolute inset-6 rounded-[calc(var(--radius-xl)+0.25rem)] bg-primary/8 blur-3xl" />
                <div className="relative overflow-hidden rounded-[var(--radius-xl)] border border-border/80 bg-background p-3 shadow-[0_24px_80px_rgba(15,23,42,0.12)]">
                  <div className="relative aspect-[4/3] overflow-hidden rounded-[calc(var(--radius-lg)-0.125rem)] border border-border/70 bg-muted">
                    <Image
                      src={service.cover_image}
                      alt={service.cover_image_alt ?? service.name}
                      fill
                      className="object-contain p-4 sm:p-5"
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

      {service.description ? (
        <Section className="py-10 sm:py-14">
          <Container>
            <div className="grid gap-6 lg:grid-cols-[minmax(0,1.1fr)_minmax(16rem,0.9fr)] lg:gap-8">
              <div className="rounded-[var(--radius-xl)] border border-border bg-background p-5 shadow-sm sm:p-6">
                <SectionHeading
                  align="start"
                  className="mb-6 gap-3"
                  badge={t("contextBadge")}
                  title={t("contextTitle")}
                  subtitle={t("contextIntro")}
                />
                {introParagraph ? (
                  <p className="ui-copy text-pretty text-lg leading-8 text-foreground">{introParagraph}</p>
                ) : null}
                {remainingParagraphs.length > 0 ? (
                  <div className="mt-5 space-y-4 text-pretty text-base leading-7 text-muted-foreground">
                    {remainingParagraphs.map((paragraph, index) => (
                      <p key={`description-${index}`} className="whitespace-pre-line">
                        {paragraph}
                      </p>
                    ))}
                  </div>
                ) : null}
              </div>

              {supportBlocks.length > 0 ? (
                <aside className="grid gap-3 lg:content-start">
                  {supportBlocks.map((block, index) => (
                    <div
                      key={`${block.title}-${index}`}
                      className={cn(
                        "rounded-[var(--radius-lg)] border p-4 shadow-sm",
                        index === 1 ? "border-primary/20 bg-primary/5" : "border-border bg-surface",
                      )}
                    >
                      <p className="ui-kicker text-muted-foreground">{block.title}</p>
                      <p className="mt-3 text-pretty text-sm leading-7 text-foreground/85">{block.body}</p>
                    </div>
                  ))}
                </aside>
              ) : null}
            </div>
          </Container>
        </Section>
      ) : null}

      <Section className="border-y border-border bg-surface py-10 sm:py-14">
        <Container>
          <SectionHeading
            align="start"
            className="mb-8 gap-3"
            badge={t("approachBadge")}
            title={t("approachTitle")}
            subtitle={t("approachIntro")}
          />
          <ol className="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            {approachSteps.map((step, index) => {
              const Icon = step.icon;
              return (
                <li
                  key={step.key}
                  className="flex h-full flex-col gap-3 rounded-[var(--radius-lg)] border border-border bg-background p-4 shadow-sm"
                >
                  <div className="flex items-center justify-between gap-3">
                    <span className="flex size-9 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-secondary">
                      {String(index + 1).padStart(2, "0")}
                    </span>
                    <Icon className="size-5 text-secondary" aria-hidden="true" />
                  </div>
                  <h2 className="text-lg font-bold tracking-tight text-foreground">{step.title}</h2>
                  <p className="text-pretty text-sm leading-7 text-muted-foreground">{step.body}</p>
                </li>
              );
            })}
          </ol>
        </Container>
      </Section>

      {relatedCaseStudies.length > 0 ? (
        <Section className="py-10 sm:py-14">
          <Container>
            <SectionHeading
              align="start"
              className="mb-8 gap-3"
              badge={t("relatedWorkBadge")}
              title={t("relatedWorkTitle")}
              subtitle={t("relatedWorkSubtitle")}
            />
            <div className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
              {relatedCaseStudies.map((caseStudy) => (
                <CaseStudyCard key={caseStudy.slug} caseStudy={caseStudy} headingLevel="h3" />
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      {service.features.length > 0 ? (
        <Section className={cn(relatedCaseStudies.length > 0 ? "pt-0" : "py-10 sm:py-14")}>
          <Container>
            <SectionHeading
              align="start"
              className="mb-8 gap-3"
              badge={t("capabilitiesBadge")}
              title={t("capabilitiesTitle")}
              subtitle={t("capabilitiesIntro")}
            />
            <ol className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
              {service.features.map((feature, index) => (
                <li
                  key={`${feature}-${index}`}
                  className="flex gap-4 rounded-[var(--radius-lg)] border border-border bg-surface px-4 py-4 shadow-sm"
                >
                  <span className="flex size-9 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-secondary">
                    {String(index + 1).padStart(2, "0")}
                  </span>
                  <p className="pt-1 text-pretty text-sm font-medium leading-6 text-foreground">{feature}</p>
                </li>
              ))}
            </ol>
          </Container>
        </Section>
      ) : null}

      {service.tech_stack.length > 0 ? (
        <Section className="border-t border-border bg-surface py-8 sm:py-10">
          <Container>
            <div className="rounded-[var(--radius-xl)] border border-border bg-background p-5 shadow-sm sm:p-6">
              <div className="max-w-2xl">
                <p className="ui-kicker text-muted-foreground">{t("technologyLabel")}</p>
                <h2 className="mt-3 text-xl font-bold tracking-tight text-foreground">{t("technologyTitle")}</h2>
                <p className="ui-copy mt-2 text-pretty text-sm leading-7 text-muted-foreground">
                  {t("technologyIntro")}
                </p>
              </div>
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

      <section className="pb-12 pt-0 sm:pb-16">
        <Container>
          <div className="rounded-[var(--radius-2xl)] border border-border bg-linear-to-br from-primary/8 via-surface to-background p-6 shadow-sm sm:p-8">
            <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
              <div className="max-w-2xl">
                <p className="ui-kicker text-secondary">{t("detailCtaBadge")}</p>
                <h2 className="mt-3 text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
                  {t("detailCtaTitle")}
                </h2>
                <p className="ui-copy mt-3 text-pretty text-base leading-7 text-muted-foreground">
                  {t("detailCtaSubtitle")}
                </p>
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
