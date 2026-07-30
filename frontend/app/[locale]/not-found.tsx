"use client";

import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Button } from "@/components/ui/button";

/**
 * Locale-aware 404. Rendered inside [locale]/layout.tsx, so it inherits the
 * correct <html lang/dir>, Header, Footer, and the next-intl provider that
 * backs useTranslations here. Shown for both notFound() from missing API
 * entities (detail pages) and any unmatched path under a valid locale.
 */
export default function NotFound() {
  const t = useTranslations("notFound");

  return (
    <Section>
      <Container narrow>
        <div className="flex flex-col items-center gap-6 py-16 text-center">
          <span className="text-6xl font-extrabold tracking-tight text-primary sm:text-7xl">
            {t("code")}
          </span>
          <h1 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
            {t("title")}
          </h1>
          <p className="max-w-md text-pretty text-base leading-relaxed text-muted-foreground">
            {t("description")}
          </p>
          <div className="mt-2 flex flex-wrap items-center justify-center gap-3">
            <Button asChild size="lg">
              <Link href="/">{t("home")}</Link>
            </Button>
            <Button asChild size="lg" variant="outline">
              <Link href="/systems">{t("systems")}</Link>
            </Button>
            <Button asChild size="lg" variant="ghost">
              <Link href="/contact">{t("contact")}</Link>
            </Button>
          </div>
        </div>
      </Container>
    </Section>
  );
}
