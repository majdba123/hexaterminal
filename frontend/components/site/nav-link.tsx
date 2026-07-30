"use client";

import { Link, usePathname } from "@/i18n/navigation";
import { cn } from "@/lib/utils";

/**
 * Primary-nav item with an active state.
 *
 * `usePathname` from `@/i18n/navigation` returns the path WITHOUT the locale
 * prefix, so these comparisons work identically in `en` and `ar` and never
 * need the locale spliced out by hand.
 *
 * A section is active for its own hub and everything beneath it -- `/services`
 * stays lit while the reader is on `/services/crm-platform`. Home is matched
 * exactly, otherwise it would be active everywhere.
 *
 * `aria-current="page"` carries the state for assistive tech; the underline is
 * the visual half of the same fact, not a substitute for it.
 */
export function NavLink({ href, label }: { href: string; label: string }) {
  const pathname = usePathname();
  const active = href === "/" ? pathname === "/" : pathname.startsWith(href);

  return (
    <Link
      href={href}
      aria-current={active ? "page" : undefined}
      className={cn(
        // whitespace-nowrap: several Arabic labels are two words and were
        // wrapping mid-item, which broke the row's rhythm at lg.
        // text-[0.9375rem]: 14px read as small print against a 40px logo and a
        // 68px headline; 15px is legible without crowding the row.
        "focus-ring relative inline-flex min-h-11 items-center whitespace-nowrap rounded-[var(--radius-sm)] px-4 text-[0.9375rem] font-medium transition-colors xl:px-5",
        // The underline is a pseudo-element rather than a border so it does
        // not shift the item's box when it appears.
        "after:absolute after:inset-x-4 after:bottom-1.5 after:h-0.5 after:origin-center after:scale-x-0 after:rounded-full after:bg-secondary after:transition-transform after:duration-200 after:ease-out xl:after:inset-x-5",
        active
          ? "text-foreground after:scale-x-100"
          : "text-muted-foreground hover:text-foreground hover:after:scale-x-100",
      )}
    >
      {label}
    </Link>
  );
}
