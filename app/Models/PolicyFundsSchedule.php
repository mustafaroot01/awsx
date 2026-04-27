<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PolicyFundsSchedule extends Model
{
    protected $table = 'policy_funds_schedule';

    protected $fillable = [
        'policy_id',
        'category',
        'value',
        'description',
    ];

    public function policy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }
}
