import { expect, test } from "@playwright/test";

import { resolveVisitorIp } from "../lib/api/visitor-ip";

/**
 * Regression tests for the throttle key the public API proxy forwards to
 * Laravel.
 *
 * These exist because of a real regression: an earlier commit on this branch
 * trusted `CF-Connecting-IP` unconditionally, which is a rate-limit bypass on
 * any deployment whose origin is reachable without Cloudflare in front — a
 * caller rotates the header and gets a fresh `throttle:public-leads` bucket
 * each request. The first test below is the one that matters; it fails if that
 * default is ever flipped back.
 *
 * Run with `npm run test:unit` (no browser, no dev server, no API).
 */

function headers(values: Record<string, string>): Pick<Headers, "get"> {
  const lower = new Map(Object.entries(values).map(([k, v]) => [k.toLowerCase(), v]));
  return { get: (name: string) => lower.get(name.toLowerCase()) ?? null };
}

test.describe("resolveVisitorIp", () => {
  test("ignores CF-Connecting-IP when Cloudflare is not trusted", () => {
    // The security property. Without an enforced Cloudflare-only ingress, a
    // client-supplied CF-Connecting-IP must not become the throttle key.
    const ip = resolveVisitorIp(
      headers({ "cf-connecting-ip": "6.6.6.6", "x-real-ip": "203.0.113.9" }),
      { trustCloudflare: false },
    );

    expect(ip).toBe("203.0.113.9");
  });

  test("falls back to X-Forwarded-For, not CF-Connecting-IP, when untrusted", () => {
    // Same property with no X-Real-IP present: the forged header still must
    // not win.
    const ip = resolveVisitorIp(
      headers({ "cf-connecting-ip": "6.6.6.6", "x-forwarded-for": "6.6.6.7, 203.0.113.9" }),
      { trustCloudflare: false },
    );

    expect(ip).toBe("203.0.113.9");
  });

  test("returns null when untrusted and only CF-Connecting-IP is present", () => {
    // Laravel then keys the throttle on the connection IP it sees itself,
    // which is the pre-Cloudflare behaviour.
    const ip = resolveVisitorIp(headers({ "cf-connecting-ip": "6.6.6.6" }), {
      trustCloudflare: false,
    });

    expect(ip).toBeNull();
  });

  test("prefers CF-Connecting-IP when Cloudflare is trusted", () => {
    // Behind Cloudflare, X-Real-IP is an edge address, so every visitor would
    // otherwise share one bucket.
    const ip = resolveVisitorIp(
      headers({ "cf-connecting-ip": "203.0.113.9", "x-real-ip": "172.70.1.1" }),
      { trustCloudflare: true },
    );

    expect(ip).toBe("203.0.113.9");
  });

  test("uses the right-most X-Forwarded-For hop, never a client-supplied one", () => {
    // Left-most entries are whatever the client sent. Nginx appends the real
    // peer last.
    const ip = resolveVisitorIp(
      headers({ "x-forwarded-for": "1.1.1.1, 2.2.2.2, 203.0.113.9" }),
      { trustCloudflare: false },
    );

    expect(ip).toBe("203.0.113.9");
  });

  test("prefers X-Real-IP over X-Forwarded-For", () => {
    const ip = resolveVisitorIp(
      headers({ "x-real-ip": "203.0.113.9", "x-forwarded-for": "1.1.1.1" }),
      { trustCloudflare: false },
    );

    expect(ip).toBe("203.0.113.9");
  });

  test("ignores blank and whitespace-only header values", () => {
    expect(
      resolveVisitorIp(headers({ "x-real-ip": "   ", "x-forwarded-for": "203.0.113.9" }), {
        trustCloudflare: false,
      }),
    ).toBe("203.0.113.9");

    expect(
      resolveVisitorIp(headers({ "cf-connecting-ip": "  ", "x-real-ip": "203.0.113.9" }), {
        trustCloudflare: true,
      }),
    ).toBe("203.0.113.9");
  });

  test("trims surrounding whitespace", () => {
    expect(
      resolveVisitorIp(headers({ "x-real-ip": "  203.0.113.9  " }), { trustCloudflare: false }),
    ).toBe("203.0.113.9");
  });

  test("returns null with no usable headers", () => {
    expect(resolveVisitorIp(headers({}), { trustCloudflare: true })).toBeNull();
    expect(resolveVisitorIp(headers({ "x-forwarded-for": " , , " }), {})).toBeNull();
  });

  test("defaults to not trusting Cloudflare", () => {
    // The default must come from the environment, and TRUST_CLOUDFLARE_CLIENT_IP
    // is unset here — so the forged header must lose without being told to.
    const ip = resolveVisitorIp(
      headers({ "cf-connecting-ip": "6.6.6.6", "x-real-ip": "203.0.113.9" }),
    );

    expect(ip).toBe("203.0.113.9");
  });
});
