<?php

namespace App\Services;

use App\Models\ContentPreview;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Mints and resolves secure, expiring CMS preview links.
 *
 * The plain token is 48 bytes of CSPRNG randomness (far beyond brute-force
 * range), returned exactly once at mint time and embedded directly in the
 * Filament "Preview" action's URL -- it is never persisted or logged in
 * plain form. Only sha256(token) is stored, so a database leak cannot be
 * used to forge or replay a preview link.
 */
class PreviewTokenService
{
    private const DEFAULT_TTL_MINUTES = 1440; // 24h

    /**
     * @return array{preview: ContentPreview, token: string}
     */
    public function mint(Model $previewable, string $locale, ?User $user, int $ttlMinutes = self::DEFAULT_TTL_MINUTES): array
    {
        $token = Str::random(64);

        $preview = ContentPreview::create([
            'previewable_type' => $previewable::class,
            'previewable_id' => $previewable->getKey(),
            'locale' => $locale,
            'token_hash' => hash('sha256', $token),
            'created_by' => $user?->id,
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);

        return ['preview' => $preview, 'token' => $token];
    }

    /**
     * Resolves a plain token to its preview record + underlying model.
     * Returns null for anything that isn't an active (non-revoked,
     * non-expired) token -- an invalid token behaves identically to an
     * expired one from the caller's perspective (fail closed, no oracle).
     *
     * @return array{preview: ContentPreview, record: Model}|null
     */
    public function resolve(string $token): ?array
    {
        $preview = ContentPreview::query()
            ->active()
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (! $preview) {
            return null;
        }

        $record = $preview->previewable;
        if (! $record) {
            return null;
        }

        $preview->recordAccess();

        Log::channel(config('logging.default'))->info('cms.preview.accessed', [
            'preview_id' => $preview->id,
            'previewable_type' => $preview->previewable_type,
            'previewable_id' => $preview->previewable_id,
            'locale' => $preview->locale,
        ]);

        return ['preview' => $preview, 'record' => $record];
    }
}
