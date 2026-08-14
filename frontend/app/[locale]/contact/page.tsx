import type { Metadata } from "next";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { getCompanySettings } from "@/lib/api/client";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Breadcrumb } from "@/components/site/breadcrumb";
import { LeadForm } from "@/components/site/lead-form";

function whatsappHref(value: string): string | null {
  const number = value.replace(/\D/g, "");
  return number ? `https://wa.me/${number}` : null;
}

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
    description: t("heroSubtitle"),
  });
}

export default async function ContactPage({
  params,
  searchParams,
}: {
  params: Promise<{ locale: string }>;
  searchParams: Promise<{ lead?: string }>;
}) {
  const { locale } = await params;
  const { lead } = await searchParams;
  setRequestLocale(locale);
  const [t, settings] = await Promise.all([getTranslations("contact"), getCompanySettings(locale)]);
  const email = settings?.email ?? null;
  const phone = settings?.phone ?? null;
  const whatsapp = settings?.whatsapp ? whatsappHref(settings.whatsapp) : null;

  return (
    <>
      <Section as="div" className="bg-surface">
        <Container>
          <Breadcrumb items={[{ label: t("title") }]} />
          <div className="max-w-3xl">
            <span className="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-secondary">
              {t("heroBadge")}
            </span>
            <h1 className="mt-5 text-balance text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
              {t("heroTitle")}
            </h1>
            <p className="mt-5 max-w-2xl text-pretty text-lg leading-relaxed text-muted-foreground">{t("heroSubtitle")}</p>
          </div>
        </Container>
      </Section>

      <Section>
        <Container>
          <div className="grid gap-12 lg:grid-cols-[minmax(0,1fr)_20rem] lg:items-start">
            <div>
              {lead === "success" ? (
                <p role="status" className="mb-6 rounded-[var(--radius-md)] border border-success/30 bg-success/10 p-4 text-sm font-medium text-success">
                  {t("nativeSuccess")}
                </p>
              ) : null}
              {lead === "error" ? (
                <p role="alert" className="mb-6 rounded-[var(--radius-md)] border border-destructive/30 bg-destructive/10 p-4 text-sm font-medium text-destructive">
                  {t("nativeError")}
                </p>
              ) : null}
              <LeadForm
                locale={locale}
                sourcePage="/contact"
                submitLabel={t("formSubmit")}
                submittingLabel={t("formSubmitting")}
              />
            </div>
            {email || phone || whatsapp ? (
              <aside className="border-s border-border ps-6">
                <h2 className="text-lg font-bold text-foreground">{t("detailsTitle")}</h2>
                <div className="mt-5 flex flex-col items-start gap-3 text-sm">
                  {email ? <a className="focus-ring text-secondary hover:text-foreground" href={`mailto:${email}`}>{email}</a> : null}
                  {phone ? <a className="focus-ring text-secondary hover:text-foreground" href={`tel:${phone}`}>{phone}</a> : null}
                  {whatsapp ? <a className="focus-ring text-secondary hover:text-foreground" href={whatsapp} target="_blank" rel="noopener noreferrer">{t("whatsapp")}</a> : null}
                </div>
              </aside>
            ) : null}
          </div>
        </Container>
      </Section>
    </>
  );
}
