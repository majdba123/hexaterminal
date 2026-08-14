"use client";

import * as React from "react";
import { useTranslations } from "next-intl";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Field, fieldProps } from "@/components/ui/field";
import type { LeadIntent, LeadPayload } from "@/lib/api/types";
import { getAttribution } from "@/lib/attribution";
import { trackEvent } from "@/components/site/analytics-script";
import {
  focusFirstError,
  leadIntentRequiresSummary,
  mapLeadApiValidationErrors,
  validateContactFields,
  type FieldErrors,
} from "@/lib/validate";

type Status = "idle" | "submitting" | "success" | "error" | "invalid";

export function LeadForm({
  locale,
  sourcePage,
  intent = "start_project",
  submitLabel,
  submittingLabel,
}: {
  locale: string;
  sourcePage: string;
  intent?: LeadIntent;
  submitLabel?: string;
  submittingLabel?: string;
}) {
  const t = useTranslations("startProject");
  const tc = useTranslations("common");
  const [status, setStatus] = React.useState<Status>("idle");
  const [errors, setErrors] = React.useState<FieldErrors>({});
  const startedRef = React.useRef(false);
  const summaryRequired = leadIntentRequiresSummary(intent);

  function handleFocusOnce() {
    if (startedRef.current) return;
    startedRef.current = true;
    trackEvent("lead_form_start", { intent, source_page: sourcePage });
  }

  /** Clears a field's error as soon as the user starts correcting it. */
  const clearError = (field: string) => () => {
    setErrors((prev) => {
      if (!prev[field]) return prev;
      const next = { ...prev };
      delete next[field];
      return next;
    });
  };

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();

    // Captured before the first `await` -- the DOM nulls out
    // event.currentTarget once dispatch finishes, so reading it after an
    // await (to call .reset()) would throw and get swallowed below.
    const formEl = event.currentTarget;
    const form = new FormData(formEl);

    // This form is `noValidate`, so HTML validation attributes are only hints
    // for input semantics and assistive tech. Keep this client contract aligned
    // with Laravel before any request leaves the browser.
    const fieldErrors = validateContactFields(
      {
        name: String(form.get("name") ?? ""),
        email: String(form.get("email") ?? ""),
        summary: String(form.get("summary") ?? ""),
      },
      {
        required: tc("fieldRequired"),
        email: tc("fieldEmail"),
        summaryMin: t("formValidationError"),
      },
      intent,
    );

    if (Object.keys(fieldErrors).length > 0) {
      setErrors(fieldErrors);
      setStatus("invalid");
      focusFirstError(formEl, fieldErrors);
      return;
    }

    setErrors({});
    setStatus("submitting");
    const attribution = getAttribution();
    const payload: LeadPayload = {
      intent,
      name: String(form.get("name") ?? ""),
      email: String(form.get("email") ?? ""),
      phone: String(form.get("phone") ?? "") || undefined,
      company: String(form.get("company") ?? "") || undefined,
      country: String(form.get("country") ?? "") || undefined,
      project_type: String(form.get("project_type") ?? "") || undefined,
      budget_range: String(form.get("budget_range") ?? "") || undefined,
      timeline: String(form.get("timeline") ?? "") || undefined,
      summary: String(form.get("summary") ?? "") || undefined,
      source_page: sourcePage,
      landing_page: attribution?.landingPage,
      first_touch_at: attribution?.firstTouchAt,
      utm: attribution?.utm && Object.keys(attribution.utm).length > 0 ? attribution.utm : undefined,
      locale,
      website: String(form.get("website") ?? ""),
    };

    try {
      const res = await fetch("/api/leads", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });

      if (res.status === 422) {
        const apiErrors = mapLeadApiValidationErrors(await res.json().catch(() => null), {
          invalid: t("formValidationError"),
          email: tc("fieldEmail"),
          summary: t("formValidationError"),
        });

        if (Object.keys(apiErrors).length > 0) {
          setErrors(apiErrors);
          setStatus("invalid");
          focusFirstError(formEl, apiErrors);
          return;
        }
      }

      if (!res.ok) throw new Error(`Lead submission failed: ${res.status}`);
      setStatus("success");
      trackEvent("lead_form_submit", { intent, source_page: sourcePage });
      formEl.reset();
    } catch {
      setStatus("error");
    }
  }

  if (status === "success") {
    return (
      <div
        role="status"
        aria-live="polite"
        className="rounded-[var(--radius-lg)] border border-success/30 bg-success/10 p-6 text-sm font-medium text-success"
      >
        {t("formSuccess")}
      </div>
    );
  }

  return (
    <form
      action="/api/leads"
      method="post"
      onSubmit={handleSubmit}
      onFocusCapture={handleFocusOnce}
      className="flex flex-col gap-5"
      noValidate
      aria-busy={status === "submitting"}
    >
      <input type="hidden" name="intent" value={intent} />
      <input type="hidden" name="locale" value={locale} />
      <input type="hidden" name="source_page" value={sourcePage} />
      <input
        type="text"
        name="website"
        tabIndex={-1}
        autoComplete="off"
        className="absolute left-[-9999px] h-0 w-0 opacity-0"
        aria-hidden="true"
      />

      <div className="grid gap-5 sm:grid-cols-2">
        <Field id="name" label={t("formName")} error={errors.name}>
          <Input
            id="name"
            name="name"
            required
            autoComplete="name"
            onChange={clearError("name")}
            {...fieldProps("name", errors.name)}
          />
        </Field>
        <Field id="email" label={t("formEmail")} error={errors.email}>
          <Input
            id="email"
            name="email"
            type="email"
            required
            autoComplete="email"
            onChange={clearError("email")}
            {...fieldProps("email", errors.email)}
          />
        </Field>
        <Field id="phone" label={t("formPhone")} error={errors.phone}>
          <Input
            id="phone"
            name="phone"
            type="tel"
            autoComplete="tel"
            onChange={clearError("phone")}
            {...fieldProps("phone", errors.phone)}
          />
        </Field>
        <Field id="company" label={t("formCompany")} error={errors.company}>
          <Input
            id="company"
            name="company"
            autoComplete="organization"
            onChange={clearError("company")}
            {...fieldProps("company", errors.company)}
          />
        </Field>
        <Field id="country" label={t("formCountry")} error={errors.country}>
          <Input
            id="country"
            name="country"
            autoComplete="country-name"
            onChange={clearError("country")}
            {...fieldProps("country", errors.country)}
          />
        </Field>
        <Field id="project_type" label={t("formProjectType")} error={errors.project_type}>
          <Input
            id="project_type"
            name="project_type"
            placeholder={t("formProjectTypePlaceholder")}
            onChange={clearError("project_type")}
            {...fieldProps("project_type", errors.project_type)}
          />
        </Field>
        <Field id="budget_range" label={t("formBudget")} error={errors.budget_range}>
          <Input
            id="budget_range"
            name="budget_range"
            onChange={clearError("budget_range")}
            {...fieldProps("budget_range", errors.budget_range)}
          />
        </Field>
        <Field id="timeline" label={t("formTimeline")} error={errors.timeline}>
          <Input
            id="timeline"
            name="timeline"
            onChange={clearError("timeline")}
            {...fieldProps("timeline", errors.timeline)}
          />
        </Field>
      </div>

      <Field id="summary" label={t("formSummary")} error={errors.summary}>
        <Textarea
          id="summary"
          name="summary"
          required={summaryRequired}
          minLength={summaryRequired ? 10 : undefined}
          placeholder={t("formSummaryPlaceholder")}
          onChange={clearError("summary")}
          {...fieldProps("summary", errors.summary)}
        />
      </Field>

      {/* Two distinct failures, two distinct messages: `invalid` means the
          user still has something to correct above (the per-field errors are
          the real instruction, this just explains why nothing was sent),
          `error` means the request itself failed. */}
      {status === "invalid" ? (
        <p role="alert" className="text-sm font-medium text-destructive">{t("formValidationError")}</p>
      ) : null}
      {status === "error" ? (
        <p role="alert" className="text-sm font-medium text-destructive">{t("formError")}</p>
      ) : null}

      <Button type="submit" size="lg" disabled={status === "submitting"} className="self-start">
        {status === "submitting" ? (submittingLabel ?? t("formSubmitting")) : (submitLabel ?? t("formSubmit"))}
      </Button>
    </form>
  );
}
