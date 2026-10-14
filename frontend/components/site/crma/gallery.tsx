"use client";

import { useRef, useState } from "react";
import Image from "next/image";
import { ChevronLeft, ChevronRight, Maximize2 } from "lucide-react";
import { Dialog, DialogContent, DialogTitle } from "@/components/ui/dialog";
import { CRMA_SHOTS, crmaSrc, type CrmaShotKey } from "./shots";

export type GalleryItem = { key: CrmaShotKey; caption: string; alt: string };

/**
 * Secondary screens as thumbnails; each opens a full-size viewer built on the
 * shared Dialog (focus trap, Escape, focus return, aria-modal). The viewer
 * adds previous/next buttons and arrow keys, mirrored under RTL. Thumbnails
 * lazy-load; the full-size image loads only when the viewer opens.
 */
export function CrmaGallery({
  items,
  labels,
}: {
  items: GalleryItem[];
  labels: { open: string; close: string; previous: string; next: string; counter: string };
}) {
  const [open, setOpen] = useState<number | null>(null);
  // The Dialog is controlled and has no Trigger, so Radix cannot return focus
  // on its own: remember the thumbnail that opened it and restore focus there.
  const opener = useRef<HTMLButtonElement | null>(null);
  const openers = useRef<(HTMLButtonElement | null)[]>([]);
  const current = open === null ? null : items[open];

  function step(delta: number) {
    setOpen((i) => (i === null ? i : (i + delta + items.length) % items.length));
  }

  function onKeyDown(e: React.KeyboardEvent) {
    const rtl = document.documentElement.dir === "rtl";
    if (e.key === "ArrowRight") step(rtl ? -1 : 1);
    else if (e.key === "ArrowLeft") step(rtl ? 1 : -1);
    else return;
    e.preventDefault();
  }

  return (
    <>
      <ul className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
        {items.map((item, i) => (
          <li key={item.key}>
            <button
              type="button"
              ref={(el) => {
                openers.current[i] = el;
              }}
              onClick={() => {
                opener.current = openers.current[i];
                setOpen(i);
              }}
              aria-label={`${labels.open}: ${item.caption}`}
              className="focus-ring group block w-full rounded-[var(--radius-lg)] text-start"
            >
              <span className="block overflow-hidden rounded-[var(--radius-lg)] border border-border/70 bg-white transition-colors group-hover:border-secondary/50">
                <Image
                  src={crmaSrc(item.key)}
                  alt=""
                  width={CRMA_SHOTS[item.key].width}
                  height={CRMA_SHOTS[item.key].height}
                  sizes="(min-width: 1024px) 30vw, (min-width: 640px) 46vw, 100vw"
                  className="block h-auto w-full"
                />
              </span>
              <span className="mt-3 flex items-start justify-between gap-3">
                <span className="text-sm font-medium text-foreground">{item.caption}</span>
                <Maximize2 aria-hidden="true" className="mt-0.5 size-4 shrink-0 text-muted-foreground" />
              </span>
            </button>
          </li>
        ))}
      </ul>

      <Dialog open={open !== null} onOpenChange={(o) => !o && setOpen(null)}>
        <DialogContent
          closeLabel={labels.close}
          aria-describedby={undefined}
          onKeyDown={onKeyDown}
          onCloseAutoFocus={(e) => {
            e.preventDefault();
            opener.current?.focus();
          }}
          className="max-h-[94svh] max-w-[min(96vw,1600px)] overflow-auto p-3"
        >
          {current ? (
            <>
              <div className="flex flex-wrap items-center justify-between gap-3 px-1 pb-3 pe-10">
                <DialogTitle className="text-sm font-semibold text-foreground">{current.caption}</DialogTitle>
                <div className="flex items-center gap-1">
                  <span className="me-2 text-xs tabular-nums text-muted-foreground">
                    {labels.counter.replace("{n}", String((open ?? 0) + 1)).replace("{total}", String(items.length))}
                  </span>
                  <button
                    type="button"
                    onClick={() => step(-1)}
                    aria-label={labels.previous}
                    className="focus-ring flex size-11 items-center justify-center rounded-full text-foreground hover:bg-muted"
                  >
                    <ChevronLeft aria-hidden="true" className="size-5 rtl:rotate-180" />
                  </button>
                  <button
                    type="button"
                    onClick={() => step(1)}
                    aria-label={labels.next}
                    className="focus-ring flex size-11 items-center justify-center rounded-full text-foreground hover:bg-muted"
                  >
                    <ChevronRight aria-hidden="true" className="size-5 rtl:rotate-180" />
                  </button>
                </div>
              </div>
              <div className="flex justify-center rounded-[var(--radius-md)] bg-white">
                <Image
                  key={current.key}
                  src={crmaSrc(current.key)}
                  alt={current.alt}
                  width={CRMA_SHOTS[current.key].width}
                  height={CRMA_SHOTS[current.key].height}
                  sizes="96vw"
                  className="block h-auto max-h-[78svh] w-auto max-w-full"
                />
              </div>
            </>
          ) : null}
        </DialogContent>
      </Dialog>
    </>
  );
}
