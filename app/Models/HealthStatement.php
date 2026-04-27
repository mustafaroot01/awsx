<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HealthStatement extends Model
{
    protected $fillable = [
        'policy_id',
        'health_items',
        'family_history',
    ];

    protected $casts = [
        'health_items' => 'array',
        'family_history' => 'array',
    ];

    public function policy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
}
