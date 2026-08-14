import Image from "next/image";
import { Link } from "@/i18n/navigation";
import { Card, CardContent, CardTitle, CardDescription } from "@/components/ui/card";
import type { CaseStudy, CaseStudySummary } from "@/lib/api/types";

export function CaseStudyCard({
  caseStudy,
  headingLevel = "h3",
}: {
  caseStudy: CaseStudy | CaseStudySummary;
  /** Heading level for the card title. Pass "h2" on listing pages where the
   * page h1 is the only heading above the grid, so the document does not skip
   * from h1 to h3. */
  headingLevel?: "h2" | "h3";
}) {
  return (
    <Link
      href={`/case-studies/${caseStudy.slug}`}
      className="focus-ring group block rounded-[var(--radius-lg)]"
    >
      <Card className="h-full overflow-hidden transition-colors group-hover:border-primary/40">
        <div className="relative aspect-16/9 w-full overflow-hidden bg-muted">
          {caseStudy.cover_image ? (
            <Image
              src={caseStudy.cover_image}
              alt={caseStudy.cover_image_alt ?? caseStudy.title}
              fill
              className="object-cover transition-transform duration-200 ease-out group-hover:scale-105"
              sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
            />
          ) : (
            <div aria-hidden="true" className="flex size-full items-center justify-center bg-linear-to-br from-secondary/15 to-accent/15 text-4xl font-black text-secondary/30">
              {caseStudy.title.charAt(0)}
            </div>
          )}
        </div>
        <CardContent className="flex flex-col gap-2">
          {caseStudy.client_name ? (
            <span className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              {caseStudy.client_name}
            </span>
          ) : null}
          <CardTitle as={headingLevel}>{caseStudy.title}</CardTitle>
          {caseStudy.summary ? (
            <CardDescription className="line-clamp-2">{caseStudy.summary}</CardDescription>
          ) : null}
        </CardContent>
      </Card>
    </Link>
  );
}
