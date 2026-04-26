<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Policy extends Model
{
    // Category → production plan group mapping (for target vs achieved tracking)
    public const PLAN_CATEGORY_MAP = [
        'life'              => 'life',
        'group_health'      => 'group_health',
        'vehicle'           => 'general_property',
        'fire_theft'        => 'general_property',
        'transport_marine'  => 'general_property',
        'engineering'       => 'general_property',
        'personal_accident' => 'general_property',
        'cash'              => 'general_property',
    ];

    protected $fillable = [
        'policy_no',
        'category',
        'client_name',
        'amount',
        'issue_date',
        'expiry_date',
        'branch_id',
        'employee_id',
        'notes',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'issue_date'  => 'date:Y-m-d',
        'expiry_date' => 'date:Y-m-d',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function lifeDetails(): HasOne
    {
        return $this->hasOne(LifePolicyDetail::class);
    }
}
