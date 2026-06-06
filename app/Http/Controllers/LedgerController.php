<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Payment;
use App\Exports\TenantLedgerExport;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class LedgerController extends Controller
{
    // -----------------------------------------------------------------------
    // Main ledger page
    // -----------------------------------------------------------------------

    public function index(Request $request): View
    {
        $tenants = Tenant::orderBy('name')->get(['id', 'name']);
        $units = Unit::orderBy('unit_number')->get(['id', 'unit_number', 'type']);

        $entries = collect();
        $summary = null;
        $subject = null;
        $hasQuery = $request->filled('scope');

        if ($hasQuery) {
            [$entries, $summary, $subject] = $this->resolveEntries($request);
        }

        return view('ledger.index', [
            'title' => 'Ledger',
            'tenants' => $tenants,
            'units' => $units,
            'entries' => $entries,
            'summary' => $summary,
            'subject' => $subject,
            'filters' => $request->only([
                'scope',
                'tenant_id',
                'unit_id',
                'from',
                'to',
                'type',
                'status',
                'category',
            ]),
            'hasQuery' => $hasQuery,
        ]);
    }

    // -----------------------------------------------------------------------
    // Export Excel
    // -----------------------------------------------------------------------

    public function exportExcel(Request $request): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        [$entries, $summary, $subject] = $this->resolveEntries($request);

        $filename = 'ledger-' . str($subject)->slug() . '-' . now()->format('Y-m-d') . '.xlsx';

        \App\Models\ActivityLog::log('export_excel', "Exported Tenant Ledger to Excel: {$filename}", null, [
            'subject' => $subject,
            'filters' => $request->all(),
        ]);

        return Excel::download(
            new TenantLedgerExport($entries, $subject, $summary),
            $filename
        );
    }

    // -----------------------------------------------------------------------
    // Export PDF
    // -----------------------------------------------------------------------

    public function exportPdf(Request $request): \Illuminate\Http\Response
    {
        [$entries, $summary, $subject] = $this->resolveEntries($request);

        $filters = $request->only(['from', 'to', 'type', 'status', 'category']);

        $period = ($filters['from'] ?? false) || ($filters['to'] ?? false)
            ? ($filters['from'] ?? '—') . ' to ' . ($filters['to'] ?? '—')
            : 'All time';

        $pdf = Pdf::loadView('ledger.pdf', [
            'entries' => $entries,
            'subjectName' => $subject,
            'summary' => $summary,
            'period' => $period,
            'filters' => $filters,
        ])->setPaper('a4', 'landscape');

        $filename = 'ledger-' . str($subject)->slug() . '-' . now()->format('Y-m-d') . '.pdf';

        \App\Models\ActivityLog::log('export_pdf', "Exported Tenant Ledger to PDF: {$filename}", null, [
            'subject' => $subject,
            'filters' => $filters,
        ]);

        return $pdf->download($filename);
    }

    // -----------------------------------------------------------------------
    // Core — build entries from filters
    // -----------------------------------------------------------------------

    private function resolveEntries(Request $request): array
    {
        $scope = $request->scope;   // 'tenant' or 'unit'
        $from = $request->from;
        $to = $request->to;
        $type = $request->type;
        $status = $request->status;
        $category = $request->category; // 'payment', 'utility', or null

        if ($scope === 'tenant' && $request->filled('tenant_id')) {
            $tenant = Tenant::with('unit')->findOrFail($request->tenant_id);
            $subject = $tenant->name . ' (' . $tenant->unit?->unit_number . ')';
            $entries = $this->buildEntries(
                tenantId: $tenant->id,
                unitId: null,
                from: $from,
                to: $to,
                type: $type,
                status: $status,
                category: $category,
            );
        } elseif ($scope === 'unit' && $request->filled('unit_id')) {
            $unit = Unit::findOrFail($request->unit_id);
            $subject = 'Unit ' . $unit->unit_number;
            $entries = $this->buildEntries(
                tenantId: null,
                unitId: $unit->id,
                from: $from,
                to: $to,
                type: $type,
                status: $status,
                category: $category,
            );
        } else {
            $entries = collect();
            $subject = '—';
        }

        $summary = [
            'total_due' => $entries->sum('amount_due'),
            'total_paid' => $entries->sum('amount_paid'),
            'outstanding' => $entries->sum('amount_due') - $entries->sum('amount_paid'),
            'count' => $entries->count(),
        ];

        return [$entries, $summary, $subject];
    }

    // -----------------------------------------------------------------------
    // Build unified entries collection
    // -----------------------------------------------------------------------

    private function buildEntries(
        ?int $tenantId,
        ?int $unitId,
        ?string $from,
        ?string $to,
        ?string $type,
        ?string $status,
        ?string $category,
    ): Collection {
        $paymentTypes = ['rent', 'maintenance', 'fine', 'other'];
        $utilityTypes = ['electricity', 'water', 'gas'];

        $allowedTypes = [];
        if ($category === 'payment') {
            $allowedTypes = $paymentTypes;
        } elseif ($category === 'utility') {
            $allowedTypes = $utilityTypes;
        } else {
            $allowedTypes = array_merge($paymentTypes, $utilityTypes);
        }

        if ($type) {
            $allowedTypes = [$type];
        }

        $payments = Payment::with(['tenant', 'unit'])
            ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
            ->when($unitId, fn($q) => $q->where('unit_id', $unitId))
            ->when($from, fn($q) => $q->where('month', '>=', $from))
            ->when($to, fn($q) => $q->where('month', '<=', $to))
            ->whereIn('type', $allowedTypes)
            ->when($status, fn($q) => $q->where('status', $status))
            ->get()
            ->map(fn($p) => [
                'date' => $p->due_date,
                'month' => $p->month,
                'description' => $p->type_label . ' — ' . $p->month->format('F Y'),
                'tenant' => $p->tenant->name ?? '—',
                'unit' => $p->unit->unit_number ?? '—',
                'category' => in_array($p->type, $utilityTypes) ? 'utility' : 'payment',
                'type' => $p->type,
                'amount_due' => (float) $p->amount,
                'amount_paid' => (float) $p->amount_paid,
                'status' => $p->status,
                'method' => $p->payment_method,
                'paid_at' => $p->paid_at,
            ]);

        $balance = 0;

        return $payments
            ->sortBy('date')
            ->values()
            ->map(function ($entry) use (&$balance) {
                $balance += $entry['amount_due'] - $entry['amount_paid'];
                $entry['balance'] = $balance;
                return $entry;
            });
    }
}