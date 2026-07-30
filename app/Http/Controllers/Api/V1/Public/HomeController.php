<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\Public\Concerns\CachesPublicResponses;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\CaseStudyResource;
use App\Http\Resources\V1\Public\ServiceResource;
use App\Http\Resources\V1\Public\SystemResource;
use App\Http\Resources\V1\Public\TestimonialResource;
use App\Models\CaseStudy;
use App\Models\Service;
use App\Models\System;
use App\Models\TeamMember;
use App\Models\Testimonial;

class HomeController extends Controller
{
    use CachesPublicResponses;

    public function index()
    {
        $payload = $this->rememberList('home', 'all', function () {
            return [
                'services' => Service::published()->orderBy('sort_order')->get(),
                'featured_systems' => System::published()->featured()->with('industries')->orderBy('sort_order')->limit(6)->get(),
                'featured_case_studies' => CaseStudy::published()->featured()->with(['serviceOffering', 'system'])->orderBy('sort_order')->limit(6)->get(),
                'testimonials' => Testimonial::approved()->featured()->orderByDesc('given_at')->limit(6)->get(),
                'stats' => [
                    'services' => Service::published()->count(),
                    'systems' => System::published()->count(),
                    'case_studies' => CaseStudy::published()->count(),
                    'team' => TeamMember::published()->count(),
                ],
            ];
        });

        return response()->json([
            'data' => [
                'services' => ServiceResource::collection($payload['services']),
                'featured_systems' => SystemResource::collection($payload['featured_systems']),
                'featured_case_studies' => CaseStudyResource::collection($payload['featured_case_studies']),
                'testimonials' => TestimonialResource::collection($payload['testimonials']),
                'stats' => $payload['stats'],
            ],
        ]);
    }
}
