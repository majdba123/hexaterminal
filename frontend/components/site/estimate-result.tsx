"use client";

import * as React from "react";
import { useTranslations } from "next-intl";
import { Link } from "@/i18n/navigation";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import { Field, fieldProps } from "@/components/ui/field";
import { trackEvent } from "@/components/site/analytics-script";
import { getAttribution } from "@/lib/attribution";
import { focusFirstError, validateContactFields, type FieldErrors } from "@/lib/validate";
import type { CostEstimateResult, EstimateLeadPayload } from "@/lib/api/types";

type Action = NonNullable<EstimateLeadPayload["requested_action"]>;
type Status = "idle" | "form" | "submitting" | "success" | "error" | "invalid";

const fmt = (n: number) => new Intl.NumberFormat("en-US").format(n);

export function EstimateResult({
  locale,
  estimate,
}: {
  locale: string;
  estimate: CostEstimateResult;
}) {
  const t = useTranslations("estimator");
  const tc = useTranslations("common");
  const [status, setStatus] = React.useState<Status>("idle");
  const [action, setAction] = React.useState<Action>("email_estimate");
  const [errors, setErrors] = React.useState<FieldErrors>({});

  const clearError = (field: string) => () => {
    setErrors((prev) => {
      if (!prev[field]) return prev;
      const next = { ...prev };
      delete next[field];
      return next;
    });
  };

  React.useEffect(() => {
    trackEvent("estimate_result_viewed");
  }, []);

  function chooseAction(next: Action) {
    setAction(next);
    setStatus("form");
    if (next === "book_call") trackEvent("discovery_call_clicked");
    if (next === "request_proposal") trackEvent("proposal_requested");
    if (next === "email_estimate") trackEvent("estimate_email_requested");
  }

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    const formEl = event.currentTarget;
    const form = new FormData(formEl);

    // See lead-form.tsx: this form is `noValidate`, so the required/email
    // attributes are hints, not enforcement. Validate before we POST.
    const fieldErrors = validateContactFields(
      {
        name: String(form.get("name") ?? ""),
        email: String(form.get("email") ?? ""),
      },
      { required: tc("fieldRequired"), email: tc("fieldEmail") },
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

    const payload: EstimateLeadPayload = {
      name: String(form.get("name") ?? ""),
      email: String(form.get("email") ?? ""),
      phone: String(form.get("phone") ?? "") || undefined,
      company: String(form.get("company") ?? "") || undefined,
      summary: String(form.get("summary") ?? "") || undefined,
      requested_action: action,
      source_page: `/estimate/${estimate.public_uuid}`,
      landing_page: attribution?.landingPage,
      first_touch_at: attribution?.firstTouchAt,
      utm: attribution?.utm && Object.keys(attribution.utm).length > 0 ? attribution.utm : undefined,
      locale,
      website: String(form.get("website") ?? ""),
    };

    try {
      const res = await fetch(`/api/estimates/${estimate.public_uuid}/lead`, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload),
      });
      if (!res.ok) throw new Error(`Lead failed: ${res.status}`);
      setStatus("success");
    } catch {
      setStatus("error");
    }
  }

  const actions: Action[] = ["email_estimate", "book_call", "request_proposal", "start_project", "ask_question"];

  return (
    <div className="mx-auto max-w-3xl">
      {/* Result headline */}
      <div className="rounded-[var(--radius-xl)] border border-border bg-surface p-6 sm:p-8">
        <p className="text-sm font-semibold uppercase tracking-wide text-secondary">{t("resultTitle")}</p>
        <div className="mt-4 grid gap-6 sm:grid-cols-2">
          <div>
            <p className="text-sm text-muted-foreground">{t("estimatedRange")}</p>
            <p className="mt-1 text-3xl font-extrabold tabular-nums text-foreground">
              {estimate.currency} {fmt(estimate.amount_min)}–{fmt(estimate.amount_max)}
            </p>
          </div>
          <div>
            <p className="text-sm text-muted-foreground">{t("estimatedTimeline")}</p>
            <p className="mt-1 text-3xl font-extrabold tabular-nums text-foreground">
              {estimate.timeline_weeks_min}–{estimate.timeline_weeks_max} {t("weeks")}
            </p>
          </div>
        </div>
        <div className="mt-6 flex flex-wrap gap-3 text-sm">
          <span className="rounded-full border border-border px-3 py-1">
            {t("complexity")}: {t(`complexity_${estimate.complexity}`)}
          </span>
          <span className="rounded-full border border-border px-3 py-1">
            {t("confidence")}: {t(`confidence_${estimate.confidence}`)}
          </span>
        </div>
      </div>

      {/* Cost drivers */}
      {estimate.cost_drivers.length > 0 ? (
        <section className="mt-8">
          <h2 className="text-lg font-bold text-foreground">{t("costDrivers")}</h2>
          <ul className="mt-3 grid gap-2 sm:grid-cols-2">
            {estimate.cost_drivers.map((driver) => (
              <li
                key={driver.key}
                className="flex items-center justify-between gap-3 rounded-[var(--radius-md)] border border-border px-3 py-2 text-sm"
              >
                <span className="text-foreground">{driver.label}</span>
                <span className="text-xs font-medium uppercase tracking-wide text-muted-foreground">
                  {t(`weight_${driver.weight}`)}
                </span>
              </li>
            ))}
          </ul>
        </section>
      ) : null}

      {/* Recommended engagement model */}
      {estimate.recommended_engagement_model ? (
        <section className="mt-8 rounded-[var(--radius-lg)] border border-primary/30 bg-primary/5 p-5">
          <p className="text-sm text-muted-foreground">{t("recommendedModel")}</p>
          <Link href="/pricing" className="mt-1 inline-block text-lg font-bold text-foreground hover:text-secondary">
            {estimate.recommended_engagement_model.title}
          </Link>
        </section>
      ) : null}

      {/* Assumptions */}
      {estimate.assumptions.length > 0 ? (
        <section className="mt-8">
          <h2 className="text-lg font-bold text-foreground">{t("assumptions")}</h2>
          <ul className="mt-3 list-disc space-y-1 ps-5 text-sm text-muted-foreground">
            {estimate.assumptions.map((a, i) => (
              <li key={i}>{a}</li>
            ))}
          </ul>
        </section>
      ) : null}

      {/* Disclaimer */}
      <section className="mt-8 rounded-[var(--radius-lg)] border border-border bg-muted/40 p-5">
        <h2 className="text-sm font-bold text-foreground">{t("disclaimerTitle")}</h2>
        <p className="mt-2 text-pretty text-sm leading-relaxed text-muted-foreground">{t("disclaimer")}</p>
      </section>

      {/* Next steps */}
      <section className="mt-10">
        <h2 className="text-lg font-bold text-foreground">{t("nextStepsTitle")}</h2>

        {status === "success" ? (
          <div className="mt-4 rounded-[var(--radius-lg)] border border-success/30 bg-success/10 p-5 text-sm font-medium text-success">
            {t("leadSuccess")}
          </div>
        ) : (
          <>
            <div className="mt-4 flex flex-wrap gap-3">
              {actions.map((a) => (
                <Button
                  key={a}
                  // Selected as long as the form is open in any state -- keyed
                  // on `status === "form"` alone, the chosen action visibly
                  // lost its selection the moment the user submitted it.
                  variant={action === a && status !== "idle" ? "primary" : "outline"}
                  aria-pressed={action === a && status !== "idle"}
                  onClick={() => chooseAction(a)}
                >
                  {t(a === "email_estimate" ? "emailEstimate" : a === "book_call" ? "bookCall" : a === "request_proposal" ? "requestProposal" : a === "start_project" ? "startProject" : "askQuestion")}
                </Button>
              ))}
            </div>

            {status === "form" ||
            status === "submitting" ||
            status === "error" ||
            status === "invalid" ? (
              <form onSubmit={handleSubmit} className="mt-6 flex flex-col gap-4" noValidate>
                <input
                  type="text"
                  name="website"
                  tabIndex={-1}
                  autoComplete="off"
                  className="absolute left-[-9999px] h-0 w-0 opacity-0"
                  aria-hidden="true"
                />
                <div className="grid gap-4 sm:grid-cols-2">
                  <Field id="name" label={t("leadName")} error={errors.name}>
                    <Input
                      id="name"
                      name="name"
                      required
                      autoComplete="name"
                      onChange={clearError("name")}
                      {...fieldProps("name", errors.name)}
                    />
                  </Field>
                  <Field id="email" label={t("leadEmail")} error={errors.email}>
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
                  <Field id="company" label={t("leadCompany")}>
                    <Input id="company" name="company" autoComplete="organization" />
                  </Field>
                  <Field id="phone" label={t("leadPhone")}>
                    <Input id="phone" name="phone" type="tel" autoComplete="tel" />
                  </Field>
                </div>
                <Field id="summary" label={t("leadMessage")}>
                  <Textarea id="summary" name="summary" />
                </Field>
                {status === "invalid" ? (
                  <p className="text-sm font-medium text-destructive">{tc("formValidationError")}</p>
                ) : null}
                {status === "error" ? (
                  <p className="text-sm font-medium text-destructive">{t("leadError")}</p>
                ) : null}
                <Button type="submit" disabled={status === "submitting"} className="self-start">
                  {status === "submitting" ? t("submitting") : t("submit")}
                </Button>
              </form>
            ) : null}
          </>
        )}
      </section>

      <p className="mt-8 text-center text-xs text-muted-foreground">{t("resultSaved")}</p>
    </div>
  );
}
