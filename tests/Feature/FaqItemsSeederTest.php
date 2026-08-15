<?php

namespace Tests\Feature;

use App\Models\FaqItem;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\FaqItemsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FaqItemsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_faq_items_seeder_creates_the_ten_approved_localized_faqs(): void
    {
        $this->seed(FaqItemsSeeder::class);

        $this->assertDatabaseCount('faqs', 10);
        $this->assertSame(range(1, 10), FaqItem::published()->orderBy('sort_order')->pluck('sort_order')->all());
        $this->assertSame(
            [
                'How much does a custom software project cost?',
                'How long does a project take?',
                'What if our requirements are not fully defined yet?',
                'Can you work with an existing system instead of building from scratch?',
                'Can a project be delivered in phases?',
                'Do you provide support after launch?',
                'Can you integrate the new system with our existing tools?',
                'Can you build both web platforms and mobile applications?',
                'Can HexaTerminal work with remote clients and teams?',
                'How are source code, access, and project handover handled?',
            ],
            FaqItem::published()->orderBy('sort_order')->get()->map(fn (FaqItem $faq) => $faq->getTranslation('question', 'en'))->all()
        );

        $first = FaqItem::published()->orderBy('sort_order')->firstOrFail();
        $this->assertSame('How much does a custom software project cost?', $first->getTranslation('question', 'en'));
        $this->assertSame('كم تكلفة تطوير مشروع برمجي مخصص؟', $first->getTranslation('question', 'ar'));
        $this->assertSame(
            'The cost depends on the actual scope of the project, including workflows, users, integrations, data, technical complexity, delivery requirements, and post-launch needs. We first understand the project and define the scope before providing a commercial proposal.',
            $first->getTranslation('answer', 'en')
        );
        $this->assertSame(
            'تعتمد التكلفة على النطاق الفعلي للمشروع، بما يشمل العمليات، والمستخدمين، والتكاملات، والبيانات، والتعقيد التقني، ومتطلبات التنفيذ، واحتياجات ما بعد الإطلاق. نبدأ أولاً بفهم المشروع وتحديد نطاقه قبل تقديم العرض التجاري.',
            $first->getTranslation('answer', 'ar')
        );
        $this->assertNull($first->category);
        $this->assertTrue($first->is_published);
    }

    public function test_faq_items_seeder_is_idempotent_via_english_question_match(): void
    {
        $this->seed(FaqItemsSeeder::class);
        $this->seed(FaqItemsSeeder::class);

        $this->assertDatabaseCount('faqs', 10);
    }

    public function test_database_seeder_preserves_existing_approved_seed_data_and_adds_faqs(): void
    {
        config([
            'app.admin_email' => 'bootstrap-admin@hexaterminal.test',
            'app.admin_password' => 'a-long-secure-password',
        ]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('service_offerings', 3);
        $this->assertDatabaseCount('company_settings', 1);
        $this->assertDatabaseCount('team_members', 1);
        $this->assertDatabaseCount('engagement_models', 4);
        $this->assertDatabaseCount('faqs', 10);
        $this->assertDatabaseCount('systems', 0);
        $this->assertDatabaseCount('industries', 0);
        $this->assertDatabaseCount('case_studies', 0);
        $this->assertDatabaseCount('testimonials', 0);
        $this->assertDatabaseCount('articles', 0);

        $this->getJson('/api/v1/public/faqs?locale=ar')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('data.0.question', 'كم تكلفة تطوير مشروع برمجي مخصص؟')
            ->assertJsonPath('data.9.question', 'كيف يتم التعامل مع الكود المصدري والصلاحيات وتسليم المشروع؟');

        $this->getJson('/api/v1/public/pricing?locale=en')
            ->assertOk()
            ->assertJsonCount(0, 'data.faqs');
    }
}
