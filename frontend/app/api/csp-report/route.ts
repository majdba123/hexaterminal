import { NextResponse } from "next/server";

/**
 * CSP violation-reporting boundary (`report-uri` in lib/csp.ts). Browsers
 * POST a `application/csp-report` (or `application/reports+json`) body here
 * whenever a directive blocks something -- this is how a Report-Only
 * rollout is actually evaluated before switching to enforced. Logs to the
 * server console/log pipeline only; never echoes the report back, never
 * stores it in a public-readable location, and always responds 204 so a
 * misconfigured reporter can't retry-storm the endpoint.
 *
 * This endpoint is UNAUTHENTICATED by necessity -- the browser posts it, so it
 * cannot carry a secret. That makes it an anonymous write into the log
 * pipeline, so the body is capped before anything is logged. Without a cap,
 * anyone on the internet can POST an arbitrarily large body and have it
 * written to logs verbatim: disk exhaustion and log-ingestion cost for the
 * price of a curl loop. (The 204-always behaviour above guards against a
 * *misconfigured* reporter; it does nothing against a deliberate flood.)
 * A genuine violation report is well under 4 KB.
 */
export const dynamic = "force-dynamic";

/** Generous for a real report, far too small to be a useful flood vector. */
const MAX_REPORT_BYTES = 8 * 1024;

export async function POST(request: Request) {
  // Attacker-supplied, so this only lets us reject early; the byte count of
  // what we actually read below is the real enforcement.
  const declaredLength = Number(request.headers.get("content-length") ?? "0");
  if (Number.isFinite(declaredLength) && declaredLength > MAX_REPORT_BYTES) {
    return new NextResponse(null, { status: 204 });
  }

  try {
    const raw = await request.text();

    // A chunked request carries no usable Content-Length, so this is the
    // check that actually bounds what can reach the log.
    if (new TextEncoder().encode(raw).length > MAX_REPORT_BYTES) {
      return new NextResponse(null, { status: 204 });
    }

    // Parse before logging: an unparseable body is not a report, and logging
    // raw text would let a caller write arbitrary bytes into the log stream.
    // Re-serialising the parsed value also escapes any newlines it contained.
    console.warn("[csp-report]", JSON.stringify(JSON.parse(raw)));
  } catch {
    // Malformed report body -- nothing to log, still acknowledge receipt.
  }

  return new NextResponse(null, { status: 204 });
}
