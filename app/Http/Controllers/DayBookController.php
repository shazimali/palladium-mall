<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class DayBookController extends Controller
{
    /**
     * Show the Day Book report.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.daybook')) {
            abort(403, 'Unauthorized action.');
        }

        // Default to today
        $dateStr = $request->input('date', Carbon::today()->toDateString());
        $startDateStr = $request->input('start_date', $dateStr);
        $endDateStr = $request->input('end_date', $dateStr);

        try {
            $startDate = Carbon::parse($startDateStr)->startOfDay();
            $endDate = Carbon::parse($endDateStr)->endOfDay();
        } catch (\Exception $e) {
            $startDate = Carbon::today()->startOfDay();
            $endDate = Carbon::today()->endOfDay();
        }

        // Fetch Inflows (Receiving Vouchers)
        $inflows = \App\Models\ReceivingVoucher::with(['tenant', 'owner', 'paymentAccount', 'payments.unit'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Fetch Outflows (Expenses)
        $expenses = Expense::with(['expenseHead', 'paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        // Fetch Outflows (Payment Vouchers)
        $paymentVouchers = \App\Models\PaymentVoucher::with(['owner', 'paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get();

        // Combine and sort outflows
        $outflows = $expenses->concat($paymentVouchers)->sortBy(function ($item) {
            return $item->date->format('Y-m-d') . '_' . $item->created_at->format('Y-m-d H:i:s');
        })->values();

        // Calculations
        $totalInflows = $inflows->sum('amount');
        $totalOutflows = $outflows->sum('amount');
        $netFlow = $totalInflows - $totalOutflows;

        return view('reports.day_book', [
            'title'         => 'Day Book Report',
            'inflows'       => $inflows,
            'outflows'      => $outflows,
            'totalInflows'  => $totalInflows,
            'totalOutflows' => $totalOutflows,
            'netFlow'       => $netFlow,
            'startDate'     => $startDate->toDateString(),
            'endDate'       => $endDate->toDateString(),
            'isSingleDay'   => $startDate->isSameDay($endDate),
        ]);
    }
}
