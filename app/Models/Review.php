<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'name',
        'content',
        'rating',
        'review_date',
        'is_approved'
    ];

    protected $casts = [
        'review_date' => 'date',
        'is_approved' => 'boolean',
    ];

    protected $attributes = [
        'is_approved' => false,
    ];

    /**
     * Scope for approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope for pending reviews
     */
    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    /**
     * Scope for high rating reviews (4-5 stars)
     */
    public function scopeHighRating($query)
    {
        return $query->where('rating', '>=', 4);
    }

    /**
     * Check if review is approved
     */
    public function isApproved(): bool
    {
        return $this->is_approved;
    }

    /**
     * Get rating as stars
     */
    public function getStarsAttribute(): string
    {
        return str_repeat('⭐', $this->rating);
    }
}