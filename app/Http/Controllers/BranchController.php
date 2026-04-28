<?php

namespace App\Http\Controllers;

use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Branch::with(['manager', 'deputy']);

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('governorate', 'LIKE', "%{$q}%")
                    ->orWhere('location', 'LIKE', "%{$q}%");
            });
        }

        if ($governorate = $request->get('governorate')) {
            $query->where('governorate', $governorate);
        }

        $query->orderBy('id', 'asc');

        $itemsPerPage = (int) $request->get('itemsPerPage', 10);
        $page         = (int) $request->get('page', 1);

        if ($itemsPerPage === -1) {
            $items = $query->get();
            $total = $items->count();
        } else {
            $paginated = $query->paginate($itemsPerPage, ['*'], 'page', $page);
            $items     = collect($paginated->items());
            $total     = $paginated->total();
        }

        return response()->json([
            'branches'      => BranchResource::collection($items),
            'totalBranches' => $total,
        ]);
    }

    public function show(Branch $branch): JsonResponse
    {
        return response()->json(new BranchResource($branch));
    }

    private function validateManagerConflict(?int $managerId, ?int $deputyId, ?int $excludeBranchId = null): ?array
    {
        if ($managerId && $managerId === $deputyId) {
            return ['message' => 'لا يمكن أن يكون المستخدم مديراً ومعاوناً في نفس الوقت.'];
        }

        if ($managerId) {
            $conflict = Branch::where(function($q) use ($managerId) {
                    $q->where('manager_id', $managerId)->orWhere('deputy_id', $managerId);
                })
                ->when($excludeBranchId, fn($q) => $q->where('id', '!=', $excludeBranchId))
                ->first();
            if ($conflict) {
                return ['message' => 'هذا المستخدم معيّن بالفعل في فرع "' . $conflict->name . '".'];
            }
        }

        if ($deputyId) {
            $conflict = Branch::where(function($q) use ($deputyId) {
                    $q->where('manager_id', $deputyId)->orWhere('deputy_id', $deputyId);
                })
                ->when($excludeBranchId, fn($q) => $q->where('id', '!=', $excludeBranchId))
                ->first();
            if ($conflict) {
                return ['message' => 'هذا المستخدم (المعاون) معيّن بالفعل في فرع "' . $conflict->name . '".'];
            }
        }

        return null;
    }

    public function store(Request $request): JsonResponse
    {
        // Accept both managerId (frontend) and manager_id (standard)
        $managerId = $request->input('managerId') ?? $request->input('manager_id');
        $deputyId  = $request->input('deputyId')  ?? $request->input('deputy_id');

        if ($error = $this->validateManagerConflict($managerId, $deputyId)) {
            return response()->json($error, 422);
        }

        $branch = Branch::create([
            'name'        => $request->input('name'),
            'location'    => $request->input('location'),
            'governorate' => $request->input('governorate'),
            'manager_id'  => $managerId,
            'deputy_id'   => $deputyId,
        ]);

        // Sync branch_id to the users
        if ($managerId) {
            \App\Models\User::where('id', $managerId)->update(['branch_id' => $branch->id]);
        }
        if ($deputyId) {
            \App\Models\User::where('id', $deputyId)->update(['branch_id' => $branch->id]);
        }

        return response()->json(new BranchResource($branch->load(['manager', 'deputy'])), 201);
    }

    public function update(Request $request, Branch $branch): JsonResponse
    {
        $managerId = $request->input('managerId') ?? $request->input('manager_id') ?? $branch->manager_id;
        $deputyId  = $request->input('deputyId')  ?? $request->input('deputy_id')  ?? $branch->deputy_id;

        if ($error = $this->validateManagerConflict($managerId, $deputyId, $branch->id)) {
            return response()->json($error, 422);
        }

        $branch->update([
            'name'        => $request->input('name',        $branch->name),
            'location'    => $request->input('location',    $branch->location),
            'governorate' => $request->input('governorate', $branch->governorate),
            'manager_id'  => $managerId,
            'deputy_id'   => $deputyId,
        ]);

        // Sync branch_id to the users
        if ($managerId) {
            \App\Models\User::where('id', $managerId)->update(['branch_id' => $branch->id]);
        }
        if ($deputyId) {
            \App\Models\User::where('id', $deputyId)->update(['branch_id' => $branch->id]);
        }

        return response()->json(new BranchResource($branch->fresh(['manager', 'deputy'])));
    }

    public function destroy(Branch $branch): JsonResponse
    {
        $branch->delete();

        return response()->json(null, 204);
    }
    public function comparison(Request $request): JsonResponse
    {
        $year = $request->get('year', now()->year);
        $branchIds = $request->get('branchIds'); // Optional filter
        
        $query = Branch::query();
        
        // Filter by branch if the user is a branch manager
        if (auth()->check() && auth()->user()->branch_id) {
            $query->where('id', auth()->user()->branch_id);
        } elseif ($branchIds) {
            $query->whereIn('id', is_array($branchIds) ? $branchIds : explode(',', $branchIds));
        }
        
        $branches = $query->get();
        
        $data = $branches->map(function($branch) use ($year) {
            $targets = \App\Models\BranchProductionTarget::where('branch_id', $branch->id)
                ->whereHas('plan', function($q) use ($year) {
                    $q->where('year', $year);
                })->get();
            
            $categories = [
                'vehicle'           => 'تأمين السيارات',
                'fire_theft'        => 'الحريق والسرقة',
                'group_health'      => 'الصحي الجماعي',
                'transport_marine'  => 'النقل / البحري',
                'engineering'       => 'التأمين الهندسي',
                'life'              => 'تأمين الحياة',
                'personal_accident' => 'الحوادث الشخصية',
                'cash'              => 'تأمين النقد',
            ];

            $catStats = [];
            $tTotal = 0;
            $aTotal = 0;

            foreach ($categories as $slug => $name) {
                $target = $targets->where('category', $slug)->sum('target_amount');
                $actual = \App\Models\Policy::where('branch_id', $branch->id)
                    ->whereYear('issue_date', $year)
                    ->where('category', $slug)
                    ->sum('amount');
                
                $catStats[$slug] = [
                    'name'   => $name,
                    'target' => $target,
                    'actual' => (float) $actual,
                    'pct'    => $target > 0 ? round(($actual / $target) * 100, 2) : 0,
                ];

                $tTotal += $target;
                $aTotal += $actual;
            }
                
            return [
                'id'          => $branch->id,
                'name'        => $branch->name,
                'governorate' => $branch->governorate,
                'totals'      => [
                    'target'     => $tTotal,
                    'actual'     => (float) $aTotal,
                    'percentage' => $tTotal > 0 ? round(($aTotal / $tTotal) * 100, 2) : 0,
                ],
                'categories' => $catStats
            ];
        });

        return response()->json($data);
    }

    public function roiAnalysis(Request $request): JsonResponse
    {
        $year = $request->get('year', now()->year);

        $branches = Branch::all();

        $result = $branches->map(function ($branch) use ($year) {
            $totalProduction = \App\Models\Policy::where('branch_id', $branch->id)
                ->whereYear('issue_date', $year)
                ->sum('amount');

            $totalIncentives = \App\Models\EmployeeIncentive::whereHas('employee', function($q) use ($branch) {
                    $q->where('branch_id', $branch->id);
                })
                ->where('year', $year)
                ->sum('total_points'); // Assuming 1 point = 1 unit or we just show points

            // If you have a monetary value for points, multiply here.
            // For now, let's just show points vs production amount.

            return [
                'id'              => $branch->id,
                'name'            => $branch->name,
                'production'      => (float) $totalProduction,
                'incentivePoints' => (float) $totalIncentives,
                'ratio'           => $totalProduction > 0 ? round(($totalIncentives / $totalProduction) * 100, 2) : 0,
            ];
        });

        return response()->json($result);
    }
}
