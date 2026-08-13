"use client";

import Image from "next/image";
import { ArrowUpRight } from "lucide-react";
import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import { Card, CardContent, CardDescription, CardTitle } from "@/components/ui/card";
import type { Service } from "@/lib/api/types";

/** Full service-offering treatment used by the public Services listing only. */
export function ServicesListingCard({ service }: { service: Service }) {
  const t = useTranslations("services");
  const description = service.tagline ?? service.summary ?? service.description;

  return (
    <Link href={`/services/${service.slug}`} className="focus-ring group block rounded-[var(--radius-lg)]">
      <Card className="flex h-full min-h-[27rem] flex-col overflow-hidden border-border/80 bg-surface transition-colors group-hover:border-primary/40">
        <div className="relative aspect-[16/10] overflow-hidden bg-muted">
          {service.cover_image ? (
            <Image
              src={service.cover_image}
              alt={service.cover_image_alt ?? ""}
              fill
              className="object-cover transition-transform duration-300 ease-out group-hover:scale-[1.03]"
              sizes="(min-width: 1280px) 33vw, (min-width: 768px) 50vw, 100vw"
            />
          ) : (
            <div className="flex size-full items-end p-5" aria-hidden="true">
              <span className="text-5xl font-extrabold leading-none text-foreground/15">
                {service.name.charAt(0)}
              </span>
            </div>
          )}
        </div>
        <CardContent className="flex flex-1 flex-col items-start gap-4 p-6">
          <div className="flex flex-col gap-2">
            <CardTitle as="h2">{service.name}</CardTitle>
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
