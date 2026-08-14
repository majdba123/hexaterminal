<?php

namespace Database\Seeders;

use App\Models\CaseStudy;
use App\Models\Industry;
use App\Models\Service;
use App\Models\System;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

/**
 * Temporary, non-client preview content for local and explicitly approved
 * fresh-seed environments. All public copy is CMS data, ready to be replaced
 * through Admin when verified business content is available.
 */
class WebsitePreviewSeeder extends Seeder
{
    public function run(): void
    {
        $industries = $this->seedIndustries();
        $services = $this->services();
        $systems = $this->seedSystems($industries);
        $this->seedCaseStudies($services, $systems, $industries);
    }

    /** @return array<string, Industry> */
    private function seedIndustries(): array
    {
        return collect([
            'professional-services' => [
                'name' => ['en' => 'Professional Services', 'ar' => 'الخدمات المهنية'],
                'summary' => ['en' => 'Preview industry context for operational workflows and client delivery.', 'ar' => 'سياق قطاع معاينة لسير العمل التشغيلي وتسليم الخدمات للعملاء.'],
                'description' => ['en' => 'A preview industry record used to demonstrate how service businesses can connect delivery, clients, and internal operations.', 'ar' => 'سجل قطاع للمعاينة يوضح كيف يمكن لشركات الخدمات ربط التسليم والعملاء والعمليات الداخلية.'],
            ],
            'commerce-retail' => [
                'name' => ['en' => 'Commerce & Retail', 'ar' => 'التجارة والتجزئة'],
                'summary' => ['en' => 'Preview industry context for catalog, order, and fulfillment workflows.', 'ar' => 'سياق قطاع معاينة لتدفقات الكتالوج والطلبات والتنفيذ.'],
                'description' => ['en' => 'A preview industry record used to demonstrate connected commerce operations without representing a client deployment.', 'ar' => 'سجل قطاع للمعاينة يوضح عمليات التجارة المتصلة دون أن يمثل تنفيذًا لعميل.'],
            ],
            'field-operations' => [
                'name' => ['en' => 'Field Operations', 'ar' => 'العمليات الميدانية'],
                'summary' => ['en' => 'Preview industry context for distributed teams and mobile workflows.', 'ar' => 'سياق قطاع معاينة للفرق الموزعة وسير العمل عبر الجوال.'],
                'description' => ['en' => 'A preview industry record used to demonstrate scheduling, coordination, and mobile work patterns.', 'ar' => 'سجل قطاع للمعاينة يوضح أنماط الجدولة والتنسيق والعمل عبر الجوال.'],
            ],
        ])->mapWithKeys(function (array $attributes, string $slug): array {
            $industry = Industry::updateOrCreate(
                ['slug' => $slug],
                [...$attributes, 'is_published' => true, 'published_at' => now(), 'sort_order' => array_search($slug, ['professional-services', 'commerce-retail', 'field-operations'], true)],
            );
            $this->upsertSeo($industry, $attributes['name'], $attributes['summary']);

            return [$slug => $industry];
        })->all();
    }

    /** @return array<string, Service> */
    private function services(): array
    {
        $services = Service::query()
            ->whereIn('slug', Service::CORE_SERVICE_SLUGS)
            ->get()
            ->keyBy('slug');

        if ($services->count() !== count(Service::CORE_SERVICE_SLUGS)) {
            $this->call(ServicesSeeder::class);

            $services = Service::query()
                ->whereIn('slug', Service::CORE_SERVICE_SLUGS)
                ->get()
                ->keyBy('slug');
        }

        if ($services->count() !== count(Service::CORE_SERVICE_SLUGS)) {
            throw new \RuntimeException('The approved core services are required before preview case studies can be seeded.');
        }

        return $services->all();
    }

    /**
     * @param array<string, Industry> $industries
     * @return array<string, System>
     */
    private function seedSystems(array $industries): array
    {
        $systems = [
            'preview-operations-command-center' => [
                'type' => System::TYPE_BUSINESS_SYSTEM,
                'category' => 'Operations',
                'name' => ['en' => 'Preview Operations Command Center', 'ar' => 'مركز قيادة العمليات - معاينة'],
                'tagline' => ['en' => 'A concept workspace for coordinating delivery, client work, and internal approvals.', 'ar' => 'مساحة عمل تصورية لتنسيق التسليم وعمل العملاء والموافقات الداخلية.'],
                'short_description' => ['en' => 'Preview concept for a connected operational workspace.', 'ar' => 'نموذج معاينة لمساحة عمل تشغيلية مترابطة.'],
                'full_description' => ['en' => 'A non-client preview concept showing how a modular operations workspace can consolidate work queues, client context, and internal handoffs.', 'ar' => 'نموذج معاينة غير خاص بعميل يوضح كيف يمكن لمساحة عمل تشغيلية معيارية أن تجمع قوائم العمل وسياق العملاء والتسليمات الداخلية.'],
                'problem' => ['en' => 'Teams often coordinate delivery across disconnected tools and incomplete status views.', 'ar' => 'غالبًا ما تنسق الفرق التسليم عبر أدوات منفصلة ورؤى حالة غير مكتملة.'],
                'solution' => ['en' => 'A shared operational workspace with role-aware queues and connected records.', 'ar' => 'مساحة عمل تشغيلية مشتركة بقوائم حسب الدور وسجلات مترابطة.'],
                'features' => ['en' => 'Work queues\nClient timeline\nApproval routing', 'ar' => 'قوائم العمل\nالخط الزمني للعميل\nتوجيه الموافقات'],
                'business_outcomes' => ['en' => 'Preview scenario only; replace with verified outcomes before public use.', 'ar' => 'سيناريو معاينة فقط؛ يُستبدل بنتائج موثقة قبل الاستخدام العام.'],
                'target_audience' => ['en' => 'Operations and service delivery teams.', 'ar' => 'فرق العمليات وتسليم الخدمات.'],
                'tech_stack' => ['Laravel', 'Next.js', 'PostgreSQL'],
                'industry_slugs' => ['professional-services'],
            ],
            'preview-field-service-platform' => [
                'type' => System::TYPE_PLATFORM,
                'category' => 'Mobile operations',
                'name' => ['en' => 'Preview Field Service Platform', 'ar' => 'منصة الخدمات الميدانية - معاينة'],
                'tagline' => ['en' => 'A concept platform for teams moving between office and field work.', 'ar' => 'منصة تصورية للفرق التي تنتقل بين المكتب والعمل الميداني.'],
                'short_description' => ['en' => 'Preview concept for mobile-first scheduling and service delivery.', 'ar' => 'نموذج معاينة للجدولة وتسليم الخدمات بأسلوب الجوال أولاً.'],
                'full_description' => ['en' => 'A non-client preview concept for scheduling, assignment, status capture, and follow-up across a distributed service team.', 'ar' => 'نموذج معاينة غير خاص بعميل للجدولة والتكليف وتسجيل الحالة والمتابعة عبر فريق خدمات موزع.'],
                'problem' => ['en' => 'Field teams need current job information without relying on separate messaging threads.', 'ar' => 'تحتاج الفرق الميدانية إلى معلومات عمل حديثة دون الاعتماد على محادثات منفصلة.'],
                'solution' => ['en' => 'A shared platform with mobile task views and connected operational status.', 'ar' => 'منصة مشتركة بعروض مهام على الجوال وحالة تشغيلية مترابطة.'],
                'features' => ['en' => 'Scheduling board\nMobile task updates\nService history', 'ar' => 'لوحة جدولة\nتحديثات المهام عبر الجوال\nسجل الخدمة'],
                'business_outcomes' => ['en' => 'Preview scenario only; replace with verified outcomes before public use.', 'ar' => 'سيناريو معاينة فقط؛ يُستبدل بنتائج موثقة قبل الاستخدام العام.'],
                'target_audience' => ['en' => 'Distributed service and field operations teams.', 'ar' => 'فرق الخدمات والعمليات الميدانية الموزعة.'],
                'tech_stack' => ['React Native', 'Laravel', 'REST APIs'],
                'industry_slugs' => ['field-operations', 'professional-services'],
            ],
            'preview-commerce-operations-hub' => [
                'type' => System::TYPE_SAAS_PRODUCT,
                'category' => 'Commerce',
                'name' => ['en' => 'Preview Commerce Operations Hub', 'ar' => 'مركز عمليات التجارة - معاينة'],
                'tagline' => ['en' => 'A concept workspace connecting catalog, order, and fulfillment activity.', 'ar' => 'مساحة عمل تصورية تربط الكتالوج والطلبات وأنشطة التنفيذ.'],
                'short_description' => ['en' => 'Preview concept for connected commerce operations.', 'ar' => 'نموذج معاينة لعمليات تجارة مترابطة.'],
                'full_description' => ['en' => 'A non-client preview concept for the operational layer behind a commerce experience, from catalog administration through order handling.', 'ar' => 'نموذج معاينة غير خاص بعميل للطبقة التشغيلية خلف تجربة التجارة، من إدارة الكتالوج حتى معالجة الطلبات.'],
                'problem' => ['en' => 'Catalog and order data can become fragmented between storefront and internal operations.', 'ar' => 'قد تصبح بيانات الكتالوج والطلبات متفرقة بين واجهة المتجر والعمليات الداخلية.'],
                'solution' => ['en' => 'A connected workspace for product data, order handling, and fulfillment visibility.', 'ar' => 'مساحة عمل مترابطة لبيانات المنتجات ومعالجة الطلبات ووضوح التنفيذ.'],
                'features' => ['en' => 'Catalog workspace\nOrder queue\nFulfillment status', 'ar' => 'مساحة عمل للكتالوج\nقائمة الطلبات\nحالة التنفيذ'],
                'business_outcomes' => ['en' => 'Preview scenario only; replace with verified outcomes before public use.', 'ar' => 'سيناريو معاينة فقط؛ يُستبدل بنتائج موثقة قبل الاستخدام العام.'],
                'target_audience' => ['en' => 'Commerce operations and catalog teams.', 'ar' => 'فرق عمليات التجارة والكتالوج.'],
                'tech_stack' => ['Next.js', 'Laravel', 'PostgreSQL'],
                'industry_slugs' => ['commerce-retail'],
            ],
        ];

        $systemSlugs = array_keys($systems);

        return collect($systems)->mapWithKeys(function (array $attributes, string $slug) use ($industries, $systemSlugs): array {
            $industrySlugs = $attributes['industry_slugs'];
            unset($attributes['industry_slugs']);
            $system = System::updateOrCreate(
                ['slug' => $slug],
                [...$attributes, 'is_featured' => true, 'is_published' => true, 'published_at' => now(), 'sort_order' => array_search($slug, $systemSlugs, true)],
            );
            $system->industries()->sync(collect($industrySlugs)->map(fn (string $slug) => $industries[$slug]->id));
            $this->upsertSeo($system, $attributes['name'], $attributes['short_description']);

            return [$slug => $system];
        })->all();
    }

    /**
     * @param array<string, Service> $services
     * @param array<string, System> $systems
     * @param array<string, Industry> $industries
     */
    private function seedCaseStudies(array $services, array $systems, array $industries): void
    {
        $caseStudies = [
            'preview-operations-workspace' => [
                'title' => ['en' => 'Preview: Multi-branch Operations Workspace', 'ar' => 'معاينة: مساحة عمل لعمليات متعددة الفروع'],
                'summary' => ['en' => 'A non-client preview scenario for a connected ERP and CRM workspace.', 'ar' => 'سيناريو معاينة غير خاص بعميل لمساحة عمل ERP وCRM مترابطة.'],
                'context' => ['en' => 'Preview scenario for a growing service organization coordinating work across multiple branches.', 'ar' => 'سيناريو معاينة لمؤسسة خدمات نامية تنسق العمل عبر عدة فروع.'],
                'problem' => ['en' => 'Customer history, delivery work, and approvals are handled in separate tools.', 'ar' => 'يتم التعامل مع سجل العملاء وأعمال التسليم والموافقات عبر أدوات منفصلة.'],
                'constraints' => ['en' => 'The concept must support role-based access and incremental rollout.', 'ar' => 'يجب أن يدعم النموذج الوصول حسب الدور والإطلاق التدريجي.'],
                'solution' => ['en' => 'A connected workspace for customer context, work queues, and approval flows.', 'ar' => 'مساحة عمل مترابطة لسياق العملاء وقوائم العمل وتدفقات الموافقة.'],
                'architecture' => ['en' => 'Laravel API, Next.js operations interface, and relational data model.', 'ar' => 'واجهة Laravel API وواجهة عمليات Next.js ونموذج بيانات علائقي.'],
                'outcomes' => ['en' => 'Preview concept only. Replace with verified outcomes before presenting this as client work.', 'ar' => 'نموذج معاينة فقط. يُستبدل بنتائج موثقة قبل تقديمه كعمل لعميل.'],
                'evidence' => ['en' => 'Preview scenario; no external customer evidence or performance claims.', 'ar' => 'سيناريو معاينة؛ لا توجد أدلة لعميل خارجي أو ادعاءات أداء.'],
                'features' => ['en' => 'Customer records\nOperations queue\nApproval workflows', 'ar' => 'سجلات العملاء\nقائمة العمليات\nتدفقات الموافقة'],
                'project_classification' => CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM,
                'service_slug' => 'custom-erp-crm-systems',
                'system_slug' => 'preview-operations-command-center',
                'industry_slugs' => ['professional-services'],
            ],
            'preview-field-service-companion' => [
                'title' => ['en' => 'Preview: Field Service Companion', 'ar' => 'معاينة: رفيق الخدمات الميدانية'],
                'summary' => ['en' => 'A non-client preview scenario for a web platform and mobile field workflow.', 'ar' => 'سيناريو معاينة غير خاص بعميل لمنصة ويب وسير عمل ميداني عبر الجوال.'],
                'context' => ['en' => 'Preview scenario for a service team that moves between scheduled work and on-site updates.', 'ar' => 'سيناريو معاينة لفريق خدمات ينتقل بين العمل المجدول والتحديثات في الموقع.'],
                'problem' => ['en' => 'Job details and updates are split across calls, messages, and disconnected records.', 'ar' => 'تفاصيل المهام والتحديثات موزعة بين المكالمات والرسائل والسجلات غير المترابطة.'],
                'constraints' => ['en' => 'The experience must work for both dispatchers and mobile team members.', 'ar' => 'يجب أن تعمل التجربة للمنسقين وأعضاء الفريق عبر الجوال.'],
                'solution' => ['en' => 'A shared web and mobile workflow for assignments, status, and service history.', 'ar' => 'سير عمل مشترك عبر الويب والجوال للتكليفات والحالة وسجل الخدمة.'],
                'architecture' => ['en' => 'Mobile client, web operations console, and API integration layer.', 'ar' => 'عميل جوال ووحدة عمليات ويب وطبقة تكامل API.'],
                'outcomes' => ['en' => 'Preview concept only. Replace with verified outcomes before presenting this as client work.', 'ar' => 'نموذج معاينة فقط. يُستبدل بنتائج موثقة قبل تقديمه كعمل لعميل.'],
                'evidence' => ['en' => 'Preview scenario; no external customer evidence or performance claims.', 'ar' => 'سيناريو معاينة؛ لا توجد أدلة لعميل خارجي أو ادعاءات أداء.'],
                'features' => ['en' => 'Scheduling\nMobile updates\nService timeline', 'ar' => 'الجدولة\nتحديثات الجوال\nالخط الزمني للخدمة'],
                'project_classification' => CaseStudy::CLASSIFICATION_WEB_MOBILE_PLATFORM,
                'service_slug' => 'web-platforms-mobile-applications',
                'system_slug' => 'preview-field-service-platform',
                'industry_slugs' => ['field-operations'],
            ],
            'preview-commerce-workspace' => [
                'title' => ['en' => 'Preview: Commerce Operations Workspace', 'ar' => 'معاينة: مساحة عمل لعمليات التجارة'],
                'summary' => ['en' => 'A non-client preview scenario for connected e-commerce operations.', 'ar' => 'سيناريو معاينة غير خاص بعميل لعمليات تجارة إلكترونية مترابطة.'],
                'context' => ['en' => 'Preview scenario for a commerce team managing product content and order handling.', 'ar' => 'سيناريو معاينة لفريق تجارة يدير محتوى المنتجات ومعالجة الطلبات.'],
                'problem' => ['en' => 'Catalog administration and order handling are disconnected from the public storefront.', 'ar' => 'إدارة الكتالوج ومعالجة الطلبات منفصلتان عن واجهة المتجر العامة.'],
                'constraints' => ['en' => 'The concept must keep public content clear while supporting internal order workflows.', 'ar' => 'يجب أن يحافظ النموذج على وضوح المحتوى العام مع دعم تدفقات الطلبات الداخلية.'],
                'solution' => ['en' => 'A connected commerce workspace that brings catalog, order, and fulfillment activity together.', 'ar' => 'مساحة عمل تجارية مترابطة تجمع الكتالوج والطلبات وأنشطة التنفيذ.'],
                'architecture' => ['en' => 'Next.js storefront, Laravel operations API, and shared product data.', 'ar' => 'واجهة متجر Next.js وLaravel API للعمليات وبيانات منتجات مشتركة.'],
                'outcomes' => ['en' => 'Preview concept only. Replace with verified outcomes before presenting this as client work.', 'ar' => 'نموذج معاينة فقط. يُستبدل بنتائج موثقة قبل تقديمه كعمل لعميل.'],
                'evidence' => ['en' => 'Preview scenario; no external customer evidence or performance claims.', 'ar' => 'سيناريو معاينة؛ لا توجد أدلة لعميل خارجي أو ادعاءات أداء.'],
                'features' => ['en' => 'Catalog workspace\nOrder handling\nFulfillment visibility', 'ar' => 'مساحة الكتالوج\nمعالجة الطلبات\nوضوح التنفيذ'],
                'project_classification' => CaseStudy::CLASSIFICATION_ECOMMERCE_BUSINESS_WEBSITE,
                'service_slug' => 'ecommerce-business-websites',
                'system_slug' => 'preview-commerce-operations-hub',
                'industry_slugs' => ['commerce-retail'],
            ],
        ];

        foreach ($caseStudies as $position => $attributes) {
            $serviceSlug = $attributes['service_slug'];
            $systemSlug = $attributes['system_slug'];
            $industrySlugs = $attributes['industry_slugs'];
            unset($attributes['service_slug'], $attributes['system_slug'], $attributes['industry_slugs']);

            $caseStudy = CaseStudy::updateOrCreate(
                ['slug' => $position],
                [...$attributes, 'service_offering_id' => $services[$serviceSlug]->id, 'system_id' => $systems[$systemSlug]->id, 'is_featured' => true, 'is_published' => true, 'published_at' => now(), 'sort_order' => array_search($position, array_keys($caseStudies), true)],
            );
            $caseStudy->industries()->sync(collect($industrySlugs)->map(fn (string $slug) => $industries[$slug]->id));
            $this->upsertSeo($caseStudy, $attributes['title'], $attributes['summary'], true);
        }
    }

    /**
     * @param array<string, string> $title
     * @param array<string, string> $description
     */
    private function upsertSeo(Model $model, array $title, array $description, bool $noindex = false): void
    {
        $model->seo()->updateOrCreate([], [
            'title' => $title,
            'description' => $description,
            'noindex' => $noindex,
            'nofollow' => false,
        ]);
    }
}
