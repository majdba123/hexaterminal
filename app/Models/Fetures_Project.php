<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Fetures_Project extends Model
{
    protected $table = 'fetures_projects'; // Make sure table name matches your database

    protected $fillable = [
        'project_id',
        'feature_text',
    ];

    /**
     * علاقة الميزة بالمشروع (كثير إلى واحد)
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Projects::class, 'project_id');
    }

    /**
     * Accessor for feature preview
     */
    public function getFeaturePreviewAttribute(): string
    {
        return Str::limit($this->feature_text, 50);
    }
}
