import * as React from "react";
import { Label } from "@/components/ui/label";

/**
 * A labelled form field with its error message rendered directly beneath the
 * control, next to where the mistake was made -- not collected into a summary
 * at the bottom of the form.
 *
 * `fieldProps(id, error)` returns the wiring the control itself needs
 * (`aria-invalid`, `aria-describedby`, and the `error` styling flag) so the
 * three can never drift apart:
 *
 *   <Field id="email" label={t("formEmail")} error={errors.email}>
 *     <Input id="email" name="email" {...fieldProps("email", errors.email)} />
 *   </Field>
 */
export function Field({
  id,
  label,
  error,
  className,
  children,
}: {
  id: string;
  label: React.ReactNode;
  error?: string;
  className?: string;
  children: React.ReactNode;
}) {
  return (
    <div className={className}>
      <Label htmlFor={id}>{label}</Label>
      {children}
      {error ? (
        <p id={`${id}-error`} role="alert" className="mt-1.5 text-sm font-medium text-destructive">
          {error}
        </p>
      ) : null}
    </div>
  );
}

export function fieldProps(id: string, error?: string) {
  return {
    error: Boolean(error),
    "aria-invalid": error ? (true as const) : undefined,
    "aria-describedby": error ? `${id}-error` : undefined,
  };
}
