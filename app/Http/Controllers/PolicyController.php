<?php

namespace App\Http\Controllers;

use App\Http\Resources\PolicyResource;
use App\Models\LifePolicyDetail;
use App\Models\Policy;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Services\DocumentGeneratorService;
use App\Services\PolicyService;

class PolicyController extends Controller
{
    protected $docService;
    protected $policyService;

    public function __construct(DocumentGeneratorService $docService, PolicyService $policyService)
    {
        $this->docService    = $docService;
        $this->policyService = $policyService;
    }

    public function downloadPdf(Policy $policy)
    {
        $policy->load(['lifeDetails', 'fireTheftDetails', 'inspectionReports', 'beneficiaries', 'fundsSchedule', 'companyDetails']);
        
        $pdfContent = $this->docService->generatePolicyDocument($policy);

        return response($pdfContent)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="policy_' . $policy->policy_no . '.pdf"');
    }
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
        $policy->load(['branch', 'employee', 'lifeDetails', 'fireTheftDetails', 'companyDetails', 'beneficiaries', 'fundsSchedule', 'inspectionReports']);

        return response()->json(new PolicyResource($policy));
    }

    public function store(Request $request): JsonResponse
    {
        return \DB::transaction(function () use ($request) {
            // Branch Manager Restriction
            $branchId = (auth()->check() && auth()->user()->branch_id) 
                ? auth()->user()->branch_id 
                : $request->input('branchId');

            $policy = Policy::create([
                'policy_no'         => $request->input('policyNo'),
                'category'          => $request->input('category'),
                'status'            => $request->input('status', 'active'),
                'client_name'       => $request->input('clientName'),
                'trade_name'        => $request->input('trade_name'),
                'permanent_address' => $request->input('permanent_address'),
                'phone'             => $request->input('phone'),
                'occupation'        => $request->input('occupation'),
                'district'          => $request->input('district'),
                'mahalla'           => $request->input('mahalla'),
                'zuqaq'             => $request->input('zuqaq'),
                'dar'               => $request->input('dar'),
                'shop_no'           => $request->input('shop_no'),
                'street_region'     => $request->input('street_region'),
                'shop_phone'        => $request->input('shop_phone'),
                'amount'            => $request->input('amount'),
                'issue_date'        => $request->input('issueDate'),
                'expiry_date'       => $request->input('expiryDate'),
                'branch_id'         => $branchId,
                'employee_id'       => $request->input('employeeId', auth()->id()),
                'notes'             => $request->input('notes'),
                'source_of_funds'   => is_array($request->input('source_of_funds')) ? implode(',', $request->input('source_of_funds')) : $request->input('source_of_funds'),
                'monthly_income'    => $request->input('monthly_income'),
                'business_type'     => $request->input('business_type'),
                'aml_officer_name'  => $request->input('aml_officer_name'),
            ]);

            // 1. Life Specific
            if ($request->input('category') === 'life' && $request->has('lifeDetails')) {
                $ld = $request->input('lifeDetails');
                $policy->lifeDetails()->create([
                    'payment_cycle'           => $ld['paymentCycle'] ?? 'annual',
                    'accident_fee'            => $ld['accidentFee'] ?? 0,
                    'duration_years'          => $ld['durationYears'] ?? 2,
                    'marital_status'          => $ld['marital_status'] ?? null,
                    'id_card_no'              => $ld['id_card_no'] ?? null,
                    'issue_authority_date'    => $ld['issue_authority_date'] ?? null,
                    'spouse_name'             => $ld['spouse_name'] ?? null,
                    'work_address'            => $ld['work_address'] ?? null,
                    'home_address_detail'     => $ld['home_address_detail'] ?? null,
                    'height_cm'               => $ld['height_cm'] ?? null,
                    'weight_kg'               => $ld['weight_kg'] ?? null,
                    'weight_change_last_year' => $ld['weight_change_last_year'] ?? null,
                    'health_questionnaire'    => $ld['health_questionnaire'] ?? [],
                ]);
            }

            // 2. Fire/Theft Specific
            if ($request->input('category') === 'fire_theft' && $request->has('fireTheftDetails')) {
                $policy->fireTheftDetails()->create($request->input('fireTheftDetails'));
            }

            // 3. Inspection Report
            if ($request->has('inspection')) {
                $inspection = $request->input('inspection');
                $policy->inspectionReports()->create([
                    'construction_description' => $inspection['construction_description'],
                    'wall_material'            => $inspection['wall_material'],
                    'roof_material'            => $inspection['roof_material'],
                    'floor_material'           => $inspection['floor_material'],
                    'neighbors_connectivity'   => $inspection['neighbors_connectivity'],
                    'neighbors_nature'         => $inspection['neighbors_nature'],
                    'doors_locks_type'         => $inspection['doors_locks_type'],
                    'window_grids'             => $inspection['window_grids'],
                    'lighting_type'            => $inspection['lighting_heating'],
                    'lighting_heating'         => $inspection['lighting_heating'],
                    'machine_fuel'             => $inspection['machine_fuel'],
                    'wood_layers'              => $inspection['wood_layers'],
                    'water_source'             => $inspection['water_source'],
                    'fire_extinguishers_info'  => $inspection['extinguishers'],
                    'extinguishers'            => $inspection['extinguishers'],
                    'electrical_state'         => $inspection['electrical_state'],
                    'hazardous_observation'    => $inspection['hazardous_observation'],
                    'waste_disposal'           => $inspection['waste_disposal'],
                    'sketch_path'              => $inspection['sketch_path'],
                ]);
            }

            // 4. Beneficiaries
            if ($request->has('beneficiaries')) {
                foreach ($request->input('beneficiaries') as $ben) {
                    if (!empty($ben['name_quad'])) {
                        $policy->beneficiaries()->create($ben);
                    }
                }
            }

            // 5. Funds Schedule
            if ($request->has('funds')) {
                foreach ($request->input('funds') as $cat => $data) {
                    if ($data['value'] > 0) {
                        $policy->fundsSchedule()->create([
                            'category'    => $cat,
                            'value'       => $data['value'],
                            'description' => $data['description'],
                        ]);
                    }
                }
            }

            // 6. Company Details
            if ($request->has('companyDetails')) {
                $policy->companyDetails()->create($request->input('companyDetails'));
            }

            $policy->load(['branch', 'lifeDetails', 'fireTheftDetails', 'inspectionReports', 'beneficiaries', 'fundsSchedule', 'companyDetails']);

            // Update production plan achievement
            $this->policyService->handlePolicyCreated($policy);

            return response()->json(new PolicyResource($policy), 201);
        });
    }

    public function update(Request $request, Policy $policy): JsonResponse
    {
        return \DB::transaction(function () use ($request, $policy) {
            $branchId = (auth()->check() && auth()->user()->branch_id)
                ? auth()->user()->branch_id
                : $request->input('branchId', $policy->branch_id);

            // Snapshot before update for achievement reconciliation
            $oldAmount = (float) $policy->amount;
            $oldStatus = $policy->status;

            $policy->update([
                'policy_no'         => $request->input('policyNo',   $policy->policy_no),
                'category'          => $request->input('category',   $policy->category),
                'status'            => $request->input('status',     $policy->status),
                'client_name'       => $request->input('clientName', $policy->client_name),
                'trade_name'        => $request->input('trade_name', $policy->trade_name),
                'permanent_address' => $request->input('permanent_address', $policy->permanent_address),
                'phone'             => $request->input('phone',      $policy->phone),
                'occupation'        => $request->input('occupation', $policy->occupation),
                'district'          => $request->input('district',   $policy->district),
                'mahalla'           => $request->input('mahalla',    $policy->mahalla),
                'zuqaq'             => $request->input('zuqaq',      $policy->zuqaq),
                'dar'               => $request->input('dar',        $policy->dar),
                'shop_no'           => $request->input('shop_no',    $policy->shop_no),
                'street_region'     => $request->input('street_region', $policy->street_region),
                'shop_phone'        => $request->input('shop_phone', $policy->shop_phone),
                'amount'            => $request->input('amount',     $policy->amount),
                'issue_date'        => $request->input('issueDate',  $policy->issue_date),
                'expiry_date'       => $request->input('expiryDate', $policy->expiry_date),
                'branch_id'         => $branchId,
                'employee_id'       => $request->input('employeeId', $policy->employee_id),
                'notes'             => $request->input('notes',      $policy->notes),
                'source_of_funds'   => is_array($request->input('source_of_funds')) ? implode(',', $request->input('source_of_funds')) : $request->input('source_of_funds', $policy->source_of_funds),
                'monthly_income'    => $request->input('monthly_income', $policy->monthly_income),
                'business_type'     => $request->input('business_type', $policy->business_type),
                'aml_officer_name'  => $request->input('aml_officer_name', $policy->aml_officer_name),
            ]);

            // Sync Nested Relations (simplified for brevity, usually you'd delete/re-create or update by ID)
            if ($request->has('lifeDetails')) {
                $policy->lifeDetails()->updateOrCreate(['policy_id' => $policy->id], $request->input('lifeDetails'));
            }
            if ($request->has('fireTheftDetails')) {
                $policy->fireTheftDetails()->updateOrCreate(['policy_id' => $policy->id], $request->input('fireTheftDetails'));
            }
            if ($request->has('companyDetails')) {
                $policy->companyDetails()->updateOrCreate(['policy_id' => $policy->id], $request->input('companyDetails'));
            }

            // Reconcile production plan achievement (reverses old, adds new)
            $this->policyService->handlePolicyUpdated($policy->fresh(), $oldAmount, $oldStatus);

            return response()->json(new PolicyResource($policy->fresh(['branch', 'lifeDetails', 'fireTheftDetails', 'inspectionReports', 'beneficiaries', 'fundsSchedule', 'companyDetails'])));
        });
    }

    public function destroy(Policy $policy): JsonResponse
    {
        // Reverse achievement before deleting
        $this->policyService->handlePolicyDeleted($policy);

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
