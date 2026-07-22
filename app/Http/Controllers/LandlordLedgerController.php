<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\ReceivingVoucher;
use App\Models\GeneralReceivingVoucher;
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

        $summaryCards = [
            ['label' => 'Total Unit Value Owed',    'value' => 'Rs. ' . number_format($ledgerData['openingBalance'], 2), 'color' => 's-blue'],
            ['label' => 'Total Payments Received', 'value' => 'Rs. ' . number_format($ledgerData['totalPaid'], 2),      'color' => 's-green'],
            ['label' => 'Outstanding Balance',     'value' => 'Rs. ' . number_format($ledgerData['pendingBalance'], 2),  'color' => $ledgerData['pendingBalance'] > 0 ? 's-orange' : 's-neutral'],
        ];

        $columns = [
            ['key' => 'date_str',        'label' => 'Date'],
            ['key' => 'unit_number',     'label' => 'Flat/Shop'],
            ['key' => 'description',     'label' => 'Description'],
            ['key' => 'voucher_no',      'label' => 'Voucher / Ref #', 'td_class' => 'mono'],
            ['key' => 'debit',           'label' => 'Debit (Payable)', 'type' => 'debit',   'class' => 'text-right'],
            ['key' => 'credit',          'label' => 'Credit (Paid)',   'type' => 'credit',  'class' => 'text-right'],
            ['key' => 'running_balance', 'label' => 'Running Balance', 'type' => 'balance', 'class' => 'text-right'],
        ];

        $rows = $ledgerData['entries']->map(function($e) {
            $e['date_str'] = $e['date']->format('d M Y');
            return $e;
        })->toArray();

        return view('ledgers.print_page', [
            'type'         => 'landlord',
            'pageTitle'    => 'Landlord Ledger — ' . $ledgerData['landlord']->name,
            'filterChips'  => [
                ['label' => 'Date From', 'value' => $dateFrom ?? 'All Time'],
                ['label' => 'Date To', 'value' => $dateTo ?? 'All Time'],
            ],
            'metaItems'    => [
                ['label' => 'Landlord Name', 'value' => $ledgerData['landlord']->name],
                ['label' => 'Phone', 'value' => $ledgerData['landlord']->phone ?? '—'],
            ],
            'summaryCards' => $summaryCards,
            'columns'      => $columns,
            'rows'         => $rows,
        ]);
    }

    /**
     * Compile chronological ledger entries for a Landlord.
     */
    private function getLandlordLedgerData($landlordId, $dateFrom, $dateTo): array
    {
        $landlord = Landlord::with(['ownerships.unit'])->findOrFail($landlordId);
        $entries = collect();

        // 1. Total unit value owed across all ownership records
        $openingBalance = (float) $landlord->ownerships->sum('credit_amount');

        // Fallback unit number if landlord owns exactly 1 unit
        $singleUnitNo = $landlord->ownerships->count() === 1
            ? $landlord->ownerships->first()->unit?->unit_number
            : null;

        // 2. Prior payments / payouts if dateFrom filter is provided
        $priorPaid = 0.00;
        $priorCharged = 0.00;
        if ($dateFrom) {
            $priorPaid += (float) ReceivingVoucher::where('received_from_type', 'owner')
                ->where('owner_id', $landlordId)
                ->where('date', '<', $dateFrom)
                ->sum('amount');

            $priorPaid += (float) GeneralReceivingVoucher::where('landlord_id', $landlordId)
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

            $priorCharged += (float) \App\Models\PaymentVoucher::where('paid_to_type', 'landlord')
                ->where('landlord_id', $landlordId)
                ->where('date', '<', $dateFrom)
                ->sum('amount');
        }

        // 3. Push a separate entry row for EACH unit record (ownership) of the landlord
        if ($landlord->ownerships->isNotEmpty()) {
            foreach ($landlord->ownerships as $ownership) {
                $unitNo = $ownership->unit?->unit_number ?? '—';
                $startDate = $ownership->start_date
                    ? Carbon::parse($ownership->start_date)
                    : ($ownership->created_at ? Carbon::parse($ownership->created_at) : ($landlord->created_at ? Carbon::parse($landlord->created_at) : Carbon::now()));

                // Check if this ownership record is prior to dateFrom or within period
                $isPrior = $dateFrom && $startDate->format('Y-m-d') < $dateFrom;
                $entryDate = $isPrior ? Carbon::parse($dateFrom)->subDay() : $startDate;

                $descParts = [];
                if ($ownership->total_amount > 0) {
                    $descParts[] = 'Total: Rs. ' . number_format($ownership->total_amount, 2);
                }
                if ($ownership->received_amount > 0) {
                    $descParts[] = 'Received: Rs. ' . number_format($ownership->received_amount, 2);
                }
                $detailsStr = !empty($descParts) ? ' (' . implode(', ', $descParts) . ')' : '';

                $typeStr = $isPrior ? 'Opening Balance' : 'Unit Record';
                $descStr = $isPrior
                    ? 'Opening Balance (Carried Forward) — ' . $unitNo . $detailsStr
                    : 'Unit Record — ' . $unitNo . $detailsStr;

                $entries->push([
                    'date'        => $entryDate,
                    'voucher_no'  => $ownership->file_no ?? '—',
                    'type'        => $typeStr,
                    'description' => $descStr,
                    'debit'       => (float) $ownership->credit_amount,
                    'credit'      => 0.00,
                    'is_opening'  => true,
                    'unit_number' => $unitNo,
                    'model'       => null,
                ]);
            }
        } else {
            // Fallback opening row if landlord has no registered unit ownership records
            $entries->push([
                'date'        => $dateFrom ? Carbon::parse($dateFrom)->subDay() : ($landlord->created_at ?? Carbon::now()),
                'voucher_no'  => '—',
                'type'        => 'Opening Balance',
                'description' => $dateFrom ? 'Opening Balance (Carried Forward)' : 'Opening Balance',
                'debit'       => 0.00,
                'credit'      => 0.00,
                'is_opening'  => true,
                'unit_number' => '—',
                'model'       => null,
            ]);
        }

        // If filtering by dateFrom and there are prior payments or prior payouts, add carried forward adjustment entries
        if ($dateFrom) {
            if ($priorPaid > 0) {
                $entries->push([
                    'date'        => Carbon::parse($dateFrom)->subDay(),
                    'voucher_no'  => '—',
                    'type'        => 'Opening Balance',
                    'description' => 'Prior Payments Received (Carried Forward)',
                    'debit'       => 0.00,
                    'credit'      => $priorPaid,
                    'is_opening'  => true,
                    'unit_number' => '—',
                    'model'       => null,
                ]);
            }
            if ($priorCharged > 0) {
                $entries->push([
                    'date'        => Carbon::parse($dateFrom)->subDay(),
                    'voucher_no'  => '—',
                    'type'        => 'Opening Balance',
                    'description' => 'Prior Payouts (Carried Forward)',
                    'debit'       => $priorCharged,
                    'credit'      => 0.00,
                    'is_opening'  => true,
                    'unit_number' => '—',
                    'model'       => null,
                ]);
            }
        }

        // 4. Current period payments (owner receiving vouchers)
        $payments = ReceivingVoucher::where('received_from_type', 'owner')
            ->where('owner_id', $landlordId)
            ->with(['payments.unit', 'tenant.unit'])
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->orderBy('date', 'asc')
            ->get();

        foreach ($payments as $v) {
            $unitNo = $v->payments->first()?->unit?->unit_number ?? $v->tenant?->unit?->unit_number ?? $singleUnitNo;
            $entries->push([
                'date'        => $v->date,
                'voucher_no'  => $v->voucher_no,
                'type'        => 'Payment',
                'description' => 'Payment Received: ' . $v->voucher_no . ($v->notes ? ' - ' . $v->notes : ''),
                'debit'       => 0.00,
                'credit'      => (float) $v->amount,
                'is_opening'  => false,
                'model'       => $v,
                'unit_number' => $unitNo ?: '—',
            ]);
        }

        // 4b. Current period General Receiving Vouchers
        $grvPayments = GeneralReceivingVoucher::where('landlord_id', $landlordId)
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->orderBy('date', 'asc')
            ->get();

        foreach ($grvPayments as $grv) {
            $unitNo = $singleUnitNo;
            $entries->push([
                'date'        => $grv->date,
                'voucher_no'  => $grv->voucher_no,
                'type'        => 'General Receipt',
                'description' => 'General Receipt: ' . $grv->voucher_no . ($grv->notes ? ' - ' . $grv->notes : ''),
                'debit'       => 0.00,
                'credit'      => (float) $grv->amount,
                'is_opening'  => false,
                'model'       => $grv,
                'unit_number' => $unitNo ?: '—',
            ]);
        }

        // 5. Current period extra payments
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
            $unitNo = $ep->unit?->unit_number ?? $singleUnitNo;
            $desc = 'Extra Payment Paid' . ($unitNo ? ' (Unit ' . $unitNo . ')' : '') . ': ' . $ep->notes;
            $entries->push([
                'date'        => $ep->paid_at ? Carbon::parse($ep->paid_at) : $ep->month,
                'voucher_no'  => $v?->voucher_no ?? $ep->receipt_no ?? '—',
                'type'        => 'Extra Payment',
                'description' => $desc,
                'debit'       => 0.00,
                'credit'      => (float) $ep->amount_paid,
                'is_opening'  => false,
                'model'       => $v,
                'unit_number' => $unitNo ?: '—',
            ]);
        }

        // 6. Current period payouts (payment vouchers paid to landlord)
        $payouts = \App\Models\PaymentVoucher::where('paid_to_type', 'landlord')
            ->where('landlord_id', $landlordId)
            ->with('unit')
            ->when($dateFrom, fn($q) => $q->where('date', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->where('date', '<=', $dateTo))
            ->orderBy('date', 'asc')
            ->get();

        foreach ($payouts as $pv) {
            $unitNo = $pv->unit?->unit_number ?? $singleUnitNo;
            $entries->push([
                'date'        => $pv->date,
                'voucher_no'  => $pv->voucher_no,
                'type'        => 'Payout',
                'description' => 'Payout: ' . $pv->voucher_no . ($pv->notes ? ' - ' . $pv->notes : ''),
                'debit'       => (float) $pv->amount,
                'credit'      => 0.00,
                'is_opening'  => false,
                'model'       => $pv,
                'unit_number' => $unitNo ?: '—',
            ]);
        }

        // Sort all entries: unit opening entries first (sorted by date/unit), then non-opening entries (sorted by date)
        $entries = $entries->sortBy(function ($e) {
            $prefix = ($e['is_opening'] ?? false) ? '0000-00-00_' : '1111-11-11_';
            return $prefix . $e['date']->format('Y-m-d') . '_' . ($e['unit_number'] ?? '');
        })->values();

        // 7. Calculate continuous running balance
        $runningBalance = 0.00;
        $totalDebit = 0.00;
        $totalCredit = 0.00;

        $entries = $entries->map(function ($entry) use (&$runningBalance, &$totalDebit, &$totalCredit) {
            $runningBalance += $entry['debit'] - $entry['credit'];
            $totalDebit += $entry['debit'];
            $totalCredit += $entry['credit'];
            $entry['running_balance'] = $runningBalance;
            return $entry;
        });

        // Cumulative aggregates for summary cards
        $allTimePaid = (float) ReceivingVoucher::where('received_from_type', 'owner')
            ->where('owner_id', $landlordId)
            ->sum('amount');

        $allTimeGrvPaid = (float) GeneralReceivingVoucher::where('landlord_id', $landlordId)
            ->sum('amount');

        $allTimeExtraPaid = (float) \App\Models\Payment::where('landlord_id', $landlordId)
            ->where('type', 'extra_payment')
            ->sum('amount_paid');

        $allTimePaid += $allTimeExtraPaid + $allTimeGrvPaid;

        $allTimePayouts = (float) \App\Models\PaymentVoucher::where('paid_to_type', 'landlord')
            ->where('landlord_id', $landlordId)
            ->sum('amount');

        $pendingBalance = $openingBalance - $allTimePaid + $allTimePayouts;

        return [
            'landlord'       => $landlord,
            'entries'        => $entries,
            'openingBalance' => $openingBalance,
            'totalPaid'      => $allTimePaid,
            'pendingBalance' => $pendingBalance,
            'summary'        => [
                'opening_balance' => $openingBalance,
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
                'net_balance'  => $pendingBalance,
            ]
        ];
    }
}
