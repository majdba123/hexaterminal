import { NextResponse } from "next/server";

/**
 * Frontend health endpoint. Confirms the Next.js server process is up without
 * depending on Laravel or any external system.
 */
export const dynamic = "force-dynamic";

export async function GET() {
  return NextResponse.json(
    {
      status: "ok",
      service: "hexaterminal-frontend",
    },
    { headers: { "Cache-Control": "no-store" } },
  );
}
