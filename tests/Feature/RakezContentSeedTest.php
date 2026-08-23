<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\System;
use Database\Seeders\HexaPortfolioSeeder;
use Database\Seeders\RakezContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RakezContentSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_rakez_package_seeds_published_system_case_studies_and_articles(): void
    {
        Storage::fake('public');

        $this->seed(RakezContentSeeder::class);

        $system = System::query()->where('slug', 'rakez-erp')->firstOrFail();

        $this->assertTrue($system->is_published);
        $this->assertTrue($system->is_featured);
        $this->assertSame(System::TYPE_BUSINESS_SYSTEM, $system->type);
        $this->assertSame('systems/rakez-cover.webp', $system->cover_image);
        Storage::disk('public')->assertExists('systems/rakez-cover.webp');

        $this->assertSame(5, CaseStudy::query()
            ->where('system_id', $system->id)
            ->where('is_published', true)
            ->count());

        $this->assertDatabaseHas('case_studies', [
            'slug' => 'rakez-erp-operating-platform',
            'system_id' => $system->id,
            'is_featured' => true,
            'is_published' => true,
        ]);

        $this->assertDatabaseHas('case_studies', [
            'slug' => 'rakez-operational-ai-execution-gates',
            'video_url' => null,
            'is_published' => true,
        ]);

        $this->assertSame(6, Article::query()
            ->whereIn('slug', [
                'erp-is-not-digital-spreadsheets',
                'lead-to-contract-operational-source-of-truth',
                'marketing-attribution-inside-erp',
                'financial-workflows-need-versioned-history',
                'operational-ai-inherits-application-policy',
                'rbac-is-the-path-not-the-permission-count',
            ])
            ->where('is_published', true)
            ->count());
    }

    public function test_portfolio_systems_are_published_by_the_correct_model_flag(): void
    {
        Storage::fake('public');

        $this->seed(HexaPortfolioSeeder::class);

        foreach (['dhura', 'leadscope-ai', 'hirelens-ai', 'smartq', 'restocafe-os'] as $slug) {
            $system = System::query()->where('slug', $slug)->firstOrFail();
            $this->assertTrue($system->is_published, "Expected {$slug} to be published.");
        }
    }
}
