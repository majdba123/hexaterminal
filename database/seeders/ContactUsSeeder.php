<?php

namespace Database\Seeders;

use App\Models\Contact_Us;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ContactUsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contacts = [
            [
                'name' => 'خالد المنصور',
                'email' => 'khaled.mansour@example.com',
                'phone' => '+966501234567',
                'subject' => 'استفسار عن تطوير تطبيق جوال',
                'message' => 'السلام عليكم، أود الاستفسار عن تكلفة ومدة تطوير تطبيق جوال لمتجر إلكتروني يدعم iOS و Android. أحتاج إلى ميزات مثل الدفع الإلكتروني وتتبع الطلبات.',
                'status' => 'completed',
            ],
            [
                'name' => 'سارة العمري',
                'email' => 'sara.omari@example.com',
                'phone' => '+966559876543',
                'subject' => 'طلب عرض سعر لموقع شركة',
                'message' => 'مرحباً، نحن شركة ناشئة ونحتاج إلى موقع ويب احترافي مع لوحة تحكم لإدارة المحتوى. هل يمكنكم إرسال عرض سعر مفصل؟',
                'status' => 'in_progress',
            ],
            [
                'name' => 'فهد الدوسري',
                'email' => 'fahad.dosari@example.com',
                'phone' => '+966541112233',
                'subject' => 'مشكلة تقنية في الموقع',
                'message' => 'أواجه مشكلة في تحميل بعض الصفحات على الموقع، تظهر رسالة خطأ عند محاولة الوصول إلى صفحة المشاريع. أرجو المساعدة.',
                'status' => 'completed',
            ],
            [
                'name' => 'نورة القحطاني',
                'email' => 'noura.qahtani@example.com',
                'phone' => null,
                'subject' => 'شراكة تقنية',
                'message' => 'نحن مؤسسة تعليمية ونبحث عن شريك تقني لتطوير منصة تعليمية إلكترونية متكاملة. نود مناقشة إمكانية التعاون.',
                'status' => 'in_progress',
            ],
            [
                'name' => 'عبدالله الشهري',
                'email' => 'abdullah.shahri@example.com',
                'phone' => '+966507778899',
                'subject' => 'استفسار عن خدمات الاستضافة السحابية',
                'message' => 'هل تقدمون خدمات الاستضافة السحابية وإدارة الخوادم؟ لدينا تطبيق يحتاج إلى بنية تحتية قوية وقابلة للتوسع.',
                'status' => 'pending',
            ],
            [
                'name' => 'ريم الحربي',
                'email' => 'reem.harbi@example.com',
                'phone' => '+966533445566',
                'subject' => 'تطوير نظام إدارة مخزون',
                'message' => 'أحتاج إلى نظام إدارة مخزون متكامل لشركتي التجارية. يجب أن يدعم الباركود، التقارير المفصلة، وتنبيهات المخزون المنخفض. ما هي التكلفة التقديرية؟',
                'status' => 'pending',
            ],
            [
                'name' => 'محمد الزهراني',
                'email' => 'mohammed.zahrani@example.com',
                'phone' => '+966521234567',
                'subject' => 'صيانة ودعم فني لتطبيق قائم',
                'message' => 'لدينا تطبيق ويب مبني على Laravel ونحتاج إلى فريق لصيانته وإضافة ميزات جديدة. هل تقدمون عقود صيانة شهرية؟',
                'status' => 'pending',
            ],
        ];

        foreach ($contacts as $contact) {
            Contact_Us::create($contact);
        }

        $pendingCount = count(array_filter($contacts, fn($c) => $c['status'] === 'pending'));
        $inProgressCount = count(array_filter($contacts, fn($c) => $c['status'] === 'in_progress'));
        $completedCount = count(array_filter($contacts, fn($c) => $c['status'] === 'completed'));

        $this->command->info(count($contacts) . ' رسالة تواصل تم إنشاؤها بنجاح!');
        $this->command->info("  {$pendingCount} قيد الانتظار | {$inProgressCount} قيد المعالجة | {$completedCount} مكتملة");
    }
}

