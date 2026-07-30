import { NextRequest, NextResponse } from "next/server";
import { revalidatePath } from "next/cache";
import { timingSafeEqual } from "node:crypto";
import { routing } from "@/i18n/routing";

/**
 * Secure on-demand revalidation. Called server-to-server by Laravel when CMS
 * content is published/updated/deleted (see app/Services/RevalidationService.php)
 * so a newly published slug appears without a full redeploy, instead of waiting
 * for the 5-minute ISR window.
 *
 * Security model:
 *  - Shared-secret auth via the `x-revalidate-secret` header, compared in
 *    constant time. If REVALIDATE_SECRET is unset the endpoint is disabled
 *    (503) -- it is never open.
 *  - Best-effort replay resistance: an optional `ts` (unix seconds) in the
 *    body is rejected if older than REPLAY_WINDOW_S.
 *  - Per-process rate limiting to blunt abuse if the secret ever leaks.
 *  - The secret lives only in server env; it is never referenced from client
 *    code. This route runs on the server only.
 *
 * The endpoint is intentionally forgiving about *what* to revalidate (unknown
 * resources are a 400, not a crash) and always fails closed on auth.
 */
export const dynamic = "force-dynamic";

const SECRET = process.env.REVALIDATE_SECRET;
const REPLAY_WINDOW_S = 300;

// Map an API resource name to the frontend route segment that renders it.
// `null` means "no dedicated list/detail route" (handled specially).
const RESOURCE_ROUTES: Record<string, string> = {
  services: "services",
  systems: "systems",
  "case-studies": "case-studies",
  industries: "industries",
  articles: "insights", // articles are published under /insights
};

type RevalidateBody = {
  resource?: string;
  slug?: string;
  paths?: string[];
  ts?: number;
};

// --- tiny in-process fixed-window rate limiter -----------------------------
const RATE_LIMIT = 60; // requests
const RATE_WINDOW_MS = 60_000;
let windowStart = Date.now();
let windowCount = 0;

function rateLimited(): boolean {
  const now = Date.now();
  if (now - windowStart > RATE_WINDOW_MS) {
    windowStart = now;
    windowCount = 0;
  }
  windowCount += 1;
  return windowCount > RATE_LIMIT;
}

function secretMatches(provided: string | null): boolean {
  if (!SECRET || !provided) return false;
  const a = Buffer.from(provided);
  const b = Buffer.from(SECRET);
  if (a.length !== b.length) return false;
  return timingSafeEqual(a, b);
}

function revalidateResource(resource: string, slug?: string): string[] {
  const revalidated: string[] = [];
  const segment = RESOURCE_ROUTES[resource];

  for (const locale of routing.locales) {
    if (resource === "home") {
      revalidatePath(`/${locale}`);
      revalidated.push(`/${locale}`);
      continue;
    }
    if (!segment) continue;

    // List page (e.g. /en/systems) always refreshes.
    revalidatePath(`/${locale}/${segment}`);
    revalidated.push(`/${locale}/${segment}`);

    // Detail page for the specific slug, if given.
    if (slug) {
      revalidatePath(`/${locale}/${segment}/${slug}`);
      revalidated.push(`/${locale}/${segment}/${slug}`);
    }
  }

  // Home aggregates most content types, and the sitemap lists them all.
  if (resource !== "home") {
    for (const locale of routing.locales) {
      revalidatePath(`/${locale}`);
      revalidated.push(`/${locale}`);
    }
    revalidatePath("/sitemap.xml");
    revalidated.push("/sitemap.xml");
  }

  return Array.from(new Set(revalidated));
}

export async function POST(request: NextRequest) {
  if (!SECRET) {
    // Disabled rather than open: no secret configured => refuse.
    return NextResponse.json(
      { revalidated: false, error: "revalidation_disabled" },
      { status: 503 },
    );
  }

  if (!secretMatches(request.headers.get("x-revalidate-secret"))) {
    return NextResponse.json(
      { revalidated: false, error: "unauthorized" },
      { status: 401 },
    );
  }

  if (rateLimited()) {
    return NextResponse.json(
      { revalidated: false, error: "rate_limited" },
      { status: 429 },
    );
  }

  let body: RevalidateBody;
  try {
    body = (await request.json()) as RevalidateBody;
  } catch {
    return NextResponse.json(
      { revalidated: false, error: "invalid_json" },
      { status: 400 },
    );
  }

  // Best-effort replay window.
  if (typeof body.ts === "number") {
    const ageS = Math.abs(Date.now() / 1000 - body.ts);
    if (ageS > REPLAY_WINDOW_S) {
      return NextResponse.json(
        { revalidated: false, error: "stale_request" },
        { status: 400 },
      );
    }
  }

  const revalidated: string[] = [];

  // Explicit path list (escape hatch for one-offs / sitemap).
  if (Array.isArray(body.paths)) {
    for (const p of body.paths) {
      if (typeof p === "string" && p.startsWith("/") && p.length <= 1024) {
        revalidatePath(p);
        revalidated.push(p);
      }
    }
  }

  if (body.resource) {
    const known = body.resource === "home" || body.resource in RESOURCE_ROUTES;
    if (!known) {
      return NextResponse.json(
        { revalidated: false, error: "unknown_resource" },
        { status: 400 },
      );
    }
    revalidated.push(...revalidateResource(body.resource, body.slug));
  }

  if (revalidated.length === 0) {
    return NextResponse.json(
      { revalidated: false, error: "nothing_to_revalidate" },
      { status: 400 },
    );
  }

  return NextResponse.json({
    revalidated: true,
    paths: Array.from(new Set(revalidated)),
    now: Date.now(),
  });
}
