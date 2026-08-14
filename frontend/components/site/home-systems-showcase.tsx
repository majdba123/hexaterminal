import Image from "next/image";
import { ArrowUpRight } from "lucide-react";
import { getTranslations } from "next-intl/server";
import { Link } from "@/i18n/navigation";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardDescription, CardTitle } from "@/components/ui/card";
import type { System } from "@/lib/api/types";

/** Homepage-only presentation for featured CMS systems. */
export async function HomeSystemsShowcase({ systems }: { systems: System[] }) {
  const t = await getTranslations("systems");

  return (
    <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      {systems.map((system) => {
        const description = system.tagline ?? system.short_description;

        return (
          <Link
            key={system.slug}
            href={`/systems/${system.slug}`}
            className="focus-ring group block rounded-[var(--radius-lg)]"
          >
            <Card className="flex h-full min-h-[26rem] flex-col overflow-hidden border-border/80 bg-surface transition-colors group-hover:border-primary/40">
              <div className="relative aspect-[16/10] overflow-hidden bg-muted">
                {system.cover_image ? (
                  <Image
                    src={system.cover_image}
                    alt={system.cover_image_alt ?? system.name}
                    fill
                    className="object-cover transition-transform duration-300 ease-out group-hover:scale-[1.03]"
                    sizes="(min-width: 1280px) 33vw, (min-width: 768px) 50vw, 100vw"
                  />
                ) : (
                  <div
                    className="flex size-full items-end justify-between p-5"
                    aria-hidden="true"
                  >
                    <span className="text-5xl font-extrabold leading-none text-foreground/15">
                      {system.name.charAt(0)}
                    </span>
                    <span className="rounded-[var(--radius-md)] border border-border bg-surface px-3 py-1.5 text-xs font-semibold text-muted-foreground">
                      {t(`type.${system.type}`)}
                    </span>
                  </div>
                )}
              </div>

              <CardContent className="flex flex-1 flex-col items-start gap-4 p-6">
                <div className="flex flex-wrap items-center gap-2">
                  <Badge variant="secondary">{t(`type.${system.type}`)}</Badge>
                  {system.category ? (
                    <span className="text-xs font-medium text-muted-foreground">{system.category}</span>
                  ) : null}
                </div>
                <div className="flex flex-col gap-2">
                  <CardTitle>{system.name}</CardTitle>
                  {description ? <CardDescription>{description}</CardDescription> : null}
                </div>
                <span
                  className="mt-auto inline-flex size-10 items-center justify-center rounded-[var(--radius-md)] border border-border text-secondary transition-colors group-hover:border-primary/40 group-hover:bg-primary/10"
                  aria-hidden="true"
                >
                  <ArrowUpRight className="size-4 rtl:rotate-90" />
                </span>
              </CardContent>
            </Card>
          </Link>
        );
      })}
    </div>
  );
}
