<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EvaluationPeriodController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\ProductionPlanController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;

// Authentication
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/logout', [AuthController::class, 'logout']);
Route::get('/auth/debug-rules', [AuthController::class, 'debugRules'])->middleware('auth:sanctum');

// Users & Roles
Route::get('/apps/users/roles', [UserController::class, 'roles']);
Route::get('/apps/permissions', [RoleController::class, 'permissions']);
Route::apiResource('apps/users', UserController::class);
Route::apiResource('apps/roles', RoleController::class);

// Employee search (must be before resource route to avoid conflict with /{id})
Route::get('/apps/employees-search', [EmployeeController::class, 'search']);

// Employees CRUD
Route::get('/apps/employees/incentives', [EmployeeController::class, 'incentivesHistory']);
Route::get('/apps/employees/top-producers', [EmployeeController::class, 'topProducers']);
Route::apiResource('apps/employees', EmployeeController::class);
Route::post('/apps/employees/{employee}/calculate-incentives', [EmployeeController::class, 'calculateIncentives']);
Route::post('/apps/employees/{employee}/incentives', [EmployeeController::class, 'storeIncentive']);

// Branches CRUD
Route::get('/apps/branches/comparison', [BranchController::class, 'comparison']);
Route::get('/apps/branches/roi-analysis', [BranchController::class, 'roiAnalysis']);
Route::apiResource('apps/branches', BranchController::class);

// Production Plans
Route::get('/apps/production-plans/breakthroughs', [ProductionPlanController::class, 'breakthroughs']);
Route::post('/apps/production-plans/{productionPlan}/lock', [ProductionPlanController::class, 'lock']);
Route::get('/apps/production-plans/{productionPlan}/achievements', [ProductionPlanController::class, 'achievements']);
Route::apiResource('apps/production-plans', ProductionPlanController::class);

// Policies
Route::get('/apps/policies/stats', [PolicyController::class, 'stats']);
Route::get('/apps/policies/expiring-soon', [PolicyController::class, 'expiringSoon']);
Route::get('/apps/policies/kpis', [PolicyController::class, 'kpis']);
Route::get('/apps/policies/{policy}/download-pdf', [PolicyController::class, 'downloadPdf']);
Route::apiResource('apps/policies', PolicyController::class);

// Evaluation Periods + nested Evaluations
Route::get('/apps/evaluation-periods/active', [EvaluationPeriodController::class, 'active']);
Route::get('/apps/evaluation-periods/{evaluation_period}/evaluations', [EvaluationPeriodController::class, 'evaluations']);
Route::post('/apps/evaluation-periods/{evaluation_period}/evaluations', [EvaluationPeriodController::class, 'storeEvaluation']);
Route::delete('/apps/evaluation-periods/{evaluation_period}/evaluations/{evaluation}', [EvaluationPeriodController::class, 'destroyEvaluation']);
Route::post('/apps/evaluation-periods/{evaluation_period}/lock', [EvaluationPeriodController::class, 'lock']);
Route::post('/apps/evaluation-periods/{evaluation_period}/toggle-status', [EvaluationPeriodController::class, 'toggleStatus']);
Route::apiResource('apps/evaluation-periods', EvaluationPeriodController::class)->except(['update']);

// Settings & Logs
Route::get('/apps/activity-logs', [ActivityLogController::class, 'index']);
Route::get('/apps/settings', [SettingController::class, 'index']);
Route::post('/apps/settings', [SettingController::class, 'update']);
