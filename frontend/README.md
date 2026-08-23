# HexaTerminal Frontend

> Public website for HexaTerminal, built with Next.js and consuming the Laravel backend through the versioned public API.

## Overview

This directory contains the public-facing HexaTerminal web application.

The frontend is intentionally separated from the Laravel CMS and administrative surfaces. Public content is requested through the versioned Laravel API under `/api/v1/public/*`, while content editing and administration remain backend responsibilities.

## Technology Stack

- Next.js 16
- React 19
- TypeScript
- Tailwind CSS 4
- next-intl for localized routing/content
- Lucide React
- Radix UI primitives
- Playwright for end-to-end testing
- ESLint

## Available Scripts

Install dependencies:

```bash
npm install
```

Start the development server:

```bash
npm run dev
```

Create a production build:

```bash
npm run build
```

Start a previously built production bundle:

```bash
npm run start
```

Run linting:

```bash
npm run lint
```

Run TypeScript validation:

```bash
npm run typecheck
```

Run end-to-end tests:

```bash
npm run test:e2e
```

Run the staging Playwright configuration:

```bash
npm run test:e2e:staging
```

The presence of these scripts does not imply that every check currently passes in every environment. Runtime validation depends on the required backend, environment configuration, and test prerequisites being available.

## Backend Boundary

The frontend consumes the public Laravel contract at:

```text
/api/v1/public/*
```

This boundary is documented in detail in:

[`../docs/architecture/nextjs-laravel-boundary.md`](../docs/architecture/nextjs-laravel-boundary.md)

The public frontend should not depend directly on Filament CMS routes or legacy administrative CRUD endpoints.

## Localization

The frontend uses `next-intl` and is designed around localized public content supplied by Laravel. The current project documentation and CMS configuration identify English and Arabic as the primary supported content locales.

## Content Areas

Depending on the route, the frontend consumes structured content for areas such as:

- homepage content
- services
- systems
- case studies
- industries
- insights/articles
- team
- testimonials and FAQs
- pricing and project estimator flows
- company settings and trust content
- search and redirects

The backend remains the source of truth for publication state and public-safe API fields.

## Environment Configuration

Environment-specific API origins and other runtime values should be configured through environment variables appropriate to the target environment. Real credentials and private server values should never be committed to source control.

## Production Notes

This frontend is part of a larger Laravel + Next.js system. Production deployment must account for both applications and the API boundary between them. This README intentionally avoids prescribing a single deployment command because deployment topology can differ between environments.

## Related Documentation

- [Root Project README](../README.md)
- [CMS Data Architecture](../HEXATERMINAL_CMS_DATA_ARCHITECTURE.md)
- [Next.js ↔ Laravel Boundary](../docs/architecture/nextjs-laravel-boundary.md)
