<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\PaymentAccount;
use App\Models\PaymentVoucher;
use App\Models\ReceivingVoucher;
use App\Models\GeneralReceivingVoucher;
use App\Models\Payment;
use App\Models\Landlord;
use App\Models\LandlordPayable;
use App\Models\Owner;
use Barryvdh\Dompdf\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceivablePayableReportController extends Controller
{
    /**
     * Display the Consolidated Receivables & Payables Summary Report.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $this->buildReportData($request);

        return view('reports.receivables_payables', array_merge($data, [
            'title' => 'Receivables & Payables Summary Report',
        ]));
    }

    /**
     * Export the unified report as PDF.
     */
    public function exportPdf(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $this->buildReportData($request);

        $pdf = Pdf::loadView('reports.receivables_payables_pdf', array_merge($data, [
            'title' => 'Receivables & Payables Summary Report',
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('receivables_payables_summary_' . now()->format('Y_m_d') . '.pdf');
    }

    /**
     * AJAX endpoint to return pending balance for a specific owner.
     * Replaces the lookup endpoint from the deleted OwnerDuesReportController.
     */
    public function getOwnerBalance(Request $request)
    {
        $owner = Owner::findOrFail($request->query('owner_id'));

        return response()->json([
            'owner_id' => $owner->id,
            'owner_name' => $owner->name,
            'total_income_due' => $owner->totalIncomeDue(),
            'total_paid' => $owner->totalPaid(),
            'pending_balance' => $owner->pendingBalance(),
        ]);
    }

    /**
     * Build unified dataset for both tables.
     */
    private function buildReportData(Request $request): array
    {
        $dateFrom = $request->filled('date_from') ? $request->query('date_from') : now()->startOfMonth()->toDateString();
        $dateTo = $request->filled('date_to') ? $request->query('date_to') : now()->endOfMonth()->toDateString();
        $type = $request->query('type', 'receivables'); // 'receivables' or 'payables'
        $receivableScope = $request->query('receivable_scope', 'pm_mall'); // 'pm_mall' or 'other'
        $categories = $request->query('categories', []); // selected category checkboxes

        $payables = [];
        $receivables = [];

        // ── 1. Tenant Security Deposits & Tenant Rent/Maintenance/Utility Dues ──
        $paymentsQuery = Payment::query()
            ->with(['tenant', 'unit', 'otherTenant'])
            ->when($dateFrom, fn($q) => $q->where('due_date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('due_date', '<=', $dateTo));

        $allPayments = $paymentsQuery->get();

        if ($type === 'payables') {
            // 1a. Security Deposits (collected amount is a Payable)
            if (empty($categories) || in_array('Tenant Security Deposit', $categories)) {
                $securityPayments = $allPayments->where('type', 'security_deposit');
                $groupedSecurity = $securityPayments->groupBy(function ($p) {
                    return ($p->tenant_id ?? 0) . '_' . ($p->unit_id ?? 0);
                });

                // Fetch all tenant security deposit refund vouchers
                $refundVouchers = PaymentVoucher::whereNotNull('tenant_id')
                    ->whereNotNull('unit_id')
                    ->get()
                    ->groupBy(function ($v) {
                        return $v->tenant_id . '_' . $v->unit_id;
                    });

                foreach ($groupedSecurity as $key => $group) {
                    $first = $group->first();
                    $totalCollected = (float) $group->sum('amount_paid');

                    $refundedGroup = $refundVouchers->get(($first->tenant_id ?? 0) . '_' . ($first->unit_id ?? 0));
                    $totalRefunded = $refundedGroup ? (float) $refundedGroup->sum('amount') : 0.00;

                    $netPayable = round($totalCollected - $totalRefunded, 2);

                    if ($netPayable > 0.01 || $totalRefunded > 0.01) {
                        $tenantName = $first->otherTenant
                            ? $first->otherTenant->name
                            : ($first->tenant ? $first->tenant->name : 'Other Owned');
                        $unitNo = $first->unit ? $first->unit->unit_number : '';
                        $payables[] = [
                            'category' => 'Tenant Security Deposit',
                            'name' => $tenantName,
                            'unit' => $unitNo,
                            'due' => $totalCollected,
                            'paid' => $totalRefunded,
                            'net' => $netPayable,
                            'is_self' => (bool) ($first->unit ? $first->unit->is_self : false),
                        ];
                    }
                }
            }
        } else {
            // 1b. Tenant Rent, Maintenance, Utilities, Fines — aggregated per unit (one row per unit)
            $duesOnlyPayments = $allPayments->where('type', '!=', 'security_deposit');

            if (!empty($categories)) {
                $duesOnlyPayments = $duesOnlyPayments->filter(function ($p) use ($categories) {
                    $cat = match ($p->type) {
                        'rent'                  => 'Tenant Rent',
                        'maintenance'           => 'Tenant Maintenance',
                        'extra_payment', 'other' => 'Tenant Extra',
                        'fine'                  => 'Tenant Fine',
                        'electricity', 'water', 'gas' => 'Tenant Utilities',
                        default                 => 'Tenant Other',
                    };
                    return in_array($cat, $categories);
                });
            }

            // Group by unit_id ONLY — matches monthly matrix which sums all payment types per unit.
            // This merges extra_payment (tenant_id=null) with rent/maintenance (tenant_id=X)
            // into a single row per unit so totals align with the matrix.
            $groupedDues = $duesOnlyPayments->groupBy(fn($p) => $p->unit_id ?? 0);

            foreach ($groupedDues as $unitId => $group) {
                $totalDue = (float) $group->sum('amount');
                $totalPaid = (float) $group->sum('amount_paid');
                $netReceivable = round($totalDue - $totalPaid, 2);

                // Only show records where amounts are not received yet (netReceivable > 0.01)
                if ($netReceivable > 0.01) {
                    // Resolve name: scan whole group for best name (otherTenant > tenant > unit number)
                    // A unit may have extra_payment with null tenant_id alongside rent with real tenant_id
                    $first = $group->first();
                    $unitNo = $first->unit ? $first->unit->unit_number : '';

                    $tenantName = null;
                    foreach ($group as $p) {
                        if ($p->otherTenant) {
                            $tenantName = $p->otherTenant->name;
                            break;
                        }
                        if ($p->tenant && !$tenantName) {
                            $tenantName = $p->tenant->name;
                        }
                    }
                    $tenantName = $tenantName ?? ($unitNo ?: 'Unknown Unit');

                    $isSelf = (bool) ($first->unit ? $first->unit->is_self : false);
                    $hasActiveOtherTenant = (bool) ($first->unit && ($first->unit->other_tenant_id || $first->unit->otherTenant));

                    // Match the monthly matrix logic exactly:
                    // PM Mall tab  → is_self=false (PM Mall owned) OR is_self=true WITH active otherTenant on unit
                    // Other tab    → is_self=true WITHOUT active otherTenant on unit (self-owned vacant units)
                    $isOtherReceivable = $isSelf && !$hasActiveOtherTenant;

                    $pendingTypes = $group->filter(fn($p) => ((float)$p->amount - (float)$p->amount_paid) > 0.01)
                        ->map(fn($p) => match($p->type) {
                            'rent' => 'Rent',
                            'maintenance' => 'Maintenance',
                            'extra_payment', 'other' => 'Extra Payments',
                            'fine' => 'Fine',
                            'electricity', 'water', 'gas' => 'Utilities',
                            default => ucfirst($p->type),
                        })->unique()->values()->all();

                    $receivables[] = [
                        'category' => 'Tenant Dues',
                        'types' => $pendingTypes,
                        'name' => $tenantName,
                        'unit' => $unitNo,
                        'due' => $totalDue,
                        'paid' => $totalPaid,
                        'net' => $netReceivable,
                        'is_self' => $isSelf,
                        'has_other_tenant' => $hasActiveOtherTenant,
                        'is_other_receivable' => $isOtherReceivable,
                    ];
                }
            }

        }

        // ── 2. Party Balances (Payables and Receivables) ───────────────────────
        $parties = Party::with(['receivingVouchers', 'paymentVouchers', 'dues'])->get();

        foreach ($parties as $party) {
            // Filter relationships by date if set
            $vQuery = $party->receivingVouchers();
            if ($dateFrom)
                $vQuery->where('date', '>=', $dateFrom);
            if ($dateTo)
                $vQuery->where('date', '<=', $dateTo);
            $totalReceived = (float) $vQuery->sum('amount'); // General Receiving

            $pvQuery = $party->paymentVouchers()->where('paid_to_type', 'other');
            if ($dateFrom)
                $pvQuery->where('date', '>=', $dateFrom);
            if ($dateTo)
                $pvQuery->where('date', '<=', $dateTo);
            $totalPaid = (float) $pvQuery->sum('amount'); // Payment Vouchers

            $dQueryPayable = $party->dues()->where('type', 'payable');
            if ($dateFrom)
                $dQueryPayable->where('date', '>=', $dateFrom);
            if ($dateTo)
                $dQueryPayable->where('date', '<=', $dateTo);
            $totalPayableDue = (float) $dQueryPayable->sum('amount');

            $dQueryReceivable = $party->dues()->where('type', 'receivable');
            if ($dateFrom)
                $dQueryReceivable->where('date', '>=', $dateFrom);
            if ($dateTo)
                $dQueryReceivable->where('date', '<=', $dateTo);
            $totalReceivableDue = (float) $dQueryReceivable->sum('amount');

            if ($type === 'payables') {
                if (!empty($categories) && !in_array('Party Payable', $categories)) {
                    continue;
                }
                // Payables Side calculation: GRV + payable_dues - PV
                $netPayable = round(($totalReceived + $totalPayableDue) - $totalPaid, 2);

                if ($netPayable > 0.01) {
                    $payables[] = [
                        'category' => 'Party Payable',
                        'types' => ['Party Payable'],
                        'name' => $party->name,
                        'unit' => '',
                        'due' => $totalReceived + $totalPayableDue,
                        'paid' => $totalPaid,
                        'net' => $netPayable,
                        'is_self' => false,
                        'is_other_receivable' => false,
                    ];
                }
            } else {
                if (!empty($categories) && !in_array('Party Receivable', $categories)) {
                    continue;
                }
                // Receivables Side calculation: dues_receivable + max(0, total_paid - total_received - dues_payable)
                $excessPaid = max(0.00, $totalPaid - ($totalReceived + $totalPayableDue));
                $netReceivable = round($totalReceivableDue + $excessPaid, 2);

                if ($netReceivable > 0.01) {
                    $receivables[] = [
                        'category' => 'Party Receivable',
                        'types' => ['Party Receivable'],
                        'name' => $party->name,
                        'unit' => '',
                        'due' => $totalReceivableDue + $totalPaid,
                        'paid' => $totalReceived + $totalPayableDue,
                        'net' => $netReceivable,
                        'is_self' => false,
                        'is_other_receivable' => false,
                    ];
                }
            }
        }

        // ── 3. Landlord Credit & Landlord Payables ────────────────────────────
        if ($type === 'receivables') {
            if (empty($categories) || in_array('Landlord Credit', $categories)) {
                $landlords = Landlord::with(['ownerships'])->get();

                foreach ($landlords as $landlord) {
                    // Receivables side (Opening Credit remaining)
                    $openingBalance = (float) $landlord->ownerships->sum('credit_amount'); // credit remaining on purchase

                    $rvQuery = ReceivingVoucher::where('owner_id', $landlord->id);
                    if ($dateFrom)
                        $rvQuery->where('date', '>=', $dateFrom);
                    if ($dateTo)
                        $rvQuery->where('date', '<=', $dateTo);
                    $totalReceived = (float) $rvQuery->sum('amount');

                    $grvQuery = GeneralReceivingVoucher::where('landlord_id', $landlord->id);
                    if ($dateFrom)
                        $grvQuery->where('date', '>=', $dateFrom);
                    if ($dateTo)
                        $grvQuery->where('date', '<=', $dateTo);
                    $totalReceived += (float) $grvQuery->sum('amount');

                    $netReceivable = round($openingBalance - $totalReceived, 2);

                    if ($netReceivable > 0.01) {
                        $receivables[] = [
                            'category' => 'Landlord Credit',
                            'types' => ['Landlord Credit'],
                            'name' => $landlord->name,
                            'unit' => '',
                            'details' => 'Landlord Opening Credit',
                            'due' => $openingBalance,
                            'paid' => $totalReceived,
                            'net' => $netReceivable,
                            'is_self' => false,
                            'is_other_receivable' => false,
                        ];
                    }
                }
            }
        } else {
            // Payables side: Landlord Payables (fetched strictly from Extra Payments)
            $extraPaymentsQuery = Payment::query()
                ->where('type', 'extra_payment')
                ->where('landlord_id', '!=', null)
                ->with(['landlord', 'unit', 'tenant', 'otherTenant']);

            if ($dateFrom) {
                $extraPaymentsQuery->where('due_date', '>=', $dateFrom);
            }
            if ($dateTo) {
                $extraPaymentsQuery->where('due_date', '<=', $dateTo);
            }

            $extraPaymentsList = $extraPaymentsQuery->get();

            foreach ($extraPaymentsList as $ep) {
                if (!empty($categories) && !in_array('Landlord Payable', $categories)) {
                    continue;
                }

                $name = $ep->landlord
                    ? $ep->landlord->name
                    : ($ep->otherTenant
                        ? $ep->otherTenant->name
                        : ($ep->tenant ? $ep->tenant->name : 'N/A'));
                $unitNo = $ep->unit ? $ep->unit->unit_number : '';
                $totalDue = (float) $ep->amount;
                $totalPaid = (float) $ep->amount_paid;
                $netPayable = round($totalDue - $totalPaid, 2);

                if ($netPayable > 0.01 || $totalPaid > 0.01) {
                    $payables[] = [
                        'category' => 'Landlord Payable',
                        'types' => ['Landlord Payable'],
                        'name' => $name,
                        'unit' => $unitNo,
                        'details' => 'Extra Payment' . ($ep->notes ? ' - ' . $ep->notes : ''),
                        'due' => $totalDue,
                        'paid' => $totalPaid,
                        'net' => max(0.00, $netPayable),
                        'is_self' => (bool) ($ep->unit ? $ep->unit->is_self : false),
                        'is_other_receivable' => false,
                    ];
                }
            }
        }

        // Apply category filter checkboxes if set
        // NOTE: Tenant dues rows are tagged 'Tenant Dues' but checkboxes are sub-categories
        // (Tenant Rent, Tenant Maintenance, etc). Handle by passing through any 'Tenant Dues'
        // row when at least one tenant sub-category is selected (they were already pre-filtered
        // per payment type above). All other categories are matched exactly.
        if (!empty($categories)) {
            $tenantSubCategories = ['Tenant Rent', 'Tenant Maintenance', 'Tenant Extra', 'Tenant Fine', 'Tenant Utilities', 'Tenant Other', 'Tenant Security Deposit'];
            $hasAnyTenantCategory = count(array_intersect($categories, $tenantSubCategories)) > 0;

            $payables = collect($payables)->filter(function ($p) use ($categories, $tenantSubCategories, $hasAnyTenantCategory) {
                if ($p['category'] === 'Tenant Security Deposit') {
                    return $hasAnyTenantCategory || in_array('Tenant Security Deposit', $categories);
                }
                return in_array($p['category'], $categories);
            })->values()->all();

            $receivables = collect($receivables)->filter(function ($r) use ($categories, $hasAnyTenantCategory) {
                if ($r['category'] === 'Tenant Dues') {
                    // Tenant dues rows were already pre-filtered by type; pass through if any tenant category selected
                    return $hasAnyTenantCategory;
                }
                return in_array($r['category'], $categories);
            })->values()->all();
        }

        // Separate receivables into PM Mall managed vs Other (Not managed by PM Mall without attached other tenant)
        $allReceivablesColl = collect($receivables);
        $pmMallReceivablesList = $allReceivablesColl->where('is_other_receivable', false)->values();
        $otherReceivablesList = $allReceivablesColl->where('is_other_receivable', true)->values();

        $pmMallReceivablesNet = $pmMallReceivablesList->sum('net');
        $otherReceivablesNet = $otherReceivablesList->sum('net');

        if ($type === 'receivables') {
            if ($receivableScope === 'other') {
                $activeReceivables = $otherReceivablesList->all();
            } else {
                $activeReceivables = $pmMallReceivablesList->all();
            }
        } else {
            $activeReceivables = $receivables;
        }

        // ── 4. General cash book metrics ────────────────────────────────────
        $accounts = PaymentAccount::where('is_active', true)
            ->withSum('receivingVouchers', 'amount')
            ->withSum('generalReceivingVouchers', 'amount')
            ->withSum('paymentVouchers', 'amount')
            ->withSum('expenses', 'amount')
            ->get();

        $totalCashBalance = $accounts->sum(fn($a) => $a->current_balance);

        $totalPayablesDue = collect($payables)->sum('due');
        $totalPayablesPaid = collect($payables)->sum('paid');
        $totalPayablesNet = collect($payables)->sum('net');

        $totalReceivablesDue = collect($activeReceivables)->sum('due');
        $totalReceivablesPaid = collect($activeReceivables)->sum('paid');
        $totalReceivablesNet = collect($activeReceivables)->sum('net');

        $netPosition = $totalReceivablesNet - $totalPayablesNet;

        return [
            // Lists
            'payables' => $payables,
            'receivables' => $activeReceivables,
            'allReceivables' => $receivables,

            // Overall totals
            'totalCashBalance' => $totalCashBalance,
            'totalPayables' => $totalPayablesNet,
            'totalReceivables' => $totalReceivablesNet,
            'totalNetBalance' => $type === 'receivables' ? $totalReceivablesNet : $totalPayablesNet,
            'netPosition' => $netPosition,

            // PM Mall vs Other stats
            'pmMallReceivablesNet' => $pmMallReceivablesNet,
            'otherReceivablesNet' => $otherReceivablesNet,

            // Separate detailed sums
            'totalPayablesDue' => $totalPayablesDue,
            'totalPayablesPaid' => $totalPayablesPaid,
            'totalPayablesNet' => $totalPayablesNet,

            'totalReceivablesDue' => $totalReceivablesDue,
            'totalReceivablesPaid' => $totalReceivablesPaid,
            'totalReceivablesNet' => $totalReceivablesNet,

            'accounts' => $accounts,

            // Filters
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'type' => $type,
            'receivableScope' => $receivableScope,
            'categories' => $categories,
            'generatedAt' => now(),
        ];
    }
}
