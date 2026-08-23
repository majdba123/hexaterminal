<?php

namespace Database\Seeders;

use App\Models\TrustPage;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use JsonException;

class TrustPagesSeeder extends Seeder
{
    private const DATA_FILE = 'database/seeders/data/trust_pages_seed_data.json';

    public function run(): void
    {
        foreach ($this->pages() as $pageData) {
            $page = TrustPage::firstOrNew(['slug' => $pageData['slug']]);
            $published = (bool) ($pageData['publication']['is_published'] ?? false);

            $page->fill([
                'page_type' => $pageData['page_type'],
                'title' => $pageData['title'],
                'summary' => $pageData['summary'] ?? null,
                'sections' => $pageData['sections'],
                'faqs' => $pageData['faqs'] ?? null,
                'cta' => $pageData['cta'] ?? null,
                'founder_approved' => (bool) ($pageData['approvals']['founder_approved'] ?? false),
                'legal_approved' => (bool) ($pageData['approvals']['legal_approved'] ?? false),
                'security_approved' => (bool) ($pageData['approvals']['security_approved'] ?? false),
                'is_published' => $published,
                'status' => $pageData['publication']['status'] ?? ($published ? 'published' : 'draft'),
                'noindex' => (bool) ($pageData['publication']['noindex'] ?? true),
                'show_in_nav' => (bool) ($pageData['publication']['show_in_nav'] ?? false),
                'show_in_footer' => (bool) ($pageData['publication']['show_in_footer'] ?? false),
                'sort_order' => (int) $pageData['sort_order'],
                'reviewed_at' => null,
                'next_review_at' => null,
            ]);

            if ($published && ! $page->published_at) {
                $page->published_at = now();
            }

            if (! $published) {
                $page->published_at = null;
            }

            $page->save();

            $this->upsertSeo($page, $pageData['seo']);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function pages(): array
    {
        try {
            $payload = json_decode(
                File::get(base_path(self::DATA_FILE)),
                true,
                flags: JSON_THROW_ON_ERROR,
            );
        } catch (JsonException $exception) {
            throw new \RuntimeException('The approved trust pages seed data is invalid JSON.', previous: $exception);
        }

        if (! is_array($payload['trust_pages'] ?? null)) {
            throw new \RuntimeException('The approved trust pages seed data has no trust_pages collection.');
        }

        return $payload['trust_pages'];
    }

    /**
     * @param  array<string, mixed>  $seo
     */
    private function upsertSeo(Model $model, array $seo): void
    {
        $model->seo()->updateOrCreate([], [
            'title' => $seo['title'] ?? null,
            'description' => $seo['description'] ?? null,
            'canonical_url' => $seo['canonical_url'] ?? null,
            'og_image' => $seo['og_image'] ?? null,
            'noindex' => (bool) ($seo['noindex'] ?? true),
            'nofollow' => (bool) ($seo['nofollow'] ?? false),
        ]);
    }
}
