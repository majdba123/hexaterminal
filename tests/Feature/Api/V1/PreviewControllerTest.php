<?php

namespace Tests\Feature\Api\V1;

use App\Models\TrustPage;
use App\Models\User;
use App\Services\PreviewTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_token_renders_an_unpublished_unapproved_trust_page(): void
    {
        $page = TrustPage::create([
            'slug' => 'security',
            'page_type' => 'security',
            'title' => ['en' => 'Security'],
            'sections' => ['en' => [['heading' => 'Draft', 'body' => 'Not yet approved.']]],
            'is_published' => false,
            'status' => 'draft',
            'founder_approved' => false,
            'security_approved' => false,
        ]);

        $minted = app(PreviewTokenService::class)->mint($page, 'en', null);

        // Public endpoint 404s -- draft/unapproved content stays fail-closed.
        $this->getJson('/api/v1/public/trust-pages/security')->assertNotFound();

        // The preview token bypasses that, on purpose.
        $response = $this->getJson('/api/v1/public/preview/'.$minted['token'])->assertOk();
        $response->assertJsonPath('data.type', 'trust_page');
        $response->assertJsonPath('data.record.slug', 'security');
        $response->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->assertSame('no-store, private', $response->headers->get('Cache-Control'));
    }

    public function test_invalid_token_404s(): void
    {
        $this->getJson('/api/v1/public/preview/not-a-real-token')->assertNotFound();
    }

    public function test_expired_token_404s(): void
    {
        $page = TrustPage::create([
            'slug' => 'process',
            'page_type' => 'process',
            'title' => ['en' => 'Process'],
            'sections' => ['en' => [['heading' => 'Step', 'body' => 'Body.']]],
        ]);

        $minted = app(PreviewTokenService::class)->mint($page, 'en', null, ttlMinutes: 1);
        $minted['preview']->update(['expires_at' => now()->subMinute()]);

        $this->getJson('/api/v1/public/preview/'.$minted['token'])->assertNotFound();
    }

    public function test_revoked_token_404s(): void
    {
        $page = TrustPage::create([
            'slug' => 'technology',
            'page_type' => 'technology',
            'title' => ['en' => 'Technology'],
            'sections' => ['en' => [['heading' => 'Step', 'body' => 'Body.']]],
        ]);

        $minted = app(PreviewTokenService::class)->mint($page, 'en', null);
        $minted['preview']->revoke();

        $this->getJson('/api/v1/public/preview/'.$minted['token'])->assertNotFound();
    }

    public function test_access_is_recorded(): void
    {
        $page = TrustPage::create([
            'slug' => 'accessibility',
            'page_type' => 'accessibility',
            'title' => ['en' => 'Accessibility'],
            'sections' => ['en' => [['heading' => 'Step', 'body' => 'Body.']]],
        ]);

        $user = User::create([
            'name' => 'Editor',
            'email' => 'editor@hexaterminal.test',
            'password' => bcrypt('a-long-secure-password'),
        ]);

        $minted = app(PreviewTokenService::class)->mint($page, 'en', $user);

        $this->getJson('/api/v1/public/preview/'.$minted['token'])->assertOk();
        $this->getJson('/api/v1/public/preview/'.$minted['token'])->assertOk();

        $minted['preview']->refresh();
        $this->assertSame(2, $minted['preview']->access_count);
        $this->assertNotNull($minted['preview']->last_accessed_at);
        $this->assertSame($user->id, $minted['preview']->created_by);
    }
}
