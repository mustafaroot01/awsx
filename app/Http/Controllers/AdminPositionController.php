<?php

namespace App\Http\Controllers;

use App\Models\AdminPosition;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPositionController extends Controller
{
    public function index(): JsonResponse
    {
        $positions = AdminPosition::orderBy('sort_order')->orderBy('name')->get();

        return response()->json($positions);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'points' => 'required|integer',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $position = AdminPosition::create($validated);

        return response()->json($position, 201);
    }

    public function show(AdminPosition $adminPosition): JsonResponse
    {
        return response()->json($adminPosition);
    }

    public function update(Request $request, AdminPosition $adminPosition): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'points' => 'required|integer',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $adminPosition->update($validated);

        return response()->json($adminPosition);
    }

    public function destroy(AdminPosition $adminPosition): JsonResponse
    {
        $adminPosition->delete();

        return response()->json(['message' => 'Admin position deleted successfully']);
    }
}
