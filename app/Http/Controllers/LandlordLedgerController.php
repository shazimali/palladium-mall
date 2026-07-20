<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\ReceivingVoucher;
use App\Exports\LandlordLedgerExport;
use Barryvdh\Dompdf\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LandlordLedgerController extends Controller
{
    /**
     * Display the searchable landlord selector and the generated ledger statement.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('landlords.view')) {
            abort(403, 'Unauthorized action.');
        }

        $landlords = Landlord::orderBy('name')->get();
        
        $landlordId = $request->query('landlord_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        $ledgerData = null;
        if ($landlordId) {
            $ledgerData = $this->getLandlordLedgerData($landlordId, $dateFrom, $dateTo);
        }

        return view('landlord_ledgers.index', [
            'title'      => 'Landlord Ledger',
            'landlords'  => $landlords,
            'landlordId' => $landlordId,
            'dateFrom'   => $dateFrom,
            'dateTo'     => $dateTo,
            'ledgerData' => $ledgerData,
        ]);
    }

    /**
     * Show method - Redirects to index for backward-compatibility or single-page view.
     */
    public function show(Landlord $landlord): RedirectResponse
    {
        return redirect()->route('landlord_ledgers.index', ['landlord_id' => $landlord->id]);
    }

    /**
     * Export Landlord Ledger to Excel.
     */
    public function exportExcel(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('landlords.view')) {
            abort(403, 'Unauthorized action.');
        }

        $landlordId = $request->query('landlord_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (!$landlordId) {
            return back()->with('error', 'Select a landlord to export.');
        }

        $ledgerData = $this->getLandlordLedgerData($landlordId, $dateFrom, $dateTo);

        return Excel::download(
            new LandlordLedgerExport(
                $ledgerData['entries'],
                'Landlord Ledger - ' . $ledgerData['landlord']->name,
                $ledgerData['summary']
            ),
            'landlord_ledger_' . str_replace(' ', '_', strtolower($ledgerData['landlord']->name)) . '.xlsx'
        );
    }

    /**
     * Export Landlord Ledger to PDF.
     */
    public function exportPdf(Request $request)
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('landlords.view')) {
            abort(403, 'Unauthorized action.');
        }

        $landlordId = $request->query('landlord_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (!$landlordId) {
            return back()->with('error', 'Select a landlord to export.');
        }

        $ledgerData = $this->getLandlordLedgerData($landlordId, $dateFrom, $dateTo);

        $pdf = Pdf::loadView('ledgers.pdf', [
            'type'      => 'landlord',
            'entries'   => $ledgerData['entries'],
            'summary'   => $ledgerData['summary'],
            'dateFrom'  => $dateFrom,
            'dateTo'    => $dateTo,
            'title'     => 'Landlord Ledger — ' . $ledgerData['landlord']->name,
            'metaItems' => [
                ['label' => 'Landlord Name', 'value' => $ledgerData['landlord']->name],
                ['label' => 'Phone', 'value' => $ledgerData['landlord']->phone ?? '—'],
            ]
        ]);

        return $pdf->download('landlord_ledger_' . str_replace(' ', '_', strtolower($ledgerData['landlord']->name)) . '.pdf');
    }

    /**
     * View Printable Statement in Pop-up.
     */
    public function print(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('landlords.view')) {
            abort(403, 'Unauthorized action.');
        }

        $landlordId = $request->query('landlord_id');
        $dateFrom = $request->query('date_from');
        $dateTo = $request->query('date_to');

        if (!$landlordId) {
            abort(404, 'Select a landlord to print.');
        }

        $ledgerData = $this->getLandlordLedgerData($landlordId, $dateFrom, $dateTo);

        return view('ledgers.print', [
            'type'         => 'landlord',
            'entries'      => $ledgerData['entries'],
            'summary'      => $ledgerData['summary'],
            'dateFrom'     => $dateFrom,
            'dateTo'       => $dateTo,
            'pageTitle'    => 'Landlord Ledger — ' . $ledgerData['landlord']->name,
            'metaItems'    => [
                ['label' => 'Landlord Name', 'value' => $ledgerData['landlord']->name],
                ['label' => 'Phone', 'value' => $ledgerData['landlord']->phone ?? '—'],
            ],
            'infoCards'    => [
                ['label' => 'Total Unit Value Owed', 'value' => 'Rs. ' . number_format($ledgerData['openingBalance'], 2)],
                ['label' => 'Total Payments Received', 'value' => 'Rs. ' . number_format($ledgerData['totalPaid'], 2)],
                ['label' => 'Outstanding Balance', 'value' => 'Rs. ' . number_format($ledgerData['pendingBalance'], 2)],
            ]
        ]);
    }

    /**
     * Compile chronological ledger entries for a Landlord.
     */
    private function getLandlordLedgerData($landlordId, $dateFrom, $dateTo): array
    {
        $landlord = Landlord::with(['ownerships'])->findOrFail($landlordId);
        $entries = collect();

        // 1. Calculate opening unit value debt (credit_amount sum on ownership records)
        $openingBalance = (float) $landlord->ownerships->sum('credit_amount');

        // 2. Prior payments (all-time ReceivingVouchers prior to dateFrom + all-time paid extra_payments prior to dateFrom)
        $priorPaid = 0.00;
        if ($dateFrom) {
            $priorPaid += (float) ReceivingVoucher::where('received_from_type', 'owner')
                ->where('owner_id', $landlordId)
                ->where('date', '<', $dateFrom)
                ->sum('amount');

            $priorPaid += (float) \App\Models\Payment::where('landlord_id', $landlordId)
                ->where('type', 'extra_payment')
                ->where('amount_paid', '>', 0)
                ->where(function($q) use ($dateFrom) {
                    $q->whereNull('paid_at')->where('month', '<', $dateFrom)
                      ->orWhere('paid_at', '<', $dateFrom);
                })
                ->sum('amount_paid');
        }

        $carriedForwardBalance = $openingBalance - $priorPaid;

        // Prepend opening balance carried forward row
        $entries->push([
            'date' => $dateFrom ? Carbon::parse($dateFrom)->subDay() : ($landlord->created_at ?? Carbon::now()),
            'voucher_no' => '—',
            'type' => 'Opening Balance',
            'description' => $dateFrom ? 'Opening Balance (Carried Forward)' : 'Opening Balance',
            'debit' => $carriedForwardBalance,
            'credit' => 0.00,
            'is_opening' => true,
        ]);

        // 3. Current period payments (owner receiving vouchers)
        $payments = ReceivingVoucher::where('received_from_type', 'owner')
            ->where('owner_id', $landlordId)
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->orderBy('date', 'asc')
            ->get();

        foreach ($payments as $v) {
            $entries->push([
                'date' => $v->date,
                'voucher_no' => $v->voucher_no,
                'type' => 'Payment',
                'description' => 'Payment Received: ' . $v->voucher_no . ($v->notes ? ' - ' . $v->notes : ''),
                'debit' => 0.00,
                'credit' => (float)$v->amount,
                'is_opening' => false,
                'model' => $v,
            ]);
        }

        // 4. Current period extra payments
        $extraPayments = \App\Models\Payment::where('landlord_id', $landlordId)
            ->where('type', 'extra_payment')
            ->where('amount_paid', '>', 0)
            ->with(['receivingVouchers', 'unit'])
            ->where(function($q) use ($dateFrom, $dateTo) {
                if ($dateFrom) {
                    $q->where(fn($sub) => $sub->whereNull('paid_at')->where('month', '>=', $dateFrom)->orWhere('paid_at', '>=', $dateFrom));
                }
                if ($dateTo) {
                    $q->where(fn($sub) => $sub->whereNull('paid_at')->where('month', '<=', $dateTo)->orWhere('paid_at', '<=', $dateTo));
                }
            })
            ->get();

        foreach ($extraPayments as $ep) {
            $v = $ep->receivingVouchers->first();
            $desc = 'Extra Payment Paid (Unit ' . ($ep->unit?->unit_number ?? '—') . '): ' . $ep->notes;
            $entries->push([
                'date' => $ep->paid_at ? Carbon::parse($ep->paid_at) : $ep->month,
                'voucher_no' => $v?->voucher_no ?? $ep->receipt_no ?? '—',
                'type' => 'Extra Payment',
                'description' => $desc,
                'debit' => 0.00,
                'credit' => (float)$ep->amount_paid,
                'is_opening' => false,
                'model' => $v,
            ]);
        }

        // Sort all entries chronologically, keeping opening balance row first
        $entries = $entries->sortBy(function ($e) {
            return ($e['is_opening'] ?? false) ? '0000-00-00' : $e['date']->format('Y-m-d');
        })->values();

        // 5. Calculate running balance
        $runningBalance = $carriedForwardBalance;
        $totalDebit = 0.00;
        $totalCredit = 0.00;

        $entries = $entries->map(function ($entry) use (&$runningBalance, &$totalDebit, &$totalCredit) {
            if (empty($entry['is_opening'])) {
                $runningBalance -= $entry['credit'];
                $totalCredit += $entry['credit'];
            }
            $entry['running_balance'] = $runningBalance;
            return $entry;
        });

        // Cumulative aggregates
        $allTimePaid = (float) ReceivingVoucher::where('received_from_type', 'owner')
            ->where('owner_id', $landlordId)
            ->sum('amount');

        $allTimeExtraPaid = (float) \App\Models\Payment::where('landlord_id', $landlordId)
            ->where('type', 'extra_payment')
            ->sum('amount_paid');

        $allTimePaid += $allTimeExtraPaid;

        return [
            'landlord'       => $landlord,
            'entries'        => $entries,
            'openingBalance' => $openingBalance,
            'totalPaid'      => $allTimePaid,
            'pendingBalance' => $openingBalance - $allTimePaid,
            'summary'        => [
                'total_debit'  => $openingBalance,
                'total_credit' => $totalCredit,
                'net_balance'  => $openingBalance - $allTimePaid,
            ]
        ];
    }
}
