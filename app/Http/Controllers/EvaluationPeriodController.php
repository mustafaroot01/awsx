<?php

namespace App\Http\Controllers;

use App\Http\Resources\EvaluationPeriodResource;
use App\Http\Resources\EvaluationResource;
use App\Models\Evaluation;
use App\Models\EvaluationPeriod;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EvaluationPeriodController extends Controller
{
    public function active(Request $request): JsonResponse
    {
        $query = EvaluationPeriod::where('status', 'open');
        
        if (auth()->check() && auth()->user()->branch_id) {
            $branchId = auth()->user()->branch_id;
            $query->where(function($q) use ($branchId) {
                $q->whereDoesntHave('branches')
                  ->orWhereHas('branches', function($sq) use ($branchId) {
                      $sq->where('branches.id', $branchId);
                  });
            });
        }

        $activePeriod = $query->orderBy('year', 'desc')->orderBy('period_no', 'desc')->first();

        return response()->json([
            'activePeriod' => $activePeriod ? new EvaluationPeriodResource($activePeriod) : null
        ]);
    }

    public function index(Request $request): JsonResponse
    {
        $query = EvaluationPeriod::with(['branches'])->withCount('evaluations');

        // Filter: If user is branch manager, show periods where their branch is included or periods with NO branches (General)
        if (auth()->check() && auth()->user()->branch_id) {
            $branchId = auth()->user()->branch_id;
            $query->where(function($q) use ($branchId) {
                // Period has NO specific branches (General)
                $q->whereDoesntHave('branches')
                  // OR Period includes the user's branch
                  ->orWhereHas('branches', function($sq) use ($branchId) {
                      $sq->where('branches.id', $branchId);
                  });
            });
        }

        if ($year = $request->get('year')) {
            $query->where('year', $year);
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        $periods = $query->orderBy('year', 'desc')->orderBy('period_no', 'asc')->get();

        return response()->json([
            'periods'      => EvaluationPeriodResource::collection($periods),
            'totalPeriods' => $periods->count(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $period = EvaluationPeriod::create([
            'year'       => $request->input('year'),
            'period_no'  => $request->input('periodNo'),
            'start_date' => $request->input('startDate'),
            'end_date'   => $request->input('endDate'),
            'status'     => 'open',
        ]);

        // Sync branches if provided
        if ($branches = $request->input('branchIds')) {
            $period->branches()->sync($branches);
        }

        return response()->json(new EvaluationPeriodResource($period->loadCount('evaluations')->load('branches')), 201);
    }

    public function show(EvaluationPeriod $evaluation_period): JsonResponse
    {
        $evaluation_period->loadCount('evaluations');

        return response()->json(new EvaluationPeriodResource($evaluation_period));
    }

    public function toggleStatus($id): JsonResponse
    {
        $evaluation_period = EvaluationPeriod::findOrFail($id);

        if ($evaluation_period->status === 'locked') {
            return response()->json(['message' => 'لا يمكن تعديل حالة فترة مقفلة نهائياً'], 422);
        }

        $newStatus = ($evaluation_period->status === 'open') ? 'suspended' : 'open';
        $evaluation_period->update(['status' => $newStatus]);

        $message = ($newStatus === 'open') ? 'تم تفعيل الفترة بنجاح' : 'تم تعليق الفترة بنجاح';
        return response()->json(['message' => $message, 'status' => $newStatus]);
    }

    public function lock($id): JsonResponse
    {
        $evaluation_period = EvaluationPeriod::findOrFail($id);
        $evaluation_period->update(['status' => 'locked']);
        return response()->json(['message' => 'تم قفل فترة التقييم بنجاح']);
    }

    public function destroy(EvaluationPeriod $evaluation_period): JsonResponse
    {
        if ($evaluation_period->status === 'locked') {
            return response()->json(['message' => 'لا يمكن حذف فترة مقفلة'], 422);
        }

        $evaluation_period->delete();

        return response()->json(null, 204);
    }

    // Evaluations within a period
    public function evaluations(Request $request, $id): JsonResponse
    {
        $evaluation_period = EvaluationPeriod::findOrFail($id);
        $query = Evaluation::with(['employee', 'branch'])
            ->where('period_id', $evaluation_period->id);

        if ($branchId = $request->get('branchId')) {
            $query->where('branch_id', $branchId);
        }

        $evaluations = $query->get();

        return response()->json([
            'evaluations' => EvaluationResource::collection($evaluations),
            'total'       => $evaluations->count(),
        ]);
    }

    public function storeEvaluation(Request $request, $id): JsonResponse
    {
        $evaluation_period = EvaluationPeriod::findOrFail($id);

        if ($evaluation_period->status !== 'open') {
            return response()->json(['message' => 'لا يمكن إضافة تقييم في هذه الحالة'], 422);
        }

        $evaluation = Evaluation::updateOrCreate(
            [
                'period_id'   => $evaluation_period->id,
                'employee_id' => $request->input('employeeId'),
            ],
            [
                'branch_id'             => $request->input('branchId'),
                'score_attendance'      => $request->input('scoreAttendance', 0),
                'score_performance'     => $request->input('scorePerformance', 0),
                'score_behavior'        => $request->input('scoreBehavior', 0),
                'score_production'      => $request->input('scoreProduction', 0),
                'score_teamwork'        => $request->input('scoreTeamwork', 0),
                'notes'                 => $request->input('notes'),
                'efficiency_experience' => $request->input('efficiencyExperience'),
                'speed_of_achievement'  => $request->input('speedOfAchievement'),
                'sense_of_responsibility' => $request->input('senseOfResponsibility'),
                'behavior_with_others'  => $request->input('behaviorWithOthers'),
                'attendance_commitment' => $request->input('attendanceCommitment'),
                'appreciation_penalties' => $request->input('appreciationPenalties'),
                'points_competency'     => $request->input('pointsCompetency', 23),
                'actual_working_days'   => $request->input('actualWorkingDays', 60),
            ]
        );

        return response()->json(new EvaluationResource($evaluation->load(['employee', 'branch'])), 201);
    }

    public function destroyEvaluation($id, Evaluation $evaluation): JsonResponse
    {
        $evaluation_period = EvaluationPeriod::findOrFail($id);

        if ($evaluation_period->status !== 'open') {
            return response()->json(['message' => 'لا يمكن تعديل التقييمات في هذه الحالة'], 422);
        }

        $evaluation->delete();

        return response()->json(null, 204);
    }
}
