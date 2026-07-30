<?php

namespace App\Services;

use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\PublicClaim;
use App\Models\Service;
use App\Models\System;
use App\Models\TrustPage;
use Illuminate\Database\Eloquent\Model;

/**
 * Backend-observable SEO health for `php artisan hexa:seo-audit`.
 *
 * Deliberately distinct from ContentCompletenessReport: that service tells
 * founders what content is missing; this one tells them what will actually
 * hurt search visibility once content IS published -- title/description
 * presence and length, canonical validity, duplicate titles/descriptions,
 * and the specific noindex/sitemap contradiction that
 * frontend/app/sitemap.ts cannot see from the backend (it includes every
 * published record regardless of that record's own `seo.noindex`).
 *
 * Checks that require live frontend/crawl state (redirect chains, broken
 * internal links, orphan pages) are out of scope here -- see
 * `hexa:link-audit` (Wave 9).
 *
 * @phpstan-type AuditModel Service|System|CaseStudy|Industry|Article|TrustPage
 */
class SeoAuditReport
{
    private const MIN_TITLE = 10;

    private const MAX_TITLE = 60;

    private const MIN_DESCRIPTION = 50;

    private const MAX_DESCRIPTION = 160;

    private const TYPES = [
        'service' => Service::class,
        'system' => System::class,
        'case_study' => CaseStudy::class,
        'industry' => Industry::class,
        'article' => Article::class,
        'trust_page' => TrustPage::class,
    ];

    /**
     * @return array{
     *     findings: list<array{type: string, slug: string, severity: string, check: string, detail: string}>,
     *     category_scores: array<string, int>,
     *     overall_score: int,
     *     blocker_count: int,
     * }
     */
    public function build(): array
    {
        $findings = [];
        $categoryTotals = [];
        $categoryBlockers = [];

        foreach (self::TYPES as $typeName => $class) {
            $categoryTotals[$typeName] = 0;
            $categoryBlockers[$typeName] = 0;

            $records = $class::query()->with('seo')->get();

            $titlesByLocale = ['en' => [], 'ar' => []];
            $descriptionsByLocale = ['en' => [], 'ar' => []];

            foreach ($records as $record) {
                $categoryTotals[$typeName]++;

                foreach ($this->inspectOne($typeName, $record) as $finding) {
                    $findings[] = $finding;
                    if ($finding['severity'] === 'blocker') {
                        $categoryBlockers[$typeName]++;
                    }
                }

                $seo = $record->seo;
                foreach (['en', 'ar'] as $locale) {
                    $title = $seo?->getTranslation('title', $locale, false);
                    if (is_string($title) && trim($title) !== '') {
                        $titlesByLocale[$locale][] = ['slug' => $record->slug, 'value' => trim($title)];
                    }
                    $description = $seo?->getTranslation('description', $locale, false);
                    if (is_string($description) && trim($description) !== '') {
                        $descriptionsByLocale[$locale][] = ['slug' => $record->slug, 'value' => trim($description)];
                    }
                }
            }

            foreach ($this->findDuplicates($typeName, 'title', $titlesByLocale) as $finding) {
                $findings[] = $finding;
                $categoryBlockers[$typeName]++;
            }
            foreach ($this->findDuplicates($typeName, 'description', $descriptionsByLocale) as $finding) {
                $findings[] = $finding;
                $categoryBlockers[$typeName]++;
            }
        }

        foreach ($this->inspectClaims() as $finding) {
            $findings[] = $finding;
        }

        $categoryScores = [];
        foreach ($categoryTotals as $typeName => $total) {
            $categoryScores[$typeName] = $total === 0
                ? 100
                : max(0, 100 - (int) round(($categoryBlockers[$typeName] / max($total, 1)) * 100));
        }

        // self::TYPES is a fixed non-empty const, so $categoryScores always has entries.
        $overallScore = (int) round(array_sum($categoryScores) / count($categoryScores));
        $blockerCount = count(array_filter($findings, fn ($f) => $f['severity'] === 'blocker'));

        return [
            'findings' => $findings,
            'category_scores' => $categoryScores,
            'overall_score' => $overallScore,
            'blocker_count' => $blockerCount,
        ];
    }

    /**
     * @param  AuditModel  $record
     * @return list<array{type: string, slug: string, severity: string, check: string, detail: string}>
     */
    private function inspectOne(string $typeName, Model $record): array
    {
        $findings = [];
        $slug = (string) $record->slug;
        $isPublished = (bool) $record->is_published;
        $seo = $record->seo;

        if ($isPublished) {
            $title = $seo?->getTranslation('title', 'en', false);
            if (blank($title)) {
                $findings[] = $this->finding($typeName, $slug, 'blocker', 'missing_title', 'published without an EN SEO title');
            } elseif (mb_strlen($title) > self::MAX_TITLE) {
                $findings[] = $this->finding($typeName, $slug, 'warning', 'title_too_long', 'EN SEO title exceeds '.self::MAX_TITLE.' characters');
            } elseif (mb_strlen($title) < self::MIN_TITLE) {
                $findings[] = $this->finding($typeName, $slug, 'warning', 'title_too_short', 'EN SEO title is under '.self::MIN_TITLE.' characters');
            }

            $description = $seo?->getTranslation('description', 'en', false);
            if (blank($description)) {
                $findings[] = $this->finding($typeName, $slug, 'blocker', 'missing_description', 'published without an EN SEO description');
            } elseif (mb_strlen($description) > self::MAX_DESCRIPTION) {
                $findings[] = $this->finding($typeName, $slug, 'warning', 'description_too_long', 'EN SEO description exceeds '.self::MAX_DESCRIPTION.' characters');
            } elseif (mb_strlen($description) < self::MIN_DESCRIPTION) {
                $findings[] = $this->finding($typeName, $slug, 'warning', 'description_too_short', 'EN SEO description is under '.self::MIN_DESCRIPTION.' characters');
            }

            if ($seo === null) {
                $findings[] = $this->finding($typeName, $slug, 'warning', 'missing_og_image', 'no SEO row, so no OG image either');
            } elseif (blank($seo->og_image)) {
                $findings[] = $this->finding($typeName, $slug, 'warning', 'missing_og_image', 'no Open Graph image set');
            }

            // This is the exact gap frontend/app/sitemap.ts has: every
            // published record of these types gets a static sitemap entry
            // regardless of its own seo.noindex flag.
            if ($seo?->noindex && $typeName !== 'trust_page') {
                $findings[] = $this->finding($typeName, $slug, 'blocker', 'noindex_in_sitemap', 'published, seo.noindex=true, but the sitemap includes every published record of this type unconditionally');
            }

            if ($seo !== null && blank($seo->canonical_url) === false && ! str_starts_with((string) $seo->canonical_url, 'http')) {
                $findings[] = $this->finding($typeName, $slug, 'blocker', 'invalid_canonical', 'canonical_url is set but does not start with http(s)://');
            }

            if ($this->isEmptyIndexable($typeName, $record)) {
                $findings[] = $this->finding($typeName, $slug, 'blocker', 'empty_indexable_page', 'published with no real EN body content');
            }
        }

        return $findings;
    }

    /** @param  AuditModel  $record */
    private function isEmptyIndexable(string $typeName, Service|System|CaseStudy|Industry|Article|TrustPage $record): bool
    {
        $bodyField = match ($typeName) {
            'article' => 'body',
            'case_study' => 'summary',
            'trust_page' => 'sections',
            default => 'description',
        };

        if (! array_key_exists($bodyField, $record->getAttributes()) && ! in_array($bodyField, $record->translatable ?? [], true)) {
            return false;
        }

        $value = $record->getTranslation($bodyField, 'en', false);

        return blank($value);
    }

    /**
     * @param  array<string, list<array{slug: string, value: string}>>  $valuesByLocale
     * @return list<array{type: string, slug: string, severity: string, check: string, detail: string}>
     */
    private function findDuplicates(string $typeName, string $field, array $valuesByLocale): array
    {
        $findings = [];

        foreach ($valuesByLocale as $locale => $entries) {
            $grouped = [];
            foreach ($entries as $entry) {
                $grouped[mb_strtolower($entry['value'])][] = $entry['slug'];
            }

            foreach ($grouped as $value => $slugs) {
                if (count($slugs) > 1) {
                    foreach ($slugs as $slug) {
                        $findings[] = $this->finding(
                            $typeName,
                            $slug,
                            'blocker',
                            "duplicate_{$field}",
                            "{$locale} SEO {$field} is shared with: ".implode(', ', array_diff($slugs, [$slug])),
                        );
                    }
                }
            }
        }

        return $findings;
    }

    /** @return list<array{type: string, slug: string, severity: string, check: string, detail: string}> */
    private function inspectClaims(): array
    {
        $findings = [];

        foreach (PublicClaim::with('claimable')->get() as $claim) {
            $owner = $claim->claimable;
            $slug = $owner->slug ?? "claim#{$claim->id}";

            if ($claim->approved_for_publication && $claim->expires_at !== null && $claim->expires_at->isPast()) {
                $findings[] = $this->finding('public_claim', $slug, 'blocker', 'expired_public_claim', "approved claim has expired ({$claim->category})");
            }
        }

        return $findings;
    }

    /** @return array{type: string, slug: string, severity: string, check: string, detail: string} */
    private function finding(string $type, string $slug, string $severity, string $check, string $detail): array
    {
        return ['type' => $type, 'slug' => $slug, 'severity' => $severity, 'check' => $check, 'detail' => $detail];
    }
}
