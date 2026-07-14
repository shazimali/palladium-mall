<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ReceivingVoucher;
use App\Models\PaymentVoucher;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;

class CashBookController extends Controller
{
    /**
     * Show the Cash Book report.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.cashbook')) {
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

        $reportData = $this->getCashBookEntries($startDate, $endDate);

        return view('reports.cash_book', [
            'title'         => 'Cash Book Report',
            'ledgerEntries' => $reportData['entries'],
            'totalInflows'  => $reportData['totalInflows'],
            'totalOutflows' => $reportData['totalOutflows'],
            'netFlow'       => $reportData['totalInflows'] - $reportData['totalOutflows'],
            'startDate'     => $startDate->toDateString(),
            'endDate'       => $endDate->toDateString(),
            'isSingleDay'   => $startDate->isSameDay($endDate),
        ]);
    }

    /**
     * Print the Cash Book report in a new window.
     */
    public function print(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.cashbook')) {
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

        $reportData = $this->getCashBookEntries($startDate, $endDate);

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
            'pageTitle'    => 'Cash Book Ledger Statement',
            'filterChips'  => $filterChips,
            'columns'      => $columns,
            'rows'         => $reportData['entries']->toArray(),
        ]);
    }

    /**
     * Helper to fetch and format Cash Book entries (filtered by Cash payment method/account) for the given date range.
     */
    private function getCashBookEntries(Carbon $startDate, Carbon $endDate): array
    {
        // Fetch Inflows (Receiving Vouchers) filtered by cash
        $inflows = ReceivingVoucher::with(['tenant', 'owner', 'paymentAccount', 'payments.unit'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                  ->orWhereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
            ->get();

        // Fetch General Inflows filtered by cash
        $generalInflows = \App\Models\GeneralReceivingVoucher::with(['party', 'paymentAccount'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                  ->orWhereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
            ->get();

        // Fetch Owner Receivables filtered by cash accounts
        $ownerReceivables = \App\Models\OwnerReceivable::with(['owner', 'paymentAccount'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'))
            ->get();

        // Fetch Outflows (Expenses) filtered by cash
        $expenses = Expense::with(['expenseHead', 'paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                  ->orWhereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
            ->get();

        // Fetch Outflows (Payment Vouchers) filtered by cash
        $paymentVouchers = PaymentVoucher::with(['owner', 'paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                  ->orWhereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
            ->get();

        // Fetch Owner Payables filtered by cash accounts
        $ownerPayables = \App\Models\OwnerPayable::with(['owner', 'paymentAccount'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->whereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'))
            ->get();

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

        foreach ($generalInflows as $inflow) {
            $details = '👤 Party: ' . ($inflow->party ? $inflow->party->name : 'N/A');
            if ($inflow->notes) {
                $details .= ' • ' . $inflow->notes;
            }
            $ledgerEntries->push([
                'date' => $inflow->date ?? $inflow->created_at,
                'created_at' => $inflow->created_at,
                'voucher_no' => $inflow->voucher_no,
                'type' => 'Inflow',
                'details' => $details,
                'method' => $inflow->payment_method . ($inflow->paymentAccount ? ' (' . $inflow->paymentAccount->name . ')' : ''),
                'debit' => 0.0,
                'credit' => (float)$inflow->amount,
            ]);
        }

        foreach ($ownerReceivables as $orv) {
            $details = '👤 Partner Inflow (ORV): ' . ($orv->owner ? $orv->owner->name : 'N/A');
            if ($orv->reference) {
                $details .= ' • Ref: ' . $orv->reference;
            }
            if ($orv->notes) {
                $details .= ' • ' . $orv->notes;
            }
            $ledgerEntries->push([
                'date' => $orv->date ?? $orv->created_at,
                'created_at' => $orv->created_at,
                'voucher_no' => $orv->voucher_no,
                'type' => 'Inflow',
                'details' => $details,
                'method' => ($orv->paymentAccount ? $orv->paymentAccount->name : 'N/A'),
                'debit' => 0.0,
                'credit' => (float)$orv->amount,
            ]);
        }

        foreach ($expenses as $expense) {
            $details = '💸 Expense: ' . ($expense->expenseHead?->name ?? 'Expense');
            if ($expense->notes) {
                $details .= ' • ' . $expense->notes;
            }
            $ledgerEntries->push([
                'date' => $expense->date,
                'created_at' => $expense->created_at,
                'voucher_no' => $expense->voucher_no,
                'type' => 'Outflow',
                'details' => $details,
                'method' => $expense->payment_method . ($expense->paymentAccount ? ' (' . $expense->paymentAccount->name . ')' : ''),
                'debit' => (float)$expense->amount,
                'credit' => 0.0,
            ]);
        }

        foreach ($paymentVouchers as $pv) {
            $details = $pv->is_advance
                ? '⚠️ Advance Payout to: ' . ($pv->paid_to_type === 'owner' ? ($pv->owner?->name ?? 'Partner') : ($pv->other_name ?? 'N/A'))
                : '📤 Payout to: ' . ($pv->paid_to_type === 'owner' ? ($pv->owner?->name ?? 'Partner') : ($pv->other_name ?? 'N/A'));
            if ($pv->notes) {
                $details .= ' • ' . $pv->notes;
            }
            $ledgerEntries->push([
                'date' => $pv->date,
                'created_at' => $pv->created_at,
                'voucher_no' => $pv->voucher_no,
                'type' => 'Outflow',
                'details' => $details,
                'method' => $pv->payment_method . ($pv->paymentAccount ? ' (' . $pv->paymentAccount->name . ')' : ''),
                'debit' => (float)$pv->amount,
                'credit' => 0.0,
            ]);
        }

        foreach ($ownerPayables as $opv) {
            $details = '💸 Partner Outflow (OPV): ' . ($opv->owner ? $opv->owner->name : 'N/A');
            if ($opv->reference) {
                $details .= ' • Ref: ' . $opv->reference;
            }
            if ($opv->notes) {
                $details .= ' • ' . $opv->notes;
            }
            $ledgerEntries->push([
                'date' => $opv->date ?? $opv->created_at,
                'created_at' => $opv->created_at,
                'voucher_no' => $opv->voucher_no,
                'type' => 'Outflow',
                'details' => $details,
                'method' => ($opv->paymentAccount ? $opv->paymentAccount->name : 'N/A'),
                'debit' => (float)$opv->amount,
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

        $totalInflows = (float) $inflows->sum('amount') + (float) $generalInflows->sum('amount') + (float) $ownerReceivables->sum('amount');
        $totalOutflows = (float) $expenses->sum('amount') + (float) $paymentVouchers->sum('amount') + (float) $ownerPayables->sum('amount');

        return [
            'entries' => $ledgerEntries,
            'totalInflows' => $totalInflows,
            'totalOutflows' => $totalOutflows,
        ];
    }
}
