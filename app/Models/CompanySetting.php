<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Spatie\Translatable\HasTranslations;

/**
 * Single-row company settings edited in Filament (Settings page). Secrets
 * never live here -- analytics is provider name + public site id only, and
 * lead_recipients are internal notification addresses (not exposed by the
 * public API; see SettingsController for the public field whitelist).
 */
class CompanySetting extends Model
{
    use HasTranslations;

    private const CACHE_KEY = 'company-settings:current';

    protected $fillable = [
        'company_name', 'tagline', 'description', 'email', 'phone', 'whatsapp',
        'address', 'social_links', 'booking_url', 'lead_recipients',
        'default_og_image', 'analytics_provider', 'analytics_site_id', 'footer_note',
    ];

    public array $translatable = ['company_name', 'tagline', 'description', 'address', 'footer_note'];

    protected $casts = [
        'social_links' => 'array',
    ];

    protected static function booted(): void
    {
        $clear = function (): void {
            Cache::forget(self::CACHE_KEY);
            foreach (['en', 'ar'] as $locale) {
                Cache::forget("api:v1:public:settings:list:{$locale}:all");
            }
        };
        static::saved($clear);
        static::deleted($clear);
    }

    /** The one settings row (created empty on first access), cached. */
    public static function current(): self
    {
        return Cache::remember(self::CACHE_KEY, 300, fn () => self::firstOrCreate([]));
    }

    /** @return list<string> parsed internal lead-notification recipients */
    public function leadRecipients(): array
    {
        return array_values(array_filter(array_map(
            'trim',
            explode(',', (string) $this->lead_recipients),
        ), fn (string $email) => filter_var($email, FILTER_VALIDATE_EMAIL) !== false));
    }
}
