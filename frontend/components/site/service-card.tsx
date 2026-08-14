import Image from "next/image";
import { ArrowRight } from "lucide-react";
import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import { Card, CardContent, CardTitle, CardDescription } from "@/components/ui/card";
import type { Service } from "@/lib/api/types";

export function ServiceCard({
  service,
  headingLevel = "h3",
}: {
  service: Service;
  /** Heading level for the card title. Pass "h2" on listing pages where the
   * page h1 is the only heading above the grid, so the document does not skip
   * from h1 to h3. */
  headingLevel?: "h2" | "h3";
}) {
  const t = useTranslations("common");

  return (
    <Link href={`/services/${service.slug}`} className="focus-ring group block rounded-[var(--radius-lg)]">
      <Card className="h-full overflow-hidden transition-colors group-hover:border-primary/40">
        {service.cover_image ? (
          <div className="relative aspect-16/9 w-full overflow-hidden bg-muted">
            <Image
              src={service.cover_image}
              alt={service.cover_image_alt ?? service.name}
              fill
              className="object-cover transition-transform duration-200 ease-out group-hover:scale-105"
              sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
            />
          </div>
        ) : null}
        <CardContent className="flex flex-col gap-2">
          <CardTitle as={headingLevel}>{service.name}</CardTitle>
          {service.summary || service.description ? (
            <CardDescription className="line-clamp-2">
              {service.summary ?? service.description}
            </CardDescription>
          ) : null}
          {/* The arrow carries a label: on its own it was an affordance with
              no text, which reads as unfinished and gives the eye nothing to
              land on. text-secondary, not text-primary -- see globals.css. */}
          <span className="mt-2 inline-flex items-center gap-1.5 text-sm font-semibold text-secondary">
            {t("learnMore")}
            <ArrowRight className="rtl:rotate-180 size-4 transition-transform group-hover:translate-x-1 rtl:group-hover:-translate-x-1" aria-hidden="true" />
          </span>
        </CardContent>
      </Card>
    </Link>
  );
}
