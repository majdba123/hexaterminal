<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\CaseStudy;
use App\Models\System;
use App\Services\SystemSeedImageSynchronizer;
use Illuminate\Database\Seeder;

class RakezContentSeeder extends Seeder
{
    public function run(): void
    {
        $cover = app(SystemSeedImageSynchronizer::class)->sync('systems/rakez-cover.webp');
        $system = $this->seedSystem($cover);
        $this->seedCaseStudies($system, $cover);
        $this->seedArticles($cover);
    }

    private function seedSystem(string $cover): System
    {
        $system = System::firstOrNew(['slug' => 'rakez-erp']);
        $system->fill([
            'type' => System::TYPE_BUSINESS_SYSTEM,
            'category' => 'Real Estate ERP & Operations',
            'name' => ['en' => 'Rakez ERP', 'ar' => 'راكز ERP'],
            'tagline' => [
                'en' => 'A real-estate operating platform connecting CRM, inventory, contracts, finance, marketing, HR, and guarded AI.',
                'ar' => 'منصة تشغيل عقارية تربط CRM والمخزون والعقود والمالية والتسويق والموارد البشرية والذكاء الاصطناعي المنضبط.',
            ],
            'short_description' => [
                'en' => 'Rakez is a multi-domain ERP/CRM built for workflows where one commercial event can affect sales, unit inventory, contracts, deposits, commissions, claims, marketing attribution, notifications, and management reporting.',
                'ar' => 'راكز نظام ERP/CRM متعدد المجالات صُمم لسير عمل قد يؤثر فيه حدث تجاري واحد على المبيعات ومخزون الوحدات والعقود والعربون والعمولات والمطالبات والإسناد التسويقي والإشعارات وتقارير الإدارة.',
            ],
            'full_description' => [
                'en' => 'Rakez treats business operations as connected state transitions rather than isolated CRUD screens. The system coordinates CRM and real-estate inventory with reservations, contracts, payment and commission workflows, credit and claims, marketing attribution, HR, permissions, notifications, integrations, reporting, and AI-assisted tools that remain behind application authorization and confirmation boundaries.',
                'ar' => 'يتعامل راكز مع العمليات كحالات مترابطة وليست شاشات CRUD منفصلة. ينسق النظام CRM والمخزون العقاري مع الحجوزات والعقود والدفعات والعمولات والائتمان والمطالبات والإسناد التسويقي والموارد البشرية والصلاحيات والإشعارات والتكاملات والتقارير وأدوات AI التي تبقى خلف صلاحيات التطبيق وحدود التأكيد.',
            ],
            'problem' => [
                'en' => 'Real-estate operations become fragile when leads, availability, reservations, contracts, money, commissions, campaigns, and approvals are split across spreadsheets and disconnected tools.',
                'ar' => 'تصبح العمليات العقارية هشة عندما تتوزع العملاء والوحدات والحجوزات والعقود والأموال والعمولات والحملات والموافقات بين جداول وأدوات منفصلة.',
            ],
            'solution' => [
                'en' => 'A role-aware operational backbone keeps commercial, financial, marketing, and support workflows on shared business rules, explicit state transitions, traceable history, and bounded integrations.',
                'ar' => 'عمود تشغيلي موحد يربط المسارات التجارية والمالية والتسويقية والداعمة بقواعد أعمال مشتركة وحالات واضحة وتاريخ قابل للتتبع وتكاملات منضبطة.',
            ],
            'features' => [
                'en' => "CRM and lead operations\nProjects, properties and unit inventory\nReservations and negotiation states\nContracts, deposits and payment plans\nCommissions, rewards and claims\nCredit and title-transfer workflows\nMarketing attribution and provider synchronization\nHR, tasks and notifications\nGranular authorization and audit boundaries\nGuarded AI tools and RAG-assisted workflows",
                'ar' => "CRM وإدارة العملاء\nالمشاريع والعقارات ومخزون الوحدات\nالحجوزات وحالات التفاوض\nالعقود والعربون وخطط الدفع\nالعمولات والمكافآت والمطالبات\nالائتمان ونقل الملكية\nالإسناد التسويقي ومزامنة المزودات\nالموارد البشرية والمهام والإشعارات\nصلاحيات دقيقة وحدود تدقيق\nأدوات AI منضبطة ومسارات RAG",
            ],
            'business_outcomes' => [
                'en' => "One operational source of truth across departments\nTraceable commercial and financial state changes\nSafer marketing attribution without guessing missing mappings\nAI assistance that inherits application policy instead of bypassing it",
                'ar' => "مصدر تشغيلي موحد بين الأقسام\nتغييرات تجارية ومالية قابلة للتتبع\nإسناد تسويقي أكثر أماناً دون اختلاق روابط ناقصة\nمساعدة AI ترث سياسات التطبيق ولا تتجاوزها",
            ],
            'target_audience' => [
                'en' => 'Real-estate developers, brokerages, sales organizations, and multi-department companies that need a custom operational ERP rather than another disconnected tool.',
                'ar' => 'المطورون العقاريون وشركات الوساطة وفرق المبيعات والشركات متعددة الأقسام التي تحتاج ERP تشغيلياً مخصصاً بدلاً من أداة إضافية منفصلة.',
            ],
            'tech_stack' => ['Laravel 12', 'PHP', 'REST APIs', 'Sanctum', 'Redis', 'Reverb', 'Spatie Permission', 'MySQL', 'AI / RAG'],
            'cover_image' => $cover,
            'cover_image_alt' => ['en' => 'Rakez ERP business operations system map.', 'ar' => 'خريطة نظام راكز ERP للعمليات التجارية.'],
            'gallery' => [],
            'demo_url' => null,
            'live_url' => null,
            'is_featured' => true,
            'is_published' => true,
            'sort_order' => 2,
        ]);
        $system->published_at ??= now();
        $system->save();

        return $system;
    }

    private function seedCaseStudies(System $system, string $cover): void
    {
        $studies = [
            [
                'slug' => 'rakez-erp-operating-platform',
                'title' => ['en' => 'Rakez ERP: Engineering an AI-Enabled Real-Estate Operating Platform', 'ar' => 'راكز ERP: هندسة منصة تشغيل عقارية مدعومة بالذكاء الاصطناعي'],
                'summary' => ['en' => 'How a production-style ERP stays coherent when sales, inventory, reservations, contracts, finance, credit, marketing attribution, permissions, and AI-assisted workflows share the same business state.', 'ar' => 'كيف يحافظ ERP تشغيلي على اتساقه عندما تشترك المبيعات والمخزون والحجوزات والعقود والمالية والائتمان والإسناد التسويقي والصلاحيات وAI في نفس حالة العمل.'],
                'context' => ['en' => 'Rakez spans a commercial core, financial control, and operational support. Leads become opportunities, units move through reservation and sale states, approved contracts affect commissions and claims, and synchronized provider data affects CRM and marketing analytics.', 'ar' => 'يغطي راكز النواة التجارية والضبط المالي والدعم التشغيلي. تتحول العملاء إلى فرص، وتتحرك الوحدات عبر الحجز والبيع، وتؤثر العقود المعتمدة في العمولات والمطالبات، وتغذي بيانات المزودات CRM والتحليلات التسويقية.'],
                'problem' => ['en' => 'The difficult part was not creating more modules; it was keeping downstream domains consistent when one event crosses several of them.', 'ar' => 'التحدي لم يكن إضافة المزيد من الموديولات، بل إبقاء المجالات اللاحقة متسقة عندما يعبر حدث واحد عدة أقسام من النظام.'],
                'constraints' => ['en' => 'Atomic state checks, duplicate prevention, authorization, historical financial correctness, provider account scoping, idempotent synchronization, and AI boundaries all had to coexist.', 'ar' => 'كان يجب الجمع بين فحوص الحالة الذرية ومنع التكرار والصلاحيات وصحة التاريخ المالي ونطاق حسابات المزود والمزامنة idempotent وحدود AI.'],
                'solution' => ['en' => 'Model the operation as explicit state transitions with shared domain rules. Keep permissions and data scope in the backend, preserve historical snapshots where policy can change, normalize external provider evidence before attribution, and let AI assist only through registered application tools.', 'ar' => 'تم نمذجة التشغيل كحالات واضحة بقواعد مشتركة، مع إبقاء الصلاحيات ونطاق البيانات في الـbackend، وحفظ snapshots تاريخية للسياسات المتغيرة، وتطبيع أدلة مزودي الإعلانات قبل الإسناد، والسماح لـAI بالمساعدة فقط عبر أدوات التطبيق المسجلة.'],
                'architecture' => ['en' => 'Laravel business application with domain services, REST APIs, granular RBAC, transactional workflows, Redis/realtime support, provider adapters, reporting, and guarded AI orchestration.', 'ar' => 'تطبيق أعمال Laravel مع domain services وREST APIs وRBAC دقيق ومعاملات تشغيلية وRedis/realtime وموصلات مزودات وتقارير وAI orchestration منضبط.'],
                'outcomes' => ['en' => 'The system provides a single operational model across commercial, financial, marketing, and support workflows, with automated regression coverage around critical business rules.', 'ar' => 'يوفر النظام نموذج تشغيل موحداً بين المسارات التجارية والمالية والتسويقية والداعمة مع اختبارات انحدار آلية لقواعد العمل الحساسة.'],
                'evidence' => ['en' => 'The public-safe case-study package documents the cross-domain map and dependencies. Repository verification material also records a large automated test suite and granular permission model; numerical snapshots are treated as inspected-code evidence rather than business-performance claims.', 'ar' => 'توثق حزمة دراسة الحالة العامة خريطة المجالات والاعتماديات بينها. كما تسجل مواد التحقق في المستودع مجموعة اختبارات آلية كبيرة ونموذج صلاحيات دقيق، مع اعتبار الأرقام snapshots للكود المفحوص وليست ادعاءات أداء تجاري.'],
                'features' => ['en' => "Commercial core\nFinancial control\nOperational support\nProvider synchronization\nAuthorization boundaries\nGuarded AI", 'ar' => "النواة التجارية\nالضبط المالي\nالدعم التشغيلي\nمزامنة المزودات\nحدود الصلاحيات\nAI منضبط"],
                'sort_order' => 1,
            ],
            [
                'slug' => 'rakez-ads-attribution',
                'title' => ['en' => 'Rakez Ads Attribution: The Campaign ID Was Not Enough', 'ar' => 'الإسناد الإعلاني في راكز: معرّف الحملة وحده لا يكفي'],
                'summary' => ['en' => 'Why external campaign identifiers are treated as evidence, not truth, before spend, leads, insights, or writable actions are mapped to an internal project.', 'ar' => 'لماذا تُعامل معرّفات الحملات الخارجية كدليل لا كحقيقة قبل ربط الإنفاق والعملاء والتحليلات والإجراءات القابلة للكتابة بمشروع داخلي.'],
                'context' => ['en' => 'Ad-provider data has value only when it can be mapped back to the correct internal business entity.', 'ar' => 'لا تصبح بيانات مزود الإعلان مفيدة إلا عندما يمكن ربطها بشكل موثوق بالكيان التجاري الداخلي الصحيح.'],
                'problem' => ['en' => 'Browser-trusted or fuzzy mapping can put spend, leads, and ROAS against the wrong project.', 'ar' => 'قد يؤدي الاعتماد على قيم المتصفح أو المطابقة التقريبية إلى نسب الإنفاق والعملاء وROAS إلى المشروع الخطأ.'],
                'constraints' => ['en' => 'Provider identifiers can be incomplete or ambiguous, and a missing mapping must not be converted into invented certainty.', 'ar' => 'قد تكون معرّفات المزود ناقصة أو ملتبسة، ويجب ألا تتحول المطابقة المفقودة إلى يقين مصطنع.'],
                'solution' => ['en' => 'Use a server-side chain: provider account -> synchronized campaign -> verified mapping -> internal project code. Preserve unattributed as a legitimate state and block writable campaign actions until mapping is verified.', 'ar' => 'استخدام سلسلة server-side: حساب المزود ← حملة متزامنة ← مطابقة موثقة ← كود المشروع الداخلي. تبقى حالة Unattributed مشروعة، وتُمنع الإجراءات القابلة للكتابة حتى توثيق المطابقة.'],
                'architecture' => ['en' => 'Provider adapters normalize campaigns, spend, leads, and insights into internal records. Verification gates connect those records to project-level reporting and operational actions.', 'ar' => 'تعمل موصلات المزودات على توحيد الحملات والإنفاق والعملاء والتحليلات في سجلات داخلية، ثم تربط بوابات التحقق هذه السجلات بتقارير المشروع والإجراءات التشغيلية.'],
                'outcomes' => ['en' => 'Marketing reporting can distinguish verified attribution from honest gaps instead of silently guessing.', 'ar' => 'تستطيع التقارير التسويقية التفريق بين الإسناد الموثق والفجوات الحقيقية بدلاً من التخمين الصامت.'],
                'evidence' => ['en' => 'The publication-ready Ads & Integration carousel documents the verified mapping chain, unattributed state, normalized data families, and write gate.', 'ar' => 'يوثق Carousel Ads & Integration الجاهز للنشر سلسلة المطابقة الموثقة وحالة Unattributed وعائلات البيانات الموحدة وبوابة الكتابة.'],
                'features' => ['en' => "Verified mapping chain\nUnattributed state\nSpend normalization\nLead normalization\nProject-level insights\nWrite gates", 'ar' => "سلسلة مطابقة موثقة\nحالة Unattributed\nتوحيد الإنفاق\nتوحيد العملاء\nتحليلات على مستوى المشروع\nبوابات كتابة"],
                'sort_order' => 2,
            ],
            [
                'slug' => 'rakez-reservation-sales-credit-flow',
                'title' => ['en' => 'Rakez Sales & Credit: A Reservation Can Affect Half the ERP', 'ar' => 'المبيعات والائتمان في راكز: الحجز الواحد قد يؤثر على نصف الـERP'],
                'summary' => ['en' => 'A reservation is modeled as a cross-domain transaction spanning availability, negotiation, snapshots, deposits, credit, title transfer, commissions, claims, dashboards, and notifications.', 'ar' => 'يُعامل الحجز كمعاملة عابرة للمجالات تشمل التوفر والتفاوض والـsnapshots والعربون والائتمان ونقل الملكية والعمولات والمطالبات واللوحات والإشعارات.'],
                'context' => ['en' => 'Unit availability and customer terms can change while multiple operators act concurrently.', 'ar' => 'يمكن أن تتغير حالة الوحدة وشروط العميل بينما يعمل عدة مستخدمين بالتوازي.'],
                'problem' => ['en' => 'Treating reservation as one database insert creates race conditions and inconsistent downstream states.', 'ar' => 'اعتبار الحجز مجرد insert واحد يخلق race conditions وحالات غير متسقة في بقية النظام.'],
                'constraints' => ['en' => 'Prevent double reservation, preserve the business context at the time of the deal, and support distinct negotiation/off-plan paths.', 'ar' => 'منع الحجز المزدوج وحفظ سياق العمل وقت الصفقة ودعم مسارات تفاوض وoff-plan مختلفة.'],
                'solution' => ['en' => 'Row-lock the unit, validate state, branch through explicit transitions, snapshot project/unit/customer/payment terms, and let downstream financial and notification workflows consume the same accepted state.', 'ar' => 'قفل صف الوحدة والتحقق من حالتها والمرور عبر transitions واضحة وحفظ snapshots للمشروع والوحدة والعميل وشروط الدفع، ثم جعل المسارات المالية والإشعارات تعتمد الحالة نفسها.'],
                'architecture' => ['en' => 'Reserve -> negotiate/confirm -> deposit -> credit -> title transfer -> sold, with downstream commission, claims, reporting, and notifications.', 'ar' => 'حجز ← تفاوض/تأكيد ← عربون ← ائتمان ← نقل ملكية ← مباع، مع عمولات ومطالبات وتقارير وإشعارات لاحقة.'],
                'outcomes' => ['en' => 'The transaction has one authoritative state progression that other domains can trust.', 'ar' => 'تملك المعاملة تسلسلاً واحداً موثوقاً للحالة يمكن لبقية المجالات الاعتماد عليه.'],
                'evidence' => ['en' => 'The Sales & Credit carousel documents row locking, state branching, snapshots, deposits, title transfer, and downstream dependencies.', 'ar' => 'يوثق Carousel Sales & Credit قفل الصفوف وتفرعات الحالة والـsnapshots والعربون ونقل الملكية والاعتماديات اللاحقة.'],
                'features' => ['en' => "Availability locking\nNegotiation state machine\nBusiness snapshots\nDeposit/installment rules\nCredit stages\nTitle transfer\nDownstream finance", 'ar' => "قفل التوفر\nState machine للتفاوض\nSnapshots تجارية\nقواعد العربون والأقساط\nمراحل الائتمان\nنقل الملكية\nالمالية اللاحقة"],
                'sort_order' => 3,
            ],
            [
                'slug' => 'rakez-financial-workflows',
                'title' => ['en' => "Rakez Financial Workflows: Today's Settings Should Not Rewrite Yesterday's Money", 'ar' => 'المسارات المالية في راكز: إعدادات اليوم يجب ألا تعيد كتابة أموال الأمس'],
                'summary' => ['en' => 'Commission and reward logic is modeled as versioned, auditable distribution with explicit participants, unresolved allocation, approval lifecycle, row locks, and duplicate prevention.', 'ar' => 'تُنمذج العمولات والمكافآت كتوزيع versioned وقابل للتدقيق مع مشاركين واضحين وتوزيع غير محلول ودورة موافقة وrow locks ومنع التكرار.'],
                'context' => ['en' => 'A real sale can distribute value across multiple participants, roles, weights, management shares, policies, claims, and later disputes.', 'ar' => 'قد توزع الصفقة الواحدة القيمة بين عدة مشاركين وأدوار وأوزان وحصص إدارية وسياسات ومطالبات ونزاعات لاحقة.'],
                'problem' => ['en' => 'A single percentage field cannot explain who was owed what, under which policy, or why a historical amount changed.', 'ar' => 'حقل نسبة واحد لا يشرح لمن استحقت الأموال وتحت أي سياسة ولماذا تغير رقم تاريخي.'],
                'constraints' => ['en' => 'Policy changes must not retroactively rewrite accepted calculations, concurrent triggers must not duplicate payouts, and unresolved money must remain visible.', 'ar' => 'يجب ألا تعيد تغييرات السياسة كتابة الحسابات السابقة، وألا تنتج triggers المتزامنة دفعات مكررة، وأن يبقى أي مبلغ غير موزع ظاهراً.'],
                'solution' => ['en' => 'Persist participants and policy/version snapshots, preserve unresolved buckets, use explicit approval/payment states, and enforce transaction/locking invariants in the database.', 'ar' => 'حفظ المشاركين وsnapshots للسياسات والإصدارات والإبقاء على الحصص غير المحلولة واستخدام حالات موافقة/دفع واضحة وفرض invariants المعاملات والقفل في قاعدة البيانات.'],
                'architecture' => ['en' => 'Versioned calculation context + participant allocation + pending/approved/payable/paid lifecycle + claims and compensation history.', 'ar' => 'سياق حساب versioned + توزيع المشاركين + دورة pending/approved/payable/paid + تاريخ المطالبات والتعويضات.'],
                'outcomes' => ['en' => 'Financial state remains explainable and reconstructable even when policy evolves.', 'ar' => 'تبقى الحالة المالية قابلة للتفسير وإعادة البناء حتى مع تغير السياسات.'],
                'evidence' => ['en' => 'The Financial Workflows carousel documents policy/version snapshots, unresolved allocation, approval states, row locks, duplicate prevention, and claims history.', 'ar' => 'يوثق Carousel Financial Workflows snapshots السياسات والإصدارات والتوزيع غير المحلول وحالات الموافقة وrow locks ومنع التكرار وتاريخ المطالبات.'],
                'features' => ['en' => "Participant weights\nManagement shares\nUnresolved allocation\nPolicy snapshots\nApproval lifecycle\nRow locks\nClaims history", 'ar' => "أوزان المشاركين\nحصص الإدارة\nتوزيع غير محلول\nSnapshots للسياسات\nدورة موافقة\nRow locks\nتاريخ المطالبات"],
                'sort_order' => 4,
            ],
            [
                'slug' => 'rakez-operational-ai-execution-gates',
                'title' => ['en' => 'Rakez Operational AI: The Model Suggests, the Backend Decides', 'ar' => 'الذكاء التشغيلي في راكز: النموذج يقترح والـBackend يقرر'],
                'summary' => ['en' => 'AI is exposed through scoped tool families that inherit user capability, data scope, guardrails, audit, and confirmation rather than receiving independent authority.', 'ar' => 'يعمل AI عبر عائلات أدوات محددة ترث صلاحية المستخدم ونطاق البيانات والـguardrails والتدقيق والتأكيد بدلاً من امتلاك سلطة مستقلة.'],
                'context' => ['en' => 'Rakez uses AI across CRM, project and contract lookup, sales and finance queries, marketing analysis, RAG, and operational assistance.', 'ar' => 'يستخدم راكز AI في CRM والبحث بالمشاريع والعقود واستعلامات المبيعات والمالية وتحليل التسويق وRAG والمساعدة التشغيلية.'],
                'problem' => ['en' => 'Connecting a model directly to business data or write actions would create a second authorization system and an unsafe execution path.', 'ar' => 'ربط النموذج مباشرة ببيانات الأعمال أو إجراءات الكتابة سيخلق نظام صلاحيات ثانياً ومسار تنفيذ غير آمن.'],
                'constraints' => ['en' => 'Every tool call must respect authenticated capability, tenant/project/record scope, privacy controls, numeric/business guardrails, and confirmation for operational impact.', 'ar' => 'يجب أن يحترم كل استدعاء أداة صلاحية المستخدم ونطاق الشركة/المشروع/السجل وضوابط الخصوصية وقواعد الأرقام والأعمال والتأكيد عند التأثير التشغيلي.'],
                'solution' => ['en' => 'Resolve capability first, validate context and data scope, apply guardrails, use RAG only as evidence, return structured output, log the call, and require explicit confirmation before impactful writes.', 'ar' => 'حل الصلاحية أولاً ثم التحقق من السياق ونطاق البيانات وتطبيق guardrails واستخدام RAG كدليل فقط وإرجاع استجابة منظمة وتسجيل الاستدعاء وطلب تأكيد صريح قبل أي كتابة مؤثرة.'],
                'architecture' => ['en' => 'Authenticated user -> capability resolution -> scoped tool -> context check -> guardrails/RAG -> structured response -> audit -> human confirmation.', 'ar' => 'مستخدم موثق ← حل الصلاحية ← أداة محددة ← تحقق السياق ← guardrails/RAG ← استجابة منظمة ← تدقيق ← تأكيد بشري.'],
                'outcomes' => ['en' => 'AI improves access to operational intelligence without becoming a bypass around backend policy.', 'ar' => 'يحسن AI الوصول إلى الذكاء التشغيلي دون أن يصبح طريقاً لتجاوز سياسات الـbackend.'],
                'evidence' => ['en' => 'The Operational AI carousel records 17 registered AI tools in the inspected Rakez implementation and states that they remain behind application authorization.', 'ar' => 'يسجل Carousel Operational AI وجود 17 أداة AI مسجلة في نسخة راكز المفحوصة وأنها تبقى خلف صلاحيات التطبيق.'],
                'features' => ['en' => "Scoped tool families\nCapability resolution\nData-scope checks\nPII and business guardrails\nRAG as evidence\nAudit logs\nHuman confirmation", 'ar' => "عائلات أدوات محددة\nحل الصلاحيات\nفحص نطاق البيانات\nGuardrails للخصوصية والأعمال\nRAG كدليل\nسجلات تدقيق\nتأكيد بشري"],
                'sort_order' => 5,
            ],
        ];

        foreach ($studies as $data) {
            $study = CaseStudy::firstOrNew(['slug' => $data['slug']]);
            $study->fill($data + [
                'client_name' => 'Hexa Terminal',
                'project_url' => null,
                'video_url' => null,
                'cover_image' => $cover,
                'cover_image_alt' => ['en' => 'Rakez ERP case study visual.', 'ar' => 'صورة دراسة حالة راكز ERP.'],
                'gallery' => [],
                'system_id' => $system->id,
                'project_classification' => CaseStudy::CLASSIFICATION_CUSTOM_ERP_CRM,
                'is_featured' => true,
                'is_published' => true,
            ]);
            $study->published_at ??= now();
            $study->save();
        }
    }

    private function seedArticles(string $cover): void
    {
        $articles = [
            [
                'slug' => 'erp-is-not-digital-spreadsheets',
                'title' => ['en' => 'ERP Is Not Digital Spreadsheets: Model the Workflow, Not the Screen', 'ar' => 'الـERP ليس Excel رقمياً: نمذج سير العمل لا الشاشة'],
                'excerpt' => ['en' => 'The value of an ERP appears when one business event crosses multiple departments and the system keeps every downstream state consistent.', 'ar' => 'تظهر قيمة الـERP عندما يعبر حدث تجاري واحد عدة أقسام ويحافظ النظام على اتساق جميع الحالات الناتجة.'],
                'body' => [
                    'en' => '<h2>CRUD is the easy part</h2><p>An ERP becomes difficult when a reservation changes inventory, creates financial obligations, affects commissions, advances credit, updates dashboards, and notifies people at the same time. Copying spreadsheet columns into database tables does not solve that problem.</p><h2>Model business transitions</h2><p>The useful unit of design is the business transition: what state is allowed now, who may change it, what must be locked, which facts must be snapshotted, and which downstream domains consume the result.</p><h2>Preserve history</h2><p>Prices, policies, commissions, and responsibilities change. An operational system should preserve the context used for an accepted transaction instead of silently recalculating history with today\'s settings.</p><h2>One source of truth means one set of rules</h2><p>Centralization is not putting every screen in one menu. It is making CRM, contracts, finance, marketing, and operations agree on the same accepted business state.</p>',
                    'ar' => '<h2>CRUD هو الجزء السهل</h2><p>يصبح الـERP صعباً عندما يغير الحجز المخزون وينشئ التزامات مالية ويؤثر على العمولات والائتمان واللوحات والإشعارات في الوقت نفسه. نسخ أعمدة Excel إلى جداول قاعدة بيانات لا يحل هذه المشكلة.</p><h2>نمذج انتقالات العمل</h2><p>الوحدة المفيدة في التصميم هي انتقال الحالة التجارية: ما الحالة المسموحة الآن؟ من يملك تغييرها؟ ماذا يجب قفله؟ ما المعلومات التي يجب حفظ snapshot لها؟ وما المجالات التي ستعتمد النتيجة؟</p><h2>احفظ التاريخ</h2><p>الأسعار والسياسات والعمولات والمسؤوليات تتغير. النظام التشغيلي الجيد يحفظ السياق الذي استُخدم في المعاملة المعتمدة بدلاً من إعادة حساب الماضي بإعدادات اليوم.</p><h2>مصدر واحد للحقيقة يعني قواعد واحدة</h2><p>المركزية ليست جمع كل الشاشات في قائمة واحدة، بل جعل CRM والعقود والمالية والتسويق والعمليات تتفق على حالة عمل واحدة موثوقة.</p>',
                ],
            ],
            [
                'slug' => 'lead-to-contract-operational-source-of-truth',
                'title' => ['en' => 'From Lead to Contract: Designing One Operational Source of Truth', 'ar' => 'من الـLead إلى العقد: كيف تبني مصدراً تشغيلياً واحداً للحقيقة'],
                'excerpt' => ['en' => 'A lead is not isolated CRM data once it becomes a reservation, contract, payment plan, commission event, and management metric.', 'ar' => 'الـLead لا يبقى مجرد بيانات CRM عندما يتحول إلى حجز وعقد وخطة دفع وعمولة ومؤشر إداري.'],
                'body' => [
                    'en' => '<h2>The handoffs are the product</h2><p>Sales software fails when every team owns a separate copy of the customer and deal. The important architecture is the handoff from lead to opportunity, from opportunity to inventory decision, from reservation to contract, and from contract to finance.</p><h2>Normalize identity and ownership</h2><p>Before automating anything, the system needs stable customer identity, lead ownership, source traceability, project and unit references, and explicit transition rules.</p><h2>Make downstream effects deterministic</h2><p>A confirmed state should have predictable effects. Notifications, dashboards, commissions, claims, and accounting-adjacent records should consume the same accepted event, not infer their own version of it.</p><h2>This is where custom ERP earns its cost</h2><p>The advantage is not another dashboard. It is fewer disagreements between departments about what happened and what should happen next.</p>',
                    'ar' => '<h2>التسليم بين الأقسام هو المنتج الحقيقي</h2><p>يفشل نظام المبيعات عندما يمتلك كل فريق نسخة منفصلة من العميل والصفقة. الهندسة المهمة هي الانتقال من lead إلى فرصة، ومن الفرصة إلى قرار مخزون، ومن الحجز إلى العقد، ومن العقد إلى المالية.</p><h2>وحّد الهوية والملكية</h2><p>قبل الأتمتة يحتاج النظام هوية عميل ثابتة وملكية واضحة للـlead وتتبع المصدر وروابط دقيقة للمشروع والوحدة وقواعد انتقال صريحة.</p><h2>اجعل الآثار اللاحقة حتمية</h2><p>الحالة المعتمدة يجب أن تنتج آثاراً متوقعة. الإشعارات واللوحات والعمولات والمطالبات يجب أن تعتمد الحدث نفسه لا أن تستنتج كل جهة نسخة مختلفة منه.</p><h2>هنا تظهر قيمة الـERP المخصص</h2><p>القيمة ليست Dashboard إضافية؛ بل تقليل الخلاف بين الأقسام حول ما حدث وما الذي يجب أن يحدث بعده.</p>',
                ],
            ],
            [
                'slug' => 'marketing-attribution-inside-erp',
                'title' => ['en' => 'Marketing Attribution Inside an ERP: Evidence Before ROAS', 'ar' => 'الإسناد التسويقي داخل ERP: الدليل قبل ROAS'],
                'excerpt' => ['en' => 'OAuth gives you a connection. Reliable attribution requires verified account, campaign, and internal-project mapping after the callback.', 'ar' => 'OAuth يمنحك اتصالاً، لكن الإسناد الموثوق يحتاج تحققاً من الحساب والحملة وربطهما بالمشروع الداخلي بعد الـcallback.'],
                'body' => [
                    'en' => '<h2>Campaign IDs are inputs, not truth</h2><p>External identifiers can be missing, reused, stripped, or disconnected from the business entity management actually cares about. If the application maps them directly from browser input, a clean-looking dashboard can still be wrong.</p><h2>Build a verification chain</h2><p>A safer model is provider account → synchronized campaign → verified mapping → internal project code. Spend, leads, and insights inherit that mapping.</p><h2>Unattributed is valid data</h2><p>When evidence is insufficient, record the gap. A truthful unattributed bucket is more useful than an invented ROAS number.</p><h2>Gate writes more strictly than reads</h2><p>Reporting may display incomplete attribution with a warning. Actions that can pause campaigns, change budgets, or trigger business workflows should require verified mapping and authorization.</p>',
                    'ar' => '<h2>معرّف الحملة مدخل وليس حقيقة</h2><p>قد تكون المعرّفات الخارجية ناقصة أو معاد استخدامها أو محذوفة من المتصفح أو غير مرتبطة بالكيان الذي يهم الإدارة. الربط المباشر قد ينتج Dashboard نظيفة لكنها خاطئة.</p><h2>ابنِ سلسلة تحقق</h2><p>نموذج أكثر أماناً: حساب المزود ← حملة متزامنة ← مطابقة موثقة ← كود المشروع الداخلي. بعدها يرث الإنفاق والعملاء والتحليلات هذه المطابقة.</p><h2>Unattributed بيانات صحيحة</h2><p>عندما لا يكفي الدليل، سجّل الفجوة. رقم غير منسوب بصدق أفضل من ROAS مختلق.</p><h2>شدّد بوابة الكتابة أكثر من القراءة</h2><p>يمكن للتقرير عرض إسناد ناقص مع تنبيه، أما إيقاف الحملات أو تعديل الميزانية أو تشغيل workflow فيجب أن يتطلب مطابقة موثقة وصلاحية واضحة.</p>',
                ],
            ],
            [
                'slug' => 'financial-workflows-need-versioned-history',
                'title' => ['en' => 'Financial Workflows Need Versioned History, Not One Percentage Field', 'ar' => 'المسارات المالية تحتاج تاريخاً Versioned لا حقل نسبة واحداً'],
                'excerpt' => ['en' => 'Commission correctness depends on participants, policy versions, lifecycle states, locking, and preserved unresolved allocation.', 'ar' => 'صحة العمولات تعتمد على المشاركين وإصدارات السياسات وحالات الدورة والقفل وحفظ التوزيع غير المحلول.'],
                'body' => [
                    'en' => '<h2>Money needs explainability</h2><p>A commission amount is the result of participants, roles, weights, policy, timing, approvals, and prior events. Storing only the final number removes the explanation.</p><h2>Snapshot the rule that produced the amount</h2><p>If policy changes next month, an already accepted calculation should still be reconstructable under the policy that was active when it happened.</p><h2>Never make missing allocation disappear</h2><p>If a bucket cannot be assigned, preserve it as unresolved. Financial software should expose uncertainty rather than silently lose value or invent a recipient.</p><h2>Concurrency belongs in the design</h2><p>Duplicate prevention and row locking protect the invariant when two triggers try to calculate or approve the same business event.</p>',
                    'ar' => '<h2>المال يحتاج تفسيراً</h2><p>قيمة العمولة نتيجة مشاركين وأدوار وأوزان وسياسة وتوقيت وموافقات وأحداث سابقة. حفظ الرقم النهائي فقط يلغي سبب الرقم.</p><h2>احفظ Snapshot للقاعدة التي أنتجت الرقم</h2><p>إذا تغيرت السياسة الشهر القادم يجب أن تبقى العملية السابقة قابلة لإعادة البناء وفق السياسة التي كانت فعالة وقتها.</p><h2>لا تجعل التوزيع الناقص يختفي</h2><p>إذا تعذر إسناد جزء من العمولة، احفظه كـunresolved. البرنامج المالي يجب أن يظهر عدم اليقين لا أن يخفي القيمة أو يختلق مستفيداً.</p><h2>التزامن جزء من التصميم</h2><p>منع التكرار وrow locking يحميان القاعدة عندما يحاول triggerان حساب أو اعتماد الحدث نفسه.</p>',
                ],
            ],
            [
                'slug' => 'operational-ai-inherits-application-policy',
                'title' => ['en' => 'Operational AI Should Inherit Application Policy', 'ar' => 'الذكاء التشغيلي يجب أن يرث سياسات التطبيق'],
                'excerpt' => ['en' => 'A useful AI assistant does not become a parallel super-admin. Tools should execute inside the same capability, scope, audit, and confirmation boundaries as the application.', 'ar' => 'مساعد AI المفيد لا يتحول إلى Super Admin موازٍ؛ يجب أن تعمل أدواته داخل حدود الصلاحيات والنطاق والتدقيق والتأكيد نفسها الخاصة بالتطبيق.'],
                'body' => [
                    'en' => '<h2>The model is not the authorization layer</h2><p>The application should decide what the user may see or do before a model is asked to reason about it.</p><h2>Tool families beat one omnipotent chatbot</h2><p>Separate scoped tools for CRM, finance, marketing, search, RAG, and operational status make capability boundaries explicit and testable.</p><h2>RAG adds evidence, not authority</h2><p>Retrieved context can improve an answer. It must not expand the user\'s permissions or create a path around record-level scope.</p><h2>Writes deserve confirmation</h2><p>For actions with operational impact, a strong pattern is capability → context check → guardrails → proposed action → human confirmation → backend execution → audit.</p>',
                    'ar' => '<h2>النموذج ليس طبقة الصلاحيات</h2><p>يجب أن يقرر التطبيق ما يحق للمستخدم رؤيته أو فعله قبل أن يُطلب من النموذج التفكير فيه.</p><h2>عائلات أدوات أفضل من Chatbot مطلق السلطة</h2><p>أدوات منفصلة ومحددة لـCRM والمالية والتسويق والبحث وRAG والحالة التشغيلية تجعل حدود الصلاحيات واضحة وقابلة للاختبار.</p><h2>RAG يضيف دليلاً لا سلطة</h2><p>السياق المسترجع يحسن الإجابة لكنه لا يجب أن يوسع صلاحيات المستخدم أو يفتح طريقاً حول نطاق السجلات.</p><h2>الكتابة تستحق تأكيداً</h2><p>للإجراءات المؤثرة: صلاحية ← فحص سياق ← guardrails ← إجراء مقترح ← تأكيد بشري ← تنفيذ backend ← تدقيق.</p>',
                ],
            ],
            [
                'slug' => 'rbac-is-the-path-not-the-permission-count',
                'title' => ['en' => 'RBAC Is the Path, Not the Permission Count', 'ar' => 'RBAC هو مسار التنفيذ وليس عدد الصلاحيات'],
                'excerpt' => ['en' => 'A large permission catalog means little if one route, tool, data query, or approval path can bypass it.', 'ar' => 'قائمة صلاحيات ضخمة لا تعني الكثير إذا كان Route أو Tool أو Query أو مسار موافقة واحد يستطيع تجاوزها.'],
                'body' => [
                    'en' => '<h2>Permission lists are only inventory</h2><p>The security property comes from enforcement: route middleware, policies, data scope, ownership checks, approval boundaries, and denial tests.</p><h2>Test the negative path</h2><p>It is easy to prove an admin can perform an action. The useful regression test proves the wrong role, wrong tenant, wrong project, or wrong record cannot.</p><h2>AI and integrations must use the same model</h2><p>Adding a new tool or provider adapter should not create a second shortcut around authorization. Every entry point needs the same capability and scope model.</p><h2>Measure coverage, not impressiveness</h2><p>The relevant question is not how many permissions exist; it is whether every sensitive execution path is covered by the intended policy.</p>',
                    'ar' => '<h2>قائمة الصلاحيات مجرد Inventory</h2><p>الخاصية الأمنية تأتي من التنفيذ: middleware وpolicies ونطاق البيانات وفحص الملكية وحدود الموافقة واختبارات المنع.</p><h2>اختبر المسار السلبي</h2><p>من السهل إثبات أن Admin يستطيع تنفيذ العملية. الاختبار المهم يثبت أن الدور أو الشركة أو المشروع أو السجل الخطأ لا يستطيع.</p><h2>AI والتكاملات تستخدم النموذج نفسه</h2><p>إضافة Tool أو مزود جديد يجب ألا تنشئ اختصاراً ثانياً حول الصلاحيات. كل نقطة دخول تحتاج capability ونطاق البيانات نفسيهما.</p><h2>قس التغطية لا الانبهار</h2><p>السؤال ليس كم صلاحية موجودة، بل هل كل مسار حساس مغطى بالسياسة المقصودة؟</p>',
                ],
            ],
        ];

        foreach ($articles as $index => $data) {
            $article = Article::firstOrNew(['slug' => $data['slug']]);
            $article->fill($data + [
                'cover_image' => $cover,
                'cover_image_alt' => ['en' => 'Hexa Terminal insight illustrated with the Rakez ERP operating-system map.', 'ar' => 'مقال Hexa Terminal موضح بخريطة نظام راكز ERP.'],
                'og_image' => $cover,
                'author_id' => null,
                'article_category_id' => null,
                'is_featured' => $index < 3,
                'is_published' => true,
                'updated_content_at' => now(),
            ]);
            $article->published_at ??= now()->subMinutes(count($articles) - $index);
            $article->save();
        }
    }
}
