<?php

namespace App\Http\Controllers;

use App\Models\MeterReadingVoucher;
use App\Models\Unit;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class MeterReadingVoucherController extends Controller
{
    /**
     * Display a listing of Meter Reading Vouchers matching Receiving Vouchers strategy.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('meter_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = MeterReadingVoucher::query()
            ->with(['unit.floor', 'unit.block', 'unit.tenant', 'unit.otherTenant', 'user']);

        if ($request->filled('search')) {
            $term = trim($request->search);
            $query->where(function ($q) use ($term) {
                $q->where('voucher_no', 'like', "%{$term}%")
                    ->orWhere('meter_ref_no', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%")
                    ->orWhereHas('unit', function ($u) use ($term) {
                        $u->where('unit_number', 'like', "%{$term}%")
                            ->orWhereHas('tenant', function ($t) use ($term) {
                                $t->where('name', 'like', "%{$term}%");
                            })
                            ->orWhereHas('otherTenant', function ($ot) use ($term) {
                                $ot->where('name', 'like', "%{$term}%");
                            });
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $dateFrom = $request->date_from ?? $request->start_date;
        if (!empty($dateFrom)) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        $dateTo = $request->date_to ?? $request->end_date;
        if (!empty($dateTo)) {
            $query->whereDate('date', '<=', $dateTo);
        }

        // Base query for summary cards matching current filters
        $baseFilteredQuery = MeterReadingVoucher::query();
        if ($request->filled('search')) {
            $term = trim($request->search);
            $baseFilteredQuery->where(function ($q) use ($term) {
                $q->where('voucher_no', 'like', "%{$term}%")
                    ->orWhere('meter_ref_no', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%")
                    ->orWhereHas('unit', function ($u) use ($term) {
                        $u->where('unit_number', 'like', "%{$term}%")
                            ->orWhereHas('tenant', function ($t) use ($term) {
                                $t->where('name', 'like', "%{$term}%");
                            })
                            ->orWhereHas('otherTenant', function ($ot) use ($term) {
                                $ot->where('name', 'like', "%{$term}%");
                            });
                    });
            });
        }
        if ($request->filled('unit_id')) {
            $baseFilteredQuery->where('unit_id', $request->unit_id);
        }
        if (!empty($dateFrom)) {
            $baseFilteredQuery->whereDate('date', '>=', $dateFrom);
        }
        if (!empty($dateTo)) {
            $baseFilteredQuery->whereDate('date', '<=', $dateTo);
        }

        $totalBilledAmount = (float) (clone $baseFilteredQuery)->sum('amount');
        $totalPaidAmount   = (float) (clone $baseFilteredQuery)->where('status', 'paid')->sum('amount');
        $totalUnpaidAmount = (float) (clone $baseFilteredQuery)->where('status', 'unpaid')->sum('amount');

        $vouchers = $query->latest('date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $units = Unit::orderBy('unit_number')->get();

        return view('meter_reading_vouchers.index', [
            'title'             => 'Meter Reading Vouchers',
            'vouchers'          => $vouchers,
            'units'             => $units,
            'totalBilledAmount' => $totalBilledAmount,
            'totalPaidAmount'   => $totalPaidAmount,
            'totalUnpaidAmount' => $totalUnpaidAmount,
        ]);
    }

    /**
     * Show the form for creating a new Meter Reading Voucher.
     */
    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('meter_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $units = Unit::with(['floor', 'block', 'tenant', 'otherTenant', 'meters'])->orderBy('unit_number')->get();
        $nextVoucherNo = MeterReadingVoucher::getNextVoucherNo();

        $unitMeterMap = [];
        foreach ($units as $u) {
            $unitMeterMap[$u->id] = $u->meters->where('type', 'electricity')->first()?->meter_ref_no
                ?? $u->meters->first()?->meter_ref_no
                ?? MeterReadingVoucher::where('unit_id', $u->id)->whereNotNull('meter_ref_no')->latest('date')->value('meter_ref_no')
                ?? '';
        }

        return view('meter_reading_vouchers.create', [
            'title'         => 'New Meter Reading Voucher',
            'units'         => $units,
            'unitMeterMap'  => $unitMeterMap,
            'nextVoucherNo' => $nextVoucherNo,
        ]);
    }

    /**
     * Store a newly created Meter Reading Voucher in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('meter_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'unit_id'         => 'required|exists:units,id',
            'date'            => 'required|date',
            'due_date'        => 'nullable|date',
            'meter_ref_no'    => 'required|string|max:100',
            'current_reading' => 'nullable|numeric|min:0',
            'amount'          => 'required|numeric|min:0.01',
            'status'          => 'required|in:paid,unpaid',
            'meter_image'     => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'notes'           => 'nullable|string',
        ]);

        $dateObj = \Carbon\Carbon::parse($validated['date']);
        $existing = MeterReadingVoucher::where('unit_id', $validated['unit_id'])
            ->whereYear('date', $dateObj->year)
            ->whereMonth('date', $dateObj->month)
            ->first();

        if ($existing) {
            $unitNumber = Unit::find($validated['unit_id'])?->unit_number ?? 'this Flat/Shop';
            return back()->withInput()->withErrors([
                'unit_id' => "A Meter Reading Voucher (#{$existing->voucher_no}) for Flat/Shop {$unitNumber} already exists for " . $dateObj->format('F Y') . "."
            ]);
        }

        $imagePath = null;
        if ($request->hasFile('meter_image')) {
            $imagePath = $request->file('meter_image')->store('meter_vouchers', 'public');
        }

        $voucher = MeterReadingVoucher::create([
            'unit_id'         => $validated['unit_id'],
            'date'            => $validated['date'],
            'due_date'        => $validated['due_date'] ?? null,
            'meter_ref_no'    => $validated['meter_ref_no'],
            'current_reading' => $validated['current_reading'] ?? null,
            'amount'          => $validated['amount'],
            'status'          => $validated['status'],
            'meter_image'     => $imagePath,
            'notes'           => $validated['notes'] ?? null,
            'user_id'         => auth()->id(),
        ]);

        ActivityLog::log(
            'create_meter_voucher',
            "Created Meter Reading Voucher #{$voucher->voucher_no} of Rs. " . number_format($voucher->amount) . " for Unit " . ($voucher->unit?->unit_number ?? 'N/A'),
            $voucher
        );

        return redirect()->route('meter-reading-vouchers.index')
            ->with('success', "Meter Reading Voucher #{$voucher->voucher_no} created successfully.");
    }

    /**
     * Display the specified Meter Reading Voucher.
     */
    public function show(MeterReadingVoucher $meterReadingVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('meter_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $meterReadingVoucher->load(['unit.floor', 'unit.block', 'unit.tenant', 'unit.otherTenant', 'user']);

        return view('meter_reading_vouchers.show', [
            'title'   => 'Meter Reading Voucher ' . $meterReadingVoucher->voucher_no,
            'voucher' => $meterReadingVoucher,
        ]);
    }

    /**
     * Show the form for editing the specified Meter Reading Voucher.
     */
    public function edit(MeterReadingVoucher $meterReadingVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('meter_vouchers.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $units = Unit::with(['floor', 'block', 'tenant', 'otherTenant', 'meters'])->orderBy('unit_number')->get();

        $unitMeterMap = [];
        foreach ($units as $u) {
            $unitMeterMap[$u->id] = $u->meters->where('type', 'electricity')->first()?->meter_ref_no
                ?? $u->meters->first()?->meter_ref_no
                ?? MeterReadingVoucher::where('unit_id', $u->id)->whereNotNull('meter_ref_no')->latest('date')->value('meter_ref_no')
                ?? '';
        }

        return view('meter_reading_vouchers.edit', [
            'title'        => 'Edit Meter Reading Voucher ' . $meterReadingVoucher->voucher_no,
            'voucher'      => $meterReadingVoucher,
            'units'        => $units,
            'unitMeterMap' => $unitMeterMap,
        ]);
    }

    /**
     * Update the specified Meter Reading Voucher in storage.
     */
    public function update(Request $request, MeterReadingVoucher $meterReadingVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('meter_vouchers.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'unit_id'         => 'required|exists:units,id',
            'date'            => 'required|date',
            'due_date'        => 'nullable|date',
            'meter_ref_no'    => 'required|string|max:100',
            'current_reading' => 'nullable|numeric|min:0',
            'amount'          => 'required|numeric|min:0.01',
            'status'          => 'required|in:paid,unpaid',
            'meter_image'     => 'nullable|image|mimes:jpeg,jpg,png,webp|max:5120',
            'notes'           => 'nullable|string',
        ]);

        $dateObj = \Carbon\Carbon::parse($validated['date']);
        $existing = MeterReadingVoucher::where('unit_id', $validated['unit_id'])
            ->whereYear('date', $dateObj->year)
            ->whereMonth('date', $dateObj->month)
            ->where('id', '!=', $meterReadingVoucher->id)
            ->first();

        if ($existing) {
            $unitNumber = Unit::find($validated['unit_id'])?->unit_number ?? 'this Flat/Shop';
            return back()->withInput()->withErrors([
                'unit_id' => "Another Meter Reading Voucher (#{$existing->voucher_no}) for Flat/Shop {$unitNumber} already exists for " . $dateObj->format('F Y') . "."
            ]);
        }

        if ($request->hasFile('meter_image')) {
            if ($meterReadingVoucher->meter_image && Storage::disk('public')->exists($meterReadingVoucher->meter_image)) {
                Storage::disk('public')->delete($meterReadingVoucher->meter_image);
            }
            $validated['meter_image'] = $request->file('meter_image')->store('meter_vouchers', 'public');
        }

        $meterReadingVoucher->update($validated);

        ActivityLog::log(
            'update_meter_voucher',
            "Updated Meter Reading Voucher #{$meterReadingVoucher->voucher_no}",
            $meterReadingVoucher
        );

        return redirect()->route('meter-reading-vouchers.index')
            ->with('success', "Meter Reading Voucher #{$meterReadingVoucher->voucher_no} updated successfully.");
    }

    /**
     * Remove the specified Meter Reading Voucher from storage.
     */
    public function destroy(MeterReadingVoucher $meterReadingVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('meter_vouchers.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $voucherNo = $meterReadingVoucher->voucher_no;
        $meterReadingVoucher->delete();

        ActivityLog::log(
            'delete_meter_voucher',
            "Deleted Meter Reading Voucher #{$voucherNo}",
            $meterReadingVoucher
        );

        return redirect()->route('meter-reading-vouchers.index')
            ->with('success', "Meter Reading Voucher #{$voucherNo} deleted successfully.");
    }

    /**
     * Printable view of the Meter Reading Voucher.
     */
    public function print(MeterReadingVoucher $meterReadingVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('meter_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $meterReadingVoucher->load(['unit.floor', 'unit.block', 'unit.tenant', 'unit.otherTenant', 'user']);

        return view('meter_reading_vouchers.print', [
            'voucher' => $meterReadingVoucher,
        ]);
    }

    /**
     * Print filtered list of Meter Reading Vouchers matching Receiving Vouchers print-list strategy.
     */
    public function printList(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('meter_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = MeterReadingVoucher::query()
            ->with(['unit.floor', 'unit.block', 'unit.tenant', 'unit.otherTenant', 'user']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('voucher_no', 'like', "%{$term}%")
                    ->orWhere('meter_ref_no', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%")
                    ->orWhereHas('unit', function ($u) use ($term) {
                        $u->where('unit_number', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }

        $dateFrom = $request->date_from ?? $request->start_date;
        if (!empty($dateFrom)) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        $dateTo = $request->date_to ?? $request->end_date;
        if (!empty($dateTo)) {
            $query->whereDate('date', '<=', $dateTo);
        }

        $totalAmount       = (float) (clone $query)->sum('amount');
        $totalPaidAmount   = (float) (clone $query)->where('status', 'paid')->sum('amount');
        $totalUnpaidAmount = (float) (clone $query)->where('status', 'unpaid')->sum('amount');

        $vouchers = $query->latest('date')->latest('id')->get();
        $selectedUnit = $request->unit_id ? Unit::find($request->unit_id)?->unit_number : null;

        return view('meter_reading_vouchers.print_list', [
            'title'             => 'Meter Reading Vouchers Report',
            'vouchers'          => $vouchers,
            'totalAmount'       => $totalAmount,
            'totalPaidAmount'   => $totalPaidAmount,
            'totalUnpaidAmount' => $totalUnpaidAmount,
            'selectedUnit'      => $selectedUnit,
            'filters'           => $request->all(),
        ]);
    }
}
