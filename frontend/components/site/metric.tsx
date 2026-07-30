export function Metric({ value, label }: { value: number | string; label: string }) {
  return (
    // Centred at every breakpoint: the hero is a centred composition, so
    // start-aligning these at sm+ (as before) pulled the proof row out of
    // line with everything above it.
    <div className="flex flex-col items-center gap-1 text-center">
      {/* text-primary is contrast-safe here and only here among the brand-blue
          text uses: at 30px+ extrabold this clears the WCAG large-text
          threshold, where the bar is 3:1 rather than 4.5:1 (see globals.css). */}
      <span className="text-3xl font-extrabold tabular-nums text-primary sm:text-4xl">
        {value}
        <span aria-hidden="true">+</span>
      </span>
      <span className="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
        {label}
      </span>
    </div>
  );
}
