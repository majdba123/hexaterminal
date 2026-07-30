<?php

namespace App\Models\Concerns;

use App\Models\ContentActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Editorial workflow + audit trail for CMS content types.
 *
 * `status` (draft -> in_review -> approved -> scheduled/published ->
 * archived) is the editorial state; `is_published` + `published_at` remain
 * the public-visibility contract read by the Publishable scope and the
 * entire API. This trait keeps the two in sync in BOTH directions so the
 * pre-existing seeders/tests/commands that only set `is_published` keep
 * working:
 *
 *  - status set explicitly  -> is_published derived (published|scheduled => true)
 *  - only is_published set  -> status derived (true => published, false => draft)
 *
 * "Scheduled" is is_published=true with a future published_at -- the
 * Publishable scope already hides it until the timestamp passes, so no cron
 * is needed for scheduled publishing to take effect.
 *
 * Auditability: stamps created_by/updated_by/approved_by/published_by (+
 * approved_at) from the authenticated user, and appends created / updated /
 * status_changed / deleted entries to content_activities (attribute NAMES
 * only -- never values).
 */
trait HasEditorialWorkflow
{
    public static array $workflowStatuses = [
        'draft', 'in_review', 'approved', 'scheduled', 'published', 'archived',
    ];

    public static function bootHasEditorialWorkflow(): void
    {
        static::saving(function ($model) {
            // Bidirectional sync (status wins when both changed).
            if ($model->isDirty('status')) {
                $model->is_published = in_array($model->status, ['published', 'scheduled'], true);
            } elseif ($model->isDirty('is_published')) {
                $model->status = $model->is_published ? 'published' : ($model->status ?: 'draft');
            }

            if ($model->status === 'published' && $model->published_at === null) {
                $model->published_at = now();
            }

            // Audit stamps -- only when a real user is acting (CLI/seeders stay null).
            $userId = Auth::id();
            if ($userId !== null) {
                if (! $model->exists) {
                    $model->created_by = $model->created_by ?? $userId;
                }
                $model->updated_by = $userId;

                if ($model->isDirty('status')) {
                    if ($model->status === 'approved') {
                        $model->approved_by = $userId;
                        $model->approved_at = now();
                    }
                    if (in_array($model->status, ['published', 'scheduled'], true)) {
                        $model->published_by = $userId;
                    }
                }
            }
        });

        static::created(function ($model) {
            ContentActivity::record($model, 'created');
        });

        static::updated(function ($model) {
            if ($model->wasChanged('status')) {
                ContentActivity::record($model, 'status_changed', [
                    'from' => $model->getOriginal('status'),
                    'to' => $model->status,
                ]);
            }

            $changed = array_values(array_diff(
                array_keys($model->getChanges()),
                ['updated_at', 'updated_by', 'status', 'is_published', 'published_by'],
            ));
            if ($changed !== []) {
                ContentActivity::record($model, 'updated', ['fields' => $changed]);
            }
        });

        static::deleted(function ($model) {
            ContentActivity::record($model, 'deleted', ['slug' => $model->slug ?? null]);
        });
    }

    public function initializeHasEditorialWorkflow(): void
    {
        $this->mergeFillable([
            'status', 'created_by', 'updated_by', 'approved_by', 'published_by', 'approved_at',
        ]);
        $this->mergeCasts(['approved_at' => 'datetime']);
    }

    public function activities()
    {
        return $this->morphMany(ContentActivity::class, 'subject')->latest('created_at');
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** @return BelongsTo<User, $this> */
    public function publisher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }
}
