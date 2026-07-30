<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $videos = [
            [
                'title' => 'من نحن - تعرف على فريقنا',
                'description' => 'فيديو تعريفي عن شركتنا وفريق العمل المتميز. تعرف على رؤيتنا ورسالتنا وكيف نعمل لتقديم أفضل الحلول التقنية.',
                'location' => 'homepage',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
            ],
            [
                'title' => 'كيف نبني مشاريع ناجحة',
                'description' => 'شرح مفصل لمنهجية عملنا في تطوير المشاريع البرمجية من الفكرة إلى الإطلاق، باستخدام أحدث التقنيات وأفضل الممارسات.',
                'location' => 'homepage',
                'video_url' => 'https://www.youtube.com/watch?v=jNQXAC9IVRw',
            ],
            [
                'title' => 'عرض مشروع - منصة التجارة الإلكترونية',
                'description' => 'عرض تفصيلي لأحد مشاريعنا الناجحة في مجال التجارة الإلكترونية. نستعرض الميزات الرئيسية والتقنيات المستخدمة.',
                'location' => 'projects',
                'video_url' => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
            ],
            [
                'title' => 'تقنيات الذكاء الاصطناعي في مشاريعنا',
                'description' => 'كيف ندمج تقنيات الذكاء الاصطناعي وتعلم الآلة في حلولنا البرمجية لتقديم تجربة مستخدم ذكية ومتطورة.',
                'location' => 'services',
                'video_url' => 'https://www.youtube.com/watch?v=kJQP7kiw5Fk',
            ],
            [
                'title' => 'شهادات عملائنا',
                'description' => 'استمع إلى تجارب عملائنا الناجحة وكيف ساعدناهم في تحقيق أهدافهم التقنية وتطوير أعمالهم.',
                'location' => 'reviews',
                'video_url' => 'https://www.youtube.com/watch?v=RgKAFK5djSk',
            ],
        ];

        foreach ($videos as $video) {
            Video::create($video);
        }

        $this->command->info(count($videos) . ' فيديو تم إنشاؤه بنجاح!');
    }
}

