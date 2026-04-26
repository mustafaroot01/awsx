<?php

namespace App\Http\Controllers;

use App\Http\Resources\PolicyResource;
use App\Models\LifePolicyDetail;
use App\Models\Policy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PolicyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Policy::with(['branch', 'lifeDetails']);

        // Filter by branch if user is branch manager
        if (auth()->check() && auth()->user()->branch_id) {
            $query->where('branch_id', auth()->user()->branch_id);
        }

        if ($q = $request->get('q')) {
            $query->where(function ($sub) use ($q) {
                $sub->where('policy_no', 'LIKE', "%{$q}%")
                    ->orWhere('client_name', 'LIKE', "%{$q}%");
            });
        }

        if ($category = $request->get('category')) {
            $query->where('category', $category);
        }

        if ($branchId = $request->get('branchId')) {
            $query->where('branch_id', $branchId);
        }

        if ($year = $request->get('year')) {
            $query->whereYear('issue_date', $year);
        }

        $query->orderBy('issue_date', 'desc');

        $itemsPerPage = (int) $request->get('itemsPerPage', 10);
        $page         = (int) $request->get('page', 1);

        if ($itemsPerPage === -1) {
            $items = $query->get();
            $total = $items->count();
            $pages = 1;
        } else {
            $paginated = $query->paginate($itemsPerPage, ['*'], 'page', $page);
            $items     = collect($paginated->items());
            $total     = $paginated->total();
            $pages     = $paginated->lastPage();
        }

        return response()->json([
            'policies'      => PolicyResource::collection($items),
            'totalPolicies' => $total,
            'totalPages'    => $pages,
        ]);
    }

    public function show(Policy $policy): JsonResponse
    {
        $policy->load(['branch', 'employee', 'lifeDetails']);

        return response()->json(new PolicyResource($policy));
    }

    public function store(Request $request): JsonResponse
    {
        // Branch Manager Restriction: Force their own branch
        $branchId = (auth()->check() && auth()->user()->branch_id) 
            ? auth()->user()->branch_id 
            : $request->input('branchId');

        $policy = Policy::create([
            'policy_no'   => $request->input('policyNo'),
            'category'    => $request->input('category'),
            'client_name' => $request->input('clientName'),
            'amount'      => $request->input('amount'),
            'issue_date'  => $request->input('issueDate'),
            'expiry_date' => $request->input('expiryDate'),
            'branch_id'   => $branchId,
            'employee_id' => $request->input('employeeId'),
            'notes'       => $request->input('notes'),
        ]);

        if ($request->input('category') === 'life' && $request->has('lifeDetails')) {
            $ld = $request->input('lifeDetails');
            LifePolicyDetail::create([
                'policy_id'            => $policy->id,
                'payment_cycle'        => $ld['paymentCycle'],
                'accident_fee'         => $ld['accidentFee'] ?? 0,
                'duration_years'       => $ld['durationYears'] ?? 2,
                'id_number'            => $ld['idNumber'] ?? null,
                'birth_date'           => $ld['birthDate'] ?? null,
                'phone'                => $ld['phone'] ?? null,
                'address'              => $ld['address'] ?? null,
                'beneficiary_name'     => $ld['beneficiaryName'] ?? null,
                'beneficiary_relation' => $ld['beneficiaryRelation'] ?? null,
            ]);
        }

        $policy->load(['branch', 'lifeDetails']);

        return response()->json(new PolicyResource($policy), 201);
    }

    public function update(Request $request, Policy $policy): JsonResponse
    {
        // Branch Manager Restriction: Cannot change to another branch
        $branchId = (auth()->check() && auth()->user()->branch_id) 
            ? auth()->user()->branch_id 
            : $request->input('branchId', $policy->branch_id);

        $policy->update([
            'policy_no'   => $request->input('policyNo',   $policy->policy_no),
            'category'    => $request->input('category',   $policy->category),
            'client_name' => $request->input('clientName', $policy->client_name),
            'amount'      => $request->input('amount',     $policy->amount),
            'issue_date'  => $request->input('issueDate',  $policy->issue_date),
            'expiry_date' => $request->input('expiryDate', $policy->expiry_date),
            'branch_id'   => $branchId,
            'employee_id' => $request->input('employeeId', $policy->employee_id),
            'notes'       => $request->input('notes',      $policy->notes),
        ]);

        if ($policy->category === 'life' && $request->has('lifeDetails')) {
            $ld = $request->input('lifeDetails');
            $policy->lifeDetails()->updateOrCreate(
                ['policy_id' => $policy->id],
                [
                    'payment_cycle'        => $ld['paymentCycle'],
                    'accident_fee'         => $ld['accidentFee'] ?? 0,
                    'duration_years'       => $ld['durationYears'] ?? 2,
                    'id_number'            => $ld['idNumber'] ?? null,
                    'birth_date'           => $ld['birthDate'] ?? null,
                    'phone'                => $ld['phone'] ?? null,
                    'address'              => $ld['address'] ?? null,
                    'beneficiary_name'     => $ld['beneficiaryName'] ?? null,
                    'beneficiary_relation' => $ld['beneficiaryRelation'] ?? null,
                ]
            );
        }

        return response()->json(new PolicyResource($policy->fresh(['branch', 'lifeDetails'])));
    }

    public function destroy(Policy $policy): JsonResponse
    {
        $policy->delete();

        return response()->json(null, 204);
    }

    public function expiringSoon(Request $request): JsonResponse
    {
        $days = (int) $request->get('days', 30);
        $userBranchId = (auth()->check() && auth()->user()->branch_id) ? auth()->user()->branch_id : null;

        $query = Policy::with(['branch', 'employee'])
            ->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays($days))
            ->orderBy('expiry_date', 'asc');

        if ($userBranchId) {
            $query->where('branch_id', $userBranchId);
        }

        return response()->json(PolicyResource::collection($query->get()));
    }

    public function kpis(Request $request): JsonResponse
    {
        $branchId = (auth()->check() && auth()->user()->branch_id) ? auth()->user()->branch_id : null;

        $query = Policy::query();
        if ($branchId) $query->where('branch_id', $branchId);

        $todayProduction = (clone $query)->whereDate('issue_date', now())->sum('amount');
        $todayCount      = (clone $query)->whereDate('issue_date', now())->count();
        $totalPolicies   = (clone $query)->count();
        
        $expiringCount   = (clone $query)->where('expiry_date', '>=', now())
            ->where('expiry_date', '<=', now()->addDays(7))
            ->count();

        return response()->json([
            'todayProduction' => (float) $todayProduction,
            'todayCount'      => $todayCount,
            'totalPolicies'   => $totalPolicies,
            'expiringCount'   => $expiringCount,
        ]);
    }

    public function stats(Request $request): JsonResponse
    {
        $year     = $request->get('year', now()->year);
        $branchId = $request->get('branchId');

        $categories = ['vehicle', 'fire_theft', 'group_health', 'transport_marine', 'engineering', 'life', 'personal_accident', 'cash'];

        // Determine branch filter
        $userBranchId = (auth()->check() && auth()->user()->branch_id) ? auth()->user()->branch_id : $branchId;

        $stats = [];
        foreach ($categories as $cat) {
            $baseQuery = Policy::where('category', $cat)->whereYear('issue_date', $year);
            if ($userBranchId) $baseQuery->where('branch_id', $userBranchId);

            $stats[$cat] = [
                'count'  => $baseQuery->count(),
                'amount' => (float) $baseQuery->sum('amount'),
            ];
        }

        // Monthly Trend
        $monthlyTrend = [];
        for ($m = 1; $m <= 12; $m++) {
            $trendQuery = Policy::whereYear('issue_date', $year)->whereMonth('issue_date', $m);
            if ($userBranchId) $trendQuery->where('branch_id', $userBranchId);
            
            $monthlyTrend[] = [
                'month' => $m,
                'amount' => (float) $trendQuery->sum('amount'),
            ];
        }

        $totalQuery = Policy::whereYear('issue_date', $year);
        if ($userBranchId) $totalQuery->where('branch_id', $userBranchId);

        $prevYearQuery = Policy::whereYear('issue_date', $year - 1);
        if ($userBranchId) $prevYearQuery->where('branch_id', $userBranchId);

        return response()->json([
            'year'               => $year,
            'stats'              => $stats,
            'monthlyTrend'       => $monthlyTrend,
            'totalCount'         => $totalQuery->count(),
            'totalAmount'        => (float) $totalQuery->sum('amount'),
            'previousYearAmount' => (float) $prevYearQuery->sum('amount'),
        ]);
    }
}
