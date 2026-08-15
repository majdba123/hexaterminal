<?php

namespace Database\Seeders;

use App\Models\TeamMember;
use App\Services\TeamMemberSeedImageSynchronizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use JsonException;

class TeamMembersSeeder extends Seeder
{
    private const DATA_FILE = 'database/seeders/data/team_member_seed_data.json';

    public function run(): void
    {
        $data = $this->seedData()['member'];
        $photo = app(TeamMemberSeedImageSynchronizer::class);

        $member = TeamMember::firstOrNew(['slug' => $data['slug']]);
        $member->fill([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'position' => $data['position'],
            'bio' => $data['bio'],
            'specialization' => $data['specialization'],
            'expertise' => $data['expertise'],
            'languages' => $data['languages'],
            'location' => $data['location'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'photo' => $photo->sync($data['image_source_filename'], $data['target_public_disk_path']),
            'photo_alt' => $data['photo_alt'],
            'github_url' => $data['links']['github'] ?? null,
            'linkedin_url' => null,
            'cv_file' => $data['cv'],
            'is_published' => (bool) $data['publication_intent']['is_published'],
            'publication_consent' => (bool) $data['publication_intent']['publication_consent'],
            'is_founder' => (bool) $data['publication_intent']['is_founder'],
            'seo_eligible' => true,
            'person_jsonld_eligible' => true,
            'sort_order' => (int) $data['sort_order'],
        ]);

        $member->save();
    }

    /** @return array<string, mixed> */
    private function seedData(): array
    {
        try {
            $data = json_decode(
                File::get(base_path(self::DATA_FILE)),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new \RuntimeException('The approved team member seed data is invalid JSON.', previous: $exception);
        }

        if (! is_array($data) || ! is_array($data['member'] ?? null)) {
            throw new \RuntimeException('The approved team member seed data has no member payload.');
        }

        return $data;
    }
}
