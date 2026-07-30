# Filament CMS — Hexa Terminal

Phase 3 of the transformation. Filament 4.11 installed at `/cms`, running
alongside the legacy custom admin at `/admin` (untouched, still functional
until cutover — see `docs/architecture/content-model.md` for why the
underlying tables are separate).

## Panel setup

`app/Providers/Filament/CmsPanelProvider.php` (manually registered in
`config/app.php`'s `providers` array — this app kept the Laravel 10-style
provider registration through the Phase 1 upgrade, so Filament's installer
didn't auto-register the generated provider into `bootstrap/providers.php`
the way a fresh Laravel 11+ skeleton would).

- Panel ID/path: `cms` (not `admin` — that path is the legacy panel's).
- `lara-zeus/spatie-translatable:^1.0` (the Filament-4-compatible major
  version; `^2.0` requires Filament 5) registered as
  `SpatieTranslatablePlugin::make()->defaultLocales(['en', 'ar'])`.

## Authorization

`App\Models\User` now implements `FilamentUser::canAccessPanel()`, gated by
`spatie/laravel-permission` roles (`admin`, `editor`) via the `HasRoles`
trait — **not** the legacy `type == 1` integer, which stays scoped to the
`/admin` panel's `AdminMiddleware` only. `RolesSeeder` creates both roles;
`UsersTableSeeder` assigns `admin` to the seeded user in addition to setting
`type = 1`, so the one seeded account can use both panels during the
transition period.

## Resources (11)

| Resource | Translatable | Notes |
|---|---|---|
| Service | ✅ | Auto-slug from `name` on create |
| System | ✅ | `type` enum select; industries M2M |
| CaseStudy | ✅ | service/system selects, industries M2M, `video_url` |
| Industry | ✅ | Auto-slug from `name` |
| Article | ✅ | Auto-slug from `title`; author select |
| TeamMember | ✅ (position, bio only) | Auto-slug from first+last name |
| Testimonial | ✅ (content only) | Moderation: Approve/Reject row actions, pending-count nav badge |
| FaqItem | ✅ | Named `FaqItem` not `Faq` — collides with legacy `FAQ.php` on case-insensitive filesystems |
| ContactLead | — | Triage UI: submission fields disabled, only status/priority/notes editable; new-lead-count nav badge |
| Redirect | — | Plain CRUD; `hit_count`/`last_hit_at` are system-managed, not exposed in the form |
| AiGeneration | — | Review-only: no create page (`canCreate() => false`), all generation fields disabled, only `status`/`reviewed_at` editable. Not populated yet — Phase 7 builds the service that creates rows here |

Every translatable resource follows the same pattern (`lara-zeus/spatie-translatable`):
1. Resource class: `use Translatable;` (from `LaraZeus\SpatieTranslatable\Resources\Concerns\Translatable`)
2. List/Create/Edit pages: the matching page-level `Translatable` trait +
   `LocaleSwitcher::make()` in header actions (List and Edit only — Create
   doesn't need the switcher since the record doesn't exist yet, though the
   `handleRecordCreation` override still captures `otherLocaleData`).
3. Form fields for translatable model attributes use the plain field name
   (e.g. `TextInput::make('name')`) — no manual per-locale wrapping needed,
   the content driver swaps values based on the active Livewire locale.

## Verified (not just route-registered)

`tests/Feature/Cms/`:
- `ServiceResourceTest` — full round-trip: 403 for non-role users, list
  renders, create persists an English translation, **editing in a second
  locale (`activeLocale = 'ar'`) correctly writes to that locale without
  touching the English value** (the core thing that could silently break).
- `AllResourcesRenderTest` — every resource's list and create page actually
  renders (not just that the route exists) for an authenticated CMS admin;
  confirms `AiGeneration`'s create route is genuinely absent (404, not just
  hidden).

## Deferred to later phases

- Filament `RelationManager`s for `SeoMeta` (currently only reachable via
  the model's `seo()` relation, no dedicated UI) and for pivot tables
  (`industry_system`, `case_study_industry` are edited via the `Select`
  multi-relationship pickers on System/CaseStudy forms, which is sufficient
  for now).
- `spatie/laravel-activitylog` for CMS audit trail (Filament's own
  `EditAction`/`DeleteAction` provide basic confirmation but no history yet).
- Media upload validation depth (type/size/dimension limits) — `FileUpload`
  components currently accept defaults.
