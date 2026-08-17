"use client";

import { useEffect, useId, useRef, useState } from "react";
import { ChevronDown } from "lucide-react";
import { Link, usePathname } from "@/i18n/navigation";
import { cn } from "@/lib/utils";

export function NavGroup({
  label,
  items,
}: {
  label: string;
  items: { label: string; href: string }[];
}) {
  const pathname = usePathname();
  const [open, setOpen] = useState(false);
  const rootRef = useRef<HTMLDivElement>(null);
  const triggerRef = useRef<HTMLButtonElement>(null);
  const menuId = useId();
  const active =
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
        triggerRef.current?.focus();
      }
    }

    document.addEventListener("mousedown", onPointerDown);
    document.addEventListener("keydown", onKeyDown);
    return () => {
      document.removeEventListener("mousedown", onPointerDown);
      document.removeEventListener("keydown", onKeyDown);
    };
  }, []);

  function focusFirstItem() {
    const firstItem = rootRef.current?.querySelector<HTMLAnchorElement>('[role="menuitem"]');
    firstItem?.focus();
  }

  return (
    <div ref={rootRef} className="relative">
      <button
        ref={triggerRef}
        type="button"
        aria-haspopup="menu"
        aria-expanded={open}
        aria-controls={menuId}
        onClick={() => setOpen((value) => !value)}
        onKeyDown={(event) => {
          if (event.key === "ArrowDown" || event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            setOpen(true);
            requestAnimationFrame(focusFirstItem);
          }
        }}
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
          id={menuId}
          role="menu"
          aria-label={label}
          onKeyDown={(event) => {
            if (event.key === "Escape") {
              event.preventDefault();
              setOpen(false);
              triggerRef.current?.focus();
            }
          }}
          className="absolute start-0 top-full z-30 mt-2 min-w-56 rounded-[var(--radius-lg)] border border-border bg-background p-2 shadow-lg rtl:start-auto rtl:end-0"
        >
          {items.map((item) => {
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
