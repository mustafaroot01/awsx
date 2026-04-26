<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(Role::withCount('users')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'        => 'required|string|unique:roles',
            'permissions' => 'array',
        ]);

        $role = Role::create([
            'name' => $request->name,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return response()->json($role->load('permissions'), 201);
    }

    public function show(Role $role): JsonResponse
    {
        return response()->json($role->load('permissions'));
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        // Protect System Administration role
        if ($role->name === 'إدارة النظام') {
            return response()->json(['message' => 'لا يمكن تعديل صلاحيات مجموعة إدارة النظام لأنها تملك صلاحيات مطلقة برمجياً.'], 422);
        }

        $request->validate([
            'name'        => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        $role->update([
            'name' => $request->name,
        ]);

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return response()->json($role->load('permissions'));
    }

    public function destroy(Role $role): JsonResponse
    {
        // Protect System Administration role
        if ($role->name === 'إدارة النظام') {
            return response()->json(['message' => 'لا يمكن حذف مجموعة إدارة النظام لأنها أساسية للنظام.'], 422);
        }

        if ($role->users()->count() > 0) {
            return response()->json(['message' => 'لا يمكن حذف مجموعة مرتبطة بمستخدمين.'], 422);
        }
        
        $role->delete();
        return response()->json(null, 204);
    }

    /**
     * Get all permissions grouped by category
     */
    public function permissions(): JsonResponse
    {
        $permissions = \App\Models\Permission::all()->groupBy('category');
        return response()->json($permissions);
    }
}
