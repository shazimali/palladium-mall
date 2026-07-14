<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\Party;
use App\Models\PaymentAccount;
use App\Models\ReceivingVoucher;
use App\Models\GeneralReceivingVoucher;
use App\Models\PaymentVoucher;
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
            'owner_id'         => $owner->id,
            'owner_name'       => $owner->name,
            'total_income_due' => $owner->totalIncomeDue(),
            'total_paid'       => $owner->totalPaid(),
            'pending_balance'  => $owner->pendingBalance(),
        ]);
    }

    /**
     * Build unified dataset for both tables.
     */
    private function buildReportData(Request $request): array
    {
        $search   = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        // ── 1. Managing Owners calculations ──────────────────────────────────
        $owners = Owner::orderBy('name')->get();

        $totalTenantIncome = (float) ReceivingVoucher::where('received_from_type', 'tenant')
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->sum('amount');

        $totalPartyIncome  = (float) GeneralReceivingVoucher::when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->sum('amount');

        $totalIncome = $totalTenantIncome + $totalPartyIncome;

        $ownerRows          = [];
        $totalOwnersDue     = 0.00;
        $totalOwnersPaid    = 0.00;
        $totalOwnersPending = 0.00;

        foreach ($owners as $owner) {
            // Re-evaluate owner totals scoped within the filtered period if needed,
            // but the client prefers standard cumulative dues vs payouts. We can keep it standard
            // or filter if dates are selected. Let's keep it standard since owner totals are cumulative all-time.
            $due     = $owner->totalIncomeDue();
            $paid    = $owner->totalPaid();
            $pending = max(0.00, round($due - $paid, 2));

            // If search is set, filter owners by name
            if ($search && !str_contains(strtolower($owner->name), strtolower($search))) {
                continue;
            }

            $ownerRows[] = [
                'owner'      => $owner,
                'percentage' => (float) $owner->partnership_percentage,
                'due'        => $due,
                'paid'       => $paid,
                'pending'    => $pending,
            ];

            $totalOwnersDue     += $due;
            $totalOwnersPaid    += $paid;
            $totalOwnersPending += $pending;
        }

        // ── 2. Party Heads calculations ─────────────────────────────────────
        $partyQuery = Party::query()
            ->withSum(['dues as total_receivable_due' => function ($q) use ($dateFrom, $dateTo) {
                $q->where('type', 'receivable');
                if ($dateFrom) $q->where('date', '>=', $dateFrom);
                if ($dateTo)   $q->where('date', '<=', $dateTo);
            }], 'amount')
            ->withSum(['dues as total_payable_due' => function ($q) use ($dateFrom, $dateTo) {
                $q->where('type', 'payable');
                if ($dateFrom) $q->where('date', '>=', $dateFrom);
                if ($dateTo)   $q->where('date', '<=', $dateTo);
            }], 'amount')
            ->withSum(['receivingVouchers as total_received' => function ($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) $q->where('date', '>=', $dateFrom);
                if ($dateTo)   $q->where('date', '<=', $dateTo);
            }], 'amount')
            ->withSum(['paymentVouchers as total_paid' => function ($q) use ($dateFrom, $dateTo) {
                $q->where('paid_to_type', 'other'); // standard payment voucher path
                if ($dateFrom) $q->where('date', '>=', $dateFrom);
                if ($dateTo)   $q->where('date', '<=', $dateTo);
            }], 'amount')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('name');

        $parties = $partyQuery->get();

        $partyRows = $parties->map(function ($party) {
            $recDue  = (float) $party->total_receivable_due;
            $recPaid = (float) $party->total_received;
            $netRec  = round($recDue - $recPaid, 2);

            $payDue  = (float) $party->total_payable_due;
            $payPaid = (float) $party->total_paid;
            $netPay  = round($payDue - $payPaid, 2);

            return [
                'party'    => $party,
                'rec_due'  => $recDue,
                'rec_paid' => $recPaid,
                'net_rec'  => $netRec,
                'pay_due'  => $payDue,
                'pay_paid' => $payPaid,
                'net_pay'  => $netPay,
            ];
        });

        // Keep active parties with pending balances
        $partyRows = $partyRows->filter(function ($row) {
            return abs($row['net_rec']) > 0.01 || abs($row['net_pay']) > 0.01;
        })->values();

        $partyTotals = [
            'rec_due'  => $partyRows->sum('rec_due'),
            'rec_paid' => $partyRows->sum('rec_paid'),
            'net_rec'  => $partyRows->sum('net_rec'),
            'pay_due'  => $partyRows->sum('pay_due'),
            'pay_paid' => $partyRows->sum('pay_paid'),
            'net_pay'  => $partyRows->sum('net_pay'),
        ];

        // ── 3. General cash book metrics ────────────────────────────────────
        $accounts = PaymentAccount::where('is_active', true)
            ->withSum('receivingVouchers', 'amount')
            ->withSum('generalReceivingVouchers', 'amount')
            ->withSum('paymentVouchers', 'amount')
            ->withSum('expenses', 'amount')
            ->get();

        $totalCashBalance = $accounts->sum(fn($a) => $a->current_balance);
        $disposableAmount = $totalCashBalance - $totalOwnersPending;

        return [
            // Owners
            'ownerRows'           => $ownerRows,
            'totalOwnersDue'      => $totalOwnersDue,
            'totalOwnersPaid'     => $totalOwnersPaid,
            'totalOwnersPending'  => $totalOwnersPending,
            
            // Parties
            'partyRows'           => $partyRows,
            'partyTotals'         => $partyTotals,

            // Overall
            'totalIncome'         => $totalIncome,
            'totalTenantIncome'   => $totalTenantIncome,
            'totalPartyIncome'    => $totalPartyIncome,
            'totalCashBalance'    => $totalCashBalance,
            'disposableAmount'    => $disposableAmount,
            'accounts'            => $accounts,

            // Filters
            'search'              => $search,
            'dateFrom'            => $dateFrom,
            'dateTo'              => $dateTo,
            'generatedAt'         => now(),
        ];
    }
}
