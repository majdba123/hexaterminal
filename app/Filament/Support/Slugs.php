<?php

namespace App\Filament\Support;

use Closure;
use Filament\Forms\Components\TextInput;

/**
 * Shared, validated slug input for every content resource.
 *
 * Slugs are not just identifiers -- they are interpolated into generated
 * output that has no escaping of its own:
 *
 *  * `frontend/app/sitemap.ts` puts them in `<loc>` and in `hreflang` hrefs,
 *    and Next builds that XML by bare string concatenation, so a slug
 *    containing `</loc></url><url><loc>...` injects attacker-chosen entries
 *    into a sitemap served from the verified production domain (SEO poisoning
 *    / phishing-URL laundering), or malforms the document and voids it.
 *  * they form public URLs and canonical/alternate link targets.
 *
 * `Str::slug()` on the title is only a create-time convenience default; the
 * field stays freely editable afterwards, so the format has to be *validated*,
 * not merely defaulted.
 */
class Slugs
{
    /** Lowercase alphanumerics in hyphen-separated groups. No leading, trailing or doubled hyphens. */
    public const PATTERN = '/^[a-z0-9]+(?:-[a-z0-9]+)*$/';

    public static function input(string $name = 'slug'): TextInput
    {
        return TextInput::make($name)
            ->maxLength(191)
            ->rule(static function (): Closure {
                return static function (string $attribute, mixed $value, Closure $fail): void {
                    // Blank is allowed: some resources auto-generate the slug
                    // from the title when it is left empty. Validating '' here
                    // would break those, and an empty slug never reaches the
                    // sitemap anyway.
                    if (blank($value)) {
                        return;
                    }

                    if (preg_match(self::PATTERN, (string) $value) !== 1) {
                        $fail('The slug may only contain lowercase letters, numbers and single hyphens (for example: crm-platform).');
                    }
                };
            });
    }
}
