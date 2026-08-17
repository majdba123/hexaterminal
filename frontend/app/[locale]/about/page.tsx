import type { Metadata } from "next";
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

  return (
    <>
      <Section as="div" className="bg-surface pb-10 pt-10 sm:pb-14 sm:pt-14">
        <Container>
          <Breadcrumb items={[{ label: t("title") }]} />
          <div className="grid gap-8 lg:grid-cols-[minmax(0,1.15fr)_minmax(19rem,0.85fr)] lg:items-start">
            <div className="max-w-3xl">
              <Badge>{t("heroBadge")}</Badge>
              <h1 className="mt-4 text-balance text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
                {t("heroTitle")}
              </h1>
              <p className="mt-4 max-w-2xl text-pretty text-lg leading-relaxed text-muted-foreground">
                {t("heroSubtitle")}
              </p>

              <div className="mt-6 flex flex-wrap gap-2">
                <span className="rounded-full border border-border bg-background px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                  {t("heroCapabilitySystems")}
                </span>
                <span className="rounded-full border border-border bg-background px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                  {t("heroCapabilityPlatforms")}
                </span>
                <span className="rounded-full border border-border bg-background px-3 py-1 text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                  {t("heroCapabilityBackend")}
                </span>
              </div>

              <div className="mt-8 flex flex-wrap gap-3">
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

            <aside className="rounded-[var(--radius-xl)] border border-border/80 bg-background p-6 shadow-sm">
              <p className="text-xs font-semibold uppercase tracking-[0.2em] text-secondary">
                {t("heroAsideLabel")}
              </p>
              <h2 className="mt-3 text-2xl font-bold tracking-tight text-foreground">
                {t("heroAsideTitle")}
              </h2>
              <p className="mt-3 text-pretty text-sm leading-relaxed text-muted-foreground">
                {t("heroAsideBody")}
              </p>
              <ul className="mt-6 space-y-3 border-t border-border/70 pt-5">
                {[1, 2, 3].map((point) => (
                  <li key={point} className="flex gap-3">
                    <span className="mt-1 size-2 rounded-full bg-secondary" aria-hidden="true" />
                    <span className="text-sm leading-relaxed text-foreground/85">
                      {t(`heroAsidePoint${point}` as "heroAsidePoint1")}
                    </span>
                  </li>
                ))}
              </ul>
            </aside>
          </div>
        </Container>
      </Section>

      <Section className="pb-10 pt-0 sm:pb-14 sm:pt-0">
        <Container>
          <div className="grid gap-6 lg:grid-cols-[17rem_minmax(0,1fr)] lg:gap-10">
            <div className="space-y-3">
              <Badge variant="outline">{t("deliveryModelBadge")}</Badge>
              <h2 className="text-balance text-3xl font-extrabold tracking-tight text-foreground">
                {t("deliveryModelTitle")}
              </h2>
              <p className="max-w-sm text-pretty text-sm leading-relaxed text-muted-foreground">
                {t("deliveryModelSubtitle")}
              </p>
            </div>

            <div className="rounded-[var(--radius-xl)] border border-border/80 bg-background p-5 shadow-sm sm:p-6">
              <ol className="grid gap-4 lg:grid-cols-5 lg:gap-3">
                {deliveryModel.map((item, index) => {
                  const Icon = item.icon;
                  return (
                    <li
                      key={item.key}
                      className="flex h-full flex-col gap-3 rounded-[var(--radius-lg)] border border-border/70 bg-surface p-4"
                    >
                      <div className="flex items-center justify-between gap-3">
                        <Icon className="size-5 text-secondary" aria-hidden="true" />
                        <span className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                          0{index + 1}
                        </span>
                      </div>
                      <h3 className="text-base font-bold text-foreground">{item.title}</h3>
                      <p className="text-pretty text-sm leading-relaxed text-muted-foreground">{item.body}</p>
                    </li>
                  );
                })}
              </ol>
            </div>
          </div>
        </Container>
      </Section>

      <Section className="border-y border-border bg-surface py-12 sm:py-16">
        <Container>
          <div className="grid gap-8 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:gap-12">
            <div className="max-w-xl">
              <Badge>{t("positioningBadge")}</Badge>
              <h2 className="mt-4 text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
                {t("positioningTitle")}
              </h2>
            </div>

            <div className="space-y-5">
              <p className="max-w-2xl text-pretty text-base leading-relaxed text-foreground">
                {t("positioningBody")}
              </p>
              <div className="grid gap-3 sm:grid-cols-2">
                {[1, 2, 3, 4].map((point) => (
                  <div
                    key={point}
                    className="rounded-[var(--radius-lg)] border border-border/80 bg-background px-4 py-4 text-sm leading-relaxed text-muted-foreground"
                  >
                    <span className="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-secondary">
                      0{point}
                    </span>
                    {t(`approachPoint${point}` as "approachPoint1")}
                  </div>
                ))}
              </div>
            </div>
          </div>
        </Container>
      </Section>

      <Section className="py-12 sm:py-16">
        <Container>
          <div className="mb-8 flex max-w-3xl flex-col gap-3">
            <Badge variant="outline">{tHome("howWeWorkBadge")}</Badge>
            <h2 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
              {tHome("howWeWorkTitle")}
            </h2>
            <p className="text-pretty text-base leading-relaxed text-muted-foreground">
              {tHome("howWeWorkSubtitle")}
            </p>
          </div>

          <ol className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            {processSteps.map((step, index) => (
              <li
                key={step.key}
                className="rounded-[var(--radius-xl)] border border-border/80 bg-background p-5 shadow-sm"
              >
                <div className="flex items-center gap-3">
                  <span className="flex size-10 items-center justify-center rounded-full bg-primary/10 text-sm font-extrabold text-secondary">
                    0{step.key}
                  </span>
                  {index < processSteps.length - 1 ? (
                    <span className="hidden h-px flex-1 bg-border xl:block" aria-hidden="true" />
                  ) : null}
                </div>
                <h3 className="mt-4 text-lg font-bold text-foreground">{step.title}</h3>
                <p className="mt-2 text-pretty text-sm leading-relaxed text-muted-foreground">
                  {step.body}
                </p>
              </li>
            ))}
          </ol>
        </Container>
      </Section>

      <Section className="border-y border-border bg-surface py-12 sm:py-16">
        <Container>
          <div className="grid gap-8 lg:grid-cols-[18rem_minmax(0,1fr)] lg:gap-10">
            <div className="space-y-3">
              <Badge>{t("trustBadge")}</Badge>
              <h2 className="text-balance text-3xl font-extrabold tracking-tight text-foreground">
                {t("trustTitle")}
              </h2>
              <p className="text-pretty text-sm leading-relaxed text-muted-foreground">
                {t("trustSubtitle")}
              </p>
            </div>

            <div className="rounded-[var(--radius-xl)] border border-border/80 bg-background">
              {principles.map((principle, index) => (
                <div
                  key={principle.key}
                  className={cn(
                    "grid gap-3 px-5 py-5 sm:grid-cols-[14rem_minmax(0,1fr)] sm:gap-6 sm:px-6",
                    index !== principles.length - 1 && "border-b border-border/70",
                  )}
                >
                  <h3 className="text-sm font-semibold uppercase tracking-[0.16em] text-foreground">
                    {principle.title}
                  </h3>
                  <p className="text-pretty text-sm leading-relaxed text-muted-foreground">
                    {principle.body}
                  </p>
                </div>
              ))}
            </div>
          </div>
        </Container>
      </Section>

      {team.length > 0 ? (
        <Section id="team" className="py-12 sm:py-16">
          <Container>
            <div className="mb-8 flex max-w-3xl flex-col gap-3">
              <Badge variant="outline">{t("teamBadge")}</Badge>
              <h2 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
                {t("teamTitle")}
              </h2>
              <p className="text-pretty text-base leading-relaxed text-muted-foreground">
                {t("teamSubtitle")}
              </p>
            </div>

            <div
              className={cn(
                team.length === 1 ? "max-w-xl" : "grid gap-6",
                team.length > 1 ? teamGridClass(team.length) : "",
              )}
            >
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
          </Container>
        </Section>
      ) : null}
    </>
  );
}
