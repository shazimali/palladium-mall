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
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\MatrixService;

class ReportController extends Controller
{
    protected MatrixService $matrixService;

    public function __construct(MatrixService $matrixService)
    {
        $this->matrixService = $matrixService;
    }

    // -------------------------------------------------------------------------
    // Main Reports Page
    // -------------------------------------------------------------------------

    public function index(Request $request): View
    {
        $this->prepareMatrixDate($request);

        $tenants = Tenant::orderBy('name')->get(['id', 'name']);
        $units = Unit::orderBy('unit_number')->get(['id', 'unit_number', 'type']);
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
            if (in_array($request->report_type, ['monthly_matrix', 'monthly_matrix_expected'])) {
                $entries = $this->buildMatrixEntries($request);
                $summary = $this->buildMatrixSummary($entries);
            } elseif ($request->report_type === 'security_deposit_matrix') {
                $entries = $this->matrixService->buildSecurityDepositMatrixEntries($request);
                $summary = $this->matrixService->buildSecurityDepositMatrixSummary($entries);
            } elseif ($request->report_type === 'potential_revenue') {
                $entries = $this->buildPotentialRevenueEntries($request);
                $summary = $this->buildPotentialRevenueSummary($entries);
            } else {
                $entries = $this->buildEntries($request);
                $summary = $this->buildSummary($entries, $request);
            }
        }

        return view('reports.index', [
            'title' => 'Reports',
            'tenants' => $tenants,
            'units' => $units,
            'landlords' => $landlords,
            'paymentAccounts' => $paymentAccounts,
            'entries' => $entries,
            'summary' => $summary,
            'hasQuery' => $hasQuery,
            'filters' => $request->only([
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
        $this->prepareMatrixDate($request);

        $reportType = $request->report_type ?? 'all';
        if (in_array($reportType, ['monthly_matrix', 'monthly_matrix_expected'])) {
            $entries = $this->buildMatrixEntries($request);
            $summary = $this->buildMatrixSummary($entries);
        } elseif ($reportType === 'security_deposit_matrix') {
            $entries = $this->matrixService->buildSecurityDepositMatrixEntries($request);
            $summary = $this->matrixService->buildSecurityDepositMatrixSummary($entries);
        } elseif ($reportType === 'potential_revenue') {
            $entries = $this->buildPotentialRevenueEntries($request);
            $summary = $this->buildPotentialRevenueSummary($entries);
        } else {
            $entries = $this->buildEntries($request);
            $summary = $this->buildSummary($entries, $request);
        }
        $label = $this->reportLabel($request);
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
        $this->prepareMatrixDate($request);

        $reportType = $request->report_type ?? 'all';
        if (in_array($reportType, ['monthly_matrix', 'monthly_matrix_expected'])) {
            $entries = $this->buildMatrixEntries($request);
            $summary = $this->buildMatrixSummary($entries);
        } elseif ($reportType === 'security_deposit_matrix') {
            $entries = $this->matrixService->buildSecurityDepositMatrixEntries($request);
            $summary = $this->matrixService->buildSecurityDepositMatrixSummary($entries);
        } elseif ($reportType === 'potential_revenue') {
            $entries = $this->buildPotentialRevenueEntries($request);
            $summary = $this->buildPotentialRevenueSummary($entries);
        } else {
            $entries = $this->buildEntries($request);
            $summary = $this->buildSummary($entries, $request);
        }
        $label = $this->reportLabel($request);
        $filters = $request->only([
            'date_from',
            'date_to',
            'unit_id',
            'tenant_id',
            'status',
            'report_type',
            'landlord_id',
            'payment_method',
            'payment_account_id',
            'unit_status',
            'owner_type'
        ]);

        if (in_array($reportType, ['monthly_matrix', 'monthly_matrix_expected'])) {
            $month = ($filters['date_from'] ?? false) ? \Carbon\Carbon::parse($filters['date_from'])->format('F Y') : \Carbon\Carbon::now()->format('F Y');
            $period = $month;
        } else {
            $period = ($filters['date_from'] ?? false) || ($filters['date_to'] ?? false)
                ? ($filters['date_from'] ?? '—') . ' to ' . ($filters['date_to'] ?? '—')
                : 'All time';
        }

        $pdf = Pdf::loadView('reports.pdf', [
            'entries' => $entries,
            'summary' => $summary,
            'label' => $label,
            'period' => $period,
            'filters' => $filters,
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
    // Print View (New Window)
    // -------------------------------------------------------------------------

    public function print(Request $request): View
    {
        $this->prepareMatrixDate($request);

        $reportType = $request->report_type ?? 'all';
        if (in_array($reportType, ['monthly_matrix', 'monthly_matrix_expected'])) {
            $entries = $this->buildMatrixEntries($request);
            $summary = $this->buildMatrixSummary($entries);
        } elseif ($reportType === 'security_deposit_matrix') {
            $entries = $this->matrixService->buildSecurityDepositMatrixEntries($request);
            $summary = $this->matrixService->buildSecurityDepositMatrixSummary($entries);
        } elseif ($reportType === 'potential_revenue') {
            $entries = $this->buildPotentialRevenueEntries($request);
            $summary = $this->buildPotentialRevenueSummary($entries);
        } else {
            $entries = $this->buildEntries($request);
            $summary = $this->buildSummary($entries, $request);
        }
        $label = $this->reportLabel($request);
        $filters = $request->only([
            'date_from',
            'date_to',
            'unit_id',
            'tenant_id',
            'status',
            'report_type',
            'landlord_id',
            'payment_method',
            'payment_account_id',
            'unit_status',
            'owner_type'
        ]);

        if ($reportType === 'monthly_matrix') {
            $month = ($filters['date_from'] ?? false) ? \Carbon\Carbon::parse($filters['date_from'])->format('F Y') : \Carbon\Carbon::now()->format('F Y');
            $period = $month;
        } else {
            $period = ($filters['date_from'] ?? false) || ($filters['date_to'] ?? false)
                ? ($filters['date_from'] ?? '—') . ' to ' . ($filters['date_to'] ?? '—')
                : 'All time';
        }

        return view('reports.pdf', [
            'entries' => $entries,
            'summary' => $summary,
            'label' => $label,
            'period' => $period,
            'filters' => $filters,
            'reportType' => $reportType,
            'paymentAccounts' => PaymentAccount::orderBy('name')->get(['id', 'name']),
            'isPrint' => true,
        ]);
    }


    // -------------------------------------------------------------------------
    // Core: build unified entries collection
    // -------------------------------------------------------------------------

    private function buildEntries(Request $request): Collection
    {
        $reportType = $request->report_type ?? 'all';   // rent | fines | utilities | all
        $from = $request->date_from;
        $to = $request->date_to;
        $unitId = $request->unit_id;
        $tenantId = $request->tenant_id;
        $status = $request->status;
        $landlordId = $request->landlord_id;
        $paymentMethod = $request->payment_method;
        $paymentAccountId = $request->payment_account_id;
        $unitStatus = $request->unit_status;
        $ownerType = $request->owner_type;

        $query = Payment::with(['tenant', 'unit.landlord', 'paymentAccount', 'otherTenant', 'receivingVouchers'])
            ->when($unitId, fn($q) => $q->where('unit_id', $unitId))
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($from, fn($q) => $q->where('month', '>=', $from))
            ->when($to, fn($q) => $q->where('month', '<=', $to))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($paymentMethod, fn($q) => $q->where('payment_method', $paymentMethod))
            ->when($paymentAccountId, fn($q) => $q->where('payment_account_id', $paymentAccountId))
            ->when($landlordId, fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('landlord_id', $landlordId)))
            ->when($unitStatus, fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('status', $unitStatus)));

        // Owner type filters
        if ($ownerType === 'pm_mall') {
            $query->whereHas('unit', fn($qu) => $qu->where('is_self', false));
        } elseif ($ownerType === 'other') {
            $query->whereHas('unit', fn($qu) => $qu->where('is_self', true));
        }

        // Apply Report Type specific unit constraints
        if ($reportType === 'occupied' || $reportType === 'occupide') {
            $query->whereHas('unit', fn($qu) => $qu->where('is_self', true)->whereHas('otherTenant'));
        } elseif ($reportType === 'non_occupied' || $reportType === 'non_occupide') {
            $query->whereHas('unit', fn($qu) => $qu->where('is_self', true)->whereDoesntHave('otherTenant'));
        } elseif ($reportType === 'other_owned') {
            $query->whereHas('unit', fn($qu) => $qu->where('is_self', true));
        } else {
            // Default base safety filter
            if (!$ownerType) {
                $query->where(function ($sq) {
                    $sq->whereHas('unit', function ($qu) {
                        $qu->where('is_self', true)->whereHas('otherTenant');
                    })->orWhereHas('unit', function ($qu) {
                        $qu->where('is_self', false);
                    });
                });
            }
        }

        if ($reportType === 'rent') {
            $query->where('type', 'rent');
        } elseif ($reportType === 'fines') {
            $query->where('type', 'fine');
        } elseif ($reportType === 'utilities') {
            $query->whereIn('type', ['electricity', 'water', 'gas']);
        } elseif ($reportType === 'maintinance' || $reportType === 'maintenance') {
            $query->where('type', 'maintenance');
        } elseif ($reportType === 'security_deposit') {
            $query->where('type', 'security_deposit');
        } elseif ($reportType === 'extra_payments') {
            $query->whereIn('type', ['extra_payment', 'other', 'fine']);
        } else {
            $query->whereIn('type', ['rent', 'fine', 'maintenance', 'electricity', 'water', 'gas', 'other', 'extra_payment', 'security_deposit']);
        }

        $dbPayments = $query->get();

        $agreements = \App\Models\Agreement::where('status', 'active')->get();

        $entries = $dbPayments->map(function ($p) {
            $securityDeposit = ($p->type === 'security_deposit') ? (float) $p->amount : 0.0;

            return [
                'created_date' => $p->created_at,
                'voucher_number' => $p->receivingVouchers->isNotEmpty()
                    ? implode(', ', $p->receivingVouchers->pluck('voucher_no')->toArray())
                    : ($p->receipt_no ?? ('PM-PAY-' . str_pad($p->id, 5, '0', STR_PAD_LEFT))),
                'month' => $p->month,
                'date' => $p->due_date,
                'unit' => $p->unit?->unit_number ?? '—',
                'tenant' => $p->tenant?->name ?? ($p->otherTenant?->name ?? '—'),
                'landlord' => $p->unit?->landlord?->name ?? '—',
                'payment_method' => $p->payment_method ? ucfirst(str_replace('_', ' ', $p->payment_method)) : '—',
                'payment_account' => $p->paymentAccount?->name ?? '—',
                'category' => in_array($p->type, ['electricity', 'water', 'gas']) ? 'utility' : 'payment',
                'type' => $p->type,
                'description' => $p->type_label . ' — ' . $p->month?->format('F Y'),
                'amount_due' => (float) $p->amount,
                'amount_paid' => (float) $p->amount_paid,
                'status' => $p->status,
                'paid_at' => $p->paid_at,
                'is_self' => (bool) ($p->unit?->is_self),
                'security_deposit' => $securityDeposit,
            ];
        });

        // Projections: only run if payment-specific filters are NOT set
        $runProjections = !$request->filled('payment_method')
            && !$request->filled('payment_account_id')
            && !$request->filled('status');

        if (
            $runProjections && (
                $reportType === 'all' ||
                $reportType === 'maintinance' ||
                $reportType === 'maintenance' ||
                $reportType === 'other_owned' ||
                $reportType === 'occupied' ||
                $reportType === 'occupide' ||
                $reportType === 'non_occupied' ||
                $reportType === 'non_occupide'
            )
        ) {
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

                // Pluck all agreement IDs that already have a security deposit payment record in the DB
                $existingSecurityDepositAgreementIds = \App\Models\Payment::where('type', 'security_deposit')
                    ->pluck('agreement_id')
                    ->toArray();

                $projectSelfUnits = collect();
                if (
                    $ownerType !== 'pm_mall' && (
                        $reportType === 'all' ||
                        $reportType === 'maintinance' ||
                        $reportType === 'maintenance' ||
                        $reportType === 'other_owned' ||
                        $reportType === 'occupied' ||
                        $reportType === 'occupide' ||
                        $reportType === 'non_occupied' ||
                        $reportType === 'non_occupide'
                    )
                ) {
                    $projectSelfUnits = \App\Models\Unit::where('is_self', true)
                        ->where('default_maintenance_charge', '>', 0)
                        ->when($unitId, fn($q) => $q->where('id', $unitId))
                        ->when($landlordId, fn($q) => $q->where('landlord_id', $landlordId))
                        ->when($unitStatus, fn($q) => $q->where('status', $unitStatus))
                        ->when($reportType === 'occupied' || $reportType === 'occupide', fn($q) => $q->whereHas('otherTenant'))
                        ->when($reportType === 'non_occupied' || $reportType === 'non_occupide', fn($q) => $q->whereDoesntHave('otherTenant'))
                        ->with(['otherTenant', 'landlord'])
                        ->get();
                }

                foreach ($monthsToProject as $month) {
                    $monthStr = $month->format('Y-m-d');

                    $activeAgreements = \App\Models\Agreement::where('status', 'active')
                        ->when($unitId, fn($q) => $q->where('unit_id', $unitId))
                        ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                        ->when($landlordId, fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('landlord_id', $landlordId)))
                        ->when($unitStatus, fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('status', $unitStatus)))
                        ->when($ownerType === 'pm_mall', fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('is_self', false)))
                        ->when($ownerType === 'other' || $reportType === 'other_owned', fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('is_self', true)))
                        ->when($reportType === 'occupied' || $reportType === 'occupide', fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('is_self', true)->whereHas('otherTenant')))
                        ->when($reportType === 'non_occupied' || $reportType === 'non_occupide', fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('is_self', true)->whereDoesntHave('otherTenant')))
                        ->where('start_date', '<=', $month->copy()->endOfMonth())
                        ->where('end_date', '>=', $month->copy()->startOfMonth())
                        ->with(['tenant', 'unit.landlord'])
                        ->get();

                    foreach ($activeAgreements as $agreement) {
                        $dueDay = min($agreement->payment_due_day ?: 1, $month->daysInMonth);
                        $dueDate = $month->copy()->day($dueDay);

                        // Rent projection (only for 'all'; report_type=rent shows DB billings only)
                        if ($reportType === 'all') {
                            $rentKey = "{$agreement->unit_id}_{$agreement->tenant_id}_rent_{$monthStr}";
                            if (!isset($existingKeys[$rentKey])) {
                                $rentMonthStr = $month->format('Y-m');
                                $startMonthStr = $agreement->start_date ? $agreement->start_date->format('Y-m') : '';
                                $hasExistingPayment = in_array($agreement->id, $existingSecurityDepositAgreementIds);
                                $securityDeposit = ($rentMonthStr === $startMonthStr && !$hasExistingPayment) ? (float) $agreement->security_deposit : 0.0;

                                $entries->push([
                                    'created_date' => null,
                                    'voucher_number' => 'PM-PAY-PROJ',
                                    'month' => $month->copy(),
                                    'date' => $dueDate,
                                    'unit' => $agreement->unit?->unit_number ?? '—',
                                    'tenant' => $agreement->tenant?->name ?? '—',
                                    'landlord' => $agreement->unit?->landlord?->name ?? '—',
                                    'payment_method' => '—',
                                    'payment_account' => '—',
                                    'category' => 'payment',
                                    'type' => 'rent',
                                    'description' => 'Rent — ' . $month->format('F Y') . ' (Projected)',
                                    'amount_due' => (float) $agreement->monthly_rent,
                                    'amount_paid' => 0.0,
                                    'status' => 'pending',
                                    'paid_at' => null,
                                    'is_self' => (bool) ($agreement->unit?->is_self),
                                    'security_deposit' => $securityDeposit,
                                ]);
                            }
                        }

                        // Maintenance projection
                        if (($reportType === 'all' || $reportType === 'maintinance' || $reportType === 'maintenance' || $reportType === 'other_owned') && $agreement->maintenance_charge > 0) {
                            $maintKey = "{$agreement->unit_id}_{$agreement->tenant_id}_maintenance_{$monthStr}";
                            if (!isset($existingKeys[$maintKey])) {
                                $entries->push([
                                    'created_date' => null,
                                    'voucher_number' => 'PM-PAY-PROJ',
                                    'month' => $month->copy(),
                                    'date' => $dueDate,
                                    'unit' => $agreement->unit?->unit_number ?? '—',
                                    'tenant' => $agreement->tenant?->name ?? '—',
                                    'landlord' => $agreement->unit?->landlord?->name ?? '—',
                                    'payment_method' => '—',
                                    'payment_account' => '—',
                                    'category' => 'payment',
                                    'type' => 'maintenance',
                                    'description' => 'Maintenance — ' . $month->format('F Y') . ' (Projected)',
                                    'amount_due' => (float) $agreement->maintenance_charge,
                                    'amount_paid' => 0.0,
                                    'status' => 'pending',
                                    'paid_at' => null,
                                    'is_self' => (bool) ($agreement->unit?->is_self),
                                    'security_deposit' => 0.0,
                                ]);
                            }
                        }
                    }

                    // Self Units maintenance projections
                    foreach ($projectSelfUnits as $selfUnit) {
                        $maintKey = "{$selfUnit->id}__maintenance_{$monthStr}";
                        if (!isset($existingKeys[$maintKey])) {
                            $selfDueDate = $month->copy()->day(min(10, $month->daysInMonth));
                            $entries->push([
                                'created_date' => null,
                                'voucher_number' => 'PM-PAY-PROJ',
                                'month' => $month->copy(),
                                'date' => $selfDueDate,
                                'unit' => $selfUnit->unit_number,
                                'tenant' => $selfUnit->otherTenant?->name ?? '—',
                                'landlord' => $selfUnit->landlord?->name ?? '—',
                                'payment_method' => '—',
                                'payment_account' => '—',
                                'category' => 'payment',
                                'type' => 'maintenance',
                                'description' => 'Maintenance — ' . $month->format('F Y') . ' (Projected)',
                                'amount_due' => (float) $selfUnit->default_maintenance_charge,
                                'amount_paid' => 0.0,
                                'status' => 'pending',
                                'paid_at' => null,
                                'is_self' => true,
                                'security_deposit' => 0.0,
                            ]);
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

    private function buildSummary(Collection $entries, ?Request $request = null): array
    {
        $rentCollected = $entries->where('type', 'rent')->sum('amount_paid');
        $maintenanceCollected = $entries->where('type', 'maintenance')->sum('amount_paid');
        $utilitiesPaid = $entries->where('category', 'utility')->sum('amount_paid');
        $finesCollected = $entries->where('type', 'fine')->sum('amount_paid');

        // Sum amount_paid grouped by payment_account name (ignoring empty/hyphen entries)
        $accountSummaries = $entries->groupBy('payment_account')
            ->map(fn($group) => $group->sum('amount_paid'))
            ->filter(fn($total, $account) => $account !== '—' && $total > 0)
            ->toArray();

        $prevUnpaidRent = 0.0;
        $prevUnpaidServ = 0.0;
        $prevUnpaidSec = 0.0;
        $prevUnpaidExtra = 0.0;
        $prevUnpaidTotal = 0.0;

        if ($request && $request->filled('date_from')) {
            $reportType = $request->report_type ?? 'all';
            $from = $request->date_from;
            $unitId = $request->unit_id;
            $tenantId = $request->tenant_id;
            $landlordId = $request->landlord_id;
            $paymentMethod = $request->payment_method;
            $paymentAccountId = $request->payment_account_id;
            $unitStatus = $request->unit_status;
            $ownerType = $request->owner_type;

            $prevQuery = Payment::where('month', '<', $from)
                ->whereRaw('amount > amount_paid')
                ->when($unitId, fn($q) => $q->where('unit_id', $unitId))
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->when($paymentMethod, fn($q) => $q->where('payment_method', $paymentMethod))
                ->when($paymentAccountId, fn($q) => $q->where('payment_account_id', $paymentAccountId))
                ->when($landlordId, fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('landlord_id', $landlordId)))
                ->when($unitStatus, fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('status', $unitStatus)));

            if ($ownerType === 'pm_mall') {
                $prevQuery->whereHas('unit', fn($qu) => $qu->where('is_self', false));
            } elseif ($ownerType === 'other') {
                $prevQuery->whereHas('unit', fn($qu) => $qu->where('is_self', true));
            }

            if ($reportType === 'occupied' || $reportType === 'occupide') {
                $prevQuery->whereHas('unit', fn($qu) => $qu->where('is_self', true)->whereHas('otherTenant'));
            } elseif ($reportType === 'non_occupied' || $reportType === 'non_occupide') {
                $prevQuery->whereHas('unit', fn($qu) => $qu->where('is_self', true)->whereDoesntHave('otherTenant'));
            } elseif ($reportType === 'other_owned') {
                $prevQuery->whereHas('unit', fn($qu) => $qu->where('is_self', true));
            } else {
                if (!$ownerType) {
                    $prevQuery->where(function ($sq) {
                        $sq->whereHas('unit', function ($qu) {
                            $qu->where('is_self', true)->whereHas('otherTenant');
                        })->orWhereHas('unit', function ($qu) {
                            $qu->where('is_self', false);
                        });
                    });
                }
            }

            if ($reportType === 'rent') {
                $prevQuery->where('type', 'rent');
            } elseif ($reportType === 'fines') {
                $prevQuery->where('type', 'fine');
            } elseif ($reportType === 'utilities') {
                $prevQuery->whereIn('type', ['electricity', 'water', 'gas']);
            } elseif ($reportType === 'maintinance' || $reportType === 'maintenance') {
                $prevQuery->where('type', 'maintenance');
            } elseif ($reportType === 'security_deposit') {
                $prevQuery->where('type', 'security_deposit');
            } elseif ($reportType === 'extra_payments') {
                $prevQuery->whereIn('type', ['extra_payment', 'other', 'fine']);
            }

            $prevPayments = $prevQuery->get();
            foreach ($prevPayments as $pp) {
                $diff = (float)$pp->amount - (float)$pp->amount_paid;
                if ($diff > 0) {
                    $prevUnpaidTotal += $diff;
                    if ($pp->type === 'rent') {
                        $prevUnpaidRent += $diff;
                    } elseif (in_array($pp->type, ['maintenance', 'utility', 'electricity', 'water', 'gas'])) {
                        $prevUnpaidServ += $diff;
                    } elseif ($pp->type === 'security_deposit') {
                        $prevUnpaidSec += $diff;
                    } else {
                        $prevUnpaidExtra += $diff;
                    }
                }
            }
        }

        $totalDue = $entries->sum('amount_due');
        $totalPaid = $entries->sum('amount_paid');
        $outstanding = max(0.0, $totalDue - $totalPaid) + $prevUnpaidTotal;

        return [
            'total_due' => $totalDue,
            'total_paid' => $totalPaid,
            'outstanding' => $outstanding,
            'count' => $entries->count(),
            'rent_collected' => $rentCollected,
            'maintenance_collected' => $maintenanceCollected,
            'utilities_paid' => $utilitiesPaid,
            'fines_collected' => $finesCollected,
            'accounts_summary' => $accountSummaries,
            'prev_unpaid_rent' => $prevUnpaidRent,
            'prev_unpaid_serv' => $prevUnpaidServ,
            'prev_unpaid_sec' => $prevUnpaidSec,
            'prev_unpaid_extra' => $prevUnpaidExtra,
            'prev_unpaid_total' => $prevUnpaidTotal,
        ];
    }

    // -------------------------------------------------------------------------
    // Monthly Matrix Builders
    // -------------------------------------------------------------------------

    private function buildMatrixEntries(Request $request): Collection
    {
        return $this->matrixService->buildMatrixEntries($request);
    }

    private function buildMatrixSummary(Collection $matrixEntries): array
    {
        return $this->matrixService->buildMatrixSummary($matrixEntries);
    }

    private function buildPotentialRevenueEntries(Request $request): Collection
    {
        $unitId = $request->unit_id;
        $unitStatus = $request->unit_status;
        $landlordId = $request->landlord_id;
        $ownerType = $request->owner_type;

        $query = Unit::with(['activeAgreement', 'landlord']);

        if ($unitId) {
            $query->where('id', $unitId);
        }
        if ($unitStatus) {
            $query->where('status', $unitStatus);
        }
        if ($landlordId) {
            $query->where('landlord_id', $landlordId);
        }
        if ($ownerType) {
            if ($ownerType === 'pm_mall') {
                $query->where('is_self', false);
            } elseif ($ownerType === 'other') {
                $query->where('is_self', true);
            }
        }

        $units = $query->orderBy('unit_number')->get();

        return $units->map(function ($unit) {
            $isRented = $unit->status === 'rented';
            $activeAgreement = $unit->activeAgreement;

            if ($isRented && $activeAgreement) {
                $rent = (float) $activeAgreement->monthly_rent;
                $maintenance = (float) $activeAgreement->maintenance_charge;
                $source = 'Active Agreement';
            } else {
                $rent = (float) ($unit->default_monthly_rent ?? 0.0);
                $maintenance = (float) ($unit->default_maintenance_charge ?? 0.0);
                $source = 'Default Values';
            }

            $total = $rent + $maintenance;

            return [
                'unit_id' => $unit->id,
                'unit_number' => $unit->unit_number,
                'type' => $unit->type,
                'status' => $unit->status,
                'landlord' => $unit->landlord?->name ?? '—',
                'source' => $source,
                'rent' => $rent,
                'maintenance' => $maintenance,
                'total' => $total,
            ];
        });
    }

    private function buildPotentialRevenueSummary(Collection $entries): array
    {
        $count = $entries->count();
        $totalRent = $entries->sum('rent');
        $totalMaintenance = $entries->sum('maintenance');
        $totalCombined = $entries->sum('total');

        $rentedCount = $entries->where('status', 'rented')->count();
        $vacantCount = $count - $rentedCount;

        return [
            'count' => $count,
            'total_rent' => $totalRent,
            'total_maintenance' => $totalMaintenance,
            'total_combined' => $totalCombined,
            'rented_count' => $rentedCount,
            'vacant_count' => $vacantCount,
        ];
    }

    // -------------------------------------------------------------------------
    // Human-readable report label for filenames / headings
    // -------------------------------------------------------------------------

    private function reportLabel(Request $request): string
    {
        return match ($request->report_type ?? 'all') {
            'rent' => 'Rent Collected',
            'fines' => 'Fines',
            'utilities' => 'Utilities Paid',
            'monthly_matrix' => 'Monthly Matrix (Generated Billings)',
            'monthly_matrix_expected' => 'Monthly Matrix (Expected Revenue)',
            'security_deposit_matrix' => 'Security Deposit Matrix',
            'potential_revenue' => 'Fully Rented Forecast',
            'maintinance', 'maintenance' => 'Maintenance',
            'other_owned' => 'Other Owned Payments',
            'occupied', 'occupide' => 'Occupied (External Units)',
            'non_occupied', 'non_occupide' => 'Non-Occupied (External Units)',
            default => 'Full Report',
        };
    }

    private function prepareMatrixDate(Request $request): void
    {
        // Default to current month only if no filter query params are passed (initial load or reset)
        $filterKeys = array_filter($request->keys(), fn($k) => $k !== 'no_sidebar');
        if (empty($filterKeys)) {
            $request->merge([
                'date_from' => \Carbon\Carbon::now()->startOfMonth()->toDateString(),
                'date_to' => \Carbon\Carbon::now()->endOfMonth()->toDateString(),
            ]);
        }

        if ($request->filled('date_from') && !$request->filled('date_to')) {
            $request->merge([
                'date_to' => \Carbon\Carbon::parse($request->date_from)->endOfMonth()->toDateString(),
            ]);
        }

        if (in_array($request->report_type, ['monthly_matrix', 'monthly_matrix_expected']) && !$request->filled('date_from')) {
            $request->merge(['date_from' => \Carbon\Carbon::now()->startOfMonth()->toDateString()]);
        }
    }
}
