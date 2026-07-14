<?php

namespace App\Http\Controllers;

use App\Models\Party;
use Barryvdh\Dompdf\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartyDuesReportController extends Controller
{
    /**
     * Display the Global Party Dues Summary Report.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $this->buildReportData($request);

        return view('reports.party_dues', array_merge($data, [
            'title' => 'Party Receivables & Payables Summary',
        ]));
    }

    /**
     * Export the report as PDF.
     */
    public function exportPdf(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $this->buildReportData($request);

        $pdf = Pdf::loadView('reports.party_dues_pdf', array_merge($data, [
            'title' => 'Party Receivables & Payables Summary',
        ]))->setPaper('a4', 'landscape'); // landscape layout is better for wide table

        return $pdf->download('party_dues_summary_' . now()->format('Y_m_d') . '.pdf');
    }

    // ─────────────────────────────────────────────────────────────────────────

    private function buildReportData(Request $request): array
    {
        $search   = $request->query('search');
        $dateFrom = $request->query('date_from');
        $dateTo   = $request->query('date_to');

        $query = Party::query()
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
                $q->where('paid_to_type', 'other');
                if ($dateFrom) $q->where('date', '>=', $dateFrom);
                if ($dateTo)   $q->where('date', '<=', $dateTo);
            }], 'amount')
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })
            ->orderBy('name');

        $parties = $query->get();

        // Map and compute net outstanding balances
        $rows = $parties->map(function ($party) {
            $recDue  = (float) $party->total_receivable_due;
            $recPaid = (float) $party->total_received;
            $netRec  = round($recDue - $recPaid, 2);

            $payDue  = (float) $party->total_payable_due;
            $payPaid = (float) $party->total_paid;
            $netPay  = round($payDue - $payPaid, 2);

            return [
                'party'        => $party,
                'rec_due'      => $recDue,
                'rec_paid'     => $recPaid,
                'net_rec'      => $netRec,
                'pay_due'      => $payDue,
                'pay_paid'     => $payPaid,
                'net_pay'      => $netPay,
            ];
        });

        // ONLY keep parties which have Receivable (Owed to Us) != 0 OR Payable (We Owe) != 0
        $rows = $rows->filter(function ($row) {
            return abs($row['net_rec']) > 0.01 || abs($row['net_pay']) > 0.01;
        })->values();

        // Compute overall totals
        $totals = [
            'rec_due'  => $rows->sum('rec_due'),
            'rec_paid' => $rows->sum('rec_paid'),
            'net_rec'  => $rows->sum('net_rec'),
            'pay_due'  => $rows->sum('pay_due'),
            'pay_paid' => $rows->sum('pay_paid'),
            'net_pay'  => $rows->sum('net_pay'),
        ];

        return [
            'rows'        => $rows,
            'totals'      => $totals,
            'search'      => $search,
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'generatedAt' => now(),
        ];
    }
}
