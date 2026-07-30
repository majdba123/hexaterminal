<?php

namespace App\Services;

use App\Models\AiGeneration;
use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\PublicClaim;
use App\Models\Service;
use App\Models\System;
use App\Models\TrustPage;
use Illuminate\Support\Facades\DB;

/**
 * Content-readiness inspection used by `php artisan hexa:content-report` and
 * the CMS dashboard: what founders must still fill in before deployment.
 * Read-only; every finding carries type/slug/problem so it is directly
 * actionable in Filament.
 *
 * @phpstan-type ContentModel Service|System|CaseStudy|Industry|Article|TrustPage
 */
class ContentCompletenessReport
{
    /** Content types checked, with the fields both locales must have. */
    private const TYPES = [
        'service' => [Service::class, ['name', 'summary']],
        'system' => [System::class, ['name', 'short_description']],
        'case_study' => [CaseStudy::class, ['title', 'summary']],
        'industry' => [Industry::class, ['name', 'summary']],
        'article' => [Article::class, ['title', 'excerpt', 'body']],
        'trust_page' => [TrustPage::class, ['title', 'summary', 'sections']],
    ];

    /**
     * @return array{findings: list<array{type: string, slug: string, status: string, problem: string}>, totals: array<string, int>}
     */
    public function build(): array
    {
        $findings = [];

        foreach (self::TYPES as $typeName => [$class, $requiredFields]) {
            foreach ($class::query()->with('seo')->get() as $record) {
                foreach ($this->inspect($record, $requiredFields) as $problem) {
                    $findings[] = [
                        'type' => $typeName,
                        'slug' => (string) $record->slug,
                        'status' => (string) ($record->status ?? ($record->is_published ? 'published' : 'draft')),
                        'problem' => $problem,
                    ];
                }
            }

            // Duplicate slugs (defensive -- the DB constraint should prevent it).
            $table = (new $class)->getTable();
            $dupes = DB::table($table)->select('slug')->groupBy('slug')->havingRaw('COUNT(*) > 1')->pluck('slug');
            foreach ($dupes as $slug) {
                $findings[] = ['type' => $typeName, 'slug' => (string) $slug, 'status' => '-', 'problem' => 'duplicate slug'];
            }
        }

        foreach ($this->inspectClaims() as $finding) {
            $findings[] = $finding;
        }

        foreach (app(InternalLinkSuggester::class)->brokenRelatedLinks() as $link) {
            $findings[] = [
                'type' => 'link',
                'slug' => $link['source'],
                'status' => 'published',
                'problem' => $link['problem'].' ('.$link['target'].')',
            ];
        }

        $totals = [
            'findings' => count($findings),
            'unpublished_content' => collect($findings)->where('problem', 'not published')->count(),
            'missing_arabic' => collect($findings)->filter(fn ($f) => str_contains($f['problem'], 'missing AR'))->count(),
            'missing_english' => collect($findings)->filter(fn ($f) => str_contains($f['problem'], 'missing EN'))->count(),
            'missing_seo' => collect($findings)->filter(fn ($f) => str_contains($f['problem'], 'SEO'))->count(),
            'failed_ai_generations' => AiGeneration::where('status', AiGeneration::STATUS_FAILED)->count(),
        ];

        return ['findings' => $findings, 'totals' => $totals];
    }

    /**
     * @param  ContentModel  $record
     * @return list<string>
     */
    private function inspect(Service|System|CaseStudy|Industry|Article|TrustPage $record, array $requiredFields): array
    {
        $problems = [];

        if ($record instanceof TrustPage) {
            foreach ($this->missingApprovals($record) as $problem) {
                $problems[] = $problem;
            }
        }

        foreach ($requiredFields as $field) {
            foreach (['en' => 'EN', 'ar' => 'AR'] as $locale => $label) {
                $value = $record->getTranslation($field, $locale, false);
                if (! is_string($value) || trim($value) === '') {
                    $problems[] = "missing {$label} {$field}";
                }
            }
        }

        if (blank($record->slug)) {
            $problems[] = 'missing slug';
        }

        if (! $record->is_published) {
            $problems[] = 'not published';
        }

        $seo = $record->seo;
        if ($seo === null) {
            $problems[] = 'missing SEO metadata row';
        } else {
            foreach (['en' => 'EN', 'ar' => 'AR'] as $locale => $label) {
                if (blank($seo->getTranslation('title', $locale, false))) {
                    $problems[] = "missing {$label} SEO title";
                }
                if (blank($seo->getTranslation('description', $locale, false))) {
                    $problems[] = "missing {$label} SEO description";
                }
            }
            if ($seo->noindex && $record->is_published) {
                $problems[] = 'published but noindex';
            }
        }

        if (array_key_exists('cover_image', $record->getAttributes()) && blank($record->cover_image)) {
            $problems[] = 'missing cover image';
        }

        // Metric-evidence hygiene: case studies with outcomes but no evidence labels.
        if ($record instanceof CaseStudy
            && filled($record->getTranslation('outcomes', 'en', false))
            && blank($record->getTranslation('evidence', 'en', false))) {
            $problems[] = 'outcomes without evidence labels (unverified metrics must not ship)';
        }

        return $problems;
    }

    /** @return list<string> */
    private function missingApprovals(TrustPage $page): array
    {
        $problems = [];

        if (in_array($page->page_type, TrustPage::TYPES_REQUIRING_FOUNDER_APPROVAL, true) && ! $page->founder_approved) {
            $problems[] = 'missing founder approval';
        }
        if (in_array($page->page_type, TrustPage::TYPES_REQUIRING_LEGAL_APPROVAL, true) && ! $page->legal_approved) {
            $problems[] = 'missing legal approval';
        }
        if (in_array($page->page_type, TrustPage::TYPES_REQUIRING_SECURITY_APPROVAL, true) && ! $page->security_approved) {
            $problems[] = 'missing security approval';
        }
        if ($page->is_published && ! $page->isReadyForPublication()) {
            $problems[] = 'published flag set but not actually ready for publication (fail-closed contract will hide it anyway)';
        }
        if ($page->next_review_at !== null && $page->next_review_at->isPast()) {
            $problems[] = 'review overdue (next_review_at has passed)';
        }

        return $problems;
    }

    /**
     * Governed public claims that are stale or internally contradictory:
     * approved-for-publication but not actually verified, or approved but
     * already expired. Never flags confidential claims by content (only by
     * category/slug of the owner), so no sensitive text leaks into reports.
     *
     * @return list<array{type: string, slug: string, status: string, problem: string}>
     */
    private function inspectClaims(): array
    {
        $findings = [];

        foreach (PublicClaim::with('claimable')->get() as $claim) {
            $owner = $claim->claimable;
            $slug = $owner->slug ?? "claim#{$claim->id}";

            if ($claim->approved_for_publication && $claim->verification_status !== 'verified') {
                $findings[] = [
                    'type' => 'public_claim',
                    'slug' => $slug,
                    'status' => $claim->verification_status,
                    'problem' => "approved for publication but not verified ({$claim->category})",
                ];
            }

            if ($claim->approved_for_publication && $claim->expires_at !== null && $claim->expires_at->isPast()) {
                $findings[] = [
                    'type' => 'public_claim',
                    'slug' => $slug,
                    'status' => $claim->verification_status,
                    'problem' => "approved claim has expired ({$claim->category})",
                ];
            }

            if ($claim->next_review_at !== null && $claim->next_review_at->isPast()) {
                $findings[] = [
                    'type' => 'public_claim',
                    'slug' => $slug,
                    'status' => $claim->verification_status,
                    'problem' => "review overdue ({$claim->category})",
                ];
            }
        }

        return $findings;
    }
}
