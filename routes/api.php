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
Route::get('/auth/debug-rules', [AuthController::class, 'debugRules'])->middleware('auth:api');

Route::middleware('auth:api')->group(function () {

    // ── Users & Roles ──────────────────────────────────────────────
    Route::get('/apps/users/roles',    [UserController::class,  'roles']);
    Route::get('/apps/permissions',    [RoleController::class,  'permissions']);
    Route::apiResource('apps/users', UserController::class)->middleware([
        'index'   => 'permission:read.User',
        'show'    => 'permission:read.User',
        'store'   => 'permission:create.User',
        'update'  => 'permission:update.User',
        'destroy' => 'permission:delete.User',
    ]);
    Route::apiResource('apps/roles', RoleController::class)->middleware([
        'index'   => 'permission:read.Role',
        'show'    => 'permission:read.Role',
        'store'   => 'permission:create.Role',
        'update'  => 'permission:update.Role',
        'destroy' => 'permission:delete.Role',
    ]);

    // ── Employees ─────────────────────────────────────────────────
    Route::get('/apps/employees-search',                                       [EmployeeController::class, 'search']);
    Route::get('/apps/employees/incentives',                                   [EmployeeController::class, 'incentivesHistory']);
    Route::get('/apps/employees/top-producers',                                [EmployeeController::class, 'topProducers']);
    Route::post('/apps/employees/{employee}/calculate-incentives',             [EmployeeController::class, 'calculateIncentives']);
    Route::post('/apps/employees/{employee}/incentives',                       [EmployeeController::class, 'storeIncentive']);
    Route::apiResource('apps/employees', EmployeeController::class)->middleware([
        'index'   => 'permission:read.Employee',
        'show'    => 'permission:read.Employee',
        'store'   => 'permission:create.Employee',
        'update'  => 'permission:update.Employee',
        'destroy' => 'permission:delete.Employee',
    ]);

    // ── Branches ──────────────────────────────────────────────────
    Route::get('/apps/branches/comparison',   [BranchController::class, 'comparison']) ->middleware('permission:read.Statistics');
    Route::get('/apps/branches/roi-analysis', [BranchController::class, 'roiAnalysis'])->middleware('permission:read.Statistics');
    Route::apiResource('apps/branches', BranchController::class)->middleware([
        'index'   => 'permission:read.Branch',
        'show'    => 'permission:read.Branch',
        'store'   => 'permission:create.Branch',
        'update'  => 'permission:update.Branch',
        'destroy' => 'permission:delete.Branch',
    ]);

    // ── Production Plans ──────────────────────────────────────────
    Route::get('/apps/production-plans/breakthroughs',                 [ProductionPlanController::class, 'breakthroughs'])->middleware('permission:read.Statistics');
    Route::post('/apps/production-plans/{productionPlan}/lock',        [ProductionPlanController::class, 'lock'])         ->middleware('permission:update.ProductionPlan');
    Route::get('/apps/production-plans/{productionPlan}/achievements', [ProductionPlanController::class, 'achievements']) ->middleware('permission:read.ProductionPlan');
    Route::apiResource('apps/production-plans', ProductionPlanController::class)->middleware([
        'index'   => 'permission:read.ProductionPlan',
        'show'    => 'permission:read.ProductionPlan',
        'store'   => 'permission:create.ProductionPlan',
        'update'  => 'permission:update.ProductionPlan',
        'destroy' => 'permission:delete.ProductionPlan',
    ]);

    // ── Policies ──────────────────────────────────────────────────
    Route::get('/apps/policies/stats',                 [PolicyController::class, 'stats'])      ->middleware('permission:read.Statistics');
    Route::get('/apps/policies/expiring-soon',         [PolicyController::class, 'expiringSoon'])->middleware('permission:read.Policy');
    Route::get('/apps/policies/kpis',                  [PolicyController::class, 'kpis'])        ->middleware('permission:read.Statistics');
    Route::get('/apps/policies/{policy}/download-pdf', [PolicyController::class, 'downloadPdf'])->middleware('permission:print.Policy');
    Route::apiResource('apps/policies', PolicyController::class)->middleware([
        'index'   => 'permission:read.Policy',
        'show'    => 'permission:read.Policy',
        'store'   => 'permission:create.Policy',
        'update'  => 'permission:update.Policy',
        'destroy' => 'permission:delete.Policy',
    ]);

    // ── Evaluations ───────────────────────────────────────────────
    Route::get('/apps/evaluation-periods/active',                                          [EvaluationPeriodController::class, 'active']);
    Route::get('/apps/evaluation-periods/{evaluation_period}/evaluations',                 [EvaluationPeriodController::class, 'evaluations'])   ->middleware('permission:read.Evaluation');
    Route::post('/apps/evaluation-periods/{evaluation_period}/evaluations',                [EvaluationPeriodController::class, 'storeEvaluation'])->middleware('permission:create.Evaluation');
    Route::delete('/apps/evaluation-periods/{evaluation_period}/evaluations/{evaluation}', [EvaluationPeriodController::class, 'destroyEvaluation'])->middleware('permission:delete.Evaluation');
    Route::post('/apps/evaluation-periods/{evaluation_period}/lock',                       [EvaluationPeriodController::class, 'lock'])           ->middleware('permission:update.Evaluation');
    Route::post('/apps/evaluation-periods/{evaluation_period}/toggle-status',              [EvaluationPeriodController::class, 'toggleStatus'])   ->middleware('permission:update.Evaluation');
    Route::apiResource('apps/evaluation-periods', EvaluationPeriodController::class)->except(['update'])->middleware([
        'index'   => 'permission:read.Evaluation',
        'show'    => 'permission:read.Evaluation',
        'store'   => 'permission:create.Evaluation',
        'destroy' => 'permission:delete.Evaluation',
    ]);

    // ── Settings & Logs ───────────────────────────────────────────
    Route::get('/apps/activity-logs', [ActivityLogController::class, 'index'])  ->middleware('permission:read.Log');
    Route::get('/apps/settings',      [SettingController::class,     'index'])  ->middleware('permission:manage.Settings');
    Route::post('/apps/settings',     [SettingController::class,     'update']) ->middleware('permission:manage.Settings');

});
