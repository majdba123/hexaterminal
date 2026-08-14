import Image from "next/image";
import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import { Card, CardContent, CardTitle, CardDescription } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import type { System } from "@/lib/api/types";

export function SystemCard({
  system,
  headingLevel = "h3",
}: {
  system: System;
  /** Heading level for the card title. Pass "h2" on listing pages where the
   * page h1 is the only heading above the grid, so the document does not skip
   * from h1 to h3. */
  headingLevel?: "h2" | "h3";
}) {
  const t = useTranslations("systems");

  return (
    <Link href={`/systems/${system.slug}`} className="focus-ring group block rounded-[var(--radius-lg)]">
      <Card className="h-full overflow-hidden transition-colors group-hover:border-primary/40">
        <div className="relative aspect-16/9 w-full overflow-hidden bg-muted">
          {system.cover_image ? (
            <Image
              src={system.cover_image}
              alt={system.cover_image_alt ?? system.name}
              fill
              className="object-cover transition-transform duration-200 ease-out group-hover:scale-105"
              sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
            />
          ) : (
            <div aria-hidden="true" className="flex size-full items-center justify-center bg-linear-to-br from-primary/15 to-accent/15 text-4xl font-black text-primary/30">
              {system.name.charAt(0)}
            </div>
          )}
        </div>
        <CardContent className="flex flex-col gap-2">
          <Badge className="w-fit">{t(`type.${system.type}`)}</Badge>
          <CardTitle as={headingLevel}>{system.name}</CardTitle>
          {system.tagline ? <CardDescription>{system.tagline}</CardDescription> : null}
        </CardContent>
      </Card>
    </Link>
  );
}
