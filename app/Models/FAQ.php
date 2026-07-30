<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class FAQ extends Model
{
    protected $fillable = [
        'question',
        'answer',
    ];

    /**
     * Accessor for short question preview
     */
    public function getQuestionPreviewAttribute(): string
    {
        return Str::limit($this->question, 80);
    }

    /**
     * Accessor for plain text answer (without HTML)
     */
    public function getPlainAnswerAttribute(): string
    {
        return strip_tags($this->answer);
    }

    /**
     * Accessor for short answer preview
     */
    public function getAnswerPreviewAttribute(): string
    {
        return Str::limit(strip_tags($this->answer), 100);
    }

    /**
     * Scope for active FAQs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}