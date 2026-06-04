<?php

namespace App\Http\Controllers;

use App\Models\Agreement;
use App\Models\EmergencyContact;
use App\Models\Guarantor;
use App\Models\MoveInChecklist;
use App\Models\Tenant;
use App\Models\TenantDocumentChecklist;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TenantController extends Controller
{
    // -----------------------------------------------------------------------
    // Index
    // -----------------------------------------------------------------------

    public function index(Request $request): View
    {
        $tenants = Tenant::with(['unit', 'activeAgreement'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->status === 'active', fn($q) => $q->active())
            ->when($request->status === 'inactive', fn($q) => $q->inactive())
            ->when($request->status === 'draft', fn($q) => $q->draft())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('tenants.index', [
            'title'   => 'Tenants',
            'tenants' => $tenants,
        ]);
    }

    // -----------------------------------------------------------------------
    // Wizard Step 1 — Create tenant (GET + POST)
    // -----------------------------------------------------------------------

    public function create(): View
    {
        return view('tenants.create', [
            'title' => 'Add New Tenant',
            'step'  => 1,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'father_name'    => 'nullable|string|max:255',
            'cnic'           => 'required|string|max:15',
            'date_of_birth'  => 'nullable|date',
            'gender'         => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'phone'          => 'required|string|max:20',
            'whatsapp_number'=> 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'address'        => 'required|string|max:500',
            'occupation'     => 'nullable|string|max:255',
            'monthly_income' => 'nullable|numeric|min:0',
            'tenancy_type'   => 'nullable|in:residential,commercial,student',
            'adults_count'   => 'nullable|integer|min:1',
            'children_count' => 'nullable|integer|min:0',
            'passport_photo' => 'nullable|image|max:2048',
        ]);

        $data['status'] = 'draft';

        if ($request->hasFile('passport_photo')) {
            $data['passport_photo'] = $request->file('passport_photo')->store('tenants/photos', 'public');
        }

        $tenant = Tenant::create($data);

        return redirect()->route('tenants.showStep', [$tenant, 2])
            ->with('success', 'Step 1 saved. Continue with guarantor details.');
    }

    // -----------------------------------------------------------------------
    // Wizard Steps 2–6 — Show & Save
    // -----------------------------------------------------------------------

    public function showStep(Tenant $tenant, int $step): View
    {
        $data = ['title' => 'Add Tenant — Step ' . $step, 'tenant' => $tenant, 'step' => $step];

        return match ($step) {
            1 => view('tenants.wizard.step1', array_merge($data, [])),
            2 => view('tenants.wizard.step2', array_merge($data, [
                'guarantor'         => $tenant->guarantor,
                'emergencyContacts' => $tenant->emergencyContacts,
            ])),
            3 => view('tenants.wizard.step3', array_merge($data, [
                'agreement' => $tenant->agreements()->latest()->first(),
                'units'     => Unit::where('status', 'vacant')
                    ->orWhere('id', $tenant->unit_id)
                    ->orderBy('unit_number')
                    ->get(),
            ])),
            4 => view('tenants.wizard.step4', array_merge($data, [
                'checklist' => $tenant->documentChecklist,
            ])),
            5 => view('tenants.wizard.step5', array_merge($data, [
                'checklist'  => $tenant->moveInChecklists()->where('type', 'move_in')->first(),
                'agreement'  => $tenant->agreements()->latest()->first(),
            ])),
            6 => view('tenants.wizard.step6', array_merge($data, [
                'guarantor'         => $tenant->guarantor,
                'emergencyContacts' => $tenant->emergencyContacts,
                'agreement'         => $tenant->agreements()->latest()->first(),
                'docChecklist'      => $tenant->documentChecklist,
                'moveInChecklist'   => $tenant->moveInChecklists()->where('type', 'move_in')->first(),
            ])),
            default => redirect()->route('tenants.showStep', [$tenant, 1]),
        };
    }

    public function saveStep(Request $request, Tenant $tenant, int $step): RedirectResponse
    {
        return match ($step) {
            2 => $this->saveStep2($request, $tenant),
            3 => $this->saveStep3($request, $tenant),
            4 => $this->saveStep4($request, $tenant),
            5 => $this->saveStep5($request, $tenant),
            default => redirect()->route('tenants.showStep', [$tenant, $step]),
        };
    }

    // -----------------------------------------------------------------------
    // Step 2 — Guarantor & Emergency Contacts
    // -----------------------------------------------------------------------

    private function saveStep2(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            // Guarantor
            'guarantor_name'     => 'required|string|max:255',
            'guarantor_cnic'     => 'required|string|max:15',
            'guarantor_relation' => 'required|in:dealer,friend,relative,employer,other',
            'guarantor_phone'    => 'required|string|max:20',
            'guarantor_address'  => 'required|string|max:500',
            'guarantor_occupation' => 'nullable|string|max:255',
            // Emergency contacts (min 2)
            'contacts'           => 'required|array|min:2',
            'contacts.*.name'    => 'required|string|max:255',
            'contacts.*.relation'=> 'required|in:father,mother,brother,sister,wife,husband,son,daughter,other',
            'contacts.*.phone'   => 'required|string|max:20',
            'contacts.*.address' => 'nullable|string|max:500',
        ]);

        // Upsert guarantor
        $tenant->guarantor()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'name'       => $data['guarantor_name'],
                'cnic'       => $data['guarantor_cnic'],
                'relation'   => $data['guarantor_relation'],
                'phone'      => $data['guarantor_phone'],
                'address'    => $data['guarantor_address'],
                'occupation' => $data['guarantor_occupation'] ?? null,
            ]
        );

        // Replace emergency contacts
        $tenant->emergencyContacts()->delete();
        foreach ($data['contacts'] as $contact) {
            $tenant->emergencyContacts()->create($contact);
        }

        return redirect()->route('tenants.showStep', [$tenant, 3])
            ->with('success', 'Step 2 saved.');
    }

    // -----------------------------------------------------------------------
    // Step 3 — Unit & Agreement Terms
    // -----------------------------------------------------------------------

    private function saveStep3(Request $request, Tenant $tenant): RedirectResponse
    {
        $data = $request->validate([
            'unit_id'              => 'required|exists:units,id',
            'start_date'           => 'required|date',
            'end_date'             => 'required|date|after:start_date',
            'monthly_rent'         => 'required|numeric|min:0',
            'maintenance_charge'   => 'nullable|numeric|min:0',
            'security_deposit'     => 'required|numeric|min:0',
            'payment_due_day'      => 'required|integer|min:1|max:31',
            'grace_period_days'    => 'nullable|integer|min:0',
            'notice_period_months' => 'nullable|integer|min:0',
            'fine_per_day'         => 'nullable|numeric|min:0',
            'terms'                => 'nullable|string',
        ]);

        // Mark unit as occupied
        if ($tenant->unit_id && $tenant->unit_id !== (int) $data['unit_id']) {
            Unit::find($tenant->unit_id)?->update(['status' => 'vacant']);
        }
        Unit::find($data['unit_id'])->update(['status' => 'occupied']);
        $tenant->update(['unit_id' => $data['unit_id']]);

        // Expire any previous active agreements
        $tenant->agreements()->where('status', 'active')->update(['status' => 'expired']);

        // Upsert agreement
        $tenant->agreements()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'status' => 'draft'],
            array_merge($data, ['status' => 'draft'])
        );

        return redirect()->route('tenants.showStep', [$tenant, 4])
            ->with('success', 'Step 3 saved.');
    }

    // -----------------------------------------------------------------------
    // Step 4 — Document Checklist
    // -----------------------------------------------------------------------

    private function saveStep4(Request $request, Tenant $tenant): RedirectResponse
    {
        $booleans = [
            'cnic_copy_tenant', 'cnic_copy_father', 'cnic_copy_guarantor',
            'passport_photo', 'nikah_nama', 'frc_form_b', 'police_verification',
            'tenant_application_form', 'tenancy_agreement_copy', 'rules_acknowledgment',
            'inspection_report', 'property_handover_form', 'security_deposit_receipt', 'meter_picture',
            'emergency_contacts_added', 'guarantor_info_added', 'guarantor_business_card',
            'tenant_business_card', 'property_advisor_card', 'old_tenant_verification',
            'business_license', 'utility_bills_clearance',
        ];

        $data = [];
        foreach ($booleans as $field) {
            $data[$field] = $request->boolean($field);
        }
        $data['notes'] = $request->input('notes');

        $checklist = $tenant->documentChecklist()->firstOrNew(['tenant_id' => $tenant->id]);

        // Handle file uploads
        $fileFields = ['cnic_front_image', 'cnic_back_image', 'signed_agreement_scan', 'bank_voucher'];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($checklist->{$field}) {
                    Storage::disk('public')->delete($checklist->{$field});
                }
                $data[$field] = $request->file($field)->store('tenants/documents', 'public');
            }
        }

        $checklist->fill($data);
        $checklist->tenant_id = $tenant->id;
        $checklist->save();

        return redirect()->route('tenants.showStep', [$tenant, 5])
            ->with('success', 'Step 4 saved.');
    }

    // -----------------------------------------------------------------------
    // Step 5 — Move-in Inspection
    // -----------------------------------------------------------------------

    private function saveStep5(Request $request, Tenant $tenant): RedirectResponse
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
        $data['type'] = 'move_in';

        $agreement = $tenant->agreements()->latest()->first();
        $data['agreement_id'] = $agreement?->id;

        $tenant->moveInChecklists()
            ->where('type', 'move_in')
            ->updateOrCreate(
                ['tenant_id' => $tenant->id, 'type' => 'move_in'],
                $data
            );

        return redirect()->route('tenants.showStep', [$tenant, 6])
            ->with('success', 'Step 5 saved.');
    }

    // -----------------------------------------------------------------------
    // Step 6 — Confirm (promote draft → active)
    // -----------------------------------------------------------------------

    public function confirm(Request $request, Tenant $tenant): RedirectResponse
    {
        // Activate tenant
        $tenant->update(['status' => 'active']);

        // Activate agreement
        $tenant->agreements()->where('status', 'draft')->update(['status' => 'active']);

        return redirect()->route('tenants.show', $tenant)
            ->with('success', 'Tenant ' . $tenant->name . ' has been added successfully.');
    }

    // -----------------------------------------------------------------------
    // Show — Tenant Profile
    // -----------------------------------------------------------------------

    public function show(Tenant $tenant): View
    {
        $tenant->load([
            'unit', 'guarantor', 'emergencyContacts',
            'activeAgreement', 'agreements',
            'documentChecklist', 'moveInChecklists',
        ]);

        return view('tenants.show', [
            'title'  => 'Tenant — ' . $tenant->name,
            'tenant' => $tenant,
        ]);
    }

    // -----------------------------------------------------------------------
    // Edit — redirect to wizard at the appropriate step
    // -----------------------------------------------------------------------

    public function edit(Tenant $tenant): RedirectResponse
    {
        return redirect()->route('tenants.showStep', [$tenant, 1]);
    }

    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        // Step 1 edit update
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'father_name'    => 'nullable|string|max:255',
            'cnic'           => 'required|string|max:15',
            'date_of_birth'  => 'nullable|date',
            'gender'         => 'nullable|in:male,female,other',
            'marital_status' => 'nullable|in:single,married,divorced,widowed',
            'phone'          => 'required|string|max:20',
            'whatsapp_number'=> 'nullable|string|max:20',
            'email'          => 'nullable|email|max:255',
            'address'        => 'required|string|max:500',
            'occupation'     => 'nullable|string|max:255',
            'monthly_income' => 'nullable|numeric|min:0',
            'tenancy_type'   => 'nullable|in:residential,commercial,student',
            'adults_count'   => 'nullable|integer|min:1',
            'children_count' => 'nullable|integer|min:0',
            'passport_photo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('passport_photo')) {
            if ($tenant->passport_photo) {
                Storage::disk('public')->delete($tenant->passport_photo);
            }
            $data['passport_photo'] = $request->file('passport_photo')->store('tenants/photos', 'public');
        }

        $tenant->update($data);

        return redirect()->route('tenants.showStep', [$tenant, 2])
            ->with('success', 'Personal details updated.');
    }

    // -----------------------------------------------------------------------
    // Destroy
    // -----------------------------------------------------------------------

    public function destroy(Tenant $tenant): RedirectResponse
    {
        if ($tenant->unit) {
            $tenant->unit->update(['status' => 'vacant']);
        }

        if ($tenant->passport_photo) {
            Storage::disk('public')->delete($tenant->passport_photo);
        }

        $tenant->delete();

        return redirect()->route('tenants.index')
            ->with('success', 'Tenant removed successfully.');
    }
}