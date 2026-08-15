<?php

namespace Tests\Feature\Api\V1;

use App\Models\FaqItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqPublicVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_zero_published_faqs_returns_an_empty_public_collection(): void
    {
        $this->getJson('/api/v1/public/faqs?locale=en')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_one_published_faq_is_visible_publicly(): void
    {
        FaqItem::create([
            'question' => ['en' => 'Q1', 'ar' => 'س1'],
            'answer' => ['en' => 'A1', 'ar' => 'ج1'],
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->getJson('/api/v1/public/faqs?locale=en')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.question', 'Q1')
            ->assertJsonPath('data.0.answer', 'A1');
    }

    public function test_unpublished_faq_is_hidden_publicly(): void
    {
        FaqItem::create([
            'question' => ['en' => 'Hidden Q', 'ar' => 'س مخفي'],
            'answer' => ['en' => 'Hidden A', 'ar' => 'ج مخفي'],
            'is_published' => false,
            'sort_order' => 1,
        ]);

        $this->getJson('/api/v1/public/faqs?locale=en')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_multiple_published_faqs_are_returned_in_sort_order(): void
    {
        FaqItem::create([
            'question' => ['en' => 'Third', 'ar' => 'الثالث'],
            'answer' => ['en' => 'A3', 'ar' => 'ج3'],
            'is_published' => true,
            'sort_order' => 3,
        ]);
        FaqItem::create([
            'question' => ['en' => 'First', 'ar' => 'الأول'],
            'answer' => ['en' => 'A1', 'ar' => 'ج1'],
            'is_published' => true,
            'sort_order' => 1,
        ]);
        FaqItem::create([
            'question' => ['en' => 'Second', 'ar' => 'الثاني'],
            'answer' => ['en' => 'A2', 'ar' => 'ج2'],
            'is_published' => true,
            'sort_order' => 2,
        ]);

        $this->getJson('/api/v1/public/faqs?locale=en')
            ->assertOk()
            ->assertJsonPath('data.0.question', 'First')
            ->assertJsonPath('data.1.question', 'Second')
            ->assertJsonPath('data.2.question', 'Third');
    }

    public function test_public_faqs_use_the_requested_locale(): void
    {
        FaqItem::create([
            'question' => ['en' => 'How much?', 'ar' => 'كم التكلفة؟'],
            'answer' => ['en' => 'It depends.', 'ar' => 'يعتمد على النطاق.'],
            'is_published' => true,
            'sort_order' => 1,
        ]);

        $this->getJson('/api/v1/public/faqs?locale=ar')
            ->assertOk()
            ->assertJsonPath('data.0.question', 'كم التكلفة؟')
            ->assertJsonPath('data.0.answer', 'يعتمد على النطاق.');
    }
}
