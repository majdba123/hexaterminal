import { getTranslations, setRequestLocale } from "next-intl/server";
import { getHome, getArticles, getFaqs, getIndustries } from "@/lib/api/client";
import { Hero } from "@/components/site/hero";
import { Showreel } from "@/components/site/showreel";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { SectionHeading } from "@/components/site/section-heading";
import { ServiceCard } from "@/components/site/service-card";
import { HomeSystemsShowcase } from "@/components/site/home-systems-showcase";
import { HomeCaseStudiesShowcase } from "@/components/site/home-case-studies-showcase";
import { ArticleCard } from "@/components/site/article-card";
import { CTA } from "@/components/site/cta";
import { Card, CardContent } from "@/components/ui/card";
import { Button } from "@/components/ui/button";
import { Accordion, AccordionItem, AccordionTrigger, AccordionContent } from "@/components/ui/accordion";
import { Link } from "@/i18n/navigation";
import { JsonLd } from "@/components/site/json-ld";
import { faqPageJsonLd } from "@/lib/seo/jsonld";
import { Star } from "lucide-react";
import { ArrowRight } from "lucide-react";
import { cn } from "@/lib/utils";

export default async function HomePage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);

  const [home, articles, faqs, industries, t] = await Promise.all([
    getHome(locale),
    getArticles(locale, 1, 3),
    getFaqs(locale),
    getIndustries(locale),
    getTranslations("home"),
  ]);

  if (!home) {
    return null;
  }

  const steps = [1, 2, 3, 4] as const;

  return (
    <>
      {faqs.length > 0 ? (
        <JsonLd
          data={faqPageJsonLd(faqs.map((faq) => ({ question: faq.question, answer: faq.answer })))}
        />
      ) : null}

      <Hero />

      {home.services.length > 0 ? (
        <Section className="bg-surface">
          <Container>
            <SectionHeading
              badge={t("whatWeBuildBadge")}
              title={t("whatWeBuildTitle")}
            />
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {home.services.slice(0, 6).map((service) => (
                <ServiceCard key={service.slug} service={service} />
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      {home.featured_systems.length > 0 ? (
        <Section className="bg-surface">
          <Container>
            <SectionHeading
              align="start"
              badge={t("featuredSystemsBadge")}
              title={t("featuredSystemsTitle")}
              subtitle={t("featuredSystemsSubtitle")}
            />
            <HomeSystemsShowcase systems={home.featured_systems} />
            <div className="mt-8">
              <Button asChild variant="outline" size="lg">
                <Link href="/systems">
                  {t("featuredSystemsCta")}
                  <ArrowRight className="rtl:rotate-180" aria-hidden="true" />
                </Link>
              </Button>
            </div>
          </Container>
        </Section>
      ) : null}

      <Showreel />

      {home.featured_case_studies.length > 0 ? (
        <Section>
          <Container>
            <SectionHeading
              align="start"
              badge={t("caseStudiesBadge")}
              title={t("caseStudiesTitle")}
              subtitle={t("caseStudiesSubtitle")}
            />
            <HomeCaseStudiesShowcase caseStudies={home.featured_case_studies} />
            <div className="mt-8">
              <Button asChild variant="outline" size="lg">
                <Link href="/case-studies">
                  {t("caseStudiesCta")}
                  <ArrowRight className="rtl:rotate-180" aria-hidden="true" />
                </Link>
              </Button>
            </div>
          </Container>
        </Section>
      ) : null}

      {industries.length > 0 ? (
        <Section className="bg-surface">
          <Container>
            <SectionHeading
              badge={t("industriesBadge")}
              title={t("industriesTitle")}
              subtitle={t("industriesSubtitle")}
            />
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
              {industries.map((industry) => (
                // focus-ring belongs on the Link, not the inner Card: the Link
                // is what receives keyboard focus, so on the Card the
                // :focus-visible rule never matched and these tiles had no
                // visible focus indicator at all. Matches the industries
                // listing page.
                <Link
                  key={industry.slug}
                  href={`/industries/${industry.slug}`}
                  className="focus-ring group block rounded-[var(--radius-lg)]"
                >
                  <Card className="h-full p-6 transition-colors group-hover:border-primary/40">
                    <h3 className="text-base font-bold text-foreground">{industry.name}</h3>
                    {industry.summary ? (
                      <p className="mt-2 text-sm text-muted-foreground">{industry.summary}</p>
                    ) : null}
                  </Card>
                </Link>
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      <Section>
        <Container>
          <SectionHeading
            badge={t("howWeWorkBadge")}
            title={t("howWeWorkTitle")}
            subtitle={t("howWeWorkSubtitle")}
          />
          <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            {steps.map((step) => (
              <div key={step} className="flex flex-col gap-3">
                <span className="flex size-10 items-center justify-center rounded-full bg-primary/10 text-sm font-extrabold text-secondary tabular-nums">
                  {step}
                </span>
                <h3 className="text-base font-bold text-foreground">
                  {t(`howWeWorkStep${step}Title` as "howWeWorkStep1Title")}
                </h3>
                <p className="text-pretty text-sm leading-relaxed text-muted-foreground">
                  {t(`howWeWorkStep${step}Desc` as "howWeWorkStep1Desc")}
                </p>
              </div>
            ))}
          </div>
        </Container>
      </Section>

      {articles.data.length > 0 ? (
        <Section className="bg-surface">
          <Container>
            <SectionHeading
              badge={t("insightsBadge")}
              title={t("insightsTitle")}
              subtitle={t("insightsSubtitle")}
            />
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {articles.data.map((article) => (
                <ArticleCard key={article.slug} article={article} />
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      {home.testimonials.length > 0 ? (
        <Section>
          <Container>
            <SectionHeading
              badge={t("testimonialsBadge")}
              title={t("testimonialsTitle")}
            />
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
              {home.testimonials.map((testimonial, index) => (
                <Card key={index} className="p-6">
                  <CardContent className="flex flex-col gap-4 p-0">
                    <div className="flex gap-0.5 text-warning" aria-label={`${testimonial.rating}/5`}>
                      {Array.from({ length: 5 }).map((_, i) => (
                        <Star
                          key={i}
                          className={cn(
                            "size-4",
                            i < testimonial.rating ? "fill-current" : "opacity-25",
                          )}
                        />
                      ))}
                    </div>
                    <p className="text-sm leading-relaxed text-foreground">
                      &ldquo;{testimonial.content}&rdquo;
                    </p>
                    <div>
                      <p className="text-sm font-semibold text-foreground">
                        {testimonial.author_name}
                      </p>
                      {testimonial.company ? (
                        <p className="text-xs text-muted-foreground">{testimonial.company}</p>
                      ) : null}
                    </div>
                  </CardContent>
                </Card>
              ))}
            </div>
          </Container>
        </Section>
      ) : null}

      {faqs.length > 0 ? (
        <Section className="bg-surface">
          <Container narrow>
            <SectionHeading badge={t("faqBadge")} title={t("faqTitle")} />
            <Accordion type="single" collapsible className="rounded-[var(--radius-lg)] border border-border bg-background px-6">
              {faqs.map((faq, index) => (
                <AccordionItem key={index} value={`faq-${index}`}>
                  <AccordionTrigger>{faq.question}</AccordionTrigger>
                  <AccordionContent>{faq.answer}</AccordionContent>
                </AccordionItem>
              ))}
            </Accordion>
          </Container>
        </Section>
      ) : null}

      <CTA
        title={t("finalCtaTitle")}
        subtitle={t("finalCtaSubtitle")}
        buttonLabel={t("finalCtaButton")}
      />
    </>
  );
}
