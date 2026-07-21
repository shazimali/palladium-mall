<?php

namespace App\Http\Controllers;

use App\Models\Party;
use App\Models\PartyDue;
use App\Models\GeneralReceivingVoucher;
use App\Models\PaymentVoucher;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class PartyLedgerController extends Controller
{
    /**
     * Display the party ledger.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('ledgers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $parties = Party::orderBy('name')->get();
        $selectedParty = null;
        $ledgerEntries = collect();
        $summary = [
            'total_due_receivable' => 0.0,
            'total_received' => 0.0,
            'net_receivable' => 0.0,
            'total_due_payable' => 0.0,
            'total_paid' => 0.0,
            'net_payable' => 0.0,
        ];

        if ($request->filled('party_id')) {
            $selectedParty = Party::findOrFail($request->party_id);
            $partyId = $selectedParty->id;

            // 1. Fetch Dues
            $dues = PartyDue::where('party_id', $partyId)->get();

            // 2. Fetch Receipts (General Receiving Vouchers)
            $receipts = GeneralReceivingVoucher::where('party_id', $partyId)->get();

            // 3. Fetch Payments (Payment Vouchers of type 'other' linked to this party)
            $payments = PaymentVoucher::where('party_id', $partyId)
                ->where('paid_to_type', 'other')
                ->get();

            // Calculate summaries
            $summary['total_due_receivable'] = (float) $dues->where('type', 'receivable')->sum('amount');
            $summary['total_received'] = (float) $receipts->sum('amount');
            $summary['net_receivable'] = $summary['total_due_receivable'] - $summary['total_received'];

            $summary['total_due_payable'] = (float) $dues->where('type', 'payable')->sum('amount');
            $summary['total_paid'] = (float) $payments->sum('amount');
            $summary['net_payable'] = $summary['total_due_payable'] - $summary['total_paid'];

            // Combine into unified ledger entries
            // Due Receivable is Dr (Debits what they owe us)
            // Receipt is Cr (Credits what they owe us)
            // Due Payable is Cr (Credits what we owe them)
            // Payment is Dr (Debits what we owe them)
            
            // Dues
            foreach ($dues as $due) {
                $ledgerEntries->push([
                    'id' => $due->id,
                    'is_due' => true,
                    'date' => $due->date,
                    'created_at' => $due->created_at,
                    'ref' => $due->reference ?? '—',
                    'type' => $due->type === 'receivable' ? 'Due Receivable' : 'Due Payable',
                    'description' => $due->notes ?? ($due->type === 'receivable' ? 'Due Receivable Logged' : 'Due Payable Logged'),
                    'debit' => $due->type === 'receivable' ? (float)$due->amount : 0.0,
                    'credit' => $due->type === 'payable' ? (float)$due->amount : 0.0,
                ]);
            }

            // Receipts
            foreach ($receipts as $receipt) {
                $ledgerEntries->push([
                    'id' => $receipt->id,
                    'is_due' => false,
                    'date' => $receipt->date,
                    'created_at' => $receipt->created_at,
                    'ref' => $receipt->voucher_no,
                    'type' => 'Receipt (General)',
                    'description' => $receipt->notes ?? 'Received Inflow',
                    'debit' => 0.0,
                    'credit' => (float)$receipt->amount,
                ]);
            }

            // Payments
            foreach ($payments as $payment) {
                $ledgerEntries->push([
                    'id' => $payment->id,
                    'is_due' => false,
                    'date' => $payment->date,
                    'created_at' => $payment->created_at,
                    'ref' => $payment->voucher_no,
                    'type' => $payment->is_advance ? 'Payment (Advance)' : 'Payment',
                    'description' => $payment->notes ?? 'Paid Outflow',
                    'debit' => (float)$payment->amount,
                    'credit' => 0.0,
                ]);
            }

            // Sort chronologically
            $ledgerEntries = $ledgerEntries->sortBy(function ($item) {
                $date = $item['date'] instanceof Carbon ? $item['date'] : Carbon::parse($item['date']);
                $createdAt = $item['created_at'] instanceof Carbon ? $item['created_at'] : Carbon::parse($item['created_at']);
                return $date->format('Y-m-d') . '_' . $createdAt->format('Y-m-d H:i:s');
            })->values();

            // Calculate running balance per row
            $runningBalance = 0.0;
            $ledgerEntries = $ledgerEntries->map(function ($entry) use (&$runningBalance) {
                $runningBalance += $entry['debit'] - $entry['credit'];
                $entry['balance'] = $runningBalance;
                return $entry;
            });
        }

        return view('ledgers.party', [
            'parties' => $parties,
            'selectedParty' => $selectedParty,
            'ledgerEntries' => $ledgerEntries,
            'summary' => $summary,
        ]);
    }

    /**
     * Store a new due record.
     */
    public function storeDue(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('ledgers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'party_id' => ['required', 'exists:parties,id'],
            'type'     => ['required', 'string', 'in:receivable,payable'],
            'amount'   => ['required', 'numeric', 'min:1'],
            'date'     => ['required', 'date'],
            'reference'=> ['nullable', 'string', 'max:255'],
            'notes'    => ['nullable', 'string', 'max:1000'],
        ]);

        $data['user_id'] = auth()->id();

        PartyDue::create($data);

        return redirect()->back()->with('success', 'Due record added successfully.');
    }

    /**
     * Delete a due record.
     */
    public function destroyDue(PartyDue $due): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('ledgers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $due->delete();

        return redirect()->back()->with('success', 'Due record deleted successfully.');
    }

    /**
     * Print the party ledger.
     */
    public function print(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('ledgers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $selectedParty = Party::findOrFail($request->party_id);
        $partyId = $selectedParty->id;

        $dues = PartyDue::where('party_id', $partyId)->get();
        $receipts = GeneralReceivingVoucher::where('party_id', $partyId)->get();
        $payments = PaymentVoucher::where('party_id', $partyId)
            ->where('paid_to_type', 'other')
            ->get();

        $summary = [
            'total_due_receivable' => (float) $dues->where('type', 'receivable')->sum('amount'),
            'total_received' => (float) $receipts->sum('amount'),
            'net_receivable' => 0.0,
            'total_due_payable' => (float) $dues->where('type', 'payable')->sum('amount'),
            'total_paid' => (float) $payments->sum('amount'),
            'net_payable' => 0.0,
        ];
        $summary['net_receivable'] = $summary['total_due_receivable'] - $summary['total_received'];
        $summary['net_payable'] = $summary['total_due_payable'] - $summary['total_paid'];

        $ledgerEntries = collect();

        // Combine chronologically
        foreach ($dues as $due) {
            $ledgerEntries->push([
                'date' => $due->date,
                'created_at' => $due->created_at,
                'ref' => $due->reference ?? '—',
                'type' => $due->type === 'receivable' ? 'Due Receivable' : 'Due Payable',
                'description' => $due->notes ?? ($due->type === 'receivable' ? 'Due Receivable Logged' : 'Due Payable Logged'),
                'debit' => $due->type === 'receivable' ? (float)$due->amount : 0.0,
                'credit' => $due->type === 'payable' ? (float)$due->amount : 0.0,
            ]);
        }

        foreach ($receipts as $receipt) {
            $ledgerEntries->push([
                'date' => $receipt->date,
                'created_at' => $receipt->created_at,
                'ref' => $receipt->voucher_no,
                'type' => 'Receipt (General)',
                'description' => $receipt->notes ?? 'Received Inflow',
                'debit' => 0.0,
                'credit' => (float)$receipt->amount,
            ]);
        }

        foreach ($payments as $payment) {
            $ledgerEntries->push([
                'date' => $payment->date,
                'created_at' => $payment->created_at,
                'ref' => $payment->voucher_no,
                'type' => $payment->is_advance ? 'Payment (Advance)' : 'Payment',
                'description' => $payment->notes ?? 'Paid Outflow',
                'debit' => (float)$payment->amount,
                'credit' => 0.0,
            ]);
        }

        $ledgerEntries = $ledgerEntries->sortBy(function ($item) {
            $date = $item['date'] instanceof Carbon ? $item['date'] : Carbon::parse($item['date']);
            $createdAt = $item['created_at'] instanceof Carbon ? $item['created_at'] : Carbon::parse($item['created_at']);
            return $date->format('Y-m-d') . '_' . $createdAt->format('Y-m-d H:i:s');
        })->values();

        // Calculate running balance per row
        $runningBalance = 0.0;
        $ledgerEntries = $ledgerEntries->map(function ($entry) use (&$runningBalance) {
            $runningBalance += $entry['debit'] - $entry['credit'];
            $entry['balance'] = $runningBalance;
            return $entry;
        });

        return view('ledgers.party_print', [
            'selectedParty' => $selectedParty,
            'ledgerEntries' => $ledgerEntries,
            'summary' => $summary,
        ]);
    }
}
