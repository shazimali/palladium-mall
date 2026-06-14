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
            'title' => 'Tenants',
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
            'units' => Unit::orderBy('unit_number')->get(),
        ]);
    }

    public function store(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->has('phone')) {
            $request->merge(['phone' => preg_replace('/[^\d+]/', '', $request->input('phone'))]);
        }
        if ($request->has('whatsapp_number')) {
            $request->merge(['whatsapp_number' => preg_replace('/[^\d+]/', '', $request->input('whatsapp_number'))]);
        }

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'father_name'      => 'nullable|string|max:255',
            'cnic'             => 'required|string|max:15',
            'gender'           => 'nullable|in:male,female,other',
            'marital_status'   => 'nullable|in:single,married,divorced,widowed',
            'phone'            => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            'whatsapp_number'  => ['nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'email'            => 'nullable|email|max:255',
            'address'          => 'required|string|max:500',
            'occupation'       => 'nullable|string|max:255',
            'monthly_income'   => 'nullable|numeric|min:0',
            'tenancy_type'     => 'nullable|in:residential,commercial,student',
            'adults_count'     => 'nullable|integer|min:1',
            'children_count'   => 'nullable|integer|min:0',
            'passport_photo'   => 'nullable|image|max:2048',
            'cnic_front_image' => 'nullable|image|max:2048',
            'cnic_back_image'  => 'nullable|image|max:2048',
            'delete_passport_photo'       => 'nullable|boolean',
            'delete_cnic_front_image'     => 'nullable|boolean',
            'delete_cnic_back_image'      => 'nullable|boolean',
            'unit_id'          => 'required|exists:units,id',
            // Emergency contact (one mandatory)
            'ec_name'          => 'required|string|max:255',
            'ec_relation'      => 'required|in:father,mother,brother,sister,wife,husband,son,daughter,other',
            'ec_phone'         => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            // Partners
            'rented_by_multiple' => 'required|boolean',
            'partners'         => 'required_if:rented_by_multiple,1|array|min:1',
            'partners.*.name'  => 'required_with:partners|string|max:255',
            'partners.*.father_name' => 'nullable|string|max:255',
            'partners.*.cnic'  => ['required_with:partners', 'string', 'max:15', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'partners.*.gender' => 'nullable|in:male,female,other',
            'partners.*.marital_status' => 'nullable|in:single,married,divorced,widowed',
            'partners.*.phone' => ['required_with:partners', 'string', 'max:20'],
            'partners.*.whatsapp_number' => 'nullable|string|max:20',
            'partners.*.email' => 'nullable|email|max:255',
            'partners.*.address' => 'required_with:partners|string|max:500',
            'partners.*.occupation' => 'nullable|string|max:255',
            'partners.*.monthly_income' => 'nullable|numeric|min:0',
            'partners.*.passport_photo' => 'nullable|image|max:2048',
            'partners.*.cnic_front_image' => 'nullable|image|max:2048',
            'partners.*.cnic_back_image' => 'nullable|image|max:2048',
            'partners.*.delete_passport_photo'   => 'nullable|boolean',
            'partners.*.delete_cnic_front_image' => 'nullable|boolean',
            'partners.*.delete_cnic_back_image'  => 'nullable|boolean',
        ], [
            'unit_id.required'  => 'Please select a flat or shop.',
            'phone.regex'       => 'Phone format must be digits only (e.g. 03001234567)',
            'whatsapp_number.regex' => 'WhatsApp format must be digits only (e.g. 03001234567)',
            'ec_phone.regex'    => 'Emergency contact phone must be digits only',
            'partners.required_if' => 'At least one partner/co-tenant is required when rented by multiple persons.',
            'partners.min'      => 'At least one partner/co-tenant is required when rented by multiple persons.',
            'partners.*.name.required_with' => 'Partner name is required.',
            'partners.*.cnic.required_with' => 'Partner CNIC is required.',
            'partners.*.cnic.regex' => 'Partner CNIC format must be XXXXX-XXXXXXX-X',
            'partners.*.phone.required_with' => 'Partner phone is required.',
            'partners.*.address.required_with' => 'Partner address is required.',
        ]);

        $data['status'] = 'draft';

        // Check if tenant with this CNIC already exists
        $existingTenant = Tenant::where('cnic', $data['cnic'])->first();

        $tenantData = collect($data)->except([
            'ec_name', 'ec_relation', 'ec_phone', 'partners', 'rented_by_multiple',
            'passport_photo', 'cnic_front_image', 'cnic_back_image'
        ])->toArray();

        if ($request->boolean('delete_passport_photo')) {
            if ($existingTenant && $existingTenant->passport_photo) {
                Storage::disk('public')->delete($existingTenant->passport_photo);
            }
            $tenantData['passport_photo'] = null;
        } elseif ($request->hasFile('passport_photo')) {
            if ($existingTenant && $existingTenant->passport_photo) {
                Storage::disk('public')->delete($existingTenant->passport_photo);
            }
            $tenantData['passport_photo'] = $request->file('passport_photo')->store('tenants/photos', 'public');
        }

        if ($request->boolean('delete_cnic_front_image')) {
            if ($existingTenant && $existingTenant->cnic_front_image) {
                Storage::disk('public')->delete($existingTenant->cnic_front_image);
            }
            $tenantData['cnic_front_image'] = null;
        } elseif ($request->hasFile('cnic_front_image')) {
            if ($existingTenant && $existingTenant->cnic_front_image) {
                Storage::disk('public')->delete($existingTenant->cnic_front_image);
            }
            $tenantData['cnic_front_image'] = $request->file('cnic_front_image')->store('tenants/documents', 'public');
        }

        if ($request->boolean('delete_cnic_back_image')) {
            if ($existingTenant && $existingTenant->cnic_back_image) {
                Storage::disk('public')->delete($existingTenant->cnic_back_image);
            }
            $tenantData['cnic_back_image'] = null;
        } elseif ($request->hasFile('cnic_back_image')) {
            if ($existingTenant && $existingTenant->cnic_back_image) {
                Storage::disk('public')->delete($existingTenant->cnic_back_image);
            }
            $tenantData['cnic_back_image'] = $request->file('cnic_back_image')->store('tenants/documents', 'public');
        }

        if ($existingTenant) {
            $existingTenant->update($tenantData);
            $tenant = $existingTenant;
            $msg = 'Existing tenant profile loaded and updated. Continue with guarantor details.';
        } else {
            $tenant = Tenant::create($tenantData);
            $msg = 'Step 1 saved. Continue with guarantor details.';
        }

        // Save emergency contact (replace existing)
        $tenant->emergencyContacts()->delete();
        $tenant->emergencyContacts()->create([
            'name'     => $data['ec_name'],
            'relation' => $data['ec_relation'],
            'phone'    => preg_replace('/[^\d+]/', '', $data['ec_phone']),
            'address'  => null,
        ]);

        // Keep track of old partners to preserve or delete their files
        $oldPartners = $tenant->partners()->get();
        $oldPartnersMap = $oldPartners->keyBy('cnic');
        $reusedFiles = [];

        // Save partners
        $newPartnersData = [];
        if ($request->input('rented_by_multiple') == 1 && $request->has('partners') && is_array($request->partners)) {
            foreach ($request->partners as $i => $partnerData) {
                if (!empty($partnerData['name'])) {
                    $oldPartner = $oldPartnersMap->get($partnerData['cnic']);
                    
                    $passportPhotoPath = $oldPartner?->passport_photo;
                    if (!empty($partnerData['delete_passport_photo'])) {
                        if ($oldPartner?->passport_photo) {
                            Storage::disk('public')->delete($oldPartner->passport_photo);
                        }
                        $passportPhotoPath = null;
                    } elseif ($request->hasFile("partners.{$i}.passport_photo")) {
                        if ($oldPartner?->passport_photo) {
                            Storage::disk('public')->delete($oldPartner->passport_photo);
                        }
                        $passportPhotoPath = $request->file("partners.{$i}.passport_photo")->store('tenants/photos', 'public');
                    }
                    if ($passportPhotoPath) {
                        $reusedFiles[] = $passportPhotoPath;
                    }

                    $cnicFrontPath = $oldPartner?->cnic_front_image;
                    if (!empty($partnerData['delete_cnic_front_image'])) {
                        if ($oldPartner?->cnic_front_image) {
                            Storage::disk('public')->delete($oldPartner->cnic_front_image);
                        }
                        $cnicFrontPath = null;
                    } elseif ($request->hasFile("partners.{$i}.cnic_front_image")) {
                        if ($oldPartner?->cnic_front_image) {
                            Storage::disk('public')->delete($oldPartner->cnic_front_image);
                        }
                        $cnicFrontPath = $request->file("partners.{$i}.cnic_front_image")->store('tenants/documents', 'public');
                    }
                    if ($cnicFrontPath) {
                        $reusedFiles[] = $cnicFrontPath;
                    }

                    $cnicBackPath = $oldPartner?->cnic_back_image;
                    if (!empty($partnerData['delete_cnic_back_image'])) {
                        if ($oldPartner?->cnic_back_image) {
                            Storage::disk('public')->delete($oldPartner->cnic_back_image);
                        }
                        $cnicBackPath = null;
                    } elseif ($request->hasFile("partners.{$i}.cnic_back_image")) {
                        if ($oldPartner?->cnic_back_image) {
                            Storage::disk('public')->delete($oldPartner->cnic_back_image);
                        }
                        $cnicBackPath = $request->file("partners.{$i}.cnic_back_image")->store('tenants/documents', 'public');
                    }
                    if ($cnicBackPath) {
                        $reusedFiles[] = $cnicBackPath;
                    }

                    $newPartnersData[] = [
                        'name' => $partnerData['name'],
                        'father_name' => $partnerData['father_name'] ?? null,
                        'cnic' => $partnerData['cnic'],
                        'gender' => $partnerData['gender'] ?? null,
                        'marital_status' => $partnerData['marital_status'] ?? null,
                        'phone' => preg_replace('/[^\d+]/', '', $partnerData['phone']),
                        'whatsapp_number' => isset($partnerData['whatsapp_number']) ? preg_replace('/[^\d+]/', '', $partnerData['whatsapp_number']) : null,
                        'email' => $partnerData['email'] ?? null,
                        'address' => $partnerData['address'] ?? null,
                        'occupation' => $partnerData['occupation'] ?? null,
                        'monthly_income' => $partnerData['monthly_income'] ?? null,
                        'passport_photo' => $passportPhotoPath,
                        'cnic_front_image' => $cnicFrontPath,
                        'cnic_back_image' => $cnicBackPath,
                    ];
                }
            }
        }

        // Delete any old partner files that are not reused
        foreach ($oldPartners as $oldP) {
            if ($oldP->passport_photo && !in_array($oldP->passport_photo, $reusedFiles)) {
                Storage::disk('public')->delete($oldP->passport_photo);
            }
            if ($oldP->cnic_front_image && !in_array($oldP->cnic_front_image, $reusedFiles)) {
                Storage::disk('public')->delete($oldP->cnic_front_image);
            }
            if ($oldP->cnic_back_image && !in_array($oldP->cnic_back_image, $reusedFiles)) {
                Storage::disk('public')->delete($oldP->cnic_back_image);
            }
        }

        $tenant->partners()->delete();
        foreach ($newPartnersData as $np) {
            $tenant->partners()->create($np);
        }

        if ($request->expectsJson()) {
            $redirectUrl = $request->input('save_only')
                ? route('tenants.showStep', [$tenant, 1])
                : route('tenants.showStep', [$tenant, 2]);

            return response()->json([
                'success' => true,
                'message' => $request->input('save_only') ? 'Step 1 saved successfully.' : 'Step 1 saved. Proceeding...',
                'tenant'  => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'cnic' => $tenant->cnic,
                    'passport_photo_url' => $tenant->passport_photo_url,
                    'unit' => $tenant->unit ? [
                        'unit_number' => $tenant->unit->unit_number,
                        'floor_name'  => $tenant->unit->floor?->name,
                        'block_name'  => $tenant->unit->block?->name,
                    ] : null,
                ],
                'redirect_url' => $redirectUrl,
            ]);
        }

        if ($request->input('save_only')) {
            return redirect()->route('tenants.showStep', [$tenant, 1])
                ->with('success', 'Step 1 saved.');
        }

        return redirect()->route('tenants.showStep', [$tenant, 2])
            ->with('success', $msg);
    }

    // -----------------------------------------------------------------------
    // Wizard Steps 2–6 — Show & Save
    // -----------------------------------------------------------------------

    public function showStep(Tenant $tenant, int $step): View
    {
        $data = ['title' => 'Add Tenant — Step ' . $step, 'tenant' => $tenant, 'step' => $step];

        return match ($step) {
            1 => view('tenants.wizard.step1', array_merge($data, [
                'units' => Unit::orderBy('unit_number')->get(),
                'partners' => $tenant->partners()->get()->map(fn($p) => [
                    'id'                 => $p->id,
                    'name'               => $p->name,
                    'father_name'        => $p->father_name,
                    'cnic'               => $p->cnic,
                    'gender'             => $p->gender,
                    'marital_status'     => $p->marital_status,
                    'phone'              => $p->phone,
                    'whatsapp_number'    => $p->whatsapp_number,
                    'email'              => $p->email,
                    'address'            => $p->address,
                    'occupation'         => $p->occupation,
                    'monthly_income'     => $p->monthly_income,
                    'passport_photo_url' => $p->passport_photo_url,
                    'cnic_front_url'     => $p->cnic_front_url,
                    'cnic_back_url'      => $p->cnic_back_url,
                ]),
            ])),
            2 => view('tenants.wizard.step2', array_merge($data, [
                'guarantors'        => $tenant->guarantors()->get(),
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
                'checklist' => $tenant->moveInChecklists()->where('type', 'move_in')->first(),
                'agreement' => $tenant->agreements()->latest()->first(),
            ])),
            6 => view('tenants.wizard.step6', array_merge($data, [
                'partners'         => $tenant->partners()->get(),
                'guarantors'       => $tenant->guarantors()->get(),
                'guarantor'        => $tenant->guarantors()->first(),
                'emergencyContacts' => $tenant->emergencyContacts,
                'agreement'        => $tenant->agreements()->latest()->first(),
                'docChecklist'     => $tenant->documentChecklist,
                'moveInChecklist'  => $tenant->moveInChecklists()->where('type', 'move_in')->first(),
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
            // Multiple guarantors array
            'guarantors'                => 'required|array|min:1',
            'guarantors.*.name'         => 'required|string|max:255',
            'guarantors.*.cnic'         => ['required', 'string', 'max:20', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'guarantors.*.relation'     => 'required|in:dealer,friend,relative,employer,other',
            'guarantors.*.phone'        => ['required', 'string', 'max:20'],
            'guarantors.*.address'      => 'required|string|max:500',
            'guarantors.*.occupation'   => 'nullable|string|max:255',
            'guarantors.*.shop_name'    => 'nullable|string|max:255',
            'guarantors.*.cnic_front'    => 'nullable|image|max:2048',
            'guarantors.*.cnic_back'     => 'nullable|image|max:2048',
            'guarantors.*.photo'         => 'nullable|image|max:2048',
            'guarantors.*.visiting_card' => 'nullable|image|max:2048',
            'guarantors.*.delete_cnic_front'    => 'nullable|boolean',
            'guarantors.*.delete_cnic_back'     => 'nullable|boolean',
            'guarantors.*.delete_photo'         => 'nullable|boolean',
            'guarantors.*.delete_visiting_card' => 'nullable|boolean',
        ]);

        // Normalize phones
        foreach ($data['guarantors'] as $i => $g) {
            $data['guarantors'][$i]['phone'] = preg_replace('/[^\d+]/', '', $g['phone']);
        }

        // Keep track of old guarantors to preserve or delete their files
        $oldGuarantors = $tenant->guarantors()->get();
        $oldGuarantorsMap = $oldGuarantors->keyBy('cnic');
        $reusedFiles = [];

        // Save guarantors
        $newGuarantorsData = [];
        if ($request->has('guarantors') && is_array($request->guarantors)) {
            foreach ($request->guarantors as $i => $gData) {
                $oldG = $oldGuarantorsMap->get($gData['cnic']);
                
                // cnic_front
                $cnicFrontPath = $oldG?->cnic_front;
                if (!empty($gData['delete_cnic_front'])) {
                    if ($oldG?->cnic_front) {
                        Storage::disk('public')->delete($oldG->cnic_front);
                    }
                    $cnicFrontPath = null;
                } elseif ($request->hasFile("guarantors.{$i}.cnic_front")) {
                    if ($oldG?->cnic_front) {
                        Storage::disk('public')->delete($oldG->cnic_front);
                    }
                    $cnicFrontPath = $request->file("guarantors.{$i}.cnic_front")->store('tenants/guarantors', 'public');
                }
                if ($cnicFrontPath) {
                    $reusedFiles[] = $cnicFrontPath;
                }

                // cnic_back
                $cnicBackPath = $oldG?->cnic_back;
                if (!empty($gData['delete_cnic_back'])) {
                    if ($oldG?->cnic_back) {
                        Storage::disk('public')->delete($oldG->cnic_back);
                    }
                    $cnicBackPath = null;
                } elseif ($request->hasFile("guarantors.{$i}.cnic_back")) {
                    if ($oldG?->cnic_back) {
                        Storage::disk('public')->delete($oldG->cnic_back);
                    }
                    $cnicBackPath = $request->file("guarantors.{$i}.cnic_back")->store('tenants/guarantors', 'public');
                }
                if ($cnicBackPath) {
                    $reusedFiles[] = $cnicBackPath;
                }

                // photo
                $photoPath = $oldG?->photo;
                if (!empty($gData['delete_photo'])) {
                    if ($oldG?->photo) {
                        Storage::disk('public')->delete($oldG->photo);
                    }
                    $photoPath = null;
                } elseif ($request->hasFile("guarantors.{$i}.photo")) {
                    if ($oldG?->photo) {
                        Storage::disk('public')->delete($oldG->photo);
                    }
                    $photoPath = $request->file("guarantors.{$i}.photo")->store('tenants/guarantors', 'public');
                }
                if ($photoPath) {
                    $reusedFiles[] = $photoPath;
                }

                // visiting_card
                $visitingCardPath = $oldG?->visiting_card;
                if (!empty($gData['delete_visiting_card'])) {
                    if ($oldG?->visiting_card) {
                        Storage::disk('public')->delete($oldG->visiting_card);
                    }
                    $visitingCardPath = null;
                } elseif ($request->hasFile("guarantors.{$i}.visiting_card")) {
                    if ($oldG?->visiting_card) {
                        Storage::disk('public')->delete($oldG->visiting_card);
                    }
                    $visitingCardPath = $request->file("guarantors.{$i}.visiting_card")->store('tenants/guarantors', 'public');
                }
                if ($visitingCardPath) {
                    $reusedFiles[] = $visitingCardPath;
                }

                // Fallbacks for older columns
                $visitingCardPhotoPath = $oldG?->visiting_card_photo ?: $visitingCardPath;
                $cnicImagePath = $oldG?->cnic_image ?: $cnicFrontPath;

                $newGuarantorsData[] = [
                    'name' => $gData['name'],
                    'cnic' => $gData['cnic'],
                    'relation' => $gData['relation'],
                    'phone' => preg_replace('/[^\d+]/', '', $gData['phone']),
                    'address' => $gData['address'],
                    'occupation' => $gData['occupation'] ?? null,
                    'shop_name' => $gData['shop_name'] ?? null,
                    'cnic_front' => $cnicFrontPath,
                    'cnic_back' => $cnicBackPath,
                    'photo' => $photoPath,
                    'visiting_card' => $visitingCardPath,
                    'visiting_card_photo' => $visitingCardPhotoPath,
                    'cnic_image' => $cnicImagePath,
                ];
            }
        }

        // Delete any old guarantor files that are not reused
        foreach ($oldGuarantors as $oldG) {
            if ($oldG->cnic_front && !in_array($oldG->cnic_front, $reusedFiles)) {
                Storage::disk('public')->delete($oldG->cnic_front);
            }
            if ($oldG->cnic_back && !in_array($oldG->cnic_back, $reusedFiles)) {
                Storage::disk('public')->delete($oldG->cnic_back);
            }
            if ($oldG->photo && !in_array($oldG->photo, $reusedFiles)) {
                Storage::disk('public')->delete($oldG->photo);
            }
            if ($oldG->visiting_card && !in_array($oldG->visiting_card, $reusedFiles)) {
                Storage::disk('public')->delete($oldG->visiting_card);
            }
            if ($oldG->visiting_card_photo && !in_array($oldG->visiting_card_photo, $reusedFiles)) {
                Storage::disk('public')->delete($oldG->visiting_card_photo);
            }
            if ($oldG->cnic_image && !in_array($oldG->cnic_image, $reusedFiles)) {
                Storage::disk('public')->delete($oldG->cnic_image);
            }
        }

        $tenant->guarantors()->delete();
        foreach ($newGuarantorsData as $ng) {
            $tenant->guarantors()->create($ng);
        }

        if ($request->input('save_only')) {
            return redirect()->route('tenants.showStep', [$tenant, 2])
                ->with('success', 'Step 2 saved.');
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
            'unit_id'              => 'nullable|exists:units,id',
            'start_date'           => 'required|date',
            'end_date'             => 'required|date|after:start_date',
            'monthly_rent'         => 'required|numeric|min:0',
            'maintenance_charge'   => 'nullable|numeric|min:0',
            'security_deposit'     => 'required|numeric|min:0',
            'payment_due_day'      => 'required|integer|min:1|max:31',
            'grace_period_days'    => 'nullable|integer|min:0',
            'notice_period_months' => 'nullable|integer|min:0',
            'fine_per_day'         => 'required|numeric|min:0',
            'terms'                => 'nullable|string',
            'govt_document'        => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        // Handle govt_document upload
        if ($request->hasFile('govt_document')) {
            $existing = $tenant->agreements()->latest()->first();
            if ($existing?->govt_document) {
                Storage::disk('public')->delete($existing->govt_document);
            }
            $data['govt_document'] = $request->file('govt_document')->store('tenants/documents', 'public');
        }

        // Use unit from tenant (set in Step 1), or override if provided
        $unitId = $data['unit_id'] ?? $tenant->unit_id;
        unset($data['unit_id']);

        if ($unitId) {
            // Mark previous unit as vacant if changed
            if ($tenant->unit_id && $tenant->unit_id !== (int) $unitId) {
                Unit::find($tenant->unit_id)?->update(['status' => 'vacant']);
            }
            
            // Flat status should only be 'rented' if govt_document is uploaded (either in this request or previously)
            $latestAgreement = $tenant->agreements()->latest()->first();
            $hasGovtDocument = isset($data['govt_document']) || ($latestAgreement && !empty($latestAgreement->govt_document));

            if ($hasGovtDocument) {
                Unit::find($unitId)?->update(['status' => 'rented']);
            } else {
                Unit::find($unitId)?->update(['status' => 'vacant']);
            }
            
            $tenant->update(['unit_id' => $unitId]);
            $data['unit_id'] = $unitId;
        }

        // Expire any previous active agreements
        $tenant->agreements()->where('status', 'active')->update(['status' => 'expired']);

        // Upsert agreement
        $tenant->agreements()->updateOrCreate(
            ['tenant_id' => $tenant->id, 'status' => 'draft'],
            array_merge($data, ['status' => 'draft'])
        );

        if ($request->input('save_only')) {
            return redirect()->route('tenants.showStep', [$tenant, 3])
                ->with('success', 'Step 3 saved.');
        }

        return redirect()->route('tenants.showStep', [$tenant, 4])
            ->with('success', 'Step 3 saved.');
    }

    // -----------------------------------------------------------------------
    // Step 4 — Document Checklist
    // -----------------------------------------------------------------------

    private function saveStep4(Request $request, Tenant $tenant): RedirectResponse
    {
        $booleans = [
            'cnic_copy_tenant_front',
            'cnic_copy_tenant_back',
            'cnic_copy_father',
            'cnic_copy_guarantor',
            'passport_photo',
            'nikah_nama',
            'frc_form_b',
            'police_verification',
            'tenant_application_form',
            'tenancy_agreement_copy',
            'rules_acknowledgment',
            'inspection_report',
            'property_handover_form',
            'security_deposit_receipt',
            'meter_picture',
            'emergency_contacts_added',
            'guarantor_info_added',
            'guarantor_business_card',
            'tenant_business_card',
            'property_advisor_card',
            'old_tenant_verification',
            'business_license',
            'utility_bills_clearance',
        ];

        $data = [];
        foreach ($booleans as $field) {
            $data[$field] = $request->boolean($field);
        }
        $data['notes'] = $request->input('notes');

        $checklist = $tenant->documentChecklist()->firstOrNew(['tenant_id' => $tenant->id]);

        // Handle file uploads
        $fileFields = [
            'cnic_front_image',
            'cnic_back_image',
            'signed_agreement_scan',
            'bank_voucher',
            'cnic_copy_father_file',
            'cnic_copy_guarantor_file',
            'passport_photo_file',
            'nikah_nama_file',
            'frc_form_b_file',
            'police_verification_file',
            'tenant_application_form_file',
            'rules_acknowledgment_file',
            'inspection_report_file',
            'property_handover_form_file',
            'meter_picture_file',
            'emergency_contacts_added_file',
            'guarantor_info_added_file',
            'guarantor_business_card_file',
            'tenant_business_card_file',
            'property_advisor_card_file',
            'old_tenant_verification_file',
            'business_license_file',
            'utility_bills_clearance_file',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                if ($checklist->{$field}) {
                    Storage::disk('public')->delete($checklist->{$field});
                }
                $data[$field] = $request->file($field)->store('tenants/documents', 'public');
            }
        }

        // Auto-check checkboxes if files are uploaded or already exist
        $fileToCheckboxMap = [
            'cnic_front_image' => 'cnic_copy_tenant_front',
            'cnic_back_image' => 'cnic_copy_tenant_back',
            'signed_agreement_scan' => 'tenancy_agreement_copy',
            'bank_voucher' => 'security_deposit_receipt',
            'cnic_copy_father_file' => 'cnic_copy_father',
            'cnic_copy_guarantor_file' => 'cnic_copy_guarantor',
            'passport_photo_file' => 'passport_photo',
            'nikah_nama_file' => 'nikah_nama',
            'frc_form_b_file' => 'frc_form_b',
            'police_verification_file' => 'police_verification',
            'tenant_application_form_file' => 'tenant_application_form',
            'rules_acknowledgment_file' => 'rules_acknowledgment',
            'inspection_report_file' => 'inspection_report',
            'property_handover_form_file' => 'property_handover_form',
            'meter_picture_file' => 'meter_picture',
            'emergency_contacts_added_file' => 'emergency_contacts_added',
            'guarantor_info_added_file' => 'guarantor_info_added',
            'guarantor_business_card_file' => 'guarantor_business_card',
            'tenant_business_card_file' => 'tenant_business_card',
            'property_advisor_card_file' => 'property_advisor_card',
            'old_tenant_verification_file' => 'old_tenant_verification',
            'business_license_file' => 'business_license',
            'utility_bills_clearance_file' => 'utility_bills_clearance',
        ];

        foreach ($fileToCheckboxMap as $fileField => $checkboxField) {
            $hasFile = !empty($data[$fileField]) || !empty($checklist->{$fileField});
            if ($hasFile) {
                $data[$checkboxField] = true;
            }
        }

        $checklist->fill($data);
        $checklist->tenant_id = $tenant->id;
        $checklist->save();

        if ($request->input('save_only')) {
            return redirect()->route('tenants.showStep', [$tenant, 4])
                ->with('success', 'Step 4 saved.');
        }

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
            'checklist_date' => 'required|date',
            'damage_notes' => 'nullable|string',
            'inventory_notes' => 'nullable|string',
            'flat_condition' => 'nullable|in:good,needs_repair',
            'deposit_deduction' => 'nullable|numeric|min:0',
            'final_remarks' => 'nullable|string',
        ]);

        $booleans = [
            'rooms_cleaned',
            'kitchen_cleaned',
            'bathrooms_cleaned',
            'no_garbage',
            'no_wall_damage',
            'paint_condition_ok',
            'light_fixtures_ok',
            'electric_wiring_ok',
            'no_breaker_issues',
            'furniture_ok',
            'ac_working',
            'kitchen_appliances_ok',
            'stove_clean',
            'keys_returned',
            'doors_locks_ok',
            'windows_ok',
            'balcony_doors_ok',
            'water_supply_ok',
            'electricity_supply_ok',
            'gas_supply_ok',
            'no_pending_utility_bills',
            'no_pending_maintenance',
            'no_pending_rent',
            'fixtures_available',
            'no_missing_items',
            'access_cards_returned',
            'no_pending_requests',
            'move_out_form_signed',
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

        if ($request->input('save_only')) {
            return redirect()->route('tenants.showStep', [$tenant, 5])
                ->with('success', 'Step 5 saved.');
        }

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
    // Print Step Data (Steps 1, 2, 3)
    // -----------------------------------------------------------------------

    public function printStep(Tenant $tenant, int $step)
    {
        $tenant->load(['guarantors', 'emergencyContacts', 'activeAgreement', 'unit']);
        $agreement = $tenant->agreements()->latest()->first();

        if ($step === 1) {
            return view('tenants.print.step1', compact('tenant'));
        } elseif ($step === 2) {
            return view('tenants.print.step2', [
                'tenant' => $tenant,
                'guarantors' => $tenant->guarantors,
                'guarantor' => $tenant->guarantors->first(),
                'emergencyContacts' => $tenant->emergencyContacts,
            ]);
        } elseif ($step === 3) {
            return view('tenants.print.step3', [
                'tenant' => $tenant,
                'agreement' => $agreement,
            ]);
        } elseif ($step === 4) {
            return view('tenants.print.step4', [
                'tenant' => $tenant,
                'checklist' => $tenant->documentChecklist,
            ]);
        } elseif ($step === 5) {
            $checklist = $tenant->moveInChecklists()->where('type', 'move_in')->first();
            return view('tenants.print.step5', [
                'tenant' => $tenant,
                'checklist' => $checklist,
                'agreement' => $agreement,
            ]);
        }

        abort(404);
    }
    // -----------------------------------------------------------------------
    // Show — Tenant Profile
    // -----------------------------------------------------------------------

    public function show(Tenant $tenant): View
    {
        $tenant->load([
            'unit',
            'guarantors',
            'partners',
            'emergencyContacts',
            'activeAgreement',
            'agreements',
            'documentChecklist',
            'moveInChecklists',
        ]);

        return view('tenants.show', [
            'title' => 'Tenant — ' . $tenant->name,
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

    public function update(Request $request, Tenant $tenant): \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
    {
        if ($request->has('phone')) {
            $request->merge(['phone' => preg_replace('/[^\d+]/', '', $request->input('phone'))]);
        }
        if ($request->has('whatsapp_number')) {
            $request->merge(['whatsapp_number' => preg_replace('/[^\d+]/', '', $request->input('whatsapp_number'))]);
        }

        $data = $request->validate([
            'name'             => 'required|string|max:255',
            'father_name'      => 'nullable|string|max:255',
            'cnic'             => 'required|string|max:15',
            'gender'           => 'nullable|in:male,female,other',
            'marital_status'   => 'nullable|in:single,married,divorced,widowed',
            'phone'            => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            'whatsapp_number'  => ['nullable', 'string', 'max:20', 'regex:/^\d+$/'],
            'email'            => 'nullable|email|max:255',
            'address'          => 'required|string|max:500',
            'occupation'       => 'nullable|string|max:255',
            'monthly_income'   => 'nullable|numeric|min:0',
            'tenancy_type'     => 'nullable|in:residential,commercial,student',
            'adults_count'     => 'nullable|integer|min:1',
            'children_count'   => 'nullable|integer|min:0',
            'passport_photo'   => 'nullable|image|max:2048',
            'cnic_front_image' => 'nullable|image|max:2048',
            'cnic_back_image'  => 'nullable|image|max:2048',
            'delete_passport_photo'       => 'nullable|boolean',
            'delete_cnic_front_image'     => 'nullable|boolean',
            'delete_cnic_back_image'      => 'nullable|boolean',
            'unit_id'          => 'required|exists:units,id',
            // Emergency contact (one mandatory)
            'ec_name'          => 'required|string|max:255',
            'ec_relation'      => 'required|in:father,mother,brother,sister,wife,husband,son,daughter,other',
            'ec_phone'         => ['required', 'string', 'max:20', 'regex:/^\d+$/'],
            // Partners
            'rented_by_multiple' => 'required|boolean',
            'partners'         => 'required_if:rented_by_multiple,1|array|min:1',
            'partners.*.name'  => 'required_with:partners|string|max:255',
            'partners.*.father_name' => 'nullable|string|max:255',
            'partners.*.cnic'  => ['required_with:partners', 'string', 'max:15', 'regex:/^\d{5}-\d{7}-\d{1}$/'],
            'partners.*.gender' => 'nullable|in:male,female,other',
            'partners.*.marital_status' => 'nullable|in:single,married,divorced,widowed',
            'partners.*.phone' => ['required_with:partners', 'string', 'max:20'],
            'partners.*.whatsapp_number' => 'nullable|string|max:20',
            'partners.*.email' => 'nullable|email|max:255',
            'partners.*.address' => 'required_with:partners|string|max:500',
            'partners.*.occupation' => 'nullable|string|max:255',
            'partners.*.monthly_income' => 'nullable|numeric|min:0',
            'partners.*.passport_photo' => 'nullable|image|max:2048',
            'partners.*.cnic_front_image' => 'nullable|image|max:2048',
            'partners.*.cnic_back_image' => 'nullable|image|max:2048',
            'partners.*.delete_passport_photo'   => 'nullable|boolean',
            'partners.*.delete_cnic_front_image' => 'nullable|boolean',
            'partners.*.delete_cnic_back_image'  => 'nullable|boolean',
        ], [
            'unit_id.required'  => 'Please select a flat or shop.',
            'phone.regex'       => 'Phone format must be digits only (e.g. 03001234567)',
            'whatsapp_number.regex' => 'WhatsApp format must be digits only (e.g. 03001234567)',
            'ec_phone.regex'    => 'Emergency contact phone must be digits only',
            'partners.required_if' => 'At least one partner/co-tenant is required when rented by multiple persons.',
            'partners.min'      => 'At least one partner/co-tenant is required when rented by multiple persons.',
            'partners.*.name.required_with' => 'Partner name is required.',
            'partners.*.cnic.required_with' => 'Partner CNIC is required.',
            'partners.*.cnic.regex' => 'Partner CNIC format must be XXXXX-XXXXXXX-X',
            'partners.*.phone.required_with' => 'Partner phone is required.',
            'partners.*.address.required_with' => 'Partner address is required.',
        ]);

        $tenantData = collect($data)->except([
            'ec_name', 'ec_relation', 'ec_phone', 'partners', 'rented_by_multiple',
            'passport_photo', 'cnic_front_image', 'cnic_back_image'
        ])->toArray();

        if ($request->boolean('delete_passport_photo')) {
            if ($tenant->passport_photo) {
                Storage::disk('public')->delete($tenant->passport_photo);
            }
            $tenantData['passport_photo'] = null;
        } elseif ($request->hasFile('passport_photo')) {
            if ($tenant->passport_photo) {
                Storage::disk('public')->delete($tenant->passport_photo);
            }
            $tenantData['passport_photo'] = $request->file('passport_photo')->store('tenants/photos', 'public');
        }

        if ($request->boolean('delete_cnic_front_image')) {
            if ($tenant->cnic_front_image) {
                Storage::disk('public')->delete($tenant->cnic_front_image);
            }
            $tenantData['cnic_front_image'] = null;
        } elseif ($request->hasFile('cnic_front_image')) {
            if ($tenant->cnic_front_image) {
                Storage::disk('public')->delete($tenant->cnic_front_image);
            }
            $tenantData['cnic_front_image'] = $request->file('cnic_front_image')->store('tenants/documents', 'public');
        }

        if ($request->boolean('delete_cnic_back_image')) {
            if ($tenant->cnic_back_image) {
                Storage::disk('public')->delete($tenant->cnic_back_image);
            }
            $tenantData['cnic_back_image'] = null;
        } elseif ($request->hasFile('cnic_back_image')) {
            if ($tenant->cnic_back_image) {
                Storage::disk('public')->delete($tenant->cnic_back_image);
            }
            $tenantData['cnic_back_image'] = $request->file('cnic_back_image')->store('tenants/documents', 'public');
        }

        $tenant->update($tenantData);

        // Save emergency contact (replace first one)
        $tenant->emergencyContacts()->delete();
        $tenant->emergencyContacts()->create([
            'name'     => $data['ec_name'],
            'relation' => $data['ec_relation'],
            'phone'    => preg_replace('/[^\d+]/', '', $data['ec_phone']),
            'address'  => null,
        ]);

        // Keep track of old partners to preserve or delete their files
        $oldPartners = $tenant->partners()->get();
        $oldPartnersMap = $oldPartners->keyBy('cnic');
        $reusedFiles = [];

        // Save partners
        $newPartnersData = [];
        if ($request->input('rented_by_multiple') == 1 && $request->has('partners') && is_array($request->partners)) {
            foreach ($request->partners as $i => $partnerData) {
                if (!empty($partnerData['name'])) {
                    $oldPartner = $oldPartnersMap->get($partnerData['cnic']);
                    
                    $passportPhotoPath = $oldPartner?->passport_photo;
                    if (!empty($partnerData['delete_passport_photo'])) {
                        if ($oldPartner?->passport_photo) {
                            Storage::disk('public')->delete($oldPartner->passport_photo);
                        }
                        $passportPhotoPath = null;
                    } elseif ($request->hasFile("partners.{$i}.passport_photo")) {
                        if ($oldPartner?->passport_photo) {
                            Storage::disk('public')->delete($oldPartner->passport_photo);
                        }
                        $passportPhotoPath = $request->file("partners.{$i}.passport_photo")->store('tenants/photos', 'public');
                    }
                    if ($passportPhotoPath) {
                        $reusedFiles[] = $passportPhotoPath;
                    }

                    $cnicFrontPath = $oldPartner?->cnic_front_image;
                    if (!empty($partnerData['delete_cnic_front_image'])) {
                        if ($oldPartner?->cnic_front_image) {
                            Storage::disk('public')->delete($oldPartner->cnic_front_image);
                        }
                        $cnicFrontPath = null;
                    } elseif ($request->hasFile("partners.{$i}.cnic_front_image")) {
                        if ($oldPartner?->cnic_front_image) {
                            Storage::disk('public')->delete($oldPartner->cnic_front_image);
                        }
                        $cnicFrontPath = $request->file("partners.{$i}.cnic_front_image")->store('tenants/documents', 'public');
                    }
                    if ($cnicFrontPath) {
                        $reusedFiles[] = $cnicFrontPath;
                    }

                    $cnicBackPath = $oldPartner?->cnic_back_image;
                    if (!empty($partnerData['delete_cnic_back_image'])) {
                        if ($oldPartner?->cnic_back_image) {
                            Storage::disk('public')->delete($oldPartner->cnic_back_image);
                        }
                        $cnicBackPath = null;
                    } elseif ($request->hasFile("partners.{$i}.cnic_back_image")) {
                        if ($oldPartner?->cnic_back_image) {
                            Storage::disk('public')->delete($oldPartner->cnic_back_image);
                        }
                        $cnicBackPath = $request->file("partners.{$i}.cnic_back_image")->store('tenants/documents', 'public');
                    }
                    if ($cnicBackPath) {
                        $reusedFiles[] = $cnicBackPath;
                    }

                    $newPartnersData[] = [
                        'name' => $partnerData['name'],
                        'father_name' => $partnerData['father_name'] ?? null,
                        'cnic' => $partnerData['cnic'],
                        'gender' => $partnerData['gender'] ?? null,
                        'marital_status' => $partnerData['marital_status'] ?? null,
                        'phone' => preg_replace('/[^\d+]/', '', $partnerData['phone']),
                        'whatsapp_number' => isset($partnerData['whatsapp_number']) ? preg_replace('/[^\d+]/', '', $partnerData['whatsapp_number']) : null,
                        'email' => $partnerData['email'] ?? null,
                        'address' => $partnerData['address'] ?? null,
                        'occupation' => $partnerData['occupation'] ?? null,
                        'monthly_income' => $partnerData['monthly_income'] ?? null,
                        'passport_photo' => $passportPhotoPath,
                        'cnic_front_image' => $cnicFrontPath,
                        'cnic_back_image' => $cnicBackPath,
                    ];
                }
            }
        }

        // Delete any old partner files that are not reused
        foreach ($oldPartners as $oldP) {
            if ($oldP->passport_photo && !in_array($oldP->passport_photo, $reusedFiles)) {
                Storage::disk('public')->delete($oldP->passport_photo);
            }
            if ($oldP->cnic_front_image && !in_array($oldP->cnic_front_image, $reusedFiles)) {
                Storage::disk('public')->delete($oldP->cnic_front_image);
            }
            if ($oldP->cnic_back_image && !in_array($oldP->cnic_back_image, $reusedFiles)) {
                Storage::disk('public')->delete($oldP->cnic_back_image);
            }
        }

        $tenant->partners()->delete();
        foreach ($newPartnersData as $np) {
            $tenant->partners()->create($np);
        }

        if ($request->expectsJson()) {
            $redirectUrl = $request->input('save_only')
                ? route('tenants.showStep', [$tenant, 1])
                : route('tenants.showStep', [$tenant, 2]);

            return response()->json([
                'success' => true,
                'message' => $request->input('save_only') ? 'Step 1 saved successfully.' : 'Step 1 saved. Proceeding...',
                'tenant'  => [
                    'id' => $tenant->id,
                    'name' => $tenant->name,
                    'cnic' => $tenant->cnic,
                    'passport_photo_url' => $tenant->passport_photo_url,
                    'unit' => $tenant->unit ? [
                        'unit_number' => $tenant->unit->unit_number,
                        'floor_name'  => $tenant->unit->floor?->name,
                        'block_name'  => $tenant->unit->block?->name,
                    ] : null,
                ],
                'redirect_url' => $redirectUrl,
            ]);
        }

        if ($request->input('save_only')) {
            return redirect()->route('tenants.showStep', [$tenant, 1])
                ->with('success', 'Personal details saved.');
        }

        return redirect()->route('tenants.showStep', [$tenant, 2])
            ->with('success', 'Personal details updated.');
    }

    // -----------------------------------------------------------------------
    // Destroy
    // -----------------------------------------------------------------------

    public function getTenantByCnic(Request $request): \Illuminate\Http\JsonResponse
    {
        $cnic = $request->query('cnic');
        if (!$cnic) {
            return response()->json(['error' => 'CNIC is required'], 400);
        }

        $tenant = Tenant::where('cnic', $cnic)->first();

        if (!$tenant) {
            return response()->json(['found' => false]);
        }

        return response()->json([
            'found' => true,
            'tenant' => [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'father_name' => $tenant->father_name,
                'date_of_birth' => $tenant->date_of_birth?->format('Y-m-d'),
                'gender' => $tenant->gender,
                'marital_status' => $tenant->marital_status,
                'phone' => $tenant->phone,
                'whatsapp_number' => $tenant->whatsapp_number,
                'email' => $tenant->email,
                'address' => $tenant->address,
                'occupation' => $tenant->occupation,
                'monthly_income' => $tenant->monthly_income,
                'tenancy_type' => $tenant->tenancy_type,
                'adults_count' => $tenant->adults_count,
                'children_count' => $tenant->children_count,
                'passport_photo_url' => $tenant->passport_photo_url,
            ]
        ]);
    }

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