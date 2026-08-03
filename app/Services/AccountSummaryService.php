<?php

namespace App\Services;

use App\Models\PaymentAccount;
use App\Models\ExpenseHead;
use App\Models\Owner;
use App\Models\Unit;
use App\Models\ReceivingVoucher;
use App\Models\GeneralReceivingVoucher;
use App\Models\PaymentVoucher;
use App\Models\Expense;
use App\Models\Withdrawal;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountSummaryService
{
    /**
     * Get summary for all account types.
     */
    public function getSummary($dateFrom, $dateTo, $type = 'all')
    {
        $summary = collect();

        if ($type === 'all' || $type === 'asset') {
            $summary = $summary->concat($this->getAssetsSummary($dateFrom, $dateTo));
        }
        
        if ($type === 'all' || $type === 'liability') {
            $summary = $summary->concat($this->getLiabilitiesSummary($dateFrom, $dateTo));
        }

        if ($type === 'all' || $type === 'receivable') {
            $summary = $summary->concat($this->getReceivablesSummary($dateFrom, $dateTo));
        }
        
        if ($type === 'all' || $type === 'expense') {
            $summary = $summary->concat($this->getExpensesSummary($dateFrom, $dateTo));
        }

        return $summary;
    }

    private function getAssetsSummary($dateFrom, $dateTo)
    {
        $results = collect();
        $accounts = PaymentAccount::where('is_active', true)->get();

        foreach ($accounts as $account) {
            $opening = (float)$account->opening_balance;
            
            // Prior Period
            $priorRvIn = $dateFrom ? (float)ReceivingVoucher::where('payment_account_id', $account->id)->where('date', '<', $dateFrom)->sum('amount') : 0;
            $priorGrvIn = $dateFrom ? (float)GeneralReceivingVoucher::where('payment_account_id', $account->id)->where('date', '<', $dateFrom)->sum('amount') : 0;
            $priorPvIn = $dateFrom ? (float)PaymentVoucher::where('to_payment_account_id', $account->id)->where('date', '<', $dateFrom)->sum('amount') : 0;
            
            $priorPvOut = $dateFrom ? (float)PaymentVoucher::where('payment_account_id', $account->id)->where('date', '<', $dateFrom)->sum('amount') : 0;
            $priorGrvOut = $dateFrom ? (float)GeneralReceivingVoucher::where('from_payment_account_id', $account->id)->where('date', '<', $dateFrom)->sum('amount') : 0;
            $priorExpOut = $dateFrom ? (float)Expense::where('payment_account_id', $account->id)->where('date', '<', $dateFrom)->sum('amount')
                + (float)\App\Models\JvVoucher::where('status', 'paid')->where('payment_account_id', $account->id)->where('paid_date', '<', $dateFrom)->sum('amount') : 0;
            $priorWithOut = $dateFrom ? (float)Withdrawal::where('payment_account_id', $account->id)->where('date', '<', $dateFrom)->sum('amount') : 0;
            
            $openingBalance = $opening + ($priorRvIn + $priorGrvIn + $priorPvIn) - ($priorPvOut + $priorGrvOut + $priorExpOut + $priorWithOut);

            // Current Period Debits (Inflows for Bank/Cash)
            $rvIn = (float)ReceivingVoucher::where('payment_account_id', $account->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');
            $grvIn = (float)GeneralReceivingVoucher::where('payment_account_id', $account->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');
            $pvIn = (float)PaymentVoucher::where('to_payment_account_id', $account->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');
            
            $totalDebit = $rvIn + $grvIn + $pvIn;

            // Current Period Credits (Outflows for Bank/Cash)
            $pvOut = (float)PaymentVoucher::where('payment_account_id', $account->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');
            $grvOut = (float)GeneralReceivingVoucher::where('from_payment_account_id', $account->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');
            $expOut = (float)Expense::where('payment_account_id', $account->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount')
                + (float)\App\Models\JvVoucher::where('status', 'paid')->where('payment_account_id', $account->id)
                ->when($dateFrom, fn($q) => $q->where('paid_date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('paid_date', '<=', $dateTo))->sum('amount');
            $withOut = (float)Withdrawal::where('payment_account_id', $account->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');

            $totalCredit = $pvOut + $grvOut + $expOut + $withOut;
            $closingBalance = $openingBalance + $totalDebit - $totalCredit;

            $results->push([
                'id' => $account->id,
                'name' => $account->name,
                'type' => 'Asset (Bank/Cash)',
                'group' => 'asset',
                'opening' => $openingBalance,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'closing' => $closingBalance,
                'url' => route('ledgers.payment-account', ['payment_account_id' => $account->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]),
            ]);
        }

        return $results;
    }

    private function getLiabilitiesSummary($dateFrom, $dateTo)
    {
        $results = collect();
        $owners = Owner::orderBy('name')->get();
        
        $dateFromStr = $dateFrom ?: Carbon::now()->startOfMonth()->toDateString();
        $dateToStr   = $dateTo ?: Carbon::now()->endOfMonth()->toDateString();

        foreach ($owners as $owner) {
            // Prior (Opening Balance)
            $priorToDate = Carbon::parse($dateFromStr)->subDay()->toDateString();
            $priorProfit = $this->calculateMallNetProfit('1970-01-01', $priorToDate);
            $priorShare = round($priorProfit * ((float) $owner->partnership_percentage / 100), 2);

            $priorDeposits = (float) ReceivingVoucher::where('received_from_type', 'owner')
                ->where('owner_id', $owner->id)->where('date', '<', $dateFromStr)->sum('amount');
            $priorPvPayouts = (float) PaymentVoucher::where('paid_to_type', 'owner')
                ->where('owner_id', $owner->id)->where('date', '<', $dateFromStr)->sum('amount');
            $priorWithdrawals = (float) Withdrawal::where('owner_id', $owner->id)
                ->where('date', '<', $dateFromStr)->sum('amount');

            // For liability/equity, credit is positive balance
            $openingBalance = ($priorShare + $priorDeposits) - ($priorPvPayouts + $priorWithdrawals);

            // Current Period
            $periodProfit = $this->calculateMallNetProfit($dateFromStr, $dateToStr);
            $periodShare = round($periodProfit * ((float) $owner->partnership_percentage / 100), 2);

            $deposits = (float) ReceivingVoucher::where('received_from_type', 'owner')
                ->where('owner_id', $owner->id)->whereBetween('date', [$dateFromStr, $dateToStr])->sum('amount');
            
            $totalCredit = $periodShare + $deposits; // Increases Equity

            $pvPayouts = (float) PaymentVoucher::where('paid_to_type', 'owner')
                ->where('owner_id', $owner->id)->whereBetween('date', [$dateFromStr, $dateToStr])->sum('amount');
            $withdrawals = (float) Withdrawal::where('owner_id', $owner->id)
                ->whereBetween('date', [$dateFromStr, $dateToStr])->sum('amount');

            $totalDebit = $pvPayouts + $withdrawals; // Decreases Equity
            
            $closingBalance = $openingBalance + $totalCredit - $totalDebit;

            $results->push([
                'id' => $owner->id,
                'name' => $owner->name,
                'type' => 'Equity (Owner)',
                'group' => 'liability',
                'opening' => $openingBalance,
                'debit' => $totalDebit,    // Withdrawals
                'credit' => $totalCredit,  // Profit Share + Deposits
                'closing' => $closingBalance,
                'url' => route('ledgers.owner', ['owner_id' => $owner->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]),
            ]);
        }

        return $results;
    }

    private function getReceivablesSummary($dateFrom, $dateTo)
    {
        $results = collect();
        $units = Unit::with(['tenant', 'otherTenant'])->orderBy('unit_number')->get();

        foreach ($units as $unit) {
            $unitId = $unit->id;

            // Prior Period
            $priorInvoiced = 0;
            $priorPaid = 0;
            
            if ($dateFrom) {
                $priorPayments = Payment::where('unit_id', $unitId)
                    ->where('type', '!=', 'security_deposit')
                    ->where('month', '<', $dateFrom)->get();
                $priorInvoiced = $priorPayments->sum('amount');
                
                $priorLegacyPaid = $priorPayments->sum(function($p) {
                    $vouchered = $p->receivingVouchers->sum(fn($v) => $v->pivot->amount_allocated);
                    return max(0, (float)$p->amount_paid - (float)$vouchered);
                });

                $priorReceivingVouchers = ReceivingVoucher::where(function ($q) use ($unitId, $unit) {
                        $q->whereHas('payments', fn($qp) => $qp->where('unit_id', $unitId));
                        if ($unit->tenant_id) {
                            $q->orWhere('tenant_id', $unit->tenant_id);
                        }
                    })
                    ->where('date', '<', $dateFrom)
                    ->with('payments')
                    ->get()->unique('id');

                $priorVoucherPaid = $priorReceivingVouchers->sum(function($voucher) use ($unitId) {
                    $allocatedForUnit = $voucher->payments->where('unit_id', $unitId)->sum(fn($p) => (float)$p->pivot->amount_allocated);
                    return $allocatedForUnit > 0 ? $allocatedForUnit : (float)$voucher->amount;
                });

                $priorPayouts = PaymentVoucher::where('paid_to_type', 'tenant')
                    ->where(function($q) use ($unitId, $unit) {
                        $q->where('unit_id', $unitId);
                        if ($unit->tenant_id) $q->orWhere('tenant_id', $unit->tenant_id);
                    })->where('date', '<', $dateFrom)->sum('amount');

                $priorInvoiced += $priorPayouts; // Refunds increase balance due
                $priorPaid = $priorLegacyPaid + $priorVoucherPaid;
            }

            $openingBalance = $priorInvoiced - $priorPaid;

            // Current Period
            $periodPayments = Payment::where('unit_id', $unitId)
                ->where('type', '!=', 'security_deposit')
                ->when($dateFrom, fn($q) => $q->where('month', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('month', '<=', $dateTo))->get();
            
            $invoiced = $periodPayments->sum('amount');
            
            $legacyPaid = $periodPayments->sum(function($p) {
                $vouchered = $p->receivingVouchers->sum(fn($v) => $v->pivot->amount_allocated);
                return max(0, (float)$p->amount_paid - (float)$vouchered);
            });

            $periodVouchers = ReceivingVoucher::where(function ($q) use ($unitId, $unit) {
                    $q->whereHas('payments', fn($qp) => $qp->where('unit_id', $unitId));
                    if ($unit->tenant_id) {
                        $q->orWhere('tenant_id', $unit->tenant_id);
                    }
                })
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
                ->with('payments')
                ->get()->unique('id');

            $voucherPaid = $periodVouchers->sum(function($voucher) use ($unitId) {
                $allocatedForUnit = $voucher->payments->where('unit_id', $unitId)->sum(fn($p) => (float)$p->pivot->amount_allocated);
                return $allocatedForUnit > 0 ? $allocatedForUnit : (float)$voucher->amount;
            });

            $payouts = PaymentVoucher::where('paid_to_type', 'tenant')
                ->where(function($q) use ($unitId, $unit) {
                    $q->where('unit_id', $unitId);
                    if ($unit->tenant_id) $q->orWhere('tenant_id', $unit->tenant_id);
                })
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
                ->sum('amount');

            $totalDebit = $invoiced + $payouts;
            $totalCredit = $legacyPaid + $voucherPaid;
            $closingBalance = $openingBalance + $totalDebit - $totalCredit;

            $results->push([
                'id' => $unit->id,
                'name' => 'Unit ' . $unit->unit_number . ($unit->tenant ? ' (' . $unit->tenant->name . ')' : ''),
                'type' => 'Receivable (Tenant)',
                'group' => 'receivable',
                'opening' => $openingBalance,
                'debit' => $totalDebit,    // Invoices/Bills
                'credit' => $totalCredit,  // Receipts
                'closing' => $closingBalance,
                'url' => route('ledgers.tenant', ['unit_id' => $unit->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]),
            ]);
        }

        return $results;
    }

    private function getExpensesSummary($dateFrom, $dateTo)
    {
        $results = collect();
        $heads = ExpenseHead::orderBy('name')->get();

        foreach ($heads as $head) {
            $openingBalance = $dateFrom ? (float)Expense::where('expense_head_id', $head->id)
                ->where('date', '<', $dateFrom)->sum('amount')
                + (float)\App\Models\JvVoucher::where('expense_head_id', $head->id)
                ->where('date', '<', $dateFrom)->sum('amount') : 0;

            $totalDebit = (float)Expense::where('expense_head_id', $head->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount')
                + (float)\App\Models\JvVoucher::where('expense_head_id', $head->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');

            $totalCredit = 0.00; // Expenses typically don't have credits in this system unless refunded, but currently unhandled in this system's expense ledger.
            $closingBalance = $openingBalance + $totalDebit - $totalCredit;

            $results->push([
                'id' => $head->id,
                'name' => $head->name,
                'type' => 'Expense',
                'group' => 'expense',
                'opening' => $openingBalance,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'closing' => $closingBalance,
                'url' => route('ledgers.expense', ['expense_head_id' => $head->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]),
            ]);
        }

        return $results;
    }

    private function calculateMallNetProfit(string $from, string $to): float
    {
        if (Carbon::parse($from)->gt(Carbon::parse($to))) {
            return 0.00;
        }

        $otherTenantUnitIds = DB::table('other_tenants')->pluck('unit_id')->toArray();

        // 1. Allocations
        $allocations = DB::table('receiving_voucher_payments')
            ->join('payments', 'receiving_voucher_payments.payment_id', '=', 'payments.id')
            ->join('units', 'payments.unit_id', '=', 'units.id')
            ->join('receiving_vouchers', 'receiving_voucher_payments.receiving_voucher_id', '=', 'receiving_vouchers.id')
            ->whereNull('receiving_vouchers.deleted_at')
            ->whereNull('payments.deleted_at')
            ->whereBetween('payments.month', [$from, $to])
            ->where('payments.type', '!=', 'security_deposit')
            ->select('payments.unit_id', 'units.is_self', 'payments.type', DB::raw('SUM(receiving_voucher_payments.amount_allocated) as total'))
            ->groupBy('payments.unit_id', 'units.is_self', 'payments.type')
            ->get();

        $allocRentPmMall      = (float) $allocations->where('is_self', false)->where('type', 'rent')->sum('total');
        $allocMaintPmMall     = (float) $allocations->filter(function ($row) use ($otherTenantUnitIds) {
            return $row->type === 'maintenance' && (!$row->is_self || in_array($row->unit_id, $otherTenantUnitIds));
        })->sum('total');
        $allocMaintOtherOwned = (float) $allocations->filter(function ($row) use ($otherTenantUnitIds) {
            return $row->type === 'maintenance' && $row->is_self && !in_array($row->unit_id, $otherTenantUnitIds);
        })->sum('total');
        $allocExtraPmMall     = (float) $allocations->where('is_self', false)->whereNotIn('type', ['rent', 'maintenance', 'security_deposit'])->sum('total');

        // 2. Month Payments (Paid)
        $monthPayments = DB::table('payments')
            ->join('units', 'payments.unit_id', '=', 'units.id')
            ->whereNull('payments.deleted_at')
            ->whereBetween('payments.month', [$from, $to])
            ->where('payments.type', '!=', 'security_deposit')
            ->select('payments.unit_id', 'units.is_self', 'payments.type', DB::raw('SUM(payments.amount_paid) as total_paid'))
            ->groupBy('payments.unit_id', 'units.is_self', 'payments.type')
            ->get();

        $payRentPmMall      = (float) $monthPayments->where('is_self', false)->where('type', 'rent')->sum('total_paid');
        $payMaintPmMall     = (float) $monthPayments->filter(function ($row) use ($otherTenantUnitIds) {
            return $row->type === 'maintenance' && (!$row->is_self || in_array($row->unit_id, $otherTenantUnitIds));
        })->sum('total_paid');
        $payMaintOtherOwned = (float) $monthPayments->filter(function ($row) use ($otherTenantUnitIds) {
            return $row->type === 'maintenance' && $row->is_self && !in_array($row->unit_id, $otherTenantUnitIds);
        })->sum('total_paid');
        $payExtraPmMall     = (float) $monthPayments->where('is_self', false)->whereNotIn('type', ['rent', 'maintenance', 'security_deposit'])->sum('total_paid');

        $rentPmMall      = max($allocRentPmMall, $payRentPmMall);
        $maintPmMall     = max($allocMaintPmMall, $payMaintPmMall);
        $maintOtherOwned = max($allocMaintOtherOwned, $payMaintOtherOwned);
        $extraPmMall     = max($allocExtraPmMall, $payExtraPmMall);

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

        $totalIncome = $rentPmMall + $maintPmMall + $maintOtherOwned + $extraPmMall + $unallocatedTenantIncome;
        $totalExpenses = (float) Expense::whereBetween('date', [$from, $to])->sum('amount')
            + (float) \App\Models\JvVoucher::whereBetween('date', [$from, $to])->sum('amount');

        return $totalIncome - $totalExpenses;
    }
}
