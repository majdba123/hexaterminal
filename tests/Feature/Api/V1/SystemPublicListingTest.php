<?php

namespace Tests\Feature\Api\V1;

use App\Models\System;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemPublicListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_systems_listing_uses_cms_sort_order_and_excludes_unpublished_records(): void
    {
        $this->makeSystem('later-system', 20);
        $this->makeSystem('first-system', 5);
        $this->makeSystem('draft-system', 0, false);

        $this->getJson('/api/v1/public/systems')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'first-system')
            ->assertJsonPath('data.1.slug', 'later-system')
            ->assertJsonCount(2, 'data');
    }

    private function makeSystem(string $slug, int $sortOrder, bool $published = true): System
    {
        return System::create([
            'slug' => $slug,
            'type' => System::TYPE_BUSINESS_SYSTEM,
            'name' => ['en' => 'CMS system'],
            'tagline' => ['en' => 'CMS tagline'],
            'short_description' => ['en' => 'CMS short description'],
            'is_published' => $published,
            'sort_order' => $sortOrder,
        ]);
    }
}
