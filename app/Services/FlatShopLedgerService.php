<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FlatShopLedgerService
{
    /**
     * Build summary & payment entries for Flat/Shop Ledger.
     */
    public function buildLedgerData(Request $request): array
    {
        $dateFromStr = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateToStr = $request->input('date_to', Carbon::now()->endOfMonth()->toDateString());

        $dateFrom = Carbon::parse($dateFromStr)->startOfMonth();
        $dateTo = Carbon::parse($dateToStr)->endOfMonth();

        $ownerType = $request->input('owner_type');        // 'pm_mall' | 'other_owned' | null
        $occupancyStatus = $request->input('occupancy_status'); // 'pm_rented' | 'other_occupied' | 'other_unoccupied' | 'vacant' | null
        $billingType = $request->input('billing_type');     // 'rent' | 'maintenance' | 'extra_payment' | 'security_deposit' | 'all' | null
        $paymentStatus = $request->input('payment_status');   // 'paid' | 'unpaid' | 'partial' | 'all' | null
        $unitId = $request->input('unit_id');

        // Identify other-owned units that have payments or attached other tenants (current or history)
        $otherOwnedUnitsWithPayments = Payment::whereHas('unit', fn($q) => $q->where('is_self', true))
            ->pluck('unit_id')
            ->toArray();

        $otherTenantOccupiedUnits = Unit::where('is_self', true)
            ->where(function ($q) {
                $q->whereHas('otherTenant')
                    ->orWhereHas('otherTenantHistory');
            })
            ->pluck('id')
            ->toArray();

        $selfUnitsToInclude = array_unique(array_merge($otherOwnedUnitsWithPayments, $otherTenantOccupiedUnits));

        // Fetch target units ordered by unit_number
        $unitsQuery = Unit::with(['landlord', 'otherTenant', 'activeAgreement.tenant'])
            ->when($unitId, fn($q) => $q->where('id', $unitId))
            ->orderBy('unit_number');

        if ($ownerType === 'pm_mall') {
            $unitsQuery->where(function ($q) use ($selfUnitsToInclude) {
                $q->where('is_self', false);
                if (!empty($selfUnitsToInclude)) {
                    $q->orWhereIn('id', $selfUnitsToInclude);
                }
            });
        } elseif ($ownerType === 'other_owned') {
            $unitsQuery->where('is_self', true);
        }

        if ($occupancyStatus === 'pm_rented') {
            $unitsQuery->where('is_self', false)->whereHas('agreements', fn($qa) => $qa->where('status', 'active'));
        } elseif ($occupancyStatus === 'other_occupied') {
            $unitsQuery->where('is_self', true)->whereHas('otherTenant');
        } elseif ($occupancyStatus === 'other_unoccupied') {
            $unitsQuery->where('is_self', true)->whereDoesntHave('otherTenant');
        } elseif ($occupancyStatus === 'vacant') {
            $unitsQuery->where('is_self', false)->whereDoesntHave('agreements', fn($qa) => $qa->where('status', 'active'));
        }

        $units = $unitsQuery->get();
        $unitIds = $units->pluck('id')->toArray();

        $isSecurityDeposit = ($billingType === 'security_deposit');

        if ($isSecurityDeposit) {
            $secPayments = Payment::where('type', 'security_deposit')
                ->where('month', '>=', $dateFrom->toDateString())
                ->where('month', '<=', $dateTo->toDateString())
                ->get()
                ->groupBy('unit_id');

            $deductionPayments = Payment::where('type', 'deposit_deduction')
                ->where('month', '>=', $dateFrom->toDateString())
                ->where('month', '<=', $dateTo->toDateString())
                ->get()
                ->groupBy('unit_id');

            $rows = collect();
            $sr = 1;

            foreach ($units as $unit) {
                $unitSecPayments = $secPayments->get($unit->id) ?? collect();
                $unitDeductions  = $deductionPayments->get($unit->id) ?? collect();

                $agreement = $unit->agreements()
                    ->where('start_date', '>=', $dateFrom->toDateString())
                    ->where('start_date', '<=', $dateTo->toDateString())
                    ->orderBy('start_date', 'desc')
                    ->first();

                if (!$agreement && $unit->activeAgreement) {
                    $ag = $unit->activeAgreement;
                    if ($ag->start_date && $ag->start_date->gte($dateFrom) && $ag->start_date->lte($dateTo)) {
                        $agreement = $ag;
                    }
                }

                // Strictly filter units to those having security deposit payments, deductions, or agreements matching date range
                if ($unitSecPayments->isEmpty() && $unitDeductions->isEmpty() && !$agreement) {
                    continue;
                }

                $tenantName = $agreement?->tenant?->name 
                    ?? $unitSecPayments->first()?->tenant?->name 
                    ?? $unitSecPayments->first()?->otherTenant?->name 
                    ?? ($unit->otherTenant?->name ?? '—');

                $agSec            = (float) ($agreement?->security_deposit ?? 0);
                $paySec           = (float) $unitSecPayments->sum('amount');
                $requiredDeposit  = max($agSec, $paySec);
                $collectedDeposit = (float) $unitSecPayments->sum('amount_paid');
                $pendingDeposit   = max(0.0, $requiredDeposit - $collectedDeposit);
                $deductionDeposit = (float) $unitDeductions->sum('amount_paid');
                $netRefundable    = max(0.0, $collectedDeposit - $deductionDeposit);

                if ($agreement) {
                    $status = $unit->status === 'sp' ? 'SP' : 'RENTED';
                } elseif ($unit->otherTenant) {
                    $status = 'OCCUPIED';
                } else {
                    $status = match ($unit->status) {
                        'self'  => 'SELF',
                        'sp'    => 'SP',
                        default => 'VACANT',
                    };
                }

                $rows->push([
                    'sr'                => $sr++,
                    'unit_id'           => $unit->id,
                    'unit_number'       => $unit->unit_number,
                    'owner'             => $unit->is_self ? ($unit->landlord?->name ?? 'Other Owner') : 'PM Mall',
                    'tenant_name'       => $tenantName,
                    'status'            => $status,
                    'is_self'           => (bool) $unit->is_self,
                    'required_deposit'  => $requiredDeposit,
                    'collected_deposit' => $collectedDeposit,
                    'pending_deposit'   => $pendingDeposit,
                    'deduction_deposit' => $deductionDeposit,
                    'net_refundable'    => $netRefundable,
                ]);
            }

            $summary = [
                'total_required'       => (float) $rows->sum('required_deposit'),
                'total_collected'      => (float) $rows->sum('collected_deposit'),
                'total_pending'        => (float) $rows->sum('pending_deposit'),
                'total_deductions'     => (float) $rows->sum('deduction_deposit'),
                'total_net_refundable' => (float) $rows->sum('net_refundable'),
                'total_records'        => $rows->count(),
            ];
        } else {
            // Fetch previous unpaid balances prior to dateFrom per unit
            $prevUnpaidMap = Payment::whereIn('unit_id', $unitIds)
                ->where('month', '<', $dateFrom->toDateString())
                ->whereRaw('amount > amount_paid')
                ->where(function ($q) {
                    $q->whereHas('unit', fn($qu) => $qu->where('is_self', false))
                        ->orWhereIn('type', ['extra_payment', 'fine', 'rent', 'security_deposit'])
                        ->orWhereNotNull('other_tenant_id');
                })
                ->when($billingType && $billingType !== 'all', function ($q) use ($billingType) {
                    if ($billingType === 'extra_payment') {
                        $q->whereIn('type', ['extra_payment', 'fine']);
                    } else {
                        $q->where('type', $billingType);
                    }
                })
                ->selectRaw('unit_id, SUM(amount - amount_paid) as prev_unpaid')
                ->groupBy('unit_id')
                ->pluck('prev_unpaid', 'unit_id')
                ->toArray();

            // Fetch all payments matching criteria in selected date range
            $paymentsQuery = Payment::with(['unit', 'tenant', 'otherTenant', 'paymentAccount', 'receivingVouchers'])
                ->whereIn('unit_id', $unitIds)
                ->where('month', '>=', $dateFrom->toDateString())
                ->where('month', '<=', $dateTo->toDateString())
                ->where(function ($q) {
                    $q->whereHas('unit', fn($qu) => $qu->where('is_self', false))
                        ->orWhereIn('type', ['extra_payment', 'fine', 'rent', 'security_deposit'])
                        ->orWhereNotNull('other_tenant_id');
                });

            if ($billingType && $billingType !== 'all') {
                if ($billingType === 'extra_payment') {
                    $paymentsQuery->whereIn('type', ['extra_payment', 'fine']);
                } else {
                    $paymentsQuery->where('type', $billingType);
                }
            }

            if ($paymentStatus && $paymentStatus !== 'all') {
                $paymentsQuery->where('status', $paymentStatus);
            }

            $payments = $paymentsQuery->orderBy('unit_id')
                ->orderBy('month', 'desc')
                ->get()
                ->groupBy('unit_id');

            $rows = collect();
            $sr = 1;

            // Iterate over target units to ensure units with prev_unpaid (even if 0 current payments) are included
            foreach ($units as $unit) {
                $uPayments = $payments->get($unit->id, collect());
                $prevUnpaidVal = (float) ($prevUnpaidMap[$unit->id] ?? 0.0);

                if ($uPayments->isNotEmpty()) {
                    $firstRow = true;
                    foreach ($uPayments as $p) {
                        $tenantName = '—';
                        if ($p->tenant) {
                            $tenantName = $p->tenant->name;
                        } elseif ($p->otherTenant) {
                            $tenantName = $p->otherTenant->name;
                        } elseif ($unit) {
                            if ($unit->is_self) {
                                $tenantName = $unit->otherTenant?->name ?? ($unit->landlord?->name ?? '—');
                            } else {
                                $tenantName = $unit->activeAgreement?->tenant?->name ?? '—';
                            }
                        }

                        $paymentMethod = $p->payment_method ? ucfirst(str_replace('_', ' ', $p->payment_method)) : '—';
                        $paymentAccount = $p->paymentAccount?->name ?? '—';
                        $paidAt = $p->paid_at ? $p->paid_at->format('d M Y') : '—';

                        $rowPrevUnpaid = $firstRow ? $prevUnpaidVal : 0.0;
                        $firstRow = false;

                        $amountDue = (float) $p->amount;
                        $amountPaid = (float) $p->amount_paid;
                        $balance = $rowPrevUnpaid + $amountDue - $amountPaid;

                        $rows->push([
                            'sr' => $sr++,
                            'payment_id' => $p->id,
                            'unit_id' => $unit->id,
                            'unit_number' => $unit->unit_number ?? '—',
                            'tenant_name' => $tenantName,
                            'prev_unpaid' => $rowPrevUnpaid,
                            'amount_due' => $amountDue,
                            'amount_paid' => $amountPaid,
                            'payment_method' => $paymentMethod,
                            'payment_account' => $paymentAccount,
                            'paid_at' => $paidAt,
                            'balance' => $balance,
                            'type_label' => $p->type_label,
                            'month_label' => $p->month ? $p->month->format('M Y') : '—',
                            'status' => $p->status,
                        ]);
                    }
                } elseif ($prevUnpaidVal > 0) {
                    // Unit has no current period payments, but HAS previous unpaid balance (e.g. Unit 401 unattached in August)
                    $tenantName = $unit->is_self
                        ? ($unit->otherTenant?->name ?? ($unit->landlord?->name ?? '—'))
                        : ($unit->activeAgreement?->tenant?->name ?? '—');

                    $typeLabel = ($billingType && $billingType !== 'all')
                        ? ucfirst(str_replace('_', ' ', $billingType))
                        : 'Maintenance';

                    $rows->push([
                        'sr' => $sr++,
                        'payment_id' => null,
                        'unit_id' => $unit->id,
                        'unit_number' => $unit->unit_number ?? '—',
                        'tenant_name' => $tenantName,
                        'prev_unpaid' => $prevUnpaidVal,
                        'amount_due' => 0.0,
                        'amount_paid' => 0.0,
                        'payment_method' => '—',
                        'payment_account' => '—',
                        'paid_at' => '—',
                        'balance' => $prevUnpaidVal,
                        'type_label' => $typeLabel,
                        'month_label' => '—',
                        'status' => 'unpaid',
                    ]);
                }
            }

            $summary = [
                'total_records' => $rows->count(),
                'total_prev_unpaid' => (float) array_sum($prevUnpaidMap),
                'total_amount_due' => (float) $rows->sum('amount_due'),
                'total_amount_paid' => (float) $rows->sum('amount_paid'),
                'total_balance' => (float) array_sum($prevUnpaidMap) + (float) $rows->sum('amount_due') - (float) $rows->sum('amount_paid'),
            ];
        }

        $allRows = $rows;

        if ($request->boolean('paginate', true)) {
            $currentPage = \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage();
            $perPage = 25;
            $currentPageSearchResults = $allRows->slice(($currentPage - 1) * $perPage, $perPage)->values();
            $rows = new \Illuminate\Pagination\LengthAwarePaginator(
                $currentPageSearchResults,
                $allRows->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        }

        $selectedUnitNumber = null;
        if ($unitId) {
            $selectedUnitNumber = Unit::find($unitId)?->unit_number;
        }

        $filterTags = [];

        // Date Range Tag
        $filterTags[] = [
            'label' => 'Period',
            'value' => Carbon::parse($dateFromStr)->format('d M Y') . ' — ' . Carbon::parse($dateToStr)->format('d M Y'),
            'type'  => 'period',
        ];

        // Owner Tag
        if ($ownerType === 'pm_mall') {
            $filterTags[] = ['label' => 'Owner', 'value' => 'PM Mall', 'type' => 'owner'];
        } elseif ($ownerType === 'other_owned') {
            $filterTags[] = ['label' => 'Owner', 'value' => 'Other Owned', 'type' => 'owner'];
        } else {
            $filterTags[] = ['label' => 'Owner', 'value' => 'All Owners', 'type' => 'owner'];
        }

        // Occupancy Tag
        if ($occupancyStatus === 'pm_rented') {
            $filterTags[] = ['label' => 'Occupancy', 'value' => 'PM Mall Rented', 'type' => 'occupancy'];
        } elseif ($occupancyStatus === 'other_occupied') {
            $filterTags[] = ['label' => 'Occupancy', 'value' => 'Other Occupied', 'type' => 'occupancy'];
        } elseif ($occupancyStatus === 'other_unoccupied') {
            $filterTags[] = ['label' => 'Occupancy', 'value' => 'Other Unoccupied', 'type' => 'occupancy'];
        } elseif ($occupancyStatus === 'vacant') {
            $filterTags[] = ['label' => 'Occupancy', 'value' => 'PM Mall Vacant', 'type' => 'occupancy'];
        } else {
            $filterTags[] = ['label' => 'Occupancy', 'value' => 'All Occupancy', 'type' => 'occupancy'];
        }

        // Billing Type Tag
        if ($billingType === 'rent') {
            $filterTags[] = ['label' => 'Billing', 'value' => 'Rent', 'type' => 'billing'];
        } elseif ($billingType === 'maintenance') {
            $filterTags[] = ['label' => 'Billing', 'value' => 'Maintenance', 'type' => 'billing'];
        } elseif ($billingType === 'extra_payment') {
            $filterTags[] = ['label' => 'Billing', 'value' => 'Extra Amount', 'type' => 'billing'];
        } elseif ($billingType === 'security_deposit') {
            $filterTags[] = ['label' => 'Billing', 'value' => 'Security Deposit', 'type' => 'billing'];
        } else {
            $filterTags[] = ['label' => 'Billing', 'value' => 'All Billings', 'type' => 'billing'];
        }

        // Payment Status Tag
        if ($paymentStatus === 'paid') {
            $filterTags[] = ['label' => 'Status', 'value' => 'Paid', 'type' => 'status'];
        } elseif ($paymentStatus === 'unpaid') {
            $filterTags[] = ['label' => 'Status', 'value' => 'Unpaid', 'type' => 'status'];
        } elseif ($paymentStatus === 'partial') {
            $filterTags[] = ['label' => 'Status', 'value' => 'Partial', 'type' => 'status'];
        } else {
            $filterTags[] = ['label' => 'Status', 'value' => 'All Statuses', 'type' => 'status'];
        }

        // Unit Tag
        if ($selectedUnitNumber) {
            $filterTags[] = ['label' => 'Unit', 'value' => $selectedUnitNumber, 'type' => 'unit'];
        }

        return [
            'rows' => $rows,
            'all_rows' => $allRows,
            'summary' => $summary,
            'is_security_deposit' => $isSecurityDeposit,
            'date_from' => $dateFromStr,
            'date_to' => $dateToStr,
            'filter_tags' => $filterTags,
            'filters' => [
                'owner_type' => $ownerType,
                'occupancy_status' => $occupancyStatus,
                'billing_type' => $billingType,
                'payment_status' => $paymentStatus,
                'unit_id' => $unitId,
            ],
        ];
    }

    /**
     * Build detailed chronological statement for a single flat/shop.
     */
    public function buildUnitStatement(Unit $unit, Request $request): array
    {
        $dateFromStr = $request->input('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateToStr = $request->input('date_to', Carbon::now()->endOfMonth()->toDateString());

        $dateFrom = Carbon::parse($dateFromStr)->startOfDay();
        $dateTo = Carbon::parse($dateToStr)->endOfDay();

        $billingType = $request->input('billing_type');
        $paymentStatus = $request->input('payment_status');

        $prevPayments = Payment::where('unit_id', $unit->id)
            ->where('month', '<', $dateFrom->toDateString())
            ->when($billingType && $billingType !== 'all', function ($q) use ($billingType) {
                if ($billingType === 'extra_payment') {
                    $q->whereIn('type', ['extra_payment', 'other', 'fine']);
                } else {
                    $q->where('type', $billingType);
                }
            })
            ->get();

        $openingBalance = (float) $prevPayments->sum(fn($p) => $p->amount - $p->amount_paid);

        $payments = Payment::with(['tenant', 'otherTenant', 'paymentAccount', 'receivingVouchers'])
            ->where('unit_id', $unit->id)
            ->where('month', '>=', $dateFrom->toDateString())
            ->where('month', '<=', $dateTo->toDateString())
            ->when($billingType && $billingType !== 'all', function ($q) use ($billingType) {
                if ($billingType === 'extra_payment') {
                    $q->whereIn('type', ['extra_payment', 'other', 'fine']);
                } else {
                    $q->where('type', $billingType);
                }
            })
            ->when($paymentStatus && $paymentStatus !== 'all', fn($q) => $q->where('status', $paymentStatus))
            ->orderBy('month', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();

        $runningBalance = $openingBalance;
        $entries = collect();

        foreach ($payments as $p) {
            $debit = (float) $p->amount;
            $credit = (float) $p->amount_paid;
            $runningBalance += ($debit - $credit);

            $tenantName = $p->tenant?->name ?? ($p->otherTenant?->name ?? '—');

            $entries->push([
                'id' => $p->id,
                'date' => $p->due_date ?: $p->month,
                'month' => $p->month ? $p->month->format('M Y') : '—',
                'tenant_name' => $tenantName,
                'type' => $p->type_label,
                'type_code' => $p->type,
                'amount_due' => $debit,
                'amount_paid' => $credit,
                'payment_method' => $p->payment_method ? ucfirst(str_replace('_', ' ', $p->payment_method)) : '—',
                'payment_account' => $p->paymentAccount?->name ?? '—',
                'paid_at' => $p->paid_at ? $p->paid_at->format('d M Y') : '—',
                'balance' => $runningBalance,
                'status' => $p->status,
            ]);
        }

        return [
            'unit' => $unit,
            'opening_balance' => $openingBalance,
            'closing_balance' => $runningBalance,
            'entries' => $entries,
            'total_debit' => (float) $entries->sum('amount_due'),
            'total_credit' => (float) $entries->sum('amount_paid'),
            'date_from' => $dateFromStr,
            'date_to' => $dateToStr,
        ];
    }
}
