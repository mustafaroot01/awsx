<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeIncentive extends Model
{
    protected $fillable = [
        'employee_id',
        'year',
        'month',
        'actual_working_days',
        'competency_points',
        'total_points',
        'net_working_days',
        'penalties',
    ];

    protected $casts = [
        'penalties' => 'array',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
