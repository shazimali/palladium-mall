<?php

namespace App\Http\Controllers;

use App\Models\GeneralReceivingVoucher;
use App\Models\Party;
use App\Models\Landlord;
use App\Models\PaymentAccount;
use App\Models\ReceivingVoucher;
use App\Models\Payment;
use App\Models\PaymentVoucher;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class GeneralReceivingVoucherController extends Controller
{
    /**
     * Display a listing of general receiving vouchers.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('general_receiving_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = GeneralReceivingVoucher::with(['party', 'landlord', 'paymentAccount', 'fromPaymentAccount', 'user'])
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where('voucher_no', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhereHas('party', fn($p) => $p->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('landlord', fn($l) => $l->where('name', 'like', "%{$term}%"));
            })
            ->when($request->party_id, fn($q) => $q->where('party_id', $request->party_id))
            ->when($request->landlord_id, fn($q) => $q->where('landlord_id', $request->landlord_id))
            ->when($request->payment_account_id, fn($q) => $q->where('payment_account_id', $request->payment_account_id))
            ->when($request->start_date, fn($q) => $q->whereDate('date', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('date', '<=', $request->end_date));

        $totalAmount = (float) $query->sum('amount');

        $vouchers = $query->latest('date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();
        $parties = Party::orderBy('name')->get();
        $landlords = Landlord::orderBy('name')->get();

        return view('general_receiving_vouchers.index', [
            'title'           => 'General Receiving Vouchers',
            'vouchers'        => $vouchers,
            'paymentAccounts' => $paymentAccounts,
            'parties'         => $parties,
            'landlords'       => $landlords,
            'totalAmount'     => $totalAmount,
        ]);
    }

    /**
     * Show the form for creating a new general receiving voucher.
     */
    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('general_receiving_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $parties = Party::orderBy('name')->get();
        $landlords = Landlord::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('general_receiving_vouchers.create', [
            'title'           => 'New General Receiving Voucher',
            'parties'         => $parties,
            'landlords'       => $landlords,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Store a newly created general receiving voucher in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('general_receiving_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $rules = [
            'date'               => ['required', 'date'],
            'amount'             => ['required', 'numeric', 'min:1'],
            'received_from_type' => ['required', 'string', 'in:party,account,landlord'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ];

        if ($request->input('received_from_type') === 'account') {
            $rules['from_payment_account_id'] = ['required', 'exists:payment_accounts,id', 'different:payment_account_id'];
        } elseif ($request->input('received_from_type') === 'landlord') {
            $rules['landlord_id'] = ['required', 'exists:landlords,id'];
        } else {
            $rules['party_id'] = ['required', 'exists:parties,id'];
        }

        $data = $request->validate($rules);

        // ── Balance Guard for Source Account ──────────────────────────────
        if ($request->input('received_from_type') === 'account') {
            $sourceAccount = PaymentAccount::findOrFail($data['from_payment_account_id']);
            $currentBalance = $sourceAccount->current_balance;

            if ((float) $data['amount'] > $currentBalance + 0.01) {
                return back()->withInput()->withErrors([
                    'from_payment_account_id' => 'The selected Source Account (' . $sourceAccount->name . ') does not have sufficient balance to transfer. Current balance: Rs. ' . number_format($currentBalance, 2) . '.',
                ]);
            }

            $data['party_id'] = null;
            $data['landlord_id'] = null;
        } elseif ($request->input('received_from_type') === 'landlord') {
            $data['party_id'] = null;
            $data['from_payment_account_id'] = null;
        } else {
            $data['from_payment_account_id'] = null;
            $data['landlord_id'] = null;
        }

        $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
        $data['payment_method'] = $paymentAccount->type;
        $data['user_id'] = auth()->id() ?? 1;

        // Force amount to be rounded to nearest integer (Pakistani Rupee constraint)
        $data['amount'] = round((float) $data['amount']);

        GeneralReceivingVoucher::create($data);

        return redirect()->route('general-receiving-vouchers.index')
            ->with('success', 'General receiving voucher recorded successfully.');
    }

    /**
     * Display the specified general receiving voucher.
     */
    public function show(GeneralReceivingVoucher $generalReceivingVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('general_receiving_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $generalReceivingVoucher->load(['party', 'landlord', 'paymentAccount', 'fromPaymentAccount', 'user']);

        return view('general_receiving_vouchers.show', [
            'title'   => 'General Voucher Details — ' . $generalReceivingVoucher->voucher_no,
            'voucher' => $generalReceivingVoucher,
        ]);
    }

    /**
     * Print the specified general receiving voucher.
     */
    public function print(GeneralReceivingVoucher $generalReceivingVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('general_receiving_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $generalReceivingVoucher->load(['party', 'landlord', 'paymentAccount', 'fromPaymentAccount', 'user']);

        return view('general_receiving_vouchers.print', [
            'title'   => 'Print General Voucher — ' . $generalReceivingVoucher->voucher_no,
            'voucher' => $generalReceivingVoucher,
        ]);
    }

    /**
     * Remove the specified general receiving voucher from storage.
     */
    public function destroy(GeneralReceivingVoucher $generalReceivingVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('general_receiving_vouchers.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $generalReceivingVoucher->delete();

        return redirect()->route('general-receiving-vouchers.index')
            ->with('success', 'General receiving voucher deleted successfully.');
    }

    /**
     * Show the form for editing the specified general receiving voucher.
     */
    public function edit(GeneralReceivingVoucher $generalReceivingVoucher): View
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can edit vouchers.');
        }

        $parties = Party::orderBy('name')->get();
        $landlords = Landlord::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('general_receiving_vouchers.edit', [
            'title'           => 'Edit General Receiving Voucher — ' . $generalReceivingVoucher->voucher_no,
            'voucher'         => $generalReceivingVoucher,
            'parties'         => $parties,
            'landlords'       => $landlords,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Update the specified general receiving voucher in storage.
     */
    public function update(Request $request, GeneralReceivingVoucher $generalReceivingVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can edit vouchers.');
        }

        $rules = [
            'date'               => ['required', 'date'],
            'amount'             => ['required', 'numeric', 'min:1'],
            'received_from_type' => ['required', 'string', 'in:party,account,landlord'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ];

        if ($request->input('received_from_type') === 'account') {
            $rules['from_payment_account_id'] = ['required', 'exists:payment_accounts,id', 'different:payment_account_id'];
        } elseif ($request->input('received_from_type') === 'landlord') {
            $rules['landlord_id'] = ['required', 'exists:landlords,id'];
        } else {
            $rules['party_id'] = ['required', 'exists:parties,id'];
        }

        $data = $request->validate($rules);

        // ── Balance Guard for Source Account ──────────────────────────────
        if ($request->input('received_from_type') === 'account') {
            $sourceAccount = PaymentAccount::findOrFail($data['from_payment_account_id']);
            $currentBalance = $sourceAccount->current_balance;
            if ($generalReceivingVoucher->from_payment_account_id == $sourceAccount->id) {
                $currentBalance += (float) $generalReceivingVoucher->amount;
            }

            if ((float) $data['amount'] > $currentBalance + 0.01) {
                return back()->withInput()->withErrors([
                    'from_payment_account_id' => 'The selected Source Account (' . $sourceAccount->name . ') does not have sufficient balance to transfer. Available balance: Rs. ' . number_format($currentBalance, 2) . '.',
                ]);
            }

            $data['party_id'] = null;
            $data['landlord_id'] = null;
        } elseif ($request->input('received_from_type') === 'landlord') {
            $data['party_id'] = null;
            $data['from_payment_account_id'] = null;
        } else {
            $data['from_payment_account_id'] = null;
            $data['landlord_id'] = null;
        }

        $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
        $data['payment_method'] = $paymentAccount->type;
        $data['amount'] = round((float) $data['amount']);

        $generalReceivingVoucher->update($data);

        return redirect()->route('general-receiving-vouchers.index')
            ->with('success', 'General receiving voucher updated successfully.');
    }

    /**
     * AJAX: Return pending receivables summary for a landlord.
     * Used in the GRV create/edit form when 'landlord' type is selected.
     */
    public function getLandlordReceivables(Request $request): JsonResponse
    {
        $landlordId = $request->query('landlord_id');

        if (!$landlordId) {
            return response()->json(['error' => 'No landlord selected.'], 422);
        }

        $landlord = Landlord::with(['ownerships.unit'])->findOrFail($landlordId);

        // Total value owed (credit_amount = total_amount - received_amount on ownership)
        $totalOwed = (float) $landlord->ownerships->sum('credit_amount');

        // Total received via Receiving Vouchers (owner type)
        $totalReceived = (float) ReceivingVoucher::where('received_from_type', 'owner')
            ->where('owner_id', $landlordId)
            ->sum('amount');

        // Extra payments paid by landlord
        $totalExtraPaid = (float) Payment::where('landlord_id', $landlordId)
            ->where('type', 'extra_payment')
            ->where('amount_paid', '>', 0)
            ->sum('amount_paid');

        // Also include amounts received via General Receiving Vouchers (landlord type)
        $totalGrvReceived = (float) GeneralReceivingVoucher::where('landlord_id', $landlordId)
            ->sum('amount');

        // Payouts from mall to landlord (increases what they owe back)
        $totalPayouts = (float) PaymentVoucher::where('paid_to_type', 'landlord')
            ->where('landlord_id', $landlordId)
            ->sum('amount');

        $totalAllReceived = $totalReceived + $totalExtraPaid + $totalGrvReceived;
        $pendingBalance   = $totalOwed - $totalAllReceived + $totalPayouts;

        // Per-unit ownership breakdown
        $units = $landlord->ownerships->map(function ($ownership) {
            return [
                'unit_number'     => $ownership->unit?->unit_number ?? 'Unit #' . $ownership->unit_id,
                'total_amount'    => (float) $ownership->total_amount,
                'received_amount' => (float) $ownership->received_amount,
                'credit_amount'   => (float) $ownership->credit_amount,
                'is_current'      => (bool) $ownership->is_current,
            ];
        })->values();

        return response()->json([
            'landlord_name'    => $landlord->name,
            'landlord_phone'   => $landlord->phone ?? '—',
            'total_owed'       => $totalOwed,
            'total_received'   => $totalAllReceived,
            'total_payouts'    => $totalPayouts,
            'pending_balance'  => $pendingBalance,
            'units'            => $units,
        ]);
    }
}
