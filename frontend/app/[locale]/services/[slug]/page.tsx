import type { Metadata } from "next";
import Image from "next/image";
import { notFound } from "next/navigation";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { getService, getServices } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Link } from "@/i18n/navigation";
import { routing } from "@/i18n/routing";
import { JsonLd } from "@/components/site/json-ld";
import { breadcrumbJsonLd, serviceJsonLd } from "@/lib/seo/jsonld";
import { absoluteUrl } from "@/lib/seo/alternates";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { resolveRobots } from "@/lib/seo/indexing";

export async function generateStaticParams() {
  const params: { locale: string; slug: string }[] = [];
  for (const locale of routing.locales) {
    const { data } = await getServices(locale, 1, 50);
    for (const service of data) {
      params.push({ locale, slug: service.slug });
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
  const service = await getService(locale, slug);
  if (!service) return {};

  return pageMetadata({
    locale,
    path: `/services/${slug}`,
    title: service.seo?.title ?? service.name,
    description: service.seo?.description ?? service.summary ?? service.description ?? undefined,
    canonical: service.seo?.canonical_url,
    image: service.seo?.og_image ?? service.cover_image,
    robots: resolveRobots(service.seo?.noindex),
  });
}

export default async function ServiceDetailPage({
  params,
}: {
  params: Promise<{ locale: string; slug: string }>;
}) {
  const { locale, slug } = await params;
  setRequestLocale(locale);

  const [service, t] = await Promise.all([
    getService(locale, slug),
    getTranslations("services"),
  ]);

  if (!service) notFound();

  return (
    <Section as="div">
      <JsonLd
        data={[
          serviceJsonLd({
            name: service.name,
            description: service.summary ?? service.description,
            url: absoluteUrl(locale, `/services/${slug}`),
          }),
          breadcrumbJsonLd(
            [
              { name: t("title"), path: "/services" },
              { name: service.name, path: `/services/${slug}` },
            ],
            locale,
          ),
        ]}
      />
      <Container narrow>
        <Breadcrumb items={[{ label: t("title"), href: "/services" }, { label: service.name }]} />
        {service.cover_image ? (
          <div className="relative mb-8 aspect-16/9 w-full overflow-hidden rounded-[var(--radius-xl)] border border-border">
            <Image src={service.cover_image} alt={service.cover_image_alt ?? ""} fill className="object-cover" sizes="800px" />
          </div>
        ) : null}
        <h1 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
          {service.name}
        </h1>
        {service.tagline ? (
          <p className="mt-3 text-lg text-muted-foreground">{service.tagline}</p>
        ) : null}
        {service.description ? (
          <div className="prose-content mt-8 whitespace-pre-line text-base leading-relaxed text-foreground">
            {service.description}
          </div>
        ) : null}
        {service.features.length > 0 ? (
          <ul className="mt-8 grid gap-3 sm:grid-cols-2">
            {service.features.map((feature, i) => (
              <li key={i} className="rounded-[var(--radius-md)] border border-border bg-surface px-4 py-3 text-sm text-foreground">
                {feature}
              </li>
            ))}
          </ul>
        ) : null}
        {service.tech_stack.length > 0 ? (
          <div className="mt-8 flex flex-wrap gap-2">
            {service.tech_stack.map((tech) => (
              <Badge key={tech} variant="outline">
                {tech}
              </Badge>
            ))}
          </div>
        ) : null}
        <div className="mt-10">
          <Button asChild size="lg">
            <Link href="/start-a-project">{t("detailCta")}</Link>
          </Button>
        </div>
      </Container>
    </Section>
  );
}
