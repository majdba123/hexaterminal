<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use Database\Seeders\ServicesSeeder;
use Database\Seeders\SystemsSeeder;
use Database\Seeders\VetoraCaseStudySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VetoraCaseStudyFeaturedSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_canonical_vetora_seed_marks_the_case_study_featured(): void
    {
        $payload = json_decode(
            File::get(base_path('database/seeders/data/vetora_case_study_seed_data.json')),
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        $this->assertTrue($payload['case_study']['featured']);
    }

    public function test_vetora_case_study_seeder_persists_the_featured_state(): void
    {
        Storage::fake('public');
        $this->seed([ServicesSeeder::class, SystemsSeeder::class]);

        $this->seed(VetoraCaseStudySeeder::class);

        $this->assertTrue(
            CaseStudy::query()
                ->where('slug', 'vetora-specialized-marketplace-operations')
                ->sole()
                ->is_featured,
        );
    }
}
