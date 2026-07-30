<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\CompanySetting;
use App\Models\FaqItem;
use App\Models\SeoMeta;
use App\Models\Service;
use App\Models\System;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Founder-reviewed content population for the launch-readiness sprint.
 *
 * Everything this seeder writes is either:
 *  (a) a safety correction to data already in the database (unpublishing
 *      the fictional demo case studies, un-approving unverified
 *      testimonials, un-publishing the mistranslated legacy service rows),
 *      or
 *  (b) new content entered in `draft`/`in_review` editorial status so it
 *      can never reach the public site until a human approves and
 *      publishes it through the normal Filament workflow.
 *
 * It never invents systems, case-study metrics, testimonials, or team
 * bios that have no factual basis in the repository -- see
 * docs/content/current-content-inventory.md for what was and wasn't
 * available at the time this was written.
 *
 * Idempotent (updateOrCreate keyed on slug / safe re-application of
 * status corrections). Not wired into DatabaseSeeder -- invoke
 * explicitly: `php artisan db:seed --class=FounderContentSeeder`.
 */
class FounderContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCompanySettings();
        $this->correctLegacyMigratedServices();
        $this->seedServicePillars();
        $this->correctDemoCaseStudies();
        $this->correctUnverifiedTestimonials();
        $this->seedFaqs();
        $this->seedSeoForRealPublishedContent();
    }

    /**
     * Safe, non-fabricated values only: the tagline/description come
     * verbatim from the founder-approved positioning direction supplied
     * with this sprint; the email matches the fallback already hardcoded
     * in the frontend legal pages (not a new invented fact). Phone,
     * WhatsApp, booking URL, and social links are left blank -- they are
     * not knowable from the repository and must be supplied by the
     * founder before publishing.
     */
    private function seedCompanySettings(): void
    {
        $settings = CompanySetting::current();
        $settings->fill([
            'company_name' => ['en' => 'Hexa Terminal', 'ar' => 'هيكسا تيرمينال'],
            'tagline' => [
                'en' => 'We build software systems that run real businesses.',
                'ar' => 'نبني أنظمة برمجية تُشغّل أعمالاً حقيقية.',
            ],
            'description' => [
                'en' => 'Hexa Terminal builds production software systems for real business operations: SaaS platforms, multi-tenant systems, CRM and ERP platforms, AI-enabled operational workflows, backend and API infrastructure, business automation, and industry-specific systems.',
                'ar' => 'تبني هيكسا تيرمينال أنظمة برمجية إنتاجية للعمليات التجارية الحقيقية: منصات SaaS، أنظمة متعددة المستأجرين، أنظمة CRM وERP، سير عمل تشغيلي مدعوم بالذكاء الاصطناعي، بنية تحتية خلفية وواجهات برمجية، أتمتة الأعمال، وأنظمة خاصة بالقطاعات.',
            ],
            'email' => $settings->email ?: 'hello@hexaterminal.com',
        ]);
        $settings->save();
    }

    /**
     * The 12 legacy-migrated `service_offerings` rows carry real Arabic
     * business copy, but it was mis-tagged under the "en" locale key,
     * has garbled unusable slugs, and is missing tagline/summary/
     * features/tech_stack/SEO entirely (see inventory doc). Rather than
     * fabricate the missing fields, unpublish them so they cannot appear
     * on the public site while still reviewable/fixable in Filament.
     */
    private function correctLegacyMigratedServices(): void
    {
        Service::query()
            ->whereNull('tagline')
            ->whereNull('summary')
            ->update(['status' => 'draft', 'is_published' => false]);
    }

    /**
     * Drafts the six service pillars named in the sprint's founder-
     * approved positioning direction. Left in `in_review` status --
     * never published automatically -- with descriptive, non-numeric
     * copy about what each pillar does; no specific client outcomes or
     * metrics are claimed anywhere in this content.
     */
    private function seedServicePillars(): void
    {
        $pillars = [
            [
                'slug' => 'saas-platforms',
                'name' => ['en' => 'SaaS Platforms', 'ar' => 'منصات SaaS'],
                'tagline' => [
                    'en' => 'Multi-tenant software products built to scale with your customers.',
                    'ar' => 'منتجات برمجية متعددة المستأجرين مبنية لتنمو مع عملائك.',
                ],
                'summary' => [
                    'en' => 'Design and engineering for subscription software products, from tenant isolation to billing to onboarding.',
                    'ar' => 'تصميم وهندسة منتجات برمجية بالاشتراك، من عزل المستأجرين إلى الفوترة والتهيئة.',
                ],
                'description' => [
                    'en' => "Buyer problem: teams need a product that supports many customers on shared infrastructure without one tenant's data or load affecting another.\nSolution approach: multi-tenant architecture with clear isolation boundaries, role-based access control, and a billing and onboarding flow built for self-serve or sales-assisted growth.\nDeliverables: a production-ready SaaS codebase, tenant management tooling, and an admin/ops surface for support and billing.",
                    'ar' => "مشكلة المشتري: تحتاج الفرق إلى منتج يدعم عملاء كثيرين على بنية تحتية مشتركة دون أن تؤثر بيانات أو حمل مستأجر واحد على آخر.\nنهج الحل: بنية متعددة المستأجرين بحدود عزل واضحة، وتحكم بالوصول حسب الأدوار، وتدفق فوترة وتهيئة يدعم النمو الذاتي أو المدعوم بالمبيعات.\nالمخرجات: قاعدة كود SaaS جاهزة للإنتاج، أدوات إدارة المستأجرين، وواجهة إدارية للدعم والفوترة.",
                ],
                'features' => ['Multi-tenant data isolation', 'Role-based access control', 'Subscription billing integration', 'Self-serve onboarding flows'],
                'tech_stack' => ['Laravel', 'PostgreSQL', 'Redis', 'Next.js'],
            ],
            [
                'slug' => 'crm-erp-systems',
                'name' => ['en' => 'CRM & ERP Systems', 'ar' => 'أنظمة CRM وERP'],
                'tagline' => [
                    'en' => 'Business-of-record systems for sales, operations, and resource planning.',
                    'ar' => 'أنظمة سجل الأعمال للمبيعات والعمليات وتخطيط الموارد.',
                ],
                'summary' => [
                    'en' => 'Custom CRM and ERP builds, or modernization of an existing one, tailored to how your business actually operates.',
                    'ar' => 'بناء أنظمة CRM وERP مخصصة، أو تحديث نظام قائم، بما يتناسب مع طريقة عمل مؤسستك الفعلية.',
                ],
                'description' => [
                    'en' => "Buyer problem: off-the-shelf CRM/ERP software forces a workflow that doesn't match how the business actually runs, or an existing legacy system has become too rigid to extend.\nSolution approach: a data model built around the business's real entities (leads, accounts, inventory, orders, resources) with the specific workflows and reports the team needs, plus integration with existing tools.\nDeliverables: a working CRM/ERP core, data migration from the legacy system where applicable, and role-based dashboards for each team.",
                    'ar' => "مشكلة المشتري: تفرض برامج CRM/ERP الجاهزة سير عمل لا يطابق طريقة عمل المؤسسة الفعلية، أو أصبح نظام قديم قائم جامداً للغاية بحيث يصعب توسيعه.\nنهج الحل: نموذج بيانات مبني حول كيانات العمل الحقيقية (العملاء المحتملون، الحسابات، المخزون، الطلبات، الموارد) مع سير العمل والتقارير التي يحتاجها الفريق، إضافة إلى التكامل مع الأدوات الحالية.\nالمخرجات: نواة CRM/ERP عاملة، ترحيل البيانات من النظام القديم عند الحاجة، ولوحات تحكم حسب دور كل فريق.",
                ],
                'features' => ['Custom data model per business entity', 'Legacy data migration', 'Role-based dashboards and reporting', 'Third-party tool integrations'],
                'tech_stack' => ['Laravel', 'MySQL', 'Filament'],
            ],
            [
                'slug' => 'ai-enabled-workflows',
                'name' => ['en' => 'AI-Enabled Workflows', 'ar' => 'سير عمل مدعوم بالذكاء الاصطناعي'],
                'tagline' => [
                    'en' => 'AI applied to a specific operational bottleneck, not bolted on for its own sake.',
                    'ar' => 'ذكاء اصطناعي يُطبَّق على عائق تشغيلي محدد، لا يُضاف لمجرد الإضافة.',
                ],
                'summary' => [
                    'en' => 'Adding AI-assisted steps to an existing workflow -- drafting, classification, extraction, review -- with a human in the loop.',
                    'ar' => 'إضافة خطوات مدعومة بالذكاء الاصطناعي إلى سير عمل قائم -- الصياغة، التصنيف، الاستخراج، المراجعة -- مع وجود إنسان في الحلقة.',
                ],
                'description' => [
                    'en' => "Buyer problem: a manual step in an existing process (drafting content, classifying tickets, extracting data from documents) is slow or repetitive, but the business can't accept unreviewed AI output going out the door.\nSolution approach: an AI-assisted step integrated into the existing workflow, with clear provenance, human approval before anything is finalized, and no automatic publishing or automatic decisions.\nDeliverables: the integrated workflow step, an approval interface, and logging of what the model suggested versus what a human approved.",
                    'ar' => "مشكلة المشتري: خطوة يدوية في عملية قائمة (صياغة محتوى، تصنيف تذاكر، استخراج بيانات من مستندات) بطيئة أو متكررة، لكن لا يمكن للمؤسسة قبول مخرجات ذكاء اصطناعي دون مراجعة.\nنهج الحل: خطوة مدعومة بالذكاء الاصطناعي مدمجة في سير العمل القائم، مع مصدر واضح للمخرجات، وموافقة بشرية قبل اعتماد أي شيء نهائياً، ودون نشر أو قرارات تلقائية.\nالمخرجات: خطوة سير العمل المدمجة، واجهة موافقة، وتسجيل لما اقترحه النموذج مقابل ما وافق عليه الإنسان.",
                ],
                'features' => ['Human-in-the-loop approval', 'Full provenance logging', 'No automatic publishing or decisions', 'Integrates into existing tools'],
                'tech_stack' => ['Laravel', 'Anthropic API'],
            ],
            [
                'slug' => 'backend-api-engineering',
                'name' => ['en' => 'Backend & API Engineering', 'ar' => 'هندسة الأنظمة الخلفية وواجهات برمجة التطبيقات'],
                'tagline' => [
                    'en' => 'The infrastructure other systems and teams build on top of.',
                    'ar' => 'البنية التحتية التي تُبنى عليها الأنظمة والفرق الأخرى.',
                ],
                'summary' => [
                    'en' => 'API design and backend engineering for systems that other applications, partners, or internal teams depend on.',
                    'ar' => 'تصميم واجهات برمجية وهندسة أنظمة خلفية للأنظمة التي تعتمد عليها تطبيقات أو شركاء أو فرق داخلية أخرى.',
                ],
                'description' => [
                    'en' => "Buyer problem: growing product surface area needs a stable, well-documented API layer instead of ad hoc endpoints added under deadline pressure.\nSolution approach: a versioned API with clear authentication, rate limiting, and documentation, backed by a data layer designed for the access patterns the business actually has.\nDeliverables: a versioned API, authentication and authorization, and documentation for internal or partner consumers.",
                    'ar' => "مشكلة المشتري: تحتاج مساحة المنتج المتنامية إلى طبقة واجهة برمجية مستقرة وموثقة بدلاً من نقاط نهاية مؤقتة تُضاف تحت ضغط المواعيد النهائية.\nنهج الحل: واجهة برمجية بإصدارات واضحة المصادقة والحد من المعدل والتوثيق، مدعومة بطبقة بيانات مصممة لأنماط الوصول الفعلية للمؤسسة.\nالمخرجات: واجهة برمجية بإصدارات، مصادقة وتفويض، وتوثيق للمستهلكين الداخليين أو الشركاء.",
                ],
                'features' => ['Versioned REST/JSON APIs', 'Authentication and rate limiting', 'API documentation', 'Data layer designed for real access patterns'],
                'tech_stack' => ['Laravel', 'PostgreSQL', 'Redis'],
            ],
            [
                'slug' => 'business-automation',
                'name' => ['en' => 'Business Automation', 'ar' => 'أتمتة الأعمال'],
                'tagline' => [
                    'en' => 'Removing manual steps from repeatable operational processes.',
                    'ar' => 'إزالة الخطوات اليدوية من العمليات التشغيلية المتكررة.',
                ],
                'summary' => [
                    'en' => 'Automating repeatable operational tasks -- reporting, notifications, data syncing between systems -- so teams spend less time on manual work.',
                    'ar' => 'أتمتة المهام التشغيلية المتكررة -- التقارير، الإشعارات، مزامنة البيانات بين الأنظمة -- ليقضي الفريق وقتاً أقل في العمل اليدوي.',
                ],
                'description' => [
                    'en' => "Buyer problem: staff spend recurring time on manual reporting, data entry between disconnected tools, or repetitive notification/follow-up tasks.\nSolution approach: scheduled jobs and event-driven automation that connect existing systems and remove the manual step, with monitoring so failures are visible rather than silent.\nDeliverables: the automated workflow, integration between the relevant systems, and monitoring/alerting on failures.",
                    'ar' => "مشكلة المشتري: يقضي الموظفون وقتاً متكرراً في إعداد التقارير يدوياً، أو إدخال البيانات بين أدوات غير متصلة، أو مهام إشعار ومتابعة متكررة.\nنهج الحل: مهام مجدولة وأتمتة قائمة على الأحداث تربط الأنظمة القائمة وتزيل الخطوة اليدوية، مع مراقبة لتكون الأعطال ظاهرة وليست صامتة.\nالمخرجات: سير العمل المؤتمت، التكامل بين الأنظمة المعنية، والمراقبة والتنبيه عند الأعطال.",
                ],
                'features' => ['Scheduled and event-driven jobs', 'Cross-system data sync', 'Failure monitoring and alerting'],
                'tech_stack' => ['Laravel', 'Redis queues'],
            ],
            [
                'slug' => 'custom-operational-software',
                'name' => ['en' => 'Custom Operational Software', 'ar' => 'برمجيات تشغيلية مخصصة'],
                'tagline' => [
                    'en' => 'Purpose-built software for a workflow no off-the-shelf tool fits.',
                    'ar' => 'برمجيات مصممة خصيصاً لسير عمل لا تناسبه أي أداة جاهزة.',
                ],
                'summary' => [
                    'en' => 'A system built specifically around one business\'s operations when no existing product fits the workflow.',
                    'ar' => 'نظام مبني خصيصاً حول عمليات مؤسسة واحدة عندما لا يناسب أي منتج قائم سير العمل.',
                ],
                'description' => [
                    'en' => "Buyer problem: the business's operations don't map onto any existing off-the-shelf product, so staff work around the gap with spreadsheets or disconnected tools.\nSolution approach: a scoped, purpose-built system designed directly around the real operational workflow, rather than a generic product configured to approximate it.\nDeliverables: a working system for the specific operation, built and delivered incrementally, with the same production standards (auth, data integrity, monitoring) as any other Hexa Terminal system.",
                    'ar' => "مشكلة المشتري: لا تتطابق عمليات المؤسسة مع أي منتج جاهز قائم، فيعمل الموظفون حول الفجوة باستخدام جداول بيانات أو أدوات غير متصلة.\nنهج الحل: نظام محدد النطاق ومبني خصيصاً حول سير العمل التشغيلي الفعلي، بدلاً من منتج عام يُهيَّأ لمحاكاته.\nالمخرجات: نظام عامل للعملية المحددة، يُبنى ويُسلَّم تدريجياً، بنفس معايير الإنتاج (المصادقة، سلامة البيانات، المراقبة) كأي نظام آخر من هيكسا تيرمينال.",
                ],
                'features' => ['Scoped, incremental delivery', 'Built around the real workflow, not a generic template', 'Production-grade auth, data integrity, and monitoring'],
                'tech_stack' => ['Laravel', 'PostgreSQL'],
            ],
        ];

        foreach ($pillars as $i => $pillar) {
            Service::updateOrCreate(
                ['slug' => $pillar['slug']],
                [
                    'name' => $pillar['name'],
                    'tagline' => $pillar['tagline'],
                    'summary' => $pillar['summary'],
                    'description' => $pillar['description'],
                    'features' => $pillar['features'],
                    'tech_stack' => $pillar['tech_stack'],
                    'status' => 'in_review',
                    'is_published' => false,
                    'sort_order' => $i + 1,
                ],
            );
        }
    }

    /**
     * The 8 legacy-migrated case studies are fictional demo SaaS products
     * (Smart Store, ProTask, SpeedEats, etc.) with placehold.co imagery,
     * no client name, and no outcomes -- they must not be shown publicly
     * as real client work (see inventory doc). Unpublish rather than
     * delete so the founder can review/reuse each one's structure.
     */
    private function correctDemoCaseStudies(): void
    {
        CaseStudy::query()
            ->whereNotNull('legacy_project_id')
            ->update(['status' => 'draft', 'is_published' => false]);
    }

    /**
     * The 5 migrated testimonials carry named individuals/companies with
     * no verifiable publication-permission record in the repository.
     * `is_approved=1` was copied from the legacy table's moderation flag
     * and is not proof of real-world consent -- revoke it until the
     * founder confirms each one may be published.
     */
    private function correctUnverifiedTestimonials(): void
    {
        Testimonial::query()
            ->whereNotNull('legacy_review_id')
            ->update(['is_approved' => false]);
    }

    /**
     * Real, useful, non-overcommitting answers to the questions listed
     * in the sprint brief. Left unpublished pending founder review --
     * these describe how the company and platform actually work
     * (editorial workflow, lead intake) rather than making commitments
     * about pricing or delivery timelines that would need sign-off.
     */
    private function seedFaqs(): void
    {
        $faqs = [
            [
                'q' => ['en' => 'What kinds of systems does Hexa Terminal build?', 'ar' => 'ما أنواع الأنظمة التي تبنيها هيكسا تيرمينال؟'],
                'a' => [
                    'en' => 'SaaS platforms, CRM and ERP systems, AI-enabled workflows, backend and API infrastructure, business automation, and custom operational software built around a specific business\'s real workflow.',
                    'ar' => 'منصات SaaS، أنظمة CRM وERP، سير عمل مدعوم بالذكاء الاصطناعي، بنية تحتية خلفية وواجهات برمجية، أتمتة الأعمال، وبرمجيات تشغيلية مخصصة مبنية حول سير العمل الفعلي لمؤسسة محددة.',
                ],
            ],
            [
                'q' => ['en' => 'Does Hexa Terminal build multi-tenant SaaS?', 'ar' => 'هل تبني هيكسا تيرمينال أنظمة SaaS متعددة المستأجرين؟'],
                'a' => [
                    'en' => 'Yes -- multi-tenant architecture with tenant data isolation and role-based access control is one of our core service pillars.',
                    'ar' => 'نعم -- البنية متعددة المستأجرين مع عزل بيانات المستأجرين والتحكم بالوصول حسب الأدوار هي أحد ركائز خدماتنا الأساسية.',
                ],
            ],
            [
                'q' => ['en' => 'Can you modernize an existing CRM or ERP?', 'ar' => 'هل يمكنكم تحديث نظام CRM أو ERP قائم؟'],
                'a' => [
                    'en' => 'Yes. We assess the existing system and data first, then plan a migration path -- often incremental -- rather than a single high-risk cutover.',
                    'ar' => 'نعم. نُقيّم النظام والبيانات القائمة أولاً، ثم نخطط مسار ترحيل -- غالباً تدريجي -- بدلاً من انتقال دفعة واحدة عالي المخاطر.',
                ],
            ],
            [
                'q' => ['en' => 'Can AI be added to an existing workflow?', 'ar' => 'هل يمكن إضافة الذكاء الاصطناعي إلى سير عمل قائم؟'],
                'a' => [
                    'en' => 'Yes, where it fits a specific bottleneck. We build AI-assisted steps with a human approving the output before it\'s used -- we do not add AI for its own sake or let it publish or decide unsupervised.',
                    'ar' => 'نعم، حيث يناسب عائقاً محدداً. نبني خطوات مدعومة بالذكاء الاصطناعي مع موافقة بشرية على المخرجات قبل استخدامها -- لا نضيف الذكاء الاصطناعي لمجرد الإضافة ولا نسمح له بالنشر أو اتخاذ القرار دون إشراف.',
                ],
            ],
            [
                'q' => ['en' => 'Do you work with GCC and EMEA companies?', 'ar' => 'هل تعملون مع شركات في دول الخليج ومنطقة الشرق الأوسط وأفريقيا وأوروبا؟'],
                'a' => [
                    'en' => 'Yes, and the platform is built bilingually (English/Arabic) from the ground up rather than translated as an afterthought.',
                    'ar' => 'نعم، والمنصة مبنية ثنائية اللغة (الإنجليزية والعربية) منذ التصميم الأساسي وليس كترجمة لاحقة.',
                ],
            ],
            [
                'q' => ['en' => 'How does a project begin?', 'ar' => 'كيف يبدأ المشروع؟'],
                'a' => [
                    'en' => 'With a discovery conversation to understand the actual workflow and constraints, followed by a scoped plan before any build work starts.',
                    'ar' => 'بمحادثة استكشافية لفهم سير العمل الفعلي والقيود، تليها خطة محددة النطاق قبل بدء أي عمل بناء.',
                ],
            ],
            [
                'q' => ['en' => 'Do you take over existing systems?', 'ar' => 'هل تتولون أنظمة قائمة؟'],
                'a' => [
                    'en' => 'Yes. We start with an assessment of the current codebase and data before committing to a plan, since the right approach depends heavily on what already exists.',
                    'ar' => 'نعم. نبدأ بتقييم قاعدة الكود والبيانات الحالية قبل الالتزام بخطة، لأن النهج الصحيح يعتمد بشكل كبير على ما هو قائم فعلاً.',
                ],
            ],
            [
                'q' => ['en' => 'What technologies do you use?', 'ar' => 'ما التقنيات التي تستخدمونها؟'],
                'a' => [
                    'en' => 'Primarily Laravel/PHP and PostgreSQL or MySQL on the backend, with Next.js/React on the frontend where a modern web client is needed. The right stack depends on the system being built.',
                    'ar' => 'بشكل أساسي Laravel/PHP وPostgreSQL أو MySQL في الخلفية، مع Next.js/React في الواجهة الأمامية عند الحاجة إلى تطبيق ويب حديث. تعتمد التقنية المناسبة على النظام قيد البناء.',
                ],
            ],
            [
                'q' => ['en' => 'Do you provide post-launch support?', 'ar' => 'هل تقدمون دعماً بعد الإطلاق؟'],
                'a' => [
                    'en' => 'Yes, support arrangements are scoped per project during the discovery conversation.',
                    'ar' => 'نعم، تُحدَّد ترتيبات الدعم لكل مشروع خلال المحادثة الاستكشافية.',
                ],
            ],
            [
                'q' => ['en' => 'How are project scope and pricing determined?', 'ar' => 'كيف يُحدَّد نطاق المشروع والتسعير؟'],
                'a' => [
                    'en' => 'Scope and pricing are determined after the discovery conversation, based on the specific system, integrations, and timeline involved -- we don\'t quote before understanding the actual requirements.',
                    'ar' => 'يُحدَّد النطاق والتسعير بعد المحادثة الاستكشافية، بناءً على النظام المحدد والتكاملات والجدول الزمني المعني -- لا نقدم عرض سعر قبل فهم المتطلبات الفعلية.',
                ],
            ],
        ];

        foreach ($faqs as $i => $faq) {
            FaqItem::updateOrCreate(
                ['category' => 'general', 'sort_order' => $i + 1],
                [
                    'question' => $faq['q'],
                    'answer' => $faq['a'],
                    'is_published' => false,
                ],
            );
        }
    }

    /**
     * SEO rows derived only from each record's own existing real fields
     * (name/tagline/summary), for content that is already real and
     * already published -- the 2 systems and 2 industries entered
     * directly in Filament. No new facts are introduced.
     */
    private function seedSeoForRealPublishedContent(): void
    {
        foreach (System::query()->where('is_published', true)->get() as $system) {
            $name = $system->getTranslation('name', 'en') ?: $system->getTranslation('name', 'ar');
            $tagline = $system->getTranslation('tagline', 'en') ?: $system->getTranslation('tagline', 'ar');

            SeoMeta::updateOrCreate(
                ['seoable_type' => System::class, 'seoable_id' => $system->id],
                [
                    'title' => ['en' => "{$name} | Hexa Terminal", 'ar' => "{$name} | هيكسا تيرمينال"],
                    'description' => ['en' => (string) $tagline, 'ar' => (string) $tagline],
                ],
            );
        }
    }
}
