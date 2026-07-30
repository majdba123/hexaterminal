import Script from "next/script";

/**
 * Privacy-conscious analytics boundary: renders ONE configured provider
 * script, or nothing at all. No provider configured => no analytics, no
 * fake data, no third-party request. Never installs multiple providers.
 *
 * Configure via env (server-read at build time, safe to expose):
 *   NEXT_PUBLIC_ANALYTICS_PROVIDER=plausible|umami
 *   NEXT_PUBLIC_ANALYTICS_DOMAIN   (plausible: site domain)
 *   NEXT_PUBLIC_ANALYTICS_SRC      (script src, e.g. your Plausible/Umami host)
 *   NEXT_PUBLIC_ANALYTICS_SITE_ID  (umami: website id)
 */
export function AnalyticsScript() {
  const provider = process.env.NEXT_PUBLIC_ANALYTICS_PROVIDER;
  const src = process.env.NEXT_PUBLIC_ANALYTICS_SRC;

  if (!provider || !src) return null;

  if (provider === "plausible") {
    return (
      <Script
        defer
        data-domain={process.env.NEXT_PUBLIC_ANALYTICS_DOMAIN}
        src={src}
        strategy="afterInteractive"
      />
    );
  }

  if (provider === "umami") {
    return (
      <Script
        defer
        data-website-id={process.env.NEXT_PUBLIC_ANALYTICS_SITE_ID}
        src={src}
        strategy="afterInteractive"
      />
    );
  }

  return null;
}

/**
 * Fire a marketing event to whichever provider is active. No-op (never
 * throws, never fakes data) when no provider is configured or the provider's
 * global function hasn't loaded yet.
 */
export function trackEvent(name: string, props?: Record<string, string | number | boolean>): void {
  if (typeof window === "undefined") return;

  const w = window as typeof window & {
    plausible?: (name: string, opts?: { props?: Record<string, unknown> }) => void;
    umami?: { track: (name: string, props?: Record<string, unknown>) => void };
  };

  try {
    w.plausible?.(name, props ? { props } : undefined);
    w.umami?.track(name, props);
  } catch {
    // Analytics must never break the page.
  }
}
