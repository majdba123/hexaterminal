# hreflang & Canonical Policy

Implemented by `frontend/lib/seo/alternates.ts`, `lib/seo/indexing.ts`, and page
`generateMetadata` functions.

## Canonical

- Every indexable page self-canonicalises to **its own locale URL**:
  `https://{domain}/{locale}{path}`.
- Canonical **never** points across languages (an AR page never canonicalises to
  the EN URL, and vice-versa).
- Preview, search, and estimate-result URLs are noindex and must not emit a
  self-referential indexable canonical.

## hreflang alternates

`localeAlternates(path)` emits, for a locale-invariant path:

- `en → https://{domain}/en{path}`
- `ar → https://{domain}/ar{path}`
- `x-default → https://{domain}/en{path}` (default locale), per Google's guidance.

Reciprocity holds by construction: both locales are generated from the same
`path`, so the EN page names AR and vice-versa.

## Slug/URL normalisation rules

- Locale prefix is **always** present (`localePrefix: "always"`).
- Slugs are lowercase, hyphenated, locale-invariant (one slug shared by both
  locales — see `docs/architecture/content-model.md`).
- No trailing slash on content paths.
- Query parameters (search `?q=`, UTM) are never part of a canonical URL.

## Known gap — genuinely missing translations

Current model assumes **every slug exists in both locales**. The alternates
helper will still emit an `ar` (or `en`) alternate even if that locale's content
is missing, which would advertise a fake/empty alternate.

**Required hardening (deferred, documented):**

1. A translation-completeness state per record (`complete` / `partial` /
   `pending_review` / `approved`).
2. `localeAlternates` should accept the set of locales that actually have
   approved content for the record and emit alternates for those only.
3. An indexable page whose other-locale translation is incomplete must **not**
   emit that alternate, and the incomplete locale's page must stay `noindex`
   rather than silently falling back to the other language.
4. Per-page-type tests asserting reciprocity and the missing-translation
   behaviour.

Until then, the locale-invariant-slug assumption is safe **only** while content
is entered in both languages together; treat single-language records as a launch
blocker for that record.
