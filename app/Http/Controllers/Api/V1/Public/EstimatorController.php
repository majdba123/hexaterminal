<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\EstimateResource;
use App\Models\CompanySetting;
use App\Models\ContactLead;
use App\Models\CostEstimate;
use App\Models\EstimatorVersion;
use App\Notifications\NewLeadNotification;
use App\Services\Estimator\EstimatorEngine;
use App\Services\LeadScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

/**
 * Public cost estimator. The result is shown WITHOUT an email gate; contact
 * capture is a separate, optional step. The Laravel engine is authoritative;
 * the frontend never computes a price. Result retrieval is by high-entropy
 * UUID only. See docs/sales/estimator-to-lead-flow.md.
 */
class EstimatorController extends Controller
{
    /** Anonymous estimates are retained for this many days, then expire. */
    private const ESTIMATE_TTL_DAYS = 30;

    /** GET /estimator -- the active version's questions + currency options. */
    public function config()
    {
        $version = EstimatorVersion::current();
        if (! $version) {
            return response()->json(['data' => ['available' => false]]);
        }

        $questions = $version->questions()->get()->map(fn ($q) => [
            'key' => $q->key,
            'step' => $q->step,
            'type' => $q->type,
            'prompt' => $q->prompt,
            'help_text' => $q->help_text,
            'is_required' => (bool) $q->is_required,
            'show_if' => $q->show_if,
            'options' => $this->localizeOptions($q->options ?? []),
        ]);

        return response()->json([
            'data' => [
                'available' => true,
                'version' => $version->key,
                'currencies' => array_keys($version->rates()),
                'questions' => $questions->values(),
            ],
        ]);
    }

    /** POST /estimates -- compute + persist an anonymous estimate, return it. */
    public function store(Request $request, EstimatorEngine $engine)
    {
        $version = EstimatorVersion::current();
        abort_if(! $version, 503, 'Estimator is not available.');

        $validated = Validator::make($request->all(), [
            'currency' => 'nullable|string|size:3',
            'locale' => 'nullable|string|in:en,ar',
            'session_id' => 'nullable|string|max:64',
            'answers' => 'required|array|min:1',
        ])->validate();

        $currency = strtoupper($validated['currency'] ?? 'USD');
        $answers = $this->sanitizeAnswers($validated['answers']);
        abort_if($answers === [], 422, 'No valid answers provided.');

        $result = $engine->compute($version, $answers, $currency);

        $estimate = CostEstimate::create([
            'estimator_version_id' => $result->versionId,
            'locale' => $validated['locale'] ?? app()->getLocale(),
            'currency' => $result->currency,
            'session_id' => $validated['session_id'] ?? null,
            'answers' => $result->answers,
            'base_amount_min' => $result->baseAmountMin,
            'base_amount_max' => $result->baseAmountMax,
            'amount_min' => $result->amountMin,
            'amount_max' => $result->amountMax,
            'timeline_weeks_min' => $result->timelineWeeksMin,
            'timeline_weeks_max' => $result->timelineWeeksMax,
            'complexity' => $result->complexity,
            'confidence' => $result->confidence,
            'cost_drivers' => $result->costDrivers,
            'assumptions' => $result->assumptions,
            'recommended_engagement_model_id' => $result->recommendedEngagementModelId,
            'status' => 'anonymous',
            'expires_at' => now()->addDays(self::ESTIMATE_TTL_DAYS),
        ]);

        $estimate->load('recommendedEngagementModel');

        return response()->json(['data' => new EstimateResource($estimate)], 201);
    }

    /** GET /estimates/{uuid} -- revisit a result. Expired => 410. */
    public function show(string $uuid)
    {
        $estimate = CostEstimate::with('recommendedEngagementModel')
            ->where('public_uuid', $uuid)
            ->first();

        abort_if(! $estimate, 404);

        if ($estimate->isExpired()) {
            return response()->json(['status' => 'expired'], 410);
        }

        return response()->json(['data' => new EstimateResource($estimate)]);
    }

    /**
     * POST /estimates/{uuid}/lead -- optional contact capture. Reuses
     * ContactLead (intent = cost_estimate), links the estimate, preserves
     * UTM/source, and applies the deterministic score with estimate signals.
     */
    public function submitLead(Request $request, string $uuid, LeadScoringService $scoring)
    {
        // Honeypot: silent success so bots learn nothing.
        if (filled($request->input('website'))) {
            return response()->json(['status' => 'success'], 201);
        }

        $estimate = CostEstimate::where('public_uuid', $uuid)->first();
        abort_if(! $estimate, 404);
        if ($estimate->isExpired()) {
            return response()->json(['status' => 'expired'], 410);
        }

        $data = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'company' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'summary' => 'nullable|string|max:5000',
            'requested_action' => 'nullable|string|in:email_estimate,book_call,request_proposal,start_project,ask_question',
            'source_page' => 'nullable|string|max:500',
            'landing_page' => 'nullable|string|max:500',
            'first_touch_at' => 'nullable|date',
            'utm' => 'nullable|array:source,medium,campaign,term,content',
            'utm.*' => 'nullable|string|max:255',
            'locale' => 'nullable|string|in:en,ar',
            'consent' => 'nullable|boolean',
        ])->validate();

        // If this estimate already produced a lead, stay idempotent.
        if ($estimate->contact_lead_id !== null) {
            return response()->json(['status' => 'success', 'estimate' => $estimate->public_uuid], 201);
        }

        // Replay/duplicate suppression consistent with LeadController.
        $existing = ContactLead::where('email', $data['email'])
            ->where('intent', 'cost_estimate')
            ->where('created_at', '>=', now()->subMinutes(10))
            ->latest()
            ->first();
        if ($existing) {
            $estimate->forceFill([
                'contact_lead_id' => $existing->id,
                'status' => $this->statusForAction($data['requested_action'] ?? null),
            ])->save();

            return response()->json(['status' => 'success', 'estimate' => $estimate->public_uuid], 201);
        }

        try {
            $lead = new ContactLead([
                'intent' => 'cost_estimate',
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'country' => $data['country'] ?? null,
                'summary' => $data['summary'] ?? null,
                'budget_range' => "{$estimate->currency} {$estimate->amount_min}-{$estimate->amount_max}",
                'source_page' => $data['source_page'] ?? null,
                'landing_page' => $data['landing_page'] ?? null,
                'referrer' => $request->header('Referer'),
                'first_touch_at' => $data['first_touch_at'] ?? null,
                'utm' => $data['utm'] ?? null,
                'locale' => $data['locale'] ?? app()->getLocale(),
                'consent' => (bool) ($data['consent'] ?? false),
            ]);

            $scored = $scoring->score($lead, $estimate);
            $lead->score = $scored['score'];
            $lead->score_breakdown = $scored['breakdown'];
            $lead->priority = $scored['priority'];
            $lead->save();

            $estimate->forceFill([
                'contact_lead_id' => $lead->id,
                'status' => $this->statusForAction($data['requested_action'] ?? null),
            ])->save();

            $this->notifyTeam($lead);

            return response()->json(['status' => 'success', 'estimate' => $estimate->public_uuid], 201);
        } catch (\Throwable $e) {
            Log::error('Estimate lead creation failed: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Failed to submit. Please try again.'], 500);
        }
    }

    /**
     * Options are stored as [{key, label:{en,ar}}]; expose the label in the
     * current locale (falling back to en) for the frontend.
     *
     * @param  list<array{key?:string,label?:array<string,string>}>  $options
     * @return list<array{key:string,label:string}>
     */
    private function localizeOptions(array $options): array
    {
        $locale = app()->getLocale();

        return array_map(fn ($o) => [
            'key' => $o['key'] ?? '',
            'label' => $o['label'][$locale] ?? ($o['label']['en'] ?? ''),
        ], $options);
    }

    private function statusForAction(?string $action): string
    {
        return match ($action) {
            'book_call' => 'discovery_requested',
            'request_proposal' => 'proposal_requested',
            default => 'lead_created',
        };
    }

    /**
     * Keep only string/array-of-string answers -- the estimator has no
     * free-text inputs, so anything else is discarded rather than stored.
     *
     * @param  array<mixed>  $answers
     * @return array<string, string|list<string>>
     */
    private function sanitizeAnswers(array $answers): array
    {
        $clean = [];
        foreach ($answers as $key => $value) {
            if (! is_string($key)) {
                continue;
            }
            if (is_string($value)) {
                $clean[$key] = mb_substr($value, 0, 100);
            } elseif (is_array($value)) {
                $list = array_values(array_filter(array_map(
                    fn ($v) => is_string($v) ? mb_substr($v, 0, 100) : null,
                    $value,
                )));
                if ($list !== []) {
                    $clean[$key] = $list;
                }
            }
        }

        return $clean;
    }

    private function notifyTeam(ContactLead $lead): void
    {
        try {
            $recipients = CompanySetting::current()->leadRecipients();
            if ($recipients === []) {
                $fallback = array_map('trim', explode(',', (string) config('mail.lead_recipients')));
                $recipients = array_values(array_filter($fallback, fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL) !== false));
            }
            if ($recipients === []) {
                return;
            }

            Notification::route('mail', $recipients)->notify(new NewLeadNotification($lead));
        } catch (\Throwable $e) {
            Log::warning('Estimate lead notification failed', ['lead_id' => $lead->id, 'error' => $e->getMessage()]);
        }
    }
}
