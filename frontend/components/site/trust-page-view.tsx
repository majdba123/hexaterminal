import type { Metadata } from "next";
import { notFound } from "next/navigation";
import { getTranslations } from "next-intl/server";
import { getTrustPage } from "@/lib/api/client";
import type { TrustPage } from "@/lib/api/types";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { JsonLd } from "@/components/site/json-ld";
import { breadcrumbJsonLd, faqPageJsonLd } from "@/lib/seo/jsonld";
import { CtaLink } from "@/components/site/cta-link";
import { localeAlternates, absoluteUrl } from "@/lib/seo/alternates";
import { resolveRobots } from "@/lib/seo/indexing";

/**
 * Shared generateMetadata for every Trust Page route. Always resolves to
 * noindex unless the CMS record is both published-ready AND does not
 * override noindex -- see TrustPage::isReadyForPublication on the backend
 * and resolveRobots()'s fail-safe default.
 */
export async function trustPageMetadata(
  locale: string,
  slug: string,
): Promise<Metadata> {
  const page = await getTrustPage(locale, slug);
  if (!page) return { robots: { index: false, follow: false } };

  return {
    title: page.seo?.title ?? page.title,
    description: page.seo?.description ?? page.summary ?? undefined,
    alternates: {
      canonical: page.seo?.canonical_url ?? absoluteUrl(locale, `/${slug}`),
      ...localeAlternates(`/${slug}`),
    },
    robots: resolveRobots(page.seo?.noindex ?? page.noindex),
    openGraph: {
      type: "website",
      title: page.seo?.title ?? page.title,
      images: page.seo?.og_image ?? undefined,
    },
  };
}

/**
 * Shared renderer for every Trust Page (security, process, accessibility,
 * technology, responsible-ai, engineering-standards, ...). Each thin
 * app/[locale]/{slug}/page.tsx just calls this with its fixed slug --
 * content, structure, approvals, and FAQs all come from the CMS-governed
 * TrustPage record. If the page doesn't exist or isn't fully approved (see
 * TrustPage::isReadyForPublication on the backend), the public API 404s
 * and this renders notFound() -- there is no client-side fallback content.
 */
export async function TrustPageView({ locale, slug }: { locale: string; slug: string }) {
  const page = await getTrustPage(locale, slug);
  if (!page) notFound();

  return <TrustPageBody locale={locale} page={page} />;
}

/** Exported so the secure preview route can render a TrustPage record fetched by token, bypassing the by-slug public fetch. */
export async function TrustPageBody({ locale, page }: { locale: string; page: TrustPage }) {
  const t = await getTranslations({ locale, namespace: "trust" });

  return (
    <Section as="div">
      <JsonLd
        data={breadcrumbJsonLd([{ name: page.title, path: `/${page.slug}` }], locale)}
      />
      {page.faqs && page.faqs.length > 0 ? (
        <JsonLd data={faqPageJsonLd(page.faqs)} />
      ) : null}
      <Container narrow>
        <Breadcrumb items={[{ label: page.title }]} />
        <h1 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {page.title}
        </h1>
        {page.summary ? (
          <p className="mt-3 max-w-xl text-pretty text-base leading-relaxed text-muted-foreground">
            {page.summary}
          </p>
        ) : null}

        {page.reviewer || page.reviewed_at ? (
          <p className="mt-4 text-xs text-muted-foreground">
            {page.reviewer ? t("reviewerLabel", { name: page.reviewer }) : null}
            {page.reviewer && page.reviewed_at ? " · " : null}
            {page.reviewed_at
              ? t("reviewedOn", { date: new Date(page.reviewed_at).toLocaleDateString(locale) })
              : null}
          </p>
        ) : null}

        <div className="mt-10 space-y-10">
          {page.sections.map((section) => (
            <div key={section.heading}>
              <h2 className="text-xl font-bold text-foreground">{section.heading}</h2>
              <p className="mt-3 whitespace-pre-line text-base leading-relaxed text-foreground">
                {section.body}
              </p>
            </div>
          ))}
        </div>

        {page.faqs && page.faqs.length > 0 ? (
          <div className="mt-16">
            <h2 className="text-xl font-bold text-foreground">{t("faqTitle")}</h2>
            <div className="mt-6 space-y-6">
              {page.faqs.map((faq) => (
                <div key={faq.question}>
                  <h3 className="font-semibold text-foreground">{faq.question}</h3>
                  <p className="mt-1 text-pretty text-sm leading-relaxed text-muted-foreground">
                    {faq.answer}
                  </p>
                </div>
              ))}
            </div>
          </div>
        ) : null}

        {page.cta && page.cta.length > 0 ? (
          <div className="mt-12">
            <CtaLink href={page.cta[0].url}>{page.cta[0].label}</CtaLink>
          </div>
        ) : null}
      </Container>
    </Section>
  );
}
