import { NextRequest, NextResponse } from "next/server";

import { badRequest, postUpstream, readJson, relayJson } from "@/lib/api/upstream";

const API_URL = process.env.API_URL ?? "http://localhost:8000/api/v1/public";

const NATIVE_LEAD_FIELDS = [
  "intent",
  "name",
  "email",
  "phone",
  "company",
  "country",
  "project_type",
  "budget_range",
  "timeline",
  "summary",
  "source_page",
  "locale",
  "website",
] as const;

function nativeRedirect(request: NextRequest, form: FormData, outcome: "success" | "error") {
  const locale = form.get("locale") === "ar" ? "ar" : "en";
  const sourcePage = form.get("source_page") === "/contact" ? "/contact" : "/start-a-project";
  const url = new URL(`/${locale}${sourcePage}`, request.url);
  url.searchParams.set("lead", outcome);
  return NextResponse.redirect(url, 303);
}

/**
 * Server-side proxy so the "Start a Project" client form never needs to
 * know the Laravel origin directly (avoids CORS config and keeps the
 * backend URL a server-only concern, consistent with lib/api/client.ts).
 */
export async function POST(request: NextRequest) {
  const isNativeForm = request.headers.get("content-type")?.startsWith("application/x-www-form-urlencoded") ?? false;
  const form = isNativeForm ? await request.formData() : null;
  const body = form
    ? NATIVE_LEAD_FIELDS.reduce<Record<string, string>>((payload, field) => {
        const value = form.get(field);
        if (typeof value === "string") payload[field] = value;
        return payload;
      }, {})
    : await readJson(request);
  if (body === null) return badRequest();

  const upstream = await postUpstream(`${API_URL}/leads`, request, body);

  if (form) {
    return nativeRedirect(request, form, upstream?.ok ? "success" : "error");
  }

  return relayJson(upstream);
}
