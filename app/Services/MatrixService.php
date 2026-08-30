<?php

namespace App\Services;

use App\Models\Agreement;
use App\Models\OtherTenantUnitHistory;
use App\Models\Payment;
use App\Models\PaymentAccount;
use App\Models\Unit;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MatrixService
{
    /**
     * Build matrix entries for a given request / date filter.
     */
    public function buildMatrixEntries(Request $request): Collection
    {
        $from = $request->date_from ?? $request->from_date;
        $month = $from ? Carbon::parse($from)->startOfMonth() : Carbon::now()->startOfMonth();
        $monthStr = $month->format('Y-m-d');
        $monthStart = $month->copy()->startOfMonth()->toDateString();
        $monthEnd = $month->copy()->endOfMonth()->toDateString();
        $unitStatus = $request->unit_status;
        $ownerType = $request->owner_type;
        $unitId = $request->unit_id;
        $tenantId = $request->tenant_id;
        $statusFilter = $request->status;
        $landlordId = $request->landlord_id;
        $paymentMethod = $request->payment_method;
        $paymentAccountId = $request->payment_account_id;

        $paymentAccounts = PaymentAccount::orderBy('name')->get(['id', 'name']);

        // Determine whether this is the "Generated" matrix (actual payments only, no projections)
        $reportType = $request->report_type;
        $isActualOnly = $reportType === 'monthly_matrix' || empty($reportType);
        $isMatrixReport = in_array($reportType, ['monthly_matrix', 'monthly_matrix_expected']) || empty($reportType);

        // Active other-tenant histories in the selected month
        $otherTenantHistories = OtherTenantUnitHistory::where('attached_at', '<=', $monthEnd)
            ->where(function ($q) use ($monthStart) {
                $q->whereNull('detached_at')
                    ->orWhere('detached_at', '>=', $monthStart);
            })
            ->with(['otherTenant'])
            ->orderBy('attached_at', 'desc')
            ->get()
            ->groupBy('unit_id');

        $selfUnitsOccupiedInMonth = $otherTenantHistories->keys()->toArray();

        // Include is_self units that EITHER had an other-tenant attached in the month
        // OR had payments generated FOR an other-tenant in the selected month
        $selfUnitsWithPaymentsThisMonth = $isMatrixReport
            ? Payment::where('month', $monthStr)
                ->whereNotNull('other_tenant_id')
                ->whereHas('unit', fn($q) => $q->where('is_self', true))
                ->pluck('unit_id')
                ->toArray()
            : [];

        // Also include is_self units that have PREVIOUS unpaid/partial other-tenant payments.
        $selfUnitsWithPrevUnpaid = $isMatrixReport
            ? Payment::where('month', '<', $monthStr)
                ->whereIn('status', ['unpaid', 'partial'])
                ->whereNotNull('other_tenant_id')
                ->whereHas('unit', fn($q) => $q->where('is_self', true))
                ->pluck('unit_id')
                ->toArray()
            : [];

        // Merge all sets
        $selfUnitsToInclude = array_unique(array_merge(
            $selfUnitsOccupiedInMonth,
            $selfUnitsWithPaymentsThisMonth,
            $selfUnitsWithPrevUnpaid
        ));

        $units = Unit::with(['landlord', 'otherTenant'])
            ->when($unitStatus, fn($q) => $q->where('status', $unitStatus))
            ->when($unitId, fn($q) => $q->where('id', $unitId))
            ->when($landlordId, fn($q) => $q->where('landlord_id', $landlordId))
            ->when($ownerType === 'pm_mall', fn($q) => $q->where('is_self', false))
            ->when($ownerType === 'other', fn($q) => $q->where('is_self', true)->whereIn('id', $selfUnitsToInclude))
            ->when(!$ownerType, function ($q) use ($selfUnitsToInclude) {
                $q->where(function ($sq) use ($selfUnitsToInclude) {
                    $sq->where('is_self', false)
                        ->orWhere(function ($ssq) use ($selfUnitsToInclude) {
                            if (!empty($selfUnitsToInclude)) {
                                $ssq->where('is_self', true)
                                    ->whereIn('id', $selfUnitsToInclude);
                            }
                        });
                });
            })
            ->when($tenantId, function ($q) use ($tenantId, $month) {
                $q->where(function ($sq) use ($tenantId, $month) {
                    $sq->whereHas('agreements', function ($qa) use ($tenantId, $month) {
                        $qa->where('status', 'active')
                            ->where('tenant_id', $tenantId)
                            ->where('start_date', '<=', $month->copy()->endOfMonth())
                            ->where('end_date', '>=', $month->copy()->startOfMonth());
                    })->orWhereHas('payments', function ($qp) use ($tenantId, $month) {
                        $qp->where('tenant_id', $tenantId)
                            ->where('month', $month->format('Y-m-d'));
                    });
                });
            })
            ->when($paymentMethod, function ($q) use ($paymentMethod, $month) {
                $q->whereHas('payments', function ($qp) use ($paymentMethod, $month) {
                    $qp->where('payment_method', $paymentMethod)
                        ->where('month', $month->format('Y-m-d'));
                });
            })
            ->when($paymentAccountId, function ($q) use ($paymentAccountId, $month) {
                $q->whereHas('payments', function ($qp) use ($paymentAccountId, $month) {
                    $qp->where('payment_account_id', $paymentAccountId)
                        ->where('month', $month->format('Y-m-d'));
                });
            })
            ->select(['id', 'unit_number', 'landlord_id', 'status', 'is_self', 'default_maintenance_charge'])
            ->orderBy('unit_number')
            ->get();

        $agreements = Agreement::where('status', 'active')
            ->where('start_date', '<=', $month->copy()->endOfMonth())
            ->where('end_date', '>=', $month->copy()->startOfMonth())
            ->with(['tenant'])
            ->get()
            ->groupBy('unit_id');

        $payments = Payment::where('month', $monthStr)
            ->when($paymentMethod, fn($q) => $q->where('payment_method', $paymentMethod))
            ->when($paymentAccountId, fn($q) => $q->where('payment_account_id', $paymentAccountId))
            ->with(['paymentAccount', 'receivingVouchers', 'otherTenant'])
            ->get()
            ->groupBy('unit_id');

        $previousUnpaidBalances = Payment::where('month', '<', $monthStr)
            ->whereIn('status', ['unpaid', 'partial'])
            ->where(function ($q) {
                $q->whereHas('unit', fn($qu) => $qu->where('is_self', false))
                    ->orWhere(function ($qu) {
                        $qu->whereHas('unit', fn($u) => $u->where('is_self', true))
                            ->whereNotNull('other_tenant_id');
                    });
            })
            ->selectRaw('unit_id, type, SUM(amount - amount_paid) as prev_unpaid')
            ->groupBy('unit_id', 'type')
            ->get();

        $existingSecurityDepositAgreementIds = Payment::where('type', 'security_deposit')
            ->pluck('agreement_id')
            ->toArray();

        $allocations = DB::table('receiving_voucher_payments')
            ->join('receiving_vouchers', 'receiving_voucher_payments.receiving_voucher_id', '=', 'receiving_vouchers.id')
            ->join('payments', 'receiving_voucher_payments.payment_id', '=', 'payments.id')
            ->whereNull('receiving_vouchers.deleted_at')
            ->whereNull('payments.deleted_at')
            ->whereBetween('receiving_vouchers.date', [$month->copy()->startOfMonth()->toDateString(), $month->copy()->endOfMonth()->toDateString()])
            ->when($paymentMethod, fn($q) => $q->where('receiving_vouchers.payment_method', $paymentMethod))
            ->when($paymentAccountId, fn($q) => $q->where('receiving_vouchers.payment_account_id', $paymentAccountId))
            ->select(
                'payments.unit_id',
                'payments.type',
                'receiving_voucher_payments.amount_allocated',
                'receiving_vouchers.payment_account_id',
                'receiving_vouchers.payment_method',
                'receiving_vouchers.voucher_no',
                'receiving_vouchers.date as voucher_date'
            )
            ->get();

        $unitAllocations = $allocations->groupBy('unit_id');

        $prevOtherTenantPayments = !empty($selfUnitsWithPrevUnpaid)
            ? Payment::whereIn('unit_id', $selfUnitsWithPrevUnpaid)
                ->whereNotNull('other_tenant_id')
                ->with(['otherTenant'])
                ->orderBy('month', 'desc')
                ->get()
                ->groupBy('unit_id')
            : collect();

        $matrixEntries = collect();

        foreach ($units as $index => $unit) {
            $agreement = $agreements->get($unit->id)?->first();
            $unitPayments = $payments->get($unit->id) ?? collect();

            $activeHistoryInMonth = $otherTenantHistories->get($unit->id)?->first();
            $activeOtherTenantInMonth = $activeHistoryInMonth?->otherTenant;
            $hasSelfTenantPayment = $unitPayments->whereNotNull('other_tenant_id')->isNotEmpty();
            $isOtherOccupiedInMonth = $unit->is_self && ($activeOtherTenantInMonth || $hasSelfTenantPayment);

            if ($unit->is_self && !$isOtherOccupiedInMonth && $unitPayments->isEmpty()) {
                $unitPayments = collect();
            }

            $unitPrevUnpaids = $previousUnpaidBalances->where('unit_id', $unit->id);
            $prevRent = (float) $unitPrevUnpaids->where('type', 'rent')->sum('prev_unpaid');
            $prevServ = (float) $unitPrevUnpaids->where('type', 'maintenance')->sum('prev_unpaid');
            $prevSec = (float) $unitPrevUnpaids->where('type', 'security_deposit')->sum('prev_unpaid');
            $prevExtra = (float) $unitPrevUnpaids->whereNotIn('type', ['rent', 'maintenance', 'security_deposit', 'deposit_deduction'])->sum('prev_unpaid');
            $prevUnpaid = $prevRent + $prevServ + $prevSec + $prevExtra;

            if ($isOtherOccupiedInMonth) {
                $status = 'OCCUPIED';
            } elseif ($unit->is_self && $prevUnpaid > 0) {
                $status = 'PREV UNPAID';
            } elseif ($agreement) {
                $status = $unit->status === 'sp' ? 'SP' : 'RENTED';
            } else {
                $status = match ($unit->status) {
                    'self' => 'SELF',
                    'sp' => 'SP',
                    default => 'VACANT',
                };
            }

            // Rent
            $rentPayment = $unitPayments->where('type', 'rent')->first();
            if ($rentPayment) {
                $rent_due = (float) $rentPayment->amount;
            } elseif (!$isActualOnly && $agreement) {
                $rent_due = (float) $agreement->monthly_rent;
            } else {
                $rent_due = 0.0;
            }

            // Services (Maintenance)
            $maintPayment = $unitPayments->where('type', 'maintenance')->first();
            if ($maintPayment) {
                if ($unit->is_self && !$maintPayment->other_tenant_id && !$isOtherOccupiedInMonth) {
                    $serv_due = 0.0;
                } else {
                    $serv_due = (float) $maintPayment->amount;
                }
            } elseif (!$isActualOnly && $agreement && $agreement->maintenance_charge > 0) {
                $serv_due = (float) $agreement->maintenance_charge;
            } elseif (!$isActualOnly && $unit->is_self && $isOtherOccupiedInMonth && $unit->default_maintenance_charge > 0) {
                $serv_due = (float) $unit->default_maintenance_charge;
            } else {
                $serv_due = 0.0;
            }

            // Security Deposit
            $secPayment = $unitPayments->where('type', 'security_deposit')->first();
            if ($secPayment) {
                $sec_due = (float) $secPayment->amount;
            } elseif (!$isActualOnly && $agreement) {
                $agreementStartMonth = $agreement->start_date ? $agreement->start_date->format('Y-m') : '';
                $selectedMonthStr = $month->format('Y-m');
                $hasExistingPayment = in_array($agreement->id, $existingSecurityDepositAgreementIds);
                if ($agreementStartMonth === $selectedMonthStr && !$hasExistingPayment) {
                    $sec_due = (float) $agreement->security_deposit;
                } else {
                    $sec_due = 0.0;
                }
            } else {
                $sec_due = 0.0;
            }

            // Extra (strictly extra_payment and other fees, excluding deposit_deduction)
            $extraPayments = $unitPayments->whereNotIn('type', ['rent', 'maintenance', 'security_deposit', 'deposit_deduction']);
            $extra_due = (float) $extraPayments->sum('amount');

            $total_due = $serv_due + $extra_due + $sec_due + $rent_due;

            $unitAllocationsForUnit = $unitAllocations->get($unit->id) ?? collect();

            if ($unitAllocationsForUnit->isNotEmpty()) {
                $rent_paid = (float) $unitAllocationsForUnit->where('type', 'rent')->sum('amount_allocated');
                $serv_paid = (float) $unitAllocationsForUnit->where('type', 'maintenance')->sum('amount_allocated');
                $sec_paid = (float) $unitAllocationsForUnit->where('type', 'security_deposit')->sum('amount_allocated');
                $extra_paid = (float) $unitAllocationsForUnit->whereNotIn('type', ['rent', 'maintenance', 'security_deposit', 'deposit_deduction'])->sum('amount_allocated');
                $total_received = (float) $unitAllocationsForUnit->sum('amount_allocated');

                $accountsBreakdown = [];
                foreach ($paymentAccounts as $account) {
                    $accountsBreakdown[$account->name] = (float) $unitAllocationsForUnit->where('payment_account_id', $account->id)->sum('amount_allocated');
                }

                $vouchers = $unitAllocationsForUnit->pluck('voucher_no')->unique()->filter()->toArray();
                $dates = $unitAllocationsForUnit->map(fn($a) => $a->voucher_date ? Carbon::parse($a->voucher_date)->format('d/m') : null)->unique()->filter()->toArray();
            } else {
                $rent_paid = $rentPayment ? (float) $rentPayment->amount_paid : 0.0;
                $serv_paid = ($maintPayment && (!$unit->is_self || $maintPayment->other_tenant_id || $isOtherOccupiedInMonth)) ? (float) $maintPayment->amount_paid : 0.0;
                $sec_paid = $secPayment ? (float) $secPayment->amount_paid : 0.0;
                $extra_paid = (float) $extraPayments->sum('amount_paid');
                $total_received = $serv_paid + $extra_paid + $sec_paid + $rent_paid;

                $accountsBreakdown = [];
                foreach ($paymentAccounts as $account) {
                    $accountsBreakdown[$account->name] = (float) $unitPayments->where('payment_account_id', $account->id)->sum('amount_paid');
                }

                $vouchers = [];
                $dates = [];
                foreach ($unitPayments as $p) {
                    if ($p->status === 'paid' || $p->amount_paid > 0) {
                        if ($p->receivingVouchers->isNotEmpty()) {
                            foreach ($p->receivingVouchers->pluck('voucher_no') as $vNo) {
                                $vouchers[] = $vNo;
                            }
                        } else {
                            $vouchers[] = $p->receipt_no ?? ('PM-PAY-' . str_pad($p->id, 5, '0', STR_PAD_LEFT));
                        }
                        if ($p->paid_at) {
                            $dates[] = $p->paid_at->format('d/m');
                        }
                    }
                }
            }

            $pending = max(0.0, $total_due - $total_received) + $prevUnpaid;
            $datesString = !empty($dates) ? implode(', ', array_unique($dates)) : '';
            $vouchersString = !empty($vouchers) ? implode('/', array_unique($vouchers)) : '';

            $firstPayment = $unitPayments->first();
            if ($unit->is_self) {
                $prevPaymentForUnit = $prevOtherTenantPayments->get($unit->id)?->first();
                $tenantName = $activeOtherTenantInMonth?->name
                    ?? $firstPayment?->otherTenant?->name
                    ?? ($prevUnpaid > 0 ? $prevPaymentForUnit?->otherTenant?->name : null)
                    ?? '—';
            } else {
                $tenantName = $agreement?->tenant?->name ?? ($firstPayment?->tenant?->name ?? '—');
            }

            $matrixEntries->push([
                'sr' => $index + 1,
                'date' => $datesString,
                'rsv' => $vouchersString,
                'flat_no' => $unit->unit_number,
                'owner' => $unit->landlord?->name ?? '—',
                'tenant' => $tenantName,
                'status' => $status,
                'serv' => $serv_due,
                'serv_paid' => $serv_paid,
                'extra' => $extra_due,
                'extra_paid' => $extra_paid,
                'rent' => $rent_due,
                'rent_paid' => $rent_paid,
                'security_deposit' => $sec_due,
                'sec_paid' => $sec_paid,
                'total_amount' => $total_due,
                'received' => $total_received,
                'payment_accounts' => $accountsBreakdown,
                'prev_rent' => $prevRent,
                'prev_serv' => $prevServ,
                'prev_sec' => $prevSec,
                'prev_extra' => $prevExtra,
                'prev_unpaid' => $prevUnpaid,
                'pending' => $pending,
                'is_self' => (bool) $unit->is_self,
            ]);
        }

        if ($statusFilter) {
            $matrixEntries = $matrixEntries->filter(function ($entry) use ($statusFilter) {
                $due = (float) $entry['total_amount'];
                $paid = (float) $entry['received'];
                $pending = (float) $entry['pending'];

                if ($statusFilter === 'paid') {
                    return $due > 0 && $pending <= 0;
                } elseif ($statusFilter === 'unpaid') {
                    return $due > 0 && $paid <= 0;
                } elseif ($statusFilter === 'partial') {
                    return $paid > 0 && $paid < $due;
                }
                return true;
            })->values()->map(function ($entry, $idx) {
                $entry['sr'] = $idx + 1;
                return $entry;
            });
        }

        return $matrixEntries;
    }

    /**
     * Build matrix summary totals from matrix entries collection.
     */
    public function buildMatrixSummary(Collection $matrixEntries): array
    {
        $paymentAccounts = PaymentAccount::orderBy('name')->get(['id', 'name']);

        $accountsTotal = [];
        foreach ($paymentAccounts as $account) {
            $accountsTotal[$account->name] = $matrixEntries->sum(function ($e) use ($account) {
                return $e['payment_accounts'][$account->name] ?? 0.0;
            });
        }

        $totalRent        = (float) $matrixEntries->sum('rent');
        $totalRentPaid    = (float) $matrixEntries->sum('rent_paid');
        $totalRentPrev    = (float) $matrixEntries->sum('prev_rent');
        $totalRentDue     = $totalRent + $totalRentPrev;
        $totalRentUnpaid  = max(0.0, $totalRentDue - $totalRentPaid);

        $totalServ        = (float) $matrixEntries->sum('serv');
        $totalServPaid    = (float) $matrixEntries->sum('serv_paid');
        $totalServPrev    = (float) $matrixEntries->sum('prev_serv');
        $totalServDue     = $totalServ + $totalServPrev;
        $totalServUnpaid  = max(0.0, $totalServDue - $totalServPaid);

        $totalExtra       = (float) $matrixEntries->sum('extra');
        $totalExtraPaid   = (float) $matrixEntries->sum('extra_paid');
        $totalExtraPrev   = (float) $matrixEntries->sum('prev_extra');
        $totalExtraDue    = $totalExtra + $totalExtraPrev;
        $totalExtraUnpaid = max(0.0, $totalExtraDue - $totalExtraPaid);

        $totalSec         = (float) $matrixEntries->sum('security_deposit');
        $totalSecPaid     = (float) $matrixEntries->sum('sec_paid');
        $totalSecPrev     = (float) $matrixEntries->sum('prev_sec');
        $totalSecDue      = $totalSec + $totalSecPrev;
        $totalSecUnpaid   = max(0.0, $totalSecDue - $totalSecPaid);

        $totalAmount      = (float) $matrixEntries->sum('total_amount');
        $totalReceived    = (float) $matrixEntries->sum('received');
        $totalPrevUnpaid  = (float) $matrixEntries->sum('prev_unpaid');
        $totalAmountDue   = $totalAmount + $totalPrevUnpaid;
        $totalPending     = (float) $matrixEntries->sum('pending');

        return [
            // Services (maintenance only)
            'total_serv'              => $totalServDue,
            'total_serv_curr'         => $totalServ,
            'total_serv_paid'         => $totalServPaid,
            'total_serv_unpaid'       => $totalServUnpaid,
            'total_serv_prev_unpaid'  => $totalServPrev,

            // Extra payments (all non-rent/maintenance/security_deposit)
            'total_extra'             => $totalExtraDue,
            'total_extra_curr'        => $totalExtra,
            'total_extra_paid'        => $totalExtraPaid,
            'total_extra_unpaid'      => $totalExtraUnpaid,
            'total_extra_prev_unpaid' => $totalExtraPrev,

            // Rent
            'total_rent'              => $totalRentDue,
            'total_rent_curr'         => $totalRent,
            'total_rent_paid'         => $totalRentPaid,
            'total_rent_unpaid'       => $totalRentUnpaid,
            'total_rent_prev_unpaid'  => $totalRentPrev,

            // Security Deposit
            'total_security_deposit'  => $totalSecDue,
            'total_sec_curr'          => $totalSec,
            'total_sec_paid'          => $totalSecPaid,
            'total_sec_unpaid'        => $totalSecUnpaid,
            'total_sec_prev_unpaid'   => $totalSecPrev,

            // Grand Total
            'total_amount'            => $totalAmountDue,
            'total_amount_curr'       => $totalAmount,
            'total_received'          => $totalReceived,
            'total_prev_unpaid'       => $totalPrevUnpaid,
            'total_pending'           => $totalPending,

            'accounts_total'          => $accountsTotal,
            'count'                   => $matrixEntries->count(),
            'rent_count'              => $matrixEntries->where('rent_paid', '>', 0)->count(),
            'serv_count'              => $matrixEntries->where('serv_paid', '>', 0)->count(),
            'sec_count'               => $matrixEntries->where('sec_paid', '>', 0)->count(),
            'extra_count'             => $matrixEntries->where('extra_paid', '>', 0)->count(),
        ];
    }
}
