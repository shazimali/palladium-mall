<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\FlatInspectionReport;
use App\Models\FlatInspectionReportItem;
use App\Models\InspectionHead;
use App\Models\InspectionPerson;
use App\Models\MoveInChecklist;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\ReceivingVoucher;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MoveOutController extends Controller
{
    public function create(Tenant $tenant): View
    {
        $tenant->load(['unit', 'activeAgreement']);

        $agreement = $tenant->activeAgreement;
        $payments  = $agreement
            ? $agreement->payments()->with('meter')->orderBy('month')->get()
            : collect();

        $flatInspectionReport = $agreement
            ? FlatInspectionReport::with('items')
                ->where('agreement_id', $agreement->id)
                ->where('type', 'move_out')
                ->first()
            : null;

        return view('tenants.move_out', [
            'title'                => 'Move-Out Inspection — ' . $tenant->name,
            'tenant'               => $tenant,
            'agreement'            => $agreement,
            'payments'             => $payments,
            'inspectionPersons'    => InspectionPerson::where('is_active', true)->orderBy('name')->get(),
            'inspectionHeads'      => InspectionHead::active()->flatInspection()->orderBy('sort_order')->get(),
            'paymentAccounts'      => PaymentAccount::where('is_active', true)->orderBy('name')->get(),
            'flatInspectionReport' => $flatInspectionReport,
        ]);
    }

    public function store(Request $request, Tenant $tenant): RedirectResponse
    {
        $rules = [
            'inspection_person_id'  => 'required|exists:inspection_persons,id',
            'checklist_date'        => 'required|date',
            'damage_notes'          => 'nullable|string',
            'inventory_notes'       => 'nullable|string',
            'flat_condition'        => 'nullable|in:good,needs_repair',
            'deposit_deduction'     => 'nullable|numeric|min:0',
            'payment_account_id'    => 'nullable|required_if:deposit_deduction,>0|exists:payment_accounts,id',
            'final_remarks'         => 'nullable|string',
            'final_meter_reading'   => 'required|numeric|min:0',
            'final_meter_image'     => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            'breaker_off_statement' => 'nullable|string|max:1000',
        ];

        // Dynamic validation for each InspectionHead row
        $heads = InspectionHead::active()->flatInspection()->get();
        foreach ($heads as $head) {
            $rules["head_{$head->id}_status"]  = 'nullable|in:pass,fail';
            $rules["head_{$head->id}_comment"] = 'nullable|string|max:500';
            $rules["head_{$head->id}_image"]   = 'nullable|image|mimes:jpg,jpeg,png,webp|max:200';
        }

        $data = $request->validate($rules);

        $inspector = InspectionPerson::findOrFail($request->inspection_person_id);
        $data['inspection_member'] = $inspector->name;

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

        $checklistData = $data;
        unset($checklistData['final_meter_reading'], $checklistData['final_meter_image'], $checklistData['breaker_off_statement']);
        foreach ($heads as $head) {
            unset($checklistData["head_{$head->id}_status"],
                  $checklistData["head_{$head->id}_comment"],
                  $checklistData["head_{$head->id}_image"]);
        }

        $depositDeduction = (float) ($data['deposit_deduction'] ?? 0);

        DB::transaction(function () use ($tenant, $data, $checklistData, $heads, $inspector, $depositDeduction, $request) {
            $checklist = MoveInChecklist::create(array_merge($checklistData, ['tenant_id' => $tenant->id]));

            // Deposit deduction accounting inflow & ledger entry
            if ($depositDeduction > 0 && !empty($data['payment_account_id'])) {
                $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
                $activeAg = $tenant->activeAgreement;
                $targetUnitId = $tenant->unit_id ?? $activeAg?->unit_id;

                // 1. Create a Payment (bill) of type 'deposit_deduction'
                $deductionPayment = Payment::create([
                    'tenant_id'          => $tenant->id,
                    'unit_id'            => $targetUnitId,
                    'agreement_id'       => $activeAg?->id,
                    'type'               => 'deposit_deduction',
                    'month'              => $data['checklist_date'],
                    'due_date'           => $data['checklist_date'],
                    'amount'             => $depositDeduction,
                    'amount_paid'        => $depositDeduction,
                    'payment_method'     => $paymentAccount->type,
                    'payment_account_id' => $paymentAccount->id,
                    'status'             => 'paid',
                    'paid_at'            => $data['checklist_date'],
                    'notes'              => 'Deposit deduction for damages/repairs upon move-out.' . (!empty($data['damage_notes']) ? ' (' . $data['damage_notes'] . ')' : ''),
                ]);

                // 2. Create a ReceivingVoucher to reflect funds inflow to the Payment Account
                $voucherNotes = 'Deposit deduction (Damages/Repairs) - ' . $tenant->name;
                if ($tenant->unit) {
                    $voucherNotes .= ' (Unit ' . $tenant->unit->unit_number . ')';
                }
                if (!empty($data['damage_notes'])) {
                    $voucherNotes .= ' [' . $data['damage_notes'] . ']';
                }

                $receivingVoucher = ReceivingVoucher::create([
                    'date'               => $data['checklist_date'],
                    'amount'             => $depositDeduction,
                    'received_from_type' => 'tenant',
                    'tenant_id'          => $tenant->id,
                    'payment_method'     => $paymentAccount->type,
                    'payment_account_id' => $paymentAccount->id,
                    'reference'          => 'MOVE-OUT-DED-' . $tenant->id,
                    'notes'              => $voucherNotes,
                    'user_id'            => auth()->id() ?? 1,
                ]);

                // 3. Link the ReceivingVoucher to the Payment
                $receivingVoucher->payments()->attach($deductionPayment->id, [
                    'amount_allocated' => $depositDeduction,
                ]);
            }

            // Upsert FlatInspectionReport (type = move_out) & items
            if ($tenant->activeAgreement) {
                $report = FlatInspectionReport::updateOrCreate(
                    ['agreement_id' => $tenant->activeAgreement->id, 'type' => 'move_out'],
                    [
                        'unit_id'              => $tenant->activeAgreement->unit_id,
                        'tenant_id'            => $tenant->id,
                        'inspected_by'         => auth()->id(),
                        'inspection_person_id' => $inspector->id,
                        'inspection_member'    => $inspector->name,
                        'inspected_at'         => $data['checklist_date'],
                        'flat_condition'       => $data['flat_condition'] ?? null,
                        'remarks'              => $data['final_remarks'] ?? null,
                    ]
                );

                foreach ($heads as $head) {
                    $rawStatus = $request->input("head_{$head->id}_status");
                    $status    = $rawStatus === 'pass' ? true : ($rawStatus === 'fail' ? false : null);
                    $comment   = $request->input("head_{$head->id}_comment");

                    $existingItem = FlatInspectionReportItem::where('flat_inspection_report_id', $report->id)
                        ->where('inspection_head_id', $head->id)
                        ->first();

                    $imagePath = $existingItem?->image_path;
                    if ($request->hasFile("head_{$head->id}_image")) {
                        if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                            Storage::disk('public')->delete($imagePath);
                        }
                        $imagePath = $request->file("head_{$head->id}_image")
                            ->store('flat_inspections', 'public');
                    }

                    FlatInspectionReportItem::updateOrCreate(
                        [
                            'flat_inspection_report_id' => $report->id,
                            'inspection_head_id'        => $head->id,
                        ],
                        [
                            'status'     => $status,
                            'remarks'    => $comment,
                            'image_path' => $imagePath,
                        ]
                    );
                }
            }

            if ($tenant->activeAgreement) {
                $tenant->activeAgreement->update([
                    'final_meter_reading' => (float) $request->input('final_meter_reading', 0),
                ]);
            }

            // Record Breaker OFF inspection log & turn breaker OFF on Unit
            if ($tenant->unit) {
                $meterImagePath = null;
                if ($request->hasFile('final_meter_image')) {
                    $meterImagePath = $request->file('final_meter_image')->store('breaker_inspections', 'public');
                }

                \App\Models\UnitBreakerInspection::create([
                    'unit_id'                 => $tenant->unit->id,
                    'agreement_id'            => $tenant->activeAgreement?->id,
                    'inspection_person_id'    => $inspector->id,
                    'breaker_status'          => 'off',
                    'meter_reading'           => (float) $request->input('final_meter_reading', 0),
                    'meter_image'             => $meterImagePath,
                    'inspection_officer_name' => $inspector->name,
                    'officer_statement'       => $request->input('breaker_off_statement', "Final move-out inspection completed by {$inspector->name}. Breaker turned OFF to prevent electricity corruption on vacant unit."),
                    'inspected_at'            => now(),
                ]);

                $tenant->unit->update([
                    'status'         => 'vacant',
                    'breaker_status' => 'off',
                ]);
            }

            // Terminate agreement & update tenant
            $tenant->activeAgreement?->update(['status' => 'terminated']);
            $tenant->update(['status' => 'inactive', 'unit_id' => null]);
        });

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Move-out inspection saved. Deposit deduction recorded to payment account, unit marked vacant, and breaker turned OFF.');
    }

    public function printMoveOut(Tenant $tenant): View
    {
        $tenant->load(['unit', 'moveInChecklists' => function($q) {
            $q->where('type', 'move_out')->with('paymentAccount');
        }]);

        $moveOut = $tenant->moveInChecklists->where('type', 'move_out')->first();

        if (!$moveOut) {
            abort(404, 'Move-out inspection not found for this tenant.');
        }

        // Get the agreement that was active at the time of move-out
        $agreement = $tenant->agreements()->where('id', $moveOut->agreement_id)->first()
            ?? $tenant->agreements()->latest()->first();

        $flatInspectionReport = $agreement
            ? FlatInspectionReport::with(['items', 'inspectionPerson'])
                ->where('agreement_id', $agreement->id)
                ->where('type', 'move_out')
                ->first()
            : null;

        return view('tenants.print.move_out', [
            'tenant'               => $tenant,
            'moveOut'              => $moveOut,
            'agreement'            => $agreement,
            'inspectionHeads'      => InspectionHead::active()->flatInspection()->orderBy('sort_order')->get(),
            'flatInspectionReport' => $flatInspectionReport,
        ]);
    }

    public function clearanceForm(Tenant $tenant): View
    {
        $tenant->load(['unit', 'moveInChecklists' => function($q) {
            $q->where('type', 'move_out');
        }]);

        $agreement = $tenant->agreements()->latest()->first();

        $partners = $agreement ? $agreement->partners : collect();
        $guarantors = $agreement ? $agreement->guarantors : collect();
        $emergencyContacts = $agreement ? $agreement->emergencyContacts : collect();

        $payments  = $agreement
            ? $agreement->payments()->with('meter')->orderBy('month')->get()
            : collect();

        $totalBilled = $payments->sum('amount');
        $totalPaid   = $payments->sum('amount_paid');
        $outstanding = max(0, $totalBilled - $totalPaid);

        return view('tenants.print.clearance_form', [
            'tenant'            => $tenant,
            'agreement'         => $agreement,
            'partners'          => $partners,
            'guarantors'        => $guarantors,
            'emergencyContacts' => $emergencyContacts,
            'payments'          => $payments,
            'totalBilled'       => $totalBilled,
            'totalPaid'         => $totalPaid,
            'outstanding'       => $outstanding,
        ]);
    }
}
