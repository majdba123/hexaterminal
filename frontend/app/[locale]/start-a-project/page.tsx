import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { LeadForm } from "@/components/site/lead-form";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "startProject" });
  return pageMetadata({
    locale,
    path: "/start-a-project",
    title: t("title"),
    description: t("heroSubtitle"),
  });
}

export default async function StartProjectPage({
  params,
  searchParams,
}: {
  params: Promise<{ locale: string }>;
  searchParams: Promise<{ lead?: string }>;
}) {
  const { locale } = await params;
  const { lead } = await searchParams;
  setRequestLocale(locale);
  const t = await getTranslations("startProject");
  const nextSteps = [1, 2, 3] as const;

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
            <p className="mt-5 max-w-2xl text-pretty text-lg leading-relaxed text-muted-foreground">{t("heroSubtitle")}</p>
          </div>
        </Container>
      </Section>

      <Section>
        <Container>
          <div className="grid gap-12 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <div>
              {lead === "success" ? (
                <p role="status" className="mb-6 rounded-[var(--radius-md)] border border-success/30 bg-success/10 p-4 text-sm font-medium text-success">
                  {t("nativeSuccess")}
                </p>
              ) : null}
              {lead === "error" ? (
                <p role="alert" className="mb-6 rounded-[var(--radius-md)] border border-destructive/30 bg-destructive/10 p-4 text-sm font-medium text-destructive">
                  {t("nativeError")}
                </p>
              ) : null}
              <LeadForm locale={locale} sourcePage="/start-a-project" />
            </div>
            <aside className="border-s border-border ps-6">
              <SectionHeading align="start" className="mb-6" title={t("nextTitle")} />
              <ol className="flex flex-col gap-5">
                {nextSteps.map((step) => (
                  <li key={step} className="flex gap-3">
                    <span className="flex size-7 shrink-0 items-center justify-center rounded-full bg-primary/10 text-xs font-extrabold text-secondary">
                      {step}
                    </span>
                    <p className="pt-1 text-pretty text-sm leading-relaxed text-muted-foreground">
                      {t(`nextStep${step}` as "nextStep1")}
                    </p>
                  </li>
                ))}
              </ol>
            </aside>
          </div>
        </Container>
      </Section>
    </>
  );
}
