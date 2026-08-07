import type { LeadIntent } from "@/lib/api/types";

/**
 * Client-side validation for the shared public lead form.
 *
 * The form carries `noValidate` so the error presentation is ours (inline,
 * translated, next to the field) rather than the browser's native bubbles.
 * That means HTML attributes such as `required`, `type="email"`, and
 * `minLength` are accessibility/input hints only; this module enforces the
 * client contract before the request is sent.
 */

export type FieldErrors = Record<string, string>;

/** Deliberately permissive: enough to catch typos, not a deliverability check. */
const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;
const SUMMARY_MIN_LENGTH = 10;
const SUMMARY_REQUIRED_INTENTS = new Set<LeadIntent>([
  "start_project",
  "request_quote",
  "general_contact",
]);

const API_ERROR_FIELDS = [
  "name",
  "email",
  "phone",
  "company",
  "country",
  "project_type",
  "budget_range",
  "timeline",
  "summary",
] as const;

function isRecord(value: unknown): value is Record<string, unknown> {
  return typeof value === "object" && value !== null && !Array.isArray(value);
}

export function leadIntentRequiresSummary(intent: LeadIntent): boolean {
  return SUMMARY_REQUIRED_INTENTS.has(intent);
}

export function validateContactFields(
  values: { name: string; email: string; summary?: string },
  messages: { required: string; email: string; summaryMin: string },
  intent: LeadIntent = "start_project",
): FieldErrors {
  const errors: FieldErrors = {};

  if (!values.name.trim()) errors.name = messages.required;

  if (!values.email.trim()) {
    errors.email = messages.required;
  } else if (!EMAIL.test(values.email.trim())) {
    errors.email = messages.email;
  }

  if (leadIntentRequiresSummary(intent)) {
    const summary = (values.summary ?? "").trim();
    if (!summary) {
      errors.summary = messages.required;
    } else if (summary.length < SUMMARY_MIN_LENGTH) {
      errors.summary = messages.summaryMin;
    }
  }

  return errors;
}

/**
 * Laravel intentionally returns a normal validation bag on HTTP 422. We only
 * use the field names from that response and replace server-provided prose
 * with our own translated messages, so Arabic pages never expose raw English
 * validator text. Unknown fields are ignored and fall back to the generic
 * request error in the caller.
 */
export function mapLeadApiValidationErrors(
  payload: unknown,
  messages: { invalid: string; email: string; summary: string },
): FieldErrors {
  if (!isRecord(payload) || !isRecord(payload.errors)) return {};

  const errors: FieldErrors = {};
  for (const field of API_ERROR_FIELDS) {
    if (!Object.prototype.hasOwnProperty.call(payload.errors, field)) continue;

    if (field === "email") {
      errors[field] = messages.email;
    } else if (field === "summary") {
      errors[field] = messages.summary;
    } else {
      errors[field] = messages.invalid;
    }
  }

  return errors;
}

/**
 * Moves keyboard focus to the first field with an error so a keyboard or
 * screen-reader user is taken to the problem instead of having to hunt for it.
 * Field order comes from the form's own DOM order, not the errors object.
 */
export function focusFirstError(form: HTMLFormElement, errors: FieldErrors): void {
  for (const element of Array.from(form.elements)) {
    const name = (element as HTMLInputElement).name;
    if (name && errors[name]) {
      (element as HTMLInputElement).focus();
      return;
    }
  }
}
