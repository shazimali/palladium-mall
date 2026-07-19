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
use App\Models\Landlord;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UnitController extends Controller
{
    public function index(Request $request): View
    {
        $baseQuery = Unit::query()
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->floor_id, fn($q) => $q->where('floor_id', $request->floor_id))
            ->when($request->block_id, fn($q) => $q->where('block_id', $request->block_id))
            ->when($request->area_id, fn($q) => $q->where('area_id', $request->area_id))
            ->when($request->filled('is_self'), fn($q) => $q->where('is_self', (bool) $request->is_self));

        $counts = [
            'total'   => (clone $baseQuery)->count(),
            'vacant'  => (clone $baseQuery)->where('status', 'vacant')->count(),
            'rented'  => (clone $baseQuery)->where('status', 'rented')->count(),
            'self'    => (clone $baseQuery)->where('status', 'self')->count(),
            'is_self' => (clone $baseQuery)->where('is_self', true)->count(),
        ];

        $units = $baseQuery
            ->with(['floor', 'block', 'area', 'landlord'])
            ->orderBy('unit_number')
            ->paginate(20)
            ->withQueryString();

        $floors = Floor::orderBy('name')->get();
        $blocks = Block::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();

        return view('units.index', [
            'title' => 'Flat / Shop Master',
            'units' => $units,
            'counts' => $counts,
            'floors' => $floors,
            'blocks' => $blocks,
            'areas' => $areas,
        ]);
    }

    public function print(Request $request): View
    {
        $units = Unit::query()
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->when($request->floor_id, fn($q) => $q->where('floor_id', $request->floor_id))
            ->when($request->block_id, fn($q) => $q->where('block_id', $request->block_id))
            ->when($request->area_id, fn($q) => $q->where('area_id', $request->area_id))
            ->when($request->filled('is_self'), fn($q) => $q->where('is_self', (bool) $request->is_self))
            ->with(['floor', 'block', 'area', 'landlord'])
            ->orderBy('unit_number')
            ->get();

        return view('units.print', [
            'title' => 'Flat / Shop Master List',
            'units' => $units,
        ]);
    }

    public function printOne(Unit $unit): View
    {
        $unit->load([
            'floor',
            'block',
            'area',
            'landlord',
            'currentOwnership.landlord',
        ]);

        return view('units.print_one', [
            'unit' => $unit,
        ]);
    }

    public function create(Request $request): View
    {
        $floors = Floor::orderBy('name')->get();
        $blocks = Block::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        $landlords = Landlord::orderBy('name')->get();
        
        $selectedLandlordId = $request->query('landlord_id');
        $unit = new Unit();

        return view('units.create', [
            'title'              => 'Add New Flat/Shop',
            'unit'               => $unit,
            'floors'             => $floors,
            'blocks'             => $blocks,
            'areas'              => $areas,
            'landlords'          => $landlords,
            'selectedLandlordId' => $selectedLandlordId,
        ]);
    }

    public function store(StoreUnitRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $unit = Unit::create([
            'unit_number'             => $data['unit_number'],
            'type'                    => $data['type'],
            'floor_id'                => $data['floor_id'],
            'block_id'                => $data['block_id'] ?? null,
            'area_id'                 => $data['area_id'] ?? null,
            'area_sqft'               => $data['area_sqft'] ?? null,
            'file_no'                 => $data['file_no'] ?? null,
            'date'                    => $data['date'] ?? now()->toDateString(),
            'status'                     => $data['status'] ?? 'vacant',
            'landlord_id'                => $data['landlord_id'] ?? null,
            'is_self'                    => $data['is_self'] ?? false,
            'default_maintenance_charge' => $data['default_maintenance_charge'] ?? null,
            'default_monthly_rent'       => $data['default_monthly_rent'] ?? null,
        ]);

        if (!empty($data['landlord_id'])) {
            \App\Models\UnitOwnership::create([
                'unit_id'               => $unit->id,
                'landlord_id'           => $data['landlord_id'],
                'is_current'            => true,
                'start_date'            => $data['date'] ?? now()->toDateString(),
                'nominee_name'          => $data['nominee_name'] ?? null,
                'nominee_relation_type' => $data['nominee_relation_type'] ?? null,
                'nominee_relation_name' => $data['nominee_relation_name'] ?? null,
                'total_amount'          => $data['total_amount'] ?? null,
                'received_amount'       => $data['received_amount'] ?? null,
                'received_from'         => $data['received_from'] ?? null,
                'approved_by'           => $data['approved_by'] ?? null,
                'received_by'           => $data['received_by'] ?? null,
                'approved_date'         => $data['approved_date'] ?? null,
                'notes'                 => $data['notes'] ?? null,
            ]);
        }

        return redirect()->route('units.show', $unit)
            ->with('success', "Flat/Shop {$unit->unit_number} created successfully.");
    }

    public function show(Unit $unit): View
    {
        $unit->load([
            'floor',
            'block',
            'area',
            'meters',
            'landlord',
            'ownerships.landlord',
            'currentOwnership.landlord',
            'agreements.tenant',
            'payments.tenant',
            'payments.paymentAccount',
            'otherTenant',
            'otherTenantHistory.otherTenant',
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

        // Inject ownership events into timeline
        foreach ($unit->ownerships as $ownership) {
            $timeline->push([
                'type' => $ownership->is_current ? 'ownership_current' : 'ownership_transfer',
                'date' => $ownership->start_date ?? $ownership->created_at,
                'title' => $ownership->is_current ? 'Flat/Shop Ownership (Current)' : 'Ownership Transferred',
                'subtitle' => 'Landlord: ' . ($ownership->landlord->name ?? '—'),
                'details' => 'Total: Rs. ' . number_format((float) $ownership->total_amount)
                    . ' | Received: Rs. ' . number_format((float) $ownership->received_amount)
                    . ' | Credit: Rs. ' . number_format((float) $ownership->credit_amount)
                    . ($unit->file_no ? ' | File: ' . $unit->file_no : ''),
                'status' => $ownership->is_current ? 'active' : 'transferred',
                'status_badge' => $ownership->is_current
                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                    : 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                'icon' => '🏢',
                'url' => route('landlords.show', $ownership->landlord_id),
            ]);
        }
        // Inject other tenant history events into timeline
        foreach ($unit->otherTenantHistory as $history) {
            if ($history->attached_at) {
                $timeline->push([
                    'type' => 'other_tenant_attached',
                    'date' => $history->attached_at,
                    'title' => 'Other Tenant Attached',
                    'subtitle' => 'Occupant: ' . ($history->otherTenant->name ?? '—'),
                    'details' => 'Attached to unit on ' . $history->attached_at->format('d M Y'),
                    'status' => 'attached',
                    'status_badge' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                    'icon' => '🔗',
                    'url' => route('other-tenants.show', $history->other_tenant_id),
                ]);
            }
            if ($history->detached_at) {
                $timeline->push([
                    'type' => 'other_tenant_detached',
                    'date' => $history->detached_at,
                    'title' => 'Other Tenant Detached',
                    'subtitle' => 'Occupant: ' . ($history->otherTenant->name ?? '—'),
                    'details' => 'Detached from unit on ' . $history->detached_at->format('d M Y'),
                    'status' => 'detached',
                    'status_badge' => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
                    'icon' => '🔓',
                    'url' => route('other-tenants.show', $history->other_tenant_id),
                ]);
            }
        }

        // Sort chronological timeline by date descending
        $timeline = $timeline->sortByDesc('date')->values();

        return view('units.show', [
            'title' => 'Unit — ' . $unit->unit_number,
            'unit' => $unit,
            'meters' => $unit->meters->keyBy('type'),
            'ownerships' => $unit->ownerships,
            'total_earnings' => $total_earnings,
            'total_outstanding' => $total_outstanding,
            'agreements_count' => $agreements_count,
            'timeline' => $timeline,
        ]);
    }

    public function edit(Unit $unit): View
    {
        $unit->load(['meters', 'landlord', 'currentOwnership']);

        $floors = Floor::orderBy('name')->get();
        $blocks = Block::orderBy('name')->get();
        $areas = Area::orderBy('name')->get();
        $landlords = Landlord::orderBy('name')->get();

        return view('units.edit', [
            'title'          => 'Update Flat/Shop — ' . $unit->unit_number,
            'unit'           => $unit,
            'existingMeters' => $unit->meters->keyBy('type'),
            'floors'         => $floors,
            'blocks'         => $blocks,
            'areas'          => $areas,
            'landlords'      => $landlords,
        ]);
    }

    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $data = $request->validated();
        $oldLandlordId = $unit->landlord_id;
        $newLandlordId = $data['landlord_id'] ?? null;

        // Update structural unit fields
        $unit->update([
            'unit_number'             => $data['unit_number'],
            'type'                    => $data['type'],
            'floor_id'                => $data['floor_id'],
            'block_id'                => $data['block_id'] ?? null,
            'area_id'                 => $data['area_id'] ?? null,
            'area_sqft'               => $data['area_sqft'] ?? null,
            'file_no'                 => $data['file_no'] ?? null,
            'date'                    => $data['date'] ?? $unit->date,
            'status'                     => $data['status'] ?? $unit->status,
            'landlord_id'                => $newLandlordId,
            'is_self'                    => $data['is_self'] ?? false,
            'default_maintenance_charge' => $data['default_maintenance_charge'] ?? null,
            'default_monthly_rent'       => $data['default_monthly_rent'] ?? null,
        ]);

        // Manage ownership records
        if ($oldLandlordId != $newLandlordId) {
            // Landlord changed or removed
            if ($oldLandlordId) {
                // Close the old ownership record
                \App\Models\UnitOwnership::where('unit_id', $unit->id)
                    ->where('is_current', true)
                    ->update([
                        'is_current' => false,
                        'end_date'   => $data['date'] ?? now()->toDateString(),
                    ]);
            }

            if ($newLandlordId) {
                // Create a new ownership record
                \App\Models\UnitOwnership::create([
                    'unit_id'               => $unit->id,
                    'landlord_id'           => $newLandlordId,
                    'is_current'            => true,
                    'start_date'            => $data['date'] ?? now()->toDateString(),
                    'nominee_name'          => $data['nominee_name'] ?? null,
                    'nominee_relation_type' => $data['nominee_relation_type'] ?? null,
                    'nominee_relation_name' => $data['nominee_relation_name'] ?? null,
                    'total_amount'          => $data['total_amount'] ?? null,
                    'received_amount'       => $data['received_amount'] ?? null,
                    'received_from'         => $data['received_from'] ?? null,
                    'approved_by'           => $data['approved_by'] ?? null,
                    'received_by'           => $data['received_by'] ?? null,
                    'approved_date'         => $data['approved_date'] ?? null,
                    'notes'                 => $data['notes'] ?? null,
                ]);
            }
        } else {
            // Landlord didn't change (could be the same landlord, or both are null)
            if ($newLandlordId) {
                // Update or create current ownership
                \App\Models\UnitOwnership::updateOrCreate(
                    ['unit_id' => $unit->id, 'is_current' => true],
                    [
                        'landlord_id'           => $newLandlordId,
                        'nominee_name'          => $data['nominee_name'] ?? null,
                        'nominee_relation_type' => $data['nominee_relation_type'] ?? null,
                        'nominee_relation_name' => $data['nominee_relation_name'] ?? null,
                        'total_amount'          => $data['total_amount'] ?? null,
                        'received_amount'       => $data['received_amount'] ?? null,
                        'received_from'         => $data['received_from'] ?? null,
                        'approved_by'           => $data['approved_by'] ?? null,
                        'received_by'           => $data['received_by'] ?? null,
                        'approved_date'         => $data['approved_date'] ?? null,
                        'notes'                 => $data['notes'] ?? null,
                    ]
                );
            } else {
                // No landlord assigned, close any current ownership if it somehow exists
                \App\Models\UnitOwnership::where('unit_id', $unit->id)
                    ->where('is_current', true)
                    ->update([
                        'is_current' => false,
                        'end_date'   => $data['date'] ?? now()->toDateString(),
                    ]);
            }
        }

        return redirect()->route('units.show', $unit)
            ->with('success', "Flat/Shop {$unit->unit_number} updated successfully.");
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

    public function importForm(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('units.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('units.import', [
            'title' => 'Import Flats/Shops via CSV',
        ]);
    }

    public function downloadTemplate()
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('units.create')) {
            abort(403, 'Unauthorized action.');
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="units_import_template.csv"',
        ];

        $columns = [
            'unit_number',
            'floor',
            'block',
            'area',
            'type',
            'status',
            'landlord_name',
            'area_sqft',
            'date',
            'electricity_meter',
            'water_meter',
            'gas_meter',
            'notes'
        ];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            // Example Row 1
            fputcsv($file, [
                'A-101',
                '1st',
                'Abubakar',
                'Single',
                'flat',
                'vacant',
                'Malik Riaz',
                '1200',
                '2026-06-06',
                'ELEC-A101',
                'WAT-A101',
                'GAS-A101',
                'Spacious 2-bedroom flat facing the park.'
            ]);

            // Example Row 2
            fputcsv($file, [
                'S-G01',
                'Ground',
                'Usman',
                'Double',
                'shop',
                'rented',
                'Mian Mansha',
                '850',
                '2026-06-01',
                'ELEC-SG01',
                '',
                '',
                'Corner shop near the main gate.'
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function importSubmit(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('units.create')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt'],
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        if (!$handle) {
            return back()->withErrors(['csv_file' => 'Could not open the uploaded CSV file.']);
        }

        $headers = fgetcsv($handle);
        if (!$headers) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'Uploaded CSV file is empty.']);
        }

        // Clean headers (trim, lowercase)
        $headers = array_map(function ($h) {
            return trim(strtolower($h));
        }, $headers);

        // Verify required headers
        $requiredHeaders = ['unit_number', 'floor', 'block', 'area', 'type', 'status', 'landlord_name'];
        $missingHeaders = array_diff($requiredHeaders, $headers);
        if (!empty($missingHeaders)) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'Missing required CSV headers: ' . implode(', ', $missingHeaders)]);
        }

        $errors = [];
        $csvUnitNumbers = [];
        $validRows = [];
        $rowIndex = 2; // Row 1 is headers

        while (($row = fgetcsv($handle)) !== false) {
            // Skip empty rows
            if (count($row) === 1 && empty($row[0])) {
                $rowIndex++;
                continue;
            }

            // Combine headers with row values
            $rowData = [];
            foreach ($headers as $index => $header) {
                $rowData[$header] = isset($row[$index]) ? trim($row[$index]) : '';
            }

            $rowErrors = [];

            // 1. unit_number validation
            if (empty($rowData['unit_number'])) {
                $rowErrors[] = "Unit Number is required.";
            } else {
                $unitNumber = $rowData['unit_number'];
                if (in_array($unitNumber, $csvUnitNumbers)) {
                    $rowErrors[] = "Duplicate Unit Number '{$unitNumber}' within CSV.";
                } else {
                    $csvUnitNumbers[] = $unitNumber;
                }

                // Check DB uniqueness
                if (Unit::where('unit_number', $unitNumber)->exists()) {
                    $rowErrors[] = "Unit Number '{$unitNumber}' already exists in database.";
                }
            }

            // 2. Type validation
            if (empty($rowData['type'])) {
                $rowErrors[] = "Type is required.";
            } else {
                $type = strtolower($rowData['type']);
                if (!in_array($type, ['flat', 'shop', 'office'])) {
                    $rowErrors[] = "Type '{$rowData['type']}' is invalid. Allowed: flat, shop, office.";
                } else {
                    $rowData['type'] = $type;
                }
            }

            // 3. Status validation
            if (empty($rowData['status'])) {
                $rowErrors[] = "Status is required.";
            } else {
                $status = strtolower($rowData['status']);
                if (!in_array($status, ['vacant', 'rented', 'self'])) {
                    $rowErrors[] = "Status '{$rowData['status']}' is invalid. Allowed: vacant, rented, self.";
                } else {
                    $rowData['status'] = $status;
                }
            }

            // 4. Required lookups
            if (empty($rowData['floor'])) {
                $rowErrors[] = "Floor is required.";
            }
            if (empty($rowData['block'])) {
                $rowErrors[] = "Block is required.";
            }
            if (empty($rowData['area'])) {
                $rowErrors[] = "Area/Zone is required.";
            }

            // 5. Landlord name lookup check
            if (empty($rowData['landlord_name'])) {
                $rowErrors[] = "Landlord Name is required.";
            } else {
                $landlordName = $rowData['landlord_name'];
                $landlordExists = Landlord::where('name', 'like', $landlordName)->exists();
                if (!$landlordExists) {
                    $rowErrors[] = "Landlord '{$landlordName}' does not exist. Create the landlord first.";
                }
            }

            // 6. Optional numeric check
            if (!empty($rowData['area_sqft']) && !is_numeric($rowData['area_sqft'])) {
                $rowErrors[] = "Area Sqft must be numeric.";
            }

            // 7. Optional date check
            if (!empty($rowData['date'])) {
                try {
                    Carbon::parse($rowData['date']);
                } catch (\Exception $e) {
                    $rowErrors[] = "Invalid Date format '{$rowData['date']}'. Use YYYY-MM-DD.";
                }
            }

            if (!empty($rowErrors)) {
                $errors[$rowIndex] = $rowErrors;
            } else {
                $validRows[$rowIndex] = $rowData;
            }

            $rowIndex++;
        }
        fclose($handle);

        if (!empty($errors)) {
            return back()->with('import_errors', $errors)->withInput();
        }

        DB::beginTransaction();
        try {
            $importedCount = 0;
            foreach ($validRows as $rowData) {
                // Find or create lookups
                $floor = Floor::firstOrCreate(['name' => trim($rowData['floor'])]);
                $block = Block::firstOrCreate(['name' => trim($rowData['block'])]);
                $area = Area::firstOrCreate(['name' => trim($rowData['area'])]);
                $landlord = Landlord::where('name', 'like', trim($rowData['landlord_name']))->first();

                $date = !empty($rowData['date']) ? Carbon::parse($rowData['date'])->toDateString() : now()->toDateString();

                $unit = Unit::create([
                    'unit_number' => $rowData['unit_number'],
                    'floor_id' => $floor->id,
                    'block_id' => $block->id,
                    'area_id' => $area->id,
                    'landlord_id' => $landlord->id,
                    'type' => $rowData['type'],
                    'status' => $rowData['status'],
                    'area_sqft' => !empty($rowData['area_sqft']) ? (float) $rowData['area_sqft'] : null,
                    'notes' => !empty($rowData['notes']) ? $rowData['notes'] : null,
                    'date' => $date,
                ]);

                // Create the initial ownership record
                \App\Models\UnitOwnership::create([
                    'unit_id' => $unit->id,
                    'landlord_id' => $landlord->id,
                    'is_current' => true,
                    'start_date' => $date,
                    'notes' => 'Imported via CSV',
                ]);

                // Create meters if specified
                if (!empty($rowData['electricity_meter'])) {
                    Meter::create([
                        'unit_id' => $unit->id,
                        'type' => 'electricity',
                        'meter_ref_no' => trim($rowData['electricity_meter']),
                        'is_active' => true,
                    ]);
                }
                if (!empty($rowData['water_meter'])) {
                    Meter::create([
                        'unit_id' => $unit->id,
                        'type' => 'water',
                        'meter_ref_no' => trim($rowData['water_meter']),
                        'is_active' => true,
                    ]);
                }
                if (!empty($rowData['gas_meter'])) {
                    Meter::create([
                        'unit_id' => $unit->id,
                        'type' => 'gas',
                        'meter_ref_no' => trim($rowData['gas_meter']),
                        'is_active' => true,
                    ]);
                }

                $importedCount++;
            }

            if (class_exists(ActivityLog::class)) {
                ActivityLog::log('import_csv', "Bulk imported {$importedCount} units via CSV file");
            }

            DB::commit();

            return redirect()->route('units.index')
                ->with('success', "Successfully imported {$importedCount} units.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['csv_file' => 'Database error occurred: ' . $e->getMessage()]);
        }
    }
}
