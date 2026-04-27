<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyDetail extends Model
{
    protected $fillable = [
        'policy_id',
        'authorized_name',
        'authorized_address',
        'founder_names',
        'manager_name',
        'board_chairman',
        'board_members',
        'shareholder_names',
        'activity_type',
        'founding_date',
        'capital',
        'founding_place',
        'external_auditor_name',
    ];

    protected $casts = [
        'founding_date' => 'date',
        'capital'       => 'decimal:2',
    ];

    public function policy()
    {
        return $this->belongsTo(Policy::class);
    }
}
