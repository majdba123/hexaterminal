"use client";

import { useEffect, useRef, useState } from "react";
import { ChevronDown } from "lucide-react";
import { Link, usePathname } from "@/i18n/navigation";
import { cn } from "@/lib/utils";

export function NavGroup({
  label,
  href,
  items,
}: {
  label: string;
  href: string;
  items: { label: string; href: string }[];
}) {
  const pathname = usePathname();
  const [open, setOpen] = useState(false);
  const rootRef = useRef<HTMLDivElement>(null);
  const active =
    pathname === href ||
    pathname.startsWith(`${href}/`) ||
    items.some((item) => pathname === item.href || pathname.startsWith(`${item.href}/`));

  useEffect(() => {
    function onPointerDown(event: MouseEvent) {
      if (!rootRef.current?.contains(event.target as Node)) {
        setOpen(false);
      }
    }

    function onKeyDown(event: KeyboardEvent) {
      if (event.key === "Escape") {
        setOpen(false);
      }
    }

    document.addEventListener("mousedown", onPointerDown);
    document.addEventListener("keydown", onKeyDown);
    return () => {
      document.removeEventListener("mousedown", onPointerDown);
      document.removeEventListener("keydown", onKeyDown);
    };
  }, []);

  return (
    <div
      ref={rootRef}
      className="relative"
      onMouseEnter={() => setOpen(true)}
      onMouseLeave={() => setOpen(false)}
    >
      <button
        type="button"
        aria-haspopup="menu"
        aria-expanded={open}
        onClick={() => setOpen((value) => !value)}
        className={cn(
          "focus-ring relative inline-flex min-h-11 items-center gap-2 whitespace-nowrap rounded-[var(--radius-sm)] px-4 text-[0.9375rem] font-medium transition-colors xl:px-5",
          "after:absolute after:inset-x-4 after:bottom-1.5 after:h-0.5 after:origin-center after:scale-x-0 after:rounded-full after:bg-secondary after:transition-transform after:duration-200 after:ease-out xl:after:inset-x-5",
          active
            ? "text-foreground after:scale-x-100"
            : "text-muted-foreground hover:text-foreground hover:after:scale-x-100",
        )}
      >
        {label}
        <ChevronDown className={cn("size-4 transition-transform", open ? "rotate-180" : "")} aria-hidden="true" />
      </button>

      {open ? (
        <div
          role="menu"
          aria-label={label}
          className="absolute end-0 top-full z-30 mt-2 min-w-56 rounded-[var(--radius-lg)] border border-border bg-background p-2 shadow-lg"
        >
          {[{ label, href }, ...items].map((item) => {
            const itemActive = pathname === item.href || pathname.startsWith(`${item.href}/`);
            return (
              <Link
                key={item.href}
                href={item.href}
                role="menuitem"
                aria-current={itemActive ? "page" : undefined}
                onClick={() => setOpen(false)}
                className={cn(
                  "focus-ring flex rounded-[var(--radius-md)] px-3 py-2.5 text-sm transition-colors",
                  itemActive
                    ? "bg-primary/10 font-semibold text-foreground"
                    : "text-muted-foreground hover:bg-muted hover:text-foreground",
                )}
              >
                {item.label}
              </Link>
            );
          })}
        </div>
      ) : null}
    </div>
  );
}
