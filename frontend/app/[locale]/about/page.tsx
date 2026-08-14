import type { Metadata } from "next";
import Image from "next/image";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { getTeam } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { CTA } from "@/components/site/cta";
import { Card, CardContent } from "@/components/ui/card";

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

export default async function AboutPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);

  const [t, tHome, team] = await Promise.all([
    getTranslations("about"),
    getTranslations("home"),
    getTeam(locale),
  ]);
  const processSteps = [1, 2, 3, 4] as const;
  const trustPrinciples = ["businessFirst", "clearScope", "reliableArchitecture", "practicalDelivery"] as const;

  return (
    <>
      <Section as="div" className="bg-surface">
        <Container>
          <Breadcrumb items={[{ label: t("title") }]} />
          <div className="max-w-3xl">
            <span className="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-secondary">
              {t("heroBadge")}
            </span>
            <h1 className="mt-5 text-balance text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
              {t("heroTitle")}
            </h1>
            <p className="mt-5 max-w-2xl text-pretty text-lg leading-relaxed text-muted-foreground">
              {t("heroSubtitle")}
            </p>
          </div>
        </Container>
      </Section>

      <Section>
        <Container narrow>
          <SectionHeading align="start" badge={t("positioningBadge")} title={t("positioningTitle")} />
          <p className="prose-content text-pretty text-base leading-relaxed text-foreground">{t("positioningBody")}</p>
        </Container>
      </Section>

      <Section className="border-y border-border bg-surface">
        <Container>
          <SectionHeading
            align="start"
            badge={tHome("howWeWorkBadge")}
            title={tHome("howWeWorkTitle")}
            subtitle={tHome("howWeWorkSubtitle")}
          />
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {processSteps.map((step) => (
              <div key={step} className="flex flex-col gap-3">
                <span className="flex size-10 items-center justify-center rounded-full bg-primary/10 text-sm font-extrabold text-secondary tabular-nums">
                  {step}
                </span>
                <h2 className="text-base font-bold text-foreground">
                  {tHome(`howWeWorkStep${step}Title` as "howWeWorkStep1Title")}
                </h2>
                <p className="text-pretty text-sm leading-relaxed text-muted-foreground">
                  {tHome(`howWeWorkStep${step}Desc` as "howWeWorkStep1Desc")}
                </p>
              </div>
            ))}
          </div>
        </Container>
      </Section>

      <Section>
        <Container>
          <SectionHeading align="start" badge={t("trustBadge")} title={t("trustTitle")} subtitle={t("trustSubtitle")} />
          <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {trustPrinciples.map((principle) => (
              <Card key={principle} className="h-full border-border/80 bg-surface">
                <CardContent className="flex h-full flex-col gap-3 p-6">
                  <h2 className="text-base font-bold text-foreground">{t(`trust.${principle}.title`)}</h2>
                  <p className="text-pretty text-sm leading-relaxed text-muted-foreground">
                    {t(`trust.${principle}.body`)}
                  </p>
                </CardContent>
              </Card>
            ))}
          </div>
        </Container>
      </Section>

      {team.length > 0 ? (
        <Section className="border-y border-border bg-surface">
          <Container>
            <SectionHeading align="start" badge={t("teamBadge")} title={t("teamTitle")} subtitle={t("teamSubtitle")} />
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
              {team.map((member) => (
                <Card key={member.slug} className="h-full overflow-hidden border-border/80">
                  <div className="relative aspect-square w-full overflow-hidden bg-muted">
                    {member.photo ? (
                      <Image
                        src={member.photo}
                        alt={member.photo_alt ?? member.full_name}
                        fill
                        className="object-cover"
                        sizes="(min-width: 1024px) 25vw, 50vw"
                      />
                    ) : (
                      <div className="flex size-full items-end p-5" aria-hidden="true">
                        <span className="text-5xl font-extrabold leading-none text-foreground/15">{member.first_name.charAt(0)}</span>
                      </div>
                    )}
                  </div>
                  <CardContent className="flex flex-col gap-2 p-6">
                    <h2 className="text-base font-bold text-foreground">{member.full_name}</h2>
                    {member.position ? <p className="text-sm text-muted-foreground">{member.position}</p> : null}
                    {member.bio ? (
                      <p className="mt-2 line-clamp-4 text-pretty text-sm leading-relaxed text-muted-foreground">{member.bio}</p>
                    ) : null}
                  </CardContent>
                </Card>
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      <CTA
        eyebrow={t("finalCtaBadge")}
        title={t("finalCtaTitle")}
        subtitle={t("finalCtaSubtitle")}
        buttonLabel={t("finalCtaButton")}
        secondaryButtonLabel={t("finalCtaSecondaryButton")}
      />
    </>
  );
}
