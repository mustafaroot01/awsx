<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $emp = $this->employee;
        $empName = $emp
            ? "{$emp->first_name} {$emp->second_name} {$emp->third_name} {$emp->fourth_name} {$emp->last_name}"
            : null;

        return [
            'id'               => $this->id,
            'periodId'         => $this->period_id,
            'employeeId'       => $this->employee_id,
            'employeeName'     => $empName,
            'employeeNo'       => $emp?->employee_no,
            'degree'           => $emp?->degree,
            'rank'             => $emp?->rank,
            'education'        => $emp?->education,
            'serviceYears'     => $emp?->hire_date ? round($emp->hire_date->diffInYears(now()), 1) : null,
            'serviceDuration'  => $emp?->hire_date
                ? (function () use ($emp) {
                    $diff = $emp->hire_date->diff(now());
                    $parts = [];
                    if ($diff->y > 0) $parts[] = $diff->y . ' سنة';
                    if ($diff->m > 0) $parts[] = $diff->m . ' شهر';
                    if ($diff->d > 0) $parts[] = $diff->d . ' يوم';
                    return implode(' و ', $parts) ?: 'أقل من يوم';
                })()
                : null,
            'adminPosition'    => $emp?->admin_position,
            'year'             => $this->period?->year,
            'periodNo'         => $this->period?->period_no,
            'createdAt'        => $this->created_at,
            'branchId'         => $this->branch_id,
            'branchName'       => $this->branch?->name,
            'scoreAttendance'  => $this->score_attendance,
            'scorePerformance' => $this->score_performance,
            'scoreBehavior'    => $this->score_behavior,
            'scoreProduction'  => $this->score_production,
            'scoreTeamwork'    => $this->score_teamwork,
            'totalScore'            => $this->total_score,
            'grade'                 => $this->grade,
            'notes'                 => $this->notes,
            'efficiencyExperience'  => $this->efficiency_experience,
            'speedOfAchievement'    => $this->speed_of_achievement,
            'senseOfResponsibility' => $this->sense_of_responsibility,
            'behaviorWithOthers'    => $this->behavior_with_others,
            'attendanceCommitment'  => $this->attendance_commitment,
            'appreciationPenalties' => $this->appreciation_penalties,
            'pointsCompetency'      => $this->points_competency,
            'pointsGrade'           => $this->points_grade,
            'pointsEducation'       => $this->points_education,
            'pointsService'         => $this->points_service,
            'pointsTotal'           => $this->points_total,
            'actualWorkingDays'     => $this->actual_working_days,
            'netWorkingDays'        => $this->net_working_days,
        ];
    }
}
