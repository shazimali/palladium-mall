<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Payment;
use App\Models\UtilityReading;
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

        return $pdf->download('ledger-' . str($subject)->slug() . '-' . now()->format('Y-m-d') . '.pdf');
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

        $includePayments = !$category || $category === 'payment';
        $includeUtilities = !$category || $category === 'utility';

        // If type is specified, only include the relevant category
        if ($type) {
            $includePayments = in_array($type, $paymentTypes);
            $includeUtilities = in_array($type, $utilityTypes);
        }

        // ── Payments ──────────────────────────────────────────────────────
        $payments = collect();
        if ($includePayments) {
            $payments = Payment::with(['tenant', 'unit'])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->when($unitId, fn($q) => $q->where('unit_id', $unitId))
                ->when($from, fn($q) => $q->where('month', '>=', $from))
                ->when($to, fn($q) => $q->where('month', '<=', $to))
                ->when($type && in_array($type, $paymentTypes), fn($q) => $q->where('type', $type))
                ->when($status, fn($q) => $q->where('status', $status))
                ->get()
                ->map(fn($p) => [
                    'date' => $p->due_date,
                    'month' => $p->month,
                    'description' => ucfirst($p->type) . ' — ' . $p->month->format('F Y'),
                    'tenant' => $p->tenant->name,
                    'unit' => $p->unit->unit_number,
                    'category' => 'payment',
                    'type' => $p->type,
                    'amount_due' => (float) $p->amount,
                    'amount_paid' => (float) $p->amount_paid,
                    'status' => $p->status,
                    'method' => $p->payment_method,
                    'paid_at' => $p->paid_at,
                ]);
        }

        // ── Utility readings ──────────────────────────────────────────────
        $utilities = collect();
        if ($includeUtilities) {
            $utilities = UtilityReading::with(['tenant', 'unit'])
                ->when($tenantId, fn($q) => $q->where('tenant_id', $tenantId))
                ->when($unitId, fn($q) => $q->where('unit_id', $unitId))
                ->when($from, fn($q) => $q->where('month', '>=', $from))
                ->when($to, fn($q) => $q->where('month', '<=', $to))
                ->when($type && in_array($type, $utilityTypes), fn($q) => $q->where('type', $type))
                ->when($status, fn($q) => $q->where('status', $status))
                ->get()
                ->map(fn($u) => [
                    'date' => $u->due_date,
                    'month' => $u->month,
                    'description' => ucfirst($u->type) . ' — ' . $u->month->format('F Y'),
                    'tenant' => $u->tenant->name,
                    'unit' => $u->unit->unit_number,
                    'category' => 'utility',
                    'type' => $u->type,
                    'amount_due' => (float) $u->bill_amount,
                    'amount_paid' => $u->isPaid() ? (float) $u->bill_amount : 0.0,
                    'status' => $u->status,
                    'method' => null,
                    'paid_at' => $u->paid_at,
                ]);
        }

        // ── Merge, sort, running balance ──────────────────────────────────
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
}