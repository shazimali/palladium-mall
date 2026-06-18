<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\Tenant;
use App\Models\Unit;
use App\Http\Requests\StoreAgreementRequest;
use App\Http\Requests\UpdateAgreementRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AgreementController extends Controller
{
    public function index(Request $request): View
    {
        $agreements = Agreement::with(['tenant', 'unit'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $expiringCount = Agreement::expiringSoon(30)->count();

        return view('agreements.index', [
            'title' => 'Agreement Center',
            'agreements' => $agreements,
            'expiringCount' => $expiringCount,
        ]);
    }

    public function create(Request $request): View
    {
        $tenants = Tenant::where('status', 'active')
            ->orderBy('name')
            ->get();

        $units = Unit::where('status', 'rented')
            ->orWhere('status', 'vacant')
            ->orderBy('unit_number')
            ->get();

        // Pre-select tenant if coming from tenant profile
        $selectedTenantId = $request->query('tenant_id');

        return view('agreements.create', [
            'title' => 'New Agreement',
            'tenants' => $tenants,
            'units' => $units,
            'selectedTenantId' => $selectedTenantId,
        ]);
    }

    public function store(StoreAgreementRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Restrict tenant to only one active agreement at a time
        $hasActiveAgreement = Agreement::where('tenant_id', $data['tenant_id'])
            ->where('status', 'active')
            ->exists();

        if ($hasActiveAgreement && $data['status'] === 'active') {
            return back()->withErrors([
                'tenant_id' => 'This tenant already has an active agreement. A tenant cannot have multiple active agreements at the same time. The previous agreement must be expired or terminated first.'
            ])->withInput();
        }

        // Handle document upload
        if ($request->hasFile('document')) {
            $data['document'] = $request->file('document')
                ->store('agreements', 'local');
        }

        $agreement = Agreement::create($data);

        if ($agreement->status === 'active') {
            $agreement->unit?->update(['status' => 'rented']);
            $agreement->tenant?->update(['status' => 'active', 'unit_id' => $agreement->unit_id]);
        }

        return redirect()
            ->route('agreements.show', $agreement)
            ->with('success', 'Agreement created successfully.');
    }

    public function show(Agreement $agreement): View
    {
        $agreement->load(['tenant', 'unit']);

        return view('agreements.show', [
            'title' => 'Agreement — ' . $agreement->tenant->name,
            'agreement' => $agreement,
        ]);
    }

    public function edit(Agreement $agreement): View
    {
        $agreement->load(['tenant', 'unit']);

        $tenants = Tenant::where('status', 'active')
            ->orderBy('name')
            ->get();

        $units = Unit::orderBy('unit_number')->get();

        return view('agreements.edit', [
            'title' => 'Edit Agreement — ' . $agreement->tenant->name,
            'agreement' => $agreement,
            'tenants' => $tenants,
            'units' => $units,
        ]);
    }

    public function update(UpdateAgreementRequest $request, Agreement $agreement): RedirectResponse
    {
        $data = $request->validated();

        // Handle document upload
        if ($request->hasFile('document')) {
            // Delete old document
            if ($agreement->document) {
                Storage::disk('local')->delete($agreement->document);
            }
            $data['document'] = $request->file('document')
                ->store('agreements', 'local');
        } else {
            unset($data['document']);
        }

        $agreement->update($data);

        if ($agreement->status === 'active') {
            $agreement->unit?->update(['status' => 'rented']);
            $agreement->tenant?->update(['status' => 'active', 'unit_id' => $agreement->unit_id]);
        } elseif (in_array($agreement->status, ['expired', 'terminated'])) {
            $agreement->unit?->update(['status' => 'vacant']);
            if ($agreement->tenant) {
                $hasOtherActive = Agreement::where('tenant_id', $agreement->tenant_id)
                    ->where('id', '!=', $agreement->id)
                    ->where('status', 'active')
                    ->exists();
                if (!$hasOtherActive) {
                    $agreement->tenant->update(['status' => 'inactive', 'unit_id' => null]);
                }
            }
        }

        return redirect()
            ->route('agreements.show', $agreement)
            ->with('success', 'Agreement updated successfully.');
    }

    public function destroy(Agreement $agreement): RedirectResponse
    {
        // Delete document from storage
        if ($agreement->document) {
            Storage::disk('local')->delete($agreement->document);
        }

        $agreement->delete();

        return redirect()
            ->route('agreements.index')
            ->with('success', 'Agreement removed successfully.');
    }
}