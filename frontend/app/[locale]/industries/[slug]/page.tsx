import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getIndustry, getSystems, getCaseStudies } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { SystemCard } from "@/components/site/system-card";
import { CaseStudyCard } from "@/components/site/case-study-card";
import { JsonLd } from "@/components/site/json-ld";
import { breadcrumbJsonLd } from "@/lib/seo/jsonld";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { resolveRobots } from "@/lib/seo/indexing";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}): Promise<Metadata> {
  const { locale, slug } = await params;
  const industry = await getIndustry(locale, slug);
  if (!industry) return {};

  return pageMetadata({
    locale,
    path: `/industries/${slug}`,
    title: industry.seo?.title ?? industry.name,
    description: industry.seo?.description ?? industry.summary ?? undefined,
    canonical: industry.seo?.canonical_url,
    image: industry.seo?.og_image ?? industry.cover_image,
    robots: resolveRobots(industry.seo?.noindex),
  });
}

export default async function IndustryDetailPage({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}) {
  const { locale, slug } = await params;
  setRequestLocale(locale);

  const [industry, t] = await Promise.all([getIndustry(locale, slug), getTranslations("industries")]);
  if (!industry) notFound();

  const [systems, caseStudies] = await Promise.all([
    getSystems(locale, { perPage: 50 }),
    getCaseStudies(locale, { perPage: 50 }),
  ]);

  const relatedSystems = systems.data.filter((system) =>
    system.industries.some((i) => i.slug === slug),
  );
  const relatedCaseStudies = caseStudies.data.filter((caseStudy) =>
    caseStudy.industries.some((i) => i.slug === slug),
  );

  return (
    <Section as="div">
      <JsonLd
        data={breadcrumbJsonLd(
          [
            { name: t("title"), path: "/industries" },
            { name: industry.name, path: `/industries/${slug}` },
          ],
          locale,
        )}
      />
      <Container narrow>
        <Breadcrumb items={[{ label: t("title"), href: "/industries" }, { label: industry.name }]} />
        {industry.cover_image ? (
          <div className="relative mb-8 aspect-16/9 w-full overflow-hidden rounded-[var(--radius-xl)] border border-border">
            <Image src={industry.cover_image} alt={industry.cover_image_alt ?? industry.name} fill className="object-cover" sizes="800px" />
          </div>
        ) : null}
        <h1 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {industry.name}
        </h1>
        {industry.description ?? industry.summary ? (
          <p className="mt-6 whitespace-pre-line text-base leading-relaxed text-foreground">
            {industry.description ?? industry.summary}
          </p>
        ) : null}
      </Container>

      {relatedSystems.length > 0 ? (
        <Container className="mt-16">
          <h2 className="mb-6 text-2xl font-extrabold text-foreground">
            {t("systemsIn", { industry: industry.name })}
          </h2>
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {relatedSystems.map((system) => (
              <SystemCard key={system.slug} system={system} />
            ))}
          </div>
        </Container>
      ) : null}

      {relatedCaseStudies.length > 0 ? (
        <Container className="mt-16">
          <h2 className="mb-6 text-2xl font-extrabold text-foreground">
            {t("caseStudiesIn", { industry: industry.name })}
          </h2>
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            {relatedCaseStudies.map((caseStudy) => (
              <CaseStudyCard key={caseStudy.slug} caseStudy={caseStudy} />
            ))}
          </div>
        </Container>
      ) : null}
    </Section>
  );
}
