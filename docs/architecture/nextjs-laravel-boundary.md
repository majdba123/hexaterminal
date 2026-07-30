# Next.js ↔ Laravel Boundary — Versioned Public API

Phase 4 of the transformation. The Next.js frontend (Phase 5) consumes Laravel
exclusively through `/api/v1/public/*`. It never touches the legacy `/api/*`
CRUD routes or Filament's `/cms` panel — those remain admin-authenticated
surfaces. This is the explicit, versioned, read-only contract between the two
codebases.

## Endpoints

All under `routes/api_v1.php`, prefix `/api/v1/public`, middleware `api.locale`
(`App\Http\Middleware\SetApiLocale`) + `cache.headers:public;max_age=300;etag`
on every GET.

| Method | Path | Controller | Notes |
|---|---|---|---|
| GET | `/home` | `HomeController@index` | Aggregate: all published services, up to 6 featured systems, up to 6 featured case studies, up to 6 featured+approved testimonials, content counts |
| GET | `/services` | `ServiceController@index` | Paginated (`?page`, `?per_page`, max 50) |
| GET | `/services/{slug}` | `ServiceController@show` | 404 if unpublished/missing |
| GET | `/systems` | `SystemController@index` | Paginated; `?type=`, `?featured=1` filters |
| GET | `/systems/{slug}` | `SystemController@show` | Includes published case studies |
| GET | `/case-studies` | `CaseStudyController@index` | Paginated; `?featured=1` |
| GET | `/case-studies/{slug}` | `CaseStudyController@show` | Includes service/system/industries |
| GET | `/industries` | `IndustryController@index` | Not paginated (small set) |
| GET | `/industries/{slug}` | `IndustryController@show` | |
| GET | `/articles` | `ArticleController@index` | Paginated, newest first |
| GET | `/articles/{slug}` | `ArticleController@show` | |
| GET | `/team` | `TeamMemberController@index` | Not paginated |
| GET | `/team/{slug}` | `TeamMemberController@show` | |
| GET | `/testimonials` | `TestimonialController@index` | Approved only; `?featured=1` |
| GET | `/faqs` | `FaqController@index` | Not paginated |
| POST | `/leads` | `LeadController@store` | Throttled `5,1`; honeypot field `website` |

## Response shape

Every response is `{"data": ...}` (single object or array) with `{"meta": {...}}`
added for paginated collections (`current_page`, `last_page`, `total`). All
models are wrapped in `App\Http\Resources\V1\Public\*Resource` — **no raw
Eloquent serialization ever reaches the client**. Resources expose only
public-safe fields (no `is_published`, no `legacy_*_id`, no timestamps beyond
what's editorially meaningful like `published_at`).

Every content resource that has one embeds its optional `SeoMeta` override
under a `seo` key (`title`, `description`, `canonical_url`, `og_image`,
`noindex`, `nofollow`) — `null` when no override exists, meaning the frontend
should fall back to its own generated defaults (Phase 6).

## Locale handling

`?locale=en|ar` (any other value is silently ignored, not an error — see
`SetApiLocale`). Sets `app()->setLocale()` for the request, so every
`spatie/laravel-translatable` accessor (`$model->name`, `$model->title`, …)
returns that language automatically inside the Resource classes — no
per-field locale logic needed in the Resources themselves. Untranslated
content falls back to `config('app.fallback_locale')` (`en`), per the
package's own behavior — never a fabricated translation.

## Published-content-only guarantee

Every query goes through the model's `Publishable::scopePublished()` (or
`Testimonial::scopeApproved()`); nothing else filters visibility. Regression-
tested per model in `tests/Feature/Api/V1/PublishedContentVisibilityTest.php`
— unpublished and future-`published_at` rows are proven absent from both the
list and the direct-slug `show` route (404, not a filtered-out list).

## Caching & invalidation

Cache driver is `file` (`config('cache.default')`, no tag support). Two
independent layers:

1. **HTTP-level** — `cache.headers:public;max_age=300;etag` lets browsers/CDNs
   avoid re-fetching unchanged responses.
2. **Server-level** — `Cache::remember` (5-minute TTL,
   `App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses`) avoids
   re-querying the DB on every hit. Keys are locale-scoped:
   `api:v1:public:{resource}:{list|show}:{locale}:{suffix}`.

**Invalidation** (`App\Observers\ClearsPublicApiCache` + one thin subclass per
translatable model, registered in `AppServiceProvider::boot()`): on
`saved`/`deleted`, forgets:
- that model's own `show` cache (both locales), by slug
- the homepage aggregate cache (both locales) — it summarizes most content
- for the small unpaginated collections (industries, team, faqs) and
  testimonials' two filter variants, the fixed `list:all`-style key

**Deliberate trade-off, not an oversight:** paginated/filtered list caches
(services, systems, case-studies, articles index) are *not* exhaustively
invalidated — there's no tag support to target "every page/filter combination
that might include this row," and hand-enumerating them would be brittle and
easy to get subtly wrong. They simply expire on the 5-minute TTL. Regression-
tested in `tests/Feature/Api/V1/CacheInvalidationTest.php`, which exercises
the observer directly (editing via the Eloquent model, not the admin
controller) to prove the cache actually clears rather than just asserting the
controller code path.

## Leads endpoint

`POST /leads` is the single "Start a Project" intake (Stage 17) — no separate
quote/demo/discovery-call forms. Validates `name`+`email` required, everything
else optional (`company`, `country`, `project_type`, `system_type`,
`budget_range`, `timeline`, `summary`, `pain_points`, `source_page`, `utm`
array, `locale`). Captures `Referer` header server-side. A hidden `website`
field is a honeypot: if filled, the request returns `201` (so a bot doesn't
learn what tripped it) but nothing is saved. Throttled `5,1` per IP, same
pattern as the legacy contact form (Phase 0).

## TypeScript contract

No generated OpenAPI spec yet — the contract is this document plus the
Resource classes as source of truth. `frontend/lib/api/types.ts` (Phase 5)
hand-mirrors these shapes; if they drift, this doc and the Resource classes
are what's authoritative, and the types file should be updated to match.
