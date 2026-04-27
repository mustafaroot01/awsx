<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePolicyDetail extends Model
{
    protected $table = 'life_policy_details';

    protected $fillable = [
        'policy_id',
        'payment_cycle',
        'accident_fee',
        'duration_years',
        'id_number',
        'birth_date',
        'phone',
        'address',
        'beneficiary_name',
        'beneficiary_relation',
        'marital_status',
        'document_type',
        'id_card_no',
        'issue_authority_date',
        'spouse_name',
        'work_address',
        'home_address_detail',
        'height_cm',
        'weight_kg',
        'weight_change_last_year',
        'health_questionnaire',
    ];

    protected $casts = [
        'accident_fee'         => 'decimal:2',
        'duration_years'       => 'integer',
        'birth_date'           => 'date:Y-m-d',
        'health_questionnaire' => 'json',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
}
