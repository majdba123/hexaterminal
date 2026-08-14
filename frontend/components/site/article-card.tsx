import Image from "next/image";
import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import { Card, CardContent, CardTitle, CardDescription } from "@/components/ui/card";
import { Badge } from "@/components/ui/badge";
import type { Article } from "@/lib/api/types";

export function ArticleCard({
  article,
  headingLevel = "h3",
}: {
  article: Article;
  /** Heading level for the card title. Pass "h2" on listing pages where the
   * page h1 is the only heading above the grid, so the document does not skip
   * from h1 to h3. */
  headingLevel?: "h2" | "h3";
}) {
  const t = useTranslations("insights");

  return (
    <Link href={`/insights/${article.slug}`} className="focus-ring group block rounded-[var(--radius-lg)]">
      <Card className="h-full overflow-hidden transition-colors group-hover:border-primary/40">
        <div className="relative aspect-16/9 w-full overflow-hidden bg-muted">
          {article.cover_image ? (
            <Image
              src={article.cover_image}
              alt={article.cover_image_alt ?? article.title}
              fill
              className="object-cover transition-transform duration-200 ease-out group-hover:scale-105"
              sizes="(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw"
            />
          ) : (
            <div aria-hidden="true" className="flex size-full items-center justify-center bg-muted text-4xl font-black text-muted-foreground/30">
              {article.title.charAt(0)}
            </div>
          )}
        </div>
        <CardContent className="flex flex-col gap-2">
          <div className="flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
            {article.category ? <Badge variant="outline">{article.category.name}</Badge> : null}
            <span className="tabular-nums">
              {t("readingMinutes", { minutes: article.reading_minutes })}
            </span>
          </div>
          <CardTitle as={headingLevel} className="line-clamp-2">{article.title}</CardTitle>
          {article.excerpt ? (
            <CardDescription className="line-clamp-2">{article.excerpt}</CardDescription>
          ) : null}
        </CardContent>
      </Card>
    </Link>
  );
}
