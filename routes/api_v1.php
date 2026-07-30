<?php

use App\Http\Controllers\Api\V1\Public\ArticleCategoryController;
use App\Http\Controllers\Api\V1\Public\ArticleController;
use App\Http\Controllers\Api\V1\Public\CaseStudyController;
use App\Http\Controllers\Api\V1\Public\EstimatorController;
use App\Http\Controllers\Api\V1\Public\FaqController;
use App\Http\Controllers\Api\V1\Public\HomeController;
use App\Http\Controllers\Api\V1\Public\IndustryController;
use App\Http\Controllers\Api\V1\Public\LeadController;
use App\Http\Controllers\Api\V1\Public\NewsletterController;
use App\Http\Controllers\Api\V1\Public\PreviewController;
use App\Http\Controllers\Api\V1\Public\PricingController;
use App\Http\Controllers\Api\V1\Public\RedirectController;
use App\Http\Controllers\Api\V1\Public\SearchController;
use App\Http\Controllers\Api\V1\Public\ServiceController;
use App\Http\Controllers\Api\V1\Public\SettingsController;
use App\Http\Controllers\Api\V1\Public\SystemController;
use App\Http\Controllers\Api\V1\Public\TeamMemberController;
use App\Http\Controllers\Api\V1\Public\TestimonialController;
use App\Http\Controllers\Api\V1\Public\TrustPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public API v1 -- the versioned, read-only contract the Next.js frontend
| consumes (Phase 5). Only published/approved content is ever exposed;
| admin CRUD stays on the legacy /api/* and Filament /cms routes.
| See docs/architecture/nextjs-laravel-boundary.md.
|--------------------------------------------------------------------------
*/

Route::prefix('v1/public')
    ->middleware('api.locale')
    ->group(function () {
        $cached = 'cache.headers:public;max_age=300;etag';

        Route::get('home', [HomeController::class, 'index'])->middleware($cached);

        Route::get('services', [ServiceController::class, 'index'])->middleware($cached);
        Route::get('services/{slug}', [ServiceController::class, 'show'])->middleware($cached);

        Route::get('systems', [SystemController::class, 'index'])->middleware($cached);
        Route::get('systems/{slug}', [SystemController::class, 'show'])->middleware($cached);

        Route::get('case-studies', [CaseStudyController::class, 'index'])->middleware($cached);
        Route::get('case-studies/{slug}', [CaseStudyController::class, 'show'])->middleware($cached);

        Route::get('industries', [IndustryController::class, 'index'])->middleware($cached);
        Route::get('industries/{slug}', [IndustryController::class, 'show'])->middleware($cached);

        Route::get('articles', [ArticleController::class, 'index'])->middleware($cached);
        Route::get('articles/{slug}', [ArticleController::class, 'show'])->middleware($cached);
        Route::get('article-categories', [ArticleCategoryController::class, 'index'])->middleware($cached);

        // Public content search: rate-limited by the public-api limiter,
        // uncached server-side (high-cardinality queries).
        Route::get('search', [SearchController::class, 'index']);

        // Public company facts (whitelisted fields only).
        Route::get('settings', [SettingsController::class, 'index'])->middleware($cached);

        Route::get('team', [TeamMemberController::class, 'index'])->middleware($cached);
        Route::get('team/{slug}', [TeamMemberController::class, 'show'])->middleware($cached);

        Route::get('trust-pages', [TrustPageController::class, 'index'])->middleware($cached);
        Route::get('trust-pages/{slug}', [TrustPageController::class, 'show'])->middleware($cached);

        Route::get('testimonials', [TestimonialController::class, 'index'])->middleware($cached);

        Route::get('faqs', [FaqController::class, 'index'])->middleware($cached);

        // Pricing page + estimator. Reads are cached; the estimator config
        // is cached, but estimate creation/retrieval/lead are uncached.
        Route::get('pricing', [PricingController::class, 'index'])->middleware($cached);
        Route::get('estimator', [EstimatorController::class, 'config'])->middleware($cached);
        Route::post('estimates', [EstimatorController::class, 'store'])->middleware('throttle:20,1');
        Route::get('estimates/{uuid}', [EstimatorController::class, 'show']);
        Route::post('estimates/{uuid}/lead', [EstimatorController::class, 'submitLead'])->middleware('throttle:5,1');

        // Locale-agnostic; consumed by frontend/next.config.ts at build time.
        Route::get('redirects', [RedirectController::class, 'index'])->middleware($cached);

        // Secure CMS preview: never cached, token IS the auth, throttled
        // against brute-force guessing even though the token space is huge.
        Route::get('preview/{token}', [PreviewController::class, 'show'])->middleware('throttle:60,1');

        // Public writes: throttled, honeypot-checked, no caching.
        Route::post('leads', [LeadController::class, 'store'])->middleware('throttle:5,1');
        Route::post('newsletter', [NewsletterController::class, 'store'])->middleware('throttle:5,1');
    });
