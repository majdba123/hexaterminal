import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { JsonLd } from "@/components/site/json-ld";
import { ViewTracker } from "@/components/site/view-tracker";
import { CrmaStory } from "@/components/site/crma/story";
import { CRMA_OG_IMAGE, CRMA_PATH } from "@/components/site/crma/shots";
import { resolveRobots } from "@/lib/seo/indexing";
import { breadcrumbJsonLd } from "@/lib/seo/jsonld";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { SITE_URL } from "@/lib/seo/site";

/**
 * CRMA -- an engineering capability showcase. Static and code-owned (it does
 * not come from the CMS), so it takes precedence over `case-studies/[slug]`.
 * Every claim on it was verified against the CRMA repository; it presents no
 * client, deployment or business result.
 */
export async function generateMetadata({ params }: { params: Promise<{ locale: string }> }): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "crma.meta" });
  return pageMetadata({
    locale,
    path: CRMA_PATH,
    title: t("title"),
    description: t("description"),
    image: `${SITE_URL}${CRMA_OG_IMAGE}`,
    robots: resolveRobots(),
  });
}

export default async function CrmaCaseStudyPage({ params }: { params: Promise<{ locale: string }> }) {
  const { locale } = await params;
  setRequestLocale(locale);
  const [t, tc] = await Promise.all([getTranslations("crma.meta"), getTranslations("caseStudies")]);

  return (
    <>
      <ViewTracker event="case_study_view" slug="crma" />
      <JsonLd
        data={breadcrumbJsonLd(
          [
            { name: tc("title"), path: "/case-studies" },
            { name: t("breadcrumb"), path: CRMA_PATH },
          ],
          locale,
        )}
      />
      <JsonLd
        data={{
          "@context": "https://schema.org",
          "@type": "WebPage",
          name: t("title"),
          description: t("description"),
          url: `${SITE_URL}/${locale}${CRMA_PATH}`,
          inLanguage: locale,
          image: `${SITE_URL}${CRMA_OG_IMAGE}`,
        }}
      />
      <CrmaStory />
    </>
  );
}
