# HexaTerminal

[English](README.md) | [العربية](README_AR.md)

> منصة الشركة والمحتوى الخاصة بـHexaTerminal، تجمع بين واجهة عامة مبنية باستخدام Next.js وBackend مبني باستخدام Laravel ولوحة CMS متكاملة باستخدام Filament وواجهة API عامة بإصدار واضح.

## نظرة عامة

HexaTerminal هي شركة برمجيات تركز على تطوير الأنظمة المخصصة والمنتجات الرقمية للأعمال. يحتوي هذا المستودع على المنصة التي تدير موقع HexaTerminal وتدفق إدارة المحتوى الخاص به.

تم فصل تجربة الزائر العامة عن إدارة المحتوى بشكل واضح:

- واجهة عامة مبنية باستخدام **Next.js**؛
- Backend مبني باستخدام **Laravel 12** ويتولى البيانات والـAPI؛
- لوحة **Filament 4 CMS** لإدارة المحتوى بشكل منظم؛
- واجهة API عامة بإصدار واضح تحت **`/api/v1/public`** تربط الواجهة بالمحتوى المنشور؛
- دعم المحتوى باللغتين **العربية والإنجليزية**.

المشروع ليس مجرد موقع تعريفي ثابت، بل يتضمن CMS منظم، سير نشر للمحتوى، API عامة، استقبال العملاء المحتملين، التسعير والتقدير المبدئي للتكلفة، البحث بالمحتوى، إدارة التحويلات، ومحتوى الثقة والحوكمة.

## الخدمات الأساسية المعروضة

المحتوى المعتمد داخل المشروع ينظم خدمات HexaTerminal ضمن ثلاث فئات رئيسية:

1. **أنظمة ERP وCRM مخصصة** — أنظمة تشغيلية تُبنى حول طريقة عمل الشركة الفعلية.
2. **منصات الويب وتطبيقات الجوال** — منصات متعددة المستخدمين، بوابات، أنظمة حجز، أسواق إلكترونية وتطبيقات.
3. **التجارة الإلكترونية ومواقع الأعمال** — مواقع وحلول تجارة إلكترونية مع وظائف وتكاملات مخصصة.

## نطاق المنصة

يدعم الـCMS والـAPI محتوى منظماً لعدد من أقسام الموقع، منها:

- الخدمات
- الأنظمة والمنتجات البرمجية
- دراسات الحالة
- القطاعات
- المقالات والتصنيفات والوسوم
- أعضاء الفريق
- آراء العملاء
- الأسئلة الشائعة
- نماذج التعاون والتسعير
- إعدادات أداة تقدير تكلفة المشروع والتقديرات الناتجة عنها
- إعدادات الشركة
- صفحات الثقة والحوكمة
- العملاء المحتملون والنشرة البريدية
- إدارة Redirects
- محتوى وبيانات SEO

## البنية العامة

```text
┌──────────────────────────────┐
│       الواجهة العامة         │
│ Next.js 16 + React 19 + TS  │
└──────────────┬───────────────┘
               │
               │ /api/v1/public/*
               ▼
┌──────────────────────────────┐
│       Laravel 12 API         │
│ Resources • Controllers     │
│ Locale • Cache • Security   │
└──────────────┬───────────────┘
               │
               ▼
┌──────────────────────────────┐
│ Eloquent Models / Database  │
│       محتوى CMS المنظم       │
└──────────────▲───────────────┘
               │
┌──────────────┴───────────────┐
│       Filament 4 CMS         │
│ نشر • وسائط • أدوار وصلاحيات │
└──────────────────────────────┘
```

تستهلك واجهة Next.js بيانات الموقع من Laravel عن طريق الـAPI العامة ذات الإصدار المحدد، ولا تعتمد مباشرة على لوحة CMS أو المسارات الإدارية القديمة.

للتفاصيل راجع [Next.js ↔ Laravel Boundary](docs/architecture/nextjs-laravel-boundary.md).

## إدارة المحتوى

البنية الحالية تعتمد محتوى CMS منظم بدلاً من وضع النصوص مباشرة داخل صفحات الواجهة.

يدعم المحتوى دورة تحرير ونشر تشمل الحالات التالية:

`draft → in_review → approved → scheduled → published → archived`

ولا يتم عرض السجلات عبر الـAPI العامة إلا وفق قواعد النشر المعتمدة في النظام.

كما تستخدم المنصة `spatie/laravel-translatable` للمحتوى متعدد اللغات، مع العربية والإنجليزية كلغتين أساسيتين في لوحة CMS.

يوجد توثيق تفصيلي لتدفق البيانات والكيانات في:

[HexaTerminal CMS Data Architecture](HEXATERMINAL_CMS_DATA_ARCHITECTURE.md)

## الـPublic API

واجهة الـAPI التي تعتمد عليها الواجهة العامة موجودة تحت:

```text
/api/v1/public/*
```

وتشمل موارد للمحتوى المنشور مثل الخدمات والأنظمة ودراسات الحالة والقطاعات والمقالات والفريق وآراء العملاء والأسئلة الشائعة والتسعير وأداة التقدير وإعدادات الشركة والبحث والتحويلات ومحتوى الثقة بالإضافة إلى عمليات الإرسال العامة.

تستخدم طبقة الـAPI مجموعة من الآليات الموجودة في الكود مثل اختيار اللغة وLaravel API Resources وCache Headers لبعض عمليات القراءة وRate Limiting لبعض عمليات الكتابة العامة.

المصدر الحالي لمسارات الـAPI هو [`routes/api_v1.php`](routes/api_v1.php).

## التقنيات المستخدمة

| المجال | التقنيات |
| --- | --- |
| Backend | PHP 8.2+, Laravel 12 |
| CMS | Filament 4 |
| Authentication / Authorization | Laravel Sanctum, Spatie Laravel Permission |
| Localization | Spatie Laravel Translatable, next-intl |
| Frontend | Next.js 16, React 19, TypeScript |
| Styling | Tailwind CSS 4 |
| Data / Cache Integration | Eloquent ORM, Redis client support عبر Predis |
| Backend Quality Tooling | PHPUnit 11, Larastan, Laravel Pint |
| Frontend Quality Tooling | ESLint, TypeScript typecheck, Playwright |

وجود أدوات الاختبار والجودة داخل المشروع لا يعني تلقائياً أن جميع الاختبارات ناجحة حالياً؛ نتائج التشغيل والـCI يجب التحقق منها بشكل مستقل عن التوثيق.

## هيكل المستودع

```text
.
├── app/                         # Laravel application, models, API and CMS
├── config/                      # Laravel configuration
├── database/                    # Migrations, factories and seeders
├── docs/                        # Architecture and engineering docs
├── frontend/                    # Next.js public website
├── public/                      # Laravel public assets / entrypoint
├── resources/                   # Laravel resources and views
├── routes/                      # Web/API/versioned public routes
├── storage/                     # Laravel runtime storage structure
├── tests/                       # Backend automated tests
├── HEXATERMINAL_CMS_DATA_ARCHITECTURE.md
├── composer.json
└── package.json
```

## الأمان والحوكمة

تحتوي البنية الحالية على عدد من الحدود الأمنية المهمة:

- البيانات العامة تمر عبر API Resource classes محددة بدلاً من تسلسل الـModels مباشرة؛
- إدارة المحتوى منفصلة عن عقد الـAPI العامة؛
- الوصول الإداري يتطلب المصادقة؛
- إعداد Filament CMS يحتوي دعماً لمصادقة MFA عبر تطبيق Authenticator للمستخدمين الإداريين؛
- الأدوار والصلاحيات مدعومة عبر Spatie Laravel Permission؛
- بعض مسارات الكتابة العامة مثل Leads والتقديرات تخضع لـRate Limiting وفق الإعدادات الحالية؛
- القيم السرية الخاصة بالبيئات المختلفة يجب أن تبقى خارج Source Control.

أي مفاتيح ظاهرة للعميل أو مدارَة من مزود خارجي يجب تقييدها وتدويرها من جهة المزود عندما يتطلب تاريخ التعرض ذلك.

## الواجهة العامة

الواجهة العامة موجودة داخل [`frontend/`](frontend/) وتستخدم Next.js App Router مع React وTypeScript وTailwind CSS و`next-intl`.

تعليمات الواجهة الخاصة موجودة في [`frontend/README.md`](frontend/README.md).

## ملاحظات التطوير

تتم إدارة Dependencies الخاصة بالـBackend باستخدام Composer، بينما تتم إدارة Dependencies الخاصة بالـFrontend باستخدام npm.

يجب الاعتماد على ملفات الإعداد المثال المناسبة لكل بيئة وعدم رفع بيانات الدخول أو المفاتيح السرية الفعلية إلى المستودع.

لأن متطلبات النشر قد تختلف بين local وstaging وproduction، لا يفترض هذا الملف وجود أمر واحد موحد وصالح لكل بيئات الإنتاج.

## التوثيق

- [CMS Data Architecture](HEXATERMINAL_CMS_DATA_ARCHITECTURE.md)
- [Next.js ↔ Laravel Boundary](docs/architecture/nextjs-laravel-boundary.md)
- [`routes/api_v1.php`](routes/api_v1.php) — عقد الـPublic API الحالي
- [`frontend/README.md`](frontend/README.md) — توثيق الواجهة العامة

## الموقع

HexaTerminal: https://www.hexaterminal.com/en

---

منصة مبنية على فصل واضح بين تجربة المستخدم العامة، إدارة المحتوى المنظمة، وبيانات التطبيق.