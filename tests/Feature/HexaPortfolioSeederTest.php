<?php

namespace Tests\Feature;

use App\Models\System;
use App\Models\TeamMember;
use Database\Seeders\HexaPortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HexaPortfolioSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_publishes_yusuf_and_the_approved_hexa_portfolio_idempotently(): void
    {
        Storage::fake('public');

        $seeder = app(HexaPortfolioSeeder::class);
        $seeder->run();
        $seeder->run();

        $member = TeamMember::query()->where('slug', 'yusuf-jojeh')->sole();

        $this->assertSame('Yusuf Mohammad Jojeh', $member->full_name);
        $this->assertSame('Co-Founder & Engineering Lead', $member->getTranslation('position', 'en'));
        $this->assertTrue($member->is_founder);
        $this->assertTrue($member->is_published);
        $this->assertTrue($member->publication_consent);
        $this->assertNull($member->email);
        $this->assertNull($member->phone);

        $slugs = ['dhura', 'leadscope-ai', 'hirelens-ai', 'smartq', 'restocafe-os'];

        $this->assertSame(5, System::query()->whereIn('slug', $slugs)->count());

        foreach ($slugs as $slug) {
            $system = System::query()->where('slug', $slug)->sole();
            $this->assertTrue($system->is_published);
            $this->assertTrue($system->is_featured);
            $this->assertNotEmpty($system->cover_image);
            Storage::disk('public')->assertExists($system->cover_image);
        }
    }
}
