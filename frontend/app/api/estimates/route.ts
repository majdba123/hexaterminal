import { NextRequest, NextResponse } from "next/server";

const API_URL = process.env.API_URL ?? "http://localhost:8000/api/v1/public";

/**
 * Server-side proxy for creating a cost estimate. Keeps the Laravel origin
 * a server-only concern (consistent with /api/leads). The estimate is
 * computed and persisted by Laravel; the frontend never calculates a price.
 */
export async function POST(request: NextRequest) {
  const body = await request.json();

  const upstream = await fetch(`${API_URL}/estimates`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });

  const data = await upstream.json();
  return NextResponse.json(data, { status: upstream.status });
}
