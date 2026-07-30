import { edgeDelayMs } from "@/lib/hexa-motion";

/**
 * Geometry for the System Stack Hex.
 *
 * A flat-top hexagon -- flat top and bottom, vertices at left and right. That
 * is the brand mark's shape, and it gives the widest usable interior for the
 * terminal at a given height.
 *
 * For circumradius R about (CX, CY) the vertices are at (+-R, 0) and
 * (+-R/2, +-R*sin60), so the box is 2R wide by R*sqrt(3) tall.
 *
 * Rings, outermost in. The reference is built from five concentric rings, not
 * one outline: two hairlines, the armoured panel band, a bright inner edge, and
 * a hairline enclosing the terminal.
 */
const CX = 360;
const CY = 312;
const SIN60 = 0.8660254;

const R_HAIRLINE_1 = 348;
const R_HAIRLINE_2 = 324;
const R_PANEL = 290;
const R_EDGE_BRIGHT = 258;
const R_TERMINAL = 244;

/** Half-thickness of the armoured band, used to size the panel stroke. */
const PANEL_WIDTH = 30;

/** Gap left at each end of a panel so the vertex chevrons can sit in it. */
const PANEL_INSET = 16;

type Point = readonly [number, number];

function round(n: number): number {
  return Math.round(n * 10) / 10;
}

/** Vertices of a flat-top hexagon, from top-left, clockwise. */
function vertices(r: number): Point[] {
  const h = r * SIN60;

  return [
    [CX - r / 2, CY - h],
    [CX + r / 2, CY - h],
    [CX + r, CY],
    [CX + r / 2, CY + h],
    [CX - r / 2, CY + h],
    [CX - r, CY],
  ];
}

function polygonPoints(r: number): string {
  return vertices(r)
    .map(([x, y]) => `${round(x)},${round(y)}`)
    .join(" ");
}

function lerp(a: Point, b: Point, t: number): Point {
  return [a[0] + (b[0] - a[0]) * t, a[1] + (b[1] - a[1]) * t];
}

function line(a: Point, b: Point): string {
  return `M ${round(a[0])} ${round(a[1])} L ${round(b[0])} ${round(b[1])}`;
}

/** The six edges of ring `r`, in draw order: top first, then clockwise. */
function edges(r: number): { key: string; a: Point; b: Point }[] {
  const v = vertices(r);
  const keys = ["top", "upper-end", "lower-end", "bottom", "lower-start", "upper-start"];

  return keys.map((key, i) => ({ key, a: v[i], b: v[(i + 1) % v.length] }));
}

/** Shortens an edge by `inset` user units at both ends. */
function inset(a: Point, b: Point, amount: number): [Point, Point] {
  const len = Math.hypot(b[0] - a[0], b[1] - a[1]);
  const t = amount / len;

  return [lerp(a, b, t), lerp(a, b, 1 - t)];
}

/** Centred sub-segment covering `fraction` of the edge. */
function centred(a: Point, b: Point, fraction: number): [Point, Point] {
  const t0 = (1 - fraction) / 2;

  return [lerp(a, b, t0), lerp(a, b, 1 - t0)];
}

/**
 * Outward-pointing chevron at a vertex -- the angular corner pieces in the
 * reference. Built from the two ring edges meeting at that vertex so it always
 * follows the hexagon's angles.
 */
function chevron(r: number, index: number, depth: number, spread: number): string {
  const v = vertices(r);
  const here = v[index];
  const prev = v[(index + 5) % 6];
  const next = v[(index + 1) % 6];

  const armA = lerp(here, prev, spread);
  const armB = lerp(here, next, spread);

  // Push the tip outward along the bisector from the centre.
  const dx = here[0] - CX;
  const dy = here[1] - CY;
  const mag = Math.hypot(dx, dy) || 1;
  const tip: Point = [here[0] + (dx / mag) * depth, here[1] + (dy / mag) * depth];

  return `M ${round(armA[0])} ${round(armA[1])} L ${round(tip[0])} ${round(tip[1])} L ${round(armB[0])} ${round(armB[1])}`;
}

/** Small illuminated dots sitting on the outer hairline. */
const HAIRLINE_DOTS: { edge: number; t: number }[] = [
  { edge: 0, t: 0.28 },
  { edge: 1, t: 0.62 },
  { edge: 2, t: 0.34 },
  { edge: 3, t: 0.7 },
  { edge: 4, t: 0.45 },
  { edge: 5, t: 0.18 },
];

/**
 * PCB traces running off every edge, each ending in a square pad.
 *
 * Built from the midpoint of each hairline edge, stepped outward along that
 * edge's outward normal with one right-angle dogleg -- board traces turn at 90
 * degrees, and that single detail is most of what separates "circuit" from
 * "generic sci-fi rays".
 */
function traces(): { key: string; d: string; pad: Point }[] {
  return edges(R_HAIRLINE_1).map(({ key, a, b }, i) => {
    const mid = lerp(a, b, i % 2 === 0 ? 0.5 : 0.36);

    // Outward normal of this edge, from the centre through its midpoint.
    const nx = mid[0] - CX;
    const ny = mid[1] - CY;
    const mag = Math.hypot(nx, ny) || 1;
    const ux = nx / mag;
    const uy = ny / mag;

    const run = 26 + (i % 3) * 12;
    const p1: Point = [mid[0] + ux * run, mid[1] + uy * run];
    // Dogleg: continue on whichever axis the normal favours least, so the turn
    // is always visible rather than collinear with the first leg.
    const horizontal = Math.abs(ux) < Math.abs(uy);
    const leg = 30 + (i % 2) * 16;
    const p2: Point = horizontal ? [p1[0] + (ux >= 0 ? leg : -leg), p1[1]] : [p1[0], p1[1] + (uy >= 0 ? leg : -leg)];

    return {
      key,
      d: `M ${round(mid[0])} ${round(mid[1])} L ${round(p1[0])} ${round(p1[1])} L ${round(p2[0])} ${round(p2[1])}`,
      pad: p2,
    };
  });
}

const CIRCUITS = traces();

/**
 * The structural SVG behind the terminal.
 *
 * Entirely decorative, so the element is aria-hidden -- what a reader needs is
 * the real text in TerminalSequence, not this chrome.
 *
 * Animation is driven by CSS keyed off `[data-hexa-play]` on the parent (see
 * app/globals.css); this component only supplies geometry and per-element
 * delays, which keeps it a server component with no client bundle cost.
 */
export function HexaFrame() {
  const panelEdges = edges(R_PANEL);

  return (
    <svg
      viewBox="0 0 720 624"
      className="hexa-svg h-auto w-full"
      aria-hidden="true"
      focusable="false"
      xmlns="http://www.w3.org/2000/svg"
    >
      <defs>
        {/*
          Technical grid. Faint by design: engineering paper, not a mesh.

          The stroke is an explicit token, NOT currentColor. Inside <defs> a
          pattern is not in the referencing element's inheritance chain, so
          `currentColor` resolved against the document's foreground (near-white)
          and the grid rendered as a bright mesh dominating the interior
          regardless of the class on the <g> that used it.
        */}
        <pattern id="hexa-grid" width="36" height="36" patternUnits="userSpaceOnUse">
          <path d="M 36 0 L 0 0 0 36" fill="none" stroke="var(--color-border)" strokeWidth="1" />
        </pattern>
        <clipPath id="hexa-grid-clip">
          <polygon points={polygonPoints(R_HAIRLINE_1)} />
        </clipPath>

      </defs>

      <g className="hexa-grid" opacity="0.9">
        <rect x="0" y="0" width="720" height="624" fill="url(#hexa-grid)" clipPath="url(#hexa-grid-clip)" />
      </g>

      {/* Ring 1 -- outermost hairline, with the small lit dots. */}
      <g className="hexa-layer hexa-layer-outer">
        <polygon points={polygonPoints(R_HAIRLINE_1)} fill="none" stroke="currentColor" strokeWidth="1" className="text-border" />

        {HAIRLINE_DOTS.map(({ edge, t }, i) => {
          const { a, b } = edges(R_HAIRLINE_1)[edge];
          const [x, y] = lerp(a, b, t);

          return (
            <circle
              key={`dot-${i}`}
              cx={round(x)}
              cy={round(y)}
              r="3"
              className="hexa-anchor fill-secondary"
              style={{ "--d": `${i * 55}ms` } as React.CSSProperties}
            />
          );
        })}
      </g>

      {/* Ring 2 -- second hairline with the top-centre notch and the outward
          vertex chevrons. */}
      <g className="hexa-layer hexa-layer-outer">
        <polygon points={polygonPoints(R_HAIRLINE_2)} fill="none" stroke="currentColor" strokeWidth="1" className="text-border" />

        {/* Notch: a small peak breaking the top edge, as in the reference. */}
        <path
          d={`M ${CX - 18} ${round(CY - R_HAIRLINE_2 * SIN60)} l 18 -9 l 18 9`}
          fill="none"
          stroke="currentColor"
          strokeWidth="1.5"
          className="text-secondary"
        />

        {[2, 5].map((vertexIndex) => (
          <path
            key={`chev-${vertexIndex}`}
            d={chevron(R_HAIRLINE_2, vertexIndex, 10, 0.075)}
            fill="none"
            stroke="currentColor"
            strokeWidth="1.5"
            strokeLinejoin="round"
            className="hexa-anchor text-secondary"
          />
        ))}
      </g>

      {/* Ring 3 -- the armoured band. Each edge is a dark panel with a bright
          bar down its centre; these six panels are what draw in sequence. */}
      <g className="hexa-layer hexa-layer-frame">
        {panelEdges.map(({ key, a, b }, i) => {
          const [pa, pb] = inset(a, b, PANEL_INSET);
          const [ba, bb] = centred(pa, pb, 0.94);

          return (
            // `--d` on the group is the panel's draw delay and is inherited by
            // the panel paths. The bars override it with their own value so they
            // light in phase 4 rather than inheriting a delay of up to 1.3s.
            <g key={`panel-${key}`} style={{ "--d": `${edgeDelayMs(i)}ms` } as React.CSSProperties}>
              {/* Bevel then body: a slightly wider stroke behind the dark fill
                  reads as the panel's edge. Both carry `hexa-panel`, so the
                  panel extrudes along the edge as one piece -- a dash draw on a
                  butt-capped thick stroke is what makes the assembly look
                  mechanical rather than like a line being sketched. */}
              <path
                d={line(pa, pb)}
                stroke="currentColor"
                strokeWidth={PANEL_WIDTH + 3}
                strokeLinecap="butt"
                pathLength={1}
                className="hexa-panel text-border"
              />
              <path
                d={line(pa, pb)}
                stroke="currentColor"
                strokeWidth={PANEL_WIDTH}
                strokeLinecap="butt"
                pathLength={1}
                className="hexa-panel text-surface-elevated"
              />

              {/*
                Bright bar: three stacked strokes of decreasing width give the
                cross-section falloff that reads as illumination.

                Deliberately NOT a linearGradient. A gradient in
                objectBoundingBox units collapses on the top and bottom panels,
                whose bounding box has zero height -- those two bars rendered
                completely dark while the four diagonals looked correct.
              */}
              {[
                { w: 16, o: 0.16 },
                { w: 9, o: 0.38 },
                { w: 4.5, o: 0.85 },
                { w: 1.8, o: 1 },
              ].map(({ w, o }) => (
                <path
                  key={`bar-${w}`}
                  d={line(ba, bb)}
                  stroke="var(--color-accent)"
                  strokeWidth={w}
                  strokeLinecap="round"
                  opacity={o}
                  className="hexa-accent"
                  style={{ "--d": `${i * 70}ms` } as React.CSSProperties}
                />
              ))}
            </g>
          );
        })}

        {/* Joint nodes in the gaps between panels. */}
        {vertices(R_PANEL).map(([x, y], i) => (
          <g key={`node-${i}`} className="hexa-node" style={{ "--d": `${i * 60}ms` } as React.CSSProperties}>
            <circle cx={round(x)} cy={round(y)} r="17" className="fill-accent" opacity="0.14" />
            <circle cx={round(x)} cy={round(y)} r="11" className="fill-background stroke-secondary" strokeWidth="1.5" />
            <circle cx={round(x)} cy={round(y)} r="6" className="fill-accent" />
          </g>
        ))}
      </g>

      {/* Ring 4 -- bright inner edge of the armour. */}
      <g className="hexa-layer hexa-layer-inner">
        <polygon
          points={polygonPoints(R_EDGE_BRIGHT)}
          fill="none"
          stroke="currentColor"
          strokeWidth="1.5"
          className="text-secondary/60"
        />
      </g>

      {/* Ring 5 -- hairline enclosing the terminal panel. */}
      <g className="hexa-layer hexa-layer-inner">
        <polygon points={polygonPoints(R_TERMINAL)} fill="none" stroke="currentColor" strokeWidth="1" className="text-border" />
      </g>

      {/*
        Circuit wires. Two only, each carrying ONE travelling pulse -- restraint
        here is what separates "engineered" from "HUD".

        The pulse is a short dash animated along the same path via
        stroke-dashoffset, not SMIL <animateMotion> (which would need
        begin="indefinite" plus a JS beginElement() to stay gated behind the
        entrance, and has uneven support) and not offset-path. A dash is pure
        CSS, gated by the same attribute as everything else, and animates one
        compositor-friendly property.
      */}
      <g className="hexa-circuit">
        {CIRCUITS.map(({ key, d, pad }, i) => (
          <g key={key}>
            <path d={d} fill="none" stroke="currentColor" strokeWidth="1" className="text-border" />
            <rect
              x={round(pad[0] - 3.5)}
              y={round(pad[1] - 3.5)}
              width="7"
              height="7"
              className="fill-background stroke-secondary"
              strokeWidth="1"
            />
            <path
              d={d}
              fill="none"
              stroke="var(--color-accent)"
              strokeWidth="2.5"
              strokeLinecap="round"
              className="hexa-pulse"
              pathLength={1}
              style={{ "--d": `${i * 1200}ms` } as React.CSSProperties}
            />
          </g>
        ))}
      </g>
    </svg>
  );
}
