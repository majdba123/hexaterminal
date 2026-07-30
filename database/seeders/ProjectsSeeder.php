<?php

namespace Database\Seeders;

use App\Models\Projects;
use App\Models\Imag_Progect;
use App\Models\Fetures_Project;
use App\Models\Services;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProjectsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get service IDs - services must be seeded first
        $services = Services::all();

        if ($services->isEmpty()) {
            $this->command->warn('No services found! Please run ServicesSeeder first.');
            return;
        }

        $projects = [
            [
                'title' => 'منصة المتجر الإلكتروني الذكي',
                'project_url' => 'https://smart-store.example.com',
                'description' => 'منصة تجارة إلكترونية متكاملة تدعم الدفع الإلكتروني المتعدد، إدارة المخزون الذكية، ونظام توصيات مبني على الذكاء الاصطناعي. تم تطويرها باستخدام Laravel و Vue.js مع واجهة مستخدم حديثة وسريعة الاستجابة.',
                'video_url' => 'https://www.youtube.com/watch?v=example1',
                'service_index' => 0,
                'images' => [
                    ['image_path' => 'https://placehold.co/800x600/1a1a2e/e94560?text=Smart+Store+1', 'alt_text' => 'الصفحة الرئيسية للمتجر', 'order' => 1],
                    ['image_path' => 'https://placehold.co/800x600/16213e/0f3460?text=Smart+Store+2', 'alt_text' => 'صفحة المنتجات', 'order' => 2],
                    ['image_path' => 'https://placehold.co/800x600/1a1a2e/e94560?text=Smart+Store+3', 'alt_text' => 'سلة التسوق', 'order' => 3],
                ],
                'features' => [
                    'نظام دفع إلكتروني متعدد البوابات',
                    'إدارة مخزون ذكية مع تنبيهات آلية',
                    'محرك توصيات مبني على الذكاء الاصطناعي',
                    'لوحة تحكم متقدمة مع تحليلات مفصلة',
                    'تصميم متجاوب يدعم جميع الأجهزة',
                    'نظام إشعارات فوري للطلبات',
                ],
            ],
            [
                'title' => 'تطبيق إدارة المشاريع ProTask',
                'project_url' => 'https://protask.example.com',
                'description' => 'تطبيق إدارة مشاريع متكامل يدعم منهجية Agile و Scrum مع لوحات Kanban تفاعلية، تتبع الوقت، وإدارة الفرق. مبني بتقنيات React و Node.js مع قاعدة بيانات PostgreSQL.',
                'video_url' => 'https://www.youtube.com/watch?v=example2',
                'service_index' => 0,
                'images' => [
                    ['image_path' => 'https://placehold.co/800x600/0f3460/16213e?text=ProTask+1', 'alt_text' => 'لوحة القيادة', 'order' => 1],
                    ['image_path' => 'https://placehold.co/800x600/533483/e94560?text=ProTask+2', 'alt_text' => 'لوحة Kanban', 'order' => 2],
                ],
                'features' => [
                    'لوحات Kanban تفاعلية مع السحب والإفلات',
                    'تتبع الوقت والإنتاجية للفريق',
                    'تقارير ورسوم بيانية تفصيلية',
                    'تكامل مع Slack و Microsoft Teams',
                    'نظام صلاحيات متعدد المستويات',
                ],
            ],
            [
                'title' => 'تطبيق توصيل الطعام SpeedEats',
                'project_url' => 'https://speedeats.example.com',
                'description' => 'تطبيق جوال لتوصيل الطعام مع تتبع الطلبات في الوقت الفعلي، نظام تقييم المطاعم، وخوارزمية توزيع ذكية للسائقين. مطور باستخدام Flutter للجوال و Laravel للخادم.',
                'video_url' => null,
                'service_index' => 1,
                'images' => [
                    ['image_path' => 'https://placehold.co/800x600/e94560/1a1a2e?text=SpeedEats+1', 'alt_text' => 'واجهة التطبيق الرئيسية', 'order' => 1],
                    ['image_path' => 'https://placehold.co/800x600/533483/ffffff?text=SpeedEats+2', 'alt_text' => 'تتبع الطلب', 'order' => 2],
                    ['image_path' => 'https://placehold.co/800x600/0f3460/e94560?text=SpeedEats+3', 'alt_text' => 'قائمة المطاعم', 'order' => 3],
                    ['image_path' => 'https://placehold.co/800x600/1a1a2e/533483?text=SpeedEats+4', 'alt_text' => 'صفحة الدفع', 'order' => 4],
                ],
                'features' => [
                    'تتبع الطلبات في الوقت الفعلي على الخريطة',
                    'خوارزمية توزيع ذكية للسائقين',
                    'نظام تقييم ومراجعة المطاعم',
                    'دعم الدفع النقدي والإلكتروني',
                    'إشعارات فورية لحالة الطلب',
                    'برنامج ولاء وخصومات ذكية',
                    'دعم متعدد اللغات',
                ],
            ],
            [
                'title' => 'نظام إدارة العيادات الطبية',
                'project_url' => 'https://medclinic.example.com',
                'description' => 'نظام متكامل لإدارة العيادات الطبية يشمل إدارة المواعيد، السجلات الطبية الإلكترونية، الفوترة، وإدارة الصيدلية. مبني على Laravel مع واجهة حديثة بتقنية Vue.js.',
                'video_url' => 'https://www.youtube.com/watch?v=example4',
                'service_index' => 2,
                'images' => [
                    ['image_path' => 'https://placehold.co/800x600/16213e/e94560?text=MedClinic+1', 'alt_text' => 'لوحة القيادة الطبية', 'order' => 1],
                    ['image_path' => 'https://placehold.co/800x600/1a1a2e/0f3460?text=MedClinic+2', 'alt_text' => 'جدول المواعيد', 'order' => 2],
                ],
                'features' => [
                    'نظام حجز مواعيد ذكي مع تذكيرات آلية',
                    'سجلات طبية إلكترونية آمنة ومشفرة',
                    'نظام فوترة وتأمين متكامل',
                    'إدارة الصيدلية والمخزون الطبي',
                    'تقارير وإحصائيات مفصلة',
                ],
            ],
            [
                'title' => 'منصة التعلم الإلكتروني EduPro',
                'project_url' => 'https://edupro.example.com',
                'description' => 'منصة تعليمية إلكترونية متكاملة تدعم البث المباشر، الاختبارات التفاعلية، وشهادات إتمام الدورات. توفر تجربة تعلم سلسة للطلاب والمدرسين مع لوحة تحكم متقدمة.',
                'video_url' => 'https://www.youtube.com/watch?v=example5',
                'service_index' => 0,
                'images' => [
                    ['image_path' => 'https://placehold.co/800x600/533483/ffffff?text=EduPro+1', 'alt_text' => 'الصفحة الرئيسية', 'order' => 1],
                    ['image_path' => 'https://placehold.co/800x600/e94560/1a1a2e?text=EduPro+2', 'alt_text' => 'صفحة الدورة', 'order' => 2],
                    ['image_path' => 'https://placehold.co/800x600/0f3460/ffffff?text=EduPro+3', 'alt_text' => 'الاختبار التفاعلي', 'order' => 3],
                ],
                'features' => [
                    'بث مباشر للمحاضرات مع تفاعل فوري',
                    'اختبارات تفاعلية متعددة الأنماط',
                    'نظام شهادات إتمام الدورات',
                    'منتدى نقاش لكل دورة',
                    'تتبع تقدم الطالب بالتفصيل',
                    'دعم تحميل وبث الفيديوهات بجودة عالية',
                ],
            ],
            [
                'title' => 'نظام إدارة الموارد البشرية HRFlow',
                'project_url' => 'https://hrflow.example.com',
                'description' => 'نظام شامل لإدارة الموارد البشرية يشمل إدارة الموظفين، الرواتب، الإجازات، التقييمات، والتوظيف. حل متكامل يساعد المؤسسات على إدارة فرقها بكفاءة عالية.',
                'video_url' => null,
                'service_index' => 3,
                'images' => [
                    ['image_path' => 'https://placehold.co/800x600/1a1a2e/533483?text=HRFlow+1', 'alt_text' => 'لوحة القيادة', 'order' => 1],
                    ['image_path' => 'https://placehold.co/800x600/16213e/e94560?text=HRFlow+2', 'alt_text' => 'إدارة الموظفين', 'order' => 2],
                ],
                'features' => [
                    'إدارة شاملة لبيانات الموظفين',
                    'نظام رواتب آلي مع خصومات وبدلات',
                    'إدارة الإجازات والغياب',
                    'نظام تقييم أداء دوري',
                    'بوابة توظيف متكاملة',
                    'تقارير وتحليلات الموارد البشرية',
                ],
            ],
            [
                'title' => 'تطبيق المحفظة الرقمية DigiWallet',
                'project_url' => 'https://digiwallet.example.com',
                'description' => 'تطبيق محفظة رقمية آمن يدعم التحويلات المالية الفورية، دفع الفواتير، والتسوق عبر رمز QR. مبني بمعايير أمان عالية مع تشفير متقدم للبيانات المالية.',
                'video_url' => 'https://www.youtube.com/watch?v=example7',
                'service_index' => 1,
                'images' => [
                    ['image_path' => 'https://placehold.co/800x600/0f3460/e94560?text=DigiWallet+1', 'alt_text' => 'الشاشة الرئيسية', 'order' => 1],
                    ['image_path' => 'https://placehold.co/800x600/e94560/ffffff?text=DigiWallet+2', 'alt_text' => 'صفحة التحويل', 'order' => 2],
                    ['image_path' => 'https://placehold.co/800x600/533483/1a1a2e?text=DigiWallet+3', 'alt_text' => 'سجل المعاملات', 'order' => 3],
                ],
                'features' => [
                    'تحويلات مالية فورية بين المستخدمين',
                    'دفع الفواتير والخدمات',
                    'مسح وإنشاء رموز QR للدفع',
                    'تشفير متقدم للبيانات المالية',
                    'إشعارات فورية لكل معاملة',
                    'تقارير مالية مفصلة',
                    'دعم متعدد العملات',
                ],
            ],
            [
                'title' => 'منصة حجز الفنادق StayBook',
                'project_url' => 'https://staybook.example.com',
                'description' => 'منصة حجز فنادق وشقق فندقية مع نظام بحث متقدم، مقارنة أسعار، ومراجعات حقيقية. تصميم عصري مع تجربة حجز سلسة وآمنة.',
                'video_url' => null,
                'service_index' => 2,
                'images' => [
                    ['image_path' => 'https://placehold.co/800x600/16213e/533483?text=StayBook+1', 'alt_text' => 'صفحة البحث', 'order' => 1],
                    ['image_path' => 'https://placehold.co/800x600/1a1a2e/e94560?text=StayBook+2', 'alt_text' => 'تفاصيل الفندق', 'order' => 2],
                ],
                'features' => [
                    'محرك بحث متقدم مع فلاتر متعددة',
                    'مقارنة أسعار الغرف والعروض',
                    'نظام مراجعات وتقييمات حقيقية',
                    'حجز فوري مع تأكيد آلي',
                    'خرائط تفاعلية لموقع الفندق',
                    'برنامج مكافآت للعملاء المتكررين',
                ],
            ],
        ];

        $projectCount = 0;
        $imageCount = 0;
        $featureCount = 0;

        foreach ($projects as $projectData) {
            // Get the service_id from the service_index
            $serviceIndex = $projectData['service_index'];
            $service = $services->get($serviceIndex) ?? $services->first();

            $project = Projects::create([
                'title' => $projectData['title'],
                'project_url' => $projectData['project_url'],
                'description' => $projectData['description'],
                'video_url' => $projectData['video_url'],
                'service_id' => $service->id,
            ]);
            $projectCount++;

            // Create project images
            if (isset($projectData['images'])) {
                foreach ($projectData['images'] as $imageData) {
                    Imag_Progect::create([
                        'project_id' => $project->id,
                        'image_path' => $imageData['image_path'],
                        'alt_text' => $imageData['alt_text'],
                        'order' => $imageData['order'],
                    ]);
                    $imageCount++;
                }
            }

            // Create project features
            if (isset($projectData['features'])) {
                foreach ($projectData['features'] as $featureText) {
                    Fetures_Project::create([
                        'project_id' => $project->id,
                        'feature_text' => $featureText,
                    ]);
                    $featureCount++;
                }
            }
        }

        $this->command->info("{$projectCount} مشروع تم إنشاؤه بنجاح!");
        $this->command->info("{$imageCount} صورة مشروع تم إنشاؤها بنجاح!");
        $this->command->info("{$featureCount} ميزة مشروع تم إنشاؤها بنجاح!");
    }
}

