<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Minimal newsletter-interest record (see migration note: not an email
 * marketing platform). Status supports future double opt-in.
 *
 * @property int $id
 * @property string $email
 * @property string $locale
 * @property string $status
 * @property Carbon|null $consent_at
 * @property string|null $source_page
 */
class NewsletterSubscriber extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    protected $fillable = ['email', 'locale', 'status', 'consent_at', 'source_page'];

    protected $casts = [
        'consent_at' => 'datetime',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }
}
