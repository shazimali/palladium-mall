<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\ReceivingVoucher;
use App\Models\GeneralReceivingVoucher;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerDuesController extends Controller
{
    /**
     * Display a listing of managing owner dues.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $dateFrom = $request->query('date_from', now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', now()->endOfMonth()->toDateString());

        $otherTenantUnitIds = \DB::table('other_tenants')->pluck('unit_id')->toArray();

        // 1. Calculate mall financials using exact Profit & Loss cash-flow logic
        $allocations = \DB::table('receiving_voucher_payments')
            ->join('payments', 'receiving_voucher_payments.payment_id', '=', 'payments.id')
            ->join('units', 'payments.unit_id', '=', 'units.id')
            ->join('receiving_vouchers', 'receiving_voucher_payments.receiving_voucher_id', '=', 'receiving_vouchers.id')
            ->whereNull('receiving_vouchers.deleted_at')
            ->whereNull('payments.deleted_at')
            ->whereBetween('payments.month', [$dateFrom, $dateTo])
            ->where('payments.type', '!=', 'security_deposit')
            ->select('payments.unit_id', 'units.is_self', 'payments.type', \DB::raw('SUM(receiving_voucher_payments.amount_allocated) as total'))
            ->groupBy('payments.unit_id', 'units.is_self', 'payments.type')
            ->get();

        $allocRentPmMall      = (float) $allocations->where('is_self', false)->where('type', 'rent')->sum('total');
        $allocMaintPmMall     = (float) $allocations->filter(function ($row) use ($otherTenantUnitIds) {
            return $row->type === 'maintenance' && (!$row->is_self || in_array($row->unit_id, $otherTenantUnitIds));
        })->sum('total');
        $allocMaintOtherOwned = (float) $allocations->filter(function ($row) use ($otherTenantUnitIds) {
            return $row->type === 'maintenance' && $row->is_self && !in_array($row->unit_id, $otherTenantUnitIds);
        })->sum('total');
        $allocExtraPmMall     = (float) $allocations->where('is_self', false)->whereNotIn('type', ['rent', 'maintenance', 'security_deposit'])->sum('total');

        $monthPayments = \DB::table('payments')
            ->join('units', 'payments.unit_id', '=', 'units.id')
            ->whereNull('payments.deleted_at')
            ->whereBetween('payments.month', [$dateFrom, $dateTo])
            ->where('payments.type', '!=', 'security_deposit')
            ->select('payments.unit_id', 'units.is_self', 'payments.type', \DB::raw('SUM(payments.amount_paid) as total_paid'))
            ->groupBy('payments.unit_id', 'units.is_self', 'payments.type')
            ->get();

        $payRentPmMall      = (float) $monthPayments->where('is_self', false)->where('type', 'rent')->sum('total_paid');
        $payMaintPmMall     = (float) $monthPayments->filter(function ($row) use ($otherTenantUnitIds) {
            return $row->type === 'maintenance' && (!$row->is_self || in_array($row->unit_id, $otherTenantUnitIds));
        })->sum('total_paid');
        $payMaintOtherOwned = (float) $monthPayments->filter(function ($row) use ($otherTenantUnitIds) {
            return $row->type === 'maintenance' && $row->is_self && !in_array($row->unit_id, $otherTenantUnitIds);
        })->sum('total_paid');
        $payExtraPmMall     = (float) $monthPayments->where('is_self', false)->whereNotIn('type', ['rent', 'maintenance', 'security_deposit'])->sum('total_paid');


        $rentPmMall      = max($allocRentPmMall, $payRentPmMall);
        $maintPmMall     = max($allocMaintPmMall, $payMaintPmMall);
        $maintOtherOwned = max($allocMaintOtherOwned, $payMaintOtherOwned);
        $extraPmMall     = max($allocExtraPmMall, $payExtraPmMall);

        $tenantIncomeAll = (float) ReceivingVoucher::where('received_from_type', 'tenant')
            ->whereBetween('date', [$dateFrom, $dateTo])
            ->sum('amount');

        $totalAllocatedTenantVouchers = (float) \DB::table('receiving_voucher_payments')
            ->join('receiving_vouchers', 'receiving_voucher_payments.receiving_voucher_id', '=', 'receiving_vouchers.id')
            ->whereNull('receiving_vouchers.deleted_at')
            ->where('receiving_vouchers.received_from_type', 'tenant')
            ->whereBetween('receiving_vouchers.date', [$dateFrom, $dateTo])
            ->sum('receiving_voucher_payments.amount_allocated');

        $unallocatedTenantIncome = max(0.00, $tenantIncomeAll - $totalAllocatedTenantVouchers);

        $totalIncome = $rentPmMall + $maintPmMall + $maintOtherOwned + $extraPmMall + $unallocatedTenantIncome;

        $totalExpenses = (float) Expense::whereBetween('date', [$dateFrom, $dateTo])->sum('amount');

        $netProfit = $totalIncome - $totalExpenses;

        // 2. Fetch all owners with calculated shares for the selected date range
        $owners = Owner::orderBy('name')->get();

        $ownersData = $owners->map(function ($owner) use ($netProfit, $dateFrom, $dateTo) {
            $profitShare = round($netProfit * ((float) $owner->partnership_percentage / 100), 2);

            $totalPaid = (float) $owner->withdrawals()
                ->when($dateFrom, fn($q) => $q->whereDate('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->whereDate('date', '<=', $dateTo))
                ->sum('amount');

            $dueAmount = max(0.00, round($profitShare - $totalPaid, 2));

            return [
                'id' => $owner->id,
                'name' => $owner->name,
                'partnership_percentage' => $owner->partnership_percentage,
                'profit_share' => $profitShare,
                'total_paid' => $totalPaid,
                'due_amount' => $dueAmount,
            ];
        });

        return view('reports.owner_dues', [
            'title' => 'Managing Owner Dues Statement',
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'totalIncome' => $totalIncome,
            'totalExpenses' => $totalExpenses,
            'netProfit' => $netProfit,
            'ownersData' => $ownersData,
        ]);
    }
}
