import { getTranslations } from "next-intl/server";
import { ArrowLeft } from "lucide-react";
import { Link } from "@/i18n/navigation";
import { Button } from "@/components/ui/button";
import { Container } from "@/components/site/container";
import { HexaTerminalVisual } from "@/components/site/hexa-terminal-visual";

/**
 * Staggered slide-up entrance (keyframes: `hexa-rise` in app/globals.css).
 *
 * An inline style rather than a Tailwind class because the delay is per
 * element; generating six arbitrary `animate-[...]` variants would be less
 * legible than one helper, and Tailwind would tree-shake a utility-layer
 * keyframe it cannot see a class name for.
 *
 * `both` fill mode matters: it holds the from-state before the delay elapses,
 * so nothing flashes at full opacity and then jumps. Reduced motion is handled
 * globally (globals.css clamps animation-duration), and because the fill mode
 * is `both` those users land on the final state instantly instead of on a
 * blank hero.
 */
function rise(delayMs: number): React.CSSProperties {
  // Longhand only, never the `animation` shorthand. Mixing the shorthand with
  // an `animationDelay` longhand in one style object is a hydration mismatch:
  // the shorthand resets delay to 0, so the result depends on property order,
  // and React's server serializer and the client DOM disagree about it. React
  // reported exactly that ("some attributes of the server rendered HTML didn't
  // match the client properties") on this element.
  return {
    animationName: "hexa-rise",
    animationDuration: "0.7s",
    animationTimingFunction: "var(--ease-out-soft)",
    animationFillMode: "both",
    animationDelay: `${delayMs}ms`,
  };
}

/**
 * One production proof point.
 *
 * Deliberately not the old <Metric>: half of these are not numbers
 * ("SaaS · CRM · ERP"), and a component that hard-codes a "+" suffix and a
 * numeric type cannot express them. `tabular-nums` still applies so the
 * numeric ones align.
 */
function ProofPoint({
  value,
  label,
  variant = "number",
}: {
  value: string;
  label: string;
  /**
   * `number` gets the full display size. `text` is for values that are words
   * rather than figures ("SaaS · CRM · ERP"), which at display size wrapped
   * mid-value and left an orphaned "· ERP" on its own line. Smaller and
   * nowrap keeps it on one line and still reads as a peer of the numbers.
   */
  variant?: "number" | "text";
}) {
  return (
    <div className="flex flex-col gap-1">
      <span
        className={
          variant === "number"
            ? "text-xl font-extrabold tabular-nums text-foreground sm:text-2xl"
            : "whitespace-nowrap text-sm font-extrabold text-foreground sm:text-base"
        }
      >
        {value}
      </span>
      <span className="text-xs font-medium leading-snug text-muted-foreground">{label}</span>
    </div>
  );
}

/**
 * Home hero.
 *
 * Asymmetric by design, and it MIRRORS with reading direction: the film's
 * focal hexagon sits on the inline-END half and the copy on the inline-START
 * half, so Arabic reads copy-right / mark-left and English the reverse.
 * Neither half is a boxed panel, and the composition is not symmetric --
 * which is what made an earlier centred version read as a stock SaaS hero.
 *
 * `hero-cinematic` (globals.css) makes the hero FOLLOW the page theme. The
 * film is authored dark, but light mode inverts its luminance and rotates the
 * hue back, so the same footage renders as a blue mark on white. An earlier
 * build pinned the dark tokens here in both themes, which left light mode
 * opening on a black slab.
 *
 * The section claims the first viewport (`min-h` = viewport minus the 80px
 * header) and centres its WHOLE composition in it, proof points included, so
 * there is no dead band under the navbar and nothing important falls below the
 * fold.
 */
export async function Hero() {
  const t = await getTranslations("home");

  const proofPoints = [
    { key: "systems", value: t("proofSystemsValue"), label: t("proofSystemsLabel"), variant: "number" as const },
    { key: "apis", value: t("proofApisValue"), label: t("proofApisLabel"), variant: "number" as const },
    { key: "domains", value: t("proofDomainsValue"), label: t("proofDomainsLabel"), variant: "text" as const },
    { key: "security", value: t("proofSecurityValue"), label: t("proofSecurityLabel"), variant: "number" as const },
  ];

  return (
    <section className="hero-cinematic relative flex min-h-[calc(100dvh-5rem)] items-center overflow-x-clip">
      <Container>
        {/* 12-column split: copy on the inline-start half, the film's focal
            point left to breathe in the other. Below lg the copy takes the
            full width and the backdrop shifts its focal point above it. */}
        {/* The section is already min-h + items-center, so this padding only
            engages once the content is taller than the viewport (small
            screens). Keeping it small is what removes the dead band that used
            to sit between the navbar and the headline. */}
        {/*
          One column until lg, matching the tablet and mobile references: copy,
          then CTAs, then the Hexa, then the proof metrics. From lg it becomes
          the two-column hero, copy on the inline-start half.
        */}
        <div className="relative grid w-full grid-cols-1 items-center gap-10 py-12 xl:grid-cols-12 xl:items-stretch xl:gap-x-8 xl:gap-y-6">

          {/*
            The copy occupies the INLINE-START half, with no direction override
            at all -- grid columns already run from the inline start, so this
            is the right half in Arabic and the left half in English. The film's
            mark is placed on the inline-END half by hero-backdrop.tsx, so the
            two mirror together and each locale reads text-first.

            An earlier version pinned this to the physical right in both
            locales; once the mark also moved to the right in LTR, the English
            hero had the copy and the hexagon stacked on the same side.
          */}
          <div className="flex flex-col items-start gap-5 text-start max-xl:mx-auto max-xl:w-full max-xl:max-w-2xl xl:col-span-6 xl:col-start-1 xl:row-start-1 xl:self-end">
            {/* Specialty label -- says what we do, not who we are; the header
                already carries the name. */}
            <span
              style={rise(0)}
              className="inline-flex items-center gap-2 rounded-full border border-border/70 bg-surface/50 px-4 py-1.5 text-xs font-semibold text-secondary backdrop-blur-sm"
            >
              <span className="size-1.5 rounded-full bg-accent" aria-hidden="true" />
              {t("heroBadge")}
            </span>

            {/* Capped at 60px rather than 68px: the copy column is half the
                grid, and the English headline is much longer than the Arabic
                one -- at 68px it broke into four lines with awkward turns
                ("We build / software / systems that run / real businesses.").
                60px holds Arabic at two lines and English at three. */}
            <h1 style={rise(90)} className="text-balance text-4xl font-extrabold leading-[1.14] tracking-tight text-foreground sm:text-5xl 2xl:text-[3.75rem]">
              {t("heroTitle")}
            </h1>

            <p style={rise(180)} className="max-w-xl text-pretty text-base leading-relaxed text-muted-foreground">
              {t("heroSubtitle")}
            </p>

            <div style={rise(270)} className="mt-1 flex w-full flex-col gap-3 sm:w-auto sm:flex-row sm:items-center">
              {/* h-14 rather than the default lg (h-13): at this scale the
                  primary action has to hold its own against a full-bleed
                  film, and a 44px-ish button next to a 68px headline reads
                  as an afterthought. */}
              <Button
                asChild
                size="lg"
                className="h-13 px-7 text-base shadow-xl shadow-primary/30"
              >
                <Link href="/start-a-project">
                  {t("heroCtaPrimary")}
                  <ArrowLeft className="size-4 rtl:rotate-180" aria-hidden="true" />
                </Link>
              </Button>
              <Button
                asChild
                size="lg"
                variant="outline"
                className="h-13 border-border/70 bg-surface/30 px-7 text-base backdrop-blur-sm hover:bg-surface/60"
              >
                <Link href="/case-studies">{t("heroCtaSecondary")}</Link>
              </Button>
            </div>

          </div>

          {/*
            The visual. Order matters on small screens: the references put it
            AFTER the CTAs and BEFORE the proof metrics, which is exactly the
            DOM order here, so no order utilities are needed.

            Width: the brief's clamp on desktop, min(88vw,720px) on tablet
            portrait, min(340px, 100vw-32px) on mobile. `mx-auto` centres it
            below lg and `justify-self-end` seats it in its own column at lg.
          */}
          <div className="w-full max-xl:mx-auto xl:col-span-6 xl:col-start-7 xl:row-span-2 xl:row-start-1 xl:self-center xl:justify-self-end">
            {/*
              Width per the brief: clamp(520px, 46vw, 760px) on desktop,
              min(88vw,720px) on tablet portrait, min(340px, 100vw-2rem) on
              mobile.

              The negative inline-end margin is what lets it actually reach
              46-50% of the HERO width. Its grid column is only ~552px at
              1440, so without breaking out of the container's gutter the
              clamp was being capped to 39% -- narrower than the reference,
              which carries the hexagon close to the viewport edge. Logical
              `-me-` so it mirrors in RTL, and the section is `overflow-x-clip`
              so this can never produce a horizontal scrollbar.
            */}
            <HexaTerminalVisual className="mx-auto w-[min(340px,calc(100vw-2rem))] sm:w-[min(88vw,720px)] xl:-me-[clamp(0px,7vw,140px)] xl:w-[clamp(520px,46vw,760px)]" />
          </div>

          {/* Proof metrics: 2x2 below the Hexa on small screens, a 4-up row
              under the copy at lg. `lg:row-start-2` puts them back beneath the
              copy column rather than in a third row of their own. */}
          <div
            style={rise(360)}
            className="grid w-full grid-cols-2 gap-x-8 gap-y-5 border-t border-border/50 pt-6 text-start max-xl:mx-auto max-xl:max-w-2xl xl:col-span-6 xl:col-start-1 xl:row-start-2 xl:grid-cols-4 xl:gap-x-6 xl:self-start"
          >
            {proofPoints.map((point) => (
              <ProofPoint key={point.key} value={point.value} label={point.label} variant={point.variant} />
            ))}
          </div>
        </div>
      </Container>
    </section>
  );
}
