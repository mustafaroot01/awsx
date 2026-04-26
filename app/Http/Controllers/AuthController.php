<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            $user = User::where('email', $request->email)->first();

            if (!$user || !Hash::check($request->password, $user->password)) {
                return response()->json([
                    'errors' => [
                        'email' => ['بيانات الاعتماد غير صحيحة.'],
                    ],
                ], 422);
            }

            // Fetch permissions from database via roles
            $permissions = DB::table('permissions')
                ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
                ->join('role_user', 'permission_role.role_id', '=', 'role_user.role_id')
                ->where('role_user.user_id', $user->id)
                ->select('permissions.slug')
                ->get()
                ->pluck('slug')
                ->toArray();

            $rules = [];

            // Precise slug → CASL mapping table
            $slugMap = [
                // Employees
                'view_employees'   => ['read',   'Employee'],
                'add_employee'     => ['create', 'Employee'],
                'edit_employee'    => ['update', 'Employee'],
                'delete_employee'  => ['delete', 'Employee'],
                'export_employees' => ['read',   'Employee'],
                // Evaluations
                'view_evaluations' => ['read',   'Evaluation'],
                'add_evaluation'   => ['create', 'Evaluation'],
                'edit_evaluation'  => ['update', 'Evaluation'],
                'delete_evaluation'=> ['delete', 'Evaluation'],
                'print_evaluation' => ['print',  'Evaluation'],
                // Branches
                'view_branches'    => ['read',   'Branch'],
                'add_branch'       => ['create', 'Branch'],
                'edit_branch'      => ['update', 'Branch'],
                'delete_branch'    => ['delete', 'Branch'],
                // Users
                'view_users'       => ['read',   'User'],
                'add_user'         => ['create', 'User'],
                'edit_user'        => ['update', 'User'],
                'delete_user'      => ['delete', 'User'],
                // Roles
                'view_roles'       => ['read',   'Role'],
                'add_role'         => ['create', 'Role'],
                'edit_role'        => ['update', 'Role'],
                'delete_role'      => ['delete', 'Role'],
                // Production Plans
                'view_plans'       => ['read',   'ProductionPlan'],
                'add_plan'         => ['create', 'ProductionPlan'],
                'edit_plan'        => ['update', 'ProductionPlan'],
                'delete_plan'      => ['delete', 'ProductionPlan'],
                // Policies
                'view_policies'    => ['read',   'Policy'],
                'add_policy'       => ['create', 'Policy'],
                'edit_policy'      => ['update', 'Policy'],
                'delete_policy'    => ['delete', 'Policy'],
                // Dashboard
                'view_dashboard'   => ['read',   'Auth'],
            ];

            foreach ($permissions as $slug) {
                if (isset($slugMap[$slug])) {
                    [$action, $subject] = $slugMap[$slug];
                    $rules[] = ['action' => $action, 'subject' => $subject];
                }
            }

            // Always add basic Auth permission
            $rules[] = ['action' => 'read', 'subject' => 'Auth'];

            // Super Admins get full access
            $superAdmins = ['mus2afa30@gmail.com', 'admin@admin.com', 'mus@mus.com', 'user@user.com'];
            $isSuperAdmin = in_array($user->email, $superAdmins) || $user->roles()->where('name', 'إدارة النظام')->exists();
            
            if ($isSuperAdmin) {
                $rules = [['action' => 'manage', 'subject' => 'all']];
            }

            return response()->json([
                'accessToken'      => 'token-' . bin2hex(random_bytes(16)),
                'userData'         => [
                    'id'        => $user->id,
                    'fullName'  => $user->name,
                    'username'  => $user->name,
                    'email'     => $user->email,
                    'avatar'    => null,
                    'role'      => ($isSuperAdmin || count($rules) > 10) ? 'admin' : 'client',
                    'branch_id' => $user->branch_id,
                ],
                'userAbilityRules' => $rules,
            ]);
        } catch (ValidationException $e) {
            throw $e; // Let Laravel handle validation errors (422)
        } catch (\Exception $e) {
            Log::error('Auth Error: ' . $e->getMessage());
            return response()->json(['message' => 'حدث خطأ في الخادم.'], 500);
        }
    }

    public function logout(): JsonResponse
    {
        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    public function debugRules(Request $request): JsonResponse
    {
        $user = $request->user();
        $permissions = DB::table('permissions')
            ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
            ->join('role_user', 'permission_role.role_id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->select('permissions.slug')
            ->get()
            ->pluck('slug');

        $rules = [];
        foreach ($permissions as $slug) {
            $parts = explode('_', $slug);
            $action_raw = $parts[0] ?? '';
            $subject_raw = $parts[1] ?? '';

            $action = 'read';
            if ($action_raw === 'add' || $action_raw === 'create') $action = 'create';
            if ($action_raw === 'edit' || $action_raw === 'update') $action = 'update';
            if ($action_raw === 'delete') $action = 'delete';
            if ($action_raw === 'print') $action = 'print';

            $subject = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $subject_raw)));
            if (str_ends_with($subject, 's') && !in_array($subject, ['Auth', 'Settings', 'Logs'])) {
                $subject = rtrim($subject, 's');
            }
            
            $specialCases = [
                'Dashboard' => 'Auth',
                'Emp'       => 'Employee',
                'Eval'      => 'Evaluation',
            ];
            if (isset($specialCases[$subject])) $subject = $specialCases[$subject];

            $rules[] = [
                'action'  => $action,
                'subject' => $subject ?: 'Auth',
            ];
        }
        $rules[] = ['action' => 'read', 'subject' => 'Auth'];

        return response()->json([
            'email' => $user->email,
            'permissions' => $permissions,
            'rules' => $rules,
        ]);
    }
}
