# Pricing & Estimator — Quality Gates

What to run to verify the pricing/estimator system, and what each gate proves. All commands below were run and passed for this sprint.

## Backend

```bash
composer validate --no-check-publish          # composer.json is valid
php artisan migrate:fresh --seed --force       # isolated schema build
php artisan hexa:migrate-legacy-content
php artisan db:seed --class=FounderContentSeeder --force
php artisan db:seed --class=DemoContentSeeder --force
php artisan db:seed --class=PricingEstimatorFixtureSeeder --force
vendor/bin/phpunit                             # full suite (169 tests)
vendor/bin/pint --test $PINT_PATHS             # governed style (see ci.yml)
vendor/bin/phpstan analyse --no-progress       # 0 errors
php artisan route:list --path=v1/public/estim  # routes registered
```

### Pricing-specific invariants (covered by `tests/Feature/Pricing/`)
- **No approved price → no public number.** `PricingApiTest` asserts unapproved, future-dated, and `hidden`/`request_quote` prices never appear.
- **Deterministic calculation.** `EstimatorEngineTest` asserts identical inputs+version → identical output.
- **Honest bands.** Output is a rounded range (multiples of the step), never an exact figure.
- **Hidden formulas.** Cost drivers carry a label + qualitative weight only; no amounts/factors/margin leak.
- **Branching integrity.** Smuggled answers to hidden questions don't change the price.
- **Version immutability.** A historical estimate keeps its version after a new one activates.
- **Result privacy.** The public estimate resource omits `base_amount_*`, `estimator_version_id`, `session_id`, and lead data.
- **No auto-reject.** Estimate-to-lead always creates a lead; scoring only orders.

## Frontend

```bash
cd frontend
npm ci
npx tsc --noEmit                               # types clean
npx eslint . --max-warnings=0                  # lint clean
API_URL=... NEXT_PUBLIC_ALLOW_INDEXING=true npm run build   # against live seeded API
npx playwright test                            # full suite (36 tests)
```

### Pricing-specific e2e (`frontend/e2e/pricing-estimator.spec.ts`)
- Pricing page EN + AR (RTL), indexable when it has content, request-quote CTAs (fail closed).
- Estimator progressive branching flow → range shown **without an email**.
- Result page is **noindex** and revisitable by URL.
- Optional lead capture appears only after the result.
- Unknown estimate id shows the not-found state.

## Pricing / privacy content gates
- No approved price means no public number (fail closed). ✔ tested
- Deterministic fixture calculation. ✔ tested
- Hidden internal formulas / margin. ✔ tested
- No private estimates in sitemap / search / RSS; result pages `noindex`. ✔ (`/estimate/{uuid}` excluded from `sitemap.ts`; verified 0 UUIDs in `sitemap.xml`)
- No PII in analytics events (`pricing_page_view`, `estimator_started`, `estimator_step_completed`, `estimator_completed`, `estimate_result_viewed`, `estimate_email_requested`, `discovery_call_clicked`, `proposal_requested`). ✔ event props carry no name/email/phone/free-text.
- No PII in public URLs (UUID only). ✔

## Do not report a pass unless the command actually succeeds.
The gates above are green as of this sprint: 169 backend tests, PHPStan 0 errors, governed Pint pass, production build OK, 36 Playwright tests pass.
