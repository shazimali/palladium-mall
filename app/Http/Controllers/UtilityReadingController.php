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

        // Format reading rows
        $readings = [];
        $totalUnitsConsumed = 0;
        $totalBilled = 0;
        $totalPaid = 0;
        $totalUnpaid = 0;

        foreach ($allMeters as $meter) {
            $key = $meter->unit_id . '_' . $meter->meter_ref_no;
            $voucher = $vouchers->get($key);

            // Fallback voucher matching by unit + type if ref no wasn't exact
            if (!$voucher) {
                $voucher = $vouchers->first(function ($v) use ($meter) {
                    return $v->unit_id == $meter->unit_id && $v->meter_ref_no == $meter->meter_ref_no;
                });
            }

            $currentReading = $voucher ? (float) $voucher->current_reading : 0;
            $amount         = $voucher ? (float) $voucher->amount : 0;
            $status         = $voucher ? strtolower($voucher->status ?? 'unpaid') : 'unpaid';
            $voucherId      = $voucher ? $voucher->id : null;
            $meterImage     = $voucher && $voucher->meter_image ? $voucher->getMeterImageUrlAttribute() : null;

            if ($selectedStatus && $status !== strtolower($selectedStatus)) {
                continue;
            }

            $totalUnitsConsumed += $currentReading;
            $totalBilled += $amount;
            if ($status === 'paid') {
                $totalPaid += $amount;
            } else {
                $totalUnpaid += $amount;
            }

            $readings[] = [
                'meter_id'          => $meter->id,
                'voucher_id'        => $voucherId,
                'unit_id'           => $meter->unit_id,
                'unit_number'       => $meter->unit->unit_number ?? 'N/A',
                'floor'             => $meter->unit->floor->name ?? 'N/A',
                'block'             => $meter->unit->block->name ?? '',
                'tenant_name'       => $meter->unit->tenant->name ?? ($meter->unit->otherTenant->name ?? 'N/A'),
                'meter_type'        => $meter->type,
                'meter_type_label'  => $meter->getTypeLabelAttribute(),
                'meter_ref_no'      => $meter->meter_ref_no ?? 'N/A',
                'meter_consumer_id' => $meter->meter_consumer_id ?? 'N/A',
                'current_reading'   => $currentReading,
                'amount'            => $amount,
                'status'            => $status,
                'meter_image_url'   => $meterImage,
                'notes'             => $voucher->notes ?? '',
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

        $readings = [];
        $totalUnitsConsumed = 0;
        $totalBilled = 0;
        $totalPaid = 0;
        $totalUnpaid = 0;

        foreach ($allMeters as $meter) {
            $key = $meter->unit_id . '_' . $meter->meter_ref_no;
            $voucher = $vouchers->get($key);

            if (!$voucher) {
                $voucher = $vouchers->first(function ($v) use ($meter) {
                    return $v->unit_id == $meter->unit_id && $v->meter_ref_no == $meter->meter_ref_no;
                });
            }

            $currentReading = $voucher ? (float) $voucher->current_reading : 0;
            $amount         = $voucher ? (float) $voucher->amount : 0;
            $status         = $voucher ? strtolower($voucher->status ?? 'unpaid') : 'unpaid';

            if ($selectedStatus && $status !== strtolower($selectedStatus)) {
                continue;
            }

            $totalUnitsConsumed += $currentReading;
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
                'meter_type'        => $meter->type,
                'meter_type_label'  => $meter->getTypeLabelAttribute(),
                'meter_ref_no'      => $meter->meter_ref_no ?? 'N/A',
                'meter_consumer_id' => $meter->meter_consumer_id ?? 'N/A',
                'current_reading'   => $currentReading,
                'amount'            => $amount,
                'status'            => $status,
            ];
        }

        return view('utility_readings.print', [
            'readings'           => $readings,
            'selectedMonth'      => $selectedMonth,
            'selectedMonthName'  => $monthCarbon->format('F Y'),
            'totalUnitsConsumed' => $totalUnitsConsumed,
            'totalBilled'        => $totalBilled,
            'totalPaid'          => $totalPaid,
            'totalUnpaid'        => $totalUnpaid,
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
            'meter_id'        => ['required', 'exists:meters,id'],
            'month'           => ['required', 'string'], // YYYY-MM
            'current_reading' => ['nullable', 'numeric', 'min:0'],
            'amount'          => ['nullable', 'numeric', 'min:0'],
            'status'          => ['required', 'in:paid,unpaid,pending'],
            'notes'           => ['nullable', 'string', 'max:500'],
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

        if (!$voucher) {
            $voucher = new MeterReadingVoucher([
                'unit_id'      => $meter->unit_id,
                'meter_ref_no' => $meter->meter_ref_no,
                'date'         => $readingDate,
                'due_date'     => $dueDate,
                'user_id'      => $user->id,
            ]);
        }

        $voucher->current_reading = $validated['current_reading'] ?? 0;
        $voucher->amount          = $validated['amount'] ?? 0;
        $voucher->status          = $validated['status'];
        $voucher->notes           = $validated['notes'] ?? null;
        $voucher->save();

        return response()->json([
            'success' => true,
            'message' => "Reading for Flat/Shop {$meter->unit->unit_number} ({$meter->getTypeLabelAttribute()}) saved successfully.",
            'data'    => [
                'voucher_id'      => $voucher->id,
                'current_reading' => (float) $voucher->current_reading,
                'amount'          => (float) $voucher->amount,
                'status'          => strtolower($voucher->status),
                'meter_image_url' => $voucher->getMeterImageUrlAttribute() ?: ($meter->meter_image ? Storage::disk('public')->url($meter->meter_image) : null),
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
