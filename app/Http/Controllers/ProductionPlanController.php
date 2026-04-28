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

        if (auth()->check() && auth()->user()->branch_id) {
            $branchId = auth()->user()->branch_id;
            $query->whereHas('branchTargets', fn($q) => $q->where('branch_id', $branchId));
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
        $year = $request->input('year');

        $existingActive = ProductionPlan::where('year', $year)
            ->where('is_locked', false)
            ->first();

        if ($existingActive) {
            return response()->json([
                'message' => "يوجد بالفعل خطة إنتاجية فعّالة لسنة {$year} ('{$existingActive->title}'). أقفل الخطة الحالية أولاً قبل إنشاء خطة جديدة.",
            ], 422);
        }

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

    public function breakthroughs(Request $request): JsonResponse
    {
        $year = $request->get('year', now()->year);

        $plans = ProductionPlan::with(['branchTargets.branch'])
            ->where('year', $year)
            ->get();

        $result = [];

        foreach ($plans as $plan) {
            $allAchievements = $this->calculateAchievements($plan->id);

            foreach ($allAchievements as $branchData) {
                foreach ($branchData['categories'] as $catKey => $catData) {
                    if ($catData['target'] > 0 && $catData['percentage'] >= 100) {
                        $result[] = [
                            'planId'       => $plan->id,
                            'planTitle'    => $plan->title,
                            'year'         => $plan->year,
                            'branchId'     => $branchData['branchId'],
                            'branchName'   => $branchData['branchName'],
                            'category'     => $catKey,
                            'target'       => $catData['target'],
                            'achieved'     => $catData['achieved'],
                            'percentage'   => $catData['percentage'],
                            'surplus'      => $catData['achieved'] - $catData['target'],
                        ];
                    }
                }
            }
        }

        return response()->json([
            'breakthroughs' => $result,
            'total'         => count($result),
            'year'          => $year,
        ]);
    }

    private function calculateAchievements(int $planId): array
    {
        $plan = ProductionPlan::with(['branchTargets.branch'])->find($planId);
        if (!$plan) return [];

        $planCategories  = ['life', 'group_health', 'general_property'];
        $policyCategories = ['life', 'group_health', 'vehicle', 'fire_theft', 'transport_marine', 'engineering', 'personal_accident', 'cash'];
        $categoryMap     = Policy::PLAN_CATEGORY_MAP;
        $result          = [];

        foreach ($plan->branchTargets->groupBy('branch_id') as $branchId => $targets) {
            if (auth()->check() && auth()->user()->branch_id && auth()->user()->branch_id != $branchId) {
                continue;
            }

            $branchName = $targets->first()->branch?->name ?? 'غير محدد';

            // Fetch achieved amounts for all 8 policy categories
            $rawAchieved = Policy::where('branch_id', $branchId)
                ->whereYear('issue_date', $plan->year)
                ->where(function ($q) { $q->where('status', 'active')->orWhere('status', 'acceptance'); })
                ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
                ->groupBy('category')
                ->get()
                ->keyBy('category');

            // Build per-policy-category detail
            $subCategories = [];
            foreach ($policyCategories as $polCat) {
                $subCategories[$polCat] = [
                    'achieved' => (float) ($rawAchieved[$polCat]->total ?? 0),
                    'count'    => (int)   ($rawAchieved[$polCat]->count ?? 0),
                    'group'    => $categoryMap[$polCat] ?? 'general_property',
                ];
            }

            // Aggregate into 3 plan groups
            $achievedByGroup = ['life' => 0.0, 'group_health' => 0.0, 'general_property' => 0.0];
            foreach ($subCategories as $polCat => $data) {
                $achievedByGroup[$data['group']] += $data['achieved'];
            }

            // Build group-level rows with sub-category breakdown
            $categories = [];
            foreach ($planCategories as $planCat) {
                $target = (float) $targets->where('category', $planCat)->sum('target_amount');
                $achieved = $achievedByGroup[$planCat];

                $categories[$planCat] = [
                    'target'     => $target,
                    'achieved'   => $achieved,
                    'percentage' => $target > 0 ? round(($achieved / $target) * 100, 1) : 0,
                    'breakdown'  => collect($subCategories)
                        ->filter(fn($d) => $d['group'] === $planCat)
                        ->map(fn($d, $k) => ['key' => $k, 'achieved' => $d['achieved'], 'count' => $d['count']])
                        ->values()
                        ->toArray(),
                ];
            }

            $result[] = [
                'branchId'   => $branchId,
                'branchName' => $branchName,
                'categories' => $categories,
            ];
        }

        return $result;
    }
}
