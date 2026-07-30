<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * Legacy project image table (pre-migration). Columns per
 * database/migrations/2025_07_20_181134_create_imag__progects_table.php and
 * 2025_11_11_205957_add_alt_text_and_order_to_imag_progects_table.php.
 *
 * @property int $id
 * @property int $project_id
 * @property string $image_path
 * @property string|null $alt_text
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Projects|null $project
 */
class Imag_Progect extends Model
{
    protected $table = 'imag__progects'; // Match your migration table name

    protected $fillable = [
        'project_id',
        'image_path',
        'alt_text',
        'order',
    ];

    /**
     * علاقة الصورة بالمشروع (كثير إلى واحد)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    /**
     * Boot method for automatic file deletion
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($image) {
            // Delete image file
            if ($image->image_path) {
                $filePath = parse_url($image->image_path, PHP_URL_PATH);
                $filePath = str_replace('api/storage/', '', $filePath);

                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                }
            }
        });
    }
}
