<?php

use App\Models\System;
use App\Models\TeamMember;
use Database\Seeders\HexaPortfolioSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('publishes Yusuf and the approved Hexa portfolio idempotently', function () {
    Storage::fake('public');

    $seeder = app(HexaPortfolioSeeder::class);
    $seeder->run();
    $seeder->run();

    $member = TeamMember::query()->where('slug', 'yusuf-jojeh')->sole();

    expect($member->full_name)->toBe('Yusuf Mohammad Jojeh')
        ->and($member->getTranslation('position', 'en'))->toBe('Co-Founder & Engineering Lead')
        ->and($member->is_founder)->toBeTrue()
        ->and($member->is_published)->toBeTrue()
        ->and($member->publication_consent)->toBeTrue()
        ->and($member->email)->toBeNull()
        ->and($member->phone)->toBeNull();

    $slugs = ['dhura', 'leadscope-ai', 'hirelens-ai', 'smartq', 'restocafe-os'];

    expect(System::query()->whereIn('slug', $slugs)->count())->toBe(5);

    foreach ($slugs as $slug) {
        $system = System::query()->where('slug', $slug)->sole();
        expect($system->is_published)->toBeTrue()
            ->and($system->is_featured)->toBeTrue()
            ->and($system->cover_image)->not->toBeEmpty();
        Storage::disk('public')->assertExists($system->cover_image);
    }
});
