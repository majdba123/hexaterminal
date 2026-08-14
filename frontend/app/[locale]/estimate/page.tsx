import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getEstimatorConfig } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { Button } from "@/components/ui/button";
import { Link } from "@/i18n/navigation";
import { CostEstimator } from "@/components/site/cost-estimator";
import { pageMetadata } from "@/lib/seo/page-metadata";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "estimator" });
  return pageMetadata({
    locale,
    path: "/estimate",
    title: t("title"),
    description: t("subtitle"),
  });
}

export default async function EstimatePage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);
  const t = await getTranslations("estimator");
  const config = await getEstimatorConfig(locale);

  return (
    <Section as="div">
      <Container narrow>
        <Breadcrumb items={[{ label: t("title") }]} />
        <h1 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">{t("title")}</h1>
        <p className="mt-3 max-w-xl text-pretty text-base leading-relaxed text-muted-foreground">{t("subtitle")}</p>

        <div className="mt-10">
          {config.available && config.questions && config.questions.length > 0 ? (
            <CostEstimator
              locale={locale}
              questions={config.questions}
              currencies={config.currencies ?? ["USD", "AED", "SAR"]}
            />
          ) : (
            <div className="rounded-[var(--radius-lg)] border border-border bg-muted/40 p-6 text-center">
              <p className="text-sm text-muted-foreground">{t("unavailable")}</p>
              <Button asChild className="mt-4">
                <Link href="/start-a-project">{t("requestQuote")}</Link>
              </Button>
            </div>
          )}
        </div>
      </Container>
    </Section>
  );
}
