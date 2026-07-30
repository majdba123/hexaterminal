import { NextResponse } from "next/server";

/**
 * Frontend health endpoint. Confirms the Next.js server process is up and,
 * as observability (not a liveness gate), whether the Laravel API is
 * reachable from the server. Never exposes the internal API URL or any other
 * configuration -- only booleans and the build SHA.
 *
 * Not cached (Route Handlers are uncached by default; `dynamic` makes that
 * explicit so a probe always reflects current state).
 */
export const dynamic = "force-dynamic";

const API_URL = process.env.API_URL ?? "http://localhost:8000/api/v1/public";
// Derive the API origin's health URL from the versioned base (…/api/v1/public
// -> …/api/health). Kept server-side; never sent to the browser.
const API_HEALTH_URL = API_URL.replace(/\/api\/v1\/public\/?$/, "/api/health");

async function apiReachable(): Promise<boolean> {
  try {
    const controller = new AbortController();
    const timer = setTimeout(() => controller.abort(), 2000);
    const res = await fetch(API_HEALTH_URL, {
      cache: "no-store",
      signal: controller.signal,
    });
    clearTimeout(timer);
    return res.ok;
  } catch {
    return false;
  }
}

export async function GET() {
  const api = await apiReachable();

  return NextResponse.json(
    {
      status: "ok",
      service: "hexa-terminal-frontend",
      version: process.env.APP_VERSION ?? process.env.NEXT_PUBLIC_COMMIT_SHA ?? "unknown",
      indexingEnabled: process.env.NEXT_PUBLIC_ALLOW_INDEXING === "true",
      checks: { api },
      time: new Date().toISOString(),
    },
    { headers: { "Cache-Control": "no-store" } },
  );
}
