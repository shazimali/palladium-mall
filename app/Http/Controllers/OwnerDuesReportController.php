<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\PaymentAccount;
use App\Models\ReceivingVoucher;
use App\Models\GeneralReceivingVoucher;
use Barryvdh\Dompdf\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OwnerDuesReportController extends Controller
{
    /**
     * Display the Owner Dues Report (running-balance approach).
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $this->buildReportData();

        return view('owner_dues.index', array_merge($data, [
            'title' => 'Owner Dues Report',
        ]));
    }

    /**
     * Export the Owner Dues Report as PDF.
     */
    public function exportPdf(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $this->buildReportData();

        $pdf = Pdf::loadView('owner_dues.pdf', array_merge($data, [
            'title' => 'Owner Dues Report',
        ]))->setPaper('a4', 'portrait');

        return $pdf->download('owner_dues_report_' . now()->format('Y_m_d') . '.pdf');
    }

    /**
     * AJAX: return pending balance for a specific owner.
     * Used in the Payment Voucher create/edit form.
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

    // ─────────────────────────────────────────────────────────────────────────

    private function buildReportData(): array
    {
        $owners = Owner::orderBy('name')->get();

        // ── Global income totals ──────────────────────────────────────────────
        $totalTenantIncome = (float) ReceivingVoucher::where('received_from_type', 'tenant')->sum('amount');
        $totalPartyIncome  = (float) GeneralReceivingVoucher::sum('amount');
        $totalIncome       = $totalTenantIncome + $totalPartyIncome;

        // ── Per-owner calculations ────────────────────────────────────────────
        $ownerRows          = [];
        $totalOwnersDue     = 0.00;
        $totalOwnersPaid    = 0.00;
        $totalOwnersPending = 0.00;

        foreach ($owners as $owner) {
            $due     = $owner->totalIncomeDue();
            $paid    = $owner->totalPaid();
            $pending = max(0.00, round($due - $paid, 2));

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

        // ── Payment account balances ──────────────────────────────────────────
        $accounts = PaymentAccount::where('is_active', true)
            ->withSum('receivingVouchers', 'amount')
            ->withSum('generalReceivingVouchers', 'amount')
            ->withSum('paymentVouchers', 'amount')
            ->withSum('expenses', 'amount')
            ->get();

        $totalCashBalance = $accounts->sum(fn($a) => $a->current_balance);

        // ── Disposable Amount ─────────────────────────────────────────────────
        // = Cash in accounts − pending owner dues
        $disposableAmount = $totalCashBalance - $totalOwnersPending;

        return [
            'ownerRows'           => $ownerRows,
            'totalIncome'         => $totalIncome,
            'totalTenantIncome'   => $totalTenantIncome,
            'totalPartyIncome'    => $totalPartyIncome,
            'totalOwnersDue'      => $totalOwnersDue,
            'totalOwnersPaid'     => $totalOwnersPaid,
            'totalOwnersPending'  => $totalOwnersPending,
            'totalCashBalance'    => $totalCashBalance,
            'disposableAmount'    => $disposableAmount,
            'accounts'            => $accounts,
            'generatedAt'         => now(),
        ];
    }
}
