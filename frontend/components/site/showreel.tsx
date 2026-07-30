"use client";

import { useState } from "react";
import Image from "next/image";
import { Play } from "lucide-react";
import { useTranslations } from "next-intl";
import { Dialog, DialogContent, DialogTitle, DialogTrigger } from "@/components/ui/dialog";
import { SectionHeading } from "@/components/site/section-heading";
import { Container } from "@/components/site/container";
import { trackEvent } from "@/components/site/analytics-script";

/**
 * Hexa Terminal Reels.mp4 -- 1080x1920 vertical, 29s, silent, 5.7MB.
 * Poster + modal (Option C+E): never eager-loaded, no autoplay in the
 * page flow -- the 5.7MB source only downloads once a visitor opens it.
 */
export function Showreel() {
  const t = useTranslations("home");
  const tc = useTranslations("common");
  const [open, setOpen] = useState(false);

  return (
    <section className="bg-surface py-16 sm:py-24">
      <Container>
        <SectionHeading
          badge={t("showreelBadge")}
          title={t("showreelTitle")}
          subtitle={t("showreelSubtitle")}
        />
        <Dialog
          open={open}
          onOpenChange={(next) => {
            setOpen(next);
            if (next) trackEvent("showreel_open");
          }}
        >
          <DialogTrigger
            className="focus-ring group relative mx-auto flex aspect-9/16 w-full max-w-xs items-center justify-center overflow-hidden rounded-[var(--radius-xl)] border border-border shadow-xl"
            aria-label={tc("watchShowreel")}
          >
            <Image
              src="/media/showreel-poster.jpg"
              alt=""
              fill
              className="object-cover transition-transform duration-200 ease-out group-hover:scale-105"
              sizes="320px"
            />
            <span className="absolute inset-0 bg-black/30 transition-colors group-hover:bg-black/40" />
            <span className="relative flex size-16 items-center justify-center rounded-full bg-white/90 text-black shadow-lg">
              <Play className="ms-1 size-6 fill-current" />
            </span>
          </DialogTrigger>
          <DialogContent closeLabel={tc("close")} className="max-w-sm bg-black p-0">
            <DialogTitle className="sr-only">{tc("watchShowreel")}</DialogTitle>
            {open ? (
              <video
                className="aspect-9/16 w-full rounded-[var(--radius-lg)]"
                controls
                autoPlay
                muted
                playsInline
                preload="none"
                poster="/media/showreel-poster.jpg"
              >
                <source src="/media/showreel.mp4" type="video/mp4" />
              </video>
            ) : null}
          </DialogContent>
        </Dialog>
      </Container>
    </section>
  );
}
