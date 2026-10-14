import Image from "next/image";
import { ArrowRight } from "lucide-react";
import { getTranslations } from "next-intl/server";
import { Link } from "@/i18n/navigation";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { CRMA_PATH, CRMA_SHOTS, crmaSrc } from "./shots";

/**
 * The CRMA teaser: one visual, one sentence, five capability labels, one way
 * in. Shown on the home page and on the work (systems, case studies) and
 * services indexes. It is labelled an engineering capability showcase
 * everywhere -- never client work.
 */
export async function CrmaTeaser({ headingLevel = "h2" }: { headingLevel?: "h2" | "h3" }) {
  const t = await getTranslations("crma.teaser");
  const labels = t.raw("labels") as string[];
  const Heading = headingLevel;

  return (
    <article
      aria-labelledby="crma-teaser-title"
      className="grid items-center gap-8 overflow-hidden rounded-[calc(var(--radius-lg)+0.25rem)] border border-border/70 bg-background p-5 sm:p-8 lg:grid-cols-[minmax(0,0.9fr)_minmax(0,1.1fr)] lg:gap-12"
    >
      <div className="flex flex-col items-start gap-4">
        <Badge>{t("eyebrow")}</Badge>
        <Heading id="crma-teaser-title" className="text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl">
          {t("title")}
        </Heading>
        <p className="text-lg font-semibold text-foreground">{t("subtitle")}</p>
        <p className="max-w-xl text-pretty text-base leading-relaxed text-muted-foreground">{t("body")}</p>
        <ul className="flex flex-wrap gap-2" aria-label={t("labelsLabel")}>
          {labels.map((label) => (
            <li key={label}>
              <Badge variant="outline">{label}</Badge>
            </li>
          ))}
        </ul>
        <Button asChild size="lg" className="mt-2">
          <Link href={CRMA_PATH}>
            {t("cta")}
            <ArrowRight className="rtl:rotate-180" aria-hidden="true" />
          </Link>
        </Button>
      </div>
      <div>
        <Link
          href={CRMA_PATH}
          tabIndex={-1}
          aria-hidden="true"
          className="block overflow-hidden rounded-[var(--radius-lg)] border border-border/70 transition-colors hover:border-secondary/50"
        >
          <Image
            src={crmaSrc("teaser")}
            alt=""
            width={CRMA_SHOTS.teaser.width}
            height={CRMA_SHOTS.teaser.height}
            sizes="(min-width: 1024px) 50vw, 100vw"
            className="block h-auto w-full"
          />
        </Link>
        <p className="mt-3 flex items-center gap-2 text-xs text-muted-foreground">
          <span aria-hidden="true" className="size-1.5 rounded-full bg-warning" />
          {t("disclosure")}
        </p>
      </div>
    </article>
  );
}
