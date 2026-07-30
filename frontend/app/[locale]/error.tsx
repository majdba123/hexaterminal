"use client";

import { useEffect } from "react";
import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import { Container } from "@/components/site/container";
import { Section } from "@/components/site/section";
import { Button } from "@/components/ui/button";

/**
 * Route-level error boundary for the [locale] segment. Client Component per
 * Next.js requirement. It is wrapped by [locale]/layout.tsx, so Header,
 * Footer, <html dir>, and the next-intl provider are all present.
 *
 * We intentionally render only a generic, translated message. `error.message`
 * from Server Components is already a redacted generic string in production
 * (Next.js strips it to avoid leaking backend details); we never surface it or
 * the digest to the user. The digest is logged for server-side correlation.
 */
export default function Error({
  error,
  unstable_retry,
}: {
  error: Error & { digest?: string };
  unstable_retry: () => void;
}) {
  const t = useTranslations("error");

  useEffect(() => {
    // Client-side breadcrumb for debugging; the authoritative log is server-side.
    console.error("Route error boundary caught:", error.digest ?? error.message);
  }, [error]);

  return (
    <Section>
      <Container narrow>
        <div className="flex flex-col items-center gap-6 py-16 text-center">
          <h1 className="text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
            {t("title")}
          </h1>
          <p className="max-w-md text-pretty text-base leading-relaxed text-muted-foreground">
            {t("description")}
          </p>
          <div className="mt-2 flex flex-wrap items-center justify-center gap-3">
            <Button size="lg" onClick={() => unstable_retry()}>
              {t("retry")}
            </Button>
            <Button asChild size="lg" variant="outline">
              <Link href="/">{t("home")}</Link>
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
