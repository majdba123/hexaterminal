"use client";

import { useEffect, useRef } from "react";
import { cn } from "@/lib/utils";

/**
 * Scroll-triggered motion for the CRMA case study.
 *
 * Progressive enhancement: the server renders everything visible. After
 * hydration, content that starts below the fold is parked (opacity/transform
 * only -- see `[data-crma-*]` in app/globals.css) and released the first time
 * it scrolls into view. Nothing is parked for visitors who prefer reduced
 * motion, and nothing is parked above the fold, so there is no flash and no
 * content that depends on JavaScript to be readable.
 */

type Tag = "div" | "ol" | "ul" | "section" | "li";

const prefersReducedMotion = () => window.matchMedia("(prefers-reduced-motion: reduce)").matches;

function useReveal<T extends HTMLElement>(attr: "data-crma-reveal" | "data-crma-stagger", stagger: boolean) {
  const ref = useRef<T>(null);

  useEffect(() => {
    const el = ref.current;
    if (!el || prefersReducedMotion() || typeof IntersectionObserver === "undefined") return;
    if (el.getBoundingClientRect().top < window.innerHeight * 0.9) return; // already on screen

    if (stagger) {
      Array.from(el.children).forEach((child, i) => (child as HTMLElement).style.setProperty("--i", String(i)));
    }
    el.setAttribute(attr, "pending");
    const io = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return;
        // Two frames so the parked state is painted before it is released.
        requestAnimationFrame(() => requestAnimationFrame(() => el.setAttribute(attr, "in")));
        io.disconnect();
      },
      { threshold: 0.18, rootMargin: "0px 0px -6% 0px" },
    );
    io.observe(el);
    return () => io.disconnect();
  }, [attr, stagger]);

  return ref;
}

/** Fades and lifts one block into place. `from="end"` slides in from the inline-end edge (mirrors under RTL). */
export function Reveal({
  as: Tag = "div",
  from = "below",
  className,
  children,
}: {
  as?: Tag;
  from?: "below" | "end";
  className?: string;
  children: React.ReactNode;
}) {
  const ref = useReveal<HTMLElement>("data-crma-reveal", false);
  return (
    <Tag ref={ref as never} data-crma-from={from} className={className}>
      {children}
    </Tag>
  );
}

/** Reveals direct children one after another. Used for lists, tiers and device rows. */
export function Stagger({
  as: Tag = "div",
  className,
  children,
  ...rest
}: {
  as?: Tag;
  className?: string;
  children: React.ReactNode;
} & React.AriaAttributes) {
  const ref = useReveal<HTMLElement>("data-crma-stagger", true);
  return (
    <Tag ref={ref as never} className={className} {...rest}>
      {children}
    </Tag>
  );
}

/**
 * A slow vertical drift tied to scroll position, at most `range` px either
 * way. Wide screens only; off for reduced motion. Transform only, written from
 * rAF, never through React state.
 */
export function Parallax({
  range = 28,
  className,
  children,
}: {
  range?: number;
  className?: string;
  children: React.ReactNode;
}) {
  const ref = useRef<HTMLDivElement>(null);

  useEffect(() => {
    const el = ref.current;
    if (!el || prefersReducedMotion()) return;
    const wide = window.matchMedia("(min-width: 1024px)");
    let frame = 0;

    const update = () => {
      frame = 0;
      if (!wide.matches) {
        el.style.transform = "";
        return;
      }
      const rect = el.getBoundingClientRect();
      const progress = (rect.top + rect.height / 2 - window.innerHeight / 2) / window.innerHeight; // -1..1 near view
      const clamped = Math.max(-1, Math.min(1, progress));
      el.style.transform = `translate3d(0, ${(clamped * range).toFixed(1)}px, 0)`;
    };
    const onScroll = () => {
      if (!frame) frame = requestAnimationFrame(update);
    };

    update();
    window.addEventListener("scroll", onScroll, { passive: true });
    window.addEventListener("resize", onScroll, { passive: true });
    return () => {
      window.removeEventListener("scroll", onScroll);
      window.removeEventListener("resize", onScroll);
      if (frame) cancelAnimationFrame(frame);
    };
  }, [range]);

  return (
    <div ref={ref} className={cn("will-change-transform", className)}>
      {children}
    </div>
  );
}
