import { NextRequest } from "next/server";

import { badRequest, postUpstream, readJson, relayJson } from "@/lib/api/upstream";

const API_URL = process.env.API_URL ?? "http://localhost:8000/api/v1/public";

/**
 * Server-side proxy for optional contact capture on an estimate. Forwards
 * the Referer so the backend can record it, but never trusts the client for
 * anything the backend re-derives.
 */
export async function POST(request: NextRequest, { params }: { params: Promise<{ uuid: string }> }) {
  const { uuid } = await params;
  const body = await readJson(request);
  if (body === null) return badRequest();

  const referer = request.headers.get("referer");
  const upstream = await postUpstream(
    `${API_URL}/estimates/${encodeURIComponent(uuid)}/lead`,
    request,
    body,
    referer ? { Referer: referer } : undefined,
  );

  return relayJson(upstream);
}
