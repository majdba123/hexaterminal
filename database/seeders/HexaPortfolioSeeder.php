<?php

namespace Database\Seeders;

use App\Models\System;
use App\Models\TeamMember;
use App\Services\SystemSeedImageSynchronizer;
use App\Services\TeamMemberSeedImageSynchronizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use JsonException;

class HexaPortfolioSeeder extends Seeder
{
    private const DATA_FILE = 'database/seeders/data/yusuf_portfolio_seed_data.json';

    public function run(): void
    {
        $data = $this->seedData();
        $this->seedTeamMember($data['team_member']);
        $this->seedSystems($data['systems']);
    }

    /** @param array<string, mixed> $data */
    private function seedTeamMember(array $data): void
    {
        $photos = app(TeamMemberSeedImageSynchronizer::class);
        $member = TeamMember::firstOrNew(['slug' => $data['slug']]);
        $member->fill([
            'first_name' => $data['first_name'], 'last_name' => $data['last_name'],
            'position' => $data['position'], 'bio' => $data['bio'],
            'specialization' => $data['specialization'], 'expertise' => $data['expertise'],
            'languages' => $data['languages'], 'location' => $data['location'],
            'email' => null, 'phone' => null,
            'photo' => $photos->sync('yusuf-jojeh.webp', 'team/yusuf-jojeh.webp'),
            'photo_alt' => $data['photo_alt'], 'github_url' => $data['github_url'],
            'linkedin_url' => $data['linkedin_url'], 'cv_file' => null,
            'is_published' => true, 'publication_consent' => true,
            'is_founder' => true, 'seo_eligible' => true,
            'person_jsonld_eligible' => true, 'sort_order' => (int) $data['sort_order'],
        ]);
        $member->save();
    }

    /** @param list<array<string, mixed>> $systems */
    private function seedSystems(array $systems): void
    {
        $images = app(SystemSeedImageSynchronizer::class);
        foreach ($systems as $data) {
            $system = System::firstOrNew(['slug' => $data['slug']]);
            $system->fill([
                'type' => $data['type'], 'category' => $data['category'],
                'name' => $data['name'], 'tagline' => $data['tagline'],
                'short_description' => $data['short_description'], 'full_description' => $data['full_description'],
                'problem' => $data['problem'], 'solution' => $data['solution'],
                'features' => $data['features'], 'business_outcomes' => $data['business_outcomes'],
                'target_audience' => $data['target_audience'], 'tech_stack' => $data['tech_stack'],
                'cover_image' => $images->sync($data['cover_image']), 'cover_image_alt' => $data['cover_image_alt'],
                'gallery' => $images->syncMany($data['gallery'] ?? []),
                'demo_url' => $data['demo_url'], 'live_url' => $data['live_url'],
                'is_featured' => true, 'status' => 'published', 'sort_order' => (int) $data['sort_order'],
            ]);
            if (! $system->published_at) { $system->published_at = now(); }
            $system->save();
        }
    }

    /** @return array{team_member: array<string, mixed>, systems: list<array<string, mixed>>} */
    private function seedData(): array
    {
        try {
            $data = json_decode(File::get(base_path(self::DATA_FILE)), true, flags: JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new \RuntimeException('The Hexa portfolio seed data is invalid JSON.', previous: $exception);
        }
        if (! is_array($data['team_member'] ?? null) || ! is_array($data['systems'] ?? null)) {
            throw new \RuntimeException('The Hexa portfolio seed data is incomplete.');
        }
        return ['team_member' => $data['team_member'], 'systems' => array_values($data['systems'])];
    }
}
