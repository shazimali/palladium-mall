<?php

namespace App\Http\Controllers;

use App\Models\OwnerReceivable;
use App\Models\Owner;
use App\Models\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class OwnerReceivableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = OwnerReceivable::with(['owner', 'paymentAccount', 'user']);

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

        $receivables = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $owners = Owner::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('owner_receivables.index', [
            'title'           => 'Owner Receivables Ledger',
            'receivables'     => $receivables,
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

        return view('owner_receivables.create', [
            'title'           => 'Record Owner Receivable Voucher',
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
            $data['receipt'] = $request->file('receipt')->store('owner_receivables/receipts', 'public');
        }

        $data['user_id'] = auth()->id() ?? 1;

        OwnerReceivable::create($data);

        return redirect()->route('owner-receivables.index')
            ->with('success', 'Owner Receivable Voucher recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(OwnerReceivable $ownerReceivable): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        $ownerReceivable->load(['owner', 'paymentAccount', 'user']);

        return view('owner_receivables.show', [
            'title'      => 'Owner Receivable Details — ' . $ownerReceivable->voucher_no,
            'receivable' => $ownerReceivable,
        ]);
    }

    /**
     * Show the edit form.
     */
    public function edit(OwnerReceivable $ownerReceivable): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        $owners = Owner::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('owner_receivables.edit', [
            'title'           => 'Edit Owner Receivable Voucher — ' . $ownerReceivable->voucher_no,
            'receivable'      => $ownerReceivable,
            'owners'          => $owners,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, OwnerReceivable $ownerReceivable): RedirectResponse
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
            if ($ownerReceivable->receipt) {
                Storage::disk('public')->delete($ownerReceivable->receipt);
            }
            $data['receipt'] = $request->file('receipt')->store('owner_receivables/receipts', 'public');
        }

        $ownerReceivable->update($data);

        return redirect()->route('owner-receivables.index')
            ->with('success', 'Owner Receivable Voucher updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(OwnerReceivable $ownerReceivable): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        if ($ownerReceivable->receipt) {
            Storage::disk('public')->delete($ownerReceivable->receipt);
        }

        $ownerReceivable->delete();

        return redirect()->route('owner-receivables.index')
            ->with('success', 'Owner Receivable Voucher cancelled/deleted successfully.');
    }

    /**
     * Print the specified resource.
     */
    public function print(OwnerReceivable $ownerReceivable): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('owners.view')) {
            abort(403, 'Unauthorized action.');
        }

        $ownerReceivable->load(['owner', 'paymentAccount', 'user']);

        return view('owner_receivables.print', [
            'title'      => 'Print Owner Receivable — ' . $ownerReceivable->voucher_no,
            'receivable' => $ownerReceivable,
        ]);
    }
}
