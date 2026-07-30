import { Link } from "@/i18n/navigation";
import { buttonVariants } from "@/components/ui/button";

/**
 * An empty state is a dead end unless it offers a way out, so `action` is the
 * point of this component rather than decoration: each caller hands the
 * visitor exactly one next step. It stays optional only for the case where
 * that next step is already on screen and adjacent -- the search page's "type
 * at least 2 characters" state sits directly under the search field, and a
 * second competing CTA there would be noise.
 *
 * Styled with buttonVariants instead of <Button asChild><Link/></Button>
 * because Button is not ref-forwarding (same pattern as mobile-nav.tsx).
 */
export function EmptyState({
  title,
  description,
  action,
}: {
  title: string;
  description: string;
  action?: { href: string; label: string };
}) {
  return (
    <div className="flex flex-col items-center gap-2 rounded-[var(--radius-lg)] border border-dashed border-border px-5 py-16 text-center">
      <p className="text-balance text-base font-semibold text-foreground">{title}</p>
      <p className="max-w-sm text-pretty text-sm text-muted-foreground">{description}</p>
      {action ? (
        <Link href={action.href} className={buttonVariants({ className: "mt-4" })}>
          {action.label}
        </Link>
      ) : null}
    </div>
  );
}
