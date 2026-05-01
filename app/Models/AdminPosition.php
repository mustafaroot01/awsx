<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPosition extends Model
{
    protected $fillable = ['name', 'points', 'sort_order', 'is_active'];

    protected $casts = [
        'points' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];
}
