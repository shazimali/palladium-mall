<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseHead;
use App\Models\PaymentAccount;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource (Expenses Ledger).
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expenses.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = Expense::with(['expenseHead', 'paymentAccount', 'user']);

        // Filters
        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('reference', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%")
                    ->orWhereHas('expenseHead', function ($h) use ($term) {
                        $h->where('name', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('expense_head_id')) {
            $query->where('expense_head_id', $request->expense_head_id);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Calculate totals based on filters
        $totalExpenses = (float) $query->sum('amount');

        $expenses = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $expenseHeads = ExpenseHead::orderBy('name')->get();

        return view('expenses.index', [
            'title'         => 'Expenses Ledger',
            'expenses'      => $expenses,
            'expenseHeads'  => $expenseHeads,
            'totalExpenses' => $totalExpenses,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expenses.create')) {
            abort(403, 'Unauthorized action.');
        }

        $expenseHeads = ExpenseHead::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('expenses.create', [
            'title'           => 'Record Expense',
            'expenseHeads'    => $expenseHeads,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expenses.create')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'expense_head_id'    => ['required', 'exists:expense_heads,id'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
            'date'               => ['required', 'date'],
            'payment_method'     => ['required', 'string', 'max:50'],
            'payment_account_id' => ['nullable', 'exists:payment_accounts,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'receipt'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // Max 5MB
        ]);

        if (!empty($data['payment_account_id'])) {
            $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
            $currentBalance = $paymentAccount->current_balance;
            if ((float) $data['amount'] > $currentBalance + 0.01) {
                return back()->withInput()->withErrors([
                    'payment_account_id' => 'The selected Payment Account (' . $paymentAccount->name . ') does not have sufficient balance. Current balance: Rs. ' . number_format($currentBalance, 2) . '.',
                ]);
            }
        }

        if ($request->hasFile('receipt')) {
            $data['receipt'] = $request->file('receipt')->store('expenses/receipts', 'public');
        }

        $data['user_id'] = auth()->id();

        Expense::create($data);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Expense $expense): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expenses.view')) {
            abort(403, 'Unauthorized action.');
        }

        $expense->load(['expenseHead', 'paymentAccount', 'user']);

        return view('expenses.show', [
            'title'   => 'Voucher details — ' . $expense->voucher_no,
            'expense' => $expense,
        ]);
    }

    public function edit(Expense $expense): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expenses.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $expenseHeads = ExpenseHead::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('expenses.edit', [
            'title'           => 'Edit recorded expense — ' . $expense->voucher_no,
            'expense'         => $expense,
            'expenseHeads'    => $expenseHeads,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Expense $expense): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expenses.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'expense_head_id'    => ['required', 'exists:expense_heads,id'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
            'date'               => ['required', 'date'],
            'payment_method'     => ['required', 'string', 'max:50'],
            'payment_account_id' => ['nullable', 'exists:payment_accounts,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'receipt'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // Max 5MB
        ]);

        if (!empty($data['payment_account_id'])) {
            $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
            $currentBalance = $paymentAccount->current_balance;

            // If updating the same account, add back the old expense amount to get actual available balance
            if ($expense->payment_account_id == $paymentAccount->id) {
                $currentBalance += (float) $expense->amount;
            }

            if ((float) $data['amount'] > $currentBalance + 0.01) {
                return back()->withInput()->withErrors([
                    'payment_account_id' => 'The selected Payment Account (' . $paymentAccount->name . ') does not have sufficient balance. Available balance: Rs. ' . number_format($currentBalance, 2) . '.',
                ]);
            }
        }

        if ($request->hasFile('receipt')) {
            if ($expense->receipt) {
                Storage::disk('public')->delete($expense->receipt);
            }
            $data['receipt'] = $request->file('receipt')->store('expenses/receipts', 'public');
        }

        $expense->update($data);

        return redirect()->route('expenses.index')
            ->with('success', 'Expense updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Expense $expense): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expenses.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $expense->delete();

        return redirect()->route('expenses.index')
            ->with('success', 'Expense voucher cancelled/deleted successfully.');
    }

    /**
     * Print the specified expense voucher.
     */
    public function print(Expense $expense): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('expenses.view')) {
            abort(403, 'Unauthorized action.');
        }

        $expense->load(['expenseHead', 'paymentAccount', 'user']);

        return view('expenses.print', [
            'title'   => 'Print Voucher — ' . $expense->voucher_no,
            'expense' => $expense,
        ]);
    }
}
