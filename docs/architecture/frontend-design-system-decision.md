# Frontend Design System Decision — Hexa Terminal

Phase 5 of the transformation. Researched from official/primary sources
(Next.js docs, Tailwind CSS blog, shadcn/ui docs, MUI docs, and comparative
analysis current as of July 2026) before writing any frontend code, per the
mandate.

## Toolchain versions locked in

- **Next.js 16.2.x**, App Router (Pages Router is in maintenance mode).
  Turbopack is the default bundler; request APIs (`cookies()`, `headers()`)
  are async-only; Node.js 20+ required (this machine runs Node 24.14.1).
- **React 19.2** (bundled with Next.js 16).
- **Tailwind CSS v4** — CSS-native `@theme` directive replaces
  `tailwind.config.js`, Rust/Lightning CSS engine, first-party Vite/Next
  integration, native support for cascade layers and `color-mix()`.
  Released stable January 2025, actively current.
- **TypeScript strict mode.**

Sources: [Next.js 16 upgrade guide](https://nextjs.org/docs/app/guides/upgrading/version-16), [Next.js App Router docs](https://nextjs.org/docs/app), [Tailwind CSS v4.0 announcement](https://tailwindcss.com/blog/tailwindcss-v4).

## Candidates evaluated

### 1. shadcn/ui + Radix primitives + Tailwind v4

- **RSC compatibility**: components are plain JSX styled with Tailwind — no
  client-side styling runtime. Static components render as true Server
  Components with zero client JS; only genuinely interactive primitives
  (dropdowns, dialogs — anything using Radix state) carry `"use client"`.
- **Ownership model**: components are copy-pasted into the repo (`components/ui/`),
  not installed as an opaque dependency — full control over markup, styling,
  and behavior, which is exactly what "one coherent, reusable Hexa Terminal
  Design System" requires rather than "generic sections assembled from a
  library."
- **Bundle/runtime**: zero runtime style overhead (Tailwind is compiled away);
  smaller client bundles than any CSS-in-JS approach.
- **Theming**: CSS custom properties + Tailwind's `@theme`, trivially supports
  a dark/light class strategy and a fully custom brand palette (no fighting a
  pre-built theme object).
- **RTL/Arabic**: Tailwind's logical-property utilities (`ps-`, `pe-`, `ms-`,
  `me-`) plus `dir="rtl"` on `<html>` — no library-side RTL plumbing needed.
- **Accessibility**: Radix primitives ship correct ARIA/keyboard behavior for
  the genuinely hard components (dialog, menu, combobox); the rest is
  semantic HTML we write directly.
- **AI-agent maintainability**: since the code lives in the repo as plain
  Tailwind + Radix, an agent (or a new engineer) reads exactly what renders —
  no indirection through a component library's internal prop system.
- **Vendor lock-in**: minimal — it's Tailwind and Radix underneath, both of
  which are viable independently if `shadcn` tooling itself is ever dropped.
- **Gaps**: no built-in complex data-grid, rich date-range picker, or charting
  suite — must be hand-built or a focused third-party addition if ever
  needed. Not a concern for a marketing/content site with a CMS-driven admin
  (Filament already covers the data-grid need on the backend side).

Source: [shadcn/ui Next.js installation docs](https://ui.shadcn.com/docs/installation/next).

### 2. Material UI (MUI)

- **RSC compatibility**: MUI runs on Emotion, a client-side CSS-in-JS runtime.
  MUI components cannot be Server Components and require a cache provider to
  avoid hydration mismatches and flash-of-unstyled-content during streaming —
  directly working against the App Router's RSC-by-default model this
  project is built on (Stage 6: "Server Components by default... do not mark
  entire page trees with use client").
  Source: [comparative analysis, RSC support](https://www.shadcndeck.com/blog/shadcn-vs-material-ui).
- **Bundle/runtime**: larger client bundles from the Emotion runtime + MUI's
  own component weight, working against Stage 15's performance mandate.
- **Visual ownership**: MUI's Material Design language would need extensive
  theme overriding to look like a distinct "Hexa Terminal" brand rather than
  "a Material app" — more fighting the library than owning the design.
- **Where it genuinely wins**: MUI X (DataGrid, DatePicker, Charts) — real
  strength for data-dense internal tooling. Not this project's need; Filament
  already owns the data-grid-heavy CMS surface.
- **Verdict**: no concrete requirement in this project's scope (marketing
  site + content pages) that MUI satisfies and shadcn/Radix cannot. Explicitly
  rejected per the mandate's default-unless-evidence-contradicts rule.

### 3. Pure custom components (no primitive library)

- Would still need to hand-build accessible dialog/menu/combobox behavior
  (focus trapping, keyboard nav, ARIA state) — Radix already solves this
  correctly and is itself unstyled, so using it costs nothing in visual
  ownership while saving significant accessibility risk.
- Rejected: reinventing what Radix already provides for free, for no
  brand-ownership benefit (shadcn/ui's Radix + Tailwind combination already
  gives full visual ownership).

## Decision

**shadcn/ui + Radix primitives + Tailwind CSS v4**, as the mandate's default
recommends and as the evidence above confirms — no candidate contradicts it
for this project's actual requirements (RSC-first marketing/content site,
custom brand identity, EN/AR RTL, WCAG 2.2 AA target, small-to-moderate
component surface).

**Not mixed with MUI or any other full component framework** — the mandate's
explicit prohibition, and there is no gap in this project's scope that would
justify it.

## What "owning" the design system means here

Not "install shadcn and use its default components as-is." Concretely:

1. Design tokens (`frontend/styles/theme.css`, Tailwind `@theme`) driven by
   the **real Hexa Terminal brand** — blue `#3663D8`, light blue `#77BEFF`,
   cyan `#00D1FF` (extracted from the delivered `icons/logo.svg`), charcoal/
   near-black, and a controlled neutral scale. **Gold is removed** — the
   legacy site's `--accent: #D4AF37` did not match the actual brand and is
   not carried into the new frontend.
2. A constrained set of composed primitives (Button, Container, Section,
   SectionHeading, Badge, Card + typed variants, Metric, LogoCloud, CTA,
   Header, MobileNav, Footer, Dialog/VideoModal, form fields, Breadcrumb,
   Pagination, EmptyState, Skeleton) — built once in `components/ui` and
   `components/site`, reused everywhere. No ad hoc one-off buttons, badges,
   spacing, or shadows scattered through page code.
3. Every color, radius, shadow, spacing, and motion value comes from the
   token system — never a raw hex/px value inline in a component.
