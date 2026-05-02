<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\LogsActivity;

class Branch extends Model
{
    use LogsActivity;
    protected $fillable = [
        'name',
        'location',
        'governorate',
        'manager_id',
        'deputy_id',
    ];

    protected $casts = [
        'manager_id' => 'integer',
        'deputy_id'  => 'integer',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function manager(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function deputy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'deputy_id');
    }
}
