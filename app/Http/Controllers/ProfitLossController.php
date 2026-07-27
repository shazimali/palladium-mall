<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\ReceivingVoucher;
use App\Models\Expense;
use App\Models\Owner;
use App\Exports\ProfitLossExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ProfitLossController extends Controller
{
    /**
     * Display the Profit & Loss report.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $filters = $this->getFilters($request);
        $data = $this->calculateProfitLossData($filters['date_from'], $filters['date_to']);

        return view('reports.profit_loss', array_merge($data, [
            'title' => 'Profit & Loss Statement',
            'filters' => $filters,
        ]));
    }

    /**
     * Export the Profit & Loss report to PDF.
     */
    public function exportPdf(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $filters = $this->getFilters($request);
        $data = $this->calculateProfitLossData($filters['date_from'], $filters['date_to']);

        $pdf = Pdf::loadView('reports.profit_loss_pdf', array_merge($data, [
            'filters' => $filters,
        ]))->setPaper('a4', 'portrait');

        $filename = 'profit-loss-' . $filters['date_from'] . '-to-' . $filters['date_to'] . '.pdf';

        \App\Models\ActivityLog::log('export_pdf', "Exported Profit & Loss statement to PDF: {$filename}", null, [
            'filters' => $filters,
        ]);

        return $pdf->download($filename);
    }

    /**
     * Export the Profit & Loss report to Excel.
     */
    public function exportExcel(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $filters = $this->getFilters($request);
        $data = $this->calculateProfitLossData($filters['date_from'], $filters['date_to']);

        $filename = 'profit-loss-' . $filters['date_from'] . '-to-' . $filters['date_to'] . '.xlsx';

        \App\Models\ActivityLog::log('export_excel', "Exported Profit & Loss statement to Excel: {$filename}", null, [
            'filters' => $filters,
        ]);

        return Excel::download(
            new ProfitLossExport(array_merge($data, ['filters' => $filters]), 'Profit & Loss Statement'),
            $filename
        );
    }

    /**
     * Helper to resolve filters.
     */
    private function getFilters(Request $request): array
    {
        $dateFrom = $request->filled('date_from')
            ? Carbon::parse($request->date_from)->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();

        $dateTo = $request->filled('date_to')
            ? Carbon::parse($request->date_to)->toDateString()
            : Carbon::now()->endOfMonth()->toDateString();

        return [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];
    }

    /**
     * Core P&L Calculation logic (Aligned with Cash Receiving Vouchers method).
     */
    private function calculateProfitLossData(string $from, string $to): array
    {
        // 1. Revenue / Income
        // A. Allocations from receiving vouchers dated in the range
        $allocations = DB::table('receiving_voucher_payments')
            ->join('payments', 'receiving_voucher_payments.payment_id', '=', 'payments.id')
            ->join('units', 'payments.unit_id', '=', 'units.id')
            ->join('receiving_vouchers', 'receiving_voucher_payments.receiving_voucher_id', '=', 'receiving_vouchers.id')
            ->whereNull('receiving_vouchers.deleted_at')
            ->whereNull('payments.deleted_at')
            ->whereBetween('receiving_vouchers.date', [$from, $to])
            ->where('payments.type', '!=', 'security_deposit')
            ->select('units.is_self', 'payments.type', DB::raw('SUM(receiving_voucher_payments.amount_allocated) as total'))
            ->groupBy('units.is_self', 'payments.type')
            ->get();

        $allocRentPmMall      = (float) $allocations->where('is_self', false)->where('type', 'rent')->sum('total');
        $allocMaintPmMall     = (float) $allocations->where('is_self', false)->where('type', 'maintenance')->sum('total');
        $allocMaintOtherOwned = (float) $allocations->where('is_self', true)->where('type', 'maintenance')->sum('total');
        $allocExtraPmMall     = (float) $allocations->where('is_self', false)->whereNotIn('type', ['rent', 'maintenance', 'security_deposit'])->sum('total');

        // B. Payments collected & billed for months in the date range
        $monthPayments = DB::table('payments')
            ->join('units', 'payments.unit_id', '=', 'units.id')
            ->whereNull('payments.deleted_at')
            ->whereBetween('payments.month', [$from, $to])
            ->where('payments.type', '!=', 'security_deposit')
            ->select(
                'units.is_self', 
                'payments.type', 
                DB::raw('SUM(payments.amount) as total_due'),
                DB::raw('SUM(payments.amount_paid) as total_paid')
            )
            ->groupBy('units.is_self', 'payments.type')
            ->get();

        $billedRentPmMall      = (float) $monthPayments->where('is_self', false)->where('type', 'rent')->sum('total_due');
        $billedMaintPmMall     = (float) $monthPayments->where('is_self', false)->where('type', 'maintenance')->sum('total_due');
        $billedMaintOtherOwned = (float) $monthPayments->where('is_self', true)->where('type', 'maintenance')->sum('total_due');
        $billedExtraPmMall     = (float) $monthPayments->where('is_self', false)->whereNotIn('type', ['rent', 'maintenance', 'security_deposit'])->sum('total_due');

        $payRentPmMall      = (float) $monthPayments->where('is_self', false)->where('type', 'rent')->sum('total_paid');
        $payMaintPmMall     = (float) $monthPayments->where('is_self', false)->where('type', 'maintenance')->sum('total_paid');
        $payMaintOtherOwned = (float) $monthPayments->where('is_self', true)->where('type', 'maintenance')->sum('total_paid');
        $payExtraPmMall     = (float) $monthPayments->where('is_self', false)->whereNotIn('type', ['rent', 'maintenance', 'security_deposit'])->sum('total_paid');

        // Max of voucher allocations or payments collected for period
        $rentPmMall      = max($allocRentPmMall, $payRentPmMall);
        $maintPmMall     = max($allocMaintPmMall, $payMaintPmMall);
        $maintOtherOwned = max($allocMaintOtherOwned, $payMaintOtherOwned);
        $extraPmMall     = max($allocExtraPmMall, $payExtraPmMall);

        // Check for any unallocated tenant vouchers in this date range
        $tenantIncomeAll = (float) ReceivingVoucher::where('received_from_type', 'tenant')
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        $totalAllocatedTenantVouchers = (float) DB::table('receiving_voucher_payments')
            ->join('receiving_vouchers', 'receiving_voucher_payments.receiving_voucher_id', '=', 'receiving_vouchers.id')
            ->whereNull('receiving_vouchers.deleted_at')
            ->where('receiving_vouchers.received_from_type', 'tenant')
            ->whereBetween('receiving_vouchers.date', [$from, $to])
            ->sum('receiving_voucher_payments.amount_allocated');

        $unallocatedTenantIncome = max(0.00, $tenantIncomeAll - $totalAllocatedTenantVouchers);

        $miscIncome  = 0.00;
        $totalIncome = $rentPmMall + $maintPmMall + $maintOtherOwned + $extraPmMall + $unallocatedTenantIncome;

        $incomeBreakdown = [
            'rent_pm_mall'      => $rentPmMall,
            'maint_pm_mall'     => $maintPmMall,
            'maint_other_owned' => $maintOtherOwned,
            'extra_pm_mall'     => $extraPmMall,
        ];

        if ($unallocatedTenantIncome > 0) {
            $incomeBreakdown['other'] = $unallocatedTenantIncome;
        }

        $incomeDetailed = [
            'rent_pm_mall' => [
                'label' => '🏠 Rent (PM Mall Units)',
                'billed' => $billedRentPmMall,
                'collected' => $rentPmMall,
                'unpaid' => max(0.0, $billedRentPmMall - $rentPmMall),
            ],
            'maint_pm_mall' => [
                'label' => '🛠️ Maintenance Charges (PM Mall Units)',
                'billed' => $billedMaintPmMall,
                'collected' => $maintPmMall,
                'unpaid' => max(0.0, $billedMaintPmMall - $maintPmMall),
            ],
            'maint_other_owned' => [
                'label' => '🛠️ Maintenance Charges (Landlord / Other-Owned Units)',
                'billed' => $billedMaintOtherOwned,
                'collected' => $maintOtherOwned,
                'unpaid' => max(0.0, $billedMaintOtherOwned - $maintOtherOwned),
            ],
            'extra_pm_mall' => [
                'label' => '💵 Extra Payments (PM Mall Units)',
                'billed' => $billedExtraPmMall,
                'collected' => $extraPmMall,
                'unpaid' => max(0.0, $billedExtraPmMall - $extraPmMall),
            ],
        ];

        if ($unallocatedTenantIncome > 0) {
            $incomeDetailed['other'] = [
                'label' => '📑 Other Tenant Receipts (Unallocated Vouchers)',
                'billed' => 0.0,
                'collected' => $unallocatedTenantIncome,
                'unpaid' => 0.0,
            ];
        }

        $totalBilledIncome  = array_sum(array_column($incomeDetailed, 'billed'));
        $totalUnpaidIncome  = array_sum(array_column($incomeDetailed, 'unpaid'));

        // 2. Expenses
        $expensesByHead = Expense::with('expenseHead')
            ->whereBetween('date', [$from, $to])
            ->select('expense_head_id', DB::raw('SUM(amount) as total_spent'))
            ->groupBy('expense_head_id')
            ->get()
            ->map(fn($e) => [
                'name' => $e->expenseHead?->name ?? 'Uncategorized',
                'amount' => (float) $e->total_spent,
            ])
            ->toArray();

        $totalExpenses = array_sum(array_column($expensesByHead, 'amount'));

        // 3. Net Profit / Loss
        $netProfitLoss = $totalIncome - $totalExpenses;

        // 4. Partner Distribution
        $owners = Owner::orderBy('name')->get();
        $totalOwnerPercentage = $owners->sum('partnership_percentage');

        $distribution = $owners->map(fn($o) => [
            'name' => $o->name,
            'percentage' => (float) $o->partnership_percentage,
            'share' => (float) ($netProfitLoss * ($o->partnership_percentage / 100)),
        ])->toArray();

        return [
            'date_from' => $from,
            'date_to' => $to,
            'incomeBreakdown' => $incomeBreakdown,
            'incomeDetailed' => $incomeDetailed,
            'totalBilledIncome' => $totalBilledIncome,
            'totalUnpaidIncome' => $totalUnpaidIncome,
            'miscIncome' => $miscIncome,
            'totalIncome' => $totalIncome,
            'expensesByHead' => $expensesByHead,
            'totalExpenses' => $totalExpenses,
            'netProfitLoss' => $netProfitLoss,
            'netProfit' => $netProfitLoss,
            'distribution' => $distribution,
            'totalOwnerSharePct' => (float) $totalOwnerPercentage,
        ];
    }
}
