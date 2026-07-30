<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\System;
use Illuminate\Database\Seeder;

/**
 * Deterministic demo content for automated end-to-end tests and local
 * previews. Populates the content types that have no legacy source (Systems,
 * Industries, Articles) so the Playwright smoke suite always has a real,
 * server-rendered detail page of each kind to assert against.
 *
 * Idempotent (updateOrCreate keyed on slug). NOT wired into DatabaseSeeder —
 * invoke explicitly: `php artisan db:seed --class=DemoContentSeeder`. Never run
 * in production; this is bilingual fixture data, not real business content.
 */
class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        // Hard guard: this is bilingual FIXTURE data, never real business
        // content. Refuse to run in production even if invoked by mistake.
        // Override deliberately with ALLOW_DEMO_SEED=true only if you truly
        // intend to inject demo fixtures into a production database.
        if (app()->environment('production') && ! env('ALLOW_DEMO_SEED', false)) {
            $message = 'DemoContentSeeder is blocked in production. Set ALLOW_DEMO_SEED=true to override (not recommended).';
            if (isset($this->command)) {
                $this->command->warn($message);
            }

            throw new \RuntimeException($message);
        }

        $industry = Industry::updateOrCreate(
            ['slug' => 'demo-fintech'],
            [
                'name' => ['en' => 'Fintech', 'ar' => 'التقنية المالية'],
                'summary' => [
                    'en' => 'Software for regulated financial operations.',
                    'ar' => 'برمجيات للعمليات المالية المنظمة.',
                ],
                'description' => [
                    'en' => 'Systems built for banks, lenders, and payment providers.',
                    'ar' => 'أنظمة مبنية للبنوك ومقدمي القروض وخدمات الدفع.',
                ],
                'is_published' => true,
                'published_at' => now()->subDays(30),
                'sort_order' => 1,
            ],
        );

        $system = System::updateOrCreate(
            ['slug' => 'demo-ledger-platform'],
            [
                'type' => System::TYPE_PLATFORM,
                'category' => 'Financial infrastructure',
                'name' => ['en' => 'Ledger Platform', 'ar' => 'منصة السجلات'],
                'tagline' => [
                    'en' => 'Double-entry accounting as a service.',
                    'ar' => 'محاسبة القيد المزدوج كخدمة.',
                ],
                'short_description' => [
                    'en' => 'A real-time, auditable ledger for money movement.',
                    'ar' => 'سجل فوري قابل للتدقيق لحركة الأموال.',
                ],
                'full_description' => [
                    'en' => 'Ledger Platform records every transaction as immutable double-entry lines with a full audit trail.',
                    'ar' => 'تسجل منصة السجلات كل معاملة كقيود مزدوجة غير قابلة للتغيير مع مسار تدقيق كامل.',
                ],
                'problem' => [
                    'en' => 'Teams outgrow spreadsheets and need an auditable source of truth.',
                    'ar' => 'تتجاوز الفرق جداول البيانات وتحتاج إلى مصدر حقيقة قابل للتدقيق.',
                ],
                'solution' => [
                    'en' => 'A hosted ledger with strong consistency and a clear API.',
                    'ar' => 'سجل مستضاف بتناسق قوي وواجهة برمجية واضحة.',
                ],
                'tech_stack' => ['Laravel', 'PostgreSQL', 'Redis'],
                'is_featured' => true,
                'is_published' => true,
                'published_at' => now()->subDays(20),
                'sort_order' => 1,
            ],
        );

        $system->industries()->syncWithoutDetaching([$industry->id]);

        CaseStudy::updateOrCreate(
            ['slug' => 'demo-ledger-platform-rollout'],
            [
                'title' => [
                    'en' => 'Rolling out an auditable ledger',
                    'ar' => 'إطلاق سجل قابل للتدقيق',
                ],
                'summary' => [
                    'en' => 'A demo fixture case study for the Ledger Platform system.',
                    'ar' => 'دراسة حالة توضيحية لنظام منصة السجلات.',
                ],
                'context' => [
                    'en' => 'A fintech team needed an auditable source of truth for money movement.',
                    'ar' => 'احتاج فريق تقنية مالية إلى مصدر حقيقة قابل للتدقيق لحركة الأموال.',
                ],
                'problem' => [
                    'en' => 'Spreadsheet-based tracking could not support a full audit trail.',
                    'ar' => 'لم يستطع التتبع القائم على جداول البيانات دعم مسار تدقيق كامل.',
                ],
                'solution' => [
                    'en' => 'Ledger Platform was deployed as the system of record for every transaction.',
                    'ar' => 'تم نشر منصة السجلات كنظام السجل لكل معاملة.',
                ],
                'system_id' => $system->id,
                'is_published' => true,
                'published_at' => now()->subDays(15),
                'sort_order' => 1,
            ],
        );

        $category = ArticleCategory::updateOrCreate(
            ['slug' => 'demo-fintech'],
            [
                'name' => ['en' => 'Fintech', 'ar' => 'التقنية المالية'],
                'sort_order' => 1,
            ],
        );

        Article::updateOrCreate(
            ['slug' => 'demo-building-auditable-systems'],
            [
                'title' => [
                    'en' => 'Building auditable systems',
                    'ar' => 'بناء أنظمة قابلة للتدقيق',
                ],
                'excerpt' => [
                    'en' => 'Why an immutable audit trail is a feature, not overhead.',
                    'ar' => 'لماذا يُعد مسار التدقيق غير القابل للتغيير ميزة وليس عبئًا.',
                ],
                'body' => [
                    'en' => 'An auditable system records what happened, when, and why, so every state can be reconstructed and trusted.',
                    'ar' => 'يسجل النظام القابل للتدقيق ما حدث ومتى ولماذا، بحيث يمكن إعادة بناء كل حالة والوثوق بها.',
                ],
                'article_category_id' => $category->id,
                'is_published' => true,
                'published_at' => now()->subDays(10),
                'updated_content_at' => now()->subDays(5),
            ],
        );
    }
}
