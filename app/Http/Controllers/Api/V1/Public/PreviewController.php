<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Public\ArticleResource;
use App\Http\Resources\V1\Public\CaseStudyResource;
use App\Http\Resources\V1\Public\EngagementModelResource;
use App\Http\Resources\V1\Public\IndustryResource;
use App\Http\Resources\V1\Public\ServiceResource;
use App\Http\Resources\V1\Public\SystemResource;
use App\Http\Resources\V1\Public\TeamMemberResource;
use App\Http\Resources\V1\Public\TrustPageResource;
use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\EngagementModel;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use App\Models\TeamMember;
use App\Models\TrustPage;
use App\Services\PreviewTokenService;
use Illuminate\Http\JsonResponse;

/**
 * Secure preview surface: renders a CMS record's public JSON shape from a
 * one-time signed token, REGARDLESS of its publish/approval state, so
 * editors and reviewers can see exactly what a page will look like before
 * it goes live. Deliberately outside `cache.headers`/`CachesPublicResponses`
 * -- every response is `no-store` and `noindex, nofollow, noarchive`, and
 * an invalid/expired/revoked token 404s identically to a nonexistent one
 * (no oracle for guessing valid tokens).
 */
class PreviewController extends Controller
{
    /** previewable_type => [Resource class, type label]. */
    private const RESOURCE_MAP = [
        Service::class => [ServiceResource::class, 'service'],
        System::class => [SystemResource::class, 'system'],
        CaseStudy::class => [CaseStudyResource::class, 'case_study'],
        Industry::class => [IndustryResource::class, 'industry'],
        Article::class => [ArticleResource::class, 'article'],
        TeamMember::class => [TeamMemberResource::class, 'team_member'],
        TrustPage::class => [TrustPageResource::class, 'trust_page'],
        EngagementModel::class => [EngagementModelResource::class, 'engagement_model'],
    ];

    public function show(string $token, PreviewTokenService $tokens): JsonResponse
    {
        $resolved = $tokens->resolve($token);

        if (! $resolved) {
            abort(404);
        }

        $record = $resolved['record'];
        $preview = $resolved['preview'];
        app()->setLocale($preview->locale);

        [$resourceClass, $typeLabel] = self::RESOURCE_MAP[$record::class]
            ?? [null, class_basename($record)];

        $data = $resourceClass
            ? (new $resourceClass($record))->resolve()
            // PricingProfile and any future type without a dedicated public
            // resource: expose fillable attributes directly rather than
            // silently failing -- still gated by the same token check above.
            : $record->only($record->getFillable());

        return response()->json([
            'data' => [
                'type' => $typeLabel,
                'record' => $data,
                'preview' => [
                    'locale' => $preview->locale,
                    'expires_at' => $preview->expires_at->toIso8601String(),
                ],
            ],
        ])
            ->header('Cache-Control', 'no-store, private')
            ->header('X-Robots-Tag', 'noindex, nofollow, noarchive');
    }
}
