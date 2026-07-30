/**
 * Client-side validation for the two lead forms.
 *
 * Both forms carry `noValidate` so the error presentation is ours (inline,
 * translated, next to the field) rather than the browser's native bubbles.
 * That means the `required` and `type="email"` attributes on the inputs are
 * NOT enforcement -- they are hints for autofill and assistive tech only, and
 * this module is the only thing standing between an empty form and the API.
 */

export type FieldErrors = Record<string, string>;

/** Deliberately permissive: enough to catch typos, not a deliverability check. */
const EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;

export function validateContactFields(
  values: { name: string; email: string },
  messages: { required: string; email: string },
): FieldErrors {
  const errors: FieldErrors = {};

  if (!values.name.trim()) errors.name = messages.required;

  if (!values.email.trim()) {
    errors.email = messages.required;
  } else if (!EMAIL.test(values.email.trim())) {
    errors.email = messages.email;
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
