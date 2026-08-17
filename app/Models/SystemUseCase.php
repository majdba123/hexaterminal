<?php

namespace App\Models;

use App\Models\Concerns\HasEditorialWorkflow;
use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

class SystemUseCase extends Model
{
    use HasEditorialWorkflow, HasFactory, HasTranslations, Publishable;

    protected $fillable = [
        'system_id', 'slug', 'title', 'actor', 'summary', 'workflow',
        'outcome', 'image', 'image_alt', 'is_published', 'published_at',
        'sort_order', 'status',
    ];

    public array $translatable = [
        'title', 'actor', 'summary', 'workflow', 'outcome', 'image_alt',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<System, $this>
     */
    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }
}
