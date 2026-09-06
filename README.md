# HexaTerminal

[English](README.md) | [العربية](README_AR.md)

> Full-stack company website and content platform for HexaTerminal, combining a Next.js public frontend with a Laravel/Filament CMS and a versioned public API.

## Overview

HexaTerminal is a software engineering company focused on custom business systems and digital products. This repository contains the platform that powers the HexaTerminal website and its content-management workflow.

The codebase separates the public experience from content administration:

- a **Next.js** frontend renders the public website;
- a **Laravel 12** backend owns application data and the public API;
- a **Filament 4** CMS provides structured content management;
- a versioned **`/api/v1/public`** contract connects the frontend to published content;
- localized content is supported in **English and Arabic**.

The repository is therefore more than a marketing website: it includes a structured CMS, publishing workflow, public API, lead capture, pricing and estimator flows, content search, redirects, and governance-oriented content models.

## Core Services Presented by HexaTerminal

The approved service content in the repository is organized around three core offerings:

1. **Custom ERP & CRM Systems** — custom operational systems designed around real business workflows.
2. **Web Platforms & Mobile Applications** — multi-user platforms, portals, booking systems, marketplaces, and mobile products.
3. **E-commerce & Business Websites** — business websites and commerce experiences with custom integrations and operational functionality.

## Platform Capabilities

The CMS and public API include structured support for:

- Services
- Systems and software products
- Case studies
- Industries
- Articles, categories, and tags
- Team members
- Testimonials
- FAQs
- Engagement models and pricing profiles
- Project-cost estimator configuration and estimates
- Company settings
- Trust and governance pages
- Leads and newsletter submissions
- Redirect management
- SEO-oriented content and metadata

## Architecture

```text
┌──────────────────────────────┐
│      Public Web Client       │
│  Next.js 16 + React 19 + TS │
└──────────────┬───────────────┘
               │
               │ /api/v1/public/*
               ▼
┌──────────────────────────────┐
│       Laravel 12 API         │
│ Resources • Controllers     │
│ Locale • Cache • Security   │
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│  Eloquent Models / Database │
│  Structured CMS Content     │
└──────────────▲───────────────┘
               │
┌──────────────┴───────────────┐
│       Filament 4 CMS         │
│ Publishing • Media • Roles  │
└──────────────────────────────┘
```

The Next.js frontend consumes the Laravel application through the explicit versioned public API rather than accessing CMS or legacy administrative routes directly.

For the detailed boundary contract, see [Next.js ↔ Laravel Boundary](docs/architecture/nextjs-laravel-boundary.md).

## Content Management

The current CMS architecture supports structured editorial content rather than hardcoded marketing pages.

Content records can move through an editorial lifecycle that includes:

`draft → in_review → approved → scheduled → published → archived`

Published API responses are filtered through publication rules before they are exposed to the public frontend.

The CMS also supports localized fields through `spatie/laravel-translatable`, with English and Arabic configured as the primary content locales.

A detailed map of the content entities and their data flow is available in [HexaTerminal CMS Data Architecture](HEXATERMINAL_CMS_DATA_ARCHITECTURE.md).

## Public API

The frontend-facing API is registered under:

```text
/api/v1/public/*
```

It exposes public-safe resources for website content including services, systems, case studies, industries, articles, team members, testimonials, FAQs, pricing, estimator flows, settings, search, redirects, trust content, and public submissions.

The API layer includes mechanisms such as locale selection, response resources, cache headers on suitable read endpoints, and throttling on selected public-write operations.

See [`routes/api_v1.php`](routes/api_v1.php) for the current route contract.

## Technology Stack

| Area | Technologies |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12 |
| CMS | Filament 4 |
| Authentication / Authorization | Laravel Sanctum, Spatie Laravel Permission |
| Localization | Spatie Laravel Translatable, next-intl |
| Frontend | Next.js 16, React 19, TypeScript |
| Styling | Tailwind CSS 4 |
| Data / Cache Integration | Eloquent ORM, Redis client support through Predis |
| Backend Quality Tooling | PHPUnit 11, Larastan, Laravel Pint |
| Frontend Quality Tooling | ESLint, TypeScript typecheck, Playwright |
| CI/CD | GitHub Actions, automated validation before production deployment |

## CI/CD & Production Validation

The repository contains a production workflow at [`.github/workflows/deploy-production.yml`](.github/workflows/deploy-production.yml). Production deployment is gated by an explicit validation job rather than treating a successful build as sufficient evidence of readiness.

The current validation pipeline includes:

- PHP and Node.js environment setup;
- backend and frontend dependency installation;
- Laravel asset build and database migration/seed validation;
- a focused Laravel test suite covering API behavior and security-sensitive areas;
- checks for CORS policy, error leakage, Filament authorization/MFA, rate limiting, replay/abuse paths, security headers, storage routes, upload allowlists, and deployment guards;
- a local health endpoint check before frontend integration validation;
- Next.js/TypeScript type checking, linting, and production build;
- deployment only after the validation job succeeds.

The workflow also contains explicit production safety handling around destructive reseeding and deployment concurrency. This repository therefore provides concrete evidence of the **CI/CD, GitHub Actions, environment configuration, testing, deployment, and production-validation work** described in my CV.

The presence of tooling or workflow definitions is not treated as proof that every historical run succeeded; runtime/CI status should be evaluated from actual workflow executions.

## Repository Structure

```text
.
├── .github/workflows/           # Production validation/deployment workflow
├── app/                         # Laravel application, models, API and CMS code
├── config/                      # Laravel application configuration
├── database/                    # Migrations, factories and seeders
├── docs/                        # Architecture and engineering documentation
├── frontend/                    # Next.js public website
├── public/                      # Laravel public assets / entrypoint
├── resources/                   # Laravel-side resources and views
├── routes/                      # Web, API and versioned public API routes
├── storage/                     # Laravel runtime storage structure
├── tests/                       # Backend automated tests
├── HEXATERMINAL_CMS_DATA_ARCHITECTURE.md
├── composer.json
└── package.json
```

## Security & Governance

The repository includes several security-oriented boundaries in its current architecture:

- public website data is exposed through explicit API Resource classes rather than raw model serialization;
- CMS content is separated from the public API contract;
- administrative access is authenticated;
- Filament CMS configuration includes authenticator-app MFA support for admin users;
- role and permission support is provided through Spatie Laravel Permission;
- public-write endpoints such as leads and estimate submissions use throttling where configured;
- environment-specific secrets are expected to remain outside source control;
- production validation includes focused security and deployment guard tests.

Client-visible or provider-managed credentials must still be restricted and rotated through their external providers whenever exposure history requires it.

See [`SECURITY.md`](SECURITY.md) for vulnerability reporting and repository credential-handling policy.

## Frontend

The public application lives under [`frontend/`](frontend/) and uses the Next.js App Router stack with React, TypeScript, Tailwind CSS, and `next-intl`.

Frontend-specific setup and commands are documented in [`frontend/README.md`](frontend/README.md).

## Development Notes

Backend dependencies are managed with Composer and frontend dependencies with npm. Environment configuration should be based on the example environment files appropriate to the intended environment; real credentials must not be committed.

Because deployment and infrastructure requirements can differ between local, staging, and production environments, this README intentionally does not claim a universal one-command production deployment process.

## Documentation

- [CMS Data Architecture](HEXATERMINAL_CMS_DATA_ARCHITECTURE.md)
- [Next.js ↔ Laravel Boundary](docs/architecture/nextjs-laravel-boundary.md)
- [`routes/api_v1.php`](routes/api_v1.php) — current public API route contract
- [`frontend/README.md`](frontend/README.md) — frontend-specific setup
- [`SECURITY.md`](SECURITY.md) — vulnerability reporting and secret-handling policy

## Project Website

HexaTerminal: https://www.hexaterminal.com/en

---

Built around a clear separation between public presentation, structured content management, application data, and production validation.
