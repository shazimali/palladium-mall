<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\Payment;
use App\Models\PaymentVoucher;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SecurityLedgerService
{
    /**
     * Build chronological security deposit ledger entries (received / deducted / refunded) per unit.
     */
    public function buildLedgerData(Request $request): array
    {
        // Unlike recurring rent/maintenance ledgers, a deposit can sit untouched for the
        // whole tenancy before being refunded, so this ledger defaults to full history
        // instead of the current month — otherwise refunds from prior months never show.
        $dateFromStr = $request->input('date_from');
        $dateToStr = $request->input('date_to');

        $dateFrom = $dateFromStr ? Carbon::parse($dateFromStr)->startOfDay() : Carbon::createFromDate(2000, 1, 1)->startOfDay();
        $dateTo = $dateToStr ? Carbon::parse($dateToStr)->endOfDay() : Carbon::now()->endOfDay();

        $transactionType = $request->input('transaction_type'); // 'received' | 'deducted' | 'refunded' | 'all' | null
        $unitId = $request->input('unit_id');

        $units = Unit::with(['landlord', 'otherTenant', 'activeAgreement.tenant'])
            ->when($unitId, fn($q) => $q->where('id', $unitId))
            ->orderBy('unit_number')
            ->get();
        $unitIds = $units->pluck('id')->toArray();

        $includeReceived = !$transactionType || $transactionType === 'all' || $transactionType === 'received';
        $includeDeducted = !$transactionType || $transactionType === 'all' || $transactionType === 'deducted';
        $includeRefunded = !$transactionType || $transactionType === 'all' || $transactionType === 'refunded';

        $receiptsByUnit = $includeReceived
            ? Payment::whereIn('unit_id', $unitIds)
                ->where('type', 'security_deposit')
                ->where('amount_paid', '>', 0)
                ->with(['tenant', 'otherTenant', 'receivingVouchers'])
                ->get()
                ->groupBy('unit_id')
            : collect();

        $deductionsByUnit = $includeDeducted
            ? Payment::whereIn('unit_id', $unitIds)
                ->where('type', 'deposit_deduction')
                ->where('amount_paid', '>', 0)
                ->with(['tenant', 'otherTenant', 'receivingVouchers'])
                ->get()
                ->groupBy('unit_id')
            : collect();

        $refundsByUnit = $includeRefunded
            ? PaymentVoucher::whereIn('unit_id', $unitIds)
                ->where('paid_to_type', 'tenant')
                ->with(['tenant', 'unit'])
                ->get()
                ->groupBy(fn($pv) => $pv->unit_id ?? $pv->tenant?->unit_id)
            : collect();

        $rows = collect();
        $totalReceived = 0.0;
        $totalDeducted = 0.0;
        $totalRefunded = 0.0;
        $totalBalance = 0.0;

        foreach ($units as $unit) {
            $unitReceipts = ($receiptsByUnit->get($unit->id) ?? collect())
                ->map(function ($p) {
                    $rv = $p->receivingVouchers->first();

                    return [
                        'date' => $p->paid_at ?: ($p->month ?: $p->due_date),
                        'type' => 'Received',
                        'debit' => 0.0,
                        'credit' => (float) $p->amount_paid,
                        'tenant_name' => $p->tenant?->name ?? $p->otherTenant?->name,
                        'reference' => $rv?->voucher_no ?? ('Bill #' . $p->id),
                        'reference_type' => $rv ? 'receiving_voucher' : null,
                        'reference_id' => $rv?->id,
                        'notes' => $p->notes,
                    ];
                });

            $unitDeductions = ($deductionsByUnit->get($unit->id) ?? collect())
                ->map(function ($p) {
                    $rv = $p->receivingVouchers->first();

                    return [
                        'date' => $p->paid_at ?: ($p->month ?: $p->due_date),
                        'type' => 'Deducted',
                        'debit' => (float) $p->amount_paid,
                        'credit' => 0.0,
                        'tenant_name' => $p->tenant?->name ?? $p->otherTenant?->name,
                        'reference' => $rv?->voucher_no ?? ('Bill #' . $p->id),
                        'reference_type' => $rv ? 'receiving_voucher' : null,
                        'reference_id' => $rv?->id,
                        'notes' => $p->notes,
                    ];
                });

            $unitRefunds = ($refundsByUnit->get($unit->id) ?? collect())
                ->map(fn($pv) => [
                    'date' => $pv->date,
                    'type' => 'Refunded',
                    'debit' => (float) $pv->amount,
                    'credit' => 0.0,
                    'tenant_name' => $pv->tenant?->name,
                    'reference' => $pv->voucher_no,
                    'reference_type' => 'payment_voucher',
                    'reference_id' => $pv->id,
                    'notes' => $pv->notes ?? null,
                ]);

            $allUnitEntries = $unitReceipts->concat($unitDeductions)->concat($unitRefunds)
                ->filter(fn($e) => $e['date'])
                ->sortBy(fn($e) => Carbon::parse($e['date'])->timestamp)
                ->values();

            $openingBalance = (float) $allUnitEntries
                ->filter(fn($e) => Carbon::parse($e['date'])->lt($dateFrom))
                ->sum(fn($e) => $e['credit'] - $e['debit']);

            $periodEntries = $allUnitEntries
                ->filter(function ($e) use ($dateFrom, $dateTo) {
                    $d = Carbon::parse($e['date']);
                    return $d->gte($dateFrom) && $d->lte($dateTo);
                })
                ->values();

            if ($periodEntries->isEmpty() && abs($openingBalance) < 0.005) {
                continue;
            }

            $tenantName = $periodEntries->first()['tenant_name']
                ?? $allUnitEntries->last()['tenant_name']
                ?? $unit->activeAgreement?->tenant?->name
                ?? $unit->otherTenant?->name
                ?? '—';

            $runningBalance = $openingBalance;

            if ($periodEntries->isEmpty()) {
                // Unit has an opening balance carried forward but no activity this period.
                $rows->push([
                    '_ts' => $dateFrom->timestamp,
                    'date' => $dateFrom->format('d M Y'),
                    'unit_id' => $unit->id,
                    'unit_number' => $unit->unit_number,
                    'owner' => $unit->is_self ? ($unit->landlord?->name ?? 'Other Owner') : 'PM Mall',
                    'tenant_name' => $tenantName,
                    'type' => 'Balance c/f',
                    'reference' => '—',
                    'reference_type' => null,
                    'reference_id' => null,
                    'debit' => 0.0,
                    'credit' => 0.0,
                    'balance' => $runningBalance,
                ]);
            } else {
                foreach ($periodEntries as $e) {
                    $runningBalance += ($e['credit'] - $e['debit']);

                    $rows->push([
                        '_ts' => Carbon::parse($e['date'])->timestamp,
                        'date' => Carbon::parse($e['date'])->format('d M Y'),
                        'unit_id' => $unit->id,
                        'unit_number' => $unit->unit_number,
                        'owner' => $unit->is_self ? ($unit->landlord?->name ?? 'Other Owner') : 'PM Mall',
                        'tenant_name' => $e['tenant_name'] ?? $tenantName,
                        'type' => $e['type'],
                        'reference' => $e['reference'],
                        'reference_type' => $e['reference_type'],
                        'reference_id' => $e['reference_id'],
                        'debit' => $e['debit'],
                        'credit' => $e['credit'],
                        'balance' => $runningBalance,
                    ]);

                    $totalReceived += $e['type'] === 'Received' ? $e['credit'] : 0.0;
                    $totalDeducted += $e['type'] === 'Deducted' ? $e['debit'] : 0.0;
                    $totalRefunded += $e['type'] === 'Refunded' ? $e['debit'] : 0.0;
                }
            }

            $totalBalance += $runningBalance;
        }

        // Rows are pushed grouped by unit; re-sort the whole set chronologically
        // (stable — ties keep their per-unit push order) and renumber Sr #.
        $rows = $rows->sortBy('_ts')->values()->map(function ($row, $index) {
            $row['sr'] = $index + 1;
            unset($row['_ts']);
            return $row;
        });

        $summary = [
            'total_units' => $rows->pluck('unit_id')->unique()->count(),
            'total_received' => $totalReceived,
            'total_deducted' => $totalDeducted,
            'total_refunded' => $totalRefunded,
            'total_balance' => $totalBalance,
            'total_records' => $rows->count(),
        ];

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

        $filterTags[] = [
            'label' => 'Period',
            'value' => ($dateFromStr || $dateToStr)
                ? $dateFrom->format('d M Y') . ' — ' . $dateTo->format('d M Y')
                : 'All Time',
            'type' => 'period',
        ];

        if ($transactionType === 'received') {
            $filterTags[] = ['label' => 'Transaction', 'value' => 'Received', 'type' => 'transaction'];
        } elseif ($transactionType === 'deducted') {
            $filterTags[] = ['label' => 'Transaction', 'value' => 'Deducted', 'type' => 'transaction'];
        } elseif ($transactionType === 'refunded') {
            $filterTags[] = ['label' => 'Transaction', 'value' => 'Refunded', 'type' => 'transaction'];
        } else {
            $filterTags[] = ['label' => 'Transaction', 'value' => 'All Transactions', 'type' => 'transaction'];
        }

        if ($selectedUnitNumber) {
            $filterTags[] = ['label' => 'Unit', 'value' => $selectedUnitNumber, 'type' => 'unit'];
        }

        return [
            'rows' => $rows,
            'all_rows' => $allRows,
            'summary' => $summary,
            'date_from' => $dateFromStr,
            'date_to' => $dateToStr,
            'filter_tags' => $filterTags,
            'filters' => [
                'transaction_type' => $transactionType,
                'unit_id' => $unitId,
            ],
        ];
    }
}
