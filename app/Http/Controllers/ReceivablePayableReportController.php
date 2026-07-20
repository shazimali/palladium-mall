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
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');
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
        } else {
            // 1b. Tenant Rent, Maintenance, Utilities, Fines — each as a separate row
            $groupedDues = $allPayments->groupBy(function ($p) {
                $chargeType = in_array($p->type, ['electricity', 'water', 'gas']) ? 'utility' : $p->type;
                return ($p->tenant_id ?? 0) . '_' . ($p->unit_id ?? 0) . '_' . $chargeType;
            });

            foreach ($groupedDues as $key => $group) {
                $first = $group->first();
                $chargeType = in_array($first->type, ['electricity', 'water', 'gas']) ? 'utility' : $first->type;

                // Security deposits are payables — skip here
                if ($chargeType === 'security_deposit') {
                    continue;
                }

                $totalDue = (float) $group->sum('amount');
                $totalPaid = (float) $group->sum('amount_paid');
                $netReceivable = round($totalDue - $totalPaid, 2);

                if ($netReceivable > 0.01) {
                    $tenantName = $first->otherTenant
                        ? $first->otherTenant->name
                        : ($first->tenant ? $first->tenant->name : 'Other Owned');
                    $unitNo = $first->unit ? $first->unit->unit_number : '';
                    $isSelf = (bool) ($first->unit ? $first->unit->is_self : false);

                    $category = match ($chargeType) {
                        'rent' => 'Tenant Rent',
                        'maintenance' => 'Tenant Maintenance',
                        'fine' => 'Tenant Fine',
                        'utility' => 'Tenant Utilities',
                        default => 'Tenant Other',
                    };

                    // Apply category filter — skip if categories selected but this category not in list
                    if (!empty($categories) && !in_array($category, $categories)) {
                        continue;
                    }

                    $hasOtherTenant = (bool) ($first->other_tenant_id || ($first->unit && $first->unit->otherTenant));
                    $isOtherReceivable = $isSelf && !$hasOtherTenant;

                    $receivables[] = [
                        'category' => $category,
                        'name' => $tenantName,
                        'unit' => $unitNo,
                        'due' => $totalDue,
                        'paid' => $totalPaid,
                        'net' => $netReceivable,
                        'is_self' => $isSelf,
                        'has_other_tenant' => $hasOtherTenant,
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
                // Payables Side calculation: GRV + payable_dues - PV
                $netPayable = round(($totalReceived + $totalPayableDue) - $totalPaid, 2);

                if ($netPayable > 0.01) {
                    $payables[] = [
                        'category' => 'Party Payable',
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
                // Receivables Side calculation: dues_receivable + max(0, total_paid - total_received - dues_payable)
                $excessPaid = max(0.00, $totalPaid - ($totalReceived + $totalPayableDue));
                $netReceivable = round($totalReceivableDue + $excessPaid, 2);

                if ($netReceivable > 0.01) {
                    $receivables[] = [
                        'category' => 'Party Receivable',
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

                $netReceivable = round($openingBalance - $totalReceived, 2);

                if ($netReceivable > 0.01) {
                    $receivables[] = [
                        'category' => 'Landlord Credit',
                        'name' => $landlord->name,
                        'unit' => '',
                        'due' => $openingBalance,
                        'paid' => $totalReceived,
                        'net' => $netReceivable,
                        'is_self' => false,
                        'is_other_receivable' => false,
                    ];
                }
            }
        } else {
            // Payables side (LandlordPayable installments building owes) - Removed
        }

        // Apply category filter checkboxes if set
        if (!empty($categories)) {
            $payables = collect($payables)->filter(fn($p) => in_array($p['category'], $categories))->values()->all();
            $receivables = collect($receivables)->filter(fn($r) => in_array($r['category'], $categories))->values()->all();
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
