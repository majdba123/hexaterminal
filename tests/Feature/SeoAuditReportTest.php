<?php

namespace Tests\Feature;

use App\Models\PublicClaim;
use App\Models\Service;
use App\Models\TrustPage;
use App\Services\SeoAuditReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoAuditReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_record_without_seo_title_or_description_is_a_blocker(): void
    {
        Service::create(['slug' => 'bare', 'name' => ['en' => 'Bare'], 'is_published' => true]);

        $report = app(SeoAuditReport::class)->build();
        $checks = collect($report['findings'])->where('slug', 'bare')->pluck('check')->all();

        $this->assertContains('missing_title', $checks);
        $this->assertContains('missing_description', $checks);
        $this->assertGreaterThan(0, $report['blocker_count']);
    }

    public function test_well_formed_seo_record_has_no_blockers(): void
    {
        $service = Service::create([
            'slug' => 'good', 'name' => ['en' => 'Good'], 'summary' => ['en' => 'A summary'],
            'description' => ['en' => 'A full description of what this service does.'], 'is_published' => true,
        ]);
        $service->seo()->create([
            'title' => ['en' => 'A perfectly reasonable SEO title'],
            'description' => ['en' => str_repeat('a well formed meta description sentence. ', 3)],
            'og_image' => '/og.jpg',
        ]);

        $report = app(SeoAuditReport::class)->build();
        $blockers = collect($report['findings'])->where('slug', 'good')->where('severity', 'blocker');

        $this->assertCount(0, $blockers);
    }

    public function test_invalid_canonical_url_is_a_blocker(): void
    {
        $service = Service::create(['slug' => 'bad-canonical', 'name' => ['en' => 'Bad'], 'summary' => ['en' => 'x'], 'is_published' => true]);
        $service->seo()->create([
            'title' => ['en' => 'A perfectly reasonable SEO title'],
            'description' => ['en' => str_repeat('a well formed meta description sentence. ', 3)],
            'canonical_url' => '/relative/not-absolute',
        ]);

        $report = app(SeoAuditReport::class)->build();
        $checks = collect($report['findings'])->where('slug', 'bad-canonical')->pluck('check')->all();

        $this->assertContains('invalid_canonical', $checks);
    }

    public function test_noindex_on_a_type_covered_by_the_static_sitemap_is_a_blocker(): void
    {
        $service = Service::create(['slug' => 'accidentally-noindexed', 'name' => ['en' => 'X'], 'summary' => ['en' => 'x'], 'is_published' => true]);
        $service->seo()->create([
            'title' => ['en' => 'A perfectly reasonable SEO title'],
            'description' => ['en' => str_repeat('a well formed meta description sentence. ', 3)],
            'noindex' => true,
        ]);

        $report = app(SeoAuditReport::class)->build();
        $checks = collect($report['findings'])->where('slug', 'accidentally-noindexed')->pluck('check')->all();

        $this->assertContains('noindex_in_sitemap', $checks);
    }

    public function test_duplicate_seo_titles_are_flagged_for_both_records(): void
    {
        foreach (['dup-a', 'dup-b'] as $slug) {
            $service = Service::create(['slug' => $slug, 'name' => ['en' => $slug], 'summary' => ['en' => 'x'], 'is_published' => true]);
            $service->seo()->create([
                'title' => ['en' => 'Exactly The Same Title Here'],
                'description' => ['en' => str_repeat('a well formed meta description sentence. ', 3)],
            ]);
        }

        $report = app(SeoAuditReport::class)->build();
        $duplicateFindings = collect($report['findings'])->where('check', 'duplicate_title');

        $this->assertCount(2, $duplicateFindings);
    }

    public function test_expired_approved_public_claim_is_a_blocker(): void
    {
        $page = TrustPage::create(['slug' => 'process', 'page_type' => 'process', 'title' => ['en' => 'Process']]);
        PublicClaim::create([
            'claimable_type' => TrustPage::class,
            'claimable_id' => $page->id,
            'category' => 'security',
            'claim_text' => 'Expired',
            'verification_status' => 'verified',
            'approved_for_publication' => true,
            'confidential' => false,
            'expires_at' => now()->subDay(),
        ]);

        $report = app(SeoAuditReport::class)->build();
        $checks = collect($report['findings'])->where('slug', 'process')->pluck('check')->all();

        $this->assertContains('expired_public_claim', $checks);
    }

    public function test_empty_category_scores_100(): void
    {
        $report = app(SeoAuditReport::class)->build();

        $this->assertSame(100, $report['overall_score']);
        $this->assertSame(0, $report['blocker_count']);
    }
}
