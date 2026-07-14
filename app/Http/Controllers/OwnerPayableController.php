<?php

namespace App\Http\Controllers;

use App\Models\OwnerPayable;
use App\Models\Owner;
use App\Models\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class OwnerPayableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = OwnerPayable::with(['owner', 'paymentAccount', 'user']);

        // Filters
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%")
                    ->orWhere('voucher_no', 'like', "%{$term}%")
                    ->orWhereHas('owner', function ($o) use ($term) {
                        $o->where('name', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->filled('payment_account_id')) {
            $query->where('payment_account_id', $request->payment_account_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Calculate totals based on filters
        $totalAmount = (float) $query->sum('amount');

        $payables = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $owners = Owner::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('owner_payables.index', [
            'title'           => 'Owner Payables Ledger',
            'payables'        => $payables,
            'owners'          => $owners,
            'paymentAccounts' => $paymentAccounts,
            'totalAmount'     => $totalAmount,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        $owners = Owner::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('owner_payables.create', [
            'title'           => 'Record Owner Payable Voucher',
            'owners'          => $owners,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'owner_id'           => ['required', 'exists:owners,id'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
            'date'               => ['required', 'date'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'receipt'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // Max 5MB
        ]);

        if ($request->hasFile('receipt')) {
            $data['receipt'] = $request->file('receipt')->store('owner_payables/receipts', 'public');
        }

        $data['user_id'] = auth()->id() ?? 1;

        // ── Owner balance guard ───────────────────────────────────────────────
        $owner          = Owner::findOrFail($data['owner_id']);
        $pendingBalance = $owner->pendingBalance();
        if ((float) $data['amount'] > $pendingBalance + 0.01) {
            return back()->withInput()->withErrors([
                'amount' => 'Payment amount (Rs. ' . number_format($data['amount'], 2) . ') exceeds '
                    . $owner->name . '\'s pending balance of Rs. ' . number_format($pendingBalance, 2) . '.',
            ]);
        }
        // ─────────────────────────────────────────────────────────────────────

        OwnerPayable::create($data);

        return redirect()->route('owner-payables.index')
            ->with('success', 'Owner Payable Voucher recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(OwnerPayable $ownerPayable): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        $ownerPayable->load(['owner', 'paymentAccount', 'user']);

        return view('owner_payables.show', [
            'title'   => 'Owner Payable Details — ' . $ownerPayable->voucher_no,
            'payable' => $ownerPayable,
        ]);
    }

    /**
     * Show the edit form.
     */
    public function edit(OwnerPayable $ownerPayable): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        $owners = Owner::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('owner_payables.edit', [
            'title'           => 'Edit Owner Payable Voucher — ' . $ownerPayable->voucher_no,
            'payable'         => $ownerPayable,
            'owners'          => $owners,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OwnerPayable $ownerPayable): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'owner_id'           => ['required', 'exists:owners,id'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
            'date'               => ['required', 'date'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'receipt'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // Max 5MB
        ]);

        if ($request->hasFile('receipt')) {
            if ($ownerPayable->receipt) {
                Storage::disk('public')->delete($ownerPayable->receipt);
            }
            $data['receipt'] = $request->file('receipt')->store('owner_payables/receipts', 'public');
        }

        // ── Owner balance guard ───────────────────────────────────────────────
        $owner          = Owner::findOrFail($data['owner_id']);
        // Temporarily add back the current payable amount before checking
        $pendingBalance = $owner->pendingBalance() + $ownerPayable->amount;
        if ((float) $data['amount'] > $pendingBalance + 0.01) {
            return back()->withInput()->withErrors([
                'amount' => 'Payment amount (Rs. ' . number_format($data['amount'], 2) . ') exceeds '
                    . $owner->name . '\'s pending balance of Rs. ' . number_format($pendingBalance, 2) . '.',
            ]);
        }
        // ─────────────────────────────────────────────────────────────────────

        $ownerPayable->update($data);

        return redirect()->route('owner-payables.index')
            ->with('success', 'Owner Payable Voucher updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OwnerPayable $ownerPayable): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        if ($ownerPayable->receipt) {
            Storage::disk('public')->delete($ownerPayable->receipt);
        }

        $ownerPayable->delete();

        return redirect()->route('owner-payables.index')
            ->with('success', 'Owner Payable Voucher cancelled/deleted successfully.');
    }

    /**
     * Print the specified resource.
     */
    public function print(OwnerPayable $ownerPayable): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        $ownerPayable->load(['owner', 'paymentAccount', 'user']);

        return view('owner_payables.print', [
            'title'   => 'Print Owner Payable — ' . $ownerPayable->voucher_no,
            'payable' => $ownerPayable,
        ]);
    }
}
