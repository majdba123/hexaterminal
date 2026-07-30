<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Legacy services table (pre-migration). Columns per
 * database/migrations/2025_07_20_180504_create_services_table.php.
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $image_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Projects> $projects
 * @property-read int $projects_count
 */
class Services extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
    ];

    /**
     * علاقة الخدمة بالمشاريع (واحد إلى كثير)
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Projects::class, 'service_id');
    }

    protected static function boot()
    {
        parent::boot();

        // Automatically delete files when model is deleted
        static::deleting(function ($service) {
            // Delete image file
            if ($service->image_path) {
                $filePath = parse_url($service->image_path, PHP_URL_PATH);
                $filePath = str_replace('api/storage/', '', $filePath);

                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        });
    }

    /**
     * Accessor for projects count
     */
    public function getProjectsCountAttribute(): int
    {
        return $this->projects()->count();
    }
}
