<?php

namespace App\Services;

use App\Models\Employee;
use Carbon\Carbon;

class IncentiveCalculatorService
{
    /**
     * Calculate total incentive points and net working days.
     *
     * @param Employee $employee
     * @param int $actualWorkingDays
     * @param int $competencyPoints Points from 23 to 35 based on job title
     * @param array $penalties List of penalty types (e.g., ['warning', 'salary_cut'])
     * @return array
     */
    public function calculate(Employee $employee, int $actualWorkingDays, int $competencyPoints, array $penalties = []): array
    {
        // 1. Calculate points from different categories
        $gradePoints     = $this->calculateGradePoints($employee->degree);
        $educationPoints = $this->calculateEducationPoints($employee->education);
        $servicePoints   = $this->calculateServicePoints($employee->hire_date);
        
        // 2. Sum up total points
        $totalPoints = $gradePoints + $educationPoints + $servicePoints + $competencyPoints;

        // 3. Calculate net working days (considering penalties)
        $netWorkingDays = $this->calculateNetWorkingDays($actualWorkingDays, $penalties);

        // 4. Check for critical penalties that reset incentives
        if ($this->hasCriticalPenalties($penalties)) {
            $totalPoints = 0;
            $netWorkingDays = 0;
        }

        return [
            'grade_points'      => $gradePoints,
            'education_points'  => $educationPoints,
            'service_points'    => $servicePoints,
            'competency_points' => $competencyPoints,
            'total_points'      => $totalPoints,
            'net_working_days'  => $netWorkingDays,
        ];
    }

    /**
     * points for Job Grade (الدرجة الوظيفية)
     */
    private function calculateGradePoints(string $degree): int
    {
        return match ($degree) {
            'الأولى'           => 25,
            'الثانية', 'الثالثة' => 22,
            'الرابعة', 'الخامسة' => 16,
            'السادسة'          => 10,
            default            => 8, // السابعة فما دون
        };
    }

    /**
     * points for Education (التحصيل الدراسي)
     */
    private function calculateEducationPoints(string $education): int
    {
        return match ($education) {
            'دكتوراه'          => 15,
            'ماجستير', 'دبلوم عالي' => 13,
            'بكالوريوس'        => 11,
            'دبلوم'           => 10,
            'إعدادية'         => 8,
            default           => 5, // متوسطة فما دون
        };
    }

    /**
     * points for Service Years (سنوات الخدمة)
     */
    private function calculateServicePoints(mixed $hireDate): int
    {
        if (!$hireDate) return 0;
        
        $years = Carbon::parse($hireDate)->diffInYears(now());

        return match (true) {
            $years >= 30 => 15,
            $years >= 25 => 14,
            $years >= 20 => 13,
            $years >= 15 => 11,
            $years >= 10 => 9,
            $years >= 5  => 8,
            default      => 7,
        };
    }

    /**
     * Calculate net days after penalties (أيام العمل الصافية)
     */
    private function calculateNetWorkingDays(int $actualDays, array $penalties): int
    {
        $deductionDays = 0;

        foreach ($penalties as $penalty) {
            $deductionDays += match ($penalty) {
                'lfat_nazar'      => 30,  // لفت نظر
                'warning'         => 60,  // إنذار
                'salary_cut'      => 90,  // قطع راتب
                'promotion_delay' => 180, // تأخير ترفيع
                default           => 0,
            };
        }

        $netDays = $actualDays - $deductionDays;

        return max(0, $netDays);
    }

    /**
     * Check for extreme penalties that reset everything
     */
    private function hasCriticalPenalties(array $penalties): bool
    {
        $criticalOnes = ['grade_reduction', 'dismissal', 'resignation'];
        
        return !empty(array_intersect($penalties, $criticalOnes));
    }
}
