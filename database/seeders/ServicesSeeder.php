<?php

namespace Database\Seeders;

use App\Models\Services;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ServicesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            [
                'title' => 'تطوير تطبيقات الويب',
                'description' => 'نقوم بتطوير تطبيقات ويب متقدمة ومتجاوبة باستخدام أحدث التقنيات مثل Laravel، React، Vue.js. نضمن تطبيقات سريعة، آمنة، وقابلة للتطوير تلبي احتياجات عملك.',
                'image_path' => 'https://cdn-icons-png.flaticon.com/512/1006/1006363.png',
            ],
            [
                'title' => 'تطبيقات الجوال',
                'description' => 'نصمم ونطور تطبيقات جوال مبتكرة لنظامي iOS و Android باستخدام React Native و Flutter. تطبيقات سلسة وسريعة مع تجربة مستخدم استثنائية.',
                'image_path' => 'https://cdn-icons-png.flaticon.com/512/3448/3448656.png',
            ],
            [
                'title' => 'حلول التجارة الإلكترونية',
                'description' => 'نبني منصات تجارة إلكترونية متكاملة مع أنظمة دفع آمنة، إدارة مخزون، وتحليلات متقدمة. نحول متجرك إلى منصة مبيعات عالمية.',
                'image_path' => 'https://cdn-icons-png.flaticon.com/512/2331/2331966.png',
            ],
            [
                'title' => 'تطوير واجهات برمجة التطبيقات',
                'description' => 'نطور واجهات برمجة تطبيقات (APIs) آمنة وفعالة لتكامل الأنظمة المختلفة. نضمن توثيقاً شاملاً وأداءً عالياً لجميع حلولك التقنية.',
                'image_path' => 'https://cdn-icons-png.flaticon.com/512/2165/2165004.png',
            ],
            [
                'title' => 'الحلول السحابية',
                'description' => 'نقدم خدمات استشارات ونشر سحابية متكاملة على منصات AWS، Azure، وGoogle Cloud. نحسن أداء تطبيقاتك ونخفض التكاليف.',
                'image_path' => 'https://cdn-icons-png.flaticon.com/512/2103/2103633.png',
            ],
            [
                'title' => 'أنظمة إدارة المحتوى',
                'description' => 'نصمم أنظمة إدارة محتوى مخصصة تمنحك تحكماً كاملاً في محتوى موقعك. حلول مرنة وسهلة الاستخدام للإدارة اليومية.',
                'image_path' => 'https://cdn-icons-png.flaticon.com/512/2486/2486924.png',
            ],
            [
                'title' => 'تطبيقات المؤسسات',
                'description' => 'نطور أنظمة إدارة مؤسسية متكاملة تشمل إدارة الموارد البشرية، المالية، والمخزون. نحول عملياتك إلى نظام رقمي متكامل.',
                'image_path' => 'https://cdn-icons-png.flaticon.com/512/3050/3050523.png',
            ],
            [
                'title' => 'حلول الذكاء الاصطناعي',
                'description' => 'ندمج تقنيات الذكاء الاصطناعي وتعلم الآلة في تطبيقاتك لتحليل البيانات، الأتمتة، واتخاذ قرارات ذكية تعزز كفاءة عملك.',
                'image_path' => 'https://cdn-icons-png.flaticon.com/512/2103/2103631.png',
            ],
            [
                'title' => 'تطبيقات إنترنت الأشياء',
                'description' => 'نطور حلول إنترنت الأشياء (IoT) متكاملة تربط أجهزتك الذكية بأنظمة إدارة مركزية. مراقبة وتحكم ذكي في الوقت الفعلي.',
                'image_path' => 'https://cdn-icons-png.flaticon.com/512/3096/3096683.png',
            ],
            [
                'title' => 'استشارات تقنية',
                'description' => 'نقدم استشارات تقنية متخصصة لتحسين البنية التحتية، تحسين الأداء، ووضع استراتيجيات التحول الرقمي المناسبة لشركتك.',
                'image_path' => 'https://cdn-icons-png.flaticon.com/512/3594/3594426.png',
            ],
            [
                'title' => 'تطبيقات الواقع المعزز',
                'description' => 'نصمم تطبيقات واقع معزز (AR) تدمج العالم الرقمي بالواقع. حلول مبتكرة للتسويق، التعليم، والتدريب.',
                'image_path' => 'https://cdn-icons-png.flaticon.com/512/6840/6840478.png',
            ],
            [
                'title' => 'أنظمة الحجز والمواعيد',
                'description' => 'نطور أنظمة حجز ومواعيد ذكية للعيادات، الفنادق، ومراكز الخدمات. إدارة فعالة للجداول والعملاء.',
                'image_path' => 'https://cdn-icons-png.flaticon.com/512/3594/3594402.png',
            ],
        ];

        foreach ($services as $service) {
            Services::create($service);
        }

        $this->command->info(count($services) . ' خدمة تم إنشاؤها بنجاح!');
    }
}