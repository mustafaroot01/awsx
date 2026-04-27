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
        'status',
        'client_name',
        'occupation',
        'mahalla',
        'zuqaq',
        'dar',
        'amount',
        'issue_date',
        'expiry_date',
        'branch_id',
        'employee_id',
        'notes',
        'source_of_funds',
        'monthly_income',
        'business_type',
        'aml_officer_name',
        'aml_signed_at',
        'trade_name',
        'permanent_address',
        'phone',
        'district',
        'shop_no',
        'street_region',
        'shop_phone',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'issue_date'     => 'date:Y-m-d',
        'expiry_date'    => 'date:Y-m-d',
        'aml_signed_at'  => 'datetime',
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

    public function fundsSchedule(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PolicyFundsSchedule::class);
    }

    public function inspectionReports(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(InspectionReport::class);
    }

    public function beneficiaries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PolicyBeneficiary::class);
    }

    public function healthStatement(): HasOne
    {
        return $this->hasOne(HealthStatement::class);
    }

    public function fireTheftDetails(): HasOne
    {
        return $this->hasOne(FireTheftDetail::class);
    }

    public function companyDetails(): HasOne
    {
        return $this->hasOne(CompanyDetail::class);
    }
}
