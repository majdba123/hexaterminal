"use client";

import { useLocale, useTranslations } from "next-intl";
import { usePathname, useRouter } from "@/i18n/navigation";
import { Button } from "@/components/ui/button";

export function LocaleSwitcher() {
  const t = useTranslations("common");
  const locale = useLocale();
  const pathname = usePathname();
  const router = useRouter();

  function toggle() {
    const next = locale === "en" ? "ar" : "en";
    router.replace(pathname, { locale: next });
  }

  return (
    <Button type="button" variant="ghost" size="sm" onClick={toggle} aria-label={t("toggleLanguage")}>
      {locale === "en" ? "AR" : "EN"}
    </Button>
  );
}
