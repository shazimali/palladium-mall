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
            'title'   => 'Profit & Loss Statement',
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
            : Carbon::now()->toDateString();

        return [
            'date_from' => $dateFrom,
            'date_to'   => $dateTo,
        ];
    }

    /**
     * Core P&L Calculation logic.
     */
    private function calculateProfitLossData(string $from, string $to): array
    {
        $start = Carbon::parse($from)->startOfDay();
        $end = Carbon::parse($to)->endOfDay();

        // 1. Revenue / Income
        // A. Standard tenant payments collected for Mall-Owned units (is_self = false)
        $billingCollections = Payment::whereHas('unit', function ($qu) {
                $qu->where('is_self', false);
            })
            ->where('amount_paid', '>', 0)
            ->whereBetween('paid_at', [$start, $end])
            ->select('type', DB::raw('SUM(amount_paid) as total_paid'))
            ->groupBy('type')
            ->get()
            ->pluck('total_paid', 'type')
            ->toArray();

        // Ensure all types are represented in array
        $types = ['rent', 'maintenance', 'electricity', 'water', 'gas', 'fine', 'other'];
        $incomeBreakdown = [];
        $totalBillingIncome = 0;
        foreach ($types as $type) {
            $val = (float) ($billingCollections[$type] ?? 0.0);
            $incomeBreakdown[$type] = $val;
            $totalBillingIncome += $val;
        }

        // B. Miscellaneous income from Receiving Vouchers of type 'other'
        $miscIncome = (float) ReceivingVoucher::where('received_from_type', 'other')
            ->whereBetween('date', [$from, $to])
            ->sum('amount');

        $totalIncome = $totalBillingIncome + $miscIncome;

        // 2. Expenses
        $expensesByHead = Expense::with('expenseHead')
            ->whereBetween('date', [$from, $to])
            ->select('expense_head_id', DB::raw('SUM(amount) as total_spent'))
            ->groupBy('expense_head_id')
            ->get()
            ->map(fn($e) => [
                'name'   => $e->expenseHead?->name ?? 'Uncategorized',
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
            'name'       => $o->name,
            'percentage' => (float) $o->partnership_percentage,
            'share'      => (float) ($netProfitLoss * ($o->partnership_percentage / 100)),
        ])->toArray();

        return [
            'date_from'          => $from,
            'date_to'            => $to,
            'incomeBreakdown'    => $incomeBreakdown,
            'miscIncome'         => $miscIncome,
            'totalIncome'        => $totalIncome,
            'expensesByHead'     => $expensesByHead,
            'totalExpenses'      => $totalExpenses,
            'netProfitLoss'      => $netProfitLoss,
            'distribution'       => $distribution,
            'totalOwnerSharePct' => (float) $totalOwnerPercentage,
        ];
    }
}
