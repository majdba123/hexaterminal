<?php

namespace Tests\Feature;

use App\Models\System;
use Database\Seeders\SystemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_approved_malik_system_with_public_media_idempotently(): void
    {
        Storage::fake('public');

        $this->seed(SystemsSeeder::class);

        $this->assertDatabaseCount('systems', 2);

        $system = System::query()->where('slug', 'malik-group')->firstOrFail();

        $this->assertSame(System::TYPE_CLIENT_SYSTEM, $system->type);
        $this->assertSame('Furniture E-commerce & Product Catalog', $system->category);
        $this->assertSame('Malik Group Furniture Catalog', $system->getTranslation('name', 'en'));
        $this->assertSame("\u{0643}\u{062A}\u{0627}\u{0644}\u{0648}\u{062C} \u{0623}\u{062B}\u{0627}\u{062B} Malik Group", $system->getTranslation('name', 'ar'));
        $this->assertSame(['Laravel', 'Blade'], $system->tech_stack);
        $this->assertTrue($system->is_featured);
        $this->assertTrue($system->is_published);
        $this->assertSame('published', $system->status);
        $this->assertNull($system->demo_url);
        $this->assertSame('https://malik.hexaterminal.com/', $system->live_url);
        $this->assertSame('systems/malik-group-cover.png', $system->cover_image);
        $this->assertSame([
            'systems/gallery/malik-group-01-shop-by-room.png',
            'systems/gallery/malik-group-02-full-catalog.png',
            'systems/gallery/malik-group-03-category-page.png',
            'systems/gallery/malik-group-04-product-detail.png',
        ], $system->gallery);

        Storage::disk('public')->assertExists($system->cover_image);
        foreach ($system->gallery as $image) {
            Storage::disk('public')->assertExists($image);
        }

        $this->getJson('/api/v1/public/systems/malik-group?locale=en')
            ->assertOk()
            ->assertJsonPath('data.slug', 'malik-group')
            ->assertJsonPath('data.type', System::TYPE_CLIENT_SYSTEM)
            ->assertJsonPath('data.name', 'Malik Group Furniture Catalog')
            ->assertJsonPath('data.full_description', 'Malik Group Furniture Catalog is a customer-facing furniture and interiors website designed around product discovery. Visitors can browse collections by room, review new arrivals, explore the full catalog, filter products by category, name, and price, and open dedicated product pages with image galleries, pricing, category context, and direct WhatsApp enquiry actions. The result is a structured public catalog that gives the business one clear digital destination for presenting a changing furniture inventory and gives customers a simple path from discovery to enquiry.')
            ->assertJsonPath('data.problem', 'A furniture business with products spread across multiple room categories needs a structured way to present a changing catalog without making customers search through disconnected product posts or ask for basic product information one item at a time. Customers need to understand what is available, browse relevant categories, compare visible prices, inspect product photos, and reach the business when a product interests them.')
            ->assertJsonPath('data.solution', 'The website turns the furniture inventory into a structured storefront: room-based collections, new-arrival discovery, a searchable and price-filterable full catalog, product cards with key information, dedicated product detail pages with multi-image galleries, category navigation, and direct WhatsApp enquiry actions.')
            ->assertJsonPath('data.business_outcomes', 'Centralizes the public product catalog in one branded digital destination'."\n".'Makes a multi-category furniture catalog easier to browse and filter'."\n".'Gives customers more product context before contacting the business'."\n".'Creates a direct path from product discovery to WhatsApp enquiry'."\n".'Supports presentation of both new arrivals and the broader catalog')
            ->assertJsonPath('data.target_audience', 'Customers browsing furniture and interiors for living rooms, bedrooms, dining spaces, home offices, storage, and related home furnishing needs.')
            ->assertJsonPath('data.live_url', 'https://malik.hexaterminal.com/')
            ->assertJsonPath('data.tech_stack', ['Laravel', 'Blade'])
            ->assertJsonPath('data.cover_image', url('/storage/systems/malik-group-cover.png'))
            ->assertJsonCount(4, 'data.gallery')
            ->assertJsonPath('data.gallery.0', url('/storage/systems/gallery/malik-group-01-shop-by-room.png'))
            ->assertJsonPath('data.gallery.3', url('/storage/systems/gallery/malik-group-04-product-detail.png'));

        $this->getJson('/api/v1/public/systems/malik-group?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.name', "\u{0643}\u{062A}\u{0627}\u{0644}\u{0648}\u{062C} \u{0623}\u{062B}\u{0627}\u{062B} Malik Group")
            ->assertJsonPath('data.tagline', "\u{0645}\u{0648}\u{0642}\u{0639} \u{0643}\u{062A}\u{0627}\u{0644}\u{0648}\u{062C} \u{0623}\u{062B}\u{0627}\u{062B} \u{0635}\u{064F}\u{0645}\u{0645} \u{0644}\u{062A}\u{0633}\u{0647}\u{064A}\u{0644} \u{0627}\u{0643}\u{062A}\u{0634}\u{0627}\u{0641} \u{0627}\u{0644}\u{0645}\u{0646}\u{062A}\u{062C}\u{0627}\u{062A} \u{0648}\u{0627}\u{0633}\u{062A}\u{0639}\u{0631}\u{0627}\u{0636} \u{062A}\u{0641}\u{0627}\u{0635}\u{064A}\u{0644}\u{0647}\u{0627} \u{0648}\u{0627}\u{0644}\u{062A}\u{0648}\u{0627}\u{0635}\u{0644} \u{0627}\u{0644}\u{0645}\u{0628}\u{0627}\u{0634}\u{0631} \u{0645}\u{0639} \u{0627}\u{0644}\u{0639}\u{0645}\u{0644}\u{0627}\u{0621}.");

        $this->seed(SystemsSeeder::class);

        $this->assertDatabaseCount('systems', 2);
        $this->assertSame(['malik-group', 'vetora'], System::query()->orderBy('sort_order')->pluck('slug')->all());
        $this->assertCount(12, Storage::disk('public')->allFiles('systems'));
    }

    public function test_system_resource_uses_the_active_public_storage_url_contract(): void
    {
        Storage::fake('public');
        config([
            'app.public_media_url' => 'https://api.hexaterminal.test',
            'filesystems.disks.public.url' => '/storage',
        ]);

        $this->seed(SystemsSeeder::class);

        $this->getJson('/api/v1/public/systems/malik-group')
            ->assertOk()
            ->assertJsonPath('data.cover_image', 'https://api.hexaterminal.test/storage/systems/malik-group-cover.png')
            ->assertJsonPath('data.gallery.3', 'https://api.hexaterminal.test/storage/systems/gallery/malik-group-04-product-detail.png');
    }

    public function test_it_seeds_the_packaged_vetora_system_with_public_media_and_empty_tech_stack_idempotently(): void
    {
        Storage::fake('public');

        $this->seed(SystemsSeeder::class);

        $system = System::query()->where('slug', 'vetora')->firstOrFail();

        $this->assertSame(System::TYPE_CLIENT_SYSTEM, $system->type);
        $this->assertSame('Agriculture & Veterinary Marketplace Platform', $system->category);
        $this->assertSame('Vetora', $system->getTranslation('name', 'en'));
        $this->assertSame("\u{0641}\u{064A}\u{062A}\u{0648}\u{0631}\u{0627}", $system->getTranslation('name', 'ar'));
        $this->assertSame([], $system->tech_stack);
        $this->assertTrue($system->is_featured);
        $this->assertTrue($system->is_published);
        $this->assertSame('published', $system->status);
        $this->assertNull($system->demo_url);
        $this->assertSame('https://msz.hexaterminal.com/', $system->live_url);
        $this->assertSame('systems/vetora-cover-public.png', $system->cover_image);
        $this->assertSame([
            'systems/gallery/vetora-00-public-marketplace.png',
            'systems/gallery/vetora-01-agriculture-vendor-dashboard.png',
            'systems/gallery/vetora-02-product-moderation.png',
            'systems/gallery/vetora-03-agriculture-syndicate-dashboard.png',
            'systems/gallery/vetora-04-veterinary-syndicate-dashboard.png',
            'systems/gallery/vetora-05-structured-product-entry.png',
        ], $system->gallery);

        Storage::disk('public')->assertExists($system->cover_image);
        foreach ($system->gallery as $image) {
            Storage::disk('public')->assertExists($image);
        }

        $this->getJson('/api/v1/public/systems/vetora?locale=en')
            ->assertOk()
            ->assertJsonPath('data.slug', 'vetora')
            ->assertJsonPath('data.name', 'Vetora')
            ->assertJsonPath('data.tech_stack', [])
            ->assertJsonPath('data.live_url', 'https://msz.hexaterminal.com/')
            ->assertJsonPath('data.cover_image', url('/storage/systems/vetora-cover-public.png'))
            ->assertJsonCount(6, 'data.gallery')
            ->assertJsonPath('data.gallery.0', url('/storage/systems/gallery/vetora-00-public-marketplace.png'))
            ->assertJsonPath('data.gallery.5', url('/storage/systems/gallery/vetora-05-structured-product-entry.png'));

        $this->getJson('/api/v1/public/systems/vetora?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.name', "\u{0641}\u{064A}\u{062A}\u{0648}\u{0631}\u{0627}");

        $this->seed(SystemsSeeder::class);

        $this->assertDatabaseCount('systems', 2);
        $this->assertSame(1, System::query()->where('slug', 'vetora')->count());
        $this->assertCount(12, Storage::disk('public')->allFiles('systems'));
    }
}
