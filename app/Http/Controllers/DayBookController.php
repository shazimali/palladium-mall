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

        // Fetch Inflows (Tenant Payments)
        // Only payments that have been paid/partially paid with amount_paid > 0
        $inflows = Payment::with(['tenant', 'unit', 'paymentAccount'])
            ->whereBetween('paid_at', [$startDate, $endDate])
            ->where('amount_paid', '>', 0)
            ->orderBy('paid_at', 'asc')
            ->get();

        // Fetch Outflows (Expenses)
        $outflows = Expense::with(['expenseHead', 'paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Calculations
        $totalInflows = $inflows->sum('amount_paid');
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
