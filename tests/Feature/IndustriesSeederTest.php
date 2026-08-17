<?php

namespace Tests\Feature;

use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\System;
use Database\Seeders\IndustriesSeeder;
use Database\Seeders\MalikCaseStudySeeder;
use Database\Seeders\ServicesSeeder;
use Database\Seeders\SystemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndustriesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_packaged_industries_with_slug_based_relationships_idempotently(): void
    {
        $this->seed([
            ServicesSeeder::class,
            SystemsSeeder::class,
            MalikCaseStudySeeder::class,
            IndustriesSeeder::class,
        ]);

        $this->assertDatabaseCount('industries', 3);

        $furniture = Industry::query()->with(['systems', 'caseStudies'])->where('slug', 'furniture-interiors')->firstOrFail();
        $agriculture = Industry::query()->with(['systems', 'caseStudies'])->where('slug', 'agriculture-agri-supplies')->firstOrFail();
        $veterinary = Industry::query()->with(['systems', 'caseStudies'])->where('slug', 'veterinary-animal-health')->firstOrFail();
        $malikSystem = System::query()->where('slug', 'malik-group')->firstOrFail();
        $vetora = System::query()->where('slug', 'vetora')->firstOrFail();
        $malikCaseStudy = CaseStudy::query()->where('slug', 'malik-group-furniture-catalog')->firstOrFail();

        $this->assertNull($furniture->icon);
        $this->assertNull($furniture->cover_image);
        $this->assertSame(['malik-group'], $furniture->systems->pluck('slug')->all());
        $this->assertSame(['malik-group-furniture-catalog'], $furniture->caseStudies->pluck('slug')->all());

        $this->assertNull($agriculture->icon);
        $this->assertNull($agriculture->cover_image);
        $this->assertSame(['vetora'], $agriculture->systems->pluck('slug')->all());
        $this->assertCount(0, $agriculture->caseStudies);

        $this->assertNull($veterinary->icon);
        $this->assertNull($veterinary->cover_image);
        $this->assertSame(['vetora'], $veterinary->systems->pluck('slug')->all());
        $this->assertCount(0, $veterinary->caseStudies);

        $this->assertSame(['furniture-interiors'], $malikSystem->fresh()->industries()->pluck('slug')->all());
        $this->assertSame(
            ['agriculture-agri-supplies', 'veterinary-animal-health'],
            $vetora->fresh()->industries()->orderBy('sort_order')->pluck('slug')->all(),
        );
        $this->assertSame(['furniture-interiors'], $malikCaseStudy->fresh()->industries()->pluck('slug')->all());

        $this->getJson('/api/v1/public/industries/furniture-interiors?locale=en')
            ->assertOk()
            ->assertJsonPath('data.slug', 'furniture-interiors')
            ->assertJsonPath('data.name', 'Furniture & Interiors')
            ->assertJsonPath('data.icon', null)
            ->assertJsonPath('data.cover_image', null);

        $this->getJson('/api/v1/public/industries/agriculture-agri-supplies?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.slug', 'agriculture-agri-supplies')
            ->assertJsonPath('data.name', "\u{0627}\u{0644}\u{0632}\u{0631}\u{0627}\u{0639}\u{0629} \u{0648}\u{0627}\u{0644}\u{0645}\u{0633}\u{062A}\u{0644}\u{0632}\u{0645}\u{0627}\u{062A} \u{0627}\u{0644}\u{0632}\u{0631}\u{0627}\u{0639}\u{064A}\u{0629}");

        $this->getJson('/api/v1/public/systems/vetora')
            ->assertOk()
            ->assertJsonPath('data.industries.0.slug', 'agriculture-agri-supplies')
            ->assertJsonPath('data.industries.1.slug', 'veterinary-animal-health');

        $this->getJson('/api/v1/public/systems/malik-group')
            ->assertOk()
            ->assertJsonPath('data.industries.0.slug', 'furniture-interiors')
            ->assertJsonPath('data.case_studies.0.slug', 'malik-group-furniture-catalog');

        $this->seed(IndustriesSeeder::class);

        $this->assertDatabaseCount('industries', 3);
        $this->assertSame(1, Industry::query()->where('slug', 'furniture-interiors')->count());
        $this->assertSame(1, $vetora->fresh()->industries()->where('slug', 'agriculture-agri-supplies')->count());
        $this->assertSame(1, $vetora->fresh()->industries()->where('slug', 'veterinary-animal-health')->count());
        $this->assertSame(1, $malikCaseStudy->fresh()->industries()->where('slug', 'furniture-interiors')->count());
    }
}
