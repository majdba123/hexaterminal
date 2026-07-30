<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Minimal newsletter-interest signup. Honeypot + route throttle protected.
 * Privacy behavior: an address that previously unsubscribed is NOT silently
 * reactivated -- we return success (no enumeration signal) but leave the
 * record unsubscribed. Double opt-in is architecturally supported via the
 * 'pending' status; current policy records single opt-in with consent_at.
 */
class NewsletterController extends Controller
{
    public function store(Request $request)
    {
        if (filled($request->input('website'))) {
            return response()->json(['status' => 'success'], 201);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'locale' => 'nullable|string|in:en,ar',
            'source_page' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        $subscriber = NewsletterSubscriber::firstOrNew(['email' => strtolower($data['email'])]);

        if (! $subscriber->exists) {
            $subscriber->fill([
                'locale' => $data['locale'] ?? app()->getLocale(),
                'status' => NewsletterSubscriber::STATUS_ACTIVE,
                'consent_at' => now(),
                'source_page' => $data['source_page'] ?? null,
            ])->save();
        }
        // Existing subscribers (active OR unsubscribed) fall through to the
        // same response: no state change, no enumeration.

        return response()->json(['status' => 'success'], 201);
    }
}
