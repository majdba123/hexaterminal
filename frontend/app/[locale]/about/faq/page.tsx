import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getFaqs } from "@/lib/api/client";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from "@/components/ui/accordion";
import { EmptyState } from "@/components/site/empty-state";
import { JsonLd } from "@/components/site/json-ld";
import { breadcrumbJsonLd, faqPageJsonLd } from "@/lib/seo/jsonld";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "about" });
  return pageMetadata({
    locale,
    path: "/about/faq",
    title: t("faqTitle"),
    description: t("faqSubtitle"),
  });
}

export default async function AboutFaqPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);

  const [faqs, tAbout, tNav, tc] = await Promise.all([
    getFaqs(locale),
    getTranslations("about"),
    getTranslations("nav"),
    getTranslations("common"),
  ]);

  return (
    <>
      <JsonLd
        data={[
          breadcrumbJsonLd(
            [
              { name: tNav("about"), path: "/about" },
              { name: tNav("faq"), path: "/about/faq" },
            ],
            locale,
          ),
          ...(faqs.length > 0
            ? [faqPageJsonLd(faqs.map((faq) => ({ question: faq.question, answer: faq.answer })))]
            : []),
        ]}
      />

      <Section as="div" className="bg-surface pb-10 pt-10 sm:pb-12 sm:pt-14">
        <Container narrow>
          <Breadcrumb items={[{ label: tNav("about"), href: "/about" }, { label: tNav("faq") }]} />
          <div className="max-w-3xl">
            <h1 className="text-balance text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
              {tAbout("faqTitle")}
            </h1>
            <p className="mt-4 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground">
              {tAbout("faqSubtitle")}
            </p>
          </div>
        </Container>
      </Section>

      <Section className="pt-0 sm:pt-0">
        <Container narrow>
          {faqs.length > 0 ? (
            <Accordion
              type="single"
              collapsible
              className="rounded-[var(--radius-xl)] border border-border bg-background px-6 shadow-sm"
            >
              {faqs.map((faq, index) => (
                <AccordionItem key={`${faq.question}-${index}`} value={`faq-${index}`}>
                  <AccordionTrigger>{faq.question}</AccordionTrigger>
                  <AccordionContent>{faq.answer}</AccordionContent>
                </AccordionItem>
              ))}
            </Accordion>
          ) : (
            <EmptyState
              title={tAbout("faqEmptyTitle")}
              description={tAbout("faqEmptyDescription")}
              action={{ href: "/start-a-project", label: tc("emptyCta") }}
            />
          )}
        </Container>
      </Section>
    </>
  );
}
