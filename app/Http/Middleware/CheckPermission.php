<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return \Symfony\Component\HttpFoundation\Response
     */
    /**
     * Permissions automatically granted to any user assigned as branch manager/deputy
     * (i.e. users who have branch_id set on their account)
     */
    private const BRANCH_MANAGER_AUTO_PERMISSIONS = [
        'read.ProductionPlan',
        'read.Statistics',
        'read.Policy',
        'read.Branch',
        'read.Employee',
        'read.Evaluation',
        'create.Evaluation',
        'update.Evaluation',
        'print.Evaluation',
    ];

    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'يجب تسجيل الدخول أولاً.',
            ], 401);
        }

        // Any user assigned to a branch (manager_id / deputy_id) gets branch-manager permissions automatically
        if ($user->branch_id && in_array($permission, self::BRANCH_MANAGER_AUTO_PERMISSIONS)) {
            return $next($request);
        }

        if (!$user->hasPermission($permission)) {
            return response()->json([
                'message' => "ليس لديك صلاحية «{$permission}» لتنفيذ هذا الإجراء.",
                'required_permission' => $permission,
            ], 403);
        }

        return $next($request);
    }
}
