"use client";

import { useId, useRef, useState } from "react";
import Image from "next/image";
import { cn } from "@/lib/utils";
import { ShotBar } from "./shot";
import { CRMA_SHOTS, crmaSrc, type CrmaShotKey } from "./shots";

export type ModuleTab = { key: CrmaShotKey; name: string; desc: string; alt: string };

/**
 * "The operating system": one window, seven real modules. An ARIA tablist
 * (roving tabindex, arrow/Home/End keys mirrored under RTL, automatic
 * activation). Only the active module's screenshot is in the DOM, so the
 * section costs one image until a visitor asks for another.
 */
export function ModuleViewer({ tabs, label, frameLabel }: { tabs: ModuleTab[]; label: string; frameLabel: string }) {
  const [active, setActive] = useState(0);
  const refs = useRef<(HTMLButtonElement | null)[]>([]);
  const base = useId();
  const current = tabs[active];

  function select(i: number) {
    const next = (i + tabs.length) % tabs.length;
    setActive(next);
    refs.current[next]?.focus();
  }

  function onKeyDown(e: React.KeyboardEvent) {
    const rtl = document.documentElement.dir === "rtl";
    const fwd = rtl ? "ArrowLeft" : "ArrowRight";
    const back = rtl ? "ArrowRight" : "ArrowLeft";
    if (e.key === fwd || e.key === "ArrowDown") select(active + 1);
    else if (e.key === back || e.key === "ArrowUp") select(active - 1);
    else if (e.key === "Home") select(0);
    else if (e.key === "End") select(tabs.length - 1);
    else return;
    e.preventDefault();
  }

  return (
    <div className="grid gap-6 lg:grid-cols-12 lg:gap-8">
      <div
        role="tablist"
        aria-label={label}
        aria-orientation="vertical"
        onKeyDown={onKeyDown}
        className="flex gap-2 overflow-x-auto pb-1 lg:col-span-3 lg:flex-col lg:overflow-visible lg:pb-0"
      >
        {tabs.map((tab, i) => (
          <button
            key={tab.key}
            ref={(el) => {
              refs.current[i] = el;
            }}
            type="button"
            role="tab"
            id={`${base}-tab-${i}`}
            aria-selected={i === active}
            aria-controls={`${base}-panel`}
            tabIndex={i === active ? 0 : -1}
            onClick={() => setActive(i)}
            className={cn(
              "focus-ring flex min-h-11 shrink-0 items-center gap-3 rounded-[var(--radius-md)] border px-4 text-start text-sm font-semibold transition-colors",
              i === active
                ? "border-primary/40 bg-primary/10 text-foreground"
                : "border-border/70 bg-background text-muted-foreground hover:text-foreground",
            )}
          >
            <span className="text-xs tabular-nums text-secondary">{String(i + 1).padStart(2, "0")}</span>
            <span>{tab.name}</span>
          </button>
        ))}
      </div>
      <div role="tabpanel" id={`${base}-panel`} aria-labelledby={`${base}-tab-${active}`} className="min-w-0 lg:col-span-9">
        <figure className="overflow-hidden rounded-[var(--radius-lg)] border border-border/70 bg-surface">
          <ShotBar label={`${frameLabel} · ${current.name}`} />
          <div className="bg-white">
            <Image
              key={current.key}
              src={crmaSrc(current.key)}
              alt={current.alt}
              width={CRMA_SHOTS[current.key].width}
              height={CRMA_SHOTS[current.key].height}
              sizes="(min-width: 1024px) 66vw, 100vw"
              className="block h-auto w-full"
            />
          </div>
        </figure>
        <p className="mt-4 max-w-2xl text-pretty text-sm leading-relaxed text-muted-foreground" aria-live="polite">
          {current.desc}
        </p>
      </div>
    </div>
  );
}
