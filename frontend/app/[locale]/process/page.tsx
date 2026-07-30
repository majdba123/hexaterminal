import type { Metadata } from "next";
import { setRequestLocale } from "next-intl/server";
import { TrustPageView, trustPageMetadata } from "@/components/site/trust-page-view";

const SLUG = "process";

export async function generateMetadata({
  params,
}: {
  params: Promise<{ locale: string }>;
}): Promise<Metadata> {
  const { locale } = await params;
  return trustPageMetadata(locale, SLUG);
}

export default async function ProcessTrustPage({
  params,
}: {
  params: Promise<{ locale: string }>;
}) {
  const { locale } = await params;
  setRequestLocale(locale);
  return <TrustPageView locale={locale} slug={SLUG} />;
}
