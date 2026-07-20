<?php

namespace App\Http\Controllers;

use App\Models\Owner;
use App\Models\PaymentAccount;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WithdrawalController extends Controller
{
    /**
     * Display a listing of owner withdrawals.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('reports.view')) {
            abort(403, 'Unauthorized action.');
        }

        $withdrawals = Withdrawal::with(['owner', 'paymentAccount', 'user'])
            ->when($request->search, function ($q) use ($request) {
                $q->where('voucher_no', 'like', "%{$request->search}%")
                  ->orWhere('reference', 'like', "%{$request->search}%")
                  ->orWhereHas('owner', fn($qo) => $qo->where('name', 'like', "%{$request->search}%"));
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->withQueryString();

        return view('withdrawals.index', [
            'title'       => 'Managing Owner Withdrawals',
            'withdrawals' => $withdrawals,
        ]);
    }

    /**
     * Show the form for creating a new withdrawal.
     */
    public function create(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('payment_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $owners = Owner::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)
            ->withSum('receivingVouchers', 'amount')
            ->withSum('generalReceivingVouchers', 'amount')
            ->withSum('paymentVouchers', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('withdrawals', 'amount')
            ->orderBy('name')
            ->get();

        return view('withdrawals.create', [
            'title'           => 'Record Withdrawal',
            'owners'          => $owners,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Store a newly created withdrawal in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('payment_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'owner_id'           => ['required', 'exists:owners,id'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
            'date'               => ['required', 'date'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ]);

        $owner = Owner::findOrFail($data['owner_id']);
        $paymentAccount = PaymentAccount::withSum('receivingVouchers', 'amount')
            ->withSum('generalReceivingVouchers', 'amount')
            ->withSum('paymentVouchers', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('withdrawals', 'amount')
            ->findOrFail($data['payment_account_id']);

        // 1. Guard against owner exceeding remaining dues
        $pendingBalance = $owner->pendingBalance();
        if ((float) $data['amount'] > $pendingBalance + 0.01) {
            return back()->withInput()->withErrors([
                'amount' => 'Withdrawal amount (Rs. ' . number_format($data['amount'], 2) . ') exceeds '
                    . $owner->name . '\'s remaining dues of Rs. ' . number_format($pendingBalance, 2) . '.',
            ]);
        }

        // 2. Guard against payment account insufficient balance
        $currentBalance = $paymentAccount->current_balance;
        if ((float) $data['amount'] > $currentBalance + 0.01) {
            return back()->withInput()->withErrors([
                'payment_account_id' => 'The selected Payment Account does not have sufficient balance. Available: Rs. ' . number_format($currentBalance, 2) . '.',
            ]);
        }

        $data['user_id'] = auth()->id();
        
        Withdrawal::create($data);

        return redirect()->route('withdrawals.index')
            ->with('success', 'Withdrawal recorded successfully.');
    }

    /**
     * Show the form for editing the specified withdrawal.
     */
    public function edit(Withdrawal $withdrawal): View
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can edit withdrawals.');
        }

        $owners = Owner::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)
            ->withSum('receivingVouchers', 'amount')
            ->withSum('generalReceivingVouchers', 'amount')
            ->withSum('paymentVouchers', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('withdrawals', 'amount')
            ->orderBy('name')
            ->get();

        return view('withdrawals.edit', [
            'title'           => 'Edit Withdrawal — ' . $withdrawal->voucher_no,
            'withdrawal'      => $withdrawal,
            'owners'          => $owners,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Update the specified withdrawal in storage.
     */
    public function update(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can edit withdrawals.');
        }

        $data = $request->validate([
            'owner_id'           => ['required', 'exists:owners,id'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
            'date'               => ['required', 'date'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
        ]);

        $owner = Owner::findOrFail($data['owner_id']);
        $paymentAccount = PaymentAccount::withSum('receivingVouchers', 'amount')
            ->withSum('generalReceivingVouchers', 'amount')
            ->withSum('paymentVouchers', 'amount')
            ->withSum('expenses', 'amount')
            ->withSum('withdrawals', 'amount')
            ->findOrFail($data['payment_account_id']);

        // 1. Guard against owner exceeding remaining dues (add back current withdrawal amount to re-evaluate limit)
        $pendingBalance = $owner->pendingBalance();
        if ($withdrawal->owner_id == $owner->id) {
            $pendingBalance += (float) $withdrawal->amount;
        }
        if ((float) $data['amount'] > $pendingBalance + 0.01) {
            return back()->withInput()->withErrors([
                'amount' => 'Withdrawal amount (Rs. ' . number_format($data['amount'], 2) . ') exceeds '
                    . $owner->name . '\'s available remaining dues of Rs. ' . number_format($pendingBalance, 2) . '.',
            ]);
        }

        // 2. Guard against payment account insufficient balance (add back current withdrawal amount if same account)
        $currentBalance = $paymentAccount->current_balance;
        if ($withdrawal->payment_account_id == $paymentAccount->id) {
            $currentBalance += (float) $withdrawal->amount;
        }
        if ((float) $data['amount'] > $currentBalance + 0.01) {
            return back()->withInput()->withErrors([
                'payment_account_id' => 'The selected Payment Account does not have sufficient balance. Available: Rs. ' . number_format($currentBalance, 2) . '.',
            ]);
        }

        $withdrawal->update($data);

        return redirect()->route('withdrawals.index')
            ->with('success', 'Withdrawal updated successfully.');
    }

    /**
     * Remove the specified withdrawal from storage.
     */
    public function destroy(Withdrawal $withdrawal): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can delete withdrawals.');
        }

        $withdrawal->delete();

        return redirect()->route('withdrawals.index')
            ->with('success', 'Withdrawal deleted successfully.');
    }
}
