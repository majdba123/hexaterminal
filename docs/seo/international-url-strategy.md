# International URL Strategy

## Current (recommended) strategy — keep

- **One primary domain.**
- Locale prefix **always**: `/en/...`, `/ar/...` (`frontend/i18n/routing.ts`).
- Reciprocal `en`/`ar` alternates + `x-default` (see
  `docs/seo/hreflang-canonical-policy.md`).

## Explicitly NOT doing (guardrail)

Do **not** add, now or reactively:

- Country folders (`/en-ae`, `/ar-sa`).
- City folders (`/dubai`, `/riyadh`).
- Regional subdomains (`ae.hexaterminal.com`).
- ccTLDs (`hexaterminal.ae`).

These fragment authority, multiply thin/duplicate pages, and imply a physical or
legal presence the company has not verified. Mass regional/city SEO pages are
out of scope.

## Decision framework for a future regional URL

A regional variant may be created **only** when ALL of the following are true and
documented:

1. **Unique content** — the regional page differs materially from the global
   page (not a templated city swap).
2. **Market-specific proof** — real, approved case studies / references for that
   market.
3. **Legal & location review** — verified legal basis to market in / claim
   presence in that market (see `docs/content/public-claims-register.md`).
4. **Regional CTA** — a genuinely different conversion path (local contact,
   currency, booking).
5. **Founder approval** — explicit sign-off recorded.

If any is missing, keep the single global `/en` `/ar` page. This framework
exists so a regional URL is a deliberate business decision, never an SEO reflex.
