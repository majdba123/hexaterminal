import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { LeadForm } from "@/components/site/lead-form";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "contact" });
  return pageMetadata({
    locale,
    path: "/contact",
    title: t("title"),
    description: t("subtitle"),
  });
}

export default async function ContactPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);
  const [t, tStart] = await Promise.all([
    getTranslations("contact"),
    getTranslations("startProject"),
  ]);

  return (
    <Section as="div">
      <Container narrow>
        <Breadcrumb items={[{ label: t("title") }]} />
        <h1 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {t("title")}
        </h1>
        <p className="mt-3 max-w-xl text-pretty text-base leading-relaxed text-muted-foreground">
          {t("subtitle")}
        </p>
        <div className="mt-10">
          <h2 className="mb-4 text-lg font-bold text-foreground">{tStart("title")}</h2>
          <LeadForm locale={locale} sourcePage="/contact" />
        </div>
      </Container>
    </Section>
  );
}
