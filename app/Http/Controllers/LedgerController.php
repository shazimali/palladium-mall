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

        // 1. Outflows: PaymentVouchers as DEBITS
        $payouts = PaymentVoucher::where('owner_id', $ownerId)
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->get();

        foreach ($payouts as $payout) {
            $entries->push([
                'date' => $payout->date,
                'voucher_no' => $payout->voucher_no,
                'account' => $payout->paymentAccount->name ?? '—',
                'reference' => $payout->reference ?? '—',
                'notes' => $payout->notes ?? 'Owner Payout',
                'debit' => (float)$payout->amount,
                'credit' => 0.00,
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

        // 1. Credits (Inflows): ReceivingVouchers
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
            ]);
        }

        // 2. Debits (Outflows): PaymentVouchers
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
            ]);
        }

        // 3. Debits (Outflows): Expense Vouchers
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
            ]);
        }

        // Sort chronologically
        $entries = $entries->sortBy(fn($e) => $e['date']->format('Y-m-d'))->values();

        $runningBalance = 0.00;
        $totalInflow = 0.00;
        $totalOutflow = 0.00;

        $entries = $entries->map(function ($entry) use (&$runningBalance, &$totalInflow, &$totalOutflow) {
            $totalInflow += $entry['debit'];
            $totalOutflow += $entry['credit'];
            $runningBalance += ($entry['debit'] - $entry['credit']);
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
}
