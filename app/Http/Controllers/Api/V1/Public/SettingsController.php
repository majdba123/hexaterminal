<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Models\CompanySetting;

/**
 * Public company facts for the frontend (footer, contact page, JSON-LD).
 * Strict whitelist: operational fields (lead_recipients, analytics ids)
 * NEVER appear here.
 */
class SettingsController extends Controller
{
    use CachesPublicResponses;

    public function index()
    {
        $settings = $this->rememberList('settings', 'all', function () {
            $s = CompanySetting::current();

            return [
                'company_name' => $s->company_name,
                'tagline' => $s->tagline,
                'description' => $s->description,
                'email' => $s->email,
                'phone' => $s->phone,
                'whatsapp' => $s->whatsapp,
                'address' => $s->address,
                'social_links' => $s->social_links ?: (object) [],
                'booking_url' => $s->booking_url,
                'default_og_image' => $s->default_og_image,
                'footer_note' => $s->footer_note,
            ];
        });

        return response()->json(['data' => $settings]);
    }
}
