import { cn } from "@/lib/utils";

export function SectionHeading({
  badge,
  title,
  subtitle,
  align = "center",
  className,
  as: Heading = "h2",
}: {
  badge?: string;
  title: React.ReactNode;
  subtitle?: React.ReactNode;
  align?: "center" | "start";
  className?: string;
  /**
   * Heading level. Defaults to `h2` because this component is normally one
   * section among several on a page. A page whose MAIN heading is this
   * component -- the content list pages, which have no other heading above
   * them -- must pass `as="h1"`, or the document starts its hierarchy at h2
   * with no h1 at all (an a11y and SEO defect).
   */
  as?: "h1" | "h2";
}) {
  return (
    <div
      className={cn(
        "mb-12 flex flex-col gap-4",
        align === "center" ? "items-center text-center" : "items-start text-start",
        className,
      )}
    >
      {badge ? (
        <span className="inline-flex items-center rounded-full border border-primary/20 bg-primary/10 px-3 py-1 text-xs font-semibold uppercase tracking-wider text-secondary">
          {badge}
        </span>
      ) : null}
      <Heading className="max-w-2xl text-balance text-3xl font-extrabold tracking-tight text-foreground sm:text-4xl">
        {title}
      </Heading>
      {subtitle ? (
        <p className="max-w-xl text-pretty text-base leading-relaxed text-muted-foreground">
          {subtitle}
        </p>
      ) : null}
    </div>
  );
}
