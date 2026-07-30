/**
 * Timing for the hero's System Stack Hex entrance.
 *
 * Every duration and delay in the animation lives here rather than being
 * scattered through the markup, so the sequence can be re-timed or disabled in
 * one place. The values are emitted as CSS custom properties and consumed by
 * the keyframes in app/globals.css -- the animation itself runs entirely on the
 * compositor (transform / opacity / stroke-dashoffset), with no per-frame JS.
 *
 * Phases follow the brief:
 *   1 environment      0 - 350ms    grid and anchor points fade up
 *   2 main frame     250 - 1500ms   six edges draw in sequence from the top
 *   3 layer assembly 700 - 1800ms   offset layers translate inward and lock
 *   4 activation    1450 - 2100ms   nodes illuminate, one pulse traverses
 *   5 terminal      1900 - 3900ms   prompt types, status rows resolve
 *   6 statement     3500 - 4300ms   closing line rises into place
 */

/** One hexagon edge: how long a single segment takes to draw. */
export const EDGE_DRAW_MS = 210;

/** Gap between consecutive edge draws. Six edges * stride + draw = phase 2. */
export const EDGE_STRIDE_MS = 208;

/** Phase 2 starts here; edge N begins at PHASE_2_MS + N * EDGE_STRIDE_MS. */
export const PHASE_2_MS = 250;

/** Status rows resolve one after another inside phase 5. */
export const STATUS_STRIDE_MS = 260;

export const HEXA_TIMELINE = {
  gridInMs: 0,
  gridDurationMs: 350,

  edgeStartMs: PHASE_2_MS,
  edgeDrawMs: EDGE_DRAW_MS,
  edgeStrideMs: EDGE_STRIDE_MS,

  layerStartMs: 700,
  layerDurationMs: 1100,

  nodeStartMs: 1450,
  nodeDurationMs: 420,
  pulseStartMs: 1600,
  pulseDurationMs: 1400,

  promptStartMs: 1900,
  promptDurationMs: 620,
  statusStartMs: 2600,
  statusStrideMs: STATUS_STRIDE_MS,
  statusDurationMs: 320,

  statementStartMs: 3500,
  statementDurationMs: 800,

  /** Idle: one restrained pulse every 8.5s, well inside the 7-10s brief. */
  idlePeriodMs: 8500,
} as const;

/** Total runtime, used to mark the visual settled and start idle behaviour. */
export const HEXA_TOTAL_MS =
  HEXA_TIMELINE.statementStartMs + HEXA_TIMELINE.statementDurationMs;

/**
 * Largest pointer parallax offset, in px. The brief caps this at 3-5px; it is
 * applied as a transform on a wrapper via a CSS variable written from a
 * requestAnimationFrame callback, never through React state.
 */
export const PARALLAX_MAX_PX = 4;

/** The six hexagon edges, in draw order: top, then clockwise. */
export const EDGE_ORDER = ["top", "upper-end", "lower-end", "bottom", "lower-start", "upper-start"] as const;

/**
 * CSS custom properties for the timeline. Spread onto the visual's root so the
 * keyframes and per-element delays read from one source.
 */
export function hexaTimelineVars(): Record<string, string> {
  const t = HEXA_TIMELINE;

  return {
    "--hexa-grid-dur": `${t.gridDurationMs}ms`,
    "--hexa-edge-dur": `${t.edgeDrawMs}ms`,
    "--hexa-layer-start": `${t.layerStartMs}ms`,
    "--hexa-layer-dur": `${t.layerDurationMs}ms`,
    "--hexa-node-start": `${t.nodeStartMs}ms`,
    "--hexa-node-dur": `${t.nodeDurationMs}ms`,
    "--hexa-pulse-start": `${t.pulseStartMs}ms`,
    "--hexa-pulse-dur": `${t.pulseDurationMs}ms`,
    "--hexa-prompt-start": `${t.promptStartMs}ms`,
    "--hexa-prompt-dur": `${t.promptDurationMs}ms`,
    "--hexa-statement-start": `${t.statementStartMs}ms`,
    "--hexa-statement-dur": `${t.statementDurationMs}ms`,
    "--hexa-idle-period": `${t.idlePeriodMs}ms`,
  };
}

/** Delay for hexagon edge `index` (0-5). */
export function edgeDelayMs(index: number): number {
  return HEXA_TIMELINE.edgeStartMs + index * HEXA_TIMELINE.edgeStrideMs;
}

/** Delay for status row `index` (0-3). */
export function statusDelayMs(index: number): number {
  return HEXA_TIMELINE.statusStartMs + index * HEXA_TIMELINE.statusStrideMs;
}
