<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Owner;
use App\Models\PaymentAccount;
use App\Models\ExpenseHead;
use App\Models\Payment;
use App\Models\ReceivingVoucher;
use App\Models\PaymentVoucher;
use App\Models\Expense;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Barryvdh\Dompdf\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\TenantLedgerExport;
use App\Exports\OwnerLedgerExport;
use App\Exports\AccountLedgerExport;
use App\Exports\ExpenseLedgerExport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LedgerController extends Controller
{
    /**
     * Tenant / Unit Ledger
     */
    public function tenant(Request $request): View
    {
        $this->authorizeLedger();

        $units = Unit::with(['tenant', 'otherTenant'])->orderBy('unit_number')->get();
        
        $unitId = $request->query('unit_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $ledgerData = null;
        if ($unitId) {
            $ledgerData = $this->getTenantLedgerData($unitId, $dateFrom, $dateTo);
        }

        return view('ledgers.tenant', [
            'title'      => 'Tenant / Unit Ledger',
            'units'      => $units,
            'unitId'     => $unitId,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
            'ledgerData' => $ledgerData,
        ]);
    }

    public function exportTenantPdf(Request $request)
    {
        $this->authorizeLedger();

        $unitId = $request->query('unit_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (!$unitId) {
            return back()->with('error', 'Select a unit to export.');
        }

        $ledgerData = $this->getTenantLedgerData($unitId, $dateFrom, $dateTo);
        
        $pdf = Pdf::loadView('ledgers.pdf', array_merge($ledgerData, [
            'type'      => 'tenant',
            'dateFrom'  => $dateFrom,
            'dateTo'    => $dateTo,
            'title'     => 'Tenant Statement - Unit ' . $ledgerData['unit']->unit_number,
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('tenant_ledger_unit_' . $ledgerData['unit']->unit_number . '.pdf');
    }

    public function exportTenantExcel(Request $request)
    {
        $this->authorizeLedger();

        $unitId = $request->query('unit_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (!$unitId) {
            return back()->with('error', 'Select a unit to export.');
        }

        $ledgerData = $this->getTenantLedgerData($unitId, $dateFrom, $dateTo);

        return Excel::download(
            new TenantLedgerExport($ledgerData['entries'], 'Tenant Ledger - Unit ' . $ledgerData['unit']->unit_number, $ledgerData['summary']),
            'tenant_ledger_unit_' . $ledgerData['unit']->unit_number . '.xlsx'
        );
    }

    /**
     * Managing Owner Ledger
     */
    public function owner(Request $request): View
    {
        $this->authorizeLedger();

        $owners = Owner::orderBy('name')->get();
        
        $ownerId = $request->query('owner_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $ledgerData = null;
        if ($ownerId) {
            $ledgerData = $this->getOwnerLedgerData($ownerId, $dateFrom, $dateTo);
        }

        return view('ledgers.owner', [
            'title'      => 'Owner Ledger',
            'owners'     => $owners,
            'ownerId'    => $ownerId,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
            'ledgerData' => $ledgerData,
        ]);
    }

    public function exportOwnerPdf(Request $request)
    {
        $this->authorizeLedger();

        $ownerId = $request->query('owner_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (!$ownerId) {
            return back()->with('error', 'Select an owner to export.');
        }

        $ledgerData = $this->getOwnerLedgerData($ownerId, $dateFrom, $dateTo);
        
        $pdf = Pdf::loadView('ledgers.pdf', array_merge($ledgerData, [
            'type'      => 'owner',
            'dateFrom'  => $dateFrom,
            'dateTo'    => $dateTo,
            'title'     => 'Owner Ledger - ' . $ledgerData['owner']->name,
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('owner_ledger_' . str_replace(' ', '_', strtolower($ledgerData['owner']->name)) . '.pdf');
    }

    public function exportOwnerExcel(Request $request)
    {
        $this->authorizeLedger();

        $ownerId = $request->query('owner_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (!$ownerId) {
            return back()->with('error', 'Select an owner to export.');
        }

        $ledgerData = $this->getOwnerLedgerData($ownerId, $dateFrom, $dateTo);

        return Excel::download(
            new OwnerLedgerExport($ledgerData['entries'], 'Owner Ledger - ' . $ledgerData['owner']->name, $ledgerData['summary']),
            'owner_ledger_' . str_replace(' ', '_', strtolower($ledgerData['owner']->name)) . '.xlsx'
        );
    }

    /**
     * Payment Account Ledger
     */
    public function paymentAccount(Request $request): View
    {
        $this->authorizeLedger();

        $accounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();
        
        $accountId = $request->query('payment_account_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $ledgerData = null;
        if ($accountId) {
            $ledgerData = $this->getAccountLedgerData($accountId, $dateFrom, $dateTo);
        }

        return view('ledgers.payment_account', [
            'title'      => 'Payment Account Ledger',
            'accounts'   => $accounts,
            'accountId'  => $accountId,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
            'ledgerData' => $ledgerData,
        ]);
    }

    public function exportAccountPdf(Request $request)
    {
        $this->authorizeLedger();

        $accountId = $request->query('payment_account_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (!$accountId) {
            return back()->with('error', 'Select a payment account to export.');
        }

        $ledgerData = $this->getAccountLedgerData($accountId, $dateFrom, $dateTo);
        
        $pdf = Pdf::loadView('ledgers.pdf', array_merge($ledgerData, [
            'type'      => 'account',
            'dateFrom'  => $dateFrom,
            'dateTo'    => $dateTo,
            'title'     => 'Account Ledger - ' . $ledgerData['account']->name,
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('account_ledger_' . str_replace(' ', '_', strtolower($ledgerData['account']->name)) . '.pdf');
    }

    public function exportAccountExcel(Request $request)
    {
        $this->authorizeLedger();

        $accountId = $request->query('payment_account_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (!$accountId) {
            return back()->with('error', 'Select a payment account to export.');
        }

        $ledgerData = $this->getAccountLedgerData($accountId, $dateFrom, $dateTo);

        return Excel::download(
            new AccountLedgerExport($ledgerData['entries'], 'Account Ledger - ' . $ledgerData['account']->name, $ledgerData['summary']),
            'account_ledger_' . str_replace(' ', '_', strtolower($ledgerData['account']->name)) . '.xlsx'
        );
    }

    /**
     * Expense Head Ledger
     */
    public function expense(Request $request): View
    {
        $this->authorizeLedger();

        $heads = ExpenseHead::orderBy('name')->get();
        
        $expenseHeadId = $request->query('expense_head_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $ledgerData = null;
        if ($expenseHeadId) {
            $ledgerData = $this->getExpenseLedgerData($expenseHeadId, $dateFrom, $dateTo);
        }

        return view('ledgers.expense', [
            'title'         => 'Expense Head Ledger',
            'heads'         => $heads,
            'expenseHeadId' => $expenseHeadId,
            'dateFrom'      => $dateFrom,
            'dateTo'        => $dateTo,
            'ledgerData'    => $ledgerData,
        ]);
    }

    public function exportExpensePdf(Request $request)
    {
        $this->authorizeLedger();

        $expenseHeadId = $request->query('expense_head_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (!$expenseHeadId) {
            return back()->with('error', 'Select an expense category to export.');
        }

        $ledgerData = $this->getExpenseLedgerData($expenseHeadId, $dateFrom, $dateTo);
        
        $pdf = Pdf::loadView('ledgers.pdf', array_merge($ledgerData, [
            'type'      => 'expense',
            'dateFrom'  => $dateFrom,
            'dateTo'    => $dateTo,
            'title'     => 'Expense Head Ledger - ' . $ledgerData['head']->name,
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('expense_head_ledger_' . str_replace(' ', '_', strtolower($ledgerData['head']->name)) . '.pdf');
    }

    public function exportExpenseExcel(Request $request)
    {
        $this->authorizeLedger();

        $expenseHeadId = $request->query('expense_head_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (!$expenseHeadId) {
            return back()->with('error', 'Select an expense category to export.');
        }

        $ledgerData = $this->getExpenseLedgerData($expenseHeadId, $dateFrom, $dateTo);

        return Excel::download(
            new ExpenseLedgerExport($ledgerData['entries'], 'Expense Head Ledger - ' . $ledgerData['head']->name, $ledgerData['summary']),
            'expense_head_ledger_' . str_replace(' ', '_', strtolower($ledgerData['head']->name)) . '.xlsx'
        );
    }

    // -------------------------------------------------------------------------
    // Helper Data Fetchers
    // -------------------------------------------------------------------------

    private function getTenantLedgerData($unitId, $dateFrom, $dateTo)
    {
        $unit = Unit::with(['tenant', 'otherTenant'])->findOrFail($unitId);
        $entries = collect();

        // 1. Fetch Payments (Bills) as DEBITS
        $payments = Payment::where('unit_id', $unitId)
            ->with(['receivingVouchers', 'paymentAccount'])
            ->when($dateFrom, fn($q) => $q->where('month', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('month', '<=', $dateTo))
            ->orderBy('month', 'asc')
            ->get();

        foreach ($payments as $payment) {
            // Debit Entry: Bill Generated
            $entries->push([
                'date' => $payment->month,
                'description' => ucfirst(str_replace('_', ' ', $payment->type)) . ' Billing - ' . $payment->month->format('M Y'),
                'reference' => 'Bill #' . $payment->id,
                'debit' => (float)$payment->amount,
                'credit' => 0.00,
                'type' => 'bill',
                'id' => $payment->id,
            ]);

            // Credit Entries: Allocated Payments via Receiving Vouchers
            foreach ($payment->receivingVouchers as $voucher) {
                if ($dateFrom && $voucher->date->lt(Carbon::parse($dateFrom))) continue;
                if ($dateTo && $voucher->date->gt(Carbon::parse($dateTo))) continue;

                $entries->push([
                    'date' => $voucher->date,
                    'description' => 'Payment received via ' . ($voucher->paymentAccount->name ?? 'Voucher'),
                    'reference' => $voucher->voucher_no,
                    'debit' => 0.00,
                    'credit' => (float)$voucher->pivot->amount_allocated,
                    'type' => 'voucher',
                    'id' => $voucher->id,
                ]);
            }

            // Legacy/Direct payments check
            $voucheredPaid = $payment->receivingVouchers->sum(fn($v) => $v->pivot->amount_allocated);
            $unvoucheredPaid = (float)$payment->amount_paid - (float)$voucheredPaid;
            if ($unvoucheredPaid > 0.01) {
                $entries->push([
                    'date' => $payment->paid_at ?? $payment->month,
                    'description' => 'Payment received (Direct Pay)',
                    'reference' => 'Direct',
                    'debit' => 0.00,
                    'credit' => $unvoucheredPaid,
                    'type' => 'legacy_payment',
                    'id' => $payment->id,
                ]);
            }
        }

        // Sort all entries chronologically
        $entries = $entries->sortBy(function ($e) {
            $datePart = $e['date']->format('Y-m-d');
            $typePart = $e['type'] === 'bill' ? '0' : '1';
            return $datePart . '-' . $typePart;
        })->values();

        $runningBalance = 0.00;
        $totalInvoiced = 0.00;
        $totalPaid = 0.00;

        $entries = $entries->map(function ($entry) use (&$runningBalance, &$totalInvoiced, &$totalPaid) {
            $totalInvoiced += $entry['debit'];
            $totalPaid += $entry['credit'];
            $runningBalance += ($entry['debit'] - $entry['credit']);
            $entry['running_balance'] = $runningBalance;
            return $entry;
        });

        return [
            'unit' => $unit,
            'entries' => $entries,
            'summary' => [
                'total_invoiced' => $totalInvoiced,
                'total_paid' => $totalPaid,
                'balance_due' => max(0.00, $totalInvoiced - $totalPaid),
            ]
        ];
    }

    private function getOwnerLedgerData($ownerId, $dateFrom, $dateTo)
    {
        $owner = Owner::findOrFail($ownerId);
        $entries = collect();

        // 1. Outflows: Withdrawals as DEBITS
        $payouts = Withdrawal::where('owner_id', $ownerId)
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->get();

        foreach ($payouts as $payout) {
            $entries->push([
                'date' => $payout->date,
                'voucher_no' => $payout->voucher_no,
                'account' => $payout->paymentAccount->name ?? '—',
                'reference' => $payout->reference ?? '—',
                'notes' => $payout->notes ?? 'Owner Withdrawal',
                'debit' => (float)$payout->amount,
                'credit' => 0.00,
                'type' => 'withdrawal',
                'id' => $payout->id,
            ]);
        }

        // 2. Inflows: ReceivingVouchers (type = 'owner') as CREDITS
        $deposits = ReceivingVoucher::where('received_from_type', 'owner')
            ->where('owner_id', $ownerId)
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->get();

        foreach ($deposits as $deposit) {
            $entries->push([
                'date' => $deposit->date,
                'voucher_no' => $deposit->voucher_no,
                'account' => $deposit->paymentAccount->name ?? '—',
                'reference' => $deposit->reference ?? '—',
                'notes' => $deposit->notes ?? 'Capital Deposit',
                'debit' => 0.00,
                'credit' => (float)$deposit->amount,
                'type' => 'receiving_voucher',
                'id' => $deposit->id,
            ]);
        }

        // Sort chronologically
        $entries = $entries->sortBy(fn($e) => $e['date']->format('Y-m-d'))->values();

        $runningBalance = 0.00;
        $totalDebit = 0.00;
        $totalCredit = 0.00;

        $entries = $entries->map(function ($entry) use (&$runningBalance, &$totalDebit, &$totalCredit) {
            $totalDebit += $entry['debit'];
            $totalCredit += $entry['credit'];
            $runningBalance += ($entry['credit'] - $entry['debit']);
            $entry['running_balance'] = $runningBalance;
            return $entry;
        });

        return [
            'owner' => $owner,
            'entries' => $entries,
            'summary' => [
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
                'net_balance' => $runningBalance,
            ]
        ];
    }

    private function getAccountLedgerData($accountId, $dateFrom, $dateTo)
    {
        $account = PaymentAccount::findOrFail($accountId);
        $entries = collect();

        // 1. Calculate prior inflows and outflows to compute carried forward opening balance
        $priorInflowRVs = 0.00;
        $priorInflowGRVs = 0.00;
        $priorOutflowPVs = 0.00;
        $priorOutflowExpenses = 0.00;
        $priorOutflowWithdrawals = 0.00;

        if ($dateFrom) {
            $priorInflowRVs = (float) ReceivingVoucher::where('payment_account_id', $accountId)
                ->where('date', '<', $dateFrom)
                ->sum('amount');

            $priorInflowGRVs = (float) \App\Models\GeneralReceivingVoucher::where('payment_account_id', $accountId)
                ->where('date', '<', $dateFrom)
                ->sum('amount');

            $priorOutflowPVs = (float) PaymentVoucher::where('payment_account_id', $accountId)
                ->where('date', '<', $dateFrom)
                ->sum('amount');

            $priorOutflowExpenses = (float) Expense::where('payment_account_id', $accountId)
                ->where('date', '<', $dateFrom)
                ->sum('amount');

            $priorOutflowWithdrawals = (float) Withdrawal::where('payment_account_id', $accountId)
                ->where('date', '<', $dateFrom)
                ->sum('amount');
        }

        $carriedForwardBalance = (float) $account->opening_balance + $priorInflowRVs + $priorInflowGRVs - $priorOutflowPVs - $priorOutflowExpenses - $priorOutflowWithdrawals;

        // Prepend carried forward opening balance row
        $entries->push([
            'date' => $dateFrom ? Carbon::parse($dateFrom)->subDay() : ($account->created_at ?? Carbon::now()),
            'voucher_no' => '—',
            'type' => 'Opening Balance',
            'description' => $dateFrom ? 'Opening Balance (Carried Forward)' : 'Opening Balance',
            'debit' => $carriedForwardBalance >= 0 ? $carriedForwardBalance : 0.00,
            'credit' => $carriedForwardBalance < 0 ? abs($carriedForwardBalance) : 0.00,
            'is_opening' => true,
        ]);

        // 2. Credits (Inflows) in the selected period: ReceivingVouchers
        $receipts = ReceivingVoucher::where('payment_account_id', $accountId)
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->get();

        foreach ($receipts as $receipt) {
            $entries->push([
                'date' => $receipt->date,
                'voucher_no' => $receipt->voucher_no,
                'type' => 'Receipt',
                'description' => $receipt->notes ?? 'Received Payment',
                'debit' => (float)$receipt->amount, // Cash/Bank debit is inflow
                'credit' => 0.00,
                'model_type' => 'receiving_voucher',
                'model_id' => $receipt->id,
            ]);
        }

        // 2b. General Credits (Inflows) in the selected period: GeneralReceivingVouchers
        $generalReceipts = \App\Models\GeneralReceivingVoucher::with('party')
            ->where('payment_account_id', $accountId)
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->get();

        foreach ($generalReceipts as $receipt) {
            $desc = 'Party: ' . ($receipt->party ? $receipt->party->name : 'N/A');
            if ($receipt->notes) {
                $desc .= ' • ' . $receipt->notes;
            }
            $entries->push([
                'date' => $receipt->date,
                'voucher_no' => $receipt->voucher_no,
                'type' => 'Receipt (General)',
                'description' => $desc,
                'debit' => (float)$receipt->amount,
                'credit' => 0.00,
                'model_type' => 'general_receiving_voucher',
                'model_id' => $receipt->id,
            ]);
        }

        // 3. Debits (Outflows) in the selected period: PaymentVouchers
        $payouts = PaymentVoucher::where('payment_account_id', $accountId)
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->get();

        foreach ($payouts as $payout) {
            $entries->push([
                'date' => $payout->date,
                'voucher_no' => $payout->voucher_no,
                'type' => 'Payout',
                'description' => $payout->notes ?? 'Owner Payout / Advance',
                'debit' => 0.00,
                'credit' => (float)$payout->amount,
                'model_type' => 'payment_voucher',
                'model_id' => $payout->id,
            ]);
        }

        // 4. Debits (Outflows) in the selected period: Expense Vouchers
        $expenses = Expense::where('payment_account_id', $accountId)
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->get();

        foreach ($expenses as $expense) {
            $entries->push([
                'date' => $expense->date,
                'voucher_no' => $expense->voucher_no,
                'type' => 'Expense',
                'description' => ($expense->expenseHead->name ?? 'Expense') . ' - ' . ($expense->notes ?? ''),
                'debit' => 0.00,
                'credit' => (float)$expense->amount,
                'model_type' => 'expense',
                'model_id' => $expense->id,
            ]);
        }

        // 5. Debits (Outflows) in the selected period: Withdrawals
        $withdrawals = Withdrawal::where('payment_account_id', $accountId)
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->get();

        foreach ($withdrawals as $withdrawal) {
            $entries->push([
                'date' => $withdrawal->date,
                'voucher_no' => $withdrawal->voucher_no,
                'type' => 'Withdrawal',
                'description' => 'Owner Withdrawal: ' . ($withdrawal->owner->name ?? 'Partner') . ($withdrawal->notes ? ' - ' . $withdrawal->notes : ''),
                'debit' => 0.00,
                'credit' => (float)$withdrawal->amount,
                'model_type' => 'withdrawal',
                'model_id' => $withdrawal->id,
            ]);
        }

        // Sort chronologically, with opening balance row always first
        $entries = $entries->sortBy(function ($e) {
            return ($e['is_opening'] ?? false) ? '0000-00-00' : $e['date']->format('Y-m-d');
        })->values();

        $runningBalance = 0.00;
        $totalInflow = 0.00;
        $totalOutflow = 0.00;

        $entries = $entries->map(function ($entry) use (&$runningBalance, &$totalInflow, &$totalOutflow) {
            if (empty($entry['is_opening'])) {
                $totalInflow += $entry['debit'];
                $totalOutflow += $entry['credit'];
                $runningBalance += ($entry['debit'] - $entry['credit']);
            } else {
                $runningBalance = $entry['debit'] - $entry['credit'];
            }
            $entry['running_balance'] = $runningBalance;
            return $entry;
        });

        return [
            'account' => $account,
            'entries' => $entries,
            'summary' => [
                'total_inflow' => $totalInflow,
                'total_outflow' => $totalOutflow,
                'net_balance' => $runningBalance,
            ]
        ];
    }

    private function getExpenseLedgerData($expenseHeadId, $dateFrom, $dateTo)
    {
        $head = ExpenseHead::findOrFail($expenseHeadId);

        $expenses = Expense::where('expense_head_id', $expenseHeadId)
            ->with(['paymentAccount'])
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->orderBy('date', 'asc')
            ->get();

        $entries = $expenses->map(fn($e) => [
            'date' => $e->date,
            'voucher_no' => $e->voucher_no,
            'notes' => $e->notes ?? '—',
            'payment_account' => $e->paymentAccount->name ?? '—',
            'reference' => $e->reference ?? '—',
            'amount' => (float)$e->amount,
            'id' => $e->id,
        ]);

        $totalAmount = $entries->sum('amount');

        return [
            'head' => $head,
            'entries' => $entries,
            'summary' => [
                'total_amount' => $totalAmount,
            ]
        ];
    }

    private function authorizeLedger()
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('ledgers.view')) {
            abort(403, 'Unauthorized action.');
        }
    }

    // -----------------------------------------------------------------------
    // Print pages (open in new window)
    // -----------------------------------------------------------------------

    public function printTenant(Request $request): \Illuminate\View\View
    {
        $this->authorizeLedger();

        $unitId   = $request->query('unit_id');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        if (!$unitId) {
            abort(400, 'No unit selected.');
        }

        $ledgerData = $this->getTenantLedgerData($unitId, $dateFrom, $dateTo);
        $unit       = $ledgerData['unit'];
        $tenant     = $unit->tenant ?? $unit->otherTenant;

        $filterChips = [
            ['label' => 'Flat / Shop', 'value' => $unit->unit_number . ($tenant ? ' — ' . $tenant->name : '')],
        ];
        if ($dateFrom) $filterChips[] = ['label' => 'Date From', 'value' => \Carbon\Carbon::parse($dateFrom)->format('d M Y')];
        if ($dateTo)   $filterChips[] = ['label' => 'Date To',   'value' => \Carbon\Carbon::parse($dateTo)->format('d M Y')];

        $s = $ledgerData['summary'];
        $summaryCards = [
            ['label' => 'Total Billed / Charges',  'value' => 'Rs. ' . number_format($s['total_invoiced'], 2), 'color' => 's-blue'],
            ['label' => 'Total Paid / Credits',     'value' => 'Rs. ' . number_format($s['total_paid'], 2),    'color' => 's-green'],
            ['label' => 'Balance Outstanding',      'value' => 'Rs. ' . number_format($s['balance_due'], 2),   'color' => $s['balance_due'] > 0 ? 's-orange' : 's-neutral'],
        ];

        $columns = [
            ['key' => 'date',            'label' => 'Date',             'type' => 'date'],
            ['key' => 'description',     'label' => 'Description'],
            ['key' => 'reference',       'label' => 'Ref / Voucher #',  'td_class' => 'mono'],
            ['key' => 'debit',           'label' => 'Debit (Charged)',  'type' => 'debit',   'class' => 'text-right'],
            ['key' => 'credit',          'label' => 'Credit (Paid)',    'type' => 'credit',  'class' => 'text-right'],
            ['key' => 'running_balance', 'label' => 'Running Balance',  'type' => 'balance', 'class' => 'text-right'],
        ];

        return view('ledgers.print_page', [
            'pageTitle'    => 'Tenant / Unit Ledger — ' . $unit->unit_number,
            'filterChips'  => $filterChips,
            'summaryCards' => $summaryCards,
            'columns'      => $columns,
            'rows'         => $ledgerData['entries']->toArray(),
        ]);
    }

    public function printOwner(Request $request): \Illuminate\View\View
    {
        $this->authorizeLedger();

        $ownerId  = $request->query('owner_id');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        if (!$ownerId) {
            abort(400, 'No owner selected.');
        }

        $ledgerData = $this->getOwnerLedgerData($ownerId, $dateFrom, $dateTo);
        $owner      = $ledgerData['owner'];

        $filterChips = [
            ['label' => 'Owner', 'value' => $owner->name . ($owner->email ? ' (' . $owner->email . ')' : '')],
        ];
        if ($dateFrom) $filterChips[] = ['label' => 'Date From', 'value' => \Carbon\Carbon::parse($dateFrom)->format('d M Y')];
        if ($dateTo)   $filterChips[] = ['label' => 'Date To',   'value' => \Carbon\Carbon::parse($dateTo)->format('d M Y')];

        $s = $ledgerData['summary'];
        $summaryCards = [
            ['label' => 'Total Payouts (Debits)',    'value' => 'Rs. ' . number_format($s['total_debit'], 2),  'color' => 's-blue'],
            ['label' => 'Total Deposits (Credits)',  'value' => 'Rs. ' . number_format($s['total_credit'], 2), 'color' => 's-green'],
            ['label' => 'Net Business Balance',      'value' => 'Rs. ' . number_format($s['net_balance'], 2),  'color' => 's-neutral'],
        ];

        $columns = [
            ['key' => 'date',            'label' => 'Date',            'type' => 'date'],
            ['key' => 'voucher_no',      'label' => 'Voucher #',       'td_class' => 'mono'],
            ['key' => 'account',         'label' => 'Account'],
            ['key' => 'reference',       'label' => 'Reference',       'td_class' => 'mono'],
            ['key' => 'notes',           'label' => 'Notes'],
            ['key' => 'debit',           'label' => 'Debit (Payout)',  'type' => 'debit',   'class' => 'text-right'],
            ['key' => 'credit',          'label' => 'Credit (Deposit)','type' => 'credit',  'class' => 'text-right'],
            ['key' => 'running_balance', 'label' => 'Running Balance', 'type' => 'balance', 'class' => 'text-right'],
        ];

        return view('ledgers.print_page', [
            'pageTitle'    => 'Owner Capital Statement — ' . $owner->name,
            'filterChips'  => $filterChips,
            'summaryCards' => $summaryCards,
            'columns'      => $columns,
            'rows'         => $ledgerData['entries']->toArray(),
        ]);
    }

    public function printAccount(Request $request): \Illuminate\View\View
    {
        $this->authorizeLedger();

        $accountId = $request->query('payment_account_id');
        $dateFrom  = $request->query('date_from');
        $dateTo    = $request->query('date_to');

        if (!$accountId) {
            abort(400, 'No account selected.');
        }

        $ledgerData = $this->getAccountLedgerData($accountId, $dateFrom, $dateTo);
        $account    = $ledgerData['account'];

        $filterChips = [
            ['label' => 'Account', 'value' => $account->name . ' (' . ucfirst($account->type) . ')'],
        ];
        if ($dateFrom) $filterChips[] = ['label' => 'Date From', 'value' => \Carbon\Carbon::parse($dateFrom)->format('d M Y')];
        if ($dateTo)   $filterChips[] = ['label' => 'Date To',   'value' => \Carbon\Carbon::parse($dateTo)->format('d M Y')];

        $s = $ledgerData['summary'];
        $summaryCards = [
            ['label' => 'Total Inflows (Debits)',   'value' => 'Rs. ' . number_format($s['total_inflow'], 2),  'color' => 's-green'],
            ['label' => 'Total Outflows (Credits)', 'value' => 'Rs. ' . number_format($s['total_outflow'], 2), 'color' => 's-blue'],
            ['label' => 'Account Running Balance',  'value' => 'Rs. ' . number_format($s['net_balance'], 2),   'color' => 's-neutral'],
        ];

        $columns = [
            ['key' => 'date',            'label' => 'Date',              'type' => 'date'],
            ['key' => 'voucher_no',      'label' => 'Voucher #',         'td_class' => 'mono'],
            ['key' => 'type',            'label' => 'Type',              'type' => 'badge'],
            ['key' => 'description',     'label' => 'Description / Ref'],
            ['key' => 'debit',           'label' => 'Debit (Inflow)',    'type' => 'debit',   'class' => 'text-right'],
            ['key' => 'credit',          'label' => 'Credit (Outflow)',  'type' => 'credit',  'class' => 'text-right'],
            ['key' => 'running_balance', 'label' => 'Running Balance',   'type' => 'balance', 'class' => 'text-right'],
        ];

        return view('ledgers.print_page', [
            'pageTitle'    => 'Cash & Bank Ledger — ' . $account->name,
            'filterChips'  => $filterChips,
            'summaryCards' => $summaryCards,
            'columns'      => $columns,
            'rows'         => $ledgerData['entries']->toArray(),
        ]);
    }

    public function printExpense(Request $request): \Illuminate\View\View
    {
        $this->authorizeLedger();

        $expenseHeadId = $request->query('expense_head_id');
        $dateFrom      = $request->query('date_from');
        $dateTo        = $request->query('date_to');

        if (!$expenseHeadId) {
            abort(400, 'No expense category selected.');
        }

        $ledgerData = $this->getExpenseLedgerData($expenseHeadId, $dateFrom, $dateTo);
        $head       = $ledgerData['head'];

        $filterChips = [
            ['label' => 'Expense Category', 'value' => $head->name . ($head->code ? ' (Code: ' . $head->code . ')' : '')],
        ];
        if ($dateFrom) $filterChips[] = ['label' => 'Date From', 'value' => \Carbon\Carbon::parse($dateFrom)->format('d M Y')];
        if ($dateTo)   $filterChips[] = ['label' => 'Date To',   'value' => \Carbon\Carbon::parse($dateTo)->format('d M Y')];

        $s = $ledgerData['summary'];
        $summaryCards = [
            ['label' => 'Total Spent Under Head', 'value' => 'Rs. ' . number_format($s['total_amount'], 2), 'color' => 's-amber'],
        ];

        $columns = [
            ['key' => 'date',            'label' => 'Date',            'type' => 'date'],
            ['key' => 'voucher_no',      'label' => 'Voucher #',       'td_class' => 'mono'],
            ['key' => 'notes',           'label' => 'Spent On / Notes'],
            ['key' => 'payment_account', 'label' => 'Payment Account'],
            ['key' => 'reference',       'label' => 'Reference',       'td_class' => 'mono'],
            ['key' => 'amount',          'label' => 'Amount',          'type' => 'amount', 'class' => 'text-right'],
        ];

        return view('ledgers.print_page', [
            'pageTitle'    => 'Expense Ledger — ' . $head->name,
            'filterChips'  => $filterChips,
            'summaryCards' => $summaryCards,
            'columns'      => $columns,
            'rows'         => $ledgerData['entries']->toArray(),
        ]);
    }
}

