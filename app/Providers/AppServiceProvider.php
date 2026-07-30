<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\FaqItem;
use App\Models\Industry;
use App\Models\PublicClaim;
use App\Models\Service;
use App\Models\System;
use App\Models\TeamMember;
use App\Models\Testimonial;
use App\Models\TrustPage;
use App\Observers\ArticleObserver;
use App\Observers\CaseStudyObserver;
use App\Observers\FaqItemObserver;
use App\Observers\IndustryObserver;
use App\Observers\PublicClaimObserver;
use App\Observers\ServiceObserver;
use App\Observers\SystemObserver;
use App\Observers\TeamMemberObserver;
use App\Observers\TestimonialObserver;
use App\Observers\TrustPageObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // AI SEO assistant provider. Anthropic is the only real provider;
        // without credentials it self-reports unavailable and AiSeoService
        // surfaces a disabled state instead of fake success.
        $this->app->bind(
            \App\Services\AiSeo\AiSeoProviderInterface::class,
            \App\Services\AiSeo\AnthropicSeoProvider::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Service::observe(ServiceObserver::class);
        System::observe(SystemObserver::class);
        CaseStudy::observe(CaseStudyObserver::class);
        Industry::observe(IndustryObserver::class);
        Article::observe(ArticleObserver::class);
        TeamMember::observe(TeamMemberObserver::class);
        Testimonial::observe(TestimonialObserver::class);
        FaqItem::observe(FaqItemObserver::class);
        TrustPage::observe(TrustPageObserver::class);
        PublicClaim::observe(PublicClaimObserver::class);
    }
}
