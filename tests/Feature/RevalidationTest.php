<?php

namespace Tests\Feature;

use App\Models\FaqItem;
use App\Models\CaseStudy;
use App\Models\Service;
use App\Services\RevalidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class RevalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_is_a_noop_when_disabled(): void
    {
        Http::fake();
        config(['revalidation.enabled' => false]);

        $sent = app(RevalidationService::class)->revalidate('systems', 'demo');

        $this->assertFalse($sent);
        Http::assertNothingSent();
    }

    public function test_service_is_a_noop_when_url_or_secret_missing(): void
    {
        Http::fake();
        config([
            'revalidation.enabled' => true,
            'revalidation.url' => null,
            'revalidation.secret' => null,
        ]);

        $this->assertFalse(app(RevalidationService::class)->enabled());
        app(RevalidationService::class)->revalidate('systems', 'demo');
        Http::assertNothingSent();
    }

    public function test_service_posts_secret_header_and_payload_when_enabled(): void
    {
        Http::fake(['*' => Http::response(['revalidated' => true], 200)]);
        config([
            'revalidation.enabled' => true,
            'revalidation.url' => 'https://staging.example.test/api/revalidate',
            'revalidation.secret' => 'top-secret-value',
        ]);

        $sent = app(RevalidationService::class)->revalidate('systems', 'demo-system');

        $this->assertTrue($sent);
        Http::assertSent(function ($request) {
            return $request->url() === 'https://staging.example.test/api/revalidate'
                && $request->hasHeader('x-revalidate-secret', 'top-secret-value')
                && $request['resource'] === 'systems'
                && $request['slug'] === 'demo-system'
                && is_int($request['ts']);
        });
    }

    public function test_non_content_resource_maps_to_home(): void
    {
        Http::fake(['*' => Http::response(['revalidated' => true], 200)]);
        config([
            'revalidation.enabled' => true,
            'revalidation.url' => 'https://staging.example.test/api/revalidate',
            'revalidation.secret' => 's',
        ]);

        app(RevalidationService::class)->revalidate('faqs');

        Http::assertSent(fn ($request) => $request['resource'] === 'home' && ! isset($request['slug']));
    }

    public function test_saving_a_model_triggers_revalidation_when_enabled(): void
    {
        Http::fake(['*' => Http::response(['revalidated' => true], 200)]);
        config([
            'revalidation.enabled' => true,
            'revalidation.url' => 'https://staging.example.test/api/revalidate',
            'revalidation.secret' => 's',
        ]);

        Service::create(['slug' => 'crm', 'name' => ['en' => 'CRM'], 'is_published' => true]);

        Http::assertSent(fn ($request) => $request['resource'] === 'services' && $request['slug'] === 'crm');
    }

    public function test_saving_a_faq_item_triggers_home_revalidation_when_enabled(): void
    {
        Http::fake(['*' => Http::response(['revalidated' => true], 200)]);
        config([
            'revalidation.enabled' => true,
            'revalidation.url' => 'https://staging.example.test/api/revalidate',
            'revalidation.secret' => 's',
        ]);

        FaqItem::create([
            'question' => ['en' => 'Q', 'ar' => 'س'],
            'answer' => ['en' => 'A', 'ar' => 'ج'],
            'is_published' => true,
            'sort_order' => 1,
        ]);

        Http::assertSent(fn ($request) => $request['resource'] === 'home' && ! isset($request['slug']));
    }

    public function test_a_failing_frontend_never_breaks_the_save(): void
    {
        Http::fake(['*' => Http::response('boom', 500)]);
        config([
            'revalidation.enabled' => true,
            'revalidation.url' => 'https://staging.example.test/api/revalidate',
            'revalidation.secret' => 's',
        ]);

        // Must not throw despite the 500 from the frontend.
        $service = Service::create(['slug' => 'ok', 'name' => ['en' => 'OK'], 'is_published' => true]);

        $this->assertNotNull($service->id);
        $this->assertTrue(Service::where('slug', 'ok')->exists());
    }

    public function test_saving_a_case_study_clears_both_locale_home_caches(): void
    {
        config(['revalidation.enabled' => false]);

        $caseStudy = CaseStudy::create([
            'slug' => 'locale-cache-check',
            'title' => ['en' => 'Locale cache check', 'ar' => 'فحص ذاكرة التخزين المؤقت'],
            'summary' => ['en' => 'Summary', 'ar' => 'ملخص'],
            'is_featured' => false,
            'is_published' => true,
        ]);

        Cache::put('api:v1:public:home:list:en:all', 'en', now()->addMinute());
        Cache::put('api:v1:public:home:list:ar:all', 'ar', now()->addMinute());

        $caseStudy->update(['is_featured' => true]);

        $this->assertFalse(Cache::has('api:v1:public:home:list:en:all'));
        $this->assertFalse(Cache::has('api:v1:public:home:list:ar:all'));
    }
}
