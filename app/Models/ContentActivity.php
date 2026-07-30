<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Append-only audit entry (see the content_activities migration). Records
 * attribute NAMES and safe metadata only -- never field values, so drafts
 * and lead PII cannot leak into the log.
 *
 * @property int $id
 * @property string $subject_type
 * @property int $subject_id
 * @property int|null $user_id
 * @property string $action
 * @property array<string, mixed>|null $details
 * @property Carbon $created_at
 */
class ContentActivity extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['subject_type', 'subject_id', 'user_id', 'action', 'details', 'created_at'];

    protected $casts = [
        'details' => 'array',
        'created_at' => 'datetime',
    ];

    public static function record(Model $subject, string $action, ?array $details = null): self
    {
        return self::create([
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => $details,
            'created_at' => now(),
        ]);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
