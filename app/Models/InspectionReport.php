<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InspectionReport extends Model
{
    protected $fillable = [
        'policy_id',
        'wall_material',
        'roof_material',
        'lighting_type',
        'fire_extinguishers_info',
        'inspector_recommendation',
        'status',
        'inspector_id',
        'construction_description',
        'wall_material',
        'roof_material',
        'floor_material',
        'neighbors_connectivity',
        'neighbors_nature',
        'doors_locks_type',
        'window_grids',
        'lighting_heating',
        'machine_fuel',
        'wood_layers',
        'water_source',
        'extinguishers',
        'electrical_state',
        'hazardous_observation',
        'waste_disposal',
        'sketch_path',
    ];

    protected $casts = [
        'neighbors_connectivity' => 'boolean',
        'window_grids'           => 'boolean',
        'wood_layers'            => 'boolean',
    ];

    public function policy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Policy::class);
    }

    public function inspector(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'inspector_id');
    }
}
