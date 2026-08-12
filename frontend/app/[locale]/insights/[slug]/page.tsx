import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getArticle, getArticles } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { routing } from "@/i18n/routing";
import { JsonLd } from "@/components/site/json-ld";
import { breadcrumbJsonLd, articleJsonLd } from "@/lib/seo/jsonld";
import { absoluteUrl } from "@/lib/seo/alternates";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { resolveRobots } from "@/lib/seo/indexing";

export async function generateStaticParams() {
  const params: { locale: string; slug: string }[] = [];
  for (const locale of routing.locales) {
    const { data } = await getArticles(locale, 1, 50);
    for (const article of data) {
      params.push({ locale, slug: article.slug });
    }
  }
  return params;
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}): Promise<Metadata> {
  const { locale, slug } = await params;
  const article = await getArticle(locale, slug);
  if (!article) return {};

  return pageMetadata({
    locale,
    path: `/insights/${slug}`,
    title: article.seo?.title ?? article.title,
    description: article.seo?.description ?? article.excerpt ?? undefined,
    canonical: article.seo?.canonical_url,
    image: article.seo?.og_image ?? article.cover_image,
    robots: resolveRobots(true),
    ogType: "article",
    publishedTime: article.published_at,
    modifiedTime: article.updated_content_at ?? article.published_at,
  });
}

export default async function ArticleDetailPage({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}) {
  const { locale, slug } = await params;
  setRequestLocale(locale);

  const [article, t] = await Promise.all([getArticle(locale, slug), getTranslations("insights")]);
  if (!article) notFound();

  const publishedDate = article.published_at
    ? new Intl.DateTimeFormat(locale, { dateStyle: "long" }).format(new Date(article.published_at))
    : null;

  return (
    <Section as="div">
      <JsonLd
        data={[
          articleJsonLd({
            title: article.title,
            description: article.excerpt,
            url: absoluteUrl(locale, `/insights/${slug}`),
            image: article.seo?.og_image ?? article.cover_image,
            datePublished: article.published_at,
            dateModified: article.updated_content_at,
            authorName: article.author?.name,
          }),
          breadcrumbJsonLd(
            [
              { name: t("title"), path: "/insights" },
              { name: article.title, path: `/insights/${slug}` },
            ],
            locale,
          ),
        ]}
      />
      <Container narrow>
        <Breadcrumb items={[{ label: t("title"), href: "/insights" }, { label: article.title }]} />
        {article.cover_image ? (
          <div className="relative mb-8 aspect-16/9 w-full overflow-hidden rounded-[var(--radius-xl)] border border-border">
            <Image src={article.cover_image} alt={article.cover_image_alt ?? ""} fill className="object-cover" sizes="800px" />
          </div>
        ) : null}
        <h1 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {article.title}
        </h1>
        <div className="mt-3 flex flex-wrap gap-x-3 gap-y-1 text-sm text-muted-foreground">
          {article.author ? <span>{t("by", { author: article.author.name })}</span> : null}
          {publishedDate ? <span>{t("publishedOn", { date: publishedDate })}</span> : null}
        </div>
        {article.excerpt ? (
          <p className="mt-4 text-lg text-muted-foreground">{article.excerpt}</p>
        ) : null}
        {article.body ? (
          <div className="prose-content mt-8 whitespace-pre-line text-base leading-relaxed text-foreground">
            {article.body}
          </div>
        ) : null}
      </Container>
    </Section>
  );
}
