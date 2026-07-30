<?php

namespace App\Providers\Filament;

use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use LaraZeus\SpatieTranslatable\SpatieTranslatablePlugin;

/**
 * The official Hexa Terminal CMS panel, at /cms -- deliberately not /admin,
 * which the legacy custom admin panel (routes/web.php, AdminMiddleware)
 * still occupies until the Next.js/Filament cutover (Stage 20).
 */
class CmsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('cms')
            ->path('cms')
            ->login()
            ->colors([
                'primary' => Color::Blue,
            ])
            ->plugin(
                SpatieTranslatablePlugin::make()
                    ->defaultLocales(['en', 'ar'])
            )
            // TOTP (authenticator-app) MFA, backed by Filament's first-party
            // provider (pragmarx/google2fa under the hood, already a
            // filament/filament dependency -- no new package required). See
            // App\Models\User for the HasAppAuthentication(Recovery)
            // implementation and docs/architecture/final-remaining-gap-inventory.md
            // for the full contract (encrypted secret, recovery codes,
            // required-for-admin policy).
            ->multiFactorAuthentication(
                [AppAuthentication::make()->recoverable()],
                isRequired: fn (): bool => (bool) Auth::user()?->hasRole('admin'),
            )
            // Sidebar group order. Without this Filament falls back to the
            // order groups happen to be discovered in, which is effectively
            // arbitrary and shifts whenever a resource is added or renamed.
            // The sequence below follows how the site is actually edited:
            // what we sell, the proof for it, the surrounding content, what it
            // costs, who asked, then the technical and governance surfaces.
            // Per-item order within each group is set by each resource's
            // $navigationSort.
            ->navigationGroups([
                'Offerings',
                'Proof',
                'Content',
                'Pricing',
                'Leads',
                'SEO',
                'Trust & Governance',
                'Settings',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
