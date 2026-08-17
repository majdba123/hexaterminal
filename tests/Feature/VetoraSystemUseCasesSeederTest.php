<?php

namespace Tests\Feature;

use App\Models\System;
use App\Models\SystemUseCase;
use Database\Seeders\SystemsSeeder;
use Database\Seeders\VetoraSystemUseCasesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VetoraSystemUseCasesSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_packaged_vetora_use_cases_idempotently_with_public_safe_media_only(): void
    {
        Storage::fake('public');

        $this->seed(SystemsSeeder::class);
        $this->seed(VetoraSystemUseCasesSeeder::class);

        $system = System::query()->where('slug', 'vetora')->firstOrFail();
        $useCases = SystemUseCase::query()
            ->where('system_id', $system->id)
            ->orderBy('sort_order')
            ->get();

        $this->assertCount(8, $useCases);
        $this->assertSame([
            'choose-specialized-market',
            'discover-and-order-supplies',
            'vendor-manages-store',
            'vendor-submits-structured-product',
            'employee-moderates-products',
            'syndicate-monitors-sector',
            'vendor-tracks-orders-and-commissions',
            'admin-manages-marketplace',
        ], $useCases->pluck('slug')->all());

        $this->assertTrue($useCases->every(fn (SystemUseCase $useCase): bool => $useCase->is_published));
        $this->assertSame('system-use-cases/vetora/01-public-market-entry.png', $useCases[0]->image);
        $this->assertSame('Choose the Relevant Professional Market', $useCases[0]->getTranslation('title', 'en'));
        $this->assertSame('اختيار السوق المتخصص المناسب', $useCases[0]->getTranslation('title', 'ar'));
        $this->assertSame('system-use-cases/vetora/08-admin-operations.png', $useCases[7]->image);

        foreach ($useCases as $useCase) {
            Storage::disk('public')->assertExists($useCase->image);
        }

        $response = $this->getJson('/api/v1/public/systems/vetora?locale=en')
            ->assertOk()
            ->assertJsonCount(8, 'data.use_cases')
            ->assertJsonPath('data.use_cases.0.slug', 'choose-specialized-market')
            ->assertJsonPath('data.use_cases.0.title', 'Choose the Relevant Professional Market')
            ->assertJsonPath('data.use_cases.0.image', url('/storage/system-use-cases/vetora/01-public-market-entry.png'))
            ->assertJsonPath('data.use_cases.7.slug', 'admin-manages-marketplace');

        $gallery = $response->json('data.gallery');
        $useCaseImages = collect($response->json('data.use_cases'))->pluck('image')->all();

        $this->assertNotContains(url('/storage/assets/evidence_only/customer-domain-selection-en.png'), $gallery);
        $this->assertNotContains(url('/storage/assets/evidence_only/customer-domain-selection-en.png'), $useCaseImages);
        $this->assertContains(url('/storage/systems/gallery/vetora-00-public-marketplace.png'), $gallery);

        $this->getJson('/api/v1/public/systems/vetora?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.use_cases.0.title', 'اختيار السوق المتخصص المناسب')
            ->assertJsonPath('data.use_cases.7.title', 'إدارة المنصة لكامل منظومة السوق');

        $this->seed(VetoraSystemUseCasesSeeder::class);

        $this->assertSame(8, SystemUseCase::query()->where('system_id', $system->id)->count());
    }
}
