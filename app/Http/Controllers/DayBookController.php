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

        // Combine outflows
        $outflows = $expenses->concat($paymentVouchers);

        // Combine into unified ledger entries
        $ledgerEntries = collect();

        foreach ($inflows as $inflow) {
            $ledgerEntries->push([
                'date' => $inflow->date ?? $inflow->created_at,
                'created_at' => $inflow->created_at,
                'voucher_no' => $inflow->voucher_no,
                'type' => 'Inflow',
                'details' => $inflow->received_from_type === 'tenant'
                    ? '👤 Tenant: ' . ($inflow->tenant ? $inflow->tenant->name : 'N/A') . ' (' . ($inflow->payments->map(fn($p) => $p->unit?->unit_number)->filter()->unique()->implode(', ') ?: 'N/A') . ')'
                    : ($inflow->received_from_type === 'owner'
                        ? '👤 Partner: ' . ($inflow->owner ? $inflow->owner->name : 'N/A')
                        : '👤 Misc: ' . ($inflow->other_name ?: 'N/A') . ($inflow->notes ? ' • ' . $inflow->notes : '')),
                'method' => $inflow->payment_method . ($inflow->paymentAccount ? ' (' . $inflow->paymentAccount->name . ')' : ''),
                'debit' => 0.0,
                'credit' => (float)$inflow->amount,
            ]);
        }

        foreach ($outflows as $outflow) {
            $isExpense = $outflow instanceof \App\Models\Expense;
            $details = $isExpense
                ? '💸 Expense: ' . ($outflow->expenseHead?->name ?? 'Expense')
                : ($outflow->is_advance
                    ? '⚠️ Advance Payout to: ' . ($outflow->paid_to_type === 'owner' ? ($outflow->owner?->name ?? 'Partner') : ($outflow->other_name ?? 'N/A'))
                    : '📤 Payout to: ' . ($outflow->paid_to_type === 'owner' ? ($outflow->owner?->name ?? 'Partner') : ($outflow->other_name ?? 'N/A')));

            if ($outflow->notes) {
                $details .= ' • ' . $outflow->notes;
            }

            $ledgerEntries->push([
                'date' => $outflow->date,
                'created_at' => $outflow->created_at,
                'voucher_no' => $outflow->voucher_no,
                'type' => 'Outflow',
                'details' => $details,
                'method' => $outflow->payment_method . ($outflow->paymentAccount ? ' (' . $outflow->paymentAccount->name . ')' : ''),
                'debit' => (float)$outflow->amount,
                'credit' => 0.0,
            ]);
        }

        // Sort chronologically
        $ledgerEntries = $ledgerEntries->sortBy(function ($item) {
            $date = $item['date'] instanceof Carbon ? $item['date'] : Carbon::parse($item['date']);
            $createdAt = $item['created_at'] instanceof Carbon ? $item['created_at'] : Carbon::parse($item['created_at']);
            return $date->format('Y-m-d') . '_' . $createdAt->format('Y-m-d H:i:s');
        })->values();

        // Calculate running balance
        $runningBalance = 0.0;
        $ledgerEntries = $ledgerEntries->map(function ($item) use (&$runningBalance) {
            $runningBalance += ($item['credit'] - $item['debit']);
            $item['running_balance'] = $runningBalance;
            return $item;
        });

        // Sums
        $totalInflows = $inflows->sum('amount');
        $totalOutflows = $outflows->sum('amount');
        $netFlow = $totalInflows - $totalOutflows;

        return view('reports.day_book', [
            'title'         => 'Day Book Report',
            'ledgerEntries' => $ledgerEntries,
            'totalInflows'  => $totalInflows,
            'totalOutflows' => $totalOutflows,
            'netFlow'       => $netFlow,
            'startDate'     => $startDate->toDateString(),
            'endDate'     => $endDate->toDateString(),
            'isSingleDay'   => $startDate->isSameDay($endDate),
        ]);
    }

    /**
     * Print the Day Book report in a new window.
     */
    public function print(Request $request): View
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

        // Combine outflows
        $outflows = $expenses->concat($paymentVouchers);

        // Combine into unified ledger entries
        $ledgerEntries = collect();

        foreach ($inflows as $inflow) {
            $ledgerEntries->push([
                'date' => $inflow->date ?? $inflow->created_at,
                'created_at' => $inflow->created_at,
                'voucher_no' => $inflow->voucher_no,
                'type' => 'Inflow',
                'details' => $inflow->received_from_type === 'tenant'
                    ? '👤 Tenant: ' . ($inflow->tenant ? $inflow->tenant->name : 'N/A') . ' (' . ($inflow->payments->map(fn($p) => $p->unit?->unit_number)->filter()->unique()->implode(', ') ?: 'N/A') . ')'
                    : ($inflow->received_from_type === 'owner'
                        ? '👤 Partner: ' . ($inflow->owner ? $inflow->owner->name : 'N/A')
                        : '👤 Misc: ' . ($inflow->other_name ?: 'N/A') . ($inflow->notes ? ' • ' . $inflow->notes : '')),
                'method' => $inflow->payment_method . ($inflow->paymentAccount ? ' (' . $inflow->paymentAccount->name . ')' : ''),
                'debit' => 0.0,
                'credit' => (float)$inflow->amount,
            ]);
        }

        foreach ($outflows as $outflow) {
            $isExpense = $outflow instanceof \App\Models\Expense;
            $details = $isExpense
                ? '💸 Expense: ' . ($outflow->expenseHead?->name ?? 'Expense')
                : ($outflow->is_advance
                    ? '⚠️ Advance Payout to: ' . ($outflow->paid_to_type === 'owner' ? ($outflow->owner?->name ?? 'Partner') : ($outflow->other_name ?? 'N/A'))
                    : '📤 Payout to: ' . ($outflow->paid_to_type === 'owner' ? ($outflow->owner?->name ?? 'Partner') : ($outflow->other_name ?? 'N/A')));

            if ($outflow->notes) {
                $details .= ' • ' . $outflow->notes;
            }

            $ledgerEntries->push([
                'date' => $outflow->date,
                'created_at' => $outflow->created_at,
                'voucher_no' => $outflow->voucher_no,
                'type' => 'Outflow',
                'details' => $details,
                'method' => $outflow->payment_method . ($outflow->paymentAccount ? ' (' . $outflow->paymentAccount->name . ')' : ''),
                'debit' => (float)$outflow->amount,
                'credit' => 0.0,
            ]);
        }

        // Sort chronologically
        $ledgerEntries = $ledgerEntries->sortBy(function ($item) {
            $date = $item['date'] instanceof Carbon ? $item['date'] : Carbon::parse($item['date']);
            $createdAt = $item['created_at'] instanceof Carbon ? $item['created_at'] : Carbon::parse($item['created_at']);
            return $date->format('Y-m-d') . '_' . $createdAt->format('Y-m-d H:i:s');
        })->values();

        // Calculate running balance
        $runningBalance = 0.0;
        $ledgerEntries = $ledgerEntries->map(function ($item) use (&$runningBalance) {
            $runningBalance += ($item['credit'] - $item['debit']);
            $item['running_balance'] = $runningBalance;
            return $item;
        });

        // Set up filters summary
        $filterChips = [
            ['label' => 'Period', 'value' => $startDate->format('d M Y') . ' to ' . $endDate->format('d M Y')],
        ];

        $columns = [
            ['key' => 'date',            'label' => 'Date',              'type' => 'date'],
            ['key' => 'voucher_no',      'label' => 'Voucher #',         'td_class' => 'mono'],
            ['key' => 'details',         'label' => 'Details / Reference'],
            ['key' => 'method',          'label' => 'Method / Account',  'td_class' => 'mono'],
            ['key' => 'debit',           'label' => 'Debit (Outflow)',   'type' => 'debit',   'class' => 'text-right'],
            ['key' => 'credit',          'label' => 'Credit (Inflow)',   'type' => 'credit',  'class' => 'text-right'],
            ['key' => 'running_balance', 'label' => 'Running Balance',   'type' => 'balance', 'class' => 'text-right'],
        ];

        return view('ledgers.print_page', [
            'pageTitle'    => 'Day Book Ledger Statement',
            'filterChips'  => $filterChips,
            'columns'      => $columns,
            'rows'         => $ledgerEntries->toArray(),
        ]);
    }
}


