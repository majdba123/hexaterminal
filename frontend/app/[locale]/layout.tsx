import type { Metadata } from "next";
import { NextIntlClientProvider, hasLocale } from "next-intl";
import { getTranslations, setRequestLocale } from "next-intl/server";
import { notFound } from "next/navigation";
import { routing } from "@/i18n/routing";
import { Header } from "@/components/site/header";
import { Footer } from "@/components/site/footer";
import { JsonLd } from "@/components/site/json-ld";
import { AttributionCapture } from "@/components/site/attribution-capture";
import { AnalyticsScript } from "@/components/site/analytics-script";
import { organizationJsonLd, websiteJsonLd } from "@/lib/seo/jsonld";
import { pageMetadata } from "@/lib/seo/page-metadata";
import { resolveRobots } from "@/lib/seo/indexing";
import { SITE_URL } from "@/lib/seo/site";
import { THEME_INIT_SCRIPT } from "@/lib/theme-init-script";
import "../globals.css";

export function generateStaticParams() {
  return routing.locales.map((locale) => ({ locale }));
}

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  const t = await getTranslations({ locale, namespace: "home" });

  const home = pageMetadata({
    locale,
    path: "",
    title: `Hexa Terminal — ${t("heroTitle")}`,
    description: t("heroSubtitle"),
    // Site-wide default robots policy. In a non-indexable environment
    // (staging/preview, or a missing NEXT_PUBLIC_ALLOW_INDEXING) this is
    // noindex,nofollow and is inherited by every child page that does not
    // set its own robots -- the fail-safe against exposing staging.
    robots: resolveRobots(),
  });

  return {
    ...home,
    metadataBase: new URL(SITE_URL),
    // `title` here is a template, not a plain string, so child pages that set
    // only a title still get the " — Hexa Terminal" suffix. pageMetadata's
    // flat title is overridden deliberately.
    title: {
      default: `Hexa Terminal — ${t("heroTitle")}`,
      template: "%s — Hexa Terminal",
    },
    alternates: {
      ...home.alternates,
      types: { "application/rss+xml": `${SITE_URL}/rss.xml?locale=${locale}` },
    },
    // Browser/app icons come from the app/icon.tsx and app/apple-icon.tsx file
    // conventions. The social image is set EXPLICITLY by pageMetadata rather
    // than relying on app/opengraph-image.tsx being picked up by convention:
    // that file sits in the `app` segment while every route lives under
    // `app/[locale]/`, and no og:image was actually being emitted.
  };
}

export default async function LocaleLayout({
  children,
  params,
}: {
  children: React.ReactNode;
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;

  if (!hasLocale(routing.locales, locale)) {
    notFound();
  }

  setRequestLocale(locale);
  const dir = locale === "ar" ? "rtl" : "ltr";

  return (
    <html lang={locale} dir={dir} suppressHydrationWarning>
      <head>
        <script dangerouslySetInnerHTML={{ __html: THEME_INIT_SCRIPT }} />
      </head>
      <body className="antialiased">
        <JsonLd data={[organizationJsonLd(), websiteJsonLd(locale)]} />
        <AttributionCapture />
        <AnalyticsScript />
        <NextIntlClientProvider>
          {/* max(1rem, env(...)): this is the one fixed element pinned to the
              viewport edge, so on a notched phone in landscape a flat 1rem
              offset can put it under the cutout or rounded corner. There is no
              Tailwind token for env(), hence the arbitrary value. */}
          <a
            href="#main-content"
            className="focus-ring sr-only fixed start-[max(1rem,env(safe-area-inset-left))] top-[max(1rem,env(safe-area-inset-top))] z-(--z-toast) rounded-[var(--radius-md)] bg-primary px-4 py-3 text-sm font-semibold text-primary-foreground focus:not-sr-only"
          >
            Skip to content
          </a>
          <Header />
          <main id="main-content">{children}</main>
          <Footer />
        </NextIntlClientProvider>
      </body>
    </html>
  );
}
