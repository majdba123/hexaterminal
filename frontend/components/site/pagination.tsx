import { ChevronLeft, ChevronRight } from "lucide-react";
import { Link } from "@/i18n/navigation";

export function Pagination({
  currentPage,
  lastPage,
  basePath,
  extraParams,
  ariaLabel,
  previousLabel,
  nextLabel,
}: {
  currentPage: number;
  lastPage: number;
  basePath: string;
  /** Additional query params to preserve across page links, e.g. { category: "foo" }. */
  extraParams?: Record<string, string | undefined>;
  ariaLabel: string;
  previousLabel: string;
  nextLabel: string;
}) {
  if (lastPage <= 1) return null;

  const pageHref = (page: number) => {
    const params = new URLSearchParams();
    params.set("page", String(page));
    for (const [key, value] of Object.entries(extraParams ?? {})) {
      if (value) params.set(key, value);
    }
    return `${basePath}?${params.toString()}`;
  };

  return (
    <nav aria-label={ariaLabel} className="mt-12 flex items-center justify-center gap-2">
      {currentPage > 1 ? (
        <Link
          href={pageHref(currentPage - 1)}
          aria-label={previousLabel}
          className="focus-ring flex size-11 items-center justify-center rounded-[var(--radius-md)] border border-border text-foreground hover:bg-muted"
        >
          <ChevronLeft className="rtl:rotate-180 size-4" aria-hidden="true" />
        </Link>
      ) : (
        <span
          aria-disabled="true"
          aria-label={previousLabel}
          className="flex size-11 items-center justify-center rounded-[var(--radius-md)] border border-border text-foreground opacity-40"
        >
          <ChevronLeft className="rtl:rotate-180 size-4" aria-hidden="true" />
        </span>
      )}
      <span aria-current="page" className="px-4 text-sm font-medium tabular-nums text-muted-foreground">
        {currentPage} / {lastPage}
      </span>
      {currentPage < lastPage ? (
        <Link
          href={pageHref(currentPage + 1)}
          aria-label={nextLabel}
          className="focus-ring flex size-11 items-center justify-center rounded-[var(--radius-md)] border border-border text-foreground hover:bg-muted"
        >
          <ChevronRight className="rtl:rotate-180 size-4" aria-hidden="true" />
        </Link>
      ) : (
        <span
          aria-disabled="true"
          aria-label={nextLabel}
          className="flex size-11 items-center justify-center rounded-[var(--radius-md)] border border-border text-foreground opacity-40"
        >
          <ChevronRight className="rtl:rotate-180 size-4" aria-hidden="true" />
        </span>
      )}
    </nav>
  );
}
