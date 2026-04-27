<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FireTheftDetail extends Model
{
    protected $fillable = [
        'policy_id',
        'is_owner',
        'has_accounting_records',
        'jewelry_storage',
        'is_insured_amount_real',
        'closing_duration',
        'guarding_nature',
        'previous_incidents',
        'neighbors_incidents',
        'hazardous_materials',
        'previous_insurance_history',
        'peril_explosion',
        'peril_flood',
        'peril_storm',
        'peril_riot',
        'peril_tank_overflow',
        'peril_self_combustion',
        'peril_aircraft_impact',
        'peril_earthquake',
    ];

    protected $casts = [
        'is_owner'                => 'boolean',
        'has_accounting_records'  => 'boolean',
        'is_insured_amount_real' => 'boolean',
        'peril_explosion'         => 'boolean',
        'peril_flood'             => 'boolean',
        'peril_storm'             => 'boolean',
        'peril_riot'              => 'boolean',
        'peril_tank_overflow'     => 'boolean',
        'peril_self_combustion'   => 'boolean',
        'peril_aircraft_impact'   => 'boolean',
        'peril_earthquake'        => 'boolean',
    ];

    public function policy()
    {
        return $this->belongsTo(Policy::class);
    }
}
