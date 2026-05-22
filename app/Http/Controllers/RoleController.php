<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRoleRequest;
use App\Http\Requests\Admin\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        $roles = Role::withCount(['permissions', 'users'])->orderBy('display_name')->get();
        return view('roles.index', compact('roles'));
    }

    public function create(): View
    {
        $permissions = Permission::orderBy('group')->orderBy('display_name')->get()->groupBy('group');
        return view('roles.create', compact('permissions'));
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $role = Role::create($request->safe()->except('permissions'));

        if ($request->filled('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $permissions = Permission::orderBy('group')->orderBy('display_name')->get()->groupBy('group');
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        return view('roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $role->update($request->safe()->except('permissions'));
        $role->permissions()->sync($request->permissions ?? []);

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
