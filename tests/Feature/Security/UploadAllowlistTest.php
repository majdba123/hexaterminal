<?php

namespace Tests\Feature\Security;

use App\Filament\Support\Uploads;
use Filament\Forms\Components\FileUpload;
use Tests\TestCase;

/**
 * Guards the application half of the arbitrary-upload fix.
 *
 * Uploads land on the `public` disk, which `storage:link` exposes inside the
 * document root as plain static files -- served by the webserver without any
 * Laravel middleware. Filament names stored files
 * `Str::ulid() . '.' . getClientOriginalExtension()`, so the CLIENT picks the
 * extension. An upload field with no `acceptedFileTypes()` therefore lets a
 * CMS editor place an arbitrary `.php` / `.svg` / `.html` file in the document
 * root: RCE if the webserver interprets it, stored XSS against the /cms origin
 * if it merely renders it.
 *
 * The webserver config is the other half (deploy/staging/nginx/api-staging.conf
 * refuses to interpret or inline-render anything under /storage/). Neither half
 * is sufficient alone, so both are tested.
 */
class UploadAllowlistTest extends TestCase
{
    public function test_image_helper_rejects_svg(): void
    {
        // SVG is an active document -- it must never be an accepted "image",
        // even though Filament's own ->image() helper (image/*) allows it.
        $this->assertNotContains('image/svg+xml', Uploads::IMAGE_MIMES);
        $this->assertNotContains('image/*', Uploads::IMAGE_MIMES);
    }

    public function test_image_helper_allows_only_raster_formats(): void
    {
        $upload = Uploads::image('cover_image');
        $accepted = $upload->getAcceptedFileTypes();

        $this->assertSame(Uploads::IMAGE_MIMES, $accepted);
        $this->assertSame('public', $upload->getDiskName());

        foreach ($accepted as $mime) {
            $this->assertStringStartsWith('image/', $mime);
            $this->assertNotSame('image/svg+xml', $mime);
        }
    }

    public function test_document_helper_allows_only_pdf(): void
    {
        $upload = Uploads::document('cv_file');

        $this->assertSame(
            ['application/pdf'],
            $upload->getAcceptedFileTypes(),
        );
        $this->assertSame('public', $upload->getDiskName());
    }

    /**
     * Regression guard for the actual root cause: `->image()` on its own
     * accepts `image/*`, and `image/svg+xml` matches that wildcard. If this
     * ever stops being true upstream, the Uploads helper is still correct --
     * but the reason it exists would have changed, so fail loudly.
     */
    public function test_filament_bare_image_helper_is_still_permissive(): void
    {
        $this->assertSame(
            ['image/*'],
            FileUpload::make('probe')->image()->getAcceptedFileTypes(),
        );
    }
}
