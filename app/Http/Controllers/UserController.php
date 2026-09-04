<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\DepartmentResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(User::class, 'user');
    }

    /**
     * Display a listing of users with search, filter, and pagination.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        return inertia('Users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'role'])
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return inertia('Users/Create', [
            'departments' => DepartmentResolver::options(),
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(\App\Http\Requests\StoreUserRequest $request)
    {
        $validated = $request->validated();

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = now();

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User account created successfully.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $record = $user->only(['id', 'name', 'email', 'phone', 'role', 'department']);
        $record['department'] = DepartmentResolver::toShortcut($user->department) ?? ($user->department ?? '');

        return inertia('Users/Edit', [
            'userRecord' => $record,
            'departments' => DepartmentResolver::options(),
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(\App\Http\Requests\UpdateUserRequest $request, User $user)
    {
        $validated = $request->validated();

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'User account updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('users.index')
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User account deleted successfully.');
    }
}
