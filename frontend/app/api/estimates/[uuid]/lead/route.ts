import { NextRequest, NextResponse } from "next/server";

const API_URL = process.env.API_URL ?? "http://localhost:8000/api/v1/public";

/**
 * Server-side proxy for optional contact capture on an estimate. Forwards
 * the Referer so the backend can record it, but never trusts the client for
 * anything the backend re-derives.
 */
export async function POST(request: NextRequest, { params }: { params: Promise<{ uuid: string }> }) {
  const { uuid } = await params;
  const body = await request.json();

  const upstream = await fetch(`${API_URL}/estimates/${encodeURIComponent(uuid)}/lead`, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
      ...(request.headers.get("referer") ? { Referer: request.headers.get("referer")! } : {}),
    },
    body: JSON.stringify(body),
  });

  const data = await upstream.json();
  return NextResponse.json(data, { status: upstream.status });
}
