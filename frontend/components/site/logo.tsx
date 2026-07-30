import { cn } from "@/lib/utils";

/**
 * Perimeter of the hexagon path below, in viewBox units.
 *
 * 12.6 + 14.83 + 14.83 + 12.6 + 14.83 + 14.83 = 84.52, rounded up so the
 * dash definitively covers the whole outline with no sliver left showing at
 * the start of the draw.
 */
const HEX_PATH_LENGTH = 86;

/**
 * Stroke lengths for the two glyph strokes, so they can draw themselves rather
 * than fade in. Both are straight-line paths, so the length is just geometry:
 *
 *   chevron `M10.4 11.2 16.4 16l-6 4.8` - two legs of sqrt(6^2 + 4.8^2)
 *   underscore `M18.2 20.8h4.6`         - a single 4.6 run
 *
 * Rounded up so no sliver of the stroke is visible before its draw begins.
 */
const CHEVRON_PATH_LENGTH = 16;
const UNDERSCORE_PATH_LENGTH = 5;

/**
 * The official Hexa Terminal logo (icons/logo.svg), inlined so the
 * wordmark can theme via `currentColor`. Mark: a hexagon outline with a
 * terminal prompt glyph (`>_`) at its center, in the brand blue/cyan
 * gradient (see app/globals.css) -- matches the branded reveal frame in
 * public/media/hero-intro.mp4, the source of truth for this mark.
 *
 * On load the WHOLE logo is drawn, not just its outline: the hexagon strokes
 * itself on, then the chevron, then the underscore, and finally the wordmark
 * wipes in left-to-right as if written. Each piece animates a stroke or a
 * clip -- nothing simply fades -- which is the same gesture as the opening
 * seconds of that film (keyframes in globals.css).
 * It runs once per full page load -- the header lives in the layout, so
 * client-side navigation does not remount it and the animation does not
 * re-fire on every route change.
 */
export function Logo({
  className,
  variant = "full",
}: {
  className?: string;
  /**
   * `mark` drops the wordmark and tightens the viewBox to the hexagon alone.
   *
   * The full lockup is ~6.5:1, so at a 28px height it is 182px wide -- which,
   * next to the header's utility cluster, overflowed a 320px viewport by 44px
   * (a real horizontal scrollbar, present on every page). The mark alone is
   * square, so the compact header fits. The link around it keeps its
   * aria-label, so nothing is lost to assistive tech.
   */
  variant?: "full" | "mark";
}) {
  const isMark = variant === "mark";

  return (
    <svg
      viewBox={isMark ? "0 0 32 32" : "0 0 208 32"}
      className={cn("h-8 w-auto text-foreground", className)}
      xmlns="http://www.w3.org/2000/svg"
      role="img"
      aria-label="Hexa Terminal"
    >
      <path
        d="M9.7 3.14h12.6L29.69 16l-7.39 12.86H9.7L2.31 16Z"
        fill="none"
        stroke="url(#hexa-logo-hex-gradient)"
        strokeWidth="2.4"
        strokeLinejoin="round"
        strokeDasharray={HEX_PATH_LENGTH}
        style={{
          // Consumed by the hexa-logo-draw keyframes.
          ["--hexa-logo-path" as string]: HEX_PATH_LENGTH,
          // Longhand only -- see the note in hero.tsx: the `animation`
          // shorthand in an inline style is a hydration-mismatch source.
          animationName: "hexa-logo-draw",
          animationDuration: "1s",
          animationTimingFunction: "var(--ease-out-soft)",
          animationFillMode: "both",
        }}
      />
      <path
        d="M10.4 11.2 16.4 16l-6 4.8"
        fill="none"
        stroke="currentColor"
        strokeWidth="2.6"
        strokeLinecap="round"
        strokeLinejoin="round"
        strokeDasharray={CHEVRON_PATH_LENGTH}
        style={{
          ["--hexa-logo-path" as string]: CHEVRON_PATH_LENGTH,
          animationName: "hexa-logo-draw",
          animationDuration: "0.4s",
          animationTimingFunction: "var(--ease-out-soft)",
          animationDelay: "0.55s",
          animationFillMode: "both",
        }}
      />
      <path
        d="M18.2 20.8h4.6"
        fill="none"
        stroke="currentColor"
        strokeWidth="2.6"
        strokeLinecap="round"
        strokeDasharray={UNDERSCORE_PATH_LENGTH}
        style={{
          ["--hexa-logo-path" as string]: UNDERSCORE_PATH_LENGTH,
          animationName: "hexa-logo-draw",
          animationDuration: "0.28s",
          animationTimingFunction: "var(--ease-out-soft)",
          animationDelay: "0.88s",
          animationFillMode: "both",
        }}
      />
      {isMark ? null : (
        <>
      {/*
        direction="ltr" is load-bearing, not decoration. "Hexa Terminal" is a
        Latin wordmark rendered as SVG <text>, and SVG text inherits `direction`
        from the document -- so on the Arabic pages (<html dir="rtl">) it laid
        itself out right-to-left from x=42, running back across the hexagon and
        off the left edge of the viewBox. The logo rendered as a garbled
        fragment overlapping the mark. isolate stops the surrounding RTL context
        from reordering it.

        textLength + lengthAdjust="spacing" pin the wordmark to a known width so
        it can never overflow the fixed 208-wide viewBox and get clipped. Without
        it the width depends on whether Inter has loaded yet; the fallback system
        font measures wider, so the tail of "Terminal" was cut off during the
        font swap. Only letter-spacing is adjusted, never glyph shapes.
      */}
      <text
        x="42"
        y="23"
        fill="currentColor"
        direction="ltr"
        textAnchor="start"
        textLength="160"
        lengthAdjust="spacing"
        style={{
          unicodeBidi: "isolate",
          animationName: "hexa-type",
          animationDuration: "620ms",
          animationTimingFunction: "var(--ease-out-soft)",
          animationDelay: "1.05s",
          animationFillMode: "both",
        }}
        fontFamily="var(--font-inter), ui-sans-serif, system-ui, sans-serif"
        fontSize="20"
        fontWeight="800"
      >
        Hexa Terminal
      </text>
        </>
      )}
      <defs>
        <linearGradient id="hexa-logo-hex-gradient" x1="2.31" y1="3.14" x2="29.69" y2="28.86" gradientUnits="userSpaceOnUse">
          <stop stopColor="#3663D8" />
          <stop offset="0.56" stopColor="#77BEFF" />
          <stop offset="0.99" stopColor="#00D1FF" />
        </linearGradient>
      </defs>
    </svg>
  );
}
