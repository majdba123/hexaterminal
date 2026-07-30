/**
 * Lightweight long-form text styling for legal pages (no @tailwindcss/
 * typography plugin installed -- this project doesn't otherwise need one, so
 * a handful of child-selector utilities cover it instead of adding a
 * dependency for two pages).
 *
 * Inline links use `text-secondary`, not `text-primary`: `--color-primary`
 * is tuned as a button BACKGROUND (paired with white foreground text), and
 * at its dark-theme value only reaches ~4.17:1 contrast as bare text
 * directly on the page background -- below the WCAG AA 4.5:1 minimum for
 * normal text (caught by e2e/accessibility.spec.ts's axe-core check).
 * `--color-secondary` reaches ~9.7:1 against the dark background and is
 * still a clearly brand-blue link color.
 */
export function LegalProse({
  children,
  dir,
}: {
  children: React.ReactNode;
  dir?: "rtl" | "ltr";
}) {
  return (
    <div
      dir={dir}
      className="mt-8 flex flex-col gap-4 text-base leading-relaxed text-foreground [&_a]:font-medium [&_a]:text-secondary [&_a]:underline [&_h2]:mt-6 [&_h2]:text-xl [&_h2]:font-bold [&_h2]:text-foreground [&_p]:text-muted-foreground"
    >
      {children}
    </div>
  );
}
