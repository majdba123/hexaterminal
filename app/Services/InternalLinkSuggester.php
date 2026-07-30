<?php

namespace App\Services;

use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use Illuminate\Support\Collection;

/**
 * Deterministic internal-link suggestions among PUBLISHED content only.
 * Ranks candidates by (a) real model relationships and (b) title-keyword
 * overlap. Suggestions are advisory: nothing is ever injected into content --
 * editors apply links manually (that IS the approval step). Self-links are
 * excluded, output is capped, and every suggested path is a real public URL
 * for a published record, so a suggestion can never leak drafts.
 *
 * @phpstan-type ContentModel Service|System|CaseStudy|Industry|Article
 */
class InternalLinkSuggester
{
    private const MAX_SUGGESTIONS = 8;

    /** Frontend route segment per content class. */
    private const SEGMENTS = [
        Service::class => 'services',
        System::class => 'systems',
        CaseStudy::class => 'case-studies',
        Industry::class => 'industries',
        Article::class => 'insights',
    ];

    /**
     * @param  ContentModel  $source
     * @return list<array{path: string, label: string, reason: string, score: int}>
     */
    public function suggestFor(Service|System|CaseStudy|Industry|Article $source, string $locale = 'en'): array
    {
        $suggestions = collect();

        // 1. Relationship-derived links (strongest signal).
        foreach ($this->relatedRecords($source) as [$record, $reason]) {
            $this->push($suggestions, $source, $record, $locale, $reason, 100);
        }

        // 2. Keyword overlap against every published record of every type.
        $sourceWords = $this->keywords($this->labelOf($source, $locale));
        if ($sourceWords !== []) {
            foreach (self::SEGMENTS as $class => $segment) {
                foreach ($class::published()->get() as $record) {
                    $overlap = count(array_intersect($sourceWords, $this->keywords($this->labelOf($record, $locale))));
                    if ($overlap > 0) {
                        $this->push($suggestions, $source, $record, $locale, 'shared topic keywords', $overlap * 10);
                    }
                }
            }
        }

        return $suggestions
            ->unique('path')       // no repeated identical anchors
            ->sortByDesc('score')
            ->take(self::MAX_SUGGESTIONS)
            ->values()
            ->all();
    }

    /**
     * Related-content links that are BROKEN: relations pointing at content
     * that is no longer published (used by the completeness report).
     *
     * @return list<array{source: string, target: string, problem: string}>
     */
    public function brokenRelatedLinks(): array
    {
        $broken = [];

        foreach (CaseStudy::published()->with(['serviceOffering', 'system'])->get() as $caseStudy) {
            if ($caseStudy->serviceOffering && ! $caseStudy->serviceOffering->is_published) {
                $broken[] = [
                    'source' => 'case-studies/'.$caseStudy->slug,
                    'target' => 'service:'.$caseStudy->serviceOffering->slug,
                    'problem' => 'related service is unpublished',
                ];
            }
            if ($caseStudy->system && ! $caseStudy->system->is_published) {
                $broken[] = [
                    'source' => 'case-studies/'.$caseStudy->slug,
                    'target' => 'system:'.$caseStudy->system->slug,
                    'problem' => 'related system is unpublished',
                ];
            }
        }

        foreach (System::published()->with('industries')->get() as $system) {
            foreach ($system->industries as $industry) {
                if (! $industry->is_published) {
                    $broken[] = [
                        'source' => 'systems/'.$system->slug,
                        'target' => 'industry:'.$industry->slug,
                        'problem' => 'related industry is unpublished',
                    ];
                }
            }
        }

        return $broken;
    }

    /**
     * @param  ContentModel  $source
     * @return list<array{0: ContentModel, 1: string}>
     */
    private function relatedRecords(Service|System|CaseStudy|Industry|Article $source): array
    {
        $related = [];

        if ($source instanceof CaseStudy) {
            if ($source->serviceOffering?->is_published) {
                $related[] = [$source->serviceOffering, 'the service this case study delivers'];
            }
            if ($source->system?->is_published) {
                $related[] = [$source->system, 'the system this case study covers'];
            }
        }

        if ($source instanceof System) {
            foreach ($source->industries()->published()->get() as $industry) {
                $related[] = [$industry, 'industry this system serves'];
            }
            foreach ($source->caseStudies()->published()->get() as $caseStudy) {
                $related[] = [$caseStudy, 'case study built on this system'];
            }
        }

        if ($source instanceof Service) {
            foreach ($source->caseStudies()->published()->get() as $caseStudy) {
                $related[] = [$caseStudy, 'case study delivered under this service'];
            }
        }

        if ($source instanceof Industry) {
            foreach ($source->systems()->published()->get() as $system) {
                $related[] = [$system, 'system serving this industry'];
            }
        }

        return $related;
    }

    /**
     * @param  ContentModel  $source
     * @param  ContentModel  $record
     */
    private function push(Collection $suggestions, Service|System|CaseStudy|Industry|Article $source, Service|System|CaseStudy|Industry|Article $record, string $locale, string $reason, int $score): void
    {
        // Never self-link.
        if ($record->getMorphClass() === $source->getMorphClass() && $record->getKey() === $source->getKey()) {
            return;
        }

        $segment = self::SEGMENTS[get_class($record)] ?? null;
        if ($segment === null || ! $record->is_published) {
            return;
        }

        $suggestions->push([
            'path' => "/{$locale}/{$segment}/{$record->slug}",
            'label' => $this->labelOf($record, $locale),
            'reason' => $reason,
            'score' => $score,
        ]);
    }

    /** @param  ContentModel  $record */
    private function labelOf(Service|System|CaseStudy|Industry|Article $record, string $locale): string
    {
        foreach (['name', 'title'] as $field) {
            if (array_key_exists($field, $record->getAttributes())) {
                return (string) $record->getTranslation($field, $locale, false);
            }
        }

        return (string) $record->slug;
    }

    /** @return list<string> lowercased significant words */
    private function keywords(string $text): array
    {
        $stop = ['the', 'and', 'for', 'with', 'your', 'our', 'من', 'في', 'على', 'إلى', 'عن'];
        $words = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($text), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_filter($words, fn ($w) => mb_strlen($w) >= 4 && ! in_array($w, $stop, true)));
    }
}
