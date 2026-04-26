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

        // Filter: If logged-in user has a branch_id, they ONLY see users in their branch
        if (auth()->check() && auth()->user()->branch_id) {
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
     * Get list of roles for selection
     */
    public function roles(): JsonResponse
    {
        return response()->json(Role::withCount('users')->get(['id', 'name']));
    }
}
