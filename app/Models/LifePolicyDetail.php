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
    ];

    protected $casts = [
        'accident_fee'   => 'decimal:2',
        'duration_years' => 'integer',
        'birth_date'     => 'date:Y-m-d',
    ];

    public function policy(): BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
}
