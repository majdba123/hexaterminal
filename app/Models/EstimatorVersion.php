<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * A versioned snapshot of the estimator's questions and rules. Exactly one
 * version is active; new estimates run against it. Immutable-after-activation
 * is enforced at the CMS/service layer (clone-before-edit) so historical
 * CostEstimates stay reproducible against the version they used.
 */
class EstimatorVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'key', 'label', 'status', 'is_active', 'base_currency', 'currency_rates',
        'floor_min', 'ceiling_max', 'notes', 'activated_at', 'activated_by', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'currency_rates' => 'array',
        'floor_min' => 'integer',
        'ceiling_max' => 'integer',
        'activated_at' => 'datetime',
    ];

    /** @return HasMany<EstimatorQuestion, $this> */
    public function questions(): HasMany
    {
        return $this->hasMany(EstimatorQuestion::class)->orderBy('step')->orderBy('sort_order');
    }

    /** @return HasMany<EstimatorRule, $this> */
    public function rules(): HasMany
    {
        return $this->hasMany(EstimatorRule::class)->orderBy('sort_order');
    }

    /** @return HasMany<CostEstimate, $this> */
    public function estimates(): HasMany
    {
        return $this->hasMany(CostEstimate::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    public static function current(): ?self
    {
        return static::active()->latest('activated_at')->first();
    }

    /**
     * Make this the single active version. Atomic: demotes any other active
     * version so exactly one is ever accepting new estimates.
     */
    public function activate(?int $userId = null): void
    {
        DB::transaction(function () use ($userId) {
            static::where('id', '!=', $this->id)
                ->where('is_active', true)
                ->update(['is_active' => false, 'status' => 'archived']);

            $this->forceFill([
                'is_active' => true,
                'status' => 'active',
                'activated_at' => now(),
                'activated_by' => $userId,
            ])->save();
        });
    }

    /** Default USD-pegged rates when none configured (AED/SAR are USD pegs). */
    public function rates(): array
    {
        return $this->currency_rates ?: ['USD' => 1, 'AED' => 3.6725, 'SAR' => 3.75];
    }
}
