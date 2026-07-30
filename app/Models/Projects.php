<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Projects extends Model
{
    protected $fillable = [
        'title',
        'project_url',
        'description',
        'video_url',
        'service_id',
    ];

    /**
     * علاقة المشروع بالخدمة (كثير إلى واحد)
     *
     * @return BelongsTo<Services, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Services::class, 'service_id');
    }

    /**
     * علاقة المشروع بالصور (واحد إلى كثير)
     *
     * @return HasMany<Imag_Progect, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(Imag_Progect::class, 'project_id');
    }

    /**
     * علاقة المشروع بالمميزات (واحد إلى كثير)
     *
     * @return HasMany<Fetures_Project, $this>
     */
    public function features(): HasMany
    {
        return $this->hasMany(Fetures_Project::class, 'project_id');
    }

    /**
     * Accessor for images count
     */
    public function getImagesCountAttribute(): int
    {
        return $this->images()->count();
    }

    /**
     * Accessor for features count
     */
    public function getFeaturesCountAttribute(): int
    {
        return $this->features()->count();
    }

    /**
     * Boot method for automatic cleanup
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically delete related records and files when project is deleted
        static::deleting(function ($project) {
            // Delete all related images and their files
            $project->images->each(function ($image) {
                if ($image->image_path) {
                    $filePath = parse_url($image->image_path, PHP_URL_PATH);
                    $filePath = str_replace('api/storage/', '', $filePath);

                    if (Storage::disk('public')->exists($filePath)) {
                        Storage::disk('public')->delete($filePath);
                    }
                }
                $image->delete();
            });

            // Delete all related features
            $project->features()->delete();
        });
    }
}
