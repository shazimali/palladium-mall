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
        $detailed = collect();
        $categoryKeys = [];

        if ($type === 'all' || $type === 'asset') {
            $detailed = $detailed->concat($this->getAssetsSummary($dateFrom, $dateTo));
            $categoryKeys[] = 'asset_cash';
            $categoryKeys[] = 'asset_bank';
        }

        if ($type === 'all' || $type === 'liability') {
            $detailed = $detailed->concat($this->getLiabilitiesSummary($dateFrom, $dateTo));
            $categoryKeys[] = 'liability';
        }

        if ($type === 'all' || $type === 'receivable') {
            $detailed = $detailed->concat($this->getReceivablesSummary($dateFrom, $dateTo));
            $categoryKeys[] = 'receivable';

            $detailed = $detailed->concat($this->getTenantSecurityDepositsSummary($dateFrom, $dateTo));
            $categoryKeys[] = 'tenant_security_deposit';
        }

        if ($type === 'all' || $type === 'expense') {
            $detailed = $detailed->concat($this->getExpensesSummary($dateFrom, $dateTo));
            $categoryKeys[] = 'expense';
        }

        if ($type === 'all' || $type === 'landlord_payable') {
            $detailed = $detailed->concat($this->getLandlordPayablesSummary($dateFrom, $dateTo));
            $categoryKeys[] = 'landlord_payable';
        }

        if ($type === 'all' || $type === 'party_due') {
            $detailed = $detailed->concat($this->getPartyDuesSummary($dateFrom, $dateTo));
            $categoryKeys[] = 'party_due';
        }

        if ($type === 'all' || $type === 'jv_payable') {
            $detailed = $detailed->concat($this->getJvPayablesSummary($dateFrom, $dateTo));
            $categoryKeys[] = 'jv_payable';
        }

        return $this->collapseToCategories($detailed, $categoryKeys);
    }

    /**
     * Collapse the per-entity rows produced by the get*Summary() methods into
     * one summary row per meaningful account category (two for assets: Cash vs Bank).
     */
    private function collapseToCategories($detailed, array $categoryKeys)
    {
        $labels = [
            'asset_cash'       => 'Cash',
            'asset_bank'       => 'Bank',
            'liability'        => 'Equity & Liabilities (Owners)',
            'receivable'       => 'Tenants',
            'tenant_security_deposit' => 'Tenant Security Deposits',
            'expense'          => 'Expenses',
            'landlord_payable' => 'Landlord Payables',
            'party_due'        => 'Party Dues',
            'jv_payable'       => 'JV Payables',
        ];

        $byKey = $detailed->groupBy(function ($row) {
            return $row['group'] === 'asset' ? 'asset_' . ($row['subtype'] ?? 'bank') : $row['group'];
        });

        $results = collect();
        foreach ($categoryKeys as $key) {
            $entries = $byKey->get($key, collect());
            $results->push([
                'id'      => $key,
                'name'    => $labels[$key],
                'type'    => $labels[$key],
                'group'   => str_starts_with($key, 'asset_') ? 'asset' : $key,
                'opening'    => $entries->sum('opening'),
                'debit'      => $entries->sum('debit'),
                'credit'     => $entries->sum('credit'),
                'closing'    => $entries->sum('closing'),
                'receivable' => $entries->sum(fn($e) => $e['receivable'] ?? max(0, $e['closing'])),
                'payable'    => $entries->sum(fn($e) => $e['payable'] ?? max(0, -$e['closing'])),
                'url'        => null,
            ]);
        }

        return $results;
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
                'subtype' => $account->id == 2 ? 'cash' : 'bank',
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

    /**
     * Tenant security deposits, shown separately from rent/maintenance dues.
     * Pending (uncollected) deposits are a receivable; once collected they become
     * a payable (held on behalf of the tenant until refunded on move-out).
     */
    private function getTenantSecurityDepositsSummary($dateFrom, $dateTo)
    {
        $results = collect();

        $unitIds = Payment::where('type', 'security_deposit')->distinct()->pluck('unit_id');
        $units = Unit::with(['tenant', 'otherTenant'])->whereIn('id', $unitIds)->orderBy('unit_number')->get();

        foreach ($units as $unit) {
            $unitId = $unit->id;

            // Prior Period
            $priorDepositInvoiced = 0.0;
            $priorDepositPaid = 0.0;
            $priorDepositRefunded = 0.0;

            if ($dateFrom) {
                $priorDepositInvoiced = (float) Payment::where('unit_id', $unitId)
                    ->where('type', 'security_deposit')
                    ->where('month', '<', $dateFrom)->sum('amount');

                $priorDepositPaid = (float) Payment::where('unit_id', $unitId)
                    ->where('type', 'security_deposit')
                    ->where(function ($q) use ($dateFrom) {
                        $q->whereNull('paid_at')->where('month', '<', $dateFrom)
                          ->orWhere('paid_at', '<', $dateFrom);
                    })->sum('amount_paid');

                $priorDepositRefunded = (float) PaymentVoucher::where('paid_to_type', 'tenant')
                    ->where(function ($q) use ($unitId, $unit) {
                        $q->where('unit_id', $unitId);
                        if ($unit->tenant_id) $q->orWhere('tenant_id', $unit->tenant_id);
                    })->where('date', '<', $dateFrom)->sum('amount');
            }

            $openingPendingDeposit = $priorDepositInvoiced - $priorDepositPaid;
            $openingHeldDeposit = $priorDepositPaid - $priorDepositRefunded;

            // Current Period
            $periodDepositInvoiced = (float) Payment::where('unit_id', $unitId)
                ->where('type', 'security_deposit')
                ->when($dateFrom, fn($q) => $q->where('month', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('month', '<=', $dateTo))->sum('amount');

            $periodDepositPaid = (float) Payment::where('unit_id', $unitId)
                ->where('type', 'security_deposit')
                ->where(function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) {
                        $q->where(fn($sub) => $sub->whereNull('paid_at')->where('month', '>=', $dateFrom)->orWhere('paid_at', '>=', $dateFrom));
                    }
                    if ($dateTo) {
                        $q->where(fn($sub) => $sub->whereNull('paid_at')->where('month', '<=', $dateTo)->orWhere('paid_at', '<=', $dateTo));
                    }
                })->sum('amount_paid');

            $periodDepositRefunded = (float) PaymentVoucher::where('paid_to_type', 'tenant')
                ->where(function ($q) use ($unitId, $unit) {
                    $q->where('unit_id', $unitId);
                    if ($unit->tenant_id) $q->orWhere('tenant_id', $unit->tenant_id);
                })
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');

            $pendingDeposit = $openingPendingDeposit + $periodDepositInvoiced - $periodDepositPaid;
            $heldDeposit = $openingHeldDeposit + $periodDepositPaid - $periodDepositRefunded;

            $entryReceivable = max(0, $pendingDeposit);
            $entryPayable = max(0, $heldDeposit);

            if ($entryReceivable == 0 && $entryPayable == 0) {
                continue;
            }

            $tenantName = $unit->tenant->name ?? $unit->otherTenant->name ?? null;

            $results->push([
                'id' => $unit->id,
                'name' => 'Unit ' . $unit->unit_number . ($tenantName ? ' (' . $tenantName . ')' : ''),
                'type' => 'Tenant Security Deposit',
                'group' => 'tenant_security_deposit',
                'opening' => $openingPendingDeposit - $openingHeldDeposit,
                'debit' => $periodDepositInvoiced,
                'credit' => $periodDepositPaid + $periodDepositRefunded,
                'closing' => $entryReceivable - $entryPayable,
                'receivable' => $entryReceivable,
                'payable' => $entryPayable,
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

    private function getLandlordPayablesSummary($dateFrom, $dateTo)
    {
        $results = collect();
        $landlords = \App\Models\Landlord::with('ownerships')->orderBy('name')->get();

        foreach ($landlords as $landlord) {
            $unitValueOwed = (float) $landlord->ownerships->sum('credit_amount');

            // Prior Period
            $priorPaid = 0.0;
            $priorCharged = 0.0;
            if ($dateFrom) {
                $priorPaid += (float) ReceivingVoucher::where('received_from_type', 'owner')
                    ->where('owner_id', $landlord->id)->where('date', '<', $dateFrom)->sum('amount');
                $priorPaid += (float) GeneralReceivingVoucher::where('landlord_id', $landlord->id)
                    ->where('date', '<', $dateFrom)->sum('amount');
                $priorPaid += (float) Payment::where('landlord_id', $landlord->id)
                    ->where('type', 'extra_payment')->where('amount_paid', '>', 0)
                    ->where(function ($q) use ($dateFrom) {
                        $q->whereNull('paid_at')->where('month', '<', $dateFrom)
                          ->orWhere('paid_at', '<', $dateFrom);
                    })->sum('amount_paid');

                $priorCharged += (float) PaymentVoucher::where('paid_to_type', 'landlord')
                    ->where('landlord_id', $landlord->id)->where('date', '<', $dateFrom)->sum('amount');
                $priorCharged += (float) \App\Models\OtherOwnedRentPurchaseVoucher::where('landlord_id', $landlord->id)
                    ->where('date', '<', $dateFrom)->sum('amount');
            }

            $openingBalance = $unitValueOwed - $priorPaid + $priorCharged;

            // Current Period Credits (payments received)
            $rvPaid = (float) ReceivingVoucher::where('received_from_type', 'owner')
                ->where('owner_id', $landlord->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');
            $grvPaid = (float) GeneralReceivingVoucher::where('landlord_id', $landlord->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');
            $extraPaid = (float) Payment::where('landlord_id', $landlord->id)
                ->where('type', 'extra_payment')->where('amount_paid', '>', 0)
                ->where(function ($q) use ($dateFrom, $dateTo) {
                    if ($dateFrom) {
                        $q->where(fn($sub) => $sub->whereNull('paid_at')->where('month', '>=', $dateFrom)->orWhere('paid_at', '>=', $dateFrom));
                    }
                    if ($dateTo) {
                        $q->where(fn($sub) => $sub->whereNull('paid_at')->where('month', '<=', $dateTo)->orWhere('paid_at', '<=', $dateTo));
                    }
                })->sum('amount_paid');

            $totalCredit = $rvPaid + $grvPaid + $extraPaid;

            // Current Period Debits (payouts / rent purchases)
            $payouts = (float) PaymentVoucher::where('paid_to_type', 'landlord')
                ->where('landlord_id', $landlord->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');
            $orp = (float) \App\Models\OtherOwnedRentPurchaseVoucher::where('landlord_id', $landlord->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');

            $totalDebit = $payouts + $orp;
            $closingBalance = $openingBalance + $totalDebit - $totalCredit;

            if ($openingBalance == 0 && $totalDebit == 0 && $totalCredit == 0) {
                continue;
            }

            $results->push([
                'id' => $landlord->id,
                'name' => $landlord->name,
                'type' => 'Landlord Payable',
                'group' => 'landlord_payable',
                'opening' => $openingBalance,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'closing' => $closingBalance,
                'url' => route('landlord_ledgers.index', ['landlord_id' => $landlord->id, 'date_from' => $dateFrom, 'date_to' => $dateTo]),
            ]);
        }

        return $results;
    }

    private function getPartyDuesSummary($dateFrom, $dateTo)
    {
        $results = collect();
        $parties = \App\Models\Party::orderBy('name')->get();

        foreach ($parties as $party) {
            $opBal = (float) ($party->opening_balance ?? 0);
            $opReceivable = $opBal > 0 ? $opBal : 0.0;
            $opPayable = $opBal < 0 ? abs($opBal) : 0.0;
            $includeOpeningBalance = !$dateFrom || !$party->created_at || $party->created_at->lt(\Carbon\Carbon::parse($dateFrom));

            // Prior Period
            $priorDebit = 0.0;
            $priorCredit = 0.0;
            if ($dateFrom) {
                $priorDebit += (float) \App\Models\PartyDue::where('party_id', $party->id)
                    ->where('type', 'receivable')->where('date', '<', $dateFrom)->sum('amount');
                $priorCredit += (float) \App\Models\PartyDue::where('party_id', $party->id)
                    ->where('type', 'payable')->where('date', '<', $dateFrom)->sum('amount');

                $priorDebit += (float) PaymentVoucher::where('party_id', $party->id)
                    ->where('paid_to_type', 'other')->where('date', '<', $dateFrom)->sum('amount');
                $priorCredit += (float) GeneralReceivingVoucher::where('party_id', $party->id)
                    ->where('date', '<', $dateFrom)->sum('amount');
            }

            $openingBalance = ($includeOpeningBalance ? ($opReceivable - $opPayable) : 0) + $priorDebit - $priorCredit;

            // Current Period
            $duesReceivable = (float) \App\Models\PartyDue::where('party_id', $party->id)
                ->where('type', 'receivable')
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');
            $payments = (float) PaymentVoucher::where('party_id', $party->id)
                ->where('paid_to_type', 'other')
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');
            $totalDebit = $duesReceivable + $payments;

            $duesPayable = (float) \App\Models\PartyDue::where('party_id', $party->id)
                ->where('type', 'payable')
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');
            $receipts = (float) GeneralReceivingVoucher::where('party_id', $party->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');
            $totalCredit = $duesPayable + $receipts;

            $closingBalance = $openingBalance + $totalDebit - $totalCredit;

            if ($openingBalance == 0 && $totalDebit == 0 && $totalCredit == 0) {
                continue;
            }

            $results->push([
                'id' => $party->id,
                'name' => $party->name,
                'type' => 'Party Due',
                'group' => 'party_due',
                'opening' => $openingBalance,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'closing' => $closingBalance,
                'url' => route('ledgers.party', ['party_id' => $party->id]),
            ]);
        }

        return $results;
    }

    /**
     * JV (Journal Voucher) expenses that are still unpaid as of the period end — a payable
     * owed against each expense head, distinct from the accrued spend shown under Expenses.
     */
    private function getJvPayablesSummary($dateFrom, $dateTo)
    {
        $results = collect();
        $heads = ExpenseHead::orderBy('name')->get();

        foreach ($heads as $head) {
            $openingPayable = $dateFrom ? (float) \App\Models\JvVoucher::where('expense_head_id', $head->id)
                ->where('date', '<', $dateFrom)
                ->where(function ($q) use ($dateFrom) {
                    $q->where('status', 'unpaid')
                      ->orWhere(fn($sub) => $sub->where('status', 'paid')->where('paid_date', '>=', $dateFrom));
                })->sum('amount') : 0.0;

            $closingPayable = (float) \App\Models\JvVoucher::where('expense_head_id', $head->id)
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
                ->where(function ($q) use ($dateTo) {
                    $q->where('status', 'unpaid')
                      ->when($dateTo, fn($sub) => $sub->orWhere(fn($s2) => $s2->where('status', 'paid')->where('paid_date', '>', $dateTo)));
                })->sum('amount');

            $periodCreated = (float) \App\Models\JvVoucher::where('expense_head_id', $head->id)
                ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))->sum('amount');

            $periodPaid = (float) \App\Models\JvVoucher::where('expense_head_id', $head->id)
                ->where('status', 'paid')
                ->when($dateFrom, fn($q) => $q->where('paid_date', '>=', $dateFrom))
                ->when($dateTo, fn($q) => $q->where('paid_date', '<=', $dateTo))->sum('amount');

            if ($openingPayable == 0 && $closingPayable == 0 && $periodCreated == 0 && $periodPaid == 0) {
                continue;
            }

            $results->push([
                'id' => $head->id,
                'name' => $head->name,
                'type' => 'JV Payable',
                'group' => 'jv_payable',
                'opening' => $openingPayable,
                'debit' => $periodCreated,
                'credit' => $periodPaid,
                'closing' => $closingPayable,
                'receivable' => 0.0,
                'payable' => $closingPayable,
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
