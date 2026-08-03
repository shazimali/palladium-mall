<?php

namespace App\Http\Controllers;

use App\Models\JvVoucher;
use App\Models\ExpenseHead;
use App\Models\PaymentAccount;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

class JvVoucherController extends Controller
{
    /**
     * Display a listing of JV Vouchers.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('jv_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = JvVoucher::with(['expenseHead', 'paymentAccount', 'user']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('voucher_no', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%")
                    ->orWhereHas('expenseHead', function ($h) use ($term) {
                        $h->where('name', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('expense_head_id')) {
            $query->where('expense_head_id', $request->expense_head_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        // Summary calculations based on current filters
        $totalJvAmount = (float) (clone $query)->sum('amount');
        $totalUnpaidAmount = (float) (clone $query)->where('status', 'unpaid')->sum('amount');
        $totalPaidAmount = (float) (clone $query)->where('status', 'paid')->sum('amount');

        $vouchers = $query->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $expenseHeads = ExpenseHead::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('jv_vouchers.index', [
            'title'             => 'JV Vouchers',
            'vouchers'          => $vouchers,
            'expenseHeads'      => $expenseHeads,
            'paymentAccounts'   => $paymentAccounts,
            'totalJvAmount'     => $totalJvAmount,
            'totalUnpaidAmount' => $totalUnpaidAmount,
            'totalPaidAmount'   => $totalPaidAmount,
        ]);
    }

    /**
     * Show form for creating a JV Voucher.
     */
    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('jv_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $expenseHeads = ExpenseHead::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();
        $nextVoucherNo = JvVoucher::getNextVoucherNo();

        return view('jv_vouchers.create', [
            'title'           => 'Create JV Voucher',
            'expenseHeads'    => $expenseHeads,
            'paymentAccounts' => $paymentAccounts,
            'nextVoucherNo'   => $nextVoucherNo,
        ]);
    }

    /**
     * Store a newly created JV Voucher.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('jv_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'expense_head_id'    => ['required', 'exists:expense_heads,id'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
            'date'               => ['required', 'date'],
            'status'             => ['required', 'in:unpaid,paid'],
            'payment_account_id' => ['nullable', 'required_if:status,paid', 'exists:payment_accounts,id'],
            'payment_method'     => ['nullable', 'required_if:status,paid', 'string', 'max:50'],
            'paid_date'          => ['nullable', 'required_if:status,paid', 'date'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'receipt'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if ($data['status'] === 'paid' && !empty($data['payment_account_id'])) {
            $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
            $currentBalance = $paymentAccount->current_balance;
            if ((float) $data['amount'] > $currentBalance + 0.01) {
                return back()->withInput()->withErrors([
                    'payment_account_id' => 'Selected Payment Account (' . $paymentAccount->name . ') has insufficient balance. Current balance: Rs. ' . number_format($currentBalance, 2) . '.',
                ]);
            }
        } else {
            $data['payment_account_id'] = null;
            $data['payment_method']     = null;
            $data['paid_date']          = null;
        }

        if ($request->hasFile('receipt')) {
            $data['receipt'] = $request->file('receipt')->store('jv_vouchers/receipts', 'public');
        }

        $data['user_id'] = auth()->id();

        $voucher = JvVoucher::create($data);

        ActivityLog::log('create_jv_voucher', "Created JV Voucher {$voucher->voucher_no} (" . ucfirst($voucher->status) . ")", $voucher);

        return redirect()->route('jv-vouchers.print', $voucher->id)
            ->with('success', 'JV Voucher created successfully.');
    }

    /**
     * Show specified JV Voucher.
     */
    public function show(JvVoucher $jvVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('jv_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $jvVoucher->load(['expenseHead', 'paymentAccount', 'user']);
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('jv_vouchers.show', [
            'title'           => 'JV Voucher Details — ' . $jvVoucher->voucher_no,
            'voucher'         => $jvVoucher,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Edit form for JV Voucher.
     */
    public function edit(JvVoucher $jvVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('jv_vouchers.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $expenseHeads = ExpenseHead::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('jv_vouchers.edit', [
            'title'           => 'Edit JV Voucher — ' . $jvVoucher->voucher_no,
            'voucher'         => $jvVoucher,
            'expenseHeads'    => $expenseHeads,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Update JV Voucher.
     */
    public function update(Request $request, JvVoucher $jvVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('jv_vouchers.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $data = $request->validate([
            'expense_head_id'    => ['required', 'exists:expense_heads,id'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
            'date'               => ['required', 'date'],
            'status'             => ['required', 'in:unpaid,paid'],
            'payment_account_id' => ['nullable', 'required_if:status,paid', 'exists:payment_accounts,id'],
            'payment_method'     => ['nullable', 'required_if:status,paid', 'string', 'max:50'],
            'paid_date'          => ['nullable', 'required_if:status,paid', 'date'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'receipt'            => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        if ($data['status'] === 'paid' && !empty($data['payment_account_id'])) {
            $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
            $currentBalance = $paymentAccount->current_balance;

            // If updating an already paid JV on the same account, credit back the old amount for threshold checking
            if ($jvVoucher->status === 'paid' && $jvVoucher->payment_account_id == $paymentAccount->id) {
                $currentBalance += (float) $jvVoucher->amount;
            }

            if ((float) $data['amount'] > $currentBalance + 0.01) {
                return back()->withInput()->withErrors([
                    'payment_account_id' => 'Selected Payment Account (' . $paymentAccount->name . ') has insufficient balance. Available balance: Rs. ' . number_format($currentBalance, 2) . '.',
                ]);
            }
        } else {
            $data['payment_account_id'] = null;
            $data['payment_method']     = null;
            $data['paid_date']          = null;
        }

        if ($request->hasFile('receipt')) {
            if ($jvVoucher->receipt) {
                Storage::disk('public')->delete($jvVoucher->receipt);
            }
            $data['receipt'] = $request->file('receipt')->store('jv_vouchers/receipts', 'public');
        }

        $jvVoucher->update($data);

        ActivityLog::log('update_jv_voucher', "Updated JV Voucher {$jvVoucher->voucher_no}", $jvVoucher);

        return redirect()->route('jv-vouchers.print', $jvVoucher->id)
            ->with('success', 'JV Voucher updated successfully.');
    }

    /**
     * Mark an unpaid JV Voucher as Paid.
     */
    public function pay(Request $request, JvVoucher $jvVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('jv_vouchers.pay')) {
            abort(403, 'Unauthorized action.');
        }

        if ($jvVoucher->status === 'paid') {
            return back()->with('error', 'This JV Voucher is already marked as Paid.');
        }

        $data = $request->validate([
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'payment_method'     => ['required', 'string', 'max:50'],
            'paid_date'          => ['required', 'date'],
            'reference'          => ['nullable', 'string', 'max:255'],
        ]);

        $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
        $currentBalance = $paymentAccount->current_balance;

        if ((float) $jvVoucher->amount > $currentBalance + 0.01) {
            return back()->withInput()->withErrors([
                'payment_account_id' => 'Selected Payment Account (' . $paymentAccount->name . ') has insufficient balance. Current balance: Rs. ' . number_format($currentBalance, 2) . '.',
            ]);
        }

        $jvVoucher->update([
            'status'             => 'paid',
            'payment_account_id' => $data['payment_account_id'],
            'payment_method'     => $data['payment_method'],
            'paid_date'          => $data['paid_date'],
            'reference'          => $data['reference'] ?? $jvVoucher->reference,
        ]);

        ActivityLog::log('pay_jv_voucher', "Marked JV Voucher {$jvVoucher->voucher_no} as Paid via {$paymentAccount->name}", $jvVoucher);

        return redirect()->back()->with('success', "JV Voucher {$jvVoucher->voucher_no} marked as Paid successfully.");
    }

    /**
     * Delete JV Voucher.
     */
    public function destroy(JvVoucher $jvVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('jv_vouchers.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $voucherNo = $jvVoucher->voucher_no;
        $jvVoucher->delete();

        ActivityLog::log('delete_jv_voucher', "Deleted JV Voucher {$voucherNo}", null);

        return redirect()->route('jv-vouchers.index')
            ->with('success', 'JV Voucher deleted successfully.');
    }

    /**
     * Print single JV Voucher.
     */
    public function print(JvVoucher $jvVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('jv_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $jvVoucher->load(['expenseHead', 'paymentAccount', 'user']);

        return view('jv_vouchers.print', [
            'title'   => 'Print JV Voucher — ' . $jvVoucher->voucher_no,
            'voucher' => $jvVoucher,
        ]);
    }

    /**
     * Print filtered listing of JV Vouchers.
     */
    public function printList(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('jv_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = JvVoucher::with(['expenseHead', 'paymentAccount', 'user']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('voucher_no', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhere('notes', 'like', "%{$term}%")
                    ->orWhereHas('expenseHead', function ($h) use ($term) {
                        $h->where('name', 'like', "%{$term}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('expense_head_id')) {
            $query->where('expense_head_id', $request->expense_head_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        $totalJvAmount = (float) (clone $query)->sum('amount');
        $totalUnpaidAmount = (float) (clone $query)->where('status', 'unpaid')->sum('amount');
        $totalPaidAmount = (float) (clone $query)->where('status', 'paid')->sum('amount');

        $vouchers = $query->orderBy('date', 'desc')->orderBy('id', 'desc')->get();

        return view('jv_vouchers.print_list', [
            'title'             => 'JV Vouchers Report',
            'vouchers'          => $vouchers,
            'totalJvAmount'     => $totalJvAmount,
            'totalUnpaidAmount' => $totalUnpaidAmount,
            'totalPaidAmount'   => $totalPaidAmount,
        ]);
    }
}
