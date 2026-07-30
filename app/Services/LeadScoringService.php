<?php

namespace App\Services;

use App\Models\ContactLead;
use App\Models\CostEstimate;

/**
 * Deterministic, explainable lead-priority scoring. Every factor is an
 * explicit business field with a documented weight; the result is a 0-100
 * score plus a per-factor breakdown stored on the lead so any human can see
 * exactly why a lead scored what it did.
 *
 * Guarantees (see docs/content/publishing-workflow.md#lead-scoring):
 *  - the score NEVER rejects or hides a lead -- it only orders the queue
 *  - no protected personal traits are used (no name/country/locale weighting)
 *  - no opaque model involvement; admins can override priority freely
 */
class LeadScoringService
{
    /** score >= HIGH -> 'high', >= NORMAL -> 'normal', else 'low' */
    private const PRIORITY_HIGH = 60;

    private const PRIORITY_NORMAL = 30;

    /**
     * @return array{score: int, breakdown: list<array{factor: string, points: int}>, priority: string}
     */
    public function score(ContactLead $lead, ?CostEstimate $estimate = null): array
    {
        $breakdown = [];

        $intentPoints = match ($lead->intent) {
            'start_project', 'request_quote' => 20,
            'request_demo', 'book_call' => 15,
            default => 5,
        };
        $breakdown[] = ['factor' => 'intent:'.$lead->intent, 'points' => $intentPoints];

        if (filled($lead->budget_range)) {
            $breakdown[] = ['factor' => 'budget_provided', 'points' => 10];
        }
        if (filled($lead->timeline)) {
            $breakdown[] = ['factor' => 'timeline_provided', 'points' => 5];
        }
        if (filled($lead->company)) {
            $breakdown[] = ['factor' => 'company_provided', 'points' => 10];
        }
        if (filled($lead->company_size)) {
            $breakdown[] = ['factor' => 'company_size_provided', 'points' => 5];
        }
        if ($lead->requested_service_id || $lead->requested_system_id) {
            $breakdown[] = ['factor' => 'specific_interest', 'points' => 10];
        }
        if (filled($lead->phone) || filled($lead->whatsapp)) {
            $breakdown[] = ['factor' => 'direct_contact_channel', 'points' => 5];
        }
        if ($lead->consent) {
            $breakdown[] = ['factor' => 'consent_given', 'points' => 5];
        }

        $summaryLength = mb_strlen(trim((string) $lead->summary));
        if ($summaryLength >= 100) {
            $breakdown[] = ['factor' => 'detailed_summary', 'points' => 10];
        } elseif ($summaryLength >= 30) {
            $breakdown[] = ['factor' => 'basic_summary', 'points' => 5];
        }

        // Spam-shaped content is a negative signal (never an auto-reject).
        $linkCount = preg_match_all('#https?://#i', (string) $lead->summary);
        if ($linkCount > 2) {
            $breakdown[] = ['factor' => 'link_heavy_summary', 'points' => -20];
        }

        // Estimator signals: an attached estimate means the buyer engaged
        // with the scoping flow -- a strong, explainable qualification signal
        // built only from project shape, never from any protected trait.
        if ($estimate !== null) {
            $breakdown[] = ['factor' => 'completed_estimate', 'points' => 10];

            $complexityPoints = match ($estimate->complexity) {
                'enterprise', 'complex' => 10,
                'advanced' => 6,
                default => 3,
            };
            $breakdown[] = ['factor' => 'estimate_complexity:'.$estimate->complexity, 'points' => $complexityPoints];

            // A larger scoped band aligns with Hexa Terminal's engagement fit.
            if ($estimate->base_amount_min >= 25000) {
                $breakdown[] = ['factor' => 'estimate_band_fit', 'points' => 8];
            } elseif ($estimate->base_amount_min >= 12000) {
                $breakdown[] = ['factor' => 'estimate_band_fit', 'points' => 4];
            }
        }

        $score = max(0, min(100, array_sum(array_column($breakdown, 'points'))));

        return [
            'score' => $score,
            'breakdown' => $breakdown,
            'priority' => $score >= self::PRIORITY_HIGH ? 'high'
                : ($score >= self::PRIORITY_NORMAL ? 'normal' : 'low'),
        ];
    }
}
