<?php

namespace Tests\Feature\Api\V1;

use App\Models\PublicClaim;
use App\Models\TrustPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrustPageVisibilityTest extends TestCase
{
    use RefreshDatabase;

    private function baseAttributes(array $overrides = []): array
    {
        return array_merge([
            'page_type' => 'process',
            'title' => ['en' => 'Our Process'],
            'sections' => ['en' => [['heading' => 'Step 1', 'body' => 'We plan.']]],
            'is_published' => true,
            'status' => 'published',
        ], $overrides);
    }

    public function test_published_trust_page_with_content_is_visible(): void
    {
        TrustPage::create($this->baseAttributes(['slug' => 'process']));

        $index = $this->getJson('/api/v1/public/trust-pages')->assertOk();
        $this->assertSame(['process'], collect($index->json('data'))->pluck('slug')->all());

        $this->getJson('/api/v1/public/trust-pages/process')->assertOk();
    }

    public function test_unpublished_trust_page_is_hidden(): void
    {
        TrustPage::create($this->baseAttributes(['slug' => 'draft-process', 'is_published' => false, 'status' => 'draft']));

        $this->getJson('/api/v1/public/trust-pages')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/public/trust-pages/draft-process')->assertNotFound();
    }

    public function test_published_trust_page_without_sections_is_hidden(): void
    {
        TrustPage::create($this->baseAttributes(['slug' => 'empty-process', 'sections' => null]));

        $this->getJson('/api/v1/public/trust-pages')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/public/trust-pages/empty-process')->assertNotFound();
    }

    public function test_security_page_requires_founder_and_security_approval(): void
    {
        TrustPage::create($this->baseAttributes([
            'slug' => 'security',
            'page_type' => 'security',
            'founder_approved' => false,
            'security_approved' => false,
        ]));

        $this->getJson('/api/v1/public/trust-pages')->assertOk()->assertJsonCount(0, 'data');
        $this->getJson('/api/v1/public/trust-pages/security')->assertNotFound();

        TrustPage::where('slug', 'security')->first()->update(['founder_approved' => true, 'security_approved' => true]);

        $this->getJson('/api/v1/public/trust-pages')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/v1/public/trust-pages/security')->assertOk();
    }

    public function test_data_privacy_page_requires_founder_and_legal_approval(): void
    {
        TrustPage::create($this->baseAttributes([
            'slug' => 'data-privacy',
            'page_type' => 'data_privacy',
            'founder_approved' => true,
            'legal_approved' => false,
        ]));

        $this->getJson('/api/v1/public/trust-pages/data-privacy')->assertNotFound();

        TrustPage::where('slug', 'data-privacy')->update(['legal_approved' => true]);

        $this->getJson('/api/v1/public/trust-pages/data-privacy')->assertOk();
    }

    public function test_only_approved_verified_nonconfidential_unexpired_claims_are_embedded(): void
    {
        $page = TrustPage::create($this->baseAttributes(['slug' => 'process-claims']));

        $visible = PublicClaim::create([
            'claimable_type' => TrustPage::class,
            'claimable_id' => $page->id,
            'category' => 'security',
            'claim_text' => 'Visible claim',
            'verification_status' => 'verified',
            'approved_for_publication' => true,
            'confidential' => false,
        ]);

        PublicClaim::create([
            'claimable_type' => TrustPage::class,
            'claimable_id' => $page->id,
            'category' => 'security',
            'claim_text' => 'Unverified claim',
            'verification_status' => 'unverified',
            'approved_for_publication' => true,
            'confidential' => false,
        ]);

        PublicClaim::create([
            'claimable_type' => TrustPage::class,
            'claimable_id' => $page->id,
            'category' => 'security',
            'claim_text' => 'Confidential claim',
            'verification_status' => 'verified',
            'approved_for_publication' => true,
            'confidential' => true,
        ]);

        PublicClaim::create([
            'claimable_type' => TrustPage::class,
            'claimable_id' => $page->id,
            'category' => 'security',
            'claim_text' => 'Expired claim',
            'verification_status' => 'verified',
            'approved_for_publication' => true,
            'confidential' => false,
            'expires_at' => now()->subDay(),
        ]);

        PublicClaim::create([
            'claimable_type' => TrustPage::class,
            'claimable_id' => $page->id,
            'category' => 'security',
            'claim_text' => 'Not approved claim',
            'verification_status' => 'verified',
            'approved_for_publication' => false,
            'confidential' => false,
        ]);

        $response = $this->getJson('/api/v1/public/trust-pages/process-claims')->assertOk();
        $claims = $response->json('data.claims');

        $this->assertCount(1, $claims);
        $this->assertSame($visible->claim_text, $claims[0]['claim_text']);
    }
}
