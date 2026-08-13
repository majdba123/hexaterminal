<?php

namespace Tests\Feature\Api\V1;

use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeSystemsShowcaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_returns_published_featured_systems_in_cms_sort_order(): void
    {
        $this->makeSystem('later-featured', 20, true);
        $this->makeSystem('not-featured', 0, false);
        $this->makeSystem('first-featured', 10, true);
        $this->makeSystem('unpublished-featured', 1, true, false);

        $response = $this->getJson('/api/v1/public/home')->assertOk();

        $this->assertSame(
            ['first-featured', 'later-featured'],
            collect($response->json('data.featured_systems'))->pluck('slug')->all(),
        );
        $response
            ->assertJsonPath('data.featured_systems.0.name', 'English system')
            ->assertJsonPath('data.featured_systems.0.tagline', 'English tagline')
            ->assertJsonPath('data.featured_systems.0.category', 'Operations');
    }

    public function test_home_featured_systems_use_the_requested_locale_and_are_empty_when_none_qualify(): void
    {
        $system = $this->makeSystem('featured-system', 0, true);

        $this->getJson('/api/v1/public/home?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.featured_systems.0.name', 'نظام معاينة')
            ->assertJsonPath('data.featured_systems.0.tagline', 'وصف معاينة');

        $system->delete();

        $this->getJson('/api/v1/public/home')
            ->assertOk()
            ->assertJsonCount(0, 'data.featured_systems');
    }

    private function makeSystem(string $slug, int $sortOrder, bool $featured, bool $published = true): System
    {
        return System::create([
            'slug' => $slug,
            'type' => System::TYPE_PLATFORM,
            'category' => 'Operations',
            'name' => ['en' => 'English system', 'ar' => 'نظام معاينة'],
            'tagline' => ['en' => 'English tagline', 'ar' => 'وصف معاينة'],
            'short_description' => ['en' => 'English summary', 'ar' => 'ملخص معاينة'],
            'is_featured' => $featured,
            'is_published' => $published,
            'sort_order' => $sortOrder,
        ]);
    }
}
