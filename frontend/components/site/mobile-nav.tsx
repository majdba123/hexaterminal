"use client";

import { useState } from "react";
import { Menu } from "lucide-react";
import { useTranslations } from "next-intl";
import { Link, usePathname } from "@/i18n/navigation";
import { buttonVariants } from "@/components/ui/button";
import { Dialog, DialogContent, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { primaryNavRoutes } from "@/lib/routes/registry";

// Shares the primary-navigation source of truth with the desktop header
// (lib/routes/registry.ts). `path: ""` (home) renders as href "/".
const navItems = primaryNavRoutes().map(
  (r) => [r.navKey as string, r.path || "/"] as const,
);

export function MobileNav() {
  const t = useTranslations("nav");
  const tc = useTranslations("common");
  const pathname = usePathname();
  const [open, setOpen] = useState(false);
  const isActive = (href: string) => href === "/" ? pathname === "/" : pathname.startsWith(href);

  return (
    <Dialog open={open} onOpenChange={setOpen}>
      {/* DialogTrigger (not a plain onClick) is required so Radix records
          this button as the element to restore focus to when the dialog
          closes -- without it, focus was dropping to <body> on Escape
          (caught by e2e/accessibility.spec.ts's keyboard-focus-restore check).
          Styled directly with buttonVariants rather than nesting the Button
          component (which isn't ref-forwarding) inside DialogTrigger asChild --
          matches the same safe pattern already used in showreel.tsx. */}
      <DialogTrigger
        type="button"
        className={buttonVariants({ variant: "ghost", size: "icon", className: "xl:hidden" })}
        aria-label={tc("openMenu")}
      >
        <Menu className="size-5" aria-hidden="true" />
      </DialogTrigger>
      <DialogContent closeLabel={tc("close")} className="top-24 max-w-sm translate-y-0">
        <DialogTitle className="sr-only">{tc("openMenu")}</DialogTitle>
        <nav aria-label={tc("openMenu")} className="flex flex-col gap-1 p-4">
          {navItems.map(([key, href]) => (
            <Link
              key={key}
              href={href}
              aria-current={isActive(href) ? "page" : undefined}
              onClick={() => setOpen(false)}
              className="focus-ring rounded-[var(--radius-md)] px-3 py-3 text-base font-semibold text-foreground hover:bg-muted"
            >
              {t(key)}
            </Link>
          ))}
          {/* Search lives here on mobile: the header bar has no room for it
              below sm (see header.tsx), so this is its only entry point on a
              phone -- it must not be dropped from the drawer. */}
          <Link
            href="/search"
            aria-current={isActive("/search") ? "page" : undefined}
            onClick={() => setOpen(false)}
            className="focus-ring rounded-[var(--radius-md)] px-3 py-3 text-base font-semibold text-foreground hover:bg-muted sm:hidden"
          >
            {t("search")}
          </Link>
          <Link
            href="/start-a-project"
            aria-current={isActive("/start-a-project") ? "page" : undefined}
            onClick={() => setOpen(false)}
            className="focus-ring mt-2 rounded-[var(--radius-md)] bg-primary px-3 py-3 text-center text-base font-semibold text-primary-foreground"
          >
            {t("startProject")}
          </Link>
        </nav>
      </DialogContent>
    </Dialog>
  );
}
