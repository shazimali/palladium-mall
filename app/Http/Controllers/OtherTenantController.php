<?php

namespace App\Http\Controllers;

use App\Models\OtherTenant;
use App\Models\OtherTenantUnitHistory;
use App\Models\Payment;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class OtherTenantController extends Controller
{
    // -----------------------------------------------------------------------
    // Index
    // -----------------------------------------------------------------------

    public function index(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_tenants.view')) {
            abort(403, 'Unauthorized action.');
        }

        $filterMonth = null;
        $filterYear = null;

        if ($request->filled('filter_month')) {
            try {
                $date = Carbon::parse($request->filter_month);
                $filterMonth = $date->month;
                $filterYear = $date->year;
            } catch (\Exception $e) {
                // Ignore parse errors
            }
        }

        $query = OtherTenant::with(['unit.floor', 'unit.block', 'unitHistory.unit'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($filterYear, function ($q) use ($filterMonth, $filterYear) {
                return $q->whereHas('unitHistory', function ($historyQ) use ($filterMonth, $filterYear) {
                    $start = Carbon::create($filterYear, $filterMonth, 1)->startOfMonth()->toDateString();
                    $end = Carbon::create($filterYear, $filterMonth, 1)->endOfMonth()->toDateString();
                    return $historyQ->where('attached_at', '<=', $end)
                        ->where(function ($sub) use ($start) {
                            $sub->whereNull('detached_at')
                                ->orWhere('detached_at', '>=', $start);
                        });
                });
            });


        $counts = [
            'total'    => (clone $query)->count(),
            'attached' => (clone $query)->whereNotNull('unit_id')->count(),
            'detached' => (clone $query)->whereNull('unit_id')->count(),
        ];

        $otherTenants = $query
            ->when($request->status === 'attached', fn($q) => $q->whereNotNull('unit_id'))
            ->when($request->status === 'detached', fn($q) => $q->whereNull('unit_id'))
            ->latest()
            ->paginate(20)
            ->withQueryString();


        // Self-owned units for the attach modal
        $selfUnits = Unit::where('is_self', true)
            ->with(['floor', 'block', 'otherTenant', 'landlord'])
            ->orderBy('unit_number')
            ->get();

        if ($request->ajax() || $request->has('ajax')) {
            return view('other-tenants._table', [
                'otherTenants' => $otherTenants,
                'counts'       => $counts,
                'selfUnits'    => $selfUnits,
            ])->render();
        }

        return view('other-tenants.index', [
            'title'        => 'Other Tenants',
            'otherTenants' => $otherTenants,
            'counts'       => $counts,
            'selfUnits'    => $selfUnits,
        ]);
    }

    /**
     * Print the list of other tenants.
     */
    public function print(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_tenants.view')) {
            abort(403, 'Unauthorized action.');
        }

        $filterMonth = null;
        $filterYear = null;

        if ($request->filled('filter_month')) {
            try {
                $date = Carbon::parse($request->filter_month);
                $filterMonth = $date->month;
                $filterYear = $date->year;
            } catch (\Exception $e) {
                // Ignore parse errors
            }
        }

        $otherTenants = OtherTenant::with(['unit.floor', 'unit.block', 'unitHistory.unit'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($filterYear, function ($q) use ($filterMonth, $filterYear) {
                return $q->whereHas('unitHistory', function ($historyQ) use ($filterMonth, $filterYear) {
                    $start = Carbon::create($filterYear, $filterMonth, 1)->startOfMonth()->toDateString();
                    $end = Carbon::create($filterYear, $filterMonth, 1)->endOfMonth()->toDateString();
                    return $historyQ->where('attached_at', '<=', $end)
                        ->where(function ($sub) use ($start) {
                            $sub->whereNull('detached_at')
                                ->orWhere('detached_at', '>=', $start);
                        });
                });
            })
            ->when($request->status === 'attached', fn($q) => $q->whereNotNull('unit_id'))
            ->when($request->status === 'detached', fn($q) => $q->whereNull('unit_id'))
            ->latest()
            ->get();

        return view('other-tenants.print', [
            'title'        => 'Other Flat/Shop Tenants List',
            'otherTenants' => $otherTenants,
        ]);
    }

    // -----------------------------------------------------------------------
    // Show
    // -----------------------------------------------------------------------

    public function show(OtherTenant $otherTenant): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_tenants.view')) {
            abort(403, 'Unauthorized action.');
        }

        $otherTenant->load(['unit.floor', 'unit.block']);

        $unitHistory = OtherTenantUnitHistory::where('other_tenant_id', $otherTenant->id)
            ->with(['unit.floor', 'unit.block'])
            ->orderBy('attached_at', 'desc')
            ->get();

        $payments = Payment::where('other_tenant_id', $otherTenant->id)
            ->with(['unit', 'paymentAccount'])
            ->orderBy('month', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('other-tenants.show', [
            'title'        => $otherTenant->name,
            'otherTenant'  => $otherTenant,
            'unitHistory'  => $unitHistory,
            'payments'     => $payments,
        ]);
    }

    // -----------------------------------------------------------------------
    // Create
    // -----------------------------------------------------------------------

    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_tenants.create')) {
            abort(403, 'Unauthorized action.');
        }

        $selfUnits = Unit::where('is_self', true)
            ->with(['floor', 'block', 'otherTenant', 'landlord'])
            ->orderBy('unit_number')
            ->get();

        return view('other-tenants.create', [
            'title'     => 'Add Other Tenant',
            'selfUnits' => $selfUnits,
        ]);
    }

    // -----------------------------------------------------------------------
    // Store
    // -----------------------------------------------------------------------

    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_tenants.create')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->has('monthly_rent') && $request->input('monthly_rent') !== null) {
            $request->merge(['monthly_rent' => str_replace(',', '', $request->input('monthly_rent'))]);
        }
        if ($request->has('maintenance_charge') && $request->input('maintenance_charge') !== null) {
            $request->merge(['maintenance_charge' => str_replace(',', '', $request->input('maintenance_charge'))]);
        }

        $data = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'cnic'               => ['nullable', 'string', 'max:15', 'unique:other_tenants,cnic', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'phone'              => ['nullable', 'string', 'max:20'],
            'whatsapp_number'    => ['nullable', 'string', 'max:20'],
            'maintenance_charge' => ['nullable', 'numeric', 'min:0'],
            'monthly_rent'       => ['required', 'numeric', 'min:0'],
            'attached_at'        => ['nullable', 'date'],
            'photo'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'unit_id'            => [
                'nullable',
                'exists:units,id',
                function ($attribute, $value, $fail) {
                    $exists = OtherTenant::where('unit_id', $value)->exists();
                    if ($exists) {
                        $fail('The selected unit is already attached to another tenant.');
                    }
                }
            ],
        ], [
            'monthly_rent.required' => 'Monthly rent is required.',
            'cnic.regex'            => 'CNIC format must be: 35201-1234567-1',
            'cnic.unique'           => 'This CNIC is already registered to another tenant.',
        ]);

        $data['monthly_rent'] = isset($data['monthly_rent']) && $data['monthly_rent'] !== '' ? $data['monthly_rent'] : 0;
        $data['maintenance_charge'] = isset($data['maintenance_charge']) && $data['maintenance_charge'] !== '' ? $data['maintenance_charge'] : 0;

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('other_tenants/photos', 'public');
        } else {
            unset($data['photo']);
        }

        $unitId = $data['unit_id'] ?? null;
        $attachedAt = $data['attached_at'] ?? null;
        unset($data['unit_id'], $data['attached_at']);

        $data['status'] = $unitId ? 'active' : 'inactive';
        $otherTenant = OtherTenant::create($data);

        // Attach to unit if selected
        if ($unitId) {
            $this->performAttach($otherTenant, $unitId, $attachedAt);
        }

        return redirect()->route('other-tenants.index')
            ->with('success', 'Other tenant added successfully.');
    }

    // -----------------------------------------------------------------------
    // Edit
    // -----------------------------------------------------------------------

    public function edit(OtherTenant $otherTenant): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_tenants.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $selfUnits = Unit::where('is_self', true)
            ->with(['floor', 'block', 'otherTenant', 'landlord'])
            ->orderBy('unit_number')
            ->get();

        return view('other-tenants.edit', [
            'title'       => 'Edit Other Tenant',
            'otherTenant' => $otherTenant->load(['unit.floor', 'unit.block']),
            'selfUnits'   => $selfUnits,
        ]);
    }

    // -----------------------------------------------------------------------
    // Update
    // -----------------------------------------------------------------------

    public function update(Request $request, OtherTenant $otherTenant): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_tenants.edit')) {
            abort(403, 'Unauthorized action.');
        }

        if ($request->has('monthly_rent') && $request->input('monthly_rent') !== null) {
            $request->merge(['monthly_rent' => str_replace(',', '', $request->input('monthly_rent'))]);
        }
        if ($request->has('maintenance_charge') && $request->input('maintenance_charge') !== null) {
            $request->merge(['maintenance_charge' => str_replace(',', '', $request->input('maintenance_charge'))]);
        }

        $data = $request->validate([
            'name'               => ['required', 'string', 'max:255'],
            'cnic'               => ['nullable', 'string', 'max:15', 'unique:other_tenants,cnic,' . $otherTenant->id, 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'phone'              => ['nullable', 'string', 'max:20'],
            'whatsapp_number'    => ['nullable', 'string', 'max:20'],
            'maintenance_charge' => ['nullable', 'numeric', 'min:0'],
            'monthly_rent'       => ['required', 'numeric', 'min:0'],
            'attached_at'        => ['nullable', 'date'],
            'photo'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'delete_photo'       => ['nullable', 'boolean'],
            'unit_id'            => [
                'nullable',
                'exists:units,id',
                function ($attribute, $value, $fail) use ($otherTenant) {
                    $exists = OtherTenant::where('unit_id', $value)
                        ->where('id', '!=', $otherTenant->id)
                        ->exists();
                    if ($exists) {
                        $fail('The selected unit is already attached to another tenant.');
                    }
                }
            ],
        ], [
            'monthly_rent.required' => 'Monthly rent is required.',
            'cnic.regex'            => 'CNIC format must be: 35201-1234567-1',
            'cnic.unique'           => 'This CNIC is already registered to another tenant.',
        ]);

        $data['monthly_rent'] = isset($data['monthly_rent']) && $data['monthly_rent'] !== '' ? $data['monthly_rent'] : 0;
        $data['maintenance_charge'] = isset($data['maintenance_charge']) && $data['maintenance_charge'] !== '' ? $data['maintenance_charge'] : 0;

        if ($request->boolean('delete_photo')) {
            if ($otherTenant->photo) {
                Storage::disk('public')->delete($otherTenant->photo);
            }
            $data['photo'] = null;
        } elseif ($request->hasFile('photo')) {
            if ($otherTenant->photo) {
                Storage::disk('public')->delete($otherTenant->photo);
            }
            $data['photo'] = $request->file('photo')->store('other_tenants/photos', 'public');
        } else {
            unset($data['photo']);
        }
        unset($data['delete_photo']);

        $newUnitId = $data['unit_id'] ?? null;
        $attachedAt = $data['attached_at'] ?? null;
        $oldUnitId = $otherTenant->unit_id;
        unset($data['unit_id'], $data['attached_at']);

        $data['status'] = $newUnitId ? 'active' : 'inactive';
        $otherTenant->update($data);

        // Handle unit changes
        if ($newUnitId != $oldUnitId) {
            if ($oldUnitId) {
                $this->performDetach($otherTenant);
            }
            if ($newUnitId) {
                $this->performAttach($otherTenant, $newUnitId, $attachedAt);
            }
        } elseif ($newUnitId && $attachedAt) {
            // Update the attachment date of the current unit
            OtherTenantUnitHistory::where('other_tenant_id', $otherTenant->id)
                ->where('unit_id', $newUnitId)
                ->whereNull('detached_at')
                ->update(['attached_at' => $attachedAt]);
        }

        return redirect()->route('other-tenants.index')
            ->with('success', 'Other tenant updated successfully.');
    }

    // -----------------------------------------------------------------------
    // Destroy
    // -----------------------------------------------------------------------

    public function destroy(OtherTenant $otherTenant): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_tenants.delete')) {
            abort(403, 'Unauthorized action.');
        }

        // Detach from unit before deleting
        if ($otherTenant->unit_id) {
            $this->performDetach($otherTenant);
        }
        $otherTenant->delete();

        return redirect()->route('other-tenants.index')
            ->with('success', 'Other tenant deleted successfully.');
    }

    // -----------------------------------------------------------------------
    // Attach to a self-owned unit
    // -----------------------------------------------------------------------

    public function attach(Request $request, OtherTenant $otherTenant): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_tenants.attach')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'unit_id' => [
                'required',
                'exists:units,id',
                function ($attribute, $value, $fail) use ($otherTenant) {
                    $exists = OtherTenant::where('unit_id', $value)
                        ->where('id', '!=', $otherTenant->id)
                        ->exists();
                    if ($exists) {
                        $fail('The selected unit is already attached to another tenant.');
                    }
                }
            ],
        ]);

        $unit = Unit::where('id', $request->unit_id)
            ->where('is_self', true)
            ->firstOrFail();

        // Detach from current unit if already attached elsewhere
        if ($otherTenant->unit_id) {
            $this->performDetach($otherTenant);
        }

        $this->performAttach($otherTenant, $unit->id);

        return redirect()->route('other-tenants.index')
            ->with('success', "Attached {$otherTenant->name} to Unit {$unit->unit_number}.");
    }

    // -----------------------------------------------------------------------
    // Detach from unit
    // -----------------------------------------------------------------------

    public function detach(OtherTenant $otherTenant): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_tenants.attach')) {
            abort(403, 'Unauthorized action.');
        }

        $this->performDetach($otherTenant);

        return redirect()->route('other-tenants.index')
            ->with('success', "{$otherTenant->name} detached from unit.");
    }

    /**
     * Print statement for particular tenant showing paid and pending dues across all past and current units.
     */
    public function printStatement(OtherTenant $otherTenant): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('other_tenants.view')) {
            abort(403, 'Unauthorized action.');
        }

        $otherTenant->load(['unit.floor', 'unit.block']);

        // Fetch full unit history (all past and present attached units)
        $unitHistory = OtherTenantUnitHistory::where('other_tenant_id', $otherTenant->id)
            ->with(['unit.floor', 'unit.block'])
            ->orderBy('attached_at', 'asc')
            ->get();

        $allUnitIds = $unitHistory->pluck('unit_id')->filter()->unique()->toArray();
        if ($otherTenant->unit_id) {
            $allUnitIds[] = $otherTenant->unit_id;
        }
        $allUnitIds = array_values(array_unique($allUnitIds));

        // Fetch payments for this tenant across all past and present units
        $payments = Payment::query()
            ->where(function ($q) use ($otherTenant, $allUnitIds) {
                $q->where('other_tenant_id', $otherTenant->id);
                if (!empty($allUnitIds)) {
                    $q->orWhereIn('unit_id', $allUnitIds);
                }
            })
            ->with(['unit.floor', 'unit.block', 'paymentAccount', 'receivingVouchers'])
            ->orderBy('month', 'asc')
            ->get();

        $totalBilled = (float) $payments->where('type', '!=', 'security_deposit')->sum('amount');
        $totalPaid = (float) $payments->sum('amount_paid');
        $totalPending = max(0.00, $totalBilled - $totalPaid);

        $unit = $otherTenant->unit;
        $latestBreakerInsp = $unit ? $unit->breakerInspections()->latest('inspected_at')->first() : null;

        return view('other-tenants.statement_print', [
            'title'             => 'Tenant Statement - ' . $otherTenant->name,
            'otherTenant'       => $otherTenant,
            'unit'              => $unit,
            'unitHistory'       => $unitHistory,
            'payments'          => $payments,
            'totalBilled'       => $totalBilled,
            'totalPaid'         => $totalPaid,
            'totalPending'      => $totalPending,
            'latestBreakerInsp' => $latestBreakerInsp,
        ]);
    }

    // -----------------------------------------------------------------------
    // Private helpers for attach / detach with history & breaker toggle
    // -----------------------------------------------------------------------

    private function performAttach(OtherTenant $otherTenant, int $unitId, $attachedAt = null): void
    {
        // Set unit_id and status on other_tenant
        $otherTenant->update([
            'unit_id' => $unitId,
            'status'  => 'active'
        ]);

        // Create history record
        OtherTenantUnitHistory::create([
            'other_tenant_id' => $otherTenant->id,
            'unit_id'         => $unitId,
            'attached_at'     => $attachedAt ?: Carbon::today(),
        ]);

        // Auto switch unit breaker ON when attached
        $unit = Unit::find($unitId);
        if ($unit) {
            $unit->update(['breaker_status' => 'on']);
        }
    }


    private function performDetach(OtherTenant $otherTenant): void
    {
        if (!$otherTenant->unit_id) {
            return;
        }

        $unitId = $otherTenant->unit_id;

        // Close the open history record
        OtherTenantUnitHistory::where('other_tenant_id', $otherTenant->id)
            ->where('unit_id', $unitId)
            ->whereNull('detached_at')
            ->update(['detached_at' => Carbon::today()]);

        // Clear unit_id and mark inactive
        $otherTenant->update([
            'unit_id' => null,
            'status'  => 'inactive'
        ]);

        // Auto switch unit breaker OFF when detached
        $unit = Unit::find($unitId);
        if ($unit) {
            $unit->update(['breaker_status' => 'off']);
        }
    }
}
