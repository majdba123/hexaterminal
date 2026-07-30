"use client";

import { useTranslations } from "next-intl";
import { ListingSkeleton } from "@/components/site/page-skeleton";

/**
 * Route-level loading UI.
 *
 * A Client Component on purpose. Next does not pass params to loading.tsx, so
 * there is no locale to hand to next-intl's `setRequestLocale` -- and calling
 * the server-side `getTranslations` without it opts the whole route out of
 * static rendering (DYNAMIC_SERVER_USAGE, which turned the not-found path into
 * a 500). `useTranslations` reads from the NextIntlClientProvider in
 * [locale]/layout.tsx, which already wraps this fallback, so the label is
 * translated with no request-scoped API and no loss of static rendering.
 */
export default function Loading() {
  const tc = useTranslations("common");
  return <ListingSkeleton label={tc("loading")} columns={4} cards={8} />;
}
