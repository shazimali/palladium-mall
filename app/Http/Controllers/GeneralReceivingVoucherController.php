<?php

namespace App\Http\Controllers;

use App\Models\GeneralReceivingVoucher;
use App\Models\Party;
use App\Models\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
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

        $query = GeneralReceivingVoucher::with(['party', 'paymentAccount', 'fromPaymentAccount', 'user'])
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where('voucher_no', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhereHas('party', fn($p) => $p->where('name', 'like', "%{$term}%"));
            })
            ->when($request->party_id, fn($q) => $q->where('party_id', $request->party_id))
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

        return view('general_receiving_vouchers.index', [
            'title'           => 'General Receiving Vouchers',
            'vouchers'        => $vouchers,
            'paymentAccounts' => $paymentAccounts,
            'parties'         => $parties,
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
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('general_receiving_vouchers.create', [
            'title'           => 'New General Receiving Voucher',
            'parties'         => $parties,
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
            'received_from_type' => ['required', 'string', 'in:party,account'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ];

        if ($request->input('received_from_type') === 'account') {
            $rules['from_payment_account_id'] = ['required', 'exists:payment_accounts,id', 'different:payment_account_id'];
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
        } else {
            $data['from_payment_account_id'] = null;
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

        $generalReceivingVoucher->load(['party', 'paymentAccount', 'fromPaymentAccount', 'user']);

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

        $generalReceivingVoucher->load(['party', 'paymentAccount', 'fromPaymentAccount', 'user']);

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
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('general_receiving_vouchers.edit', [
            'title'           => 'Edit General Receiving Voucher — ' . $generalReceivingVoucher->voucher_no,
            'voucher'         => $generalReceivingVoucher,
            'parties'         => $parties,
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
            'received_from_type' => ['required', 'string', 'in:party,account'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ];

        if ($request->input('received_from_type') === 'account') {
            $rules['from_payment_account_id'] = ['required', 'exists:payment_accounts,id', 'different:payment_account_id'];
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
        } else {
            $data['from_payment_account_id'] = null;
        }

        $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
        $data['payment_method'] = $paymentAccount->type;
        $data['amount'] = round((float) $data['amount']);

        $generalReceivingVoucher->update($data);

        return redirect()->route('general-receiving-vouchers.index')
            ->with('success', 'General receiving voucher updated successfully.');
    }
}
