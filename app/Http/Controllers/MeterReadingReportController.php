<?php

namespace App\Http\Controllers;

use App\Models\MeterReadingVoucher;
use App\Models\Unit;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MeterReadingReportController extends Controller
{
    /**
     * Display the Meter Reading Report page.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $unitId   = $request->unit_id;
        $dateFrom = $request->date_from ?? $request->start_date;
        $dateTo   = $request->date_to ?? $request->end_date;
        $status   = $request->status;

        $units = Unit::with(['floor', 'block', 'tenant', 'otherTenant', 'meters'])
            ->orderBy('unit_number')
            ->get();

        $selectedUnit = $unitId ? Unit::with(['floor', 'block', 'tenant', 'otherTenant', 'meters'])->find($unitId) : null;

        $query = MeterReadingVoucher::with(['unit.floor', 'unit.block', 'unit.tenant', 'unit.otherTenant', 'user']);

        if (!empty($unitId)) {
            $query->where('unit_id', $unitId);
        }

        if (!empty($dateFrom)) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('date', '<=', $dateTo);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $vouchers = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        // Calculate consumption diffs per unit
        $voucherData = [];
        $unitPreviousReadings = [];

        foreach ($vouchers as $v) {
            $prevReading = $unitPreviousReadings[$v->unit_id] ?? null;
            $consumption = null;

            if ($v->current_reading !== null && $prevReading !== null && $v->current_reading >= $prevReading) {
                $consumption = $v->current_reading - $prevReading;
            }

            if ($v->current_reading !== null) {
                $unitPreviousReadings[$v->unit_id] = (float) $v->current_reading;
            }

            $voucherData[] = [
                'voucher'     => $v,
                'consumption' => $consumption,
                'prev_reading'=> $prevReading,
            ];
        }

        $totalBilledAmount = (float) $vouchers->sum('amount');
        $totalPaidAmount   = (float) $vouchers->where('status', 'paid')->sum('amount');
        $totalUnpaidAmount = (float) $vouchers->where('status', 'unpaid')->sum('amount');
        $totalConsumption  = array_reduce($voucherData, fn($carry, $item) => $carry + ($item['consumption'] ?? 0), 0.0);

        return view('reports.meter_readings', [
            'title'             => 'Meter Reading Report',
            'units'             => $units,
            'selectedUnit'      => $selectedUnit,
            'voucherData'       => $voucherData,
            'vouchers'          => $vouchers,
            'unitId'            => $unitId,
            'dateFrom'          => $dateFrom,
            'dateTo'            => $dateTo,
            'status'            => $status,
            'totalBilledAmount' => $totalBilledAmount,
            'totalPaidAmount'   => $totalPaidAmount,
            'totalUnpaidAmount' => $totalUnpaidAmount,
            'totalConsumption'  => $totalConsumption,
        ]);
    }

    /**
     * Print the Meter Reading Report.
     */
    public function print(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $unitId   = $request->unit_id;
        $dateFrom = $request->date_from ?? $request->start_date;
        $dateTo   = $request->date_to ?? $request->end_date;
        $status   = $request->status;

        $selectedUnit = $unitId ? Unit::with(['floor', 'block', 'tenant', 'otherTenant', 'meters'])->find($unitId) : null;

        $query = MeterReadingVoucher::with(['unit.floor', 'unit.block', 'unit.tenant', 'unit.otherTenant', 'user']);

        if (!empty($unitId)) {
            $query->where('unit_id', $unitId);
        }

        if (!empty($dateFrom)) {
            $query->whereDate('date', '>=', $dateFrom);
        }

        if (!empty($dateTo)) {
            $query->whereDate('date', '<=', $dateTo);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $vouchers = $query->orderBy('date', 'asc')->orderBy('id', 'asc')->get();

        $voucherData = [];
        $unitPreviousReadings = [];

        foreach ($vouchers as $v) {
            $prevReading = $unitPreviousReadings[$v->unit_id] ?? null;
            $consumption = null;

            if ($v->current_reading !== null && $prevReading !== null && $v->current_reading >= $prevReading) {
                $consumption = $v->current_reading - $prevReading;
            }

            if ($v->current_reading !== null) {
                $unitPreviousReadings[$v->unit_id] = (float) $v->current_reading;
            }

            $voucherData[] = [
                'voucher'     => $v,
                'consumption' => $consumption,
                'prev_reading'=> $prevReading,
            ];
        }

        $totalBilledAmount = (float) $vouchers->sum('amount');
        $totalPaidAmount   = (float) $vouchers->where('status', 'paid')->sum('amount');
        $totalUnpaidAmount = (float) $vouchers->where('status', 'unpaid')->sum('amount');
        $totalConsumption  = array_reduce($voucherData, fn($carry, $item) => $carry + ($item['consumption'] ?? 0), 0.0);

        ActivityLog::log(
            'export_print',
            "Printed Meter Reading Report for Unit " . ($selectedUnit ? $selectedUnit->unit_number : 'All Units'),
            null,
            ['unit_id' => $unitId, 'date_from' => $dateFrom, 'date_to' => $dateTo]
        );

        return view('reports.meter_readings_print', [
            'title'             => 'Meter Reading Report',
            'selectedUnit'      => $selectedUnit,
            'voucherData'       => $voucherData,
            'vouchers'          => $vouchers,
            'unitId'            => $unitId,
            'dateFrom'          => $dateFrom,
            'dateTo'            => $dateTo,
            'status'            => $status,
            'totalBilledAmount' => $totalBilledAmount,
            'totalPaidAmount'   => $totalPaidAmount,
            'totalUnpaidAmount' => $totalUnpaidAmount,
            'totalConsumption'  => $totalConsumption,
        ]);
    }
}
