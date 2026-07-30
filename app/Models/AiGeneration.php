<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Provenance/audit trail for every AI-SEO suggestion (Stage 14). Rows
 * move pending -> generated -> reviewed -> approved|rejected|failed.
 * Nothing here is ever surfaced publicly until status = approved AND
 * a human explicitly applied it to the target content.
 */
class AiGeneration extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';

    public const STATUS_GENERATING = 'generating';

    public const STATUS_GENERATED = 'generated';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'provider', 'model', 'prompt_version', 'system_prompt_id', 'target_type',
        'target_id', 'field', 'locale', 'input_hash', 'output', 'input_tokens',
        'output_tokens', 'estimated_cost_usd', 'latency_ms', 'status',
        'failure_reason', 'error_category', 'generated_by', 'reviewed_by', 'reviewed_at',
    ];

    protected $casts = [
        'estimated_cost_usd' => 'decimal:4',
        'reviewed_at' => 'datetime',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /** @return BelongsTo<User, $this> */
    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    /** @return BelongsTo<User, $this> */
    public function reviewedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
