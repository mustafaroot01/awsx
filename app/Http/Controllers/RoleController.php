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
        // Fully lock إدارة النظام
        if ($role->name === 'إدارة النظام') {
            return response()->json(['message' => 'لا يمكن تعديل مجموعة إدارة النظام.'], 422);
        }

        $request->validate([
            'name'        => 'required|string|unique:roles,name,' . $role->id,
            'permissions' => 'array',
        ]);

        // Prevent renaming الصلاحية الافتراضية
        if ($role->name === 'الصلاحية الافتراضية' && $request->name !== 'الصلاحية الافتراضية') {
            return response()->json(['message' => 'لا يمكن تغيير اسم الصلاحية الافتراضية.'], 422);
        }

        $role->update(['name' => $request->name]);

        if ($request->has('permissions')) {
            $permIds = $request->permissions;

            // Ensure read.Auth is always kept in الصلاحية الافتراضية
            if ($role->name === 'الصلاحية الافتراضية') {
                $authPerm = \App\Models\Permission::where('slug', 'read.Auth')->first();
                if ($authPerm && !in_array($authPerm->id, $permIds)) {
                    $permIds[] = $authPerm->id;
                }
            }

            $role->permissions()->sync($permIds);
        }

        return response()->json($role->load('permissions'));
    }

    public function destroy(Role $role): JsonResponse
    {
        if (in_array($role->name, ['إدارة النظام', 'الصلاحية الافتراضية'])) {
            return response()->json(['message' => 'لا يمكن حذف هذه المجموعة لأنها أساسية للنظام.'], 422);
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
