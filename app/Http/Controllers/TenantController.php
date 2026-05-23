<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Unit;
use App\Http\Requests\StoreTenantRequest;
use App\Http\Requests\UpdateTenantRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantController extends Controller
{
    public function index(Request $request): View
    {
        $tenants = Tenant::with('unit')
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->status === 'active', fn($q) => $q->active())
            ->when($request->status === 'inactive', fn($q) => $q->inactive())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('tenants.index', [
            'title' => 'Tenant Management',
            'tenants' => $tenants,
        ]);
    }

    public function create(): View
    {
        $units = Unit::where('status', 'vacant')
            ->orderBy('unit_number')
            ->get();

        return view('tenants.create', [
            'title' => 'Add New Tenant',
            'units' => $units,
        ]);
    }

    public function store(StoreTenantRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Handle CNIC front image
        if ($request->hasFile('cnic_front_image')) {
            $data['cnic_front_image'] = $request->file('cnic_front_image')
                ->store('tenants/cnic', 'public');
        }

        // Handle CNIC back image
        if ($request->hasFile('cnic_back_image')) {
            $data['cnic_back_image'] = $request->file('cnic_back_image')
                ->store('tenants/cnic', 'public');
        }

        $tenant = Tenant::create($data);

        // Mark unit as occupied
        Unit::find($data['unit_id'])->update(['status' => 'occupied']);

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant ' . $tenant->name . ' added successfully.');
    }

    public function show(Tenant $tenant): View
    {
        $tenant->load('unit');

        return view('tenants.show', [
            'title' => 'Tenant — ' . $tenant->name,
            'tenant' => $tenant,
        ]);
    }

    public function edit(Tenant $tenant): View
    {
        $tenant->load('unit');

        $units = Unit::where('status', 'vacant')
            ->orWhere('id', $tenant->unit_id)
            ->orderBy('unit_number')
            ->get();

        return view('tenants.edit', [
            'title' => 'Edit Tenant — ' . $tenant->name,
            'tenant' => $tenant,
            'units' => $units,
        ]);
    }

    public function update(UpdateTenantRequest $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validated();

        // Handle unit change
        $oldUnitId = $tenant->unit_id;
        $newUnitId = (int) $data['unit_id'];

        if ($oldUnitId !== $newUnitId) {
            Unit::find($oldUnitId)->update(['status' => 'vacant']);
            Unit::find($newUnitId)->update(['status' => 'occupied']);
        }

        // Handle CNIC front image
        if ($request->hasFile('cnic_front_image')) {
            // Delete old image if exists
            if ($tenant->cnic_front_image) {
                Storage::disk('public')->delete($tenant->cnic_front_image);
            }
            $data['cnic_front_image'] = $request->file('cnic_front_image')
                ->store('tenants/cnic', 'public');
        } else {
            // Keep existing
            unset($data['cnic_front_image']);
        }

        // Handle CNIC back image
        if ($request->hasFile('cnic_back_image')) {
            if ($tenant->cnic_back_image) {
                Storage::disk('public')->delete($tenant->cnic_back_image);
            }
            $data['cnic_back_image'] = $request->file('cnic_back_image')
                ->store('tenants/cnic', 'public');
        } else {
            unset($data['cnic_back_image']);
        }

        $tenant->update($data);

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant ' . $tenant->name . ' updated successfully.');
    }

    public function destroy(Tenant $tenant): RedirectResponse
    {
        // Free the unit
        $tenant->unit->update(['status' => 'vacant']);

        // Delete CNIC images from storage
        if ($tenant->cnic_front_image) {
            Storage::disk('public')->delete($tenant->cnic_front_image);
        }
        if ($tenant->cnic_back_image) {
            Storage::disk('public')->delete($tenant->cnic_back_image);
        }

        $tenant->delete();

        return redirect()
            ->route('tenants.index')
            ->with('success', 'Tenant removed successfully.');
    }
}