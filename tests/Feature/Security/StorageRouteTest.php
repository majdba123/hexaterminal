<?php

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StorageRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_serves_existing_public_media(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/test.png', 'fake-png-bytes');

        $this->get('/api/storage/media/test.png')->assertOk();
    }

    public function test_blocks_path_traversal(): void
    {
        Storage::fake('public');

        $this->get('/api/storage/..%2F..%2F.env')->assertNotFound();
        $this->get('/api/storage/media/..%2f..%2f..%2f.env')->assertNotFound();
    }

    public function test_blocks_dotfiles(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('.hidden', 'secret');

        $this->get('/api/storage/.hidden')->assertNotFound();
    }

    public function test_blocks_disallowed_extensions(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('shell.php', '<?php echo "pwned";');
        Storage::disk('public')->put('config.yaml', 'secret: value');

        $this->get('/api/storage/shell.php')->assertNotFound();
        $this->get('/api/storage/config.yaml')->assertNotFound();
    }

    /**
     * An SVG is an active document: navigating to one executes its inline
     * script in THIS origin, which also serves the /cms panel. Serving one
     * inline is therefore stored XSS against an authenticated CMS session,
     * so the extension allowlist must refuse it even though it is an image.
     */
    public function test_blocks_svg_because_it_executes_script_in_this_origin(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put(
            'media/payload.svg',
            '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>',
        );

        $this->get('/api/storage/media/payload.svg')->assertNotFound();
    }

    public function test_blocks_html_documents(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/payload.html', '<script>alert(1)</script>');
        Storage::disk('public')->put('media/payload.xhtml', '<script>alert(1)</script>');

        $this->get('/api/storage/media/payload.html')->assertNotFound();
        $this->get('/api/storage/media/payload.xhtml')->assertNotFound();
    }
}
