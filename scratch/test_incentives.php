<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Employee;
use App\Services\IncentiveCalculatorService;

$service = new IncentiveCalculatorService();
$employee = Employee::first(); // Just get any employee

if (!$employee) {
    echo "No employee found\n";
    exit;
}

$result = $service->calculate($employee, 240, 23, ['resignation']);

print_r($result);
