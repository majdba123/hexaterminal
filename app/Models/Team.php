<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'phone',
        'email',
        'photo',
        'github_url',
        'cv_file',
        'specialization',
        'position'
    ];

    protected static function boot()
    {
        parent::boot();

        // Automatically delete files when model is deleted - EXACTLY like your controller
        static::deleting(function ($team) {
            // Delete photo file
            if ($team->photo) {
                $filePath = parse_url($team->photo, PHP_URL_PATH);
                $filePath = str_replace('api/storage/', '', $filePath);

                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }

            // Delete CV file
            if ($team->cv_file) {
                $filePath = parse_url($team->cv_file, PHP_URL_PATH);
                $filePath = str_replace('api/storage/', '', $filePath);

                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        });
    }
}
