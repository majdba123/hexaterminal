"use client";

import { useTranslations } from "next-intl";
import { DetailSkeleton } from "@/components/site/page-skeleton";

/** Route-level loading UI -- see a listing loading.tsx for why this is a Client Component. */
export default function Loading() {
  const tc = useTranslations("common");
  return <DetailSkeleton label={tc("loading")} />;
}
