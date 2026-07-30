<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class About_Us extends Model
{

    protected $fillable = [
        'company_name',
        'company_description',
        'website_url',
        'foundation_date',
        'contact_email',
        'facebook_url',
        'instagram_url',
        'linkedin_url',
        'github_url',
        'company_logo'
    ];

    protected $casts = [
        'foundation_date' => 'date',
    ];

    /**
     * Boot method for automatic file deletion and single record enforcement
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent creating multiple records
        static::creating(function ($model) {
            $existingCount = static::count();
            if ($existingCount >= 1) {
                throw new \Exception('Only one About Us record can exist.');
            }
        });

        static::deleting(function ($aboutUs) {
            // Delete logo file
            if ($aboutUs->company_logo) {
                $filePath = parse_url($aboutUs->company_logo, PHP_URL_PATH);
                $filePath = str_replace('api/storage/', '', $filePath);

                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        });
    }

    /**
     * Get the single instance or create empty one
     */
    public static function getSingleRecord()
    {
        return static::first() ?? new static();
    }
}