<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Services\ServiceSeedImageSynchronizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use JsonException;

class ServicesSeeder extends Seeder
{
    private const DATA_FILE = 'database/seeders/data/services_seed_data.json';

    public function run(): void
    {
        $images = app(ServiceSeedImageSynchronizer::class);

        foreach ($this->serviceDefinitions() as $definition) {
            $translations = $definition['translations'];
            $slug = $definition['slug'];

            $service = Service::firstOrNew(['slug' => $slug]);
            $service->fill([
                'name' => $this->translations($translations, 'name'),
                'tagline' => $this->translations($translations, 'tagline'),
                'summary' => $this->translations($translations, 'summary'),
                'description' => $this->translations($translations, 'description'),
                'icon' => $definition['icon'],
                'cover_image' => $images->sync($definition['cover_image_source']),
                'cover_image_alt' => $this->translations($translations, 'alt_text'),
                'features' => $this->translations($translations, 'features'),
                'tech_stack' => $definition['tech_stack'],
                'status' => 'published',
                'sort_order' => $definition['sort_order'],
            ]);

            if (! $service->published_at) {
                $service->published_at = now();
            }

            $service->save();
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function serviceDefinitions(): array
    {
        try {
            $data = json_decode(
                File::get(base_path(self::DATA_FILE)),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new \RuntimeException('The approved services seed data is invalid JSON.', previous: $exception);
        }

        if (! is_array($data['services'] ?? null)) {
            throw new \RuntimeException('The approved services seed data has no services collection.');
        }

        return $data['services'];
    }

    /** @param array<string, array<string, mixed>> $translations */
    private function translations(array $translations, string $field): array
    {
        return collect($translations)
            ->mapWithKeys(fn (array $translation, string $locale): array => [$locale => $translation[$field]])
            ->all();
    }

}
