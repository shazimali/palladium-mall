<?php

namespace App\Http\Controllers;

use App\Models\Meter;
use App\Models\MeterReadingVoucher;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class UtilityReadingController extends Controller
{
    /**
     * Display month-wise and unit-wise utility meter readings grid.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() &&
            !$user->hasPermission('utility_readings.view') &&
            !$user->hasPermission('utility_readings.edit') &&
            !$user->hasPermission('utilities.record') &&
            !$user->hasPermission('utility_meters_management') &&
            !$user->hasPermission('meters.edit') &&
            !$user->hasPermission('meter_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $canEdit = $user->isSuperAdmin() ||
                   $user->hasPermission('utility_readings.edit') ||
                   $user->hasPermission('utilities.record') ||
                   $user->hasPermission('meters.edit');

        // Selected Month (default to current month YYYY-MM)
        $selectedMonth = $request->input('month', now()->format('Y-m'));
        try {
            $monthCarbon = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        } catch (\Exception $e) {
            $monthCarbon = now()->startOfMonth();
            $selectedMonth = $monthCarbon->format('Y-m');
        }

        $selectedUnitId = $request->input('unit_id');
        $selectedType   = $request->input('type');
        $selectedStatus = $request->input('status');
        $searchTerm     = trim($request->input('search', ''));

        // Query active meters
        $metersQuery = Meter::query()
            ->with(['unit.floor', 'unit.block', 'unit.tenant', 'unit.otherTenant'])
            ->whereHas('unit');

        if ($selectedUnitId) {
            $metersQuery->where('unit_id', $selectedUnitId);
        }

        if ($selectedType) {
            $metersQuery->where('type', $selectedType);
        }

        if (!empty($searchTerm)) {
            $metersQuery->where(function ($q) use ($searchTerm) {
                $q->where('meter_ref_no', 'like', "%{$searchTerm}%")
                  ->orWhere('meter_consumer_id', 'like', "%{$searchTerm}%")
                  ->orWhereHas('unit', function ($u) use ($searchTerm) {
                      $u->where('unit_number', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $allMeters = $metersQuery->orderBy('unit_id')->orderBy('type')->get();

        // Fetch meter reading vouchers for the selected month
        $startOfMonth = $monthCarbon->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth   = $monthCarbon->copy()->endOfMonth()->format('Y-m-d');

        $vouchers = MeterReadingVoucher::whereDate('date', '>=', $startOfMonth)
            ->whereDate('date', '<=', $endOfMonth)
            ->get()
            ->keyBy(function ($v) {
                return $v->unit_id . '_' . $v->meter_ref_no;
            });

        // Fetch the latest reading prior to the selected month for each meter (to auto-fetch as previous reading)
        $prevVouchers = MeterReadingVoucher::whereDate('date', '<', $startOfMonth)
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy(function ($v) {
                return $v->unit_id . '_' . $v->meter_ref_no;
            })
            ->map(function ($group) {
                return $group->first();
            });

        $prevVouchersByUnit = MeterReadingVoucher::whereDate('date', '<', $startOfMonth)
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('unit_id')
            ->map(function ($group) {
                return $group->first();
            });

        // Format reading rows
        $readings = [];
        $totalUnitsConsumed = 0;
        $totalBilled = 0;
        $totalPaid = 0;
        $totalUnpaid = 0;

        foreach ($allMeters as $meter) {
            $key = $meter->unit_id . '_' . $meter->meter_ref_no;
            $voucher     = $vouchers->get($key);
            $prevVoucher = $prevVouchers->get($key) ?? $prevVouchersByUnit->get($meter->unit_id);

            // Fallback voucher matching by unit + type if ref no wasn't exact
            if (!$voucher) {
                $voucher = $vouchers->first(function ($v) use ($meter) {
                    return $v->unit_id == $meter->unit_id && $v->meter_ref_no == $meter->meter_ref_no;
                });
            }

            // Auto-fetch: Previous month's meter reading (current_reading) becomes this month's prev reading
            $prevReading = 0.00;
            if ($prevVoucher && $prevVoucher->current_reading !== null && (float) $prevVoucher->current_reading > 0) {
                $prevReading = (float) $prevVoucher->current_reading;
            } elseif ($voucher && $voucher->previous_reading !== null && (float) $voucher->previous_reading > 0) {
                $prevReading = (float) $voucher->previous_reading;
            }

            $currentReading = $voucher && $voucher->current_reading !== null ? (float) $voucher->current_reading : 0.00;
            $unitsConsumed  = ($currentReading > 0 && $currentReading >= $prevReading)
                ? round($currentReading - $prevReading, 2)
                : 0.00;

            $amount         = $voucher ? (float) $voucher->amount : 0;
            $status         = $voucher ? strtolower($voucher->status ?? 'unpaid') : 'unpaid';
            $voucherId      = $voucher ? $voucher->id : null;
            $meterImage     = $voucher && $voucher->meter_image ? $voucher->getMeterImageUrlAttribute() : null;

            if ($selectedStatus && $status !== strtolower($selectedStatus)) {
                continue;
            }

            $totalUnitsConsumed += $unitsConsumed;
            $totalBilled += $amount;
            if ($status === 'paid') {
                $totalPaid += $amount;
            } else {
                $totalUnpaid += $amount;
            }

            $isPaidLocked = ($status === 'paid' && !$user->isSuperAdmin());

            $readings[] = [
                'meter_id'          => $meter->id,
                'voucher_id'        => $voucherId,
                'unit_id'           => $meter->unit_id,
                'unit_number'       => $meter->unit->unit_number ?? 'N/A',
                'floor'             => $meter->unit->floor->name ?? 'N/A',
                'block'             => $meter->unit->block->name ?? '',
                'breaker_status'    => strtoupper($meter->unit->breaker_status ?? 'OFF'),
                'tenant_name'       => $meter->unit->tenant->name ?? ($meter->unit->otherTenant->name ?? 'N/A'),
                'meter_type'        => $meter->type,
                'meter_type_label'  => $meter->getTypeLabelAttribute(),
                'meter_ref_no'      => $meter->meter_ref_no ?? 'N/A',
                'meter_consumer_id' => $meter->meter_consumer_id ?? 'N/A',
                'previous_reading'  => $prevReading,
                'current_reading'   => $currentReading,
                'units_consumed'    => $unitsConsumed,
                'available'         => $voucher->available ?? '',
                'amount'            => $amount,
                'status'            => $status,
                'meter_image_url'   => $meterImage,
                'notes'             => $voucher->notes ?? '',
                'is_active'         => (bool) $meter->is_active,
                'is_paid_locked'    => $isPaidLocked,
            ];
        }

        $units = Unit::orderBy('unit_number')->get(['id', 'unit_number']);

        return view('utility_readings.index', [
            'title'              => 'Utility Meter Readings — ' . $monthCarbon->format('F Y'),
            'readings'           => $readings,
            'units'              => $units,
            'selectedMonth'      => $selectedMonth,
            'selectedMonthName'  => $monthCarbon->format('F Y'),
            'selectedUnitId'     => $selectedUnitId,
            'selectedType'       => $selectedType,
            'selectedStatus'     => $selectedStatus,
            'searchTerm'         => $searchTerm,
            'totalUnitsConsumed' => $totalUnitsConsumed,
            'totalBilled'        => $totalBilled,
            'totalPaid'          => $totalPaid,
            'totalUnpaid'        => $totalUnpaid,
            'canEdit'            => $canEdit,
            'isSuperAdmin'       => $user->isSuperAdmin(),
        ]);
    }

    /**
     * Display printable view for Utility Meter Readings.
     */
    public function print(Request $request): View
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() &&
            !$user->hasPermission('utilities.record') &&
            !$user->hasPermission('utility_meters_management') &&
            !$user->hasPermission('meters.edit') &&
            !$user->hasPermission('meter_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $selectedMonth = $request->input('month', now()->format('Y-m'));
        try {
            $monthCarbon = Carbon::createFromFormat('Y-m', $selectedMonth)->startOfMonth();
        } catch (\Exception $e) {
            $monthCarbon = now()->startOfMonth();
            $selectedMonth = $monthCarbon->format('Y-m');
        }

        $selectedUnitId = $request->input('unit_id');
        $selectedType   = $request->input('type');
        $selectedStatus = $request->input('status');
        $searchTerm     = trim($request->input('search', ''));

        $metersQuery = Meter::query()
            ->with(['unit.floor', 'unit.block', 'unit.tenant', 'unit.otherTenant'])
            ->whereHas('unit');

        if ($selectedUnitId) {
            $metersQuery->where('unit_id', $selectedUnitId);
        }

        if ($selectedType) {
            $metersQuery->where('type', $selectedType);
        }

        if (!empty($searchTerm)) {
            $metersQuery->where(function ($q) use ($searchTerm) {
                $q->where('meter_ref_no', 'like', "%{$searchTerm}%")
                  ->orWhere('meter_consumer_id', 'like', "%{$searchTerm}%")
                  ->orWhereHas('unit', function ($u) use ($searchTerm) {
                      $u->where('unit_number', 'like', "%{$searchTerm}%");
                  });
            });
        }

        $allMeters = $metersQuery->orderBy('unit_id')->orderBy('type')->get();

        $startOfMonth = $monthCarbon->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth   = $monthCarbon->copy()->endOfMonth()->format('Y-m-d');

        $vouchers = MeterReadingVoucher::whereDate('date', '>=', $startOfMonth)
            ->whereDate('date', '<=', $endOfMonth)
            ->get()
            ->keyBy(function ($v) {
                return $v->unit_id . '_' . $v->meter_ref_no;
            });

        // Fetch prior readings for previous reading calculation
        $prevVouchers = MeterReadingVoucher::whereDate('date', '<', $startOfMonth)
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy(function ($v) {
                return $v->unit_id . '_' . $v->meter_ref_no;
            })
            ->map(function ($group) {
                return $group->first();
            });

        $prevVouchersByUnit = MeterReadingVoucher::whereDate('date', '<', $startOfMonth)
            ->orderBy('date', 'desc')
            ->get()
            ->groupBy('unit_id')
            ->map(function ($group) {
                return $group->first();
            });

        $readings = [];
        $totalUnitsConsumed = 0;
        $totalBilled = 0;
        $totalPaid = 0;
        $totalUnpaid = 0;

        foreach ($allMeters as $meter) {
            $key = $meter->unit_id . '_' . $meter->meter_ref_no;
            $voucher     = $vouchers->get($key);
            $prevVoucher = $prevVouchers->get($key) ?? $prevVouchersByUnit->get($meter->unit_id);

            if (!$voucher) {
                $voucher = $vouchers->first(function ($v) use ($meter) {
                    return $v->unit_id == $meter->unit_id && $v->meter_ref_no == $meter->meter_ref_no;
                });
            }

            // Auto-fetch: Previous month's meter reading becomes this month's prev reading
            $prevReading = 0.00;
            if ($prevVoucher && $prevVoucher->current_reading !== null && (float) $prevVoucher->current_reading > 0) {
                $prevReading = (float) $prevVoucher->current_reading;
            } elseif ($voucher && $voucher->previous_reading !== null && (float) $voucher->previous_reading > 0) {
                $prevReading = (float) $voucher->previous_reading;
            }

            $currentReading = $voucher && $voucher->current_reading !== null ? (float) $voucher->current_reading : 0.00;
            $unitsConsumed  = ($currentReading > 0 && $currentReading >= $prevReading)
                ? round($currentReading - $prevReading, 2)
                : 0.00;

            $amount         = $voucher ? (float) $voucher->amount : 0;
            $status         = $voucher ? strtolower($voucher->status ?? 'unpaid') : 'unpaid';

            if ($selectedStatus && $status !== strtolower($selectedStatus)) {
                continue;
            }

            $totalUnitsConsumed += $unitsConsumed;
            $totalBilled += $amount;
            if ($status === 'paid') {
                $totalPaid += $amount;
            } else {
                $totalUnpaid += $amount;
            }

            $readings[] = [
                'meter_id'          => $meter->id,
                'unit_number'       => $meter->unit->unit_number ?? 'N/A',
                'floor'             => $meter->unit->floor->name ?? 'N/A',
                'block'             => $meter->unit->block->name ?? '',
                'breaker_status'    => strtoupper($meter->unit->breaker_status ?? 'OFF'),
                'meter_type'        => $meter->type,
                'meter_type_label'  => $meter->getTypeLabelAttribute(),
                'meter_ref_no'      => $meter->meter_ref_no ?? 'N/A',
                'meter_consumer_id' => $meter->meter_consumer_id ?? 'N/A',
                'previous_reading'  => $prevReading,
                'current_reading'   => $currentReading,
                'units_consumed'    => $unitsConsumed,
                'available'         => $voucher->available ?? '',
                'amount'            => $amount,
                'status'            => $status,
                'is_active'         => (bool) $meter->is_active,
            ];
        }

        $activeMeters   = collect($readings)->where('is_active', true)->count();
        $inactiveMeters = collect($readings)->where('is_active', false)->count();
        $breakerOn      = collect($readings)->filter(fn($r) => strtoupper($r['breaker_status'] ?? 'OFF') === 'ON')->count();
        $breakerOff     = collect($readings)->filter(fn($r) => strtoupper($r['breaker_status'] ?? 'OFF') === 'OFF')->count();
        $paidCount      = collect($readings)->where('status', 'paid')->count();
        $unpaidCount    = collect($readings)->where('status', 'unpaid')->count();
        $pendingCount   = collect($readings)->where('status', 'pending')->count();

        return view('utility_readings.print', [
            'readings'           => $readings,
            'selectedMonth'      => $selectedMonth,
            'selectedMonthName'  => $monthCarbon->format('F Y'),
            'totalUnitsConsumed' => $totalUnitsConsumed,
            'totalBilled'        => $totalBilled,
            'totalPaid'          => $totalPaid,
            'totalUnpaid'        => $totalUnpaid,
            'activeMeters'       => $activeMeters,
            'inactiveMeters'     => $inactiveMeters,
            'breakerOn'          => $breakerOn,
            'breakerOff'         => $breakerOff,
            'paidCount'          => $paidCount,
            'unpaidCount'        => $unpaidCount,
            'pendingCount'       => $pendingCount,
        ]);
    }

    /**
     * AJAX: Save/Update a single row's meter reading data.
     */
    public function updateRow(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() &&
            !$user->hasPermission('utility_readings.edit') &&
            !$user->hasPermission('utilities.record') &&
            !$user->hasPermission('utility_meters_management') &&
            !$user->hasPermission('meters.edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'meter_id'         => ['required', 'exists:meters,id'],
            'month'            => ['required', 'string'], // YYYY-MM
            'previous_reading' => ['nullable', 'numeric', 'min:0'],
            'current_reading'  => ['nullable', 'numeric', 'min:0'],
            'available'        => ['nullable', 'string', 'max:255'],
            'amount'           => ['nullable', 'numeric', 'min:0'],
            'status'           => ['required', 'in:paid,unpaid,pending'],
            'notes'            => ['nullable', 'string', 'max:500'],
        ]);

        $meter = Meter::with('unit')->findOrFail($validated['meter_id']);

        try {
            $monthCarbon = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
        } catch (\Exception $e) {
            $monthCarbon = now()->startOfMonth();
        }

        $readingDate = $monthCarbon->copy()->day(15)->format('Y-m-d');
        $dueDate     = $monthCarbon->copy()->endOfMonth()->format('Y-m-d');

        // Check if voucher exists for this unit + meter_ref_no in this month
        $startOfMonth = $monthCarbon->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth   = $monthCarbon->copy()->endOfMonth()->format('Y-m-d');

        $voucher = MeterReadingVoucher::where('unit_id', $meter->unit_id)
            ->where('meter_ref_no', $meter->meter_ref_no)
            ->whereDate('date', '>=', $startOfMonth)
            ->whereDate('date', '<=', $endOfMonth)
            ->first();

        // Lock enforcement: If existing voucher is Paid, only Super Admin can edit it
        if ($voucher && strtolower($voucher->status) === 'paid' && !$user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'This reading has been marked as Paid. Only Super Admin can modify or edit paid records.',
            ], 403);
        }

        if (!$voucher) {
            $voucher = new MeterReadingVoucher([
                'unit_id'      => $meter->unit_id,
                'meter_ref_no' => $meter->meter_ref_no,
                'date'         => $readingDate,
                'due_date'     => $dueDate,
                'user_id'      => $user->id,
            ]);
        }

        // Auto-fetch latest prior reading if previous_reading was not explicitly provided or was 0
        $prevVoucher = MeterReadingVoucher::where('unit_id', $meter->unit_id)
            ->whereDate('date', '<', $startOfMonth)
            ->orderBy('date', 'desc')
            ->first();

        $prevReading = (float) ($validated['previous_reading'] ?? 0);
        if ($prevReading <= 0 && $prevVoucher && $prevVoucher->current_reading !== null && (float) $prevVoucher->current_reading > 0) {
            $prevReading = (float) $prevVoucher->current_reading;
        }

        $currentReading = (float) ($validated['current_reading'] ?? 0);
        $unitsConsumed  = max(0.00, $currentReading - $prevReading);

        $voucher->previous_reading = $prevReading;
        $voucher->current_reading  = $currentReading;
        $voucher->units_consumed   = $unitsConsumed;
        $voucher->available        = $validated['available'] ?? null;
        $voucher->amount           = $validated['amount'] ?? 0;
        $voucher->status           = $validated['status'];
        $voucher->notes            = $validated['notes'] ?? null;
        $voucher->save();

        return response()->json([
            'success' => true,
            'message' => "Reading for Flat/Shop {$meter->unit->unit_number} ({$meter->getTypeLabelAttribute()}) saved successfully.",
            'data'    => [
                'voucher_id'       => $voucher->id,
                'previous_reading' => (float) $voucher->previous_reading,
                'current_reading'  => (float) $voucher->current_reading,
                'units_consumed'   => (float) $voucher->units_consumed,
                'available'        => $voucher->available ?? '',
                'amount'           => (float) $voucher->amount,
                'status'           => strtolower($voucher->status),
                'meter_image_url'  => $voucher->getMeterImageUrlAttribute() ?: ($meter->meter_image ? Storage::disk('public')->url($meter->meter_image) : null),
            ],
        ]);
    }

    /**
     * AJAX: Upload meter photo for a row.
     */
    public function uploadImage(Request $request): JsonResponse
    {
        $user = auth()->user();
        if (!$user->isSuperAdmin() &&
            !$user->hasPermission('utility_readings.edit') &&
            !$user->hasPermission('utilities.record') &&
            !$user->hasPermission('utility_meters_management') &&
            !$user->hasPermission('meters.edit')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
        }

        $validated = $request->validate([
            'meter_id'    => ['required', 'exists:meters,id'],
            'month'       => ['required', 'string'],
            'meter_image' => ['required', 'image', 'max:200'], // Max 200 KB
        ], [
            'meter_image.max' => 'Meter photo size must not exceed 200 KB.',
        ]);

        $meter = Meter::findOrFail($validated['meter_id']);
        $path  = $request->file('meter_image')->store('meter_readings', 'public');

        try {
            $monthCarbon = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth();
        } catch (\Exception $e) {
            $monthCarbon = now()->startOfMonth();
        }

        $startOfMonth = $monthCarbon->copy()->startOfMonth()->format('Y-m-d');
        $endOfMonth   = $monthCarbon->copy()->endOfMonth()->format('Y-m-d');

        $voucher = MeterReadingVoucher::where('unit_id', $meter->unit_id)
            ->where('meter_ref_no', $meter->meter_ref_no)
            ->whereDate('date', '>=', $startOfMonth)
            ->whereDate('date', '<=', $endOfMonth)
            ->first();

        if ($voucher) {
            if ($voucher->meter_image && Storage::disk('public')->exists($voucher->meter_image)) {
                Storage::disk('public')->delete($voucher->meter_image);
            }
            $voucher->update(['meter_image' => $path]);
        } else {
            $voucher = MeterReadingVoucher::create([
                'unit_id'         => $meter->unit_id,
                'meter_ref_no'    => $meter->meter_ref_no,
                'date'            => $monthCarbon->copy()->day(15)->format('Y-m-d'),
                'due_date'        => $monthCarbon->copy()->endOfMonth()->format('Y-m-d'),
                'current_reading' => 0,
                'amount'          => 0,
                'status'          => 'unpaid',
                'meter_image'     => $path,
                'user_id'         => $user->id,
            ]);
        }

        return response()->json([
            'success'   => true,
            'message'   => 'Meter photo uploaded successfully.',
            'image_url' => Storage::disk('public')->url($path),
        ]);
    }
}
