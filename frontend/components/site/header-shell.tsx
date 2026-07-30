"use client";

import * as React from "react";
import { cn } from "@/lib/utils";

/**
 * Sticky header shell that only takes on its glass treatment once the page has
 * scrolled.
 *
 * At the top of the home page the header sits directly above the hero, so a
 * permanent opaque bar would cut a hard line across the artwork. Leaving it
 * transparent until the first scroll lets the hero read as full-bleed, and the
 * border + blur then appear exactly when the header starts overlapping real
 * content and needs to separate itself from it.
 *
 * No theme override lives here any more. An earlier version forced a dark ink
 * scope onto the header so it could sit on a permanently-dark hero; the hero
 * now follows the page theme (see `.hero-cinematic` in globals.css, which
 * inverts the film's luminance in light mode instead of pinning it dark), so
 * the header simply inherits like every other page and light mode is light all
 * the way up.
 *
 * The scroll listener is passive and only ever flips one boolean, so it does
 * no work beyond a comparison per frame. `backdrop-blur` is toggled, never
 * animated -- animating a blur across the full width of the viewport is one of
 * the most expensive things a page can do.
 */
export function HeaderShell({ children }: { children: React.ReactNode }) {
  const [scrolled, setScrolled] = React.useState(false);

  React.useEffect(() => {
    const onScroll = () => setScrolled(window.scrollY > 8);

    onScroll(); // restore correct state on refresh mid-page
    window.addEventListener("scroll", onScroll, { passive: true });
    return () => window.removeEventListener("scroll", onScroll);
  }, []);

  return (
    <header
      className={cn(
        "sticky top-0 z-(--z-header) border-b transition-colors duration-200",
        scrolled
          ? "border-border bg-background/80 backdrop-blur-xl"
          : "border-transparent bg-transparent",
      )}
    >
      {children}
    </header>
  );
}
