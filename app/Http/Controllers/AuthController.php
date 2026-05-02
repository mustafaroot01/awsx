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
                ->distinct()
                ->get()
                ->pluck('slug')
                ->toArray();

            $rules = [];

            // ── Dynamic auto-parse: slug format "action.Subject" ──
            // No hardcoded map needed — any new permission added to DB
            // is automatically reflected in CASL rules on next login.
            foreach ($permissions as $slug) {
                if (str_contains($slug, '.')) {
                    [$action, $subject] = explode('.', $slug, 2);
                    $rules[] = ['action' => $action, 'subject' => $subject];
                }
            }

            // Always add basic Auth permission
            $rules[] = ['action' => 'read', 'subject' => 'Auth'];

            // Super Admins get full access
            $superAdmins = ['mus2afa30@gmail.com', 'admin@admin.com', 'mus@mus.com', 'user@user.com'];
            $isSuperAdmin = in_array($user->email, $superAdmins)
                || $user->roles()->whereIn('name', ['إدارة النظام', 'مدير عام'])->exists();
            
            if ($isSuperAdmin) {
                $rules = [['action' => 'manage', 'subject' => 'all']];
            }

            // Create a unique Sanctum token per device/session (supports multi-device login)
            $deviceName = $request->userAgent() ?? 'unknown';
            $token = $user->createToken($deviceName)->plainTextToken;

            return response()->json([
                'accessToken'      => $token,
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

    public function logout(Request $request): JsonResponse
    {
        // Revoke only the current token (this device), keep other devices logged in
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }
        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    public function refreshRules(Request $request): JsonResponse
    {
        $user = $request->user();

        $permissions = DB::table('permissions')
            ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
            ->join('role_user', 'permission_role.role_id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $user->id)
            ->select('permissions.slug')
            ->distinct()
            ->get()->pluck('slug')->toArray();

        $rules = [];
        foreach ($permissions as $slug) {
            if (str_contains($slug, '.')) {
                [$action, $subject] = explode('.', $slug, 2);
                $rules[] = ['action' => $action, 'subject' => $subject];
            }
        }
        $rules[] = ['action' => 'read', 'subject' => 'Auth'];

        $superAdmins = ['mus2afa30@gmail.com', 'admin@admin.com', 'mus@mus.com', 'user@user.com'];
        $isSuperAdmin = in_array($user->email, $superAdmins)
            || $user->roles()->whereIn('name', ['إدارة النظام', 'مدير عام'])->exists();
        if ($isSuperAdmin) {
            $rules = [['action' => 'manage', 'subject' => 'all']];
        }

        return response()->json([
            'userAbilityRules' => $rules,
            'userData' => [
                'id'        => $user->id,
                'fullName'  => $user->name,
                'username'  => $user->name,
                'email'     => $user->email,
                'avatar'    => null,
                'role'      => ($isSuperAdmin || count($rules) > 10) ? 'admin' : 'client',
                'branch_id' => $user->branch_id,
            ],
        ]);
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
