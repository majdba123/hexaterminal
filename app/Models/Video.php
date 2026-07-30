<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'title',        // عنوان الفيديو
        'description',  // وصف الفيديو
        'location',     // موقع الفيديو (مثال: "الصفحة الرئيسية")
        'video_url',    // رابط الفيديو (يوتيوب، فيميو، إلخ)

    ];
}
