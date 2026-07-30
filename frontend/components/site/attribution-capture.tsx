"use client";

import { useEffect } from "react";
import { captureAttribution } from "@/lib/attribution";

/** Renders nothing; captures first-touch attribution once per session. */
export function AttributionCapture() {
  useEffect(() => {
    captureAttribution();
  }, []);

  return null;
}
