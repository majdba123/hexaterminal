import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getPricing } from "@/lib/api/client";
import type { Currency, EngagementModel } from "@/lib/api/types";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { Button } from "@/components/ui/button";
import { Link } from "@/i18n/navigation";
import { Accordion, AccordionItem, AccordionTrigger, AccordionContent } from "@/components/ui/accordion";
import { JsonLd } from "@/components/site/json-ld";
import { faqPageJsonLd } from "@/lib/seo/jsonld";
import { localeAlternates } from "@/lib/seo/alternates";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "pricing" });
  const { engagement_models } = await getPricing(locale);

  return {
    title: t("title"),
    description: t("subtitle"),
    // Indexable only when there is meaningful published content.
    robots: engagement_models.length > 0 ? undefined : { index: false, follow: false },
    alternates: { canonical: `/${locale}/pricing`, ...localeAlternates("/pricing") },
  };
}

function fmt(n: number): string {
  return new Intl.NumberFormat("en-US").format(n);
}

function ModelCard({ model, t }: { model: EngagementModel; t: (k: string) => string }) {
  return (
    <div className="flex flex-col rounded-[var(--radius-xl)] border border-border bg-surface p-6">
      {model.is_featured ? (
        <span className="mb-3 inline-block w-fit rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-secondary">
          {t("featured")}
        </span>
      ) : null}
      <h3 className="text-lg font-bold text-foreground">{model.title}</h3>
      {model.summary ? <p className="mt-2 text-sm text-muted-foreground">{model.summary}</p> : null}

      {model.buyer_fit ? (
        <div className="mt-4">
          <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{t("suitableFor")}</p>
          <p className="mt-1 text-sm text-foreground">{model.buyer_fit}</p>
        </div>
      ) : null}

      {model.deliverables.length > 0 ? (
        <div className="mt-4">
          <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">{t("deliverables")}</p>
          <ul className="mt-1 list-disc space-y-1 ps-5 text-sm text-muted-foreground">
            {model.deliverables.map((d, i) => (
              <li key={i}>{d}</li>
            ))}
          </ul>
        </div>
      ) : null}

      {model.indicative_duration ? (
        <p className="mt-4 text-sm text-muted-foreground">
          <span className="font-semibold text-foreground">{t("duration")}:</span> {model.indicative_duration}
        </p>
      ) : null}

      <div className="mt-6 flex flex-1 flex-col justify-end">
        {model.pricing && model.pricing.min_amount !== null ? (
          <p className="text-xl font-extrabold tabular-nums text-foreground">
            {model.pricing.display_label ? `${model.pricing.display_label} ` : ""}
            {model.pricing.currency} {fmt(model.pricing.min_amount)}
            {model.pricing.max_amount ? `–${fmt(model.pricing.max_amount)}` : ""}
          </p>
        ) : null}
        <Button asChild variant="outline" className="mt-4 w-full">
          <Link href="/estimate">{t("requestScopedEstimate")}</Link>
        </Button>
      </div>
    </div>
  );
}

export default async function PricingPage({
  params,
  searchParams,
}: {
  params: Promise<{ locale: string }>;
  searchParams: Promise<{ currency?: string }>;
}) {
  const { locale } = await params;
  const { currency } = await searchParams;
  setRequestLocale(locale);
  const t = await getTranslations("pricing");

  const selectedCurrency = (["USD", "AED", "SAR"].includes((currency ?? "").toUpperCase())
    ? (currency as string).toUpperCase()
    : "USD") as Currency;
  const data = await getPricing(locale, selectedCurrency);

  const infoSections = [
    { title: t("costDriversTitle"), body: t("costDriversBody") },
    { title: t("paymentTitle"), body: t("paymentBody") },
    { title: t("ownershipTitle"), body: t("ownershipBody") },
    { title: t("supportTitle"), body: t("supportBody") },
  ];

  return (
    <Section as="div">
      <Container>
        {data.faqs.length > 0 ? (
          <JsonLd data={faqPageJsonLd(data.faqs.map((f) => ({ question: f.question, answer: f.answer })))} />
        ) : null}

        <Breadcrumb items={[{ label: t("title") }]} />
        <h1 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">{t("title")}</h1>
        <p className="mt-3 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground">{t("subtitle")}</p>

        {/* Philosophy */}
        <div className="mt-10 rounded-[var(--radius-lg)] border border-border bg-muted/40 p-6">
          <h2 className="text-lg font-bold text-foreground">{t("philosophyTitle")}</h2>
          <p className="mt-2 max-w-2xl text-pretty text-sm leading-relaxed text-muted-foreground">{t("philosophyBody")}</p>
        </div>

        {/* Engagement models */}
        <section className="mt-14">
          <h2 className="text-2xl font-bold text-foreground">{t("modelsTitle")}</h2>
          <p className="mt-2 max-w-2xl text-sm text-muted-foreground">{t("modelsSubtitle")}</p>

          {data.engagement_models.length > 0 ? (
            <div className="mt-8 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              {data.engagement_models.map((model) => (
                <ModelCard key={model.slug} model={model} t={t} />
              ))}
            </div>
          ) : (
            <p className="mt-6 text-sm text-muted-foreground">{t("noModels")}</p>
          )}

          <p className="mt-6 text-sm text-muted-foreground">{t("guidanceNote")}</p>
        </section>

        {/* Info sections */}
        <section className="mt-14 grid gap-6 sm:grid-cols-2">
          {infoSections.map((s) => (
            <div key={s.title} className="rounded-[var(--radius-lg)] border border-border p-6">
              <h3 className="text-base font-bold text-foreground">{s.title}</h3>
              <p className="mt-2 text-pretty text-sm leading-relaxed text-muted-foreground">{s.body}</p>
            </div>
          ))}
        </section>

        {/* Financial FAQ */}
        {data.faqs.length > 0 ? (
          <section className="mt-14">
            <h2 className="text-2xl font-bold text-foreground">{t("faqTitle")}</h2>
            <Accordion type="single" collapsible className="mt-6 rounded-[var(--radius-lg)] border border-border bg-background px-6">
              {data.faqs.map((faq, index) => (
                <AccordionItem key={index} value={`faq-${index}`}>
                  <AccordionTrigger>{faq.question}</AccordionTrigger>
                  <AccordionContent>{faq.answer}</AccordionContent>
                </AccordionItem>
              ))}
            </Accordion>
          </section>
        ) : null}

        {/* Estimator CTA */}
        <section className="mt-14 rounded-[var(--radius-xl)] border border-primary/30 bg-primary/5 p-8 text-center">
          <h2 className="text-xl font-bold text-foreground">{t("estimatorCtaTitle")}</h2>
          <p className="mx-auto mt-2 max-w-xl text-sm text-muted-foreground">{t("estimatorCtaBody")}</p>
          <div className="mt-6 flex flex-wrap justify-center gap-3">
            {data.estimator_available ? (
              <Button asChild size="lg">
                <Link href="/estimate">{t("openEstimator")}</Link>
              </Button>
            ) : null}
            <Button asChild size="lg" variant="outline">
              <Link href="/start-a-project">{t("startProjectCta")}</Link>
            </Button>
          </div>
        </section>
      </Container>
    </Section>
  );
}
