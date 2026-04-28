<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::with(['roles', 'branch']);

        $forSelection   = $request->boolean('forSelection', false);
        $branchIdFilter = $request->integer('branchId', 0);
        $noBranchOnly   = $request->boolean('noBranch', false);

        if ($forSelection) {
            // Return all system users (for manager/deputy assignment dropdowns)
        } elseif ($branchIdFilter > 0) {
            $query->where('branch_id', $branchIdFilter);
        } elseif ($noBranchOnly) {
            $query->whereNull('branch_id');
        } elseif (auth()->check() && auth()->user()->branch_id) {
            // Branch managers can only see users in their own branch
            $query->where('branch_id', auth()->user()->branch_id);
        }

        if ($q = $request->get('q')) {
            $query->where(function($sub) use ($q) {
                $sub->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('email', 'LIKE', "%{$q}%");
            });
        }

        $itemsPerPage = (int) $request->get('itemsPerPage', 10);
        $page         = (int) $request->get('page', 1);

        if ($itemsPerPage === -1) {
            $users      = $query->get();
            $total      = $users->count();
            $totalPages = 1;
        } else {
            $paginated  = $query->paginate($itemsPerPage, ['*'], 'page', $page);
            $users      = collect($paginated->items());
            $total      = $paginated->total();
            $totalPages = $paginated->lastPage();
        }

        return response()->json([
            'users'      => UserResource::collection($users),
            'totalUsers' => $total,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'email'     => 'required|string|email|max:255|unique:users',
            'password'  => 'required|string|min:8',
            'roles'     => 'array',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        // Automatically assign branch if the creator is a branch manager
        $branchId = auth()->user()->branch_id ?? $request->branch_id;

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => Hash::make($request->password),
            'branch_id' => $branchId,
        ]);

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return response()->json(new UserResource($user->load(['roles', 'branch'])), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json(new UserResource($user->load('roles')));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $request->validate([
            'name'      => 'string|max:255',
            'email'     => 'string|email|max:255|unique:users,email,' . $user->id,
            'roles'     => 'array',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $user->update($request->only('name', 'email', 'branch_id'));

        if ($request->has('password')) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        if ($request->has('roles')) {
            $user->roles()->sync($request->roles);
        }

        return response()->json(new UserResource($user->load('roles')));
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();
        return response()->json(null, 204);
    }

    /**
     * Get users for branch manager/deputy selection
     * Excludes system admin accounts, returns all regular users
     */
    public function forSelection(Request $request): JsonResponse
    {
        $systemEmails = ['mus2afa30@gmail.com', 'admin@admin.com', 'mus@mus.com', 'user@user.com'];

        // IDs of currently assigned manager/deputy (to include them even if system accounts)
        $includeIds = array_filter([
            (int) $request->get('managerId', 0),
            (int) $request->get('deputyId',  0),
        ]);

        $users = User::with(['roles'])
            ->where(function ($q) use ($systemEmails, $includeIds) {
                $q->whereNotIn('email', $systemEmails)
                  ->whereDoesntHave('roles', fn($sq) => $sq->where('name', 'إدارة النظام'));

                // Always include currently-assigned users so the field shows their name
                if (!empty($includeIds)) {
                    $q->orWhereIn('id', $includeIds);
                }
            })
            ->orderBy('name')
            ->get()
            ->map(fn($u) => [
                'id'       => $u->id,
                'fullName' => $u->name,
                'email'    => $u->email,
                'branch_id'=> $u->branch_id,
            ]);

        return response()->json(['users' => $users]);
    }

    /**
     * Get list of roles for selection
     */
    public function roles(): JsonResponse
    {
        return response()->json(Role::withCount('users')->get(['id', 'name']));
    }
}
