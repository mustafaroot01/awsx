<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = [
        'employee_no',
        'first_name',
        'second_name',
        'third_name',
        'fourth_name',
        'last_name',
        'degree',
        'rank',
        'education',
        'gender',
        'job_type',
        'production_no',
        'hire_date',
        'avatar',
        'branch_id',
    ];

    protected $casts = [
        'hire_date' => 'date:Y-m-d',
        'branch_id' => 'integer',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function policies()
    {
        return $this->hasMany(Policy::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->second_name} {$this->third_name} {$this->fourth_name} {$this->last_name}";
    }
}
