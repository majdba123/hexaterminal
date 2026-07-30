import { NextRequest, NextResponse } from "next/server";

const API_URL = process.env.API_URL ?? "http://localhost:8000/api/v1/public";

/**
 * Server-side proxy so the "Start a Project" client form never needs to
 * know the Laravel origin directly (avoids CORS config and keeps the
 * backend URL a server-only concern, consistent with lib/api/client.ts).
 */
export async function POST(request: NextRequest) {
  const body = await request.json();

  const upstream = await fetch(`${API_URL}/leads`, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(body),
  });

  const data = await upstream.json();

  return NextResponse.json(data, { status: upstream.status });
}
