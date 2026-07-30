"use client";

import { useEffect } from "react";
import { trackEvent } from "@/components/site/analytics-script";

/** Renders nothing; fires one analytics event on mount (page view of a specific content item). */
export function ViewTracker({ event, slug }: { event: string; slug: string }) {
  useEffect(() => {
    trackEvent(event, { slug });
  }, [event, slug]);

  return null;
}
