<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyBeneficiary extends Model
{
    protected $fillable = [
        'policy_id',
        'name',
        'share_survival',
        'share_death',
        'relationship',
        'name_quad',
    ];

    protected $casts = [
        'share_survival' => 'decimal:2',
        'share_death'    => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->name) && !empty($model->name_quad)) {
                $model->name = $model->name_quad;
            }
        });
    }

    public function policy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
}
