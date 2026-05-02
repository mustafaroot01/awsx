<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\LogsActivity;

class Evaluation extends Model
{
    use LogsActivity;
    protected $fillable = [
        'period_id',
        'employee_id',
        'branch_id',
        'score_attendance',
        'score_performance',
        'score_behavior',
        'score_production',
        'score_teamwork',
        'total_score',
        'notes',
        'efficiency_experience',
        'speed_of_achievement',
        'sense_of_responsibility',
        'behavior_with_others',
        'attendance_commitment',
        'appreciation_penalties',
        'points_competency',
        'points_grade',
        'points_education',
        'points_service',
        'points_total',
        'actual_working_days',
        'net_working_days',
    ];

    protected $casts = [
        'score_attendance'  => 'integer',
        'score_performance' => 'integer',
        'score_behavior'    => 'integer',
        'score_production'  => 'integer',
        'score_teamwork'    => 'integer',
        'total_score'       => 'integer',
        'points_competency' => 'integer',
        'points_grade'      => 'integer',
        'points_education'  => 'integer',
        'points_service'    => 'integer',
        'points_total'      => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Evaluation $evaluation) {
            $evaluation->total_score =
                $evaluation->score_attendance +
                $evaluation->score_performance +
                $evaluation->score_behavior +
                $evaluation->score_production +
                $evaluation->score_teamwork;

            // Auto-calculate incentive points if employee is present
            if ($evaluation->employee) {
                $service = new \App\Services\IncentiveCalculatorService();
                // We might need to handle penalties and competency points here
                // For now, let's use defaults or stored values
                $res = $service->calculate(
                    $evaluation->employee,
                    $evaluation->actual_working_days ?: 60,
                    $evaluation->points_competency ?: 23,
                    [] // We can add penalties later if needed
                );

                $evaluation->points_grade = $res['grade_points'];
                $evaluation->points_education = $res['education_points'];
                $evaluation->points_service = $res['service_points'];
                $evaluation->points_total = $res['total_points'];
                $evaluation->net_working_days = $res['net_working_days'];
            }
        });
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(EvaluationPeriod::class, 'period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function getGradeAttribute(): string
    {
        return match (true) {
            $this->total_score >= 450 => 'ممتاز',
            $this->total_score >= 375 => 'جيد جداً',
            $this->total_score >= 300 => 'جيد',
            $this->total_score >= 200 => 'مقبول',
            default                   => 'ضعيف',
        };
    }
}
