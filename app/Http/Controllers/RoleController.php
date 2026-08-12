<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\PermissionGroup;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount(['permissions', 'users', 'permissionGroups'])->orderBy('display_name')->get();
        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $majorGroups = PermissionGroup::with(['permissions' => function ($q) {
            $q->orderBy('group')->orderBy('display_name');
        }])->get();

        return view('roles.create', compact('majorGroups'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create($request->safe()->except(['permissions', 'permission_groups']));

        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        if ($request->filled('permission_groups')) {
            $role->permissionGroups()->sync($request->permission_groups);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $majorGroups = PermissionGroup::with(['permissions' => function ($q) {
            $q->orderBy('group')->orderBy('display_name');
        }])->get();

        $rolePermissions   = $role->permissions->pluck('id')->toArray();
        $roleMajorGroups   = $role->permissionGroups->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'majorGroups', 'rolePermissions', 'roleMajorGroups'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update($request->safe()->except(['permissions', 'permission_groups']));
        $role->permissions()->sync($request->permissions ?? []);
        $role->permissionGroups()->sync($request->permission_groups ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return redirect()->route('roles.index')
                ->with('error', 'Cannot delete a role that has users assigned to it.');
        }

        $role->delete();
        return redirect()->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}
