import { Boxes, CircleCheck } from "lucide-react";
import { statusDelayMs } from "@/lib/hexa-motion";

/**
 * System mono stack. Nothing monospaced is loaded by the app (only Inter and
 * Cairo), and adding a webfont for a few short lines would cost a request and a
 * layout shift on the LCP screen for no benefit -- the reference look is
 * "terminal", which any system mono delivers.
 */
const MONO = 'ui-monospace, SFMono-Regular, Menlo, Consolas, "Liberation Mono", monospace';

/**
 * The capability readout rows: icon, label, value.
 *
 * `Architecture` has no figure to report, so it resolves to a check mark rather
 * than inventing a metric -- the same reason the proof row outside the hex uses
 * a word for the domains rather than a number.
 */
const ROWS = [
  { key: "architecture", Icon: Boxes, label: "Architecture", value: null },
] as const;

/** Decorative middot between the domain names. */
function Sep() {
  return (
    <span aria-hidden="true" className="text-border">
      {" · "}
    </span>
  );
}

/**
 * The terminal display inside the hex.
 *
 * Real HTML text throughout: selectable, searchable, translatable by the
 * browser, readable by a screen reader. Nothing is baked into an image, and the
 * animation only ever changes opacity/transform, so the text is in the DOM from
 * first paint even while the sequence is still running.
 *
 * Deliberately NOT localised, and `dir="ltr"`. This is a rendering of a system
 * readout -- a technical artefact that reads the same in both locales, like the
 * `>_` in the logo. Forcing LTR matters because a Latin monospace block inside
 * an Arabic page would otherwise have its rows and separators reordered.
 *
 * The separators and row icons are aria-hidden, so a screen reader gets
 * "Architecture ready".
 */
export function TerminalSequence() {
  return (
    <div
      dir="ltr"
      style={{ fontFamily: MONO }}
      className="hexa-terminal w-full text-start [font-variant-numeric:tabular-nums]"
    >
      {/* Title line -- the heaviest thing in the panel. It is the brand, not log
          output, so it carries weight the rows below do not. */}
      <p className="hexa-prompt flex items-center gap-[0.4em] text-[clamp(0.6875rem,2.5cqw,1.125rem)] font-bold leading-none tracking-tight text-foreground">
        <span aria-hidden="true" className="text-accent">
          &gt;_
        </span>
        HEXA TERMINAL
        <span className="hexa-caret" aria-hidden="true" />
      </p>

      <p className="hexa-statement mt-[5%] text-[clamp(0.5625rem,1.85cqw,0.875rem)] leading-relaxed text-muted-foreground">
        Building production systems
      </p>

      <p className="hexa-statement mt-[1.5%] text-[clamp(0.5625rem,1.85cqw,0.875rem)] leading-relaxed text-secondary">
        SaaS
        <Sep />
        CRM
        <Sep />
        ERP
        <Sep />
        AI Workflows
      </p>

      {/* Rule between the identity block and the readout. */}
      <hr className="hexa-statement mt-[6%] border-0 border-t border-border" aria-hidden="true" />

      <dl className="mt-[5%] flex flex-col gap-[3.5%] text-[clamp(0.5625rem,1.85cqw,0.875rem)]">
        {ROWS.map(({ key, Icon, label, value }, i) => (
          <div
            key={key}
            className="hexa-status flex items-center gap-[0.6em]"
            style={{ "--d": `${statusDelayMs(i)}ms` } as React.CSSProperties}
          >
            <Icon className="size-[1.15em] shrink-0 text-secondary" strokeWidth={1.6} aria-hidden="true" />
            <dt className="text-foreground/90">{label}</dt>
            <dd className="ms-auto flex items-center text-secondary">
              {value ?? (
                <>
                  <CircleCheck className="size-[1.15em] text-accent" strokeWidth={1.8} aria-hidden="true" />
                  <span className="sr-only">ready</span>
                </>
              )}
            </dd>
          </div>
        ))}
      </dl>
    </div>
  );
}
