import { NextRequest } from "next/server";

import { badRequest, postUpstream, readJson, relayJson } from "@/lib/api/upstream";

const API_URL = process.env.API_URL ?? "http://localhost:8000/api/v1/public";

/**
 * Server-side proxy for creating a cost estimate. Keeps the Laravel origin
 * a server-only concern (consistent with /api/leads). The estimate is
 * computed and persisted by Laravel; the frontend never calculates a price.
 */
export async function POST(request: NextRequest) {
  const body = await readJson(request);
  if (body === null) return badRequest();

  return relayJson(await postUpstream(`${API_URL}/estimates`, request, body));
}
