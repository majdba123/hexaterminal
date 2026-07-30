<?php

namespace Tests\Feature\Pricing;

use App\Models\EngagementModel;
use App\Models\FaqItem;
use App\Models\PricingProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_pricing_lists_published_models_without_fabricated_prices(): void
    {
        EngagementModel::create([
            'slug' => 'mvp', 'title' => ['en' => 'MVP'], 'pricing_display_mode' => 'request_quote',
            'is_published' => true,
        ]);
        EngagementModel::create([
            'slug' => 'draft-model', 'title' => ['en' => 'Draft'], 'is_published' => false,
        ]);

        $res = $this->getJson('/api/v1/public/pricing?locale=en');

        $res->assertOk();
        $models = $res->json('data.engagement_models');
        $this->assertCount(1, $models);
        $this->assertSame('mvp', $models[0]['slug']);
        $this->assertNull($models[0]['pricing']); // fail closed
    }

    public function test_unapproved_price_never_leaks(): void
    {
        $model = EngagementModel::create([
            'slug' => 'mvp', 'title' => ['en' => 'MVP'], 'pricing_display_mode' => 'starting_from',
            'is_published' => true,
        ]);
        // A price exists but is NOT approved.
        PricingProfile::create([
            'priceable_type' => EngagementModel::class, 'priceable_id' => $model->id,
            'currency' => 'USD', 'min_amount' => 15000, 'max_amount' => 30000,
            'approved_for_publication' => false,
        ]);

        $res = $this->getJson('/api/v1/public/pricing?locale=en&currency=USD');

        $this->assertNull($res->json('data.engagement_models.0.pricing'));
    }

    public function test_approved_in_effect_price_is_shown(): void
    {
        $model = EngagementModel::create([
            'slug' => 'mvp', 'title' => ['en' => 'MVP'], 'pricing_display_mode' => 'starting_from',
            'is_published' => true,
        ]);
        PricingProfile::create([
            'priceable_type' => EngagementModel::class, 'priceable_id' => $model->id,
            'currency' => 'USD', 'min_amount' => 15000, 'max_amount' => 30000,
            'approved_for_publication' => true, 'approved_by' => null, 'approved_at' => now(),
            'effective_date' => now()->subDay(),
        ]);

        $res = $this->getJson('/api/v1/public/pricing?locale=en&currency=USD');
        $pricing = $res->json('data.engagement_models.0.pricing');

        $this->assertNotNull($pricing);
        $this->assertSame(15000, $pricing['min_amount']);
    }

    public function test_future_effective_date_fails_closed(): void
    {
        $model = EngagementModel::create([
            'slug' => 'mvp', 'title' => ['en' => 'MVP'], 'pricing_display_mode' => 'starting_from',
            'is_published' => true,
        ]);
        PricingProfile::create([
            'priceable_type' => EngagementModel::class, 'priceable_id' => $model->id,
            'currency' => 'USD', 'min_amount' => 15000, 'max_amount' => 30000,
            'approved_for_publication' => true, 'approved_at' => now(),
            'effective_date' => now()->addWeek(),
        ]);

        $res = $this->getJson('/api/v1/public/pricing?locale=en&currency=USD');
        $this->assertNull($res->json('data.engagement_models.0.pricing'));
    }

    public function test_hidden_and_request_quote_modes_never_show_number(): void
    {
        $model = EngagementModel::create([
            'slug' => 'mvp', 'title' => ['en' => 'MVP'], 'pricing_display_mode' => 'request_quote',
            'is_published' => true,
        ]);
        PricingProfile::create([
            'priceable_type' => EngagementModel::class, 'priceable_id' => $model->id,
            'currency' => 'USD', 'min_amount' => 15000, 'max_amount' => 30000,
            'approved_for_publication' => true, 'approved_at' => now(), 'effective_date' => now()->subDay(),
        ]);

        $res = $this->getJson('/api/v1/public/pricing?locale=en&currency=USD');
        // Even with an approved profile, request_quote mode shows no number.
        $this->assertNull($res->json('data.engagement_models.0.pricing'));
    }

    public function test_only_published_pricing_faqs_are_returned(): void
    {
        FaqItem::create(['question' => ['en' => 'Q1'], 'answer' => ['en' => 'A1'], 'category' => 'pricing', 'is_published' => true]);
        FaqItem::create(['question' => ['en' => 'Q2'], 'answer' => ['en' => 'A2'], 'category' => 'pricing', 'is_published' => false]);
        FaqItem::create(['question' => ['en' => 'Q3'], 'answer' => ['en' => 'A3'], 'category' => 'general', 'is_published' => true]);

        $res = $this->getJson('/api/v1/public/pricing?locale=en');
        $this->assertCount(1, $res->json('data.faqs'));
    }
}
