<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePermissionRequest;
use App\Http\Requests\Admin\UpdatePermissionRequest;
use App\Models\Permission;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::orderBy('group')->orderBy('display_name')->get()->groupBy('group');
        return view('permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        $groups = Permission::distinct()->orderBy('group')->pluck('group');
        return view('permissions.create', compact('groups'));
    }

    public function store(StorePermissionRequest $request): RedirectResponse
    {
        Permission::create($request->validated());
        return redirect()->route('permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission): View
    {
        $groups = Permission::distinct()->orderBy('group')->pluck('group');
        return view('permissions.edit', compact('permission', 'groups'));
    }

    public function update(UpdatePermissionRequest $request, Permission $permission): RedirectResponse
    {
        $permission->update($request->validated());
        return redirect()->route('permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();
        return redirect()->route('permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
