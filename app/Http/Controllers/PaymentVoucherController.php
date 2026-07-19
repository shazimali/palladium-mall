<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
use App\Models\Owner;
use App\Models\PaymentAccount;
use App\Models\Tenant;
use App\Models\Payment;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentVoucherController extends Controller
{
    /**
     * Display a listing of the payment vouchers.
     */
    public function index(Request $request): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('payment_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $query = PaymentVoucher::with(['owner', 'party', 'paymentAccount', 'user'])
            ->when($request->search, function ($q) use ($request) {
                $term = $request->search;
                $q->where('voucher_no', 'like', "%{$term}%")
                    ->orWhere('reference', 'like', "%{$term}%")
                    ->orWhere('other_name', 'like', "%{$term}%")
                    ->orWhereHas('owner', fn($o) => $o->where('name', 'like', "%{$term}%"))
                    ->orWhereHas('party', fn($p) => $p->where('name', 'like', "%{$term}%"));
            })
            ->when($request->paid_to_type, fn($q) => $q->where('paid_to_type', $request->paid_to_type))
            ->when($request->payment_account_id, fn($q) => $q->where('payment_account_id', $request->payment_account_id))
            ->when($request->start_date, fn($q) => $q->whereDate('date', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('date', '<=', $request->end_date))
            ->when($request->is_advance !== null && $request->is_advance !== '', fn($q) => $q->where('is_advance', (bool) $request->is_advance));

        // Calculate totals based on filters
        $totalAmount = (float) $query->sum('amount');

        $vouchers = $query->latest('date')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $paymentAccounts = PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('payment_vouchers.index', [
            'title'           => 'Payment Vouchers',
            'vouchers'        => $vouchers,
            'paymentAccounts' => $paymentAccounts,
            'totalAmount'     => $totalAmount,
        ]);
    }

    /**
     * Show the form for creating a new payment voucher.
     */
    public function create(): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('payment_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $owners = Owner::orderBy('name')->get();
        $parties = \App\Models\Party::orderBy('name')->get();
        $tenants = Tenant::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)
            ->withSum('receivingVouchers', 'amount')
            ->withSum('generalReceivingVouchers', 'amount')
            ->withSum('paymentVouchers', 'amount')
            ->withSum('expenses', 'amount')
            ->orderBy('name')
            ->get();

        return view('payment_vouchers.create', [
            'title'           => 'New Payment Voucher',
            'owners'          => $owners,
            'parties'         => $parties,
            'tenants'         => $tenants,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Store a newly created payment voucher in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('payment_vouchers.create')) {
            abort(403, 'Unauthorized action.');
        }

        $rules = [
            'date'               => ['required', 'date'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
            'paid_to_type'       => ['required', 'string', 'in:owner,tenant,other'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'is_advance'         => ['nullable', 'boolean'],
        ];

        if ($request->input('paid_to_type') === 'owner') {
            $rules['owner_id'] = ['required', 'exists:owners,id'];
        } elseif ($request->input('paid_to_type') === 'tenant') {
            $rules['tenant_id'] = ['required', 'exists:tenants,id'];
            $rules['unit_id']   = ['required', 'exists:units,id'];
        } else {
            $rules['party_id'] = ['required', 'exists:parties,id'];
        }

        $data = $request->validate($rules);

        $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
        $data['payment_method'] = $paymentAccount->type;
        $data['user_id'] = auth()->id() ?? 1;
        $data['is_advance'] = $request->has('is_advance');

        if ($request->input('paid_to_type') === 'owner') {
            $data['party_id'] = null;
            $data['tenant_id'] = null;
            $data['unit_id'] = null;
            $data['other_name'] = null;
        } elseif ($request->input('paid_to_type') === 'tenant') {
            $data['owner_id'] = null;
            $data['party_id'] = null;
            $tenant = Tenant::findOrFail($data['tenant_id']);
            $data['other_name'] = $tenant->name;
        } else {
            $data['owner_id'] = null;
            $data['tenant_id'] = null;
            $data['unit_id'] = null;
            $party = \App\Models\Party::findOrFail($data['party_id']);
            $data['other_name'] = $party->name;
        }

        // ── Owner balance guard ───────────────────────────────────────────────
        if ($request->input('paid_to_type') === 'owner' && isset($data['owner_id'])) {
            $owner          = Owner::findOrFail($data['owner_id']);
            $pendingBalance = $owner->pendingBalance();
            if ((float) $data['amount'] > $pendingBalance + 0.01) {
                return back()->withInput()->withErrors([
                    'amount' => 'Payment amount (Rs. ' . number_format($data['amount'], 2) . ') exceeds '
                        . $owner->name . '\'s pending income balance of Rs. ' . number_format($pendingBalance, 2) . '.',
                ]);
            }
        }

        // ── Tenant security deposit limit guard ──────────────────────────────
        if ($request->input('paid_to_type') === 'tenant' && isset($data['tenant_id']) && isset($data['unit_id'])) {
            $tenant = Tenant::findOrFail($data['tenant_id']);
            $unit = Unit::findOrFail($data['unit_id']);
            
            // Total collected security deposit for this tenant/unit
            $totalCollected = (float) Payment::where('tenant_id', $tenant->id)
                ->where('unit_id', $unit->id)
                ->where('type', 'security_deposit')
                ->sum('amount_paid');
            
            // Already refunded via vouchers
            $totalRefunded = (float) PaymentVoucher::where('tenant_id', $tenant->id)
                ->where('unit_id', $unit->id)
                ->sum('amount');
            
            $pendingRefund = round($totalCollected - $totalRefunded, 2);
            
            if ((float) $data['amount'] > $pendingRefund + 0.01) {
                return back()->withInput()->withErrors([
                    'amount' => 'Payment amount (Rs. ' . number_format($data['amount'], 2) . ') exceeds security deposit refund limit of Rs. ' . number_format($pendingRefund, 2) . ' for unit ' . $unit->unit_number . '.',
                ]);
            }
        }
        // ─────────────────────────────────────────────────────────────────────

        PaymentVoucher::create($data);

        return redirect()->route('payment-vouchers.index')
            ->with('success', 'Payment voucher recorded successfully.');
    }

    /**
     * Display the specified payment voucher.
     */
    public function show(PaymentVoucher $paymentVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('payment_vouchers.view')) {
            abort(403, 'Unauthorized action.');
        }

        $paymentVoucher->load(['owner', 'party', 'paymentAccount', 'user']);

        return view('payment_vouchers.show', [
            'title'   => 'Voucher details — ' . $paymentVoucher->voucher_no,
            'voucher' => $paymentVoucher,
        ]);
    }

    /**
     * Print the specified payment voucher.
     */
    public function print(PaymentVoucher $paymentVoucher): View
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('payment_vouchers.print')) {
            abort(403, 'Unauthorized action.');
        }

        $paymentVoucher->load(['owner', 'party', 'paymentAccount', 'user']);

        return view('payment_vouchers.print', [
            'title'   => 'Print Voucher — ' . $paymentVoucher->voucher_no,
            'voucher' => $paymentVoucher,
        ]);
    }

    /**
     * Remove the specified payment voucher from storage.
     */
    public function destroy(PaymentVoucher $paymentVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin() && !auth()->user()->hasPermission('payment_vouchers.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $paymentVoucher->delete();

        return redirect()->route('payment-vouchers.index')
            ->with('success', 'Payment voucher cancelled/deleted successfully.');
    }

    /**
     * Show the form for editing the specified payment voucher.
     */
    public function edit(PaymentVoucher $paymentVoucher): View
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can edit vouchers.');
        }

        $owners = Owner::orderBy('name')->get();
        $parties = \App\Models\Party::orderBy('name')->get();
        $tenants = Tenant::orderBy('name')->get();
        $paymentAccounts = PaymentAccount::where('is_active', true)
            ->withSum('receivingVouchers', 'amount')
            ->withSum('generalReceivingVouchers', 'amount')
            ->withSum('paymentVouchers', 'amount')
            ->withSum('expenses', 'amount')
            ->orderBy('name')
            ->get();

        return view('payment_vouchers.edit', [
            'title'           => 'Edit Payment Voucher — ' . $paymentVoucher->voucher_no,
            'voucher'         => $paymentVoucher,
            'owners'          => $owners,
            'parties'         => $parties,
            'tenants'         => $tenants,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Update the specified payment voucher in storage.
     */
    public function update(Request $request, PaymentVoucher $paymentVoucher): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action. Only Super Admin can edit vouchers.');
        }

        $rules = [
            'date'               => ['required', 'date'],
            'amount'             => ['required', 'numeric', 'min:0.01'],
            'paid_to_type'       => ['required', 'string', 'in:owner,tenant,other'],
            'payment_account_id' => ['required', 'exists:payment_accounts,id'],
            'reference'          => ['nullable', 'string', 'max:255'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'is_advance'         => ['nullable', 'boolean'],
        ];

        if ($request->input('paid_to_type') === 'owner') {
            $rules['owner_id'] = ['required', 'exists:owners,id'];
        } elseif ($request->input('paid_to_type') === 'tenant') {
            $rules['tenant_id'] = ['required', 'exists:tenants,id'];
            $rules['unit_id']   = ['required', 'exists:units,id'];
        } else {
            $rules['party_id'] = ['required', 'exists:parties,id'];
        }

        $data = $request->validate($rules);

        $paymentAccount = PaymentAccount::findOrFail($data['payment_account_id']);
        $data['payment_method'] = $paymentAccount->type;
        $data['is_advance'] = $request->has('is_advance');

        if ($request->input('paid_to_type') === 'owner') {
            $data['party_id'] = null;
            $data['tenant_id'] = null;
            $data['unit_id'] = null;
            $data['other_name'] = null;
        } elseif ($request->input('paid_to_type') === 'tenant') {
            $data['owner_id'] = null;
            $data['party_id'] = null;
            $tenant = Tenant::findOrFail($data['tenant_id']);
            $data['other_name'] = $tenant->name;
        } else {
            $data['owner_id'] = null;
            $data['tenant_id'] = null;
            $data['unit_id'] = null;
            $party = \App\Models\Party::findOrFail($data['party_id']);
            $data['other_name'] = $party->name;
        }

        // ── Owner balance guard (exclude current voucher amount from paid total) ──
        if ($request->input('paid_to_type') === 'owner' && isset($data['owner_id'])) {
            $owner            = \App\Models\Owner::findOrFail($data['owner_id']);
            $currentVoucherAmount = (float) $paymentVoucher->amount;
            $pendingBalance   = round($owner->pendingBalance() + $currentVoucherAmount, 2); // add back current to re-evaluate
            if ((float) $data['amount'] > $pendingBalance + 0.01) {
                return back()->withInput()->withErrors([
                    'amount' => 'Payment amount (Rs. ' . number_format($data['amount'], 2) . ') exceeds '
                        . $owner->name . '\'s available income balance of Rs. ' . number_format($pendingBalance, 2) . '.'
                ]);
            }
        }

        // ── Tenant security deposit limit guard ──────────────────────────────
        if ($request->input('paid_to_type') === 'tenant' && isset($data['tenant_id']) && isset($data['unit_id'])) {
            $tenant = Tenant::findOrFail($data['tenant_id']);
            $unit = Unit::findOrFail($data['unit_id']);
            
            // Total collected security deposit for this tenant/unit
            $totalCollected = (float) Payment::where('tenant_id', $tenant->id)
                ->where('unit_id', $unit->id)
                ->where('type', 'security_deposit')
                ->sum('amount_paid');
            
            // Already refunded via vouchers (exclude this one for update)
            $totalRefunded = (float) PaymentVoucher::where('tenant_id', $tenant->id)
                ->where('unit_id', $unit->id)
                ->where('id', '!=', $paymentVoucher->id)
                ->sum('amount');
            
            $pendingRefund = round($totalCollected - $totalRefunded, 2);
            
            if ((float) $data['amount'] > $pendingRefund + 0.01) {
                return back()->withInput()->withErrors([
                    'amount' => 'Payment amount (Rs. ' . number_format($data['amount'], 2) . ') exceeds security deposit refund limit of Rs. ' . number_format($pendingRefund, 2) . ' for unit ' . $unit->unit_number . '.',
                ]);
            }
        }
        // ────────────────────────────────────────────────────────────────

        $paymentVoucher->update($data);

        return redirect()->route('payment-vouchers.index')
            ->with('success', 'Payment voucher updated successfully.');
    }

    public function getTenantSecurityDeposits(Request $request)
    {
        $tenantId = $request->query('tenant_id');
        if (!$tenantId) {
            return response()->json([]);
        }

        $tenant = Tenant::findOrFail($tenantId);

        // Fetch all units that this tenant has paid security deposits for
        $securityDepositPayments = Payment::where('tenant_id', $tenantId)
            ->where('type', 'security_deposit')
            ->selectRaw('unit_id, SUM(amount_paid) as total_collected')
            ->groupBy('unit_id')
            ->with('unit')
            ->get();

        $vouchersQuery = PaymentVoucher::where('tenant_id', $tenantId)
            ->selectRaw('unit_id, SUM(amount) as total_refunded')
            ->groupBy('unit_id');

        // Exclude current voucher being edited if id is provided
        if ($request->filled('voucher_id')) {
            $vouchersQuery->where('id', '!=', $request->voucher_id);
        }

        $refunds = $vouchersQuery->get()->pluck('total_refunded', 'unit_id');

        $data = [];
        foreach ($securityDepositPayments as $sd) {
            if (!$sd->unit) continue;
            
            $refunded = $refunds->get($sd->unit_id, 0);
            $pending = max(0, $sd->total_collected - $refunded);
            
            $data[] = [
                'unit_id' => $sd->unit_id,
                'unit_number' => $sd->unit->unit_number,
                'total_collected' => (float)$sd->total_collected,
                'total_refunded' => (float)$refunded,
                'pending_refund' => (float)$pending,
            ];
        }

        return response()->json([
            'tenant_name' => $tenant->name,
            'security_deposits' => $data
        ]);
    }
}
