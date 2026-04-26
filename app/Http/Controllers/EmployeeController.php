<?php

namespace App\Http\Controllers;

use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::query();
        
        // Filter by branch if the user is a branch manager
        if (auth()->check() && auth()->user()->branch_id) {
            $query->where('branch_id', auth()->user()->branch_id);
        }

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('first_name', 'LIKE', "%{$q}%")
                    ->orWhere('second_name', 'LIKE', "%{$q}%")
                    ->orWhere('third_name', 'LIKE', "%{$q}%")
                    ->orWhere('fourth_name', 'LIKE', "%{$q}%")
                    ->orWhere('last_name', 'LIKE', "%{$q}%")
                    ->orWhere('employee_no', 'LIKE', "%{$q}%");
            });
        }

        if ($gender = $request->get('gender')) {
            $query->where('gender', $gender);
        }

        if ($jobType = $request->get('jobType')) {
            $query->where('job_type', $jobType);
        }

        if ($degree = $request->get('degree')) {
            $query->where('degree', $degree);
        }

        $columnMap = [
            'employeeNo' => 'employee_no',
            'name'       => 'first_name',
            'hireDate'   => 'hire_date',
        ];

        $sortColumn = $columnMap[$request->get('sortBy', '')] ?? 'id';
        $direction  = $request->get('orderBy', 'asc') === 'desc' ? 'desc' : 'asc';
        $query->orderBy($sortColumn, $direction);

        $itemsPerPage = (int) $request->get('itemsPerPage', 10);
        $page         = (int) $request->get('page', 1);

        if ($itemsPerPage === -1) {
            $items      = $query->get();
            $total      = $items->count();
            $totalPages = 1;
        } else {
            $paginated  = $query->paginate($itemsPerPage, ['*'], 'page', $page);
            $items      = collect($paginated->items());
            $total      = $paginated->total();
            $totalPages = $paginated->lastPage();
        }

        return response()->json([
            'employees'      => EmployeeResource::collection($items),
            'totalPages'     => $totalPages,
            'totalEmployees' => $total,
            'page'           => $page,
        ]);
    }

    public function show(Employee $employee): JsonResponse
    {
        return response()->json(new EmployeeResource($employee));
    }

    public function store(Request $request): JsonResponse
    {
        $branchId = $request->input('branchId');

        // Enforce user's branch if they are a branch manager
        if (auth()->check() && auth()->user()->branch_id) {
            $branchId = auth()->user()->branch_id;
        }

        $employee = Employee::create([
            'employee_no'   => $request->input('employeeNo'),
            'first_name'    => $request->input('firstName'),
            'second_name'   => $request->input('secondName'),
            'third_name'    => $request->input('thirdName'),
            'fourth_name'   => $request->input('fourthName'),
            'last_name'     => $request->input('lastName'),
            'degree'        => $request->input('degree'),
            'rank'          => $request->input('rank'),
            'education'     => $request->input('education'),
            'gender'        => $request->input('gender'),
            'job_type'      => $request->input('jobType'),
            'production_no' => $request->input('productionNo'),
            'hire_date'     => $request->input('hireDate'),
            'avatar'        => $request->input('avatar'),
            'branch_id'     => $branchId,
        ]);

        return response()->json(new EmployeeResource($employee), 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $employee = Employee::findOrFail($id);

            // Security check for Branch Managers
            if (auth()->check() && auth()->user()->branch_id) {
                if ((int)$employee->branch_id !== (int)auth()->user()->branch_id) {
                    return response()->json(['message' => 'Unauthorized access to this branch employee'], 403);
                }
            }

            $employee->update([
                'employee_no'   => $request->input('employeeNo'),
                'first_name'    => $request->input('firstName'),
                'second_name'   => $request->input('secondName'),
                'third_name'    => $request->input('thirdName'),
                'fourth_name'   => $request->input('fourthName'),
                'last_name'     => $request->input('lastName'),
                'degree'        => $request->input('degree'),
                'rank'          => $request->input('rank'),
                'education'     => $request->input('education'),
                'gender'        => $request->input('gender'),
                'job_type'      => $request->input('jobType'),
                'production_no' => $request->input('productionNo'),
                'hire_date'     => $request->input('hireDate'),
                'avatar'        => $request->input('avatar'),
                'branch_id'     => (auth()->user()?->branch_id) ? $employee->branch_id : $request->input('branchId'),
            ]);

            return response()->json(new EmployeeResource($employee->fresh()));
        } catch (\Exception $e) {
            \Log::error('Employee Update Failed: ' . $e->getMessage());
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Employee $employee): JsonResponse
    {
        // Enforce branch manager restriction
        if (auth()->check() && auth()->user()->branch_id) {
            if ($employee->branch_id !== auth()->user()->branch_id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }

        $employee->delete();

        return response()->json(null, 204);
    }

    public function search(Request $request): JsonResponse
    {
        $q = $request->get('q', '');

        $query = Employee::query();

        // Enforce branch filter for search
        if (auth()->check() && auth()->user()->branch_id) {
            $query->where('branch_id', auth()->user()->branch_id);
        }

        $employees = $query->when($q, function ($query) use ($q) {
                $query->where(function($sub) use ($q) {
                    $sub->where('first_name', 'LIKE', "%{$q}%")
                        ->orWhere('second_name', 'LIKE', "%{$q}%")
                        ->orWhere('third_name', 'LIKE', "%{$q}%")
                        ->orWhere('fourth_name', 'LIKE', "%{$q}%")
                        ->orWhere('last_name', 'LIKE', "%{$q}%")
                        ->orWhere('employee_no', 'LIKE', "%{$q}%");
                });
            })
            ->orderBy('first_name')
            ->limit(20)
            ->get()
            ->map(fn(Employee $e) => [
                'id'         => $e->id,
                'name'       => $e->full_name,
                'employeeNo' => $e->employee_no,
                'rank'       => $e->rank,
            ]);

        return response()->json($employees);
    }

    public function calculateIncentives(Request $request, Employee $employee, \App\Services\IncentiveCalculatorService $service): JsonResponse
    {
        $request->validate([
            'actualWorkingDays' => 'required|integer|min:0',
            'competencyPoints'  => 'required|integer|between:23,35',
            'penalties'         => 'array',
        ]);

        $result = $service->calculate(
            $employee,
            $request->input('actualWorkingDays'),
            $request->input('competencyPoints'),
            $request->input('penalties', [])
        );

        return response()->json($result);
    }

    public function storeIncentive(Request $request, Employee $employee): JsonResponse
    {
        $request->validate([
            'year'              => 'required|integer',
            'month'             => 'required|integer|between:1,12',
            'actualWorkingDays' => 'required|integer',
            'competencyPoints'  => 'required|integer',
            'totalPoints'       => 'required|integer',
            'netWorkingDays'    => 'required|integer',
            'penalties'         => 'array',
        ]);

        $incentive = \App\Models\EmployeeIncentive::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'year'        => $request->input('year'),
                'month'       => $request->input('month'),
            ],
            [
                'actual_working_days' => $request->input('actualWorkingDays'),
                'competency_points'  => $request->input('competencyPoints'),
                'total_points'       => $request->input('totalPoints'),
                'net_working_days'    => $request->input('netWorkingDays'),
                'penalties'          => $request->input('penalties'),
            ]
        );

        return response()->json($incentive, 201);
    }

    public function incentivesHistory(Request $request): JsonResponse
    {
        $year    = $request->get('year', now()->year);
        $month   = $request->get('month');
        $branchId = $request->get('branchId');

        $query = \App\Models\EmployeeIncentive::with(['employee.branch']);

        if ($year) $query->where('year', $year);
        if ($month) $query->where('month', $month);
        
        if ($branchId || (auth()->check() && auth()->user()->branch_id)) {
            $bid = auth()->user()->branch_id ?: $branchId;
            $query->whereHas('employee', function($q) use ($bid) {
                $q->where('branch_id', $bid);
            });
        }

        $items = $query->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get()
            ->map(function($i) {
                return [
                    'id'                => $i->id,
                    'employeeName'      => $i->employee->full_name,
                    'branch'            => $i->employee->branch?->name,
                    'year'              => $i->year,
                    'month'             => $i->month,
                    'totalPoints'       => $i->total_points,
                    'netWorkingDays'    => $i->net_working_days,
                    'actualWorkingDays' => $i->actual_working_days,
                    'competencyPoints'  => $i->competency_points,
                    'penalties'         => $i->penalties,
                    'createdAt'         => $i->created_at,
                ];
            });

        return response()->json($items);
    }
    public function topProducers(Request $request): JsonResponse
    {
        $year     = $request->get('year', now()->year);
        $branchId = $request->get('branchId');
        $limit    = (int) $request->get('limit', 10);

        $query = \App\Models\Employee::with(['branch'])
            ->withSum(['policies' => function($query) use ($year) {
                $query->whereYear('issue_date', $year);
            }], 'amount')
            ->withCount(['policies' => function($query) use ($year) {
                $query->whereYear('issue_date', $year);
            }]);

        // Branch filter
        $userBranchId = (auth()->check() && auth()->user()->branch_id) ? auth()->user()->branch_id : $branchId;
        if ($userBranchId) {
            $query->where('branch_id', $userBranchId);
        }

        $topEmployees = $query->orderBy('policies_sum_amount', 'desc')
            ->take($limit)
            ->get();

        return response()->json($topEmployees->map(function($emp) {
            return [
                'id'          => $emp->id,
                'fullName'    => $emp->full_name,
                'branch'      => $emp->branch?->name,
                'totalAmount' => (float) ($emp->policies_sum_amount ?? 0),
                'totalCount'  => $emp->policies_count,
                'avatar'      => $emp->avatar,
            ];
        }));
    }
}
