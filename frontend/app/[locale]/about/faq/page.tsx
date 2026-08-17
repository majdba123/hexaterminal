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
import { Link } from "@/i18n/navigation";
import { breadcrumbJsonLd, faqPageJsonLd } from "@/lib/seo/jsonld";
import type { Faq } from "@/lib/api/types";
import { Badge } from "@/components/ui/badge";

function categoryId(label: string, index: number) {
  const slug = label
    .toLowerCase()
    .trim()
    .replace(/[^\p{L}\p{N}]+/gu, "-")
    .replace(/^-+|-+$/g, "");

  return slug ? `faq-${slug}` : `faq-group-${index}`;
}

function groupFaqs(faqs: Faq[], generalLabel: string) {
  const groups: { label: string; id: string; items: Faq[] }[] = [];
  const byLabel = new Map<string, { label: string; id: string; items: Faq[] }>();

  for (const faq of faqs) {
    const label = faq.category?.trim() || generalLabel;
    const existing = byLabel.get(label);

    if (existing) {
      existing.items.push(faq);
      continue;
    }

    const group = {
      label,
      id: categoryId(label, groups.length),
      items: [faq],
    };

    byLabel.set(label, group);
    groups.push(group);
  }

  return groups;
}

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

  const groups = groupFaqs(faqs, tAbout("faqGeneralCategory"));
  const visibleFaqs = groups.flatMap((group) => group.items);

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
          ...(visibleFaqs.length > 0
            ? [faqPageJsonLd(visibleFaqs.map((faq) => ({ question: faq.question, answer: faq.answer })))]
            : []),
        ]}
      />

      <Section as="div" className="bg-surface pb-8 pt-10 sm:pb-10 sm:pt-14">
        <Container>
          <Breadcrumb items={[{ label: tNav("about"), href: "/about" }, { label: tNav("faq") }]} />
          <div className="max-w-3xl">
            <Badge>{tAbout("faqHeroBadge")}</Badge>
            <h1 className="mt-4 text-balance text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
              {tAbout("faqTitle")}
            </h1>
            <p className="mt-4 max-w-2xl text-pretty text-base leading-relaxed text-muted-foreground">
              {tAbout("faqSubtitle")}
            </p>
          </div>
        </Container>
      </Section>

      <Section className="pt-0 sm:pt-0">
        <Container>
          {groups.length > 0 ? (
            <div className="grid gap-8 lg:grid-cols-[minmax(12rem,0.28fr)_minmax(0,0.72fr)] lg:gap-10">
              <aside className="space-y-4 lg:sticky lg:top-24 lg:self-start">
                <p className="text-xs font-semibold uppercase tracking-[0.2em] text-muted-foreground">
                  {tAbout("faqBrowseLabel")}
                </p>
                <nav aria-label={tAbout("faqBrowseLabel")} className="-mx-5 overflow-x-auto px-5 lg:mx-0 lg:px-0">
                  <ul className="flex gap-2 lg:flex-col">
                    {groups.map((group) => (
                      <li key={group.id}>
                        <a
                          href={`#${group.id}`}
                          className="focus-ring inline-flex whitespace-nowrap rounded-full border border-border bg-background px-3 py-2 text-sm font-medium text-muted-foreground transition-colors hover:border-primary/30 hover:text-foreground lg:flex lg:w-full lg:justify-between lg:rounded-[var(--radius-lg)] lg:px-4"
                        >
                          <span>{group.label}</span>
                          <span className="hidden text-xs text-muted-foreground/80 lg:inline">
                            {group.items.length}
                          </span>
                        </a>
                      </li>
                    ))}
                  </ul>
                </nav>
              </aside>

              <div className="space-y-8">
                {groups.map((group, groupIndex) => (
                  <section key={group.id} id={group.id} className="scroll-mt-24">
                    <div className="mb-4 flex items-center justify-between gap-3 border-b border-border pb-4">
                      <h2 className="text-2xl font-bold tracking-tight text-foreground">
                        {group.label}
                      </h2>
                      <span className="text-xs font-semibold uppercase tracking-[0.18em] text-muted-foreground">
                        0{groupIndex + 1}
                      </span>
                    </div>

                    <Accordion type="single" collapsible className="space-y-3">
                      {group.items.map((faq, index) => (
                        <AccordionItem
                          key={`${group.id}-${index}`}
                          value={`${group.id}-${index}`}
                          className="rounded-[var(--radius-lg)] border border-border/80 bg-background px-5 shadow-sm"
                        >
                          <AccordionTrigger className="py-4 text-lg font-semibold">
                            {faq.question}
                          </AccordionTrigger>
                          <AccordionContent className="pb-4 text-sm leading-relaxed text-muted-foreground">
                            {faq.answer}
                          </AccordionContent>
                        </AccordionItem>
                      ))}
                    </Accordion>
                  </section>
                ))}

                <div className="rounded-[var(--radius-xl)] border border-border/80 bg-surface px-5 py-4 text-sm text-muted-foreground">
                  <span className="font-semibold text-foreground">{tAbout("faqEndingPrompt")}</span>{" "}
                  <Link href="/contact" className="focus-ring rounded font-semibold text-secondary hover:text-foreground">
                    {tAbout("faqEndingContact")}
                  </Link>{" "}
                  <span>{tAbout("faqEndingDivider")}</span>{" "}
                  <Link
                    href="/start-a-project"
                    className="focus-ring rounded font-semibold text-secondary hover:text-foreground"
                  >
                    {tAbout("faqEndingStart")}
                  </Link>
                </div>
              </div>
            </div>
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
