<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Translatable\HasTranslations;

/**
 * A declarative cost contribution. Never executable code -- the engine
 * reads these fields and applies base/add/multiply deterministically in a
 * fixed order. `label` is the human driver name shown in the breakdown.
 */
class EstimatorRule extends Model
{
    use HasFactory, HasTranslations;

    public const EFFECTS = ['base', 'add', 'multiply'];

    protected $fillable = [
        'estimator_version_id', 'driver', 'question_key', 'option_key', 'effect',
        'amount_min', 'amount_max', 'factor', 'weeks_min', 'weeks_max',
        'complexity_weight', 'label', 'sort_order',
    ];

    public array $translatable = ['label'];

    protected $casts = [
        'amount_min' => 'integer',
        'amount_max' => 'integer',
        'factor' => 'float',
        'weeks_min' => 'integer',
        'weeks_max' => 'integer',
        'complexity_weight' => 'integer',
        'sort_order' => 'integer',
    ];

    /** @return BelongsTo<EstimatorVersion, $this> */
    public function version(): BelongsTo
    {
        return $this->belongsTo(EstimatorVersion::class, 'estimator_version_id');
    }
}
