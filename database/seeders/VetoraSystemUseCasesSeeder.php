<?php

namespace Database\Seeders;

use App\Models\System;
use App\Models\SystemUseCase;
use App\Services\SystemSeedImageSynchronizer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use JsonException;

class VetoraSystemUseCasesSeeder extends Seeder
{
    private const DATA_FILE = 'database/seeders/data/vetora_system_use_cases_seed_data.json';

    public function run(): void
    {
        $system = System::query()->where('slug', 'vetora')->first();

        if (! $system) {
            return;
        }

        $images = app(SystemSeedImageSynchronizer::class);
        $payload = $this->payload();

        foreach ($payload['use_cases'] as $definition) {
            $useCase = SystemUseCase::firstOrNew([
                'system_id' => $system->id,
                'slug' => $definition['slug'],
            ]);

            $useCase->fill([
                'title' => $definition['title'],
                'actor' => $definition['actor'] ?? null,
                'summary' => $definition['summary'] ?? null,
                'workflow' => $definition['workflow'] ?? null,
                'outcome' => $definition['outcome'] ?? null,
                'image' => isset($definition['image']) ? $images->sync($definition['image']) : null,
                'image_alt' => $definition['image_alt'] ?? null,
                'sort_order' => (int) ($definition['sort_order'] ?? 0),
                'status' => 'published',
                'is_published' => true,
                'published_at' => $useCase->published_at ?? now(),
            ]);

            $useCase->save();
        }
    }

    /** @return array{use_cases: list<array<string, mixed>>} */
    private function payload(): array
    {
        try {
            $data = json_decode(
                File::get(base_path(self::DATA_FILE)),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new \RuntimeException('The approved Vetora use-case seed data is invalid JSON.', previous: $exception);
        }

        if (! is_array($data['use_cases'] ?? null)) {
            throw new \RuntimeException('The approved Vetora use-case seed data has no use_cases payload.');
        }

        return $data;
    }
}
