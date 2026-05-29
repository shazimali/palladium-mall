<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\UtilityReading;
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

        $hasQuery = $request->filled('report_type') || $request->filled('date_from') || $request->filled('date_to');

        $entries = collect();
        $summary = null;

        if ($hasQuery) {
            $entries = $this->buildEntries($request);
            $summary = $this->buildSummary($entries);
        }

        return view('reports.index', [
            'title'    => 'Reports',
            'tenants'  => $tenants,
            'units'    => $units,
            'entries'  => $entries,
            'summary'  => $summary,
            'hasQuery' => $hasQuery,
            'filters'  => $request->only([
                'report_type',
                'date_from',
                'date_to',
                'unit_id',
                'tenant_id',
                'status',
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
        $filters = $request->only(['date_from', 'date_to', 'unit_id', 'tenant_id', 'status', 'report_type']);

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

        return $pdf->download('report-' . str($label)->slug() . '-' . now()->format('Y-m-d') . '.pdf');
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

        $payments   = collect();
        $utilities  = collect();

        // ── Payments (rent / fines / all) ─────────────────────────────────
        if (in_array($reportType, ['rent', 'fines', 'all'])) {
            $query = Payment::with(['tenant', 'unit'])
                ->when($unitId,   fn($q) => $q->where('unit_id',   $unitId))
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->when($from,     fn($q) => $q->where('month', '>=', $from))
                ->when($to,       fn($q) => $q->where('month', '<=', $to))
                ->when($status,   fn($q) => $q->where('status', $status));

            // Scope to specific payment type(s)
            if ($reportType === 'rent') {
                $query->where('type', 'rent');
            } elseif ($reportType === 'fines') {
                $query->where('type', 'fine');
            } else {
                // 'all' — include rent + fine + maintenance + other
                $query->whereIn('type', ['rent', 'fine', 'maintenance', 'other']);
            }

            $payments = $query->get()->map(fn($p) => [
                'month'       => $p->month,
                'date'        => $p->due_date,
                'unit'        => $p->unit?->unit_number ?? '—',
                'tenant'      => $p->tenant?->name ?? '—',
                'category'    => 'payment',
                'type'        => $p->type,
                'description' => ucfirst($p->type) . ' — ' . $p->month?->format('F Y'),
                'amount_due'  => (float) $p->amount,
                'amount_paid' => (float) $p->amount_paid,
                'status'      => $p->status,
                'paid_at'     => $p->paid_at,
            ]);
        }

        // ── Utility readings ────────────────────────────────────────────────
        if (in_array($reportType, ['utilities', 'all'])) {
            $utilities = UtilityReading::with(['tenant', 'unit'])
                ->when($unitId,   fn($q) => $q->where('unit_id',   $unitId))
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->when($from,     fn($q) => $q->where('month', '>=', $from))
                ->when($to,       fn($q) => $q->where('month', '<=', $to))
                ->when($status,   fn($q) => $q->where('status', $status))
                ->get()
                ->map(fn($u) => [
                    'month'       => $u->month,
                    'date'        => $u->due_date,
                    'unit'        => $u->unit?->unit_number ?? '—',
                    'tenant'      => $u->tenant?->name ?? '—',
                    'category'    => 'utility',
                    'type'        => $u->type,
                    'description' => ucfirst($u->type) . ' — ' . $u->month?->format('F Y'),
                    'amount_due'  => (float) $u->bill_amount,
                    'amount_paid' => $u->isPaid() ? (float) $u->bill_amount : 0.0,
                    'status'      => $u->status,
                    'paid_at'     => $u->paid_at,
                ]);
        }

        // ── Merge & sort by date ───────────────────────────────────────────
        $balance = 0;

        return $payments
            ->concat($utilities)
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
