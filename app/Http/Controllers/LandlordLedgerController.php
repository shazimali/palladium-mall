<?php

namespace App\Http\Controllers;

use App\Models\Landlord;
use App\Models\ReceivingVoucher;
use App\Models\LandlordPayable;
use Illuminate\Http\Request;

class LandlordLedgerController extends Controller
{
    public function index(Request $request)
    {
        $query = Landlord::query()->with(['ownerships', 'payables']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('phone', 'like', "%{$term}%")
                  ->orWhere('email', 'like', "%{$term}%")
                  ->orWhere('cnic', 'like', "%{$term}%");
            });
        }

        $landlords = $query->paginate(15)->withQueryString();
        return view('landlord_ledgers.index', compact('landlords'));
    }

    public function show(Landlord $landlord, Request $request)
    {
        // 1. Calculate Opening Balance from Unit Ownerships
        // credit_amount = total_amount - received_amount
        $openingBalance = $landlord->ownerships->sum('credit_amount');

        // 2. Query receiving vouchers
        $voucherQuery = ReceivingVoucher::where('owner_id', $landlord->id);

        // 3. Query payables
        $payableQuery = $landlord->payables();

        // Apply date filters if provided
        if ($request->filled('date_from')) {
            $voucherQuery->where('date', '>=', $request->date_from);
            $payableQuery->where('due_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $voucherQuery->where('date', '<=', $request->date_to);
            $payableQuery->where('due_date', '<=', $request->date_to);
        }

        $vouchers = $voucherQuery->orderBy('date', 'asc')->get();
        $payables = $payableQuery->orderBy('due_date', 'asc')->get();

        // Calculate all-time total paid
        $totalPaid = ReceivingVoucher::where('owner_id', $landlord->id)->sum('amount');

        // 4. Merge and sort transactions for the ledger view
        $transactions = collect();
        
        foreach ($vouchers as $v) {
            $transactions->push([
                'date' => $v->date,
                'type' => 'receipt',
                'description' => 'Payment Received: ' . $v->voucher_no . ($v->notes ? ' - ' . $v->notes : ''),
                'debit' => 0,
                'credit' => $v->amount,
                'model' => $v,
            ]);
        }

        foreach ($payables as $p) {
            $transactions->push([
                'date' => $p->due_date ?? $p->created_at,
                'type' => 'payable',
                'description' => 'Payable Generated: ' . $p->title,
                'debit' => $p->amount, // Note: We might display this but not add to running balance if it's an installment of the opening balance.
                'credit' => 0,
                'model' => $p,
            ]);
        }

        $transactions = $transactions->sortBy('date')->values();

        return view('landlord_ledgers.show', compact('landlord', 'openingBalance', 'totalPaid', 'transactions'));
    }
}
