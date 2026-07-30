"use client";

/**
 * First-touch marketing attribution, captured client-side and persisted for
 * the session only (sessionStorage -- cleared when the tab closes; no
 * cross-session tracking, no fingerprinting). Captures on first landing and
 * is never overwritten by a later visit within the same session, so a lead
 * submitted several pages into a visit still credits the original UTM/referrer.
 */
const STORAGE_KEY = "ht-attribution";

export interface Attribution {
  landingPage: string;
  referrer: string;
  utm: Record<string, string>;
  firstTouchAt: string;
}

const UTM_KEYS = ["utm_source", "utm_medium", "utm_campaign", "utm_term", "utm_content"] as const;

/** Call once near app start (see AttributionCapture). No-op after first landing this session. */
export function captureAttribution(): void {
  if (typeof window === "undefined") return;
  if (window.sessionStorage.getItem(STORAGE_KEY)) return;

  const params = new URLSearchParams(window.location.search);
  const utm: Record<string, string> = {};
  for (const key of UTM_KEYS) {
    const value = params.get(key);
    if (value) utm[key.replace("utm_", "")] = value;
  }

  const attribution: Attribution = {
    landingPage: window.location.pathname,
    referrer: document.referrer || "",
    utm,
    firstTouchAt: new Date().toISOString(),
  };

  window.sessionStorage.setItem(STORAGE_KEY, JSON.stringify(attribution));
}

export function getAttribution(): Attribution | null {
  if (typeof window === "undefined") return null;
  const raw = window.sessionStorage.getItem(STORAGE_KEY);
  if (!raw) return null;
  try {
    return JSON.parse(raw) as Attribution;
  } catch {
    return null;
  }
}
