<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;

/**
 * Shared, allowlisted upload fields for every content resource.
 *
 * Filament's own `->image()` helper accepts `image/*`, which INCLUDES
 * `image/svg+xml`, and a bare `FileUpload::make()` accepts anything at all
 * (Livewire's default temporary-upload rules are only
 * `['required', 'file', 'max:12288']` -- no extension or MIME filter). That
 * matters more than it looks, because:
 *
 *  * uploads land on the `public` disk, which `storage:link` exposes as
 *    `public/storage` and the webserver then serves as plain static files --
 *    bypassing Laravel's middleware entirely; and
 *  * Filament stores files as `Str::ulid() . '.' . getClientOriginalExtension()`,
 *    i.e. the CLIENT chooses the extension.
 *
 * So an unrestricted field lets a CMS editor place an arbitrary `.php`, `.svg`
 * or `.html` file inside the document root. The webserver config refuses to
 * interpret or inline-render anything under /storage/ (see
 * deploy/staging/nginx/api-staging.conf), and these allowlists are the
 * application-layer half of that same fix -- neither is sufficient alone.
 *
 * Note SVG is deliberately absent from the image list: an SVG is an active
 * document that executes inline script when navigated to directly, and the
 * origin serving media also serves the /cms panel.
 */
class Uploads
{
    /** Raster formats only -- no SVG, no PDF, nothing interpretable. */
    public const IMAGE_MIMES = [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/avif',
        'image/gif',
    ];

    /** Documents an editor may attach (CVs, one-pagers). */
    public const DOCUMENT_MIMES = [
        'application/pdf',
    ];

    /**
     * Drop-in replacement for `FileUpload::make($name)->image()`.
     */
    public static function image(string $name): FileUpload
    {
        return FileUpload::make($name)
            ->image()
            ->acceptedFileTypes(self::IMAGE_MIMES);
    }

    /**
     * Document upload (PDF only). Never call bare `FileUpload::make()` for
     * these -- an unrestricted field is what made arbitrary upload possible.
     */
    public static function document(string $name): FileUpload
    {
        return FileUpload::make($name)
            ->acceptedFileTypes(self::DOCUMENT_MIMES);
    }

    /**
     * Alternative text for an uploaded image. Pair this with every image field
     * the PUBLIC site renders.
     *
     * Deliberately optional. Leaving it blank marks the image decorative
     * (`alt=""` on the frontend), which is the CORRECT answer whenever the
     * image adds nothing beyond adjacent visible text -- a card thumbnail
     * inside a link already labelled by its title, or a cover image directly
     * under a heading that names the same thing. Describing those makes screen
     * readers announce the same words twice. Only a person looking at the
     * image can make that call, which is why this is an editor field with a
     * safe default rather than something the template derives.
     *
     * Translatable: an Arabic reader needs the Arabic description.
     */
    public static function altText(string $name, string $imageLabel = 'image'): TextInput
    {
        return TextInput::make($name)
            ->label('Alt text')
            ->maxLength(160)
            ->helperText(
                "What the {$imageLabel} shows, for screen readers and image search. "
                .'Leave blank if it is purely decorative or just repeats nearby text.'
            );
    }
}
