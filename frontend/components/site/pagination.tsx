import { ChevronLeft, ChevronRight } from "lucide-react";
import { Link } from "@/i18n/navigation";
import { cn } from "@/lib/utils";

export function Pagination({
  currentPage,
  lastPage,
  basePath,
  extraParams,
}: {
  currentPage: number;
  lastPage: number;
  basePath: string;
  /** Additional query params to preserve across page links, e.g. { category: "foo" }. */
  extraParams?: Record<string, string | undefined>;
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
    <nav aria-label="Pagination" className="mt-12 flex items-center justify-center gap-2">
      <Link
        href={pageHref(Math.max(1, currentPage - 1))}
        aria-disabled={currentPage <= 1}
        className={cn(
          "focus-ring flex size-11 items-center justify-center rounded-[var(--radius-md)] border border-border text-foreground hover:bg-muted",
          currentPage <= 1 && "pointer-events-none opacity-40",
        )}
      >
        <ChevronLeft className="rtl:rotate-180 size-4" />
      </Link>
      <span className="px-4 text-sm font-medium tabular-nums text-muted-foreground">
        {currentPage} / {lastPage}
      </span>
      <Link
        href={pageHref(Math.min(lastPage, currentPage + 1))}
        aria-disabled={currentPage >= lastPage}
        className={cn(
          "focus-ring flex size-11 items-center justify-center rounded-[var(--radius-md)] border border-border text-foreground hover:bg-muted",
          currentPage >= lastPage && "pointer-events-none opacity-40",
        )}
      >
        <ChevronRight className="rtl:rotate-180 size-4" />
      </Link>
    </nav>
  );
}
