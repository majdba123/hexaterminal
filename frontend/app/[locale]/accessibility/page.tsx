import type { Metadata } from "next";
import { setRequestLocale } from "next-intl/server";
import { TrustPageView, trustPageMetadata } from "@/components/site/trust-page-view";

const SLUG = "accessibility";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  return trustPageMetadata(locale, SLUG);
}

export default async function AccessibilityTrustPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);
  return <TrustPageView locale={locale} slug={SLUG} />;
}
