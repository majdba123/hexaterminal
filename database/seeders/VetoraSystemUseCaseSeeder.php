<?php

namespace Database\Seeders;

use App\Models\System;
use App\Models\SystemUseCase;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class VetoraSystemUseCaseSeeder extends Seeder
{
    private const SYSTEM_SLUG = 'vetora';

    private const USE_CASE_SLUG = 'multi-party-agricultural-veterinary-supply-marketplace';

    public function run(): void
    {
        $system = System::query()->where('slug', self::SYSTEM_SLUG)->first();

        if (! $system) {
            throw new \RuntimeException('The approved Vetora system must exist before seeding its use case.');
        }

        SystemUseCase::query()
            ->where('system_id', $system->id)
            ->where('slug', '!=', self::USE_CASE_SLUG)
            ->delete();

        $useCase = SystemUseCase::query()->firstOrNew([
            'system_id' => $system->id,
            'slug' => self::USE_CASE_SLUG,
        ]);

        $useCase->fill([
            'title' => [
                'en' => 'Multi-Party Agricultural & Veterinary Supply Marketplace',
                'ar' => 'سوق متكامل للمستلزمات الزراعية والبيطرية',
            ],
            'actor' => null,
            'summary' => [
                'en' => 'Vetora connects veterinary doctors and agricultural engineers with specialized suppliers in Syria through one marketplace. Customers browse the relevant agriculture or veterinary catalog, suppliers manage products and orders, employees review submitted products, syndicates monitor sector activity, and administrators manage the wider platform.',
                'ar' => 'تربط فيتورا الأطباء البيطريين والمهندسين الزراعيين بالموردين المتخصصين في سوريا من خلال سوق رقمي واحد. يتصفح المستخدم المنتجات الزراعية أو البيطرية المناسبة، ويدير الموردون المنتجات والطلبات، ويراجع الموظفون المنتجات المضافة، وتتابع النقابات نشاط القطاع، بينما تدير الإدارة المنصة كاملة.',
            ],
            'workflow' => null,
            'outcome' => null,
            'image' => 'systems/vetora-cover-public.png',
            'image_alt' => [
                'en' => 'Vetora public marketplace homepage presenting agriculture and veterinary product markets.',
                'ar' => 'الواجهة العامة لمنصة فيتورا وتعرض السوقين الزراعي والبيطري.',
            ],
            'is_published' => true,
            'published_at' => $useCase->published_at ?? Carbon::now(),
            'sort_order' => 1,
            'status' => 'published',
        ]);

        $useCase->save();
    }
}
