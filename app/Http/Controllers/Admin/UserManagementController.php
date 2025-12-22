<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['roles'])
            ->withCount(['roles', 'payments']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('slug', $request->get('role'));
            });
        }

        if ($request->filled('status')) {
            $status = $request->get('status') === 'active';
            $query->where('is_active', $status);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);

        $roles = Role::active()->get();
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'inactive_users' => User::where('is_active', false)->count(),
            'admin_users' => User::whereHas('roles', fn($q) => $q->where('slug', 'admin'))->count(),
            'teacher_users' => User::whereHas('roles', fn($q) => $q->where('slug', 'teacher'))->count(),
            'student_users' => User::whereHas('roles', fn($q) => $q->where('slug', 'student'))->count(),
        ];

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'roles' => $roles,
            'stats' => $stats,
            'filters' => $request->only(['search', 'role', 'status'])
        ]);
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $this->authorize('create', User::class);

        $roles = Role::active()->get();

        return Inertia::render('Admin/Users/Create', [
            'roles' => $roles
        ]);
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'bio' => 'nullable|string|max:1000',
            'role' => 'required|string', // Legacy role field
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'custom_permissions' => 'array',
            'custom_permissions.*' => 'exists:permissions,slug',
            'is_active' => 'boolean'
        ]);

        $validated['password'] = Hash::make($validated['password']);
        
        $user = User::create($validated);

        // Assign roles
        if (!empty($validated['roles'])) {
            foreach ($validated['roles'] as $roleId) {
                $user->assignRole($roleId, auth()->id());
            }
        } else {
            // Fallback to legacy role system if no new roles assigned
            $defaultRole = Role::where('slug', $validated['role'])->first();
            if ($defaultRole) {
                $user->assignRole($defaultRole, auth()->id());
            }
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load(['roles.permissions', 'payments']);
        $user->loadCount(['payments', 'verifiedPayments']);

        return Inertia::render('Admin/Users/Show', [
            'user' => $user
        ]);
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $user->load('roles');
        $roles = Role::active()->get();
        $permissions = Permission::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:8|confirmed',
            'bio' => 'nullable|string|max:1000',
            'role' => 'required|string',
            'roles' => 'array',
            'roles.*' => 'exists:roles,id',
            'custom_permissions' => 'array',
            'custom_permissions.*' => 'exists:permissions,slug',
            'dashboard_preferences' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        // Remove password if not provided
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        // Update roles
        if (isset($validated['roles'])) {
            $user->roles()->detach(); // Remove all current roles
            foreach ($validated['roles'] as $roleId) {
                $user->assignRole($roleId, auth()->id());
            }
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        // Prevent deletion of own account
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        // Check if user has important relationships
        if ($user->payments()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete user with payment history. Consider deactivating instead.']);
        }

        if ($user->verifiedPayments()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete admin who has verified payments. Consider deactivating instead.']);
        }

        $user->roles()->detach();
        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        $this->authorize('update', $user);

        // Prevent deactivation of own account
        if ($user->id === auth()->id() && $user->is_active) {
            return back()->withErrors(['error' => 'You cannot deactivate your own account.']);
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activated' : 'deactivated';
        
        return back()->with('success', "User {$status} successfully.");
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $role = Role::find($validated['role_id']);
        
        if (!$user->hasRole($role)) {
            $user->assignRole($role, auth()->id());
        }

        return back()->with('success', 'Role assigned successfully.');
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $role = Role::find($validated['role_id']);
        $user->removeRole($role);

        return back()->with('success', 'Role removed successfully.');
    }

    /**
     * Bulk actions on users
     */
    public function bulkAction(Request $request)
    {
        $this->authorize('update', User::class);

        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id'
        ]);

        $users = User::whereIn('id', $validated['user_ids']);

        // Prevent actions on own account
        $users = $users->where('id', '!=', auth()->id());

        switch ($validated['action']) {
            case 'activate':
                $users->update(['is_active' => true]);
                $message = 'Users activated successfully.';
                break;
            case 'deactivate':
                $users->update(['is_active' => false]);
                $message = 'Users deactivated successfully.';
                break;
            case 'delete':
                // Additional safety check for deletion
                $safeToDelete = $users->whereDoesntHave('payments')
                                   ->whereDoesntHave('verifiedPayments');
                $count = $safeToDelete->count();
                $safeToDelete->get()->each(function ($user) {
                    $user->roles()->detach();
                    $user->delete();
                });
                $message = "{$count} users deleted successfully.";
                break;
        }

        return back()->with('success', $message);
    }
}