"use client";

import { useEffect, useRef, useState, useSyncExternalStore } from "react";
import { HexaFrame } from "@/components/site/hexa-frame";
import { TerminalSequence } from "@/components/site/terminal-sequence";
import { HEXA_TOTAL_MS, PARALLAX_MAX_PX, hexaTimelineVars } from "@/lib/hexa-motion";

const REDUCED_MOTION = "(prefers-reduced-motion: reduce)";
const FINE_POINTER = "(hover: hover) and (pointer: fine)";

function subscribeReducedMotion(callback: () => void) {
  const mql = window.matchMedia(REDUCED_MOTION);
  mql.addEventListener("change", callback);

  return () => mql.removeEventListener("change", callback);
}

function getReducedMotion(): boolean {
  return window.matchMedia(REDUCED_MOTION).matches;
}

/** Server render never animates, which is also the correct pre-hydration state. */
function getServerReducedMotion(): boolean {
  return true;
}

/**
 * The hero's System Stack Hex: layered SVG frame with a live terminal inside.
 *
 * Runs its build-out ONCE, when it first enters the viewport, then holds a
 * stable state with one restrained circuit pulse on a long loop. It never
 * replays on scroll -- the observer disconnects on first intersection.
 *
 * Everything visual is CSS, gated on the `data-hexa-play` attribute set here
 * (keyframes in app/globals.css). No animation library is installed, and the
 * sequence only touches transform / opacity / stroke-dashoffset, so React does
 * no per-frame work.
 *
 * FAIL-VISIBLE by design. With no attribute at all the stylesheet renders the
 * FINISHED artwork; only `data-hexa-play="playing"` animates. So no-JS,
 * no-IntersectionObserver, a crawler, and prefers-reduced-motion all get the
 * complete visual immediately rather than an invisible one -- the earlier
 * arrangement, where the pre-entrance state was authored as opacity:0, would
 * have left the whole thing blank in any of those cases.
 *
 * SSR-safe: the server snapshot reports reduced motion, so the first client
 * render matches it exactly and hydration has nothing to reconcile.
 */
export function HexaTerminalVisual({ className }: { className?: string }) {
  const rootRef = useRef<HTMLDivElement>(null);
  const prefersReducedMotion = useSyncExternalStore(
    subscribeReducedMotion,
    getReducedMotion,
    getServerReducedMotion,
  );
  // Only ever set from callbacks (observer, timer) -- never synchronously in an
  // effect body, which cascades renders.
  const [started, setStarted] = useState(false);
  const [settled, setSettled] = useState(false);

  // Deliberately does NOT depend on `started`. It used to, and the consequence
  // was that flipping `started` re-ran this effect, whose cleanup cleared the
  // settle timer it had just set -- so the visual stayed "playing" forever, the
  // idle pulse never started and parallax never enabled. The timer lives in its
  // own effect below for exactly that reason.
  useEffect(() => {
    if (prefersReducedMotion) return;

    const node = rootRef.current;
    if (!node || typeof IntersectionObserver === "undefined") return;

    const observer = new IntersectionObserver(
      ([entry]) => {
        if (!entry.isIntersecting) return;

        // Disconnect first: this must not re-fire when the visitor scrolls back.
        observer.disconnect();
        setStarted(true);
      },
      // Some of the artwork has to be on screen before it builds, so the
      // visitor sees the assembly instead of arriving at a finished frame.
      { threshold: 0.25 },
    );

    observer.observe(node);

    return () => observer.disconnect();
  }, [prefersReducedMotion]);

  // Mark settled once the timeline has run, which switches the artwork from the
  // build-out to its idle behaviour.
  useEffect(() => {
    if (!started) return;

    const timer = setTimeout(() => setSettled(true), HEXA_TOTAL_MS);

    return () => clearTimeout(timer);
  }, [started]);

  /**
   * Pointer parallax, capped at PARALLAX_MAX_PX. Written straight to a CSS
   * custom property from a requestAnimationFrame callback -- no React state, so
   * pointer movement never triggers a render. Skipped for coarse pointers
   * (touch), for reduced motion, and until the build-out has finished.
   */
  const parallaxReady = settled || prefersReducedMotion;

  useEffect(() => {
    if (!parallaxReady || prefersReducedMotion) return;

    const node = rootRef.current;
    if (!node || !window.matchMedia(FINE_POINTER).matches) return;

    let frame = 0;
    let targetX = 0;
    let targetY = 0;

    const apply = () => {
      frame = 0;
      node.style.setProperty("--hexa-px", `${targetX.toFixed(2)}px`);
      node.style.setProperty("--hexa-py", `${targetY.toFixed(2)}px`);
    };

    const onPointerMove = (event: PointerEvent) => {
      const rect = node.getBoundingClientRect();
      if (!rect.width || !rect.height) return;

      const nx = ((event.clientX - rect.left) / rect.width) * 2 - 1;
      const ny = ((event.clientY - rect.top) / rect.height) * 2 - 1;
      targetX = Math.max(-1, Math.min(1, nx)) * PARALLAX_MAX_PX;
      targetY = Math.max(-1, Math.min(1, ny)) * PARALLAX_MAX_PX;

      if (!frame) frame = requestAnimationFrame(apply);
    };

    const onPointerLeave = () => {
      targetX = 0;
      targetY = 0;
      if (!frame) frame = requestAnimationFrame(apply);
    };

    window.addEventListener("pointermove", onPointerMove, { passive: true });
    node.addEventListener("pointerleave", onPointerLeave);

    return () => {
      window.removeEventListener("pointermove", onPointerMove);
      node.removeEventListener("pointerleave", onPointerLeave);
      if (frame) cancelAnimationFrame(frame);
      node.style.removeProperty("--hexa-px");
      node.style.removeProperty("--hexa-py");
    };
  }, [parallaxReady, prefersReducedMotion]);

  // Absent attribute = finished artwork. See the fail-visible note above.
  const playState = settled ? "settled" : started ? "playing" : undefined;

  return (
    <div
      ref={rootRef}
      data-hexa-play={playState}
      style={hexaTimelineVars()}
      // `@container` so the terminal type scales with the artwork's own width
      // (cqw) rather than the viewport: the visual is sized by clamp() and by
      // which column it occupies, neither of which viewport units can track.
      className={`hexa-visual @container relative isolate ${className ?? ""}`}
    >
      <HexaFrame />

      {/*
        The terminal, centred in the hexagon's interior.

        52%: at the block's top and bottom the hexagon has already narrowed, so
        a wider box collides with the inner ring rather than sitting inside it.
        56% was measurably grazing it on the statement line.
      */}
      <div className="pointer-events-none absolute inset-0 flex items-center justify-center">
        <div className="pointer-events-auto w-[52%]">
          <TerminalSequence />
        </div>
      </div>
    </div>
  );
}
