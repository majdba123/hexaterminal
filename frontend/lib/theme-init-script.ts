/**
 * No-flash theme init: reads the saved preference before paint (default
 * dark). Rendered as a literal inline `<script>` in app/[locale]/layout.tsx.
 * Extracted to its own module (rather than an inline template literal)
 * so next.config.ts can hash this EXACT string at build time for a
 * `script-src 'sha256-...'` CSP allowance -- see lib/csp.ts. If you edit
 * this script, the hash is recomputed automatically on the next build;
 * nothing needs to be kept in sync by hand.
 */
export const THEME_INIT_SCRIPT = `
(function () {
  try {
    var stored = localStorage.getItem("ht-theme");
    var theme = stored === "light" || stored === "dark" ? stored : "dark";
    document.documentElement.setAttribute("data-theme", theme);
  } catch (e) {
    document.documentElement.setAttribute("data-theme", "dark");
  }
})();
`;
