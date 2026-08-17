"use client";

import { useState } from "react";
import { ChevronDown, Menu } from "lucide-react";
import { useTranslations } from "next-intl";
import { Link, usePathname } from "@/i18n/navigation";
import { buttonVariants } from "@/components/ui/button";
import { Dialog, DialogContent, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { cn } from "@/lib/utils";
import { navChildren, primaryNavRoutes } from "@/lib/routes/registry";

// Shares the primary-navigation source of truth with the desktop header
// (lib/routes/registry.ts). `path: ""` (home) renders as href "/".
const navItems = primaryNavRoutes().map((r) => ({
  key: r.navKey as string,
  href: r.path || "/",
}));

const aboutChildren = [
  ...primaryNavRoutes().filter((route) => route.id === "about"),
  ...navChildren("about"),
].map((route) => ({
  key: route.navKey as string,
  href: route.path || "/",
}));

export function MobileNav() {
  const t = useTranslations("nav");
  const tc = useTranslations("common");
  const pathname = usePathname();
  const [open, setOpen] = useState(false);
  const [aboutOpen, setAboutOpen] = useState(pathname.startsWith("/about"));
  const isActive = (href: string) => href === "/" ? pathname === "/" : pathname.startsWith(href);

  return (
    <Dialog
      open={open}
      onOpenChange={(nextOpen) => {
        setOpen(nextOpen);
        if (nextOpen) {
          setAboutOpen(pathname.startsWith("/about"));
        }
      }}
    >
      {/* DialogTrigger (not a plain onClick) is required so Radix records
          this button as the element to restore focus to when the dialog
          closes -- without it, focus was dropping to <body> on Escape
          (caught by e2e/accessibility.spec.ts's keyboard-focus-restore check).
          Styled directly with buttonVariants rather than nesting the Button
          component (which isn't ref-forwarding) inside DialogTrigger asChild --
          matches the same safe pattern already used in showreel.tsx. */}
      <DialogTrigger
        type="button"
        className={buttonVariants({ variant: "ghost", size: "icon", className: "2xl:hidden" })}
        aria-label={tc("openMenu")}
      >
        <Menu className="size-5" aria-hidden="true" />
      </DialogTrigger>
      <DialogContent closeLabel={tc("close")} className="top-24 max-w-sm translate-y-0">
        <DialogTitle className="sr-only">{tc("openMenu")}</DialogTitle>
        <nav aria-label={tc("openMenu")} className="flex flex-col gap-1 p-4">
          {navItems.map(({ key, href }) =>
            key === "about" ? (
              <div
                key={key}
                className={cn(
                  "rounded-[var(--radius-lg)] border border-border bg-surface px-2 py-2",
                  pathname.startsWith("/about") && "border-primary/30",
                )}
              >
                <button
                  type="button"
                  aria-expanded={aboutOpen}
                  aria-controls="mobile-about-submenu"
                  onClick={() => setAboutOpen((value) => !value)}
                  className={cn(
                    "focus-ring flex min-h-11 w-full items-center justify-between gap-3 rounded-[var(--radius-md)] px-3 py-3 text-start text-base font-medium",
                    pathname.startsWith("/about") ? "text-foreground" : "text-muted-foreground",
                  )}
                >
                  <span>{t("aboutGroup")}</span>
                  <ChevronDown
                    className={cn("size-4 shrink-0 transition-transform", aboutOpen && "rotate-180")}
                    aria-hidden="true"
                  />
                </button>
                {aboutOpen ? (
                  <div id="mobile-about-submenu" className="mt-1 flex flex-col gap-1 ps-3">
                    {aboutChildren.map(({ key: childKey, href: childHref }) => (
                      <Link
                        key={childHref}
                        href={childHref}
                        aria-current={isActive(childHref) ? "page" : undefined}
                        onClick={() => setOpen(false)}
                        className={cn(
                          "focus-ring rounded-[var(--radius-md)] px-3 py-2.5 text-sm font-medium",
                          isActive(childHref)
                            ? "bg-background text-foreground"
                            : "text-muted-foreground hover:bg-muted hover:text-foreground",
                        )}
                      >
                        {t(childKey === "about" ? "aboutUs" : childKey)}
                      </Link>
                    ))}
                  </div>
                ) : null}
              </div>
            ) : (
              <Link
                key={key}
                href={href}
                aria-current={isActive(href) ? "page" : undefined}
                onClick={() => setOpen(false)}
                className="focus-ring rounded-[var(--radius-md)] px-3 py-3 text-base font-semibold text-foreground hover:bg-muted"
              >
                {t(key)}
              </Link>
            ),
          )}
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
