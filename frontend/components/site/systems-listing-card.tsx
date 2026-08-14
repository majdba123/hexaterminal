"use client";

import Image from "next/image";
import { ArrowUpRight } from "lucide-react";
import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardDescription, CardTitle } from "@/components/ui/card";
import type { System } from "@/lib/api/types";

/** Full system treatment used by the public Systems listing only. */
export function SystemsListingCard({ system }: { system: System }) {
  const t = useTranslations("systems");
  const description = system.short_description ?? system.tagline;

  return (
    <Link href={`/systems/${system.slug}`} className="focus-ring group block rounded-[var(--radius-lg)]">
      <Card className="flex h-full min-h-[27rem] flex-col overflow-hidden border-border/80 bg-surface transition-colors group-hover:border-primary/40">
        <div className="relative aspect-[16/10] overflow-hidden bg-muted">
          {system.cover_image ? (
            <Image
              src={system.cover_image}
              alt={system.cover_image_alt ?? ""}
              fill
              className="object-cover transition-transform duration-300 ease-out group-hover:scale-[1.03]"
              sizes="(min-width: 1280px) 33vw, (min-width: 768px) 50vw, 100vw"
            />
          ) : (
            <div className="flex size-full items-end p-5" aria-hidden="true">
              <span className="text-5xl font-extrabold leading-none text-foreground/15">
                {system.name.charAt(0)}
              </span>
            </div>
          )}
        </div>
        <CardContent className="flex flex-1 flex-col items-start gap-4 p-6">
          <div className="flex flex-wrap items-center gap-2">
            <Badge variant="outline">{t(`type.${system.type}`)}</Badge>
            {system.category ? <span className="text-sm text-muted-foreground">{system.category}</span> : null}
          </div>
          <div className="flex flex-col gap-2">
            <CardTitle as="h2">{system.name}</CardTitle>
            {description ? <CardDescription>{description}</CardDescription> : null}
          </div>
          <span className="mt-auto inline-flex items-center gap-2 text-sm font-semibold text-secondary">
            {t("exploreCta")}
            <span
              className="inline-flex size-9 items-center justify-center rounded-[var(--radius-md)] border border-border transition-colors group-hover:border-primary/40 group-hover:bg-primary/10"
              aria-hidden="true"
            >
              <ArrowUpRight className="size-4 rtl:rotate-90" />
            </span>
          </span>
        </CardContent>
      </Card>
    </Link>
  );
}
