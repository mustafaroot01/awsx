<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BranchProductionTarget extends Model
{
    protected $table = 'branch_production_targets';

    protected $fillable = [
        'plan_id',
        'branch_id',
        'category',
        'target_amount',
        'achieved_amount',
    ];

    protected $casts = [
        'target_amount'   => 'decimal:2',
        'achieved_amount' => 'decimal:2',
    ];

    public function getAchievementPercentageAttribute(): float
    {
        $target = (float) $this->target_amount;
        return $target > 0 ? round(((float) $this->achieved_amount / $target) * 100, 1) : 0;
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(ProductionPlan::class, 'plan_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
