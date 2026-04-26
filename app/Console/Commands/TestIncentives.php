<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\IncentiveCalculatorService;
use Illuminate\Console\Command;

class TestIncentives extends Command
{
    protected $signature = 'test:incentives';
    protected $description = 'Test the incentive calculation logic with different scenarios';

    public function handle()
    {
        $service = new IncentiveCalculatorService();

        // Scenario 1: Senior Employee, High Education, No Penalties
        $emp1 = new Employee([
            'degree' => 'الأولى',
            'education' => 'دكتوراه',
            'hire_date' => now()->subYears(32),
        ]);
        
        $res1 = $service->calculate($emp1, 240, 35, []);
        $this->displayResult("Scenario 1: Senior, No Penalties", $res1);

        // Scenario 2: Mid-level Employee, Warning Penalty
        $emp2 = new Employee([
            'degree' => 'الرابعة',
            'education' => 'بكالوريوس',
            'hire_date' => now()->subYears(12),
        ]);
        
        $res2 = $service->calculate($emp2, 240, 28, ['warning']);
        $this->displayResult("Scenario 2: Mid-level, Warning (-60 days)", $res2);

        // Scenario 3: Junior Employee, Multiple Penalties
        $emp3 = new Employee([
            'degree' => 'السادسة',
            'education' => 'إعدادية',
            'hire_date' => now()->subYears(3),
        ]);
        
        $res3 = $service->calculate($emp3, 200, 23, ['lfat_nazar', 'salary_cut']);
        $this->displayResult("Scenario 3: Junior, Lfat Nazar + Salary Cut (-120 days)", $res3);

        // Scenario 4: Dismissal Penalty (Critical)
        $emp4 = new Employee([
            'degree' => 'الأولى',
            'education' => 'ماجستير',
            'hire_date' => now()->subYears(25),
        ]);
        
        $res4 = $service->calculate($emp4, 240, 33, ['dismissal']);
        $this->displayResult("Scenario 4: Dismissal (Should be 0 points/days)", $res4);
    }

    private function displayResult($title, $res)
    {
        $this->info("--------------------------------------------------");
        $this->info($title);
        $this->line("Total Points: " . $res['total_points']);
        $this->line("Net Working Days: " . $res['net_working_days']);
        $this->line("Details: Grade({$res['grade_points']}) + Edu({$res['education_points']}) + Service({$res['service_points']}) + Comp({$res['competency_points']})");
    }
}
