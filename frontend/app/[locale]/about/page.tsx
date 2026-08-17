import type { Metadata } from "next";
import Image from "next/image";
import { ArrowRight, Layers3, Network, ServerCog, ShieldCheck, Workflow } from "lucide-react";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getTeam } from "@/lib/api/client";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { Badge } from "@/components/ui/badge";
import { Link } from "@/i18n/navigation";
import { cn } from "@/lib/utils";
import { TeamMemberCard } from "@/components/site/team-member-card";
import { teamLayoutMode } from "@/lib/about-layout";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "about" });
  return pageMetadata({
    locale,
    path: "/about",
    title: t("title"),
    description: t("heroSubtitle"),
  });
}

function teamGridClass(count: number) {
  if (count === 2) return "md:grid-cols-2";
  if (count === 3) return "md:grid-cols-2 xl:grid-cols-3";
  return "md:grid-cols-2 xl:grid-cols-4";
}

function aboutH1Class(locale: string) {
  return locale === "ar"
    ? "text-balance text-[2.35rem] font-bold leading-[1.18] tracking-tight text-foreground sm:text-[2.75rem] lg:text-[3.25rem]"
    : "text-balance text-4xl font-bold tracking-tight text-foreground sm:text-5xl lg:text-[3.5rem]";
}

function aboutH2Class(locale: string) {
  return locale === "ar"
    ? "text-balance text-[2rem] font-bold leading-[1.22] tracking-tight text-foreground sm:text-[2.3rem] lg:text-[2.65rem]"
    : "text-balance text-3xl font-bold tracking-tight text-foreground sm:text-4xl";
}

export default async function AboutPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);

  const [t, tHome, tNav, team] = await Promise.all([
    getTranslations("about"),
    getTranslations("home"),
    getTranslations("nav"),
    getTeam(locale),
  ]);

  const deliveryModel = [
    {
      key: "problem",
      icon: Workflow,
      title: t("deliveryModel.problem.title"),
      body: t("deliveryModel.problem.body"),
    },
    {
      key: "architecture",
      icon: Network,
      title: t("deliveryModel.architecture.title"),
      body: t("deliveryModel.architecture.body"),
    },
    {
      key: "experience",
      icon: Layers3,
      title: t("deliveryModel.experience.title"),
      body: t("deliveryModel.experience.body"),
    },
    {
      key: "backend",
      icon: ServerCog,
      title: t("deliveryModel.backend.title"),
      body: t("deliveryModel.backend.body"),
    },
    {
      key: "delivery",
      icon: ShieldCheck,
      title: t("deliveryModel.delivery.title"),
      body: t("deliveryModel.delivery.body"),
    },
  ] as const;

  const processSteps = [1, 2, 3, 4].map((step) => ({
    key: step,
    title: tHome(`howWeWorkStep${step}Title` as "howWeWorkStep1Title"),
    body: tHome(`howWeWorkStep${step}Desc` as "howWeWorkStep1Desc"),
  }));

  const principles = [
    "businessFirst",
    "clearScope",
    "reliableArchitecture",
    "practicalDelivery",
  ].map((key) => ({
    key,
    title: t(`trust.${key}.title`),
    body: t(`trust.${key}.body`),
  }));

  const heroHighlights = [
    t("heroCapabilitySystems"),
    t("heroCapabilityPlatforms"),
    t("heroCapabilityBackend"),
  ];
  const isSingleMember = teamLayoutMode(team.length) === "featured";
  const featuredMember = isSingleMember ? team[0] : null;
  const featuredExpertise = featuredMember?.expertise?.filter(Boolean).slice(0, 3) ?? [];

  return (
    <>
      <Section as="div" className="bg-surface pb-8 pt-10 sm:pb-10 sm:pt-14">
        <Container>
          <Breadcrumb items={[{ label: t("title") }]} />
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1.05fr)_minmax(17rem,0.95fr)] lg:items-center">
            <div className="max-w-3xl" data-testid="about-hero">
              <Badge>{t("heroBadge")}</Badge>
              <h1 className={cn("mt-4 max-w-4xl", aboutH1Class(locale))}>
                {t("heroTitle")}
              </h1>
              <p className="ui-copy mt-4 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground sm:text-lg">
                {t("heroSubtitle")}
              </p>
              <div className="mt-5 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-muted-foreground">
                {heroHighlights.map((item, index) => (
                  <span key={item} className="inline-flex items-center gap-2">
                    {index > 0 ? <span className="hidden size-1 rounded-full bg-border sm:inline-block" aria-hidden="true" /> : null}
                    <span>{item}</span>
                  </span>
                ))}
              </div>

              <div className="mt-7 flex flex-wrap items-center gap-3">
                <Link
                  href="/start-a-project"
                  className="focus-ring inline-flex items-center gap-2 rounded-full bg-primary px-5 py-3 text-sm font-semibold text-primary-foreground transition-transform hover:-translate-y-0.5"
                >
                  {tNav("startProject")}
                  <ArrowRight className="size-4 rtl:rotate-180" aria-hidden="true" />
                </Link>
                <Link
                  href="/about/faq"
                  className="focus-ring inline-flex items-center rounded-full border border-border bg-background px-5 py-3 text-sm font-semibold text-foreground transition-colors hover:border-primary/30 hover:text-secondary"
                >
                  {tNav("faq")}
                </Link>
              </div>
            </div>

            <div className="lg:justify-self-end">
              <div className="max-w-md border-s border-border/80 ps-5 sm:ps-6">
                <p className="ui-kicker text-secondary">{t("heroAsideLabel")}</p>
                <p className="ui-copy mt-3 text-pretty text-base leading-relaxed text-foreground/85 sm:text-lg">
                  {t("heroAsideBody")}
                </p>
              </div>
            </div>
          </div>
        </Container>
      </Section>

      <Section className="pb-10 pt-2 sm:pb-12 sm:pt-4">
        <Container>
          <div className="grid gap-6 lg:grid-cols-[16rem_minmax(0,1fr)] lg:gap-10" data-testid="about-build-think">
            <div className="space-y-3">
              <Badge variant="outline">{t("deliveryModelBadge")}</Badge>
              <h2 className={aboutH2Class(locale)}>
                {t("deliveryModelTitle")}
              </h2>
              <p className="ui-copy max-w-sm text-pretty text-sm leading-relaxed text-muted-foreground sm:text-base">
                {t("deliveryModelSubtitle")}
              </p>
            </div>

            <div className="space-y-6">
              <ol className="grid gap-3 md:grid-cols-2 xl:grid-cols-5 xl:gap-4">
                {deliveryModel.map((item, index) => {
                  const Icon = item.icon;
                  return (
                    <li
                      key={item.key}
                      className="relative border-s border-border/80 ps-4 md:ps-5 xl:min-h-[11rem]"
                    >
                      <div className="flex items-center gap-3">
                        <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-bold text-secondary">
                          0{index + 1}
                        </span>
                        <Icon className="size-4 text-secondary" aria-hidden="true" />
                      </div>
                      <h3 className="mt-4 text-base font-bold text-foreground">{item.title}</h3>
                      <p className="mt-2 text-pretty text-sm leading-relaxed text-muted-foreground">{item.body}</p>
                    </li>
                  );
                })}
              </ol>

              <div className="border-t border-border pt-5">
                <p className="max-w-3xl text-pretty text-base font-medium leading-relaxed text-foreground sm:text-lg">
                  {t("positioningTitle")}
                </p>
                <p className="ui-copy mt-3 max-w-3xl text-pretty text-sm leading-relaxed text-muted-foreground sm:text-base">
                  {t("positioningBody")}
                </p>
              </div>
            </div>
          </div>
        </Container>
      </Section>

      {team.length > 0 ? (
        <Section id="team" className="border-y border-border bg-surface py-12 sm:py-14">
          <Container>
            <div className="mb-8 flex max-w-3xl flex-col gap-3" data-testid="about-team">
              <Badge variant="outline">{t("teamBadge")}</Badge>
              <h2 className={aboutH2Class(locale)}>
                {t("teamTitle")}
              </h2>
              <p className="ui-copy text-pretty text-base leading-relaxed text-muted-foreground">
                {t("teamSubtitle")}
              </p>
            </div>

            {featuredMember ? (
              <article data-testid="team-featured" className="overflow-hidden rounded-[var(--radius-xl)] border border-border/80 bg-background">
                <Link
                  href={`/about/team/${featuredMember.slug}`}
                  className="focus-ring group grid lg:grid-cols-[minmax(16rem,0.38fr)_minmax(0,0.62fr)]"
                >
                  <div className="relative aspect-[4/4.2] overflow-hidden border-b border-border/70 bg-muted lg:aspect-auto lg:min-h-[26rem] lg:border-b-0 lg:border-e">
                    {featuredMember.photo ? (
                      <Image
                        src={featuredMember.photo}
                        alt={featuredMember.photo_alt ?? featuredMember.full_name}
                        fill
                        className="object-cover transition-transform duration-300 group-hover:scale-[1.02]"
                        sizes="(min-width: 1024px) 34vw, 100vw"
                      />
                    ) : (
                      <div className="flex size-full items-end justify-start bg-linear-to-br from-primary/10 via-background to-secondary/10 p-6">
                        <span className="text-7xl font-black leading-none text-foreground/20">
                          {featuredMember.first_name.charAt(0)}
                        </span>
                      </div>
                    )}
                  </div>

                  <div className="flex flex-col p-6 sm:p-7 lg:p-8">
                    <div className="flex flex-wrap items-center gap-2">
                      {featuredMember.is_founder ? <Badge variant="secondary">{t("teamFounder")}</Badge> : null}
                      <span className="ui-kicker text-muted-foreground">{t("teamTitle")}</span>
                    </div>

                    <h3 className="mt-5 text-2xl font-bold tracking-tight text-foreground sm:text-3xl">
                      {featuredMember.full_name}
                    </h3>
                    {featuredMember.position ? (
                      <p className="mt-3 text-base font-medium text-muted-foreground">{featuredMember.position}</p>
                    ) : null}
                    {featuredMember.specialization ? (
                      <p className="ui-copy mt-4 max-w-2xl text-pretty text-base leading-relaxed text-foreground/85">
                        {featuredMember.specialization}
                      </p>
                    ) : null}

                    {featuredExpertise.length > 0 ? (
                      <ul className="mt-6 flex flex-wrap gap-2.5">
                        {featuredExpertise.map((item) => (
                          <li
                            key={item}
                            className="rounded-full border border-border/80 bg-surface px-3 py-1.5 text-xs font-medium text-muted-foreground"
                          >
                            {item}
                          </li>
                        ))}
                      </ul>
                    ) : null}

                    <div className="mt-8 inline-flex items-center gap-2 text-sm font-semibold text-foreground">
                      <span>{t("teamViewProfile")}</span>
                      <ArrowRight className="size-4 rtl:rotate-180" aria-hidden="true" />
                    </div>
                  </div>
                </Link>
              </article>
            ) : (
              <div className={cn("grid gap-6", teamGridClass(team.length))} data-testid="team-grid">
                {team.map((member) => (
                  <TeamMemberCard
                    key={member.slug}
                    member={member}
                    href={`/about/team/${member.slug}`}
                    ctaLabel={t("teamViewProfile")}
                    founderLabel={t("teamFounder")}
                  />
                ))}
              </div>
            )}
          </Container>
        </Section>
      ) : null}

      <Section className="py-12 sm:py-14">
        <Container>
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1fr)_minmax(18rem,0.72fr)] lg:gap-12" data-testid="about-process">
            <div>
              <div className="max-w-3xl">
                <Badge>{t("trustBadge")}</Badge>
                <h2 className={cn("mt-4", aboutH2Class(locale))}>{t("trustTitle")}</h2>
                <p className="ui-copy mt-4 text-pretty text-base leading-relaxed text-muted-foreground">
                  {t("trustSubtitle")}
                </p>
              </div>

              <ol className="mt-8 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                {processSteps.map((step) => (
                  <li key={step.key} className="border-s border-border ps-4 md:ps-5">
                    <span className="ui-kicker text-secondary">0{step.key}</span>
                    <h3 className="mt-3 text-lg font-bold text-foreground">{step.title}</h3>
                    <p className="mt-2 text-pretty text-sm leading-relaxed text-muted-foreground">{step.body}</p>
                  </li>
                ))}
              </ol>
            </div>

            <div className="border-t border-border pt-6 lg:border-t-0 lg:border-s lg:ps-8 lg:pt-1">
              <p className="ui-kicker text-muted-foreground">{t("positioningBadge")}</p>
              <ul className="mt-4 grid gap-4">
                {principles.map((principle) => (
                  <li key={principle.key} className="border-b border-border/70 pb-4 last:border-b-0 last:pb-0">
                    <h3 className="text-sm font-semibold text-foreground">{principle.title}</h3>
                    <p className="mt-2 text-pretty text-sm leading-relaxed text-muted-foreground">{principle.body}</p>
                  </li>
                ))}
              </ul>
            </div>
          </div>
        </Container>
      </Section>
    </>
  );
}
