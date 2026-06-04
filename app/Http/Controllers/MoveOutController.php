<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\MoveInChecklist;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MoveOutController extends Controller
{
    public function create(Tenant $tenant): View
    {
        $tenant->load(['unit', 'activeAgreement']);

        return view('tenants.move_out', [
            'title'     => 'Move-Out Inspection — ' . $tenant->name,
            'tenant'    => $tenant,
            'agreement' => $tenant->activeAgreement,
        ]);
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'inspection_member' => 'required|string|max:255',
            'checklist_date'    => 'required|date',
            'damage_notes'      => 'nullable|string',
            'inventory_notes'   => 'nullable|string',
            'flat_condition'    => 'nullable|in:good,needs_repair',
            'deposit_deduction' => 'nullable|numeric|min:0',
            'final_remarks'     => 'nullable|string',
        ]);

        $booleans = [
            'rooms_cleaned', 'kitchen_cleaned', 'bathrooms_cleaned', 'no_garbage',
            'no_wall_damage', 'paint_condition_ok', 'light_fixtures_ok', 'electric_wiring_ok', 'no_breaker_issues',
            'furniture_ok', 'ac_working', 'kitchen_appliances_ok', 'stove_clean', 'keys_returned',
            'doors_locks_ok', 'windows_ok', 'balcony_doors_ok',
            'water_supply_ok', 'electricity_supply_ok', 'gas_supply_ok',
            'no_pending_utility_bills', 'no_pending_maintenance', 'no_pending_rent',
            'fixtures_available', 'no_missing_items',
            'access_cards_returned', 'no_pending_requests', 'move_out_form_signed',
        ];
        foreach ($booleans as $field) {
            $data[$field] = $request->boolean($field);
        }
        $data['type'] = 'move_out';
        $data['agreement_id'] = $tenant->activeAgreement?->id;

        MoveInChecklist::create(array_merge($data, ['tenant_id' => $tenant->id]));

        // Terminate agreement & vacate unit
        $tenant->activeAgreement?->update(['status' => 'terminated']);
        $tenant->unit?->update(['status' => 'vacant']);
        $tenant->update(['status' => 'inactive', 'unit_id' => null]);

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Move-out inspection saved. Unit is now vacant.');
    }
}
