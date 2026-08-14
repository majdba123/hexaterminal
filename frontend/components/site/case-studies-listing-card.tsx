import Image from "next/image";
import { ArrowUpRight } from "lucide-react";
import { getTranslations } from "next-intl/server";
import { Link } from "@/i18n/navigation";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardDescription, CardTitle } from "@/components/ui/card";
import type { CaseStudy } from "@/lib/api/types";

/** Full Case Study treatment used by the public listing only. */
export async function CaseStudiesListingCard({ caseStudy }: { caseStudy: CaseStudy }) {
  const t = await getTranslations("caseStudies");
  const context = caseStudy.service?.name ?? caseStudy.industries[0]?.name ?? caseStudy.system?.name;

  return (
    <Link href={`/case-studies/${caseStudy.slug}`} className="focus-ring group block rounded-[var(--radius-lg)]">
      <Card className="flex h-full min-h-[27rem] flex-col overflow-hidden border-border/80 bg-surface transition-colors group-hover:border-primary/40">
        <div className="relative aspect-[16/10] overflow-hidden bg-muted">
          {caseStudy.cover_image ? (
            <Image
              src={caseStudy.cover_image}
              alt={caseStudy.cover_image_alt ?? caseStudy.title}
              fill
              className="object-cover transition-transform duration-300 ease-out group-hover:scale-[1.03]"
              sizes="(min-width: 1280px) 33vw, (min-width: 768px) 50vw, 100vw"
            />
          ) : (
            <div className="flex size-full items-end p-5" aria-hidden="true">
              <span className="text-5xl font-extrabold leading-none text-foreground/15">
                {caseStudy.title.charAt(0)}
              </span>
            </div>
          )}
        </div>
        <CardContent className="flex flex-1 flex-col items-start gap-4 p-6">
          {caseStudy.project_classification ? (
            <Badge variant="outline">
              {t(`classification.${caseStudy.project_classification}`)}
            </Badge>
          ) : null}
          <div className="flex flex-col gap-2">
            <CardTitle as="h2">{caseStudy.title}</CardTitle>
            {caseStudy.summary ? <CardDescription>{caseStudy.summary}</CardDescription> : null}
          </div>
          {context ? <span className="text-sm font-medium text-muted-foreground">{context}</span> : null}
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
