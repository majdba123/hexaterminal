<?php

namespace App\Filament\Support;

use App\Services\PreviewTokenService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Shared "Preview" header action for every governed content resource
 * (Services, Systems, Case Studies, Industries, Articles, Trust Pages,
 * Team Members, Pricing Profiles, Engagement Models). Mints a one-time,
 * expiring, high-entropy token via PreviewTokenService and opens it on the
 * public frontend -- works for drafts and unapproved records exactly like
 * it will for published ones, since the token itself is the authorization,
 * not the record's publish state.
 */
class PreviewAction
{
    public static function make(int $ttlMinutes = 1440): Action
    {
        return Action::make('preview')
            ->label('Preview')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->action(function (Model $record, $livewire) use ($ttlMinutes) {
                $locale = property_exists($livewire, 'activeLocale') ? $livewire->activeLocale : 'en';

                $minted = app(PreviewTokenService::class)->mint($record, $locale, Auth::user(), $ttlMinutes);

                $baseUrl = rtrim((string) config('services.frontend.url'), '/');
                $url = "{$baseUrl}/{$locale}/preview/{$minted['token']}";

                Notification::make()
                    ->title('Preview link generated')
                    ->body("Valid for {$ttlMinutes} minutes: {$url}")
                    ->success()
                    ->persistent()
                    ->actions([
                        Action::make('open')
                            ->label('Open preview')
                            ->url($url, shouldOpenInNewTab: true),
                    ])
                    ->send();
            });
    }
}
