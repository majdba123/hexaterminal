import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getIndustries } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { EmptyState } from "@/components/site/empty-state";
import { Card, CardContent, CardTitle, CardDescription } from "@/components/ui/card";
import { Link } from "@/i18n/navigation";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "industries" });
  return pageMetadata({
    locale,
    path: "/industries",
    title: t("title"),
    description: t("subtitle"),
  });
}

export default async function IndustriesPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);

  const [t, tc, industries] = await Promise.all([
    getTranslations("industries"),
    getTranslations("common"),
    getIndustries(locale),
  ]);

  return (
    <Section as="div">
      <Container>
        <Breadcrumb items={[{ label: t("title") }]} />
        <SectionHeading as="h1" align="start" title={t("title")} subtitle={t("subtitle")} />
        {industries.length > 0 ? (
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {industries.map((industry) => (
              <Link
                key={industry.slug}
                href={`/industries/${industry.slug}`}
                className="focus-ring group block rounded-[var(--radius-lg)]"
              >
                <Card className="h-full p-6 transition-colors group-hover:border-primary/40">
                  <CardContent className="flex flex-col gap-2 p-0">
                    <CardTitle as="h2">{industry.name}</CardTitle>
                    {industry.summary ? (
                      <CardDescription>{industry.summary}</CardDescription>
                    ) : null}
                  </CardContent>
                </Card>
              </Link>
            ))}
          </div>
        ) : (
          <EmptyState
            title={tc("noResults")}
            description={tc("noResultsDesc")}
            action={{ href: "/start-a-project", label: tc("emptyCta") }}
          />
        )}
      </Container>
    </Section>
  );
}
