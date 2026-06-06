<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Landlord;
use App\Models\PaymentAccount;
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
            || $request->filled('payment_account_id');

        $entries = collect();
        $summary = null;

        if ($hasQuery) {
            $entries = $this->buildEntries($request);
            $summary = $this->buildSummary($entries);
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
            ]),
        ]);
    }

    // -------------------------------------------------------------------------
    // Export Excel
    // -------------------------------------------------------------------------

    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $entries  = $this->buildEntries($request);
        $summary  = $this->buildSummary($entries);
        $label    = $this->reportLabel($request);
        $filename = 'report-' . str($label)->slug() . '-' . now()->format('Y-m-d') . '.xlsx';

        \App\Models\ActivityLog::log('export_excel', "Exported report to Excel: {$filename}", null, [
            'report_type' => $request->report_type,
            'filters' => $request->all(),
        ]);

        return Excel::download(
            new ReportExport($entries, $label, $summary),
            $filename
        );
    }

    // -------------------------------------------------------------------------
    // Export PDF
    // -------------------------------------------------------------------------

    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        $entries = $this->buildEntries($request);
        $summary = $this->buildSummary($entries);
        $label   = $this->reportLabel($request);
        $filters = $request->only([
            'date_from', 'date_to', 'unit_id', 'tenant_id', 'status', 'report_type',
            'landlord_id', 'payment_method', 'payment_account_id'
        ]);

        $period = ($filters['date_from'] ?? false) || ($filters['date_to'] ?? false)
            ? ($filters['date_from'] ?? '—') . ' to ' . ($filters['date_to'] ?? '—')
            : 'All time';

        $pdf = Pdf::loadView('reports.pdf', [
            'entries'    => $entries,
            'summary'    => $summary,
            'label'      => $label,
            'period'     => $period,
            'filters'    => $filters,
            'reportType' => $request->report_type ?? 'all',
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

        $query = Payment::with(['tenant', 'unit.landlord', 'paymentAccount'])
            ->when($unitId,           fn($q) => $q->where('unit_id',   $unitId))
            ->when($tenantId,         fn($q) => $q->where('tenant_id', $tenantId))
            ->when($from,             fn($q) => $q->where('month', '>=', $from))
            ->when($to,               fn($q) => $q->where('month', '<=', $to))
            ->when($status,           fn($q) => $q->where('status', $status))
            ->when($paymentMethod,    fn($q) => $q->where('payment_method', $paymentMethod))
            ->when($paymentAccountId, fn($q) => $q->where('payment_account_id', $paymentAccountId))
            ->when($landlordId,       fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('landlord_id', $landlordId)));

        if ($reportType === 'rent') {
            $query->where('type', 'rent');
        } elseif ($reportType === 'fines') {
            $query->where('type', 'fine');
        } elseif ($reportType === 'utilities') {
            $query->whereIn('type', ['electricity', 'water', 'gas']);
        } else {
            $query->whereIn('type', ['rent', 'fine', 'maintenance', 'electricity', 'water', 'gas', 'other']);
        }

        $entries = $query->get()->map(fn($p) => [
            'month'           => $p->month,
            'date'            => $p->due_date,
            'unit'            => $p->unit?->unit_number ?? '—',
            'tenant'          => $p->tenant?->name ?? '—',
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
        ]);

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
        $rentCollected    = $entries->where('type', 'rent')->sum('amount_paid');
        $utilitiesPaid    = $entries->where('category', 'utility')->sum('amount_paid');
        $finesCollected   = $entries->where('type', 'fine')->sum('amount_paid');

        return [
            'total_due'         => $entries->sum('amount_due'),
            'total_paid'        => $entries->sum('amount_paid'),
            'outstanding'       => $entries->sum('amount_due') - $entries->sum('amount_paid'),
            'count'             => $entries->count(),
            'rent_collected'    => $rentCollected,
            'utilities_paid'    => $utilitiesPaid,
            'fines_collected'   => $finesCollected,
        ];
    }

    // -------------------------------------------------------------------------
    // Human-readable report label for filenames / headings
    // -------------------------------------------------------------------------

    private function reportLabel(Request $request): string
    {
        return match ($request->report_type ?? 'all') {
            'rent'      => 'Rent Collected',
            'fines'     => 'Fines',
            'utilities' => 'Utilities Paid',
            default     => 'Full Report',
        };
    }
}
