<?php

namespace Database\Seeders;

use App\Models\System;
use App\Services\SystemSeedImageSynchronizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use JsonException;

class SystemsSeeder extends Seeder
{
    private const DATA_FILE = 'database/seeders/data/malik_system_seed_data.json';

    public function run(): void
    {
        $definition = $this->systemDefinition();
        $images = app(SystemSeedImageSynchronizer::class);

        $system = System::firstOrNew(['slug' => $definition['slug']]);
        $system->fill([
            'type' => $definition['type'],
            'category' => $definition['category'],
            'name' => $definition['name'],
            'tagline' => $definition['tagline'],
            'short_description' => $definition['short_description'],
            'full_description' => $definition['full_description'],
            'problem' => $definition['problem'],
            'solution' => $definition['solution'],
            'features' => $definition['features'],
            'business_outcomes' => $definition['business_outcomes'],
            'target_audience' => $definition['target_audience'],
            'tech_stack' => $definition['tech_stack'] ?? [],
            'cover_image' => $images->sync($definition['cover_image']),
            'cover_image_alt' => $definition['cover_image_alt'],
            'gallery' => $images->syncMany($definition['gallery'] ?? []),
            'demo_url' => $definition['demo_url'],
            'live_url' => $definition['live_url'],
            'is_featured' => (bool) $definition['is_featured'],
            'status' => $definition['publication_intent'] === 'published' ? 'published' : 'draft',
            'sort_order' => (int) $definition['sort_order'],
        ]);

        if (! $system->published_at && $definition['publication_intent'] === 'published') {
            $system->published_at = now();
        }

        $system->save();
    }

    /** @return array<string, mixed> */
    private function systemDefinition(): array
    {
        try {
            $data = json_decode(
                File::get(base_path(self::DATA_FILE)),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new \RuntimeException('The approved Malik system seed data is invalid JSON.', previous: $exception);
        }

        if (! is_array($data['system'] ?? null)) {
            throw new \RuntimeException('The approved Malik system seed data has no system payload.');
        }

        return $data['system'];
    }
}
