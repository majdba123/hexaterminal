<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const PUBLIC_SYSTEM_SLUGS = [
        'rakez-erp',
        'dhura',
        'matjrii',
        'leadscope-ai',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('systems')) {
            return;
        }

        $now = now();

        // Matjrii was supplied as part of the approved current portfolio but
        // did not yet exist in the production content catalog. Add only an
        // evidence-safe system record; visual media is attached separately
        // once the supplied screenshot binaries have been synchronized.
        $matjrii = DB::table('systems')->where('slug', 'matjrii')->first();

        if (! $matjrii) {
            DB::table('systems')->insert([
                'slug' => 'matjrii',
                'type' => 'saas_product',
                'category' => 'E-commerce SaaS Platform',
                'name' => json_encode([
                    'en' => 'Matjrii',
                    'ar' => 'Matjrii',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'tagline' => json_encode([
                    'en' => 'A commerce operations platform for store and product management.',
                    'ar' => 'منصة لإدارة المتاجر والمنتجات والعمليات التجارية.',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'short_description' => json_encode([
                    'en' => 'Matjrii gives store owners a centralized SaaS workspace for product management and day-to-day commerce operations.',
                    'ar' => 'يوفر Matjrii لأصحاب المتاجر مساحة SaaS مركزية لإدارة المنتجات وعمليات التجارة اليومية.',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'full_description' => json_encode([
                    'en' => 'A SaaS commerce platform centered on the store-owner workflow, combining operational dashboards with product and catalog management in one business interface.',
                    'ar' => 'منصة SaaS للتجارة تركز على سير عمل صاحب المتجر، وتجمع لوحات التشغيل مع إدارة المنتجات والكتالوج ضمن واجهة أعمال واحدة.',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'problem' => json_encode([
                    'en' => 'Store operators lose time when product administration and daily store visibility are split across disconnected screens and tools.',
                    'ar' => 'يفقد مشغلو المتاجر الوقت عندما تتوزع إدارة المنتجات ومتابعة عمليات المتجر اليومية بين شاشات وأدوات منفصلة.',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'solution' => json_encode([
                    'en' => 'A unified store-owner workspace brings product administration and operational visibility into one SaaS platform.',
                    'ar' => 'مساحة موحدة لصاحب المتجر تجمع إدارة المنتجات والرؤية التشغيلية ضمن منصة SaaS واحدة.',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'features' => json_encode([
                    'en' => "Store-owner dashboard\nProduct and catalog management\nCommerce operations workspace\nCentralized store administration",
                    'ar' => "لوحة تحكم لصاحب المتجر\nإدارة المنتجات والكتالوج\nمساحة للعمليات التجارية\nإدارة مركزية للمتجر",
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'business_outcomes' => json_encode([
                    'en' => "Clearer day-to-day store visibility\nCentralized product administration\nLess operational fragmentation",
                    'ar' => "رؤية أوضح لعمليات المتجر اليومية\nإدارة مركزية للمنتجات\nتقليل تشتت العمليات",
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'target_audience' => json_encode([
                    'en' => 'Store owners, e-commerce operators, and teams managing digital commerce workflows.',
                    'ar' => 'أصحاب المتاجر ومشغلو التجارة الإلكترونية والفرق التي تدير عمليات التجارة الرقمية.',
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'tech_stack' => json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'cover_image' => null,
                'cover_image_alt' => null,
                'gallery' => json_encode([], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'demo_url' => null,
                'live_url' => null,
                'is_featured' => true,
                'is_published' => true,
                'published_at' => $now,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        } else {
            DB::table('systems')->where('slug', 'matjrii')->update([
                'is_featured' => true,
                'is_published' => true,
                'published_at' => $matjrii->published_at ?: $now,
                'updated_at' => $now,
            ]);
        }

        // Make the four approved systems the only public system records. The
        // rows remain in the CMS and can be republished later; nothing is
        // deleted or anonymized here.
        DB::table('systems')
            ->whereNotIn('slug', self::PUBLIC_SYSTEM_SLUGS)
            ->update([
                'is_featured' => false,
                'is_published' => false,
                'updated_at' => $now,
            ]);

        DB::table('systems')
            ->whereIn('slug', self::PUBLIC_SYSTEM_SLUGS)
            ->update([
                'is_featured' => true,
                'is_published' => true,
                'updated_at' => $now,
            ]);

        if (! Schema::hasTable('case_studies')) {
            return;
        }

        $publicSystemIds = DB::table('systems')
            ->whereIn('slug', self::PUBLIC_SYSTEM_SLUGS)
            ->pluck('id');

        // Case studies for non-approved/legacy projects stay stored in the CMS
        // but are not reachable from the public API until deliberately restored.
        DB::table('case_studies')
            ->where(function ($query) use ($publicSystemIds) {
                $query->whereNull('system_id')
                    ->orWhereNotIn('system_id', $publicSystemIds);
            })
            ->update([
                'is_featured' => false,
                'is_published' => false,
                'updated_at' => $now,
            ]);
    }

    public function down(): void
    {
        // Content visibility is editorial state. A rollback must not guess
        // which archived projects were previously approved, so records remain
        // untouched and can be republished explicitly from the CMS.
    }
};
