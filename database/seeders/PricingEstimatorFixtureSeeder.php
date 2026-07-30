<?php

namespace Database\Seeders;

use App\Models\EngagementModel;
use App\Models\EstimatorQuestion;
use App\Models\EstimatorRule;
use App\Models\EstimatorVersion;
use App\Models\FaqItem;
use Illuminate\Database\Seeder;

/**
 * Deterministic fixture for the pricing/estimator feature. Creates:
 *  - six engagement models as CONTENT only (pricing_display_mode =
 *    request_quote, NO PricingProfile) -> the pricing page renders honest
 *    "request a scoped estimate" guidance with ZERO fabricated numbers
 *  - one estimator version (v1) with questions + deterministic rules,
 *    activated so /estimate works locally and in e2e
 *  - twelve financial FAQ drafts (unpublished, founder-review)
 *
 * The estimator's RANGES are indicative and disclaimered by design; the
 * rule numbers here are review fixtures, not founder-approved commercial
 * prices. Founders must review the rules and re-activate an approved
 * version, and approve PricingProfile numbers separately, before these
 * become production pricing (see docs/content/pricing-founder-approval.md).
 *
 * Production-guarded and NOT wired into DatabaseSeeder. Invoke explicitly:
 *   php artisan db:seed --class=PricingEstimatorFixtureSeeder
 */
class PricingEstimatorFixtureSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production') && ! env('ALLOW_DEMO_SEED', false)) {
            $message = 'PricingEstimatorFixtureSeeder is blocked in production. Set ALLOW_DEMO_SEED=true to override (not recommended).';
            if (isset($this->command)) {
                $this->command->warn($message);
            }

            throw new \RuntimeException($message);
        }

        $this->seedEngagementModels();
        $version = $this->seedEstimatorVersion();
        $this->seedQuestions($version);
        $this->seedRules($version);
        $version->activate();
        $this->seedFinancialFaqs();
    }

    private function seedEngagementModels(): void
    {
        $models = [
            [
                'slug' => 'discovery-and-architecture-sprint',
                'title' => ['en' => 'Discovery & Architecture Sprint', 'ar' => 'سبرنت الاكتشاف والهندسة المعمارية'],
                'summary' => ['en' => 'A fixed, short engagement to turn an idea or rough requirements into a concrete technical plan.', 'ar' => 'ارتباط قصير ومحدّد لتحويل فكرة أو متطلبات مبدئية إلى خطة تقنية ملموسة.'],
                'buyer_fit' => ['en' => 'You have an idea or early requirements and want a costed, de-risked plan before committing to a build.', 'ar' => 'لديك فكرة أو متطلبات مبكرة وتريد خطة مُسعّرة ومنخفضة المخاطر قبل الالتزام بالبناء.'],
                'billing_model' => 'discovery_sprint',
                'cta_intent' => 'book_call',
            ],
            [
                'slug' => 'mvp-focused-release',
                'title' => ['en' => 'MVP / Focused Product Release', 'ar' => 'إصدار منتج أولي مركّز'],
                'summary' => ['en' => 'Build the smallest production-quality version that delivers real operational value.', 'ar' => 'بناء أصغر نسخة بجودة إنتاجية تُقدّم قيمة تشغيلية حقيقية.'],
                'buyer_fit' => ['en' => 'You have validated requirements and want a real, launchable first version, not a throwaway prototype.', 'ar' => 'لديك متطلبات مُتحقَّق منها وتريد نسخة أولى حقيقية قابلة للإطلاق، لا نموذجًا يُرمى.'],
                'billing_model' => 'milestone_based',
                'is_featured' => true,
                'cta_intent' => 'start_project',
            ],
            [
                'slug' => 'custom-business-system',
                'title' => ['en' => 'Custom Business System', 'ar' => 'نظام أعمال مخصّص'],
                'summary' => ['en' => 'A full system built around your real operational workflow -- CRM/ERP, automation, or backend platform.', 'ar' => 'نظام كامل مبني حول سير عملك التشغيلي الفعلي -- CRM/ERP أو أتمتة أو منصة خلفية.'],
                'buyer_fit' => ['en' => 'No off-the-shelf product fits how your business actually runs.', 'ar' => 'لا يناسب أي منتج جاهز طريقة عمل مؤسستك الفعلية.'],
                'billing_model' => 'milestone_based',
                'cta_intent' => 'request_quote',
            ],
            [
                'slug' => 'dedicated-engineering-capacity',
                'title' => ['en' => 'Dedicated Engineering Capacity', 'ar' => 'طاقة هندسية مخصّصة'],
                'summary' => ['en' => 'An embedded engineering team working continuously on your product roadmap.', 'ar' => 'فريق هندسي مدمج يعمل باستمرار على خارطة طريق منتجك.'],
                'buyer_fit' => ['en' => 'You have ongoing product development and want reliable, continuous delivery capacity.', 'ar' => 'لديك تطوير منتج مستمر وتريد طاقة تسليم موثوقة ومستمرة.'],
                'billing_model' => 'dedicated_team',
                'cta_intent' => 'book_call',
            ],
            [
                'slug' => 'modernization-and-integration',
                'title' => ['en' => 'Modernization & Integration', 'ar' => 'التحديث والتكامل'],
                'summary' => ['en' => 'Modernize an existing system or connect it to the tools your business already uses.', 'ar' => 'تحديث نظام قائم أو ربطه بالأدوات التي تستخدمها مؤسستك بالفعل.'],
                'buyer_fit' => ['en' => 'You have a working but ageing system, or disconnected tools that need to talk to each other.', 'ar' => 'لديك نظام يعمل لكنه قديم، أو أدوات غير متصلة تحتاج للتواصل فيما بينها.'],
                'billing_model' => 'milestone_based',
                'cta_intent' => 'request_quote',
            ],
            [
                'slug' => 'support-and-continuous-improvement',
                'title' => ['en' => 'Support & Continuous Improvement', 'ar' => 'الدعم والتحسين المستمر'],
                'summary' => ['en' => 'Keep a live system healthy and steadily improving after launch.', 'ar' => 'إبقاء نظام حي سليمًا وفي تحسّن مستمر بعد الإطلاق.'],
                'buyer_fit' => ['en' => 'Your system is live and you want dependable maintenance and incremental improvement.', 'ar' => 'نظامك حي وتريد صيانة موثوقة وتحسينًا تدريجيًا.'],
                'billing_model' => 'support_plan',
                'cta_intent' => 'book_call',
            ],
        ];

        foreach ($models as $i => $model) {
            EngagementModel::updateOrCreate(
                ['slug' => $model['slug']],
                array_merge($model, [
                    // Content only -- NO price is ever shown until a founder
                    // approves a PricingProfile. Fail closed.
                    'pricing_display_mode' => 'request_quote',
                    'cta_label' => null,
                    'is_published' => true,
                    'is_featured' => $model['is_featured'] ?? false,
                    'sort_order' => $i + 1,
                ]),
            );
        }
    }

    private function seedEstimatorVersion(): EstimatorVersion
    {
        return EstimatorVersion::updateOrCreate(
            ['key' => 'v1'],
            [
                'label' => 'Baseline estimator (fixture)',
                'status' => 'draft',
                'is_active' => false,
                'base_currency' => 'USD',
                'currency_rates' => ['USD' => 1, 'AED' => 3.6725, 'SAR' => 3.75],
                'floor_min' => 4000,
                'ceiling_max' => 400000,
                'notes' => 'Deterministic fixture. Founder review required before production activation.',
            ],
        );
    }

    private function seedQuestions(EstimatorVersion $version): void
    {
        $version->questions()->delete();

        $q = [
            ['build', 1, 'single_select', ['en' => 'What are you building?', 'ar' => 'ما الذي تبنيه؟'], null, [
                ['saas', ['en' => 'SaaS platform', 'ar' => 'منصة SaaS']],
                ['crm_erp', ['en' => 'CRM / ERP system', 'ar' => 'نظام CRM / ERP']],
                ['ai_workflow', ['en' => 'AI-enabled workflow', 'ar' => 'سير عمل مدعوم بالذكاء الاصطناعي']],
                ['api', ['en' => 'API / backend', 'ar' => 'واجهة برمجية / نظام خلفي']],
                ['automation', ['en' => 'Business automation', 'ar' => 'أتمتة أعمال']],
                ['modernization', ['en' => 'Modernize an existing system', 'ar' => 'تحديث نظام قائم']],
                ['other', ['en' => 'Something else', 'ar' => 'شيء آخر']],
            ]],
            ['stage', 2, 'single_select', ['en' => 'Where are you now?', 'ar' => 'أين أنت الآن؟'], null, [
                ['idea', ['en' => 'Just an idea', 'ar' => 'مجرد فكرة']],
                ['documented', ['en' => 'Documented requirements', 'ar' => 'متطلبات موثّقة']],
                ['prototype', ['en' => 'An existing prototype', 'ar' => 'نموذج أولي قائم']],
                ['production', ['en' => 'A live production system', 'ar' => 'نظام إنتاجي حي']],
            ]],
            ['platforms', 3, 'multi_select', ['en' => 'Which platforms do you need?', 'ar' => 'ما المنصّات التي تحتاجها؟'], null, [
                ['web', ['en' => 'Web app', 'ar' => 'تطبيق ويب']],
                ['mobile', ['en' => 'Mobile app', 'ar' => 'تطبيق جوّال']],
                ['internal_portal', ['en' => 'Internal / admin portal', 'ar' => 'بوابة داخلية / إدارية']],
                ['api_only', ['en' => 'API only', 'ar' => 'واجهة برمجية فقط']],
            ]],
            ['users', 4, 'single_select', ['en' => 'How complex are users and permissions?', 'ar' => 'ما مدى تعقيد المستخدمين والصلاحيات؟'], null, [
                ['simple', ['en' => 'Simple users', 'ar' => 'مستخدمون بسطاء']],
                ['multiple_roles', ['en' => 'Multiple roles', 'ar' => 'أدوار متعدّدة']],
                ['advanced_rbac', ['en' => 'Advanced role-based access', 'ar' => 'تحكّم وصول متقدّم حسب الأدوار']],
                ['multi_tenant', ['en' => 'Multi-tenant isolation', 'ar' => 'عزل متعدّد المستأجرين']],
            ]],
            ['complexity_core', 5, 'multi_select', ['en' => 'What describes the core?', 'ar' => 'ما الذي يصف الجوهر؟'], null, [
                ['standard_crud', ['en' => 'Standard workflows', 'ar' => 'سير عمل قياسي']],
                ['advanced_rules', ['en' => 'Advanced business rules', 'ar' => 'قواعد أعمال متقدّمة']],
                ['realtime', ['en' => 'Real-time operations', 'ar' => 'عمليات فورية']],
                ['complex_reporting', ['en' => 'Complex reporting', 'ar' => 'تقارير معقّدة']],
                ['high_scale_compliance', ['en' => 'High-scale or compliance', 'ar' => 'حجم كبير أو امتثال']],
            ]],
            ['integrations', 6, 'single_select', ['en' => 'How many integrations?', 'ar' => 'كم عدد التكاملات؟'], null, [
                ['none', ['en' => 'None', 'ar' => 'لا شيء']],
                ['one_two', ['en' => '1–2 standard APIs', 'ar' => 'واجهتان قياسيتان']],
                ['several', ['en' => 'Several APIs', 'ar' => 'عدّة واجهات']],
                ['legacy_complex', ['en' => 'Legacy / complex', 'ar' => 'قديمة / معقّدة']],
            ]],
            ['ai', 7, 'single_select', ['en' => 'Any AI requirements?', 'ar' => 'هل من متطلبات ذكاء اصطناعي؟'], null, [
                ['none', ['en' => 'None', 'ar' => 'لا شيء']],
                ['standard_llm', ['en' => 'Standard LLM integration', 'ar' => 'تكامل نموذج لغوي قياسي']],
                ['doc_search', ['en' => 'Document / search workflows', 'ar' => 'سير عمل مستندات / بحث']],
                ['scoring_reco', ['en' => 'Scoring / recommendations', 'ar' => 'تقييم / توصيات']],
                ['agentic', ['en' => 'Advanced agentic workflow', 'ar' => 'سير عمل وكلاء متقدّم']],
            ]],
            ['migration', 8, 'single_select', ['en' => 'Do you need data migration?', 'ar' => 'هل تحتاج ترحيل بيانات؟'], ['question' => 'stage', 'in' => ['documented', 'prototype', 'production']], [
                ['none', ['en' => 'None', 'ar' => 'لا شيء']],
                ['simple_import', ['en' => 'Simple import', 'ar' => 'استيراد بسيط']],
                ['db_migration', ['en' => 'Existing database migration', 'ar' => 'ترحيل قاعدة بيانات قائمة']],
                ['complex_legacy', ['en' => 'Complex legacy migration', 'ar' => 'ترحيل قديم معقّد']],
            ]],
            ['design', 9, 'single_select', ['en' => 'Design readiness?', 'ar' => 'جاهزية التصميم؟'], null, [
                ['supplied', ['en' => 'Design supplied', 'ar' => 'التصميم مُقدَّم']],
                ['design_system', ['en' => 'Design system exists', 'ar' => 'يوجد نظام تصميم']],
                ['needed', ['en' => 'UX/UI needed', 'ar' => 'نحتاج تصميم تجربة/واجهة']],
            ]],
            ['timeline', 10, 'single_select', ['en' => 'How urgent is delivery?', 'ar' => 'ما مدى إلحاح التسليم؟'], null, [
                ['flexible', ['en' => 'Flexible', 'ar' => 'مرن']],
                ['standard', ['en' => 'Standard', 'ar' => 'قياسي']],
                ['accelerated', ['en' => 'Accelerated', 'ar' => 'مُعجَّل']],
                ['urgent', ['en' => 'Urgent', 'ar' => 'عاجل']],
            ]],
            ['post_launch', 11, 'single_select', ['en' => 'Post-launch needs?', 'ar' => 'احتياجات ما بعد الإطلاق؟'], null, [
                ['handoff', ['en' => 'Handoff only', 'ar' => 'تسليم فقط']],
                ['maintenance', ['en' => 'Maintenance', 'ar' => 'صيانة']],
                ['ongoing', ['en' => 'Ongoing development', 'ar' => 'تطوير مستمر']],
                ['sla', ['en' => 'SLA / support', 'ar' => 'دعم باتفاقية مستوى خدمة']],
            ]],
        ];

        foreach ($q as $i => [$key, $step, $type, $prompt, $showIf, $options]) {
            EstimatorQuestion::create([
                'estimator_version_id' => $version->id,
                'key' => $key,
                'step' => $step,
                'sort_order' => $i + 1,
                'type' => $type,
                'prompt' => $prompt,
                'is_required' => $key !== 'post_launch',
                'show_if' => $showIf,
                'options' => array_map(fn ($o) => ['key' => $o[0], 'label' => $o[1]], $options),
            ]);
        }
    }

    private function seedRules(EstimatorVersion $version): void
    {
        $version->rules()->delete();
        $sort = 0;
        $add = function (array $rule) use ($version, &$sort): void {
            EstimatorRule::create(array_merge([
                'estimator_version_id' => $version->id,
                'sort_order' => $sort++,
            ], $rule));
        };

        // Base bands per build type [min,max,wkmin,wkmax,cx].
        $bases = [
            'saas' => [20000, 40000, 8, 14, 3],
            'crm_erp' => [18000, 38000, 8, 14, 3],
            'ai_workflow' => [12000, 28000, 6, 12, 2],
            'api' => [10000, 22000, 5, 10, 1],
            'automation' => [8000, 18000, 4, 8, 1],
            'modernization' => [15000, 35000, 6, 12, 2],
            'other' => [10000, 25000, 5, 10, 1],
        ];
        foreach ($bases as $opt => [$mn, $mx, $wn, $wx, $cx]) {
            $add(['driver' => 'base', 'question_key' => 'build', 'option_key' => $opt, 'effect' => 'base',
                'amount_min' => $mn, 'amount_max' => $mx, 'weeks_min' => $wn, 'weeks_max' => $wx, 'complexity_weight' => $cx]);
        }

        // Add-on drivers: [driver, qkey, okey, min, max, wmin, wmax, cx, labelEn, labelAr]
        $addons = [
            ['platform_mobile', 'platforms', 'mobile', 6000, 12000, 2, 3, 1, 'Mobile app', 'تطبيق جوّال'],
            ['platform_portal', 'platforms', 'internal_portal', 3000, 6000, 0, 1, 0, 'Internal portal', 'بوابة داخلية'],
            ['users_roles', 'users', 'multiple_roles', 2000, 4000, 0, 0, 1, 'Multiple roles', 'أدوار متعدّدة'],
            ['users_rbac', 'users', 'advanced_rbac', 4000, 9000, 0, 1, 2, 'Advanced access control', 'تحكّم وصول متقدّم'],
            ['users_tenant', 'users', 'multi_tenant', 8000, 18000, 2, 3, 3, 'Multi-tenant isolation', 'عزل متعدّد المستأجرين'],
            ['core_rules', 'complexity_core', 'advanced_rules', 4000, 9000, 0, 1, 2, 'Advanced business rules', 'قواعد أعمال متقدّمة'],
            ['core_realtime', 'complexity_core', 'realtime', 6000, 14000, 2, 3, 2, 'Real-time operations', 'عمليات فورية'],
            ['core_reporting', 'complexity_core', 'complex_reporting', 4000, 10000, 0, 1, 2, 'Complex reporting', 'تقارير معقّدة'],
            ['core_scale', 'complexity_core', 'high_scale_compliance', 10000, 22000, 2, 4, 3, 'High-scale / compliance', 'حجم كبير / امتثال'],
            ['integrations_12', 'integrations', 'one_two', 2000, 5000, 0, 0, 1, 'Standard integrations', 'تكاملات قياسية'],
            ['integrations_several', 'integrations', 'several', 5000, 12000, 1, 2, 2, 'Several integrations', 'عدّة تكاملات'],
            ['integrations_legacy', 'integrations', 'legacy_complex', 9000, 20000, 2, 3, 3, 'Legacy integrations', 'تكاملات قديمة'],
            ['ai_llm', 'ai', 'standard_llm', 4000, 9000, 0, 1, 1, 'LLM integration', 'تكامل نموذج لغوي'],
            ['ai_docsearch', 'ai', 'doc_search', 6000, 13000, 1, 2, 2, 'Document / search AI', 'ذكاء المستندات / البحث'],
            ['ai_scoring', 'ai', 'scoring_reco', 5000, 11000, 0, 1, 2, 'Scoring / recommendations', 'تقييم / توصيات'],
            ['ai_agentic', 'ai', 'agentic', 10000, 22000, 2, 3, 3, 'Agentic AI workflow', 'سير عمل وكلاء'],
            ['migration_simple', 'migration', 'simple_import', 1500, 4000, 0, 0, 0, 'Data import', 'استيراد بيانات'],
            ['migration_db', 'migration', 'db_migration', 4000, 9000, 1, 1, 1, 'Database migration', 'ترحيل قاعدة بيانات'],
            ['migration_legacy', 'migration', 'complex_legacy', 9000, 20000, 2, 3, 2, 'Complex legacy migration', 'ترحيل قديم معقّد'],
            ['design_system', 'design', 'design_system', 1500, 3500, 0, 0, 0, 'Design system adaptation', 'تكييف نظام تصميم'],
            ['design_needed', 'design', 'needed', 5000, 12000, 2, 3, 1, 'UX/UI design', 'تصميم تجربة/واجهة'],
        ];
        foreach ($addons as [$driver, $qk, $ok, $mn, $mx, $wn, $wx, $cx, $le, $la]) {
            $add(['driver' => $driver, 'question_key' => $qk, 'option_key' => $ok, 'effect' => 'add',
                'amount_min' => $mn, 'amount_max' => $mx, 'weeks_min' => $wn, 'weeks_max' => $wx,
                'complexity_weight' => $cx, 'label' => ['en' => $le, 'ar' => $la]]);
        }

        // Timeline multipliers.
        $add(['driver' => 'timeline_accelerated', 'question_key' => 'timeline', 'option_key' => 'accelerated', 'effect' => 'multiply',
            'factor' => 1.15, 'complexity_weight' => 0, 'label' => ['en' => 'Accelerated timeline', 'ar' => 'جدول زمني مُعجَّل']]);
        $add(['driver' => 'timeline_urgent', 'question_key' => 'timeline', 'option_key' => 'urgent', 'effect' => 'multiply',
            'factor' => 1.3, 'complexity_weight' => 1, 'label' => ['en' => 'Urgent timeline', 'ar' => 'جدول زمني عاجل']]);
    }

    private function seedFinancialFaqs(): void
    {
        $faqs = [
            ['en' => 'How much does a custom software system cost?', 'ar' => 'كم تكلفة نظام برمجي مخصّص؟',
                'ae' => 'It depends on scope. Custom systems vary widely with users, integrations, AI, and data needs. The cost estimator gives an indicative range in minutes; a discovery conversation produces a scoped figure.', 'aa' => 'يعتمد على النطاق. تتفاوت الأنظمة المخصّصة بحسب المستخدمين والتكاملات والذكاء الاصطناعي واحتياجات البيانات. تمنحك حاسبة التكلفة نطاقًا استرشاديًا خلال دقائق، وتُنتج المحادثة الاستكشافية رقمًا محدّد النطاق.'],
            ['en' => 'Why are prices shown as ranges?', 'ar' => 'لماذا تُعرض الأسعار كنطاقات؟',
                'ae' => 'A range is honest before scope is fixed. A single exact number early on would be false precision; the range narrows as requirements are confirmed.', 'aa' => 'النطاق أصدق قبل تثبيت النطاق. الرقم الدقيق الواحد مبكرًا يكون دقة زائفة؛ ويضيق النطاق كلما تأكّدت المتطلبات.'],
            ['en' => 'What is included in the estimate?', 'ar' => 'ما الذي يتضمّنه التقدير؟',
                'ae' => 'Design and engineering effort for the scope you described. It excludes third-party service costs and taxes, which depend on your setup and jurisdiction.', 'aa' => 'جهد التصميم والهندسة للنطاق الذي وصفته. ويستثني تكاليف خدمات الأطراف الثالثة والضرائب، التي تعتمد على إعدادك وولايتك القضائية.'],
            ['en' => 'What can increase the final cost?', 'ar' => 'ما الذي قد يزيد التكلفة النهائية؟',
                'ae' => 'Additional integrations, multi-tenant isolation, advanced AI, complex data migration, and accelerated timelines are the most common drivers.', 'aa' => 'التكاملات الإضافية والعزل متعدّد المستأجرين والذكاء الاصطناعي المتقدّم وترحيل البيانات المعقّد والجداول الزمنية المُعجَّلة هي أكثر العوامل شيوعًا.'],
            ['en' => 'How are payments structured?', 'ar' => 'كيف تُنظَّم المدفوعات؟',
                'ae' => 'Payment structure is agreed per engagement during scoping. (Founder-approved wording pending.)', 'aa' => 'يُتّفق على هيكل الدفع لكل ارتباط أثناء تحديد النطاق. (الصياغة المعتمدة من المؤسّس قيد الإعداد.)'],
            ['en' => 'Who owns the source code?', 'ar' => 'من يملك الشيفرة المصدرية؟',
                'ae' => 'Code ownership terms are confirmed in the agreement. (Founder-approved wording pending.)', 'aa' => 'تُؤكَّد شروط ملكية الشيفرة في الاتفاقية. (الصياغة المعتمدة من المؤسّس قيد الإعداد.)'],
            ['en' => 'Are third-party services included?', 'ar' => 'هل خدمات الأطراف الثالثة مشمولة؟',
                'ae' => 'No. Hosting, licenses, and paid APIs are billed to you directly by their providers and are excluded from estimates.', 'aa' => 'لا. تُفوتر الاستضافة والتراخيص والواجهات المدفوعة عليك مباشرة من مزوّديها وتُستثنى من التقديرات.'],
            ['en' => 'Is maintenance included?', 'ar' => 'هل الصيانة مشمولة؟',
                'ae' => 'Maintenance is offered as a separate, optional plan rather than bundled into the build estimate.', 'aa' => 'تُقدَّم الصيانة كخطة منفصلة اختيارية بدلًا من تضمينها في تقدير البناء.'],
            ['en' => 'Can an existing system be modernized?', 'ar' => 'هل يمكن تحديث نظام قائم؟',
                'ae' => 'Yes. We assess the current system and data first, then plan an incremental modernization path.', 'aa' => 'نعم. نُقيّم النظام والبيانات الحالية أولًا، ثم نخطّط مسار تحديث تدريجي.'],
            ['en' => 'Can the project start with a discovery sprint?', 'ar' => 'هل يمكن بدء المشروع بسبرنت اكتشاف؟',
                'ae' => 'Yes. A discovery and architecture sprint is often the lowest-risk way to begin when scope is not yet fixed.', 'aa' => 'نعم. غالبًا ما يكون سبرنت الاكتشاف والهندسة أقل الطرق مخاطرةً للبدء عندما لا يكون النطاق مثبّتًا بعد.'],
            ['en' => 'Can Hexa Terminal work in milestones?', 'ar' => 'هل تعمل هيكسا تيرمينال بالمراحل؟',
                'ae' => 'Milestone-based delivery is one of the supported engagement structures, confirmed during scoping.', 'aa' => 'التسليم القائم على المراحل أحد هياكل الارتباط المدعومة، ويُؤكَّد أثناء تحديد النطاق.'],
            ['en' => 'Does the estimator create a binding quotation?', 'ar' => 'هل تُنشئ الحاسبة عرض سعر مُلزِمًا؟',
                'ae' => 'No. The estimate is indicative and non-binding. A binding quotation follows a discovery conversation and confirmed scope.', 'aa' => 'لا. التقدير استرشادي وغير مُلزِم. ويأتي العرض المُلزِم بعد محادثة استكشافية ونطاق مؤكَّد.'],
        ];

        foreach ($faqs as $i => $faq) {
            FaqItem::updateOrCreate(
                ['category' => 'pricing', 'sort_order' => $i + 1],
                [
                    'question' => ['en' => $faq['en'], 'ar' => $faq['ar']],
                    'answer' => ['en' => $faq['ae'], 'ar' => $faq['aa']],
                    'is_published' => false,
                ],
            );
        }
    }
}
