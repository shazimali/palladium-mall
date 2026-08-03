<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Payment;
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

        // Fetch Inflows (Receiving Vouchers) filtered by cash
        $inflows = ReceivingVoucher::with(['tenant', 'owner', 'paymentAccount', 'payments.unit'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                    ->orWhereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Fetch General Inflows filtered by cash
        $generalInflows = \App\Models\GeneralReceivingVoucher::with(['party', 'paymentAccount'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                    ->orWhereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
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
        $paymentVouchers = PaymentVoucher::with(['paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                    ->orWhereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
            ->get();

        // Fetch Outflows (Withdrawals) filtered by cash
        $withdrawals = \App\Models\Withdrawal::with(['owner', 'paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($q) {
                $q->whereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
            ->get();

        // Fetch Outflows (Paid JV Vouchers) filtered by cash
        $jvVouchers = \App\Models\JvVoucher::with(['expenseHead', 'paymentAccount', 'user'])
            ->where('status', 'paid')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('paid_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->whereNull('paid_date')
                            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
                    });
            })
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                    ->orWhere('payment_method', 'Cash')
                    ->orWhereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
            ->get();

        // Combine outflows
        $outflows = $expenses->concat($paymentVouchers)->concat($withdrawals)->concat($jvVouchers);

        // Combine into unified ledger entries
        $ledgerEntries = collect();

        foreach ($inflows as $inflow) {
            $unitNo = $inflow->payments->first()?->unit?->unit_number ?? $inflow->tenant?->unit?->unit_number;
            $ledgerEntries->push([
                'date' => $inflow->date ?? $inflow->created_at,
                'created_at' => $inflow->created_at,
                'voucher_no' => $inflow->voucher_no,
                'type' => 'Receipt',
                'details' => $inflow->received_from_type === 'tenant'
                    ? '👤 Tenant: ' . ($inflow->tenant ? $inflow->tenant->name : 'N/A')
                    : ($inflow->received_from_type === 'owner'
                        ? '👤 Partner: ' . ($inflow->owner ? $inflow->owner->name : 'N/A')
                        : '👤 Misc: ' . ($inflow->other_name ?: 'N/A') . ($inflow->notes ? ' • ' . $inflow->notes : '')),
                'method' => $inflow->payment_method . ($inflow->paymentAccount ? ' (' . $inflow->paymentAccount->name . ')' : ''),
                'debit' => (float) $inflow->amount,
                'credit' => 0.0,
                'model_type' => 'receiving_voucher',
                'model_id' => $inflow->id,
                'unit_number' => $unitNo,
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
                'type' => 'Receipt',
                'details' => $details,
                'method' => $inflow->payment_method . ($inflow->paymentAccount ? ' (' . $inflow->paymentAccount->name . ')' : ''),
                'debit' => (float) $inflow->amount,
                'credit' => 0.0,
                'model_type' => 'general_receiving_voucher',
                'model_id' => $inflow->id,
                'unit_number' => null,
            ]);
        }

        foreach ($outflows as $outflow) {
            $isExpense = $outflow instanceof Expense;
            $isWithdrawal = $outflow instanceof \App\Models\Withdrawal;
            $isJvVoucher = $outflow instanceof \App\Models\JvVoucher;

            if ($isExpense) {
                $type = 'Payout (Expense)';
                $details = '💸 Expense: ' . ($outflow->expenseHead?->name ?? 'Expense');
            } elseif ($isWithdrawal) {
                $type = 'Payout (Withdrawal)';
                $details = '🏧 Withdrawal: ' . ($outflow->owner?->name ?? 'Partner');
            } elseif ($isJvVoucher) {
                $type = 'Payout (JV Voucher)';
                $details = '📑 JV Voucher: ' . ($outflow->expenseHead?->name ?? 'Expense');
            } else {
                $type = 'Payout';
                $details = $outflow->is_advance
                    ? '⚠️ Advance Payout to: ' . ($outflow->paid_to_type === 'owner' ? ($outflow->owner?->name ?? 'Partner') : ($outflow->other_name ?? 'N/A'))
                    : '📤 Payout to: ' . ($outflow->paid_to_type === 'owner' ? ($outflow->owner?->name ?? 'Partner') : ($outflow->other_name ?? 'N/A'));
            }

            if ($outflow->notes) {
                $details .= ' • ' . $outflow->notes;
            }

            $entryDate = $isJvVoucher ? ($outflow->paid_date ?? $outflow->date) : $outflow->date;

            $ledgerEntries->push([
                'date' => $entryDate,
                'created_at' => $outflow->created_at,
                'voucher_no' => $outflow->voucher_no,
                'type' => $type,
                'details' => $details,
                'method' => ($isWithdrawal ? 'withdrawal' : $outflow->payment_method) . ($outflow->paymentAccount ? ' (' . $outflow->paymentAccount->name . ')' : ''),
                'debit' => 0.0,
                'credit' => (float) $outflow->amount,
                'model_type' => $isExpense ? 'expense' : ($isWithdrawal ? 'withdrawal' : ($isJvVoucher ? 'jv_voucher' : 'payment_voucher')),
                'model_id' => $outflow->id,
                'unit_number' => null,
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
            $runningBalance += ($item['debit'] - $item['credit']);
            $item['running_balance'] = $runningBalance;
            return $item;
        });

        // Sums
        $totalInflows = $inflows->sum('amount') + $generalInflows->sum('amount');
        $totalOutflows = $outflows->sum('amount');
        $netFlow = $totalInflows - $totalOutflows;

        return view('reports.cash_book', [
            'title' => 'Cash Book Report',
            'ledgerEntries' => $ledgerEntries,
            'totalInflows' => $totalInflows,
            'totalOutflows' => $totalOutflows,
            'netFlow' => $netFlow,
            'startDate' => $startDate->toDateString(),
            'endDate' => $endDate->toDateString(),
            'isSingleDay' => $startDate->isSameDay($endDate),
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

        // Fetch Inflows (Receiving Vouchers) filtered by cash
        $inflows = ReceivingVoucher::with(['tenant', 'owner', 'paymentAccount', 'payments.unit'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                    ->orWhereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Fetch General Inflows filtered by cash
        $generalInflows = \App\Models\GeneralReceivingVoucher::with(['party', 'paymentAccount'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                    ->orWhereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
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
        $paymentVouchers = PaymentVoucher::with(['paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                    ->orWhereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
            ->get();

        // Fetch Outflows (Withdrawals) filtered by cash
        $withdrawals = \App\Models\Withdrawal::with(['owner', 'paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where(function ($q) {
                $q->whereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
            ->get();

        // Fetch Outflows (Paid JV Vouchers) filtered by cash
        $jvVouchers = \App\Models\JvVoucher::with(['expenseHead', 'paymentAccount', 'user'])
            ->where('status', 'paid')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('paid_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->whereNull('paid_date')
                            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
                    });
            })
            ->where(function ($q) {
                $q->where('payment_method', 'cash')
                    ->orWhere('payment_method', 'Cash')
                    ->orWhereHas('paymentAccount', fn($acc) => $acc->where('type', 'cash'));
            })
            ->get();

        // Combine outflows
        $outflows = $expenses->concat($paymentVouchers)->concat($withdrawals)->concat($jvVouchers);

        // Combine into unified ledger entries
        $ledgerEntries = collect();

        foreach ($inflows as $inflow) {
            $unitNo = $inflow->payments->first()?->unit?->unit_number ?? $inflow->tenant?->unit?->unit_number;
            $ledgerEntries->push([
                'date' => $inflow->date ?? $inflow->created_at,
                'created_at' => $inflow->created_at,
                'voucher_no' => $inflow->voucher_no,
                'type' => 'Receipt',
                'details' => $inflow->received_from_type === 'tenant'
                    ? '👤 Tenant: ' . ($inflow->tenant ? $inflow->tenant->name : 'N/A')
                    : ($inflow->received_from_type === 'owner'
                        ? '👤 Partner: ' . ($inflow->owner ? $inflow->owner->name : 'N/A')
                        : '👤 Misc: ' . ($inflow->other_name ?: 'N/A') . ($inflow->notes ? ' • ' . $inflow->notes : '')),
                'method' => $inflow->payment_method . ($inflow->paymentAccount ? ' (' . $inflow->paymentAccount->name . ')' : ''),
                'debit' => (float) $inflow->amount,
                'credit' => 0.0,
                'model_type' => 'receiving_voucher',
                'model_id' => $inflow->id,
                'unit_number' => $unitNo,
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
                'type' => 'Receipt',
                'details' => $details,
                'method' => $inflow->payment_method . ($inflow->paymentAccount ? ' (' . $inflow->paymentAccount->name . ')' : ''),
                'debit' => (float) $inflow->amount,
                'credit' => 0.0,
                'model_type' => 'general_receiving_voucher',
                'model_id' => $inflow->id,
                'unit_number' => null,
            ]);
        }

        foreach ($outflows as $outflow) {
            $isExpense = $outflow instanceof Expense;
            $isWithdrawal = $outflow instanceof \App\Models\Withdrawal;
            $isJvVoucher = $outflow instanceof \App\Models\JvVoucher;

            if ($isExpense) {
                $type = 'Payout (Expense)';
                $details = '💸 Expense: ' . ($outflow->expenseHead?->name ?? 'Expense');
            } elseif ($isWithdrawal) {
                $type = 'Payout (Withdrawal)';
                $details = '🏧 Withdrawal: ' . ($outflow->owner?->name ?? 'Partner');
            } elseif ($isJvVoucher) {
                $type = 'Payout (JV Voucher)';
                $details = '📑 JV Voucher: ' . ($outflow->expenseHead?->name ?? 'Expense');
            } else {
                $type = 'Payout';
                $details = $outflow->is_advance
                    ? '⚠️ Advance Payout to: ' . ($outflow->other_name ?? 'N/A')
                    : '📤 Payout to: ' . ($outflow->other_name ?? 'N/A');
            }

            if ($outflow->notes) {
                $details .= ' • ' . $outflow->notes;
            }

            $entryDate = $isJvVoucher ? ($outflow->paid_date ?? $outflow->date) : $outflow->date;

            $ledgerEntries->push([
                'date' => $entryDate,
                'created_at' => $outflow->created_at,
                'voucher_no' => $outflow->voucher_no,
                'type' => $type,
                'details' => $details,
                'method' => ($isWithdrawal ? 'withdrawal' : $outflow->payment_method) . ($outflow->paymentAccount ? ' (' . $outflow->paymentAccount->name . ')' : ''),
                'debit' => 0.0,
                'credit' => (float) $outflow->amount,
                'model_type' => $isExpense ? 'expense' : ($isWithdrawal ? 'withdrawal' : ($isJvVoucher ? 'jv_voucher' : 'payment_voucher')),
                'model_id' => $outflow->id,
                'unit_number' => null,
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
            $runningBalance += ($item['debit'] - $item['credit']);
            $item['running_balance'] = $runningBalance;
            return $item;
        });

        // Set up filters summary
        $filterChips = [
            ['label' => 'Period', 'value' => $startDate->format('d M Y') . ' to ' . $endDate->format('d M Y')],
        ];

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
            ['key' => 'unit_number', 'label' => 'Flat/Shop'],
            ['key' => 'voucher_no', 'label' => 'Voucher #', 'td_class' => 'mono'],
            ['key' => 'type', 'label' => 'Type'],
            ['key' => 'details', 'label' => 'Description / Ref'],
            ['key' => 'debit', 'label' => 'Debit (Inflow)', 'type' => 'debit', 'class' => 'text-right'],
            ['key' => 'credit', 'label' => 'Credit (Outflow)', 'type' => 'credit', 'class' => 'text-right'],
            ['key' => 'running_balance', 'label' => 'Running Balance', 'type' => 'balance', 'class' => 'text-right'],
        ];

        return view('ledgers.print_page', [
            'pageTitle' => 'Daily Cash Book Statement',
            'filterChips' => $filterChips,
            'columns' => $columns,
            'rows' => $ledgerEntries->toArray(),
        ]);
    }
}
