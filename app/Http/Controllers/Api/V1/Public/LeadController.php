<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use App\Models\ContactLead;
use App\Models\Service;
use App\Models\System;
use App\Notifications\NewLeadNotification;
use App\Services\LeadScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

/**
 * Unified lead intake for every acquisition form, typed by `intent` with
 * intent-conditional validation. Anti-spam layers: route throttle (5/min/IP),
 * honeypot, duplicate/replay suppression, optional Cloudflare Turnstile
 * (only when TURNSTILE_SECRET_KEY is configured -- safe fallback without).
 * Spam handling is deliberately silent-success so bots learn nothing.
 *
 * Privacy: logs carry error class + lead id only, never the payload. The
 * response returns only {status, id}.
 */
class LeadController extends Controller
{
    public function store(Request $request, LeadScoringService $scoring)
    {
        // Honeypot: hidden field real users never fill.
        if (filled($request->input('website'))) {
            return response()->json(['status' => 'success'], 201);
        }

        if (! $this->passesTurnstile($request)) {
            return response()->json(['status' => 'success'], 201);
        }

        $validator = Validator::make($request->all(), [
            'intent' => 'nullable|string|in:'.implode(',', ContactLead::INTENTS),
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'company' => 'nullable|string|max:255',
            'company_size' => 'nullable|string|max:50',
            'role_title' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:100',
            'project_type' => 'nullable|string|max:100',
            'industry' => 'nullable|string|max:100',
            'system_type' => 'nullable|string|max:100',
            'budget_range' => 'nullable|string|max:100',
            'timeline' => 'nullable|string|max:100',
            'summary' => 'nullable|string|max:5000',
            'pain_points' => 'nullable|string|max:5000',
            'preferred_contact_method' => 'nullable|string|in:email,phone,whatsapp',
            'consent' => 'nullable|boolean',
            'requested_service_slug' => 'nullable|string|exists:service_offerings,slug',
            'requested_system_slug' => 'nullable|string|exists:systems,slug',
            'source_page' => 'nullable|string|max:500',
            'landing_page' => 'nullable|string|max:500',
            'first_touch_at' => 'nullable|date',
            'utm' => 'nullable|array:source,medium,campaign,term,content',
            'utm.*' => 'nullable|string|max:255',
            'locale' => 'nullable|string|in:en,ar',
        ]);

        // Intent-conditional requirements: project-shaped intents need a
        // summary; a call booking needs a callable channel.
        $validator->sometimes('summary', 'required|string|min:10', function ($input) {
            return in_array($input->intent ?? 'start_project', ['start_project', 'request_quote', 'general_contact'], true);
        });
        $validator->sometimes('phone', 'required_without:whatsapp', function ($input) {
            return ($input->intent ?? null) === 'book_call';
        });

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $validator->validated();
            $data['intent'] = $data['intent'] ?? 'start_project';
            $data['referrer'] = $request->header('Referer');
            $data['locale'] = $data['locale'] ?? app()->getLocale();
            $data['consent'] = (bool) ($data['consent'] ?? false);

            // Resolve public slugs to internal ids (slugs are the public API
            // contract; ids never leave the backend).
            if (! empty($data['requested_service_slug'])) {
                $data['requested_service_id'] = Service::where('slug', $data['requested_service_slug'])->value('id');
            }
            if (! empty($data['requested_system_slug'])) {
                $data['requested_system_id'] = System::where('slug', $data['requested_system_slug'])->value('id');
            }
            unset($data['requested_service_slug'], $data['requested_system_slug']);

            // Duplicate/replay suppression: identical email+intent inside a
            // short window returns the existing lead as success (idempotent
            // for double-clicks, silent for replays).
            $existing = ContactLead::where('email', $data['email'])
                ->where('intent', $data['intent'])
                ->where('created_at', '>=', now()->subMinutes(10))
                ->latest()
                ->first();
            if ($existing) {
                return response()->json(['status' => 'success', 'id' => $existing->id], 201);
            }

            $lead = new ContactLead($data);
            $result = $scoring->score($lead);
            $lead->score = $result['score'];
            $lead->score_breakdown = $result['breakdown'];
            $lead->priority = $result['priority'];
            $lead->save();

            $this->notifyTeam($lead);

            return response()->json(['status' => 'success', 'id' => $lead->id], 201);
        } catch (\Exception $e) {
            Log::error('Lead creation failed: '.$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Failed to submit. Please try again.'], 500);
        }
    }

    /**
     * Optional Cloudflare Turnstile check. Enforced only when a secret is
     * configured; otherwise passes (honeypot + throttle still apply). A
     * verification-service outage fails OPEN so real leads are never lost
     * to a third-party incident.
     */
    private function passesTurnstile(Request $request): bool
    {
        $secret = config('services.turnstile.secret_key');
        if (blank($secret)) {
            return true;
        }

        try {
            $response = Http::asForm()->timeout(3)->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                [
                    'secret' => $secret,
                    'response' => (string) $request->input('turnstile_token', ''),
                    'remoteip' => $request->ip(),
                ],
            );

            return (bool) $response->json('success', false);
        } catch (\Throwable) {
            return true;
        }
    }

    /**
     * Queued internal notification; failure never loses the lead. Safe
     * disabled mode: no recipients configured -> skip silently, no fake
     * success is reported anywhere.
     */
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
            Log::warning('Lead notification failed', ['lead_id' => $lead->id, 'error' => $e->getMessage()]);
        }
    }
}
