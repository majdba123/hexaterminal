<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\Service;
use App\Models\System;
use App\Models\SystemUseCase;
use App\Services\CaseStudySeedImageSynchronizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use JsonException;

class VetoraCaseStudySeeder extends Seeder
{
    private const DATA_FILE = 'database/seeders/data/vetora_case_study_seed_data.json';

    public function run(): void
    {
        $payload = $this->payload();
        $caseStudyData = $payload['case_study'];
        $relations = $caseStudyData['relations'];

        $system = System::query()->where('slug', $relations['system_slug'])->first();
        if (! $system) {
            throw new \RuntimeException("The approved Vetora system [{$relations['system_slug']}] must exist before seeding the case study.");
        }

        $service = Service::query()->where('slug', $relations['service_slug'])->first();
        if (! $service) {
            throw new \RuntimeException("The approved service [{$relations['service_slug']}] must exist before seeding the Vetora case study.");
        }

        SystemUseCase::query()->where('system_id', $system->id)->delete();

        $images = app(CaseStudySeedImageSynchronizer::class);
        $caseStudy = CaseStudy::firstOrNew(['slug' => $caseStudyData['slug']]);
        $published = $caseStudyData['publication_intent'] === 'published';

        $caseStudy->fill([
            'title' => $caseStudyData['title'],
            'summary' => $caseStudyData['summary'],
            'context' => $caseStudyData['context'],
            'problem' => $caseStudyData['problem'],
            'constraints' => $caseStudyData['constraints'],
            'solution' => $caseStudyData['solution'],
            'architecture' => $caseStudyData['architecture'],
            'outcomes' => $caseStudyData['outcomes'],
            'evidence' => $caseStudyData['evidence'],
            'features' => $caseStudyData['features'],
            'client_name' => $caseStudyData['client_name'],
            'project_url' => $caseStudyData['project_url'] ?? null,
            'video_url' => $caseStudyData['video_url'] ?? null,
            'cover_image' => $images->sync($caseStudyData['cover_image']),
            'cover_image_alt' => $caseStudyData['cover_image_alt'],
            'gallery' => $images->syncMany($caseStudyData['gallery'] ?? []),
            'service_offering_id' => $service->id,
            'system_id' => $system->id,
            'project_classification' => $caseStudyData['project_classification'],
            'is_featured' => (bool) $caseStudyData['featured'],
            'is_published' => $published,
            'sort_order' => (int) $caseStudyData['sort_order'],
        ]);

        if ($published && ! $caseStudy->published_at) {
            $caseStudy->published_at = now();
        }

        if (! $published) {
            $caseStudy->published_at = null;
        }

        $caseStudy->save();
        $caseStudy->industries()->sync([]);

        $this->upsertSeo($caseStudy, $caseStudyData['title'], $caseStudyData['summary']);
    }

    /**
     * @return array{case_study: array<string, mixed>}
     */
    private function payload(): array
    {
        try {
            $payload = json_decode(
                File::get(base_path(self::DATA_FILE)),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new \RuntimeException('The approved Vetora case study seed data is invalid JSON.', previous: $exception);
        }

        if (! is_array($payload['case_study'] ?? null)) {
            throw new \RuntimeException('The approved Vetora case study seed data has no case study payload.');
        }

        return $payload;
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
