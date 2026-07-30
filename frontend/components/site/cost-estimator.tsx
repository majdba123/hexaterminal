"use client";

import * as React from "react";
import { useTranslations } from "next-intl";
import { useRouter } from "@/i18n/navigation";
import { Button } from "@/components/ui/button";
import { trackEvent } from "@/components/site/analytics-script";
import { cn } from "@/lib/utils";
import type { Currency, EstimatorQuestion } from "@/lib/api/types";

type Answers = Record<string, string | string[]>;

/** A question shows if it has no condition, or the controlling answer matches. */
function isVisible(question: EstimatorQuestion, answers: Answers): boolean {
  const condition = question.show_if;
  if (!condition) return true;
  const given = answers[condition.question];
  const values = Array.isArray(given) ? given : given ? [given] : [];
  return values.some((v) => condition.in.includes(v));
}

function hasAnswer(answers: Answers, key: string): boolean {
  const a = answers[key];
  return Array.isArray(a) ? a.length > 0 : Boolean(a);
}

export function CostEstimator({
  locale,
  questions,
  currencies,
}: {
  locale: string;
  questions: EstimatorQuestion[];
  currencies: Currency[];
}) {
  const t = useTranslations("estimator");
  const router = useRouter();
  const [currency, setCurrency] = React.useState<Currency>(currencies[0] ?? "USD");
  const [answers, setAnswers] = React.useState<Answers>({});
  const [index, setIndex] = React.useState(0);
  const [submitting, setSubmitting] = React.useState(false);
  const [error, setError] = React.useState(false);
  const startedRef = React.useRef(false);

  // Answers live in in-memory React state for the duration of the flow --
  // session-scoped, no cookies, no cross-session persistence, no fingerprint.

  const visible = React.useMemo(
    () => questions.filter((q) => isVisible(q, answers)),
    [questions, answers],
  );
  const safeIndex = Math.min(index, visible.length - 1);
  const current = visible[safeIndex];
  const isLast = safeIndex === visible.length - 1;

  function selectOption(question: EstimatorQuestion, optionKey: string) {
    if (!startedRef.current) {
      startedRef.current = true;
      trackEvent("estimator_started");
    }
    setAnswers((prev) => {
      if (question.type === "multi_select") {
        const existing = Array.isArray(prev[question.key]) ? (prev[question.key] as string[]) : [];
        const next = existing.includes(optionKey)
          ? existing.filter((k) => k !== optionKey)
          : [...existing, optionKey];
        return { ...prev, [question.key]: next };
      }
      return { ...prev, [question.key]: optionKey };
    });
  }

  function isSelected(question: EstimatorQuestion, optionKey: string): boolean {
    const a = answers[question.key];
    return Array.isArray(a) ? a.includes(optionKey) : a === optionKey;
  }

  const canAdvance = current ? !current.is_required || hasAnswer(answers, current.key) : false;

  function goNext() {
    if (!canAdvance) return;
    trackEvent("estimator_step_completed", { step: safeIndex + 1 });
    if (isLast) {
      void submit();
    } else {
      setIndex(safeIndex + 1);
    }
  }

  function goBack() {
    setIndex(Math.max(0, safeIndex - 1));
  }

  async function submit() {
    setSubmitting(true);
    setError(false);
    // Only send answers for currently-visible questions (branching integrity).
    const visibleKeys = new Set(visible.map((q) => q.key));
    const payloadAnswers: Answers = {};
    for (const [k, v] of Object.entries(answers)) {
      if (visibleKeys.has(k)) payloadAnswers[k] = v;
    }

    try {
      const res = await fetch("/api/estimates", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ currency, locale, answers: payloadAnswers }),
      });
      if (!res.ok) throw new Error(`Estimate failed: ${res.status}`);
      const body = (await res.json()) as { data: { public_uuid: string } };
      trackEvent("estimator_completed");
      router.push(`/estimate/${body.data.public_uuid}`);
    } catch {
      setError(true);
      setSubmitting(false);
    }
  }

  if (!current) return null;

  const progress = Math.round(((safeIndex + 1) / visible.length) * 100);

  return (
    <div className="mx-auto max-w-2xl">
      <div className="mb-6 flex items-center justify-between gap-4">
        <p className="text-sm font-medium tabular-nums text-muted-foreground">
          {t("step")} {safeIndex + 1} {t("of")} {visible.length}
        </p>
        <label className="flex items-center gap-2 text-sm">
          <span className="text-muted-foreground">{t("currency")}</span>
          <select
            value={currency}
            onChange={(e) => setCurrency(e.target.value as Currency)}
            className="focus-ring min-h-11 rounded-[var(--radius-md)] border border-border bg-background px-3 text-sm"
            aria-label={t("currency")}
          >
            {currencies.map((c) => (
              <option key={c} value={c}>
                {c}
              </option>
            ))}
          </select>
        </label>
      </div>

      <div
        className="mb-8 h-1.5 w-full overflow-hidden rounded-full bg-muted"
        role="progressbar"
        aria-valuenow={progress}
        aria-valuemin={0}
        aria-valuemax={100}
      >
        {/* scaleX, not width: width is a layout property, so animating it
            forces layout on every frame. A transform runs on the compositor.
            origin-start keeps the fill anchored to the inline start in RTL. */}
        <div
          className="h-full w-full origin-[left] bg-primary transition-transform duration-200 ease-out rtl:origin-[right] motion-reduce:transition-none"
          style={{ transform: `scaleX(${progress / 100})` }}
        />
      </div>

      <fieldset>
        <legend className="text-xl font-bold text-foreground">{current.prompt}</legend>
        {current.help_text ? (
          <p className="mt-2 text-sm text-muted-foreground">{current.help_text}</p>
        ) : null}
        <p className="mt-1 text-xs font-medium uppercase tracking-wide text-muted-foreground">
          {current.type === "multi_select" ? t("selectMultiple") : t("selectOne")}
          {!current.is_required ? ` · ${t("optional")}` : ""}
        </p>

        {/* Native radio/checkbox inputs, not aria-pressed toggle buttons: the
            browser then supplies grouped semantics ("2 of 5"), arrow-key
            navigation within the group, and the roving tab stop for free.
            As toggle buttons a screen reader announced five unrelated
            controls with no indication that picking one clears the others.
            The input is visually hidden but still focusable, so the ring is
            drawn on the label via peer-focus-visible. */}
        <div className="mt-5 grid gap-3">
          {current.options.map((option) => {
            const selected = isSelected(current, option.key);
            const inputId = `${current.key}-${option.key}`;
            return (
              <div key={option.key} className="relative">
                {/* The input is transparent but full-bleed rather than
                    zero-sized, so it is the thing being clicked and stays a
                    real pointer target -- a 0x0 or clipped input is unhittable
                    for pointer events and for test drivers alike. The label
                    underneath carries the visible styling and the accessible
                    name, and picks up the focus ring via peer-focus-visible. */}
                <input
                  type={current.type === "multi_select" ? "checkbox" : "radio"}
                  id={inputId}
                  name={current.key}
                  value={option.key}
                  checked={selected}
                  onChange={() => selectOption(current, option.key)}
                  className="peer absolute inset-0 z-10 size-full cursor-pointer appearance-none opacity-0"
                />
                <label
                  htmlFor={inputId}
                  className={cn(
                    "flex min-h-11 cursor-pointer items-center rounded-[var(--radius-lg)] border px-4 py-3 text-start text-sm font-medium transition-colors peer-focus-visible:outline-2 peer-focus-visible:outline-offset-2 peer-focus-visible:outline-(--color-ring) motion-reduce:transition-none",
                    selected
                      ? "border-primary bg-primary/10 text-foreground"
                      : "border-border bg-background text-foreground hover:border-primary/50",
                  )}
                >
                  {option.label}
                </label>
              </div>
            );
          })}
        </div>
      </fieldset>

      {error ? <p className="mt-4 text-sm font-medium text-destructive">{t("leadError")}</p> : null}

      <div className="mt-8 flex items-center justify-between gap-4">
        <Button variant="outline" onClick={goBack} disabled={safeIndex === 0 || submitting}>
          {t("back")}
        </Button>
        <Button onClick={goNext} disabled={!canAdvance || submitting}>
          {submitting ? t("calculating") : isLast ? t("seeEstimate") : t("next")}
        </Button>
      </div>
    </div>
  );
}
