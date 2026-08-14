import Image from "next/image";
import { ArrowUpRight } from "lucide-react";
import { getTranslations } from "next-intl/server";
import { Link } from "@/i18n/navigation";
import { Badge } from "@/components/ui/badge";
import { Card, CardContent, CardDescription, CardTitle } from "@/components/ui/card";
import type { CaseStudy } from "@/lib/api/types";

/** Homepage-only presentation for featured CMS case studies. */
export async function HomeCaseStudiesShowcase({ caseStudies }: { caseStudies: CaseStudy[] }) {
  const t = await getTranslations("home");

  return (
    <div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
      {caseStudies.map((caseStudy) => {
        const industry = caseStudy.industries[0];
        const classification = caseStudy.project_classification
          ? t(`caseStudyClassification.${caseStudy.project_classification}`)
          : null;

        return (
          <Link
            key={caseStudy.slug}
            href={`/case-studies/${caseStudy.slug}`}
            className="focus-ring group block rounded-[var(--radius-lg)]"
          >
            <Card className="flex h-full min-h-[27rem] flex-col overflow-hidden border-border/80 bg-surface transition-colors group-hover:border-secondary/40">
              <div className="relative aspect-[16/10] overflow-hidden bg-muted">
                {caseStudy.cover_image ? (
                  <Image
                    src={caseStudy.cover_image}
                    alt={caseStudy.cover_image_alt ?? ""}
                    fill
                    className="object-cover transition-transform duration-300 ease-out group-hover:scale-[1.03]"
                    sizes="(min-width: 1280px) 33vw, (min-width: 768px) 50vw, 100vw"
                  />
                ) : (
                  <div className="flex size-full items-end justify-between p-5" aria-hidden="true">
                    <span className="text-5xl font-extrabold leading-none text-foreground/15">
                      {caseStudy.title.charAt(0)}
                    </span>
                    {industry ? (
                      <span className="rounded-[var(--radius-md)] border border-border bg-surface px-3 py-1.5 text-xs font-semibold text-muted-foreground">
                        {industry.name}
                      </span>
                    ) : null}
                  </div>
                )}
              </div>

              <CardContent className="flex flex-1 flex-col items-start gap-4 p-6">
                <div className="flex flex-wrap items-center gap-2">
                  {classification ? <Badge variant="secondary">{classification}</Badge> : null}
                  {caseStudy.service ? (
                    <span className="text-xs font-medium text-muted-foreground">{caseStudy.service.name}</span>
                  ) : industry ? (
                    <span className="text-xs font-medium text-muted-foreground">{industry.name}</span>
                  ) : null}
                </div>
                <div className="flex flex-col gap-2">
                  <CardTitle>{caseStudy.title}</CardTitle>
                  {caseStudy.summary ? <CardDescription>{caseStudy.summary}</CardDescription> : null}
                </div>
                <span
                  className="mt-auto inline-flex size-10 items-center justify-center rounded-[var(--radius-md)] border border-border text-secondary transition-colors group-hover:border-secondary/40 group-hover:bg-secondary/10"
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
