<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class ProductionPlan extends Model
{
    use LogsActivity;
    protected $fillable = [
        'year',
        'title',
        'total_amount',
        'is_locked',
    ];

    protected $casts = [
        'year'         => 'integer',
        'total_amount' => 'decimal:2',
        'is_locked'    => 'boolean',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(ProductionPlanCategory::class, 'plan_id');
    }

    public function branchTargets(): HasMany
    {
        return $this->hasMany(BranchProductionTarget::class, 'plan_id');
    }
}
