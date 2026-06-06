<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Floor;
use App\Models\Block;
use App\Models\Area;
use App\Models\Meter;
use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $units = Unit::query()
            ->with(['floor', 'block', 'area', 'landlord'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->floor_id, fn($q) => $q->where('floor_id', $request->floor_id))
            ->when($request->block_id, fn($q) => $q->where('block_id', $request->block_id))
            ->when($request->area_id, fn($q) => $q->where('area_id', $request->area_id))
            ->orderBy('unit_number')
            ->paginate(20)
            ->withQueryString();

        $floors = Floor::orderBy('name')->get();
        $blocks = Block::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        return view('units.index', [
            'title' => 'Flat / Shop Master',
            'units' => $units,
            'floors' => $floors,
            'blocks' => $blocks,
            'areas' => $areas,
        ]);
    }

    public function create(): View
    {
        return view('units.create', [
            'title' => 'Add New Unit',
            'floors' => Floor::orderBy('name')->get(),
            'blocks' => Block::orderBy('name')->get(),
            'areas' => Area::orderBy('name')->get(),
            'landlords' => \App\Models\Landlord::orderBy('name')->get(),
        ]);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['date'])) {
            $data['date'] = now()->toDateString();
        }
        Unit::create($data);

        return redirect()
            ->route('units.index')
            ->with('success', 'Flat/Shop created successfully.');
    }

    public function show(Unit $unit): View
    {
        $unit->load([
            'floor', 'block', 'area', 'meters', 'landlord',
            'agreements.tenant',
            'payments.tenant',
            'payments.paymentAccount'
        ]);

        $agreements = $unit->agreements;
        $payments = $unit->payments;

        // KPI Calculations
        $total_earnings = (float) $payments->whereIn('status', ['paid', 'partial'])->sum('amount_paid');
        $total_outstanding = (float) $payments->sum('amount') - (float) $payments->sum('amount_paid');
        $agreements_count = $agreements->count();

        // Compile timeline
        $timeline = collect();

        foreach ($agreements as $agreement) {
            $timeline->push([
                'type' => 'agreement',
                'date' => $agreement->start_date,
                'title' => 'Agreement Signed',
                'subtitle' => 'Tenant: ' . ($agreement->tenant->name ?? '—'),
                'details' => 'Monthly Rent: Rs. ' . number_format($agreement->monthly_rent) . ' | Security Deposit: Rs. ' . number_format($agreement->security_deposit),
                'status' => $agreement->status,
                'status_badge' => $agreement->status_badge_class,
                'icon' => '📄',
                'url' => route('agreements.show', $agreement->id),
            ]);
            
            if ($agreement->status === 'terminated') {
                $timeline->push([
                    'type' => 'agreement_terminated',
                    'date' => $agreement->updated_at,
                    'title' => 'Agreement Terminated',
                    'subtitle' => 'Tenant: ' . ($agreement->tenant->name ?? '—'),
                    'details' => 'The tenancy agreement has been terminated.',
                    'status' => 'terminated',
                    'status_badge' => 'bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400',
                    'icon' => '❌',
                    'url' => route('agreements.show', $agreement->id),
                ]);
            }
        }

        foreach ($payments as $payment) {
            $title = $payment->type_label . ' Received';
            $icon = '💰';
            if ($payment->type === 'rent') {
                $icon = '🏠';
            } elseif (in_array($payment->type, ['electricity', 'water', 'gas'])) {
                $icon = '⚡';
            }
            
            if ($payment->status === 'unpaid') {
                $title = $payment->type_label . ' Billed';
            }

            $details = 'Amount: Rs. ' . number_format($payment->amount) . ' | Paid: Rs. ' . number_format($payment->amount_paid);
            if ($payment->paymentAccount) {
                $details .= ' via ' . $payment->paymentAccount->name;
            }
            if ($payment->reference) {
                $details .= ' (Ref: ' . $payment->reference . ')';
            }

            $timeline->push([
                'type' => 'payment',
                'date' => $payment->paid_at ?? $payment->due_date,
                'title' => $title,
                'subtitle' => 'Tenant: ' . ($payment->tenant->name ?? '—'),
                'details' => $details,
                'status' => $payment->status,
                'status_badge' => $payment->status_badge_class,
                'icon' => $icon,
                'url' => route('payments.show', $payment->id),
            ]);
        }

        // Sort chronological timeline by date descending
        $timeline = $timeline->sortByDesc('date')->values();

        return view('units.show', [
            'title' => 'Unit — ' . $unit->unit_number,
            'unit' => $unit,
            'meters' => $unit->meters->keyBy('type'),
            'total_earnings' => $total_earnings,
            'total_outstanding' => $total_outstanding,
            'agreements_count' => $agreements_count,
            'timeline' => $timeline,
        ]);
    }

    public function edit(Unit $unit): View
    {
        $unit->load(['meters', 'landlord']);

        return view('units.edit', [
            'title' => 'Edit Unit — ' . $unit->unit_number,
            'unit' => $unit,
            'floors' => Floor::orderBy('name')->get(),
            'blocks' => Block::orderBy('name')->get(),
            'areas' => Area::orderBy('name')->get(),
            'landlords' => \App\Models\Landlord::orderBy('name')->get(),
            'existingMeters' => $unit->meters->keyBy('type'),
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $data = $request->validated();
        if (empty($data['date'])) {
            $data['date'] = now()->toDateString();
        }
        $unit->update($data);

        return redirect()
            ->route('units.index')
            ->with('success', 'Flat/Shop updated successfully.');
    }

    public function destroy(Unit $unit): RedirectResponse
    {
        // Soft delete — record is preserved for audit
        $unit->delete();

        return redirect()
            ->route('units.index')
            ->with('success', 'Flat/Shop removed successfully.');
    }
    public function vacate(Unit $unit): RedirectResponse
    {
        $unit->update(['status' => 'vacant']);

        \App\Models\Agreement::where('unit_id', $unit->id)
            ->where('status', 'active')
            ->update(['status' => 'terminated']);

        return redirect()->route('units.show', $unit)
            ->with('success', 'Unit marked as vacant.');
    }

    public function addTenant(Unit $unit): RedirectResponse
    {
        return redirect()->route('tenants.create', ['unit_id' => $unit->id]);
    }
}
