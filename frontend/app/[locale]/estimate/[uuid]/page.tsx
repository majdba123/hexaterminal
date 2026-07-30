import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getEstimate } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { Button } from "@/components/ui/button";
import { Link } from "@/i18n/navigation";
import { EstimateResult } from "@/components/site/estimate-result";

/**
 * Individual estimate result. ALWAYS noindex -- it is per-user, addressed by
 * a high-entropy UUID, and must never enter the sitemap, search, or JSON-LD.
 */
export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "estimator" });
  return {
    title: t("resultTitle"),
    robots: { index: false, follow: false },
  };
}

export default async function EstimateResultPage({
  params,
}: {
  params: Promise<{ locale: string; uuid: string }>;
}) {
  const { locale, uuid } = await params;
  setRequestLocale(locale);
  const t = await getTranslations("estimator");
  const estimate = await getEstimate(locale, uuid);

  return (
    <Section as="div">
      <Container narrow>
        <Breadcrumb items={[{ label: t("title"), href: "/estimate" }, { label: t("resultTitle") }]} />

        {estimate ? (
          <EstimateResult locale={locale} estimate={estimate} />
        ) : (
          <div className="mx-auto max-w-lg rounded-[var(--radius-lg)] border border-border bg-muted/40 p-8 text-center">
            <h1 className="text-xl font-bold text-foreground">{t("notFoundTitle")}</h1>
            <p className="mt-2 text-sm text-muted-foreground">{t("notFoundBody")}</p>
            <Button asChild className="mt-6">
              <Link href="/estimate">{t("runNew")}</Link>
            </Button>
          </div>
        )}
      </Container>
    </Section>
  );
}
