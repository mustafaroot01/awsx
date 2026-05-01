<?php

namespace App\Http\Controllers;

use App\Models\Rank;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RankController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $type = $request->get('type');
        $onlyActive = $request->boolean('active', false);

        $query = Rank::query();

        if ($type) {
            $query->where('type', $type);
        }

        if ($onlyActive) {
            $query->active();
        }

        $ranks = $query->orderBy('sort_order')->orderBy('name')->get();

        return response()->json($ranks);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ranks,name',
            'type' => 'required|in:admin,producer',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $rank = Rank::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return response()->json($rank, 201);
    }

    public function update(Request $request, Rank $rank): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:ranks,name,' . $rank->id,
            'type' => 'required|in:admin,producer',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $rank->update([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'sort_order' => $validated['sort_order'] ?? $rank->sort_order,
            'is_active' => $validated['is_active'] ?? $rank->is_active,
        ]);

        return response()->json($rank);
    }

    public function destroy(Rank $rank): JsonResponse
    {
        // Check if rank is used by any employee before deleting
        $count = \App\Models\Employee::where('rank', $rank->name)->count();
        if ($count > 0) {
            return response()->json([
                'message' => "لا يمكن الحذف، هذا العنوان مستخدم من {$count} موظف. قم بتغييره أولاً."
            ], 422);
        }

        $rank->delete();

        return response()->json(['message' => 'تم الحذف بنجاح']);
    }
}
