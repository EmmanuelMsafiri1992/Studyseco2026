<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RoleController extends Controller
{
    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        // Temporarily disable authorization for admin users
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }
        // $this->authorize('viewAny', Role::class);

        $roles = Role::with(['permissions', 'users'])
            ->withCount(['users'])
            ->orderBy('priority', 'desc')
            ->paginate(15);

        $permissionCategories = Permission::getCategories();

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'permissionCategories' => $permissionCategories
        ]);
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        // Temporarily disable authorization for admin users
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }
        // $this->authorize('create', Role::class);

        $permissions = Permission::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return Inertia::render('Admin/Roles/Create', [
            'permissions' => $permissions
        ]);
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        // Temporarily disable authorization for admin users
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }
        // $this->authorize('create', Role::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles',
            'slug' => 'required|string|max:255|unique:roles',
            'description' => 'nullable|string|max:1000',
            'priority' => 'required|integer|min:1|max:100',
            'dashboard_features' => 'nullable|array',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'is_active' => 'boolean'
        ]);

        $role = Role::create($validated);

        if (!empty($validated['permissions'])) {
            $role->permissions()->attach($validated['permissions']);
        }

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        // Temporarily disable authorization for admin users
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }
        // $this->authorize('view', $role);

        $role->load(['permissions', 'users.roles']);

        return Inertia::render('Admin/Roles/Show', [
            'role' => $role
        ]);
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        // Temporarily disable authorization for admin users
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }
        // $this->authorize('update', $role);

        $role->load('permissions');

        $permissions = Permission::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Temporarily disable authorization for admin users
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }
        // $this->authorize('update', $role);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:roles,name,' . $role->id,
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'nullable|string|max:1000',
            'priority' => 'required|integer|min:1|max:100',
            'dashboard_features' => 'nullable|array',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
            'is_active' => 'boolean'
        ]);

        $role->update($validated);

        // Sync permissions
        $role->permissions()->sync($validated['permissions'] ?? []);

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        // Temporarily disable authorization for admin users
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }
        // $this->authorize('delete', $role);

        if ($role->users()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete role that has assigned users.']);
        }

        if (in_array($role->slug, ['super-admin', 'admin', 'teacher', 'student'])) {
            return back()->withErrors(['error' => 'Cannot delete system default roles.']);
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()
            ->route('admin.roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Toggle role status
     */
    public function toggle(Role $role)
    {
        // Temporarily disable authorization for admin users
        if (auth()->user()->role !== 'admin') {
            abort(403, 'Access denied. Insufficient permissions.');
        }
        // $this->authorize('update', $role);

        $role->update(['is_active' => !$role->is_active]);

        $status = $role->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Role {$status} successfully.");
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        $user->assignRole($role, auth()->id());

        return back()->with('success', 'Role assigned successfully.');
    }

    /**
     * Revoke role from user
     */
    public function revokeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = \App\Models\User::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);

        $user->removeRole($role);

        return back()->with('success', 'Role revoked successfully.');
    }
}
