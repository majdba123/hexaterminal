<?php

namespace Database\Seeders;

use App\Models\Review;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reviews = [
            [
                'name' => 'أحمد السعدي',
                'content' => 'فريق استثنائي في تطوير البرمجيات. قاموا بتحويل فكرتنا إلى تطبيق متكامل يتجاوز التوقعات. الاهتمام بالتفاصيل والجودة كان ملحوظاً في كل مرحلة من مراحل المشروع.',
                'rating' => 5,
                'review_date' => '2024-10-15',
                'is_approved' => true,
            ],
            [
                'name' => 'شركة التقنية المتطورة',
                'content' => 'تعاملنا معهم لتطوير نظام إدارة متكامل، وكانت النتيجة مبهرة. الاحترافية في التعامل والالتزام بالمواعيد جعلت التجربة سلسة ومثمرة. نوصي بهم بشدة.',
                'rating' => 5,
                'review_date' => '2024-09-22',
                'is_approved' => true,
            ],
            [
                'name' => 'مريم القحطاني',
                'content' => 'مطورون مبدعون ومحترفون. قاموا بتطوير تطبيق جوال لشركتنا بأعلى معايير الجودة. الدعم الفني المستمر بعد التسليم كان مميزاً.',
                'rating' => 5,
                'review_date' => '2024-11-05',
                'is_approved' => true,
            ],
            [
                'name' => 'مهند العتيبي',
                'content' => 'تجربة رائعة من البداية إلى النهاية. فريق العمل كان متعاوناً ومتفهماً لاحتياجاتنا. التطبيق النهائي كان سريعاً وسهل الاستخدام.',
                'rating' => 4,
                'review_date' => '2024-08-30',
                'is_approved' => true,
            ],
            [
                'name' => 'مجموعة الأعمال الذكية',
                'content' => 'تعاقدنا معهم لتطوير منصة إلكترونية، وكان اختياراً موفقاً. الخبرة التقنية والتنظيم المتميز كانا واضحين في كل خطوة.',
                'rating' => 5,
                'review_date' => '2024-10-08',
                'is_approved' => true,
            ],

        ];

        foreach ($reviews as $review) {
            Review::create($review);
        }

        $approvedCount = count(array_filter($reviews, function($review) {
            return $review['is_approved'];
        }));
        
        $pendingCount = count($reviews) - $approvedCount;

        $this->command->info(count($reviews) . ' تقييم تم إنشاؤها بنجاح!');
        $this->command->info($approvedCount . ' تقييم معتمد');
        $this->command->info($pendingCount . ' تقييم قيد الانتظار');
    }
}