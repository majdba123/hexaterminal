<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\System;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use JsonException;

class IndustriesSeeder extends Seeder
{
    private const DATA_FILE = 'database/seeders/data/industries_seed_data.json';

    public function run(): void
    {
        foreach ($this->industryDefinitions() as $definition) {
            $published = $definition['publication_intent'] === 'published';
            $industry = Industry::firstOrNew(['slug' => $definition['slug']]);

            $industry->fill([
                'name' => $definition['name'],
                'summary' => $definition['summary'],
                'description' => $definition['description'],
                'icon' => null,
                'cover_image' => null,
                'cover_image_alt' => $definition['cover_image_alt'],
                'is_published' => $published,
                'status' => $published ? 'published' : 'draft',
                'sort_order' => (int) $definition['sort_order'],
            ]);

            if ($published && ! $industry->published_at) {
                $industry->published_at = now();
            }

            if (! $published) {
                $industry->published_at = null;
            }

            $industry->save();

            $industry->systems()->sync($this->systemIds($definition['relationships']['systems'] ?? []));
            $industry->caseStudies()->sync($this->caseStudyIds($definition['relationships']['case_studies'] ?? []));

            $this->upsertSeo($industry, $definition['name'], $definition['summary']);
        }
    }

    /** @return list<array<string, mixed>> */
    private function industryDefinitions(): array
    {
        try {
            $payload = json_decode(
                File::get(base_path(self::DATA_FILE)),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new \RuntimeException('The approved industry seed data is invalid JSON.', previous: $exception);
        }

        if (! is_array($payload['industries'] ?? null)) {
            throw new \RuntimeException('The approved industry seed data has no industries payload.');
        }

        return $payload['industries'];
    }

    /**
     * @param  list<string>  $slugs
     * @return list<int>
     */
    private function systemIds(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        $systems = System::query()->whereIn('slug', $slugs)->pluck('id', 'slug');

        foreach ($slugs as $slug) {
            if (! $systems->has($slug)) {
                throw new \RuntimeException("Industry relationship references missing system [{$slug}].");
            }
        }

        return array_values($systems->only($slugs)->all());
    }

    /**
     * @param  list<string>  $slugs
     * @return list<int>
     */
    private function caseStudyIds(array $slugs): array
    {
        if ($slugs === []) {
            return [];
        }

        $caseStudies = CaseStudy::query()->whereIn('slug', $slugs)->pluck('id', 'slug');

        foreach ($slugs as $slug) {
            if (! $caseStudies->has($slug)) {
                throw new \RuntimeException("Industry relationship references missing case study [{$slug}].");
            }
        }

        return array_values($caseStudies->only($slugs)->all());
    }

    /**
     * @param  array<string, string>  $title
     * @param  array<string, string>  $description
     */
    private function upsertSeo(Model $model, array $title, array $description): void
    {
        $model->seo()->updateOrCreate([], [
            'title' => $title,
            'description' => $description,
            'noindex' => false,
            'nofollow' => false,
        ]);
    }
}
