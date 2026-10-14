import Image from "next/image";
import { cn } from "@/lib/utils";
import { CRMA_SHOTS, crmaSrc, type CrmaShotKey } from "./shots";

/**
 * A real CRMA screen in a neutral window frame. The frame label is the screen
 * name only -- no domain, real or invented -- matching the source captures.
 * The screenshots are light UI, so the media well stays white in both themes.
 */
export function Shot({
  shot,
  alt,
  label,
  sizes = "(min-width: 1024px) 60vw, 100vw",
  priority = false,
  className,
  dir,
}: {
  shot: CrmaShotKey;
  alt: string;
  label?: string;
  sizes?: string;
  priority?: boolean;
  className?: string;
  dir?: "ltr" | "rtl";
}) {
  const meta = CRMA_SHOTS[shot];
  return (
    <figure
      className={cn(
        "overflow-hidden rounded-[var(--radius-lg)] border border-border/70 bg-surface shadow-[0_24px_60px_-28px_hsl(var(--shadow-color)/0.6)]",
        className,
      )}
    >
      {label ? <ShotBar label={label} dir={dir} /> : null}
      <div className="bg-white">
        <Image
          src={crmaSrc(shot)}
          alt={alt}
          width={meta.width}
          height={meta.height}
          sizes={sizes}
          priority={priority}
          className="block h-auto w-full"
        />
      </div>
    </figure>
  );
}

export function ShotBar({ label, dir }: { label: string; dir?: "ltr" | "rtl" }) {
  return (
    <figcaption
      dir={dir ?? "auto"}
      className="flex min-h-9 items-center gap-3 border-b border-border/70 bg-muted px-3 text-xs font-medium text-muted-foreground"
    >
      <span aria-hidden="true" className="flex shrink-0 gap-1.5">
        <span className="size-2 rounded-full bg-muted-foreground/30" />
        <span className="size-2 rounded-full bg-muted-foreground/30" />
        <span className="size-2 rounded-full bg-muted-foreground/30" />
      </span>
      <span className="truncate">{label}</span>
    </figcaption>
  );
}
