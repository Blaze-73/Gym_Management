<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Coach;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::with(['memberships' => function ($q) {
            $q->orderByDesc('created_at')->limit(1)->with('plan');
        }]);

        // Filter by role
        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        // Search by name or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = min(max((int) $request->get('per_page', 50), 1), 100);
        $users = $query->orderByDesc('created_at')->paginate($perPage);

        return response()->json($users);
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:admin,client,coach',
            'phone' => 'nullable|string|max:20', // Added phone here for creation
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        if ($user->role === 'coach') {
            Coach::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'specialization' => 'Personal Training',
                    'is_available' => true,
                ]
            );
        }

        $user->load(['memberships' => fn ($q) => $q->latest()->limit(1)->with('plan'), 'coach']);

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
        ], 201);
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['memberships.plan']);

        return response()->json([
            'user' => $user,
            'stats' => [
                'total_memberships' => $user->memberships()->count(),
                'active_memberships' => $user->memberships()
                    ->where('status', 'active')
                    ->where('end_date', '>', now())
                    ->count(),
                'expired_memberships' => $user->memberships()
                    ->where('end_date', '<=', now())
                    ->count(),
            ]
        ]);
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'sometimes|in:admin,client,coach',
            'password' => 'sometimes|string|min:8',
            'phone' => 'sometimes|string|max:20', // ✅ ADDED: Now phone can be updated
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        if ($user->role === 'coach') {
            Coach::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $user->name,
                    'specialization' => 'Personal Training',
                    'is_available' => true,
                ]
            );
        }

        return response()->json([
            'message' => 'User updated successfully',
            'user' => $user
        ]);
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself
        if ($user->id === auth()->id()) {
            return response()->json([
                'message' => 'You cannot delete your own account'
            ], 422);
        }

        // Delete user tokens and memberships
        $user->tokens()->delete();
        $user->memberships()->delete();
        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
    }
}
