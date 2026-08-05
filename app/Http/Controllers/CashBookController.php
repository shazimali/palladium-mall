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

        // Fetch Inflows (Receiving Vouchers) for payment_account_id = 2
        $inflows = ReceivingVoucher::with(['tenant', 'owner', 'paymentAccount', 'payments.unit'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('payment_account_id', 2)
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Fetch General Inflows for payment_account_id = 2
        $generalInflows = \App\Models\GeneralReceivingVoucher::with(['party', 'paymentAccount'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('payment_account_id', 2)
            ->get();

        // Fetch Outflows (Expenses) for payment_account_id = 2
        $expenses = Expense::with(['expenseHead', 'paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('payment_account_id', 2)
            ->get();

        // Fetch Outflows (Payment Vouchers) for payment_account_id = 2
        $paymentVouchers = PaymentVoucher::with(['paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('payment_account_id', 2)
            ->get();

        // Fetch Outflows (Withdrawals) for payment_account_id = 2
        $withdrawals = \App\Models\Withdrawal::with(['owner', 'paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('payment_account_id', 2)
            ->get();

        // Fetch Outflows (Paid JV Vouchers) for payment_account_id = 2
        $jvVouchers = \App\Models\JvVoucher::with(['expenseHead', 'paymentAccount', 'user'])
            ->where('status', 'paid')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('paid_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->whereNull('paid_date')
                            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
                    });
            })
            ->where('payment_account_id', 2)
            ->get();

        // Combine outflows
        $outflows = $expenses->concat($paymentVouchers)->concat($withdrawals)->concat($jvVouchers);

        // Combine into unified ledger entries
        $ledgerEntries = collect();

        foreach ($inflows as $inflow) {
            $unitNo = $inflow->payments->first()?->unit?->unit_number ?? $inflow->tenant?->unit?->unit_number;
            $name = $inflow->received_from_type === 'tenant'
                ? ($inflow->tenant ? $inflow->tenant->name : '')
                : ($inflow->received_from_type === 'owner'
                    ? ($inflow->owner ? $inflow->owner->name : '')
                    : ($inflow->other_name ?: ''));

            $notes = trim($inflow->notes ?? '');
            if ($notes !== '') {
                $details = ($name && !str_contains(strtolower($notes), strtolower($name))) ? $name . ' • ' . $notes : $notes;
            } else {
                $details = $name ?: '—';
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
                'model_type' => 'receiving_voucher',
                'model_id' => $inflow->id,
                'unit_number' => $unitNo,
            ]);
        }

        foreach ($generalInflows as $inflow) {
            $name = $inflow->party ? $inflow->party->name : '';
            $notes = trim($inflow->notes ?? '');
            if ($notes !== '') {
                $details = ($name && !str_contains(strtolower($notes), strtolower($name))) ? $name . ' • ' . $notes : $notes;
            } else {
                $details = $name ?: '—';
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
            $notes = trim($outflow->notes ?? '');

            if ($isExpense) {
                $type = 'Expense';
                $head = $outflow->expenseHead?->name;
                if ($notes !== '') {
                    $details = ($head && strtolower($head) !== strtolower($notes)) ? $head . ' • ' . $notes : $notes;
                } else {
                    $details = $head ?: 'Expense';
                }
            } elseif ($isWithdrawal) {
                $type = 'Payout';
                $name = $outflow->owner?->name;
                if ($notes !== '') {
                    $details = ($name && !str_contains(strtolower($notes), strtolower($name))) ? $name . ' • ' . $notes : $notes;
                } else {
                    $details = $name ?: 'Withdrawal';
                }
            } elseif ($isJvVoucher) {
                $type = 'Payout';
                $head = $outflow->expenseHead?->name;
                if ($notes !== '') {
                    $details = ($head && strtolower($head) !== strtolower($notes)) ? $head . ' • ' . $notes : $notes;
                } else {
                    $details = $head ?: 'JV Voucher';
                }
            } else {
                $type = 'Payout';
                $recipient = $outflow->paid_to_type === 'owner' ? ($outflow->owner?->name) : ($outflow->other_name);
                if ($notes !== '') {
                    $details = ($recipient && !str_contains(strtolower($notes), strtolower($recipient))) ? $recipient . ' • ' . $notes : $notes;
                } else {
                    $details = $recipient ?: 'Payout';
                }
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

        // Calculate & Prepend Opening Balance (Previous Day / Period Closing Balance)
        $openingBalance = $this->getOpeningBalance($startDate);
        $prevDate = $startDate->copy()->subDay();
        $ledgerEntries->push([
            'date' => $prevDate,
            'created_at' => Carbon::create(1970, 1, 1),
            'voucher_no' => 'OP-BAL',
            'type' => 'OP Balance',
            'details' => 'OP Balance (Closing Balance as of ' . $prevDate->format('d M Y') . ')',
            'method' => 'Cash',
            'debit' => $openingBalance >= 0 ? (float) $openingBalance : 0.0,
            'credit' => $openingBalance < 0 ? (float) abs($openingBalance) : 0.0,
            'model_type' => 'opening_balance',
            'model_id' => null,
            'unit_number' => null,
            'is_opening' => true,
        ]);

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
            'openingBalance' => $openingBalance,
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

        // Fetch Inflows (Receiving Vouchers) for payment_account_id = 2
        $inflows = ReceivingVoucher::with(['tenant', 'owner', 'paymentAccount', 'payments.unit'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('payment_account_id', 2)
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        // Fetch General Inflows for payment_account_id = 2
        $generalInflows = \App\Models\GeneralReceivingVoucher::with(['party', 'paymentAccount'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('payment_account_id', 2)
            ->get();

        // Fetch Outflows (Expenses) for payment_account_id = 2
        $expenses = Expense::with(['expenseHead', 'paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('payment_account_id', 2)
            ->get();

        // Fetch Outflows (Payment Vouchers) for payment_account_id = 2
        $paymentVouchers = PaymentVoucher::with(['paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('payment_account_id', 2)
            ->get();

        // Fetch Outflows (Withdrawals) for payment_account_id = 2
        $withdrawals = \App\Models\Withdrawal::with(['owner', 'paymentAccount', 'user'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->where('payment_account_id', 2)
            ->get();

        // Fetch Outflows (Paid JV Vouchers) for payment_account_id = 2
        $jvVouchers = \App\Models\JvVoucher::with(['expenseHead', 'paymentAccount', 'user'])
            ->where('status', 'paid')
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('paid_date', [$startDate->toDateString(), $endDate->toDateString()])
                    ->orWhere(function ($q2) use ($startDate, $endDate) {
                        $q2->whereNull('paid_date')
                            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()]);
                    });
            })
            ->where('payment_account_id', 2)
            ->get();

        // Combine outflows
        $outflows = $expenses->concat($paymentVouchers)->concat($withdrawals)->concat($jvVouchers);

        // Combine into unified ledger entries
        $ledgerEntries = collect();

        foreach ($inflows as $inflow) {
            $unitNo = $inflow->payments->first()?->unit?->unit_number ?? $inflow->tenant?->unit?->unit_number;
            $name = $inflow->received_from_type === 'tenant'
                ? ($inflow->tenant ? $inflow->tenant->name : '')
                : ($inflow->received_from_type === 'owner'
                    ? ($inflow->owner ? $inflow->owner->name : '')
                    : ($inflow->other_name ?: ''));

            $notes = trim($inflow->notes ?? '');
            if ($notes !== '') {
                $details = ($name && !str_contains(strtolower($notes), strtolower($name))) ? $name . ' • ' . $notes : $notes;
            } else {
                $details = $name ?: '—';
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
                'model_type' => 'receiving_voucher',
                'model_id' => $inflow->id,
                'unit_number' => $unitNo,
            ]);
        }

        foreach ($generalInflows as $inflow) {
            $name = $inflow->party ? $inflow->party->name : '';
            $notes = trim($inflow->notes ?? '');
            if ($notes !== '') {
                $details = ($name && !str_contains(strtolower($notes), strtolower($name))) ? $name . ' • ' . $notes : $notes;
            } else {
                $details = $name ?: '—';
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
            $notes = trim($outflow->notes ?? '');

            if ($isExpense) {
                $type = 'Expense';
                $head = $outflow->expenseHead?->name;
                if ($notes !== '') {
                    $details = ($head && strtolower($head) !== strtolower($notes)) ? $head . ' • ' . $notes : $notes;
                } else {
                    $details = $head ?: 'Expense';
                }
            } elseif ($isWithdrawal) {
                $type = 'Payout';
                $name = $outflow->owner?->name;
                if ($notes !== '') {
                    $details = ($name && !str_contains(strtolower($notes), strtolower($name))) ? $name . ' • ' . $notes : $notes;
                } else {
                    $details = $name ?: 'Withdrawal';
                }
            } elseif ($isJvVoucher) {
                $type = 'Payout';
                $head = $outflow->expenseHead?->name;
                if ($notes !== '') {
                    $details = ($head && strtolower($head) !== strtolower($notes)) ? $head . ' • ' . $notes : $notes;
                } else {
                    $details = $head ?: 'JV Voucher';
                }
            } else {
                $type = 'Payout';
                $recipient = $outflow->paid_to_type === 'owner' ? ($outflow->owner?->name) : ($outflow->other_name);
                if ($notes !== '') {
                    $details = ($recipient && !str_contains(strtolower($notes), strtolower($recipient))) ? $recipient . ' • ' . $notes : $notes;
                } else {
                    $details = $recipient ?: 'Payout';
                }
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

        // Calculate & Prepend Opening Balance (Previous Day / Period Closing Balance)
        $openingBalance = $this->getOpeningBalance($startDate);
        $prevDate = $startDate->copy()->subDay();
        $ledgerEntries->push([
            'date' => $prevDate,
            'created_at' => Carbon::create(1970, 1, 1),
            'voucher_no' => 'OP-BAL',
            'type' => 'OP Balance',
            'details' => 'OP Balance (Closing Balance as of ' . $prevDate->format('d M Y') . ')',
            'method' => 'Cash',
            'debit' => $openingBalance >= 0 ? (float) $openingBalance : 0.0,
            'credit' => $openingBalance < 0 ? (float) abs($openingBalance) : 0.0,
            'model_type' => 'opening_balance',
            'model_id' => null,
            'unit_number' => null,
            'is_opening' => true,
        ]);

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

        // Set up filters summary
        $filterChips = [
            ['label' => 'Period', 'value' => $startDate->format('d M Y') . ' to ' . $endDate->format('d M Y')],
            ['label' => 'Total Debit', 'value' => number_format($totalInflows, 2)],
            ['label' => 'Total Credit', 'value' => number_format($totalOutflows, 2)],
            ['label' => 'Net Cash', 'value' => number_format($netFlow, 2)],
        ];

        $columns = [
            ['key' => 'date', 'label' => 'Date', 'type' => 'date', 'class' => 'col-compact'],
            ['key' => 'voucher_no', 'label' => 'Voucher #', 'td_class' => 'mono', 'class' => 'col-tight'],
            ['key' => 'type', 'label' => 'Type', 'type' => 'badge', 'class' => 'col-compact'],
            ['key' => 'details', 'label' => 'Description / Ref', 'class' => 'col-desc'],
            ['key' => 'unit_number', 'label' => 'Unit', 'class' => 'col-tight'],
            ['key' => 'debit', 'label' => 'Debit', 'type' => 'debit', 'class' => 'text-right col-compact'],
            ['key' => 'credit', 'label' => 'Credit', 'type' => 'credit', 'class' => 'text-right col-compact'],
            ['key' => 'running_balance', 'label' => 'Balance', 'type' => 'balance', 'class' => 'text-right col-compact'],
        ];

        return view('ledgers.print_page', [
            'pageTitle' => 'Daily Cash Book Statement',
            'filterChips' => $filterChips,
            'columns' => $columns,
            'rows' => $ledgerEntries->toArray(),
        ]);
    }

    /**
     * Calculate previous accumulated cash balance before the given start date.
     */
    private function getOpeningBalance(Carbon $startDate): float
    {
        $startDateStr = $startDate->toDateString();

        $accountOpeningBalance = (float) (\App\Models\PaymentAccount::where('id', 2)->value('opening_balance') ?? 0.0);

        $priorReceiving = ReceivingVoucher::where('date', '<', $startDateStr)
            ->where('payment_account_id', 2)
            ->sum('amount');

        $priorGeneralReceiving = \App\Models\GeneralReceivingVoucher::where('date', '<', $startDateStr)
            ->where('payment_account_id', 2)
            ->sum('amount');

        $priorExpenses = Expense::where('date', '<', $startDateStr)
            ->where('payment_account_id', 2)
            ->sum('amount');

        $priorPaymentVouchers = PaymentVoucher::where('date', '<', $startDateStr)
            ->where('payment_account_id', 2)
            ->sum('amount');

        $priorWithdrawals = \App\Models\Withdrawal::where('date', '<', $startDateStr)
            ->where('payment_account_id', 2)
            ->sum('amount');

        $priorJvVouchers = \App\Models\JvVoucher::where('status', 'paid')
            ->where(function ($q) use ($startDateStr) {
                $q->where('paid_date', '<', $startDateStr)
                    ->orWhere(function ($q2) use ($startDateStr) {
                        $q2->whereNull('paid_date')
                            ->where('date', '<', $startDateStr);
                    });
            })
            ->where('payment_account_id', 2)
            ->sum('amount');

        return $accountOpeningBalance + (float) (($priorReceiving + $priorGeneralReceiving) - ($priorExpenses + $priorPaymentVouchers + $priorWithdrawals + $priorJvVouchers));
    }
}
