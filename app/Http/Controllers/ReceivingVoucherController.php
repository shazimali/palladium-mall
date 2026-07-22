<?php

namespace App\Http\Controllers;

use App\Models\ReceivingVoucher;
use App\Models\Tenant;
use App\Models\Owner;
use App\Models\PaymentAccount;
use App\Models\Payment;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceivingVoucherController extends Controller
{
    /**
     * Display a listing of the receiving vouchers.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('receiving_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $vouchers = ReceivingVoucher::query()
            ->with(['tenant.unit', 'owner', 'paymentAccount', 'user', 'payments.unit', 'payments.otherTenant'])
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where('voucher_no', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhere('other_name', 'like', "%{$term}%")
                    ->orWhereHas('tenant', function ($t) use ($term) {
                        $t->where('name', 'like', "%{$term}%")
                            ->orWhereHas('unit', fn($u) => $u->where('unit_number', 'like', "%{$term}%"));
                    })
                    ->orWhereHas('owner', fn($o) => $o->where('name', 'like', "%{$term}%"));
            })
            ->when($request->received_from_type, fn($q) => $q->where('received_from_type', $request->received_from_type))
            ->when($request->payment_account_id, fn($q) => $q->where('payment_account_id', $request->payment_account_id))
            ->when($request->unit_id, function ($q) use ($request) {
                $q->whereHas('payments', function ($qp) use ($request) {
                    $qp->where('unit_id', $request->unit_id);
                });
            })
            ->when($request->date_from, fn($q) => $q->where('date', '>=', $request->date_from))
            ->when($request->date_to, fn($q) => $q->where('date', '<=', $request->date_to))
            ->latest('date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();
        $units = Unit::orderBy('unit_number')->get(['id', 'unit_number']);

        return view('receiving_vouchers.index', [
            'title' => 'Receiving Vouchers',
            'vouchers' => $vouchers,
            'paymentAccounts' => $paymentAccounts,
            'units' => $units,
        ]);
    }

    /**
     * Show the form for creating a new receiving voucher.
     */
    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('receiving_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $units = Unit::with(['tenant', 'otherTenant'])->orderBy('unit_number')->get();
        $owners = Owner::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('receiving_vouchers.create', [
            'title' => 'New Receiving Voucher',
            'units' => $units,
            'owners' => $owners,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Store a newly created receiving voucher in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('receiving_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $rules = [
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'received_from_type' => ['required', 'string', 'in:tenant'],
            'unit_id' => ['required', 'exists:units,id'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        $data = $request->validate($rules);

        $unit = Unit::with('tenant')->findOrFail($data['unit_id']);
        $data['tenant_id'] = $unit->tenant?->id;

        $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
        $data['payment_method'] = $paymentAccount->type;
        $data['user_id'] = auth()->id() ?? 1;

        // Perform transaction
        DB::beginTransaction();
        try {
            $selectedPaymentIds = array_filter((array) $request->input('payment_ids', []));

            // Retrieve outstanding payments for this unit (filtered by selected payment_ids if provided)
            $paymentsQuery = Payment::where('unit_id', $data['unit_id'])
                ->whereIn('status', ['unpaid', 'partial']);

            if (!empty($selectedPaymentIds)) {
                $paymentsQuery->whereIn('id', $selectedPaymentIds);
            }

            $payments = $paymentsQuery->orderBy('month', 'asc')
                ->lockForUpdate()
                ->get();

            $totalBalance = $payments->sum(fn($p) => (float) $p->balanceDue());
            $amount = (float) $data['amount'];

            // Throw error if amount exceeds total balance
            if ($amount > $totalBalance + 0.01) {
                return back()->withInput()->withErrors([
                    'amount' => 'Voucher amount exceeds the total outstanding balance (Rs. ' . number_format($totalBalance, 2) . ').'
                ]);
            }

            // Create the Receiving Voucher
            $voucher = ReceivingVoucher::create($data);

            $remainingAmount = round($amount, 2);

            foreach ($payments as $payment) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $balanceDue = round((float) $payment->balanceDue(), 2);

                // Determine allocation for this payment
                if ($remainingAmount >= $balanceDue) {
                    $allocatedAmount = $balanceDue;
                } else {
                    $allocatedAmount = $remainingAmount;
                }

                $newAmountPaid = round((float) $payment->amount_paid + $allocatedAmount, 2);

                $payment->update([
                    'amount_paid' => $newAmountPaid,
                    'status' => Payment::calculateStatus((float) $payment->amount, $newAmountPaid),
                    'paid_at' => $payment->paid_at ?? $data['date'],
                    'payment_account_id' => $data['payment_account_id'],
                    'payment_method' => $data['payment_method'],
                    'reference' => $data['reference'],
                ]);

                // Attach to receiving voucher payments pivot table
                $voucher->payments()->attach($payment->id, ['amount_allocated' => $allocatedAmount]);

                $remainingAmount = round($remainingAmount - $allocatedAmount, 2);
            }

            DB::commit();

            return redirect()->route('receiving-vouchers.index')
                ->with('success', 'Receiving voucher recorded and auto-allocated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified receiving voucher.
     */
    public function show(ReceivingVoucher $receivingVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('receiving_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $receivingVoucher->load(['tenant', 'owner', 'paymentAccount', 'user', 'payments.unit', 'payments.otherTenant']);

        return view('receiving_vouchers.show', [
            'title' => 'Voucher details — ' . $receivingVoucher->voucher_no,
            'voucher' => $receivingVoucher,
        ]);
    }

    /**
     * Print the specified receiving voucher.
     */
    public function print(ReceivingVoucher $receivingVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('receiving_vouchers.print')) {
            abort(403, 'Unauthorized action.');
        }

        $receivingVoucher->load(['tenant', 'owner', 'paymentAccount', 'user', 'payments.unit', 'payments.otherTenant']);

        return view('receiving_vouchers.print', [
            'title' => 'Print Voucher — ' . $receivingVoucher->voucher_no,
            'voucher' => $receivingVoucher,
        ]);
    }

    /**
     * Remove the specified receiving voucher from storage.
     */
    public function destroy(ReceivingVoucher $receivingVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('receiving_vouchers.delete')) {
            abort(403, 'Unauthorized action.');
        }

        DB::beginTransaction();
        try {
            // Revert allocations if this is a tenant voucher
            if ($receivingVoucher->received_from_type === 'tenant') {
                foreach ($receivingVoucher->payments as $payment) {
                    $allocatedAmount = $payment->pivot->amount_allocated;
                    $revertedAmountPaid = max(0.00, (float) $payment->amount_paid - (float) $allocatedAmount);

                    if ($revertedAmountPaid <= 0) {
                        $payment->update([
                            'amount_paid' => 0.00,
                            'status' => 'unpaid',
                            'paid_at' => null,
                            'payment_account_id' => null,
                            'payment_method' => null,
                        ]);
                    } else {
                        $payment->update([
                            'amount_paid' => $revertedAmountPaid,
                            'status' => Payment::calculateStatus((float) $payment->amount, $revertedAmountPaid),
                        ]);
                    }
                }

                // Clear pivot relationships
                $receivingVoucher->payments()->detach();
            }

            $receivingVoucher->delete();
            DB::commit();

            return redirect()->route('receiving-vouchers.index')
                ->with('success', 'Receiving voucher cancelled/deleted successfully, and tenant balances rolled back.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('receiving-vouchers.index')
                ->with('error', 'Error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified receiving voucher.
     */
    public function edit(ReceivingVoucher $receivingVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('receiving_vouchers.edit')) {
            abort(403, 'Unauthorized action. You do not have permission to edit vouchers.');
        }

        $units = Unit::with(['tenant', 'otherTenant'])->orderBy('unit_number')->get();
        $owners = Owner::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)->orWhere('id', $receivingVoucher->payment_account_id)->orderBy('name')->get();

        $voucherUnitId = $receivingVoucher->payments->first()?->unit_id;

        return view('receiving_vouchers.edit', [
            'title' => 'Edit Receiving Voucher — ' . $receivingVoucher->voucher_no,
            'voucher' => $receivingVoucher,
            'units' => $units,
            'owners' => $owners,
            'paymentAccounts' => $paymentAccounts,
            'voucherUnitId' => $voucherUnitId,
        ]);
    }

    /**
     * Update the specified receiving voucher in storage.
     */
    public function update(Request $request, ReceivingVoucher $receivingVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('receiving_vouchers.edit')) {
            abort(403, 'Unauthorized action. You do not have permission to edit vouchers.');
        }

        $rules = [
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'received_from_type' => ['required', 'string', 'in:tenant'],
            'unit_id' => ['required', 'exists:units,id'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        $data = $request->validate($rules);

        $unit = Unit::with('tenant')->findOrFail($data['unit_id']);
        $data['tenant_id'] = $unit->tenant?->id;

        $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
        $data['payment_method'] = $paymentAccount->type;
        $data['user_id'] = auth()->id() ?? 1;

        DB::beginTransaction();
        try {
            // 1. Revert old allocations
            if ($receivingVoucher->received_from_type === 'tenant') {
                foreach ($receivingVoucher->payments as $payment) {
                    $allocatedAmount = $payment->pivot->amount_allocated;
                    $revertedAmountPaid = max(0.00, (float) $payment->amount_paid - (float) $allocatedAmount);

                    if ($revertedAmountPaid <= 0) {
                        $payment->update([
                            'amount_paid' => 0.00,
                            'status' => 'unpaid',
                            'paid_at' => null,
                            'payment_account_id' => null,
                            'payment_method' => null,
                            'reference' => null,
                        ]);
                    } else {
                        $payment->update([
                            'amount_paid' => $revertedAmountPaid,
                            'status' => Payment::calculateStatus((float) $payment->amount, $revertedAmountPaid),
                        ]);
                    }
                }
                $receivingVoucher->payments()->detach();
            }

            // 2. Fetch new outstanding balance (filtered by selected payment_ids if provided)
            $selectedPaymentIds = array_filter((array) $request->input('payment_ids', []));

            $paymentsQuery = Payment::where('unit_id', $data['unit_id'])
                ->whereIn('status', ['unpaid', 'partial']);

            if (!empty($selectedPaymentIds)) {
                $paymentsQuery->whereIn('id', $selectedPaymentIds);
            }

            $payments = $paymentsQuery->orderBy('month', 'asc')
                ->lockForUpdate()
                ->get();

            $totalBalance = $payments->sum(fn($p) => (float) $p->balanceDue());
            $amount = (float) $data['amount'];

            if ($amount > $totalBalance + 0.01) {
                throw new \Exception('Voucher amount exceeds the total outstanding balance (Rs. ' . number_format($totalBalance, 2) . ').');
            }

            // 3. Update Voucher
            $receivingVoucher->update($data);

            // 4. Apply new allocations
            $remainingAmount = round($amount, 2);

            foreach ($payments as $payment) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $balanceDue = round((float) $payment->balanceDue(), 2);

                if ($remainingAmount >= $balanceDue) {
                    $allocatedAmount = $balanceDue;
                } else {
                    $allocatedAmount = $remainingAmount;
                }

                $newAmountPaid = round((float) $payment->amount_paid + $allocatedAmount, 2);

                $payment->update([
                    'amount_paid' => $newAmountPaid,
                    'status' => Payment::calculateStatus((float) $payment->amount, $newAmountPaid),
                    'paid_at' => $payment->paid_at ?? $data['date'],
                    'payment_account_id' => $data['payment_account_id'],
                    'payment_method' => $data['payment_method'],
                    'reference' => $data['reference'],
                ]);

                $receivingVoucher->payments()->attach($payment->id, ['amount_allocated' => $allocatedAmount]);

                $remainingAmount = round($remainingAmount - $allocatedAmount, 2);
            }

            DB::commit();

            return redirect()->route('receiving-vouchers.index')
                ->with('success', 'Receiving voucher updated and auto-allocated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->withErrors(['amount' => $e->getMessage()]);
        }
    }

    /**
     * Fetch unpaid/partial payments for a tenant. (AJAX endpoint)
     */
    public function getTenantPendingPayments(Request $request): JsonResponse
    {
        $unitId = $request->query('unit_id');
        $excludeVoucherId = $request->query('exclude_voucher_id');
        if (!$unitId) {
            return response()->json(['payments' => []]);
        }

        $payments = Payment::with(['unit', 'receivingVouchers'])
            ->where('unit_id', $unitId)
            ->where(function ($query) use ($excludeVoucherId) {
                $query->whereIn('status', ['unpaid', 'partial']);
                if ($excludeVoucherId) {
                    $query->orWhereHas('receivingVouchers', function ($q) use ($excludeVoucherId) {
                        $q->where('receiving_vouchers.id', $excludeVoucherId);
                    });
                }
            })
            ->orderBy('month', 'asc')
            ->get();

        $formatted = $payments->map(function ($p) use ($excludeVoucherId) {
            $allocated = 0.00;
            if ($excludeVoucherId) {
                $voucherLink = $p->receivingVouchers->where('id', $excludeVoucherId)->first();
                if ($voucherLink && $voucherLink->pivot) {
                    $allocated = (float) $voucherLink->pivot->amount_allocated;
                }
            }
            return [
                'id' => $p->id,
                'month' => $p->month ? $p->month->format('M Y') : '—',
                'type' => $p->type_label,
                'unit_number' => $p->unit?->unit_number ?? '—',
                'amount_due' => (float) $p->amount,
                'amount_paid' => (float) $p->amount_paid - $allocated,
                'balance' => (float) $p->balanceDue() + $allocated,
            ];
        });

        return response()->json(['payments' => $formatted]);
    }
}
