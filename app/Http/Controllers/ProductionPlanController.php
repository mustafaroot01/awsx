<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductionPlanResource;
use App\Models\BranchProductionTarget;
use App\Models\Policy;
use App\Models\ProductionPlan;
use App\Models\ProductionPlanCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductionPlanController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = ProductionPlan::with(['categories', 'branchTargets.branch']);

        if ($year = $request->get('year')) {
            $query->where('year', $year);
        }

        $plans = $query->orderBy('year', 'desc')->get();

        return response()->json([
            'plans'      => ProductionPlanResource::collection($plans),
            'totalPlans' => $plans->count(),
        ]);
    }

    public function show(ProductionPlan $productionPlan): JsonResponse
    {
        $productionPlan->load(['categories', 'branchTargets.branch']);

        // Calculate achievements from policies
        $achievements = $this->calculateAchievements($productionPlan->id);

        return response()->json([
            'plan'         => new ProductionPlanResource($productionPlan),
            'achievements' => $achievements,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $plan = ProductionPlan::create([
            'year'         => $request->input('year'),
            'title'        => $request->input('title'),
            'total_amount' => $request->input('totalAmount'),
            'is_locked'    => false,
        ]);

        // Store categories
        foreach ($request->input('categories', []) as $cat) {
            ProductionPlanCategory::create([
                'plan_id'       => $plan->id,
                'category'      => $cat['category'],
                'target_amount' => $cat['targetAmount'],
            ]);
        }

        // Store branch targets
        foreach ($request->input('branchTargets', []) as $bt) {
            BranchProductionTarget::create([
                'plan_id'       => $plan->id,
                'branch_id'     => $bt['branchId'],
                'category'      => $bt['category'],
                'target_amount' => $bt['targetAmount'],
            ]);
        }

        $plan->load(['categories', 'branchTargets.branch']);

        return response()->json(new ProductionPlanResource($plan), 201);
    }

    public function update(Request $request, ProductionPlan $productionPlan): JsonResponse
    {
        $productionPlan->update([
            'year'         => $request->input('year',        $productionPlan->year),
            'title'        => $request->input('title',       $productionPlan->title),
            'total_amount' => $request->input('totalAmount', $productionPlan->total_amount),
        ]);

        // Replace categories if provided
        if ($request->has('categories')) {
            $productionPlan->categories()->delete();
            foreach ($request->input('categories') as $cat) {
                ProductionPlanCategory::create([
                    'plan_id'       => $productionPlan->id,
                    'category'      => $cat['category'],
                    'target_amount' => $cat['targetAmount'],
                ]);
            }
        }

        // Replace branch targets if provided
        if ($request->has('branchTargets')) {
            $productionPlan->branchTargets()->delete();
            foreach ($request->input('branchTargets') as $bt) {
                BranchProductionTarget::create([
                    'plan_id'       => $productionPlan->id,
                    'branch_id'     => $bt['branchId'],
                    'category'      => $bt['category'],
                    'target_amount' => $bt['targetAmount'],
                ]);
            }
        }

        $productionPlan->load(['categories', 'branchTargets.branch']);

        return response()->json(new ProductionPlanResource($productionPlan));
    }

    public function destroy(ProductionPlan $productionPlan): JsonResponse
    {
        $productionPlan->delete();

        return response()->json(null, 204);
    }

    public function lock(ProductionPlan $productionPlan): JsonResponse
    {
        $productionPlan->update(['is_locked' => true]);

        return response()->json(['message' => 'تم قفل الخطة بنجاح']);
    }

    public function achievements(ProductionPlan $productionPlan): JsonResponse
    {
        $data = $this->calculateAchievements($productionPlan->id);

        return response()->json($data);
    }

    private function calculateAchievements(int $planId): array
    {
        $plan = ProductionPlan::with(['branchTargets.branch'])->find($planId);
        if (!$plan) return [];

        $planCategories = ['life', 'group_health', 'general_property'];
        $categoryMap    = Policy::PLAN_CATEGORY_MAP;
        $result         = [];

        foreach ($plan->branchTargets->groupBy('branch_id') as $branchId => $targets) {
            // Filter: If user is branch manager, skip other branches
            if (auth()->check() && auth()->user()->branch_id && auth()->user()->branch_id != $branchId) {
                continue;
            }

            $branchName = $targets->first()->branch?->name ?? 'غير محدد';
            $row        = ['branchId' => $branchId, 'branchName' => $branchName, 'categories' => []];

            // Aggregate achieved amount from all 8 policy categories into 3 plan groups
            $achieved = Policy::where('branch_id', $branchId)
                ->whereYear('issue_date', $plan->year)
                ->select('category', DB::raw('SUM(amount) as total'))
                ->groupBy('category')
                ->pluck('total', 'category')
                ->toArray();

            // Map individual categories to plan groups
            $achievedByGroup = ['life' => 0.0, 'group_health' => 0.0, 'general_property' => 0.0];
            foreach ($achieved as $policyCat => $total) {
                $group = $categoryMap[$policyCat] ?? 'general_property';
                $achievedByGroup[$group] += (float) $total;
            }

            foreach ($planCategories as $planCat) {
                $target = (float) $targets->where('category', $planCat)->sum('target_amount');

                $row['categories'][$planCat] = [
                    'target'     => $target,
                    'achieved'   => $achievedByGroup[$planCat],
                    'percentage' => $target > 0 ? round(($achievedByGroup[$planCat] / $target) * 100, 1) : 0,
                ];
            }

            $result[] = $row;
        }

        return $result;
    }
}
