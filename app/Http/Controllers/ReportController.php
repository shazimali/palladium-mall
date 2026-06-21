<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Landlord;
use App\Models\PaymentAccount;
use App\Models\Agreement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    // -------------------------------------------------------------------------
    // Main Reports Page
    // -------------------------------------------------------------------------

    public function index(Request $request): View
    {
        $tenants = Tenant::orderBy('name')->get(['id', 'name']);
        $units   = Unit::orderBy('unit_number')->get(['id', 'unit_number', 'type']);
        $landlords = Landlord::orderBy('name')->get(['id', 'name']);
        $paymentAccounts = PaymentAccount::orderBy('name')->get(['id', 'name']);

        $hasQuery = $request->filled('report_type')
            || $request->filled('date_from')
            || $request->filled('date_to')
            || $request->filled('unit_id')
            || $request->filled('tenant_id')
            || $request->filled('status')
            || $request->filled('landlord_id')
            || $request->filled('payment_method')
            || $request->filled('payment_account_id')
            || $request->filled('unit_status')
            || $request->filled('owner_type');

        $entries = collect();
        $summary = null;

        if ($hasQuery) {
            if ($request->report_type === 'monthly_matrix') {
                $entries = $this->buildMatrixEntries($request);
                $summary = $this->buildMatrixSummary($entries);
            } else {
                $entries = $this->buildEntries($request);
                $summary = $this->buildSummary($entries);
            }
        }

        return view('reports.index', [
            'title'           => 'Reports',
            'tenants'         => $tenants,
            'units'           => $units,
            'landlords'       => $landlords,
            'paymentAccounts' => $paymentAccounts,
            'entries'         => $entries,
            'summary'         => $summary,
            'hasQuery'        => $hasQuery,
            'filters'         => $request->only([
                'report_type',
                'date_from',
                'date_to',
                'unit_id',
                'tenant_id',
                'status',
                'landlord_id',
                'payment_method',
                'payment_account_id',
                'unit_status',
                'owner_type',
            ]),
        ]);
    }

    // -------------------------------------------------------------------------
    // Export Excel
    // -------------------------------------------------------------------------

    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $reportType = $request->report_type ?? 'all';
        if ($reportType === 'monthly_matrix') {
            $entries = $this->buildMatrixEntries($request);
            $summary = $this->buildMatrixSummary($entries);
        } else {
            $entries  = $this->buildEntries($request);
            $summary  = $this->buildSummary($entries);
        }
        $label    = $this->reportLabel($request);
        $filename = 'report-' . str($label)->slug() . '-' . now()->format('Y-m-d') . '.xlsx';

        \App\Models\ActivityLog::log('export_excel', "Exported report to Excel: {$filename}", null, [
            'report_type' => $request->report_type,
            'filters' => $request->all(),
        ]);

        return Excel::download(
            new ReportExport($entries, $label, $summary, $reportType),
            $filename
        );
    }

    // -------------------------------------------------------------------------
    // Export PDF
    // -------------------------------------------------------------------------

    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $reportType = $request->report_type ?? 'all';
        if ($reportType === 'monthly_matrix') {
            $entries = $this->buildMatrixEntries($request);
            $summary = $this->buildMatrixSummary($entries);
        } else {
            $entries = $this->buildEntries($request);
            $summary = $this->buildSummary($entries);
        }
        $label   = $this->reportLabel($request);
        $filters = $request->only([
            'date_from', 'date_to', 'unit_id', 'tenant_id', 'status', 'report_type',
            'landlord_id', 'payment_method', 'payment_account_id', 'unit_status', 'owner_type'
        ]);

        if ($reportType === 'monthly_matrix') {
            $month = ($filters['date_from'] ?? false) ? \Carbon\Carbon::parse($filters['date_from'])->format('F Y') : \Carbon\Carbon::now()->format('F Y');
            $period = $month;
        } else {
            $period = ($filters['date_from'] ?? false) || ($filters['date_to'] ?? false)
                ? ($filters['date_from'] ?? '—') . ' to ' . ($filters['date_to'] ?? '—')
                : 'All time';
        }

        $pdf = Pdf::loadView('reports.pdf', [
            'entries'    => $entries,
            'summary'    => $summary,
            'label'      => $label,
            'period'     => $period,
            'filters'    => $filters,
            'reportType' => $reportType,
            'paymentAccounts' => PaymentAccount::orderBy('name')->get(['id', 'name']),
        ])->setPaper('a4', 'landscape');

        $filename = 'report-' . str($label)->slug() . '-' . now()->format('Y-m-d') . '.pdf';

        \App\Models\ActivityLog::log('export_pdf', "Exported report to PDF: {$filename}", null, [
            'report_type' => $request->report_type,
            'filters' => $filters,
        ]);

        return $pdf->download($filename);
    }

    // -------------------------------------------------------------------------
    // Core: build unified entries collection
    // -------------------------------------------------------------------------

    private function buildEntries(Request $request): Collection
    {
        $reportType = $request->report_type ?? 'all';   // rent | fines | utilities | all
        $from       = $request->date_from;
        $to         = $request->date_to;
        $unitId     = $request->unit_id;
        $tenantId   = $request->tenant_id;
        $status     = $request->status;
        $landlordId = $request->landlord_id;
        $paymentMethod = $request->payment_method;
        $paymentAccountId = $request->payment_account_id;
        $unitStatus = $request->unit_status;
        $ownerType  = $request->owner_type;

        $query = Payment::with(['tenant', 'unit.landlord', 'paymentAccount', 'otherTenant'])
            ->when($unitId,           fn($q) => $q->where('unit_id',   $unitId))
            ->when($tenantId,         fn($q) => $q->where('tenant_id', $tenantId))
            ->when($from,             fn($q) => $q->where('month', '>=', $from))
            ->when($to,               fn($q) => $q->where('month', '<=', $to))
            ->when($status,           fn($q) => $q->where('status', $status))
            ->when($paymentMethod,    fn($q) => $q->where('payment_method', $paymentMethod))
            ->when($paymentAccountId, fn($q) => $q->where('payment_account_id', $paymentAccountId))
            ->when($landlordId,       fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('landlord_id', $landlordId)))
            ->when($unitStatus,       fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('status', $unitStatus)))
            ->when($ownerType === 'pm_mall', fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('is_self', false)))
            ->when($ownerType === 'other' || $reportType === 'other_owned', fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('is_self', true)))
            ->when($reportType !== 'other_owned', function ($q) {
                $q->where(function ($sq) {
                    $sq->whereHas('unit', function ($qu) {
                        $qu->where('is_self', true)->whereHas('otherTenant');
                    })->orWhereHas('unit', function ($qu) {
                        $qu->where('is_self', false);
                    });
                });
            });

        if ($reportType === 'rent') {
            $query->where('type', 'rent');
        } elseif ($reportType === 'fines') {
            $query->where('type', 'fine');
        } elseif ($reportType === 'utilities') {
            $query->whereIn('type', ['electricity', 'water', 'gas']);
        } elseif ($reportType === 'maintinance' || $reportType === 'maintenance') {
            $query->where('type', 'maintenance');
        } elseif ($reportType === 'other_owned') {
            $query->whereHas('unit', fn($qu) => $qu->where('is_self', true));
        } else {
            $query->whereIn('type', ['rent', 'fine', 'maintenance', 'electricity', 'water', 'gas', 'other']);
        }

        $dbPayments = $query->get();

        $entries = $dbPayments->map(fn($p) => [
            'created_date'    => $p->created_at,
            'voucher_number'  => $p->receipt_no ?? ('PM-PAY-' . str_pad($p->id, 5, '0', STR_PAD_LEFT)),
            'month'           => $p->month,
            'date'            => $p->due_date,
            'unit'            => $p->unit?->unit_number ?? '—',
            'tenant'          => $p->tenant?->name ?? ($p->otherTenant?->name ?? '—'),
            'landlord'        => $p->unit?->landlord?->name ?? '—',
            'payment_method'  => $p->payment_method ? ucfirst(str_replace('_', ' ', $p->payment_method)) : '—',
            'payment_account' => $p->paymentAccount?->name ?? '—',
            'category'        => in_array($p->type, ['electricity', 'water', 'gas']) ? 'utility' : 'payment',
            'type'            => $p->type,
            'description'     => $p->type_label . ' — ' . $p->month?->format('F Y'),
            'amount_due'      => (float) $p->amount,
            'amount_paid'     => (float) $p->amount_paid,
            'status'          => $p->status,
            'paid_at'         => $p->paid_at,
            'is_self'         => (bool) ($p->unit?->is_self),
        ]);

        // Projections: only run if payment-specific filters are NOT set
        $runProjections = !$request->filled('payment_method')
            && !$request->filled('payment_account_id')
            && !$request->filled('status');

        if ($runProjections && ($reportType === 'all' || $reportType === 'rent' || $reportType === 'maintinance' || $reportType === 'maintenance' || $reportType === 'other_owned')) {
            $startMonth = $from ? \Carbon\Carbon::parse($from)->startOfMonth() : \Carbon\Carbon::now()->startOfMonth();
            $endMonth = $to ? \Carbon\Carbon::parse($to)->startOfMonth() : \Carbon\Carbon::now()->startOfMonth();

            if ($startMonth->lte($endMonth)) {
                $monthsToProject = [];
                $current = $startMonth->copy();
                $limit = 0;
                while ($current->lte($endMonth) && $limit < 36) {
                    $monthsToProject[] = $current->copy();
                    $current->addMonth();
                    $limit++;
                }

                // Index existing payments by: unit_id, tenant_id, type, month
                $existingKeys = [];
                foreach ($dbPayments as $p) {
                    $monthStr = $p->month ? $p->month->format('Y-m-d') : '';
                    $key = "{$p->unit_id}_{$p->tenant_id}_{$p->type}_{$monthStr}";
                    $existingKeys[$key] = true;
                }

                foreach ($monthsToProject as $month) {
                    $monthStr = $month->format('Y-m-d');

                    $activeAgreements = \App\Models\Agreement::where('status', 'active')
                        ->when($unitId,     fn($q) => $q->where('unit_id', $unitId))
                        ->when($tenantId,   fn($q) => $q->where('tenant_id', $tenantId))
                        ->when($landlordId, fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('landlord_id', $landlordId)))
                        ->when($unitStatus, fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('status', $unitStatus)))
                        ->when($ownerType === 'pm_mall', fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('is_self', false)))
                        ->when($ownerType === 'other' || $reportType === 'other_owned', fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('is_self', true)))
                        ->where('start_date', '<=', $month->copy()->endOfMonth())
                        ->where('end_date', '>=', $month->copy()->startOfMonth())
                        ->with(['tenant', 'unit.landlord'])
                        ->get();

                    foreach ($activeAgreements as $agreement) {
                        $dueDay = min($agreement->payment_due_day ?: 1, $month->daysInMonth);
                        $dueDate = $month->copy()->day($dueDay);

                        // Rent projection
                        if ($reportType === 'all' || $reportType === 'rent') {
                            $rentKey = "{$agreement->unit_id}_{$agreement->tenant_id}_rent_{$monthStr}";
                            if (!isset($existingKeys[$rentKey])) {
                                $entries->push([
                                    'created_date'    => null,
                                    'voucher_number'  => 'PM-PAY-PROJ',
                                    'month'           => $month->copy(),
                                    'date'            => $dueDate,
                                    'unit'            => $agreement->unit?->unit_number ?? '—',
                                    'tenant'          => $agreement->tenant?->name ?? '—',
                                    'landlord'        => $agreement->unit?->landlord?->name ?? '—',
                                    'payment_method'  => '—',
                                    'payment_account' => '—',
                                    'category'        => 'payment',
                                    'type'            => 'rent',
                                    'description'     => 'Rent — ' . $month->format('F Y') . ' (Projected)',
                                    'amount_due'      => (float) $agreement->monthly_rent,
                                    'amount_paid'     => 0.0,
                                    'status'          => 'pending',
                                    'paid_at'         => null,
                                    'is_self'         => (bool) ($agreement->unit?->is_self),
                                ]);
                            }
                        }

                        // Maintenance projection
                        if (($reportType === 'all' || $reportType === 'maintinance' || $reportType === 'maintenance' || $reportType === 'other_owned') && $agreement->maintenance_charge > 0) {
                            $maintKey = "{$agreement->unit_id}_{$agreement->tenant_id}_maintenance_{$monthStr}";
                            if (!isset($existingKeys[$maintKey])) {
                                $entries->push([
                                    'created_date'    => null,
                                    'voucher_number'  => 'PM-PAY-PROJ',
                                    'month'           => $month->copy(),
                                    'date'            => $dueDate,
                                    'unit'            => $agreement->unit?->unit_number ?? '—',
                                    'tenant'          => $agreement->tenant?->name ?? '—',
                                    'landlord'        => $agreement->unit?->landlord?->name ?? '—',
                                    'payment_method'  => '—',
                                    'payment_account' => '—',
                                    'category'        => 'payment',
                                    'type'            => 'maintenance',
                                    'description'     => 'Maintenance — ' . $month->format('F Y') . ' (Projected)',
                                    'amount_due'      => (float) $agreement->maintenance_charge,
                                    'amount_paid'     => 0.0,
                                    'status'          => 'pending',
                                    'paid_at'         => null,
                                    'is_self'         => (bool) ($agreement->unit?->is_self),
                                ]);
                            }
                        }
                    }
                }
            }
        }

        $balance = 0;

        return $entries
            ->sortBy('date')
            ->values()
            ->map(function ($entry) use (&$balance) {
                $balance += $entry['amount_due'] - $entry['amount_paid'];
                $entry['balance'] = $balance;
                return $entry;
            });
    }

    // -------------------------------------------------------------------------
    // Summary totals
    // -------------------------------------------------------------------------

    private function buildSummary(Collection $entries): array
    {
        $rentCollected        = $entries->where('type', 'rent')->sum('amount_paid');
        $maintenanceCollected = $entries->where('type', 'maintenance')->sum('amount_paid');
        $utilitiesPaid        = $entries->where('category', 'utility')->sum('amount_paid');
        $finesCollected       = $entries->where('type', 'fine')->sum('amount_paid');

        // Sum amount_paid grouped by payment_account name (ignoring empty/hyphen entries)
        $accountSummaries = $entries->groupBy('payment_account')
            ->map(fn($group) => $group->sum('amount_paid'))
            ->filter(fn($total, $account) => $account !== '—' && $total > 0)
            ->toArray();

        return [
            'total_due'             => $entries->sum('amount_due'),
            'total_paid'            => $entries->sum('amount_paid'),
            'outstanding'           => $entries->sum('amount_due') - $entries->sum('amount_paid'),
            'count'                 => $entries->count(),
            'rent_collected'        => $rentCollected,
            'maintenance_collected' => $maintenanceCollected,
            'utilities_paid'        => $utilitiesPaid,
            'fines_collected'       => $finesCollected,
            'accounts_summary'      => $accountSummaries,
        ];
    }

    // -------------------------------------------------------------------------
    // Monthly Matrix Builders
    // -------------------------------------------------------------------------

    private function buildMatrixEntries(Request $request): Collection
    {
        $from = $request->date_from;
        $month = $from ? \Carbon\Carbon::parse($from)->startOfMonth() : \Carbon\Carbon::now()->startOfMonth();
        $monthStr = $month->format('Y-m-d');
        $unitStatus = $request->unit_status;
        $ownerType  = $request->owner_type;

        $paymentAccounts = PaymentAccount::orderBy('name')->get(['id', 'name']);
        
        $units = Unit::with(['landlord', 'otherTenant'])
            ->when($unitStatus, fn($q) => $q->where('status', $unitStatus))
            ->when($ownerType === 'pm_mall', fn($q) => $q->where('is_self', false))
            ->when($ownerType === 'other',    fn($q) => $q->where('is_self', true))
            ->orderBy('unit_number')
            ->get();

        $agreements = Agreement::where('status', 'active')
            ->where('start_date', '<=', $month->copy()->endOfMonth())
            ->where('end_date', '>=', $month->copy()->startOfMonth())
            ->with(['tenant'])
            ->get()
            ->groupBy('unit_id');

        $payments = Payment::where('month', $monthStr)
            ->with(['paymentAccount'])
            ->get()
            ->groupBy('unit_id');

        $matrixEntries = collect();

        foreach ($units as $index => $unit) {
            $agreement = $agreements->get($unit->id)?->first();
            $unitPayments = $payments->get($unit->id) ?? collect();

            if ($unit->is_self && !$unit->otherTenant) {
                $unitPayments = collect();
            }

            if ($unit->is_self && $unit->otherTenant) {
                $status = 'OCCUPIED';
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
                $rent_paid = (float) $rentPayment->amount_paid;
            } elseif ($agreement) {
                $rent_due = (float) $agreement->monthly_rent;
                $rent_paid = 0.0;
            } else {
                $rent_due = 0.0;
                $rent_paid = 0.0;
            }

            // Services (Maintenance)
            $maintPayment = $unitPayments->where('type', 'maintenance')->first();
            if ($maintPayment) {
                $serv_due = (float) $maintPayment->amount;
                $serv_paid = (float) $maintPayment->amount_paid;
            } elseif ($agreement && $agreement->maintenance_charge > 0) {
                $serv_due = (float) $agreement->maintenance_charge;
                $serv_paid = 0.0;
            } else {
                $serv_due = 0.0;
                $serv_paid = 0.0;
            }

            // Extra
            $extraPayments = $unitPayments->whereNotIn('type', ['rent', 'maintenance']);
            $extra_due = (float) $extraPayments->sum('amount');
            $extra_paid = (float) $extraPayments->sum('amount_paid');

            $total_due = $serv_due + $extra_due + $rent_due;
            $total_received = $serv_paid + $extra_paid + $rent_paid;
            $pending = max(0.0, $total_due - $total_received);

            $accountsBreakdown = [];
            foreach ($paymentAccounts as $account) {
                $accountsBreakdown[$account->name] = (float) $unitPayments->where('payment_account_id', $account->id)->sum('amount_paid');
            }

            $vouchers = [];
            $dates = [];
            foreach ($unitPayments as $p) {
                if ($p->status === 'paid' || $p->amount_paid > 0) {
                    $vouchers[] = $p->receipt_no ?? ('PM-PAY-' . str_pad($p->id, 5, '0', STR_PAD_LEFT));
                    if ($p->paid_at) {
                        $dates[] = $p->paid_at->format('d/m');
                    }
                }
            }

            $datesString = !empty($dates) ? implode(', ', array_unique($dates)) : '';
            $vouchersString = !empty($vouchers) ? implode('/', array_unique($vouchers)) : '';

            $matrixEntries->push([
                'sr'               => $index + 1,
                'date'             => $datesString,
                'rsv'              => $vouchersString,
                'flat_no'          => $unit->unit_number,
                'owner'            => $unit->landlord?->name ?? '—',
                'status'           => $status,
                'serv'             => $serv_due,
                'extra'            => $extra_due,
                'rent'             => $rent_due,
                'total_amount'     => $total_due,
                'received'         => $total_received,
                'payment_accounts' => $accountsBreakdown,
                'pending'          => $pending,
                'is_self'          => (bool) $unit->is_self,
            ]);
        }

        return $matrixEntries;
    }

    private function buildMatrixSummary(Collection $matrixEntries): array
    {
        $paymentAccounts = PaymentAccount::orderBy('name')->get(['id', 'name']);

        $accountsTotal = [];
        foreach ($paymentAccounts as $account) {
            $accountsTotal[$account->name] = $matrixEntries->sum(function ($e) use ($account) {
                return $e['payment_accounts'][$account->name] ?? 0.0;
            });
        }

        return [
            'total_serv'     => $matrixEntries->sum('serv'),
            'total_extra'    => $matrixEntries->sum('extra'),
            'total_rent'     => $matrixEntries->sum('rent'),
            'total_amount'   => $matrixEntries->sum('total_amount'),
            'total_received' => $matrixEntries->sum('received'),
            'accounts_total' => $accountsTotal,
            'total_pending'  => $matrixEntries->sum('pending'),
            'count'          => $matrixEntries->count(),
        ];
    }

    // -------------------------------------------------------------------------
    // Human-readable report label for filenames / headings
    // -------------------------------------------------------------------------

    private function reportLabel(Request $request): string
    {
        return match ($request->report_type ?? 'all') {
            'rent'                       => 'Rent Collected',
            'fines'                      => 'Fines',
            'utilities'                  => 'Utilities Paid',
            'monthly_matrix'             => 'Monthly Matrix',
            'maintinance', 'maintenance' => 'Maintenance',
            'other_owned'                => 'Other Owned Payments',
            default                      => 'Full Report',
        };
    }
}
