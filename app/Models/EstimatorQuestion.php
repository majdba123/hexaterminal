<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/**
 * One estimator question. Options are an inline JSON list of
 * {key, label:{en,ar}} value objects. `show_if` is a declarative branching
 * condition -- {"question": key, "in": [option_keys]} -- evaluated on the
 * client and re-validated server-side; never executable code.
 */
class EstimatorQuestion extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = [
        'estimator_version_id', 'key', 'step', 'sort_order', 'type',
        'prompt', 'help_text', 'is_required', 'show_if', 'options',
    ];

    public array $translatable = ['prompt', 'help_text'];

    protected $casts = [
        'step' => 'integer',
        'sort_order' => 'integer',
        'is_required' => 'boolean',
        'show_if' => 'array',
        'options' => 'array',
    ];

    /** @return BelongsTo<EstimatorVersion, $this> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(EstimatorVersion::class, 'estimator_version_id');
    }
}
