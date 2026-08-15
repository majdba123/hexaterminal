<?php

namespace Database\Seeders;

use App\Models\EngagementModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use JsonException;

class EngagementModelsSeeder extends Seeder
{
    private const DATA_FILE = 'database/seeders/data/pricing_engagement_seed_data.json';

    public function run(): void
    {
        foreach ($this->engagementModels() as $definition) {
            $localized = $definition['localized'];

            $model = EngagementModel::firstOrNew(['slug' => $definition['slug']]);
            $model->fill([
                'title' => $this->localizedField($localized, 'title'),
                'summary' => $this->localizedField($localized, 'summary'),
                'buyer_fit' => $this->localizedList($localized, 'best_for'),
                'typical_scope' => $this->localizedField($localized, 'description'),
                'deliverables' => [],
                'included_items' => [],
                'excluded_items' => [],
                'indicative_duration' => null,
                'cta_label' => null,
                'cta_intent' => 'request_quote',
                'pricing_display_mode' => $definition['display_mode'],
                'billing_model' => $definition['billing_model'],
                'is_featured' => false,
                'is_published' => true,
                'sort_order' => (int) $definition['sort_order'],
            ]);
            $model->save();
        }
    }

    /** @return array<int, array<string, mixed>> */
    private function engagementModels(): array
    {
        try {
            $data = json_decode(
                File::get(base_path(self::DATA_FILE)),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new \RuntimeException('The approved engagement-model seed data is invalid JSON.', previous: $exception);
        }

        if (! is_array($data['engagement_models'] ?? null)) {
            throw new \RuntimeException('The approved engagement-model seed data has no engagement_models collection.');
        }

        return $data['engagement_models'];
    }

    /** @param array<string, array<string, mixed>> $localized */
    private function localizedField(array $localized, string $field): array
    {
        return collect($localized)
            ->mapWithKeys(fn (array $translation, string $locale): array => [$locale => $translation[$field] ?? null])
            ->all();
    }

    /** @param array<string, array<string, mixed>> $localized */
    private function localizedList(array $localized, string $field): array
    {
        return collect($localized)
            ->mapWithKeys(function (array $translation, string $locale) use ($field): array {
                $lines = preg_split('/\r\n|\r|\n/', (string) ($translation[$field] ?? '')) ?: [];

                return [
                    $locale => array_values(array_filter(
                        array_map(static fn (string $line): string => trim($line), $lines),
                        static fn (string $line): bool => $line !== '',
                    )),
                ];
            })
            ->all();
    }
}
