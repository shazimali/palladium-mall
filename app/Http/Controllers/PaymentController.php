<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\Agreement;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Requests\RecordPaymentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $monthVal = $request->input('month', Carbon::now()->startOfMonth()->toDateString());
        $month = null;
        if ($monthVal) {
            try {
                $month = Carbon::parse($monthVal)->startOfMonth()->toDateString();
            } catch (\Exception $e) {
                // Ignore invalid date formats
            }
        }

        // Base query with tenant constraints for other-owned units
        $baseQuery = Payment::where(function ($q) {
            $q->whereHas('unit', function ($qu) {
                $qu->where('is_self', true)->whereHas('otherTenant');
            })->orWhereHas('unit', function ($qu) {
                $qu->where('is_self', false);
            });
        });

        // Filter by owner_type
        $ownerType = $request->owner_type;
        $baseQuery->when($ownerType === 'pm_mall', fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('is_self', false)))
                  ->when($ownerType === 'other',    fn($q) => $q->whereHas('unit', fn($qu) => $qu->where('is_self', true)));

        $payments = (clone $baseQuery)->with(['tenant', 'unit', 'agreement', 'paymentAccount', 'otherTenant'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->type, fn($q) => $q->ofType($request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->unit_id, fn($q) => $q->where('unit_id', $request->unit_id))
            ->when($month, fn($q) => $q->forMonth($month))
            ->latest('month')
            ->paginate(20)
            ->withQueryString();

        // Summary counts for selected month and unit (defaults to current month)
        $targetMonth = $month ?: Carbon::now()->startOfMonth()->toDateString();
        $summaryQuery = (clone $baseQuery)->forMonth($targetMonth)
            ->when($request->unit_id, fn($q) => $q->where('unit_id', $request->unit_id));

        $summary = [
            'total_due' => (float) (clone $summaryQuery)->sum('amount'),
            'total_paid' => (float) (clone $summaryQuery)->sum('amount_paid'),
            'unpaid_count' => (clone $summaryQuery)->unpaid()->count(),
            'overdue_count' => (clone $summaryQuery)->whereIn('status', ['unpaid', 'partial'])->where('due_date', '<', now())->count(),
        ];

        $paymentAccounts = \App\Models\PaymentAccount::where('is_active', true)->orderBy('name')->get();
        $units = Unit::orderBy('unit_number')->get(['id', 'unit_number']);

        $search = $request->input('search');
        $highlight = function($text) use ($search) {
            if (empty($text)) return '';
            if (empty($search)) {
                return e($text);
            }
            $escapedSearch = preg_quote($search, '/');
            return preg_replace('/(' . $escapedSearch . ')/i', '<mark class="bg-amber-100 text-amber-900 rounded px-0.5 dark:bg-amber-950/70 dark:text-amber-300 font-medium">$1</mark>', e($text));
        };

        if ($request->ajax() || $request->has('ajax')) {
            return view('payments._table', [
                'payments' => $payments,
                'highlight' => $highlight,
                'summary' => $summary,
            ])->render();
        }

        return view('payments.index', [
            'title' => 'Rent & Payments',
            'payments' => $payments,
            'summary' => $summary,
            'paymentAccounts' => $paymentAccounts,
            'units' => $units,
        ]);
    }

    public function create(Request $request): View
    {
        $tenants = Tenant::where('status', 'active')
            ->whereDoesntHave('unit', fn($q) => $q->where('is_self', true))
            ->orderBy('name')
            ->get();

        $selfUnits = Unit::where('is_self', true)
            ->with(['floor', 'block', 'otherTenant', 'landlord'])
            ->orderBy('unit_number')
            ->get();

        return view('payments.create', [
            'title'     => 'Add Payment Record',
            'tenants'   => $tenants,
            'selfUnits' => $selfUnits,
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        // ── Self-unit maintenance payment (no tenant / agreement) ──────────
        if ($request->input('payment_mode') === 'self') {
            $request->validate([
                'unit_id'  => ['required', 'exists:units,id'],
                'month'    => ['required', 'date'],
                'amount'   => ['required', 'numeric', 'min:0'],
                'due_date' => ['required', 'date'],
                'notes'    => ['nullable', 'string', 'max:500'],
            ]);

            $unit  = Unit::with(['otherTenant', 'landlord'])->findOrFail($request->unit_id);
            $month = Carbon::parse($request->month)->startOfMonth()->toDateString();

            // Duplicate check
            $exists = Payment::where('unit_id', $unit->id)
                ->where('type', 'maintenance')
                ->where('month', $month)
                ->exists();

            if ($exists) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['month' => 'A maintenance payment for this unit and month already exists.']);
            }

            $otherTenant = $unit->otherTenant;
            $whatsappNumber = $otherTenant 
                ? $otherTenant->whatsapp_number 
                : $unit->landlord?->phone;

            Payment::create([
                'tenant_id'        => null,
                'other_tenant_id'  => $otherTenant?->id,
                'unit_id'          => $unit->id,
                'agreement_id'     => null,
                'type'             => 'maintenance',
                'month'            => $month,
                'amount'           => $request->amount,
                'amount_paid'      => 0,
                'status'           => 'unpaid',
                'due_date'         => $request->due_date,
                'notes'            => $request->notes,
                'whatsapp_number'  => $whatsappNumber,
            ]);

            return redirect()
                ->route('payments.index')
                ->with('success', "Maintenance payment for unit {$unit->unit_number} created successfully.");
        }

        // ── Normal tenant payment ─────────────────────────────────────────
        $data = $request->validated();
        $data['month'] = Carbon::parse($data['month'])->startOfMonth()->toDateString();
        $data['status'] = 'unpaid';
        $data['amount_paid'] = 0;

        Payment::create($data);

        return redirect()
            ->route('payments.index')
            ->with('success', 'Payment record created successfully.');
    }

    public function show(Payment $payment): View
    {
        $payment->load(['tenant', 'unit', 'agreement', 'paymentAccount']);
        $paymentAccounts = \App\Models\PaymentAccount::where('is_active', true)->orderBy('name')->get();

        $titleName = $payment->tenant
            ? $payment->tenant->name
            : 'Unit ' . ($payment->unit->unit_number ?? '—') . ' (External Owner)';

        return view('payments.show', [
            'title' => 'Payment — ' . $titleName,
            'payment' => $payment,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    public function edit(Payment $payment): View
    {
        $payment->load(['tenant', 'unit.landlord', 'agreement']);

        $tenants = Tenant::where('status', 'active')
            ->whereDoesntHave('unit', fn($q) => $q->where('is_self', true))
            ->orderBy('name')
            ->get();

        $selfUnits = Unit::where(function($q) use ($payment) {
            $q->where('is_self', true);
            if ($payment->unit_id) {
                $q->orWhere('id', $payment->unit_id);
            }
        })
        ->with(['floor', 'block', 'landlord'])
        ->orderBy('unit_number')
        ->get();

        return view('payments.edit', [
            'title' => 'Edit Payment',
            'payment' => $payment,
            'tenants' => $tenants,
            'selfUnits' => $selfUnits,
        ]);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $data = $request->validated();
        $data['month'] = Carbon::parse($data['month'])->startOfMonth()->toDateString();

        if (is_null($payment->tenant_id)) {
            $data['tenant_id'] = null;
            $data['agreement_id'] = null;
            $data['type'] = 'maintenance';
        }

        $payment->update($data);

        return redirect()
            ->route('payments.show', $payment)
            ->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        if ($payment->receipt) {
            Storage::disk('local')->delete($payment->receipt);
        }

        $payment->delete();

        return redirect()
            ->route('payments.index')
            ->with('success', 'Payment record removed successfully.');
    }

    // -----------------------------------------------------------------------
    // Record actual payment received
    // -----------------------------------------------------------------------

    public function recordPayment(RecordPaymentRequest $request, Payment $payment): RedirectResponse
    {
        $data = $request->validated();

        // Resolve payment_method from the selected payment account
        $paymentAccount = \App\Models\PaymentAccount::findOrFail($data['payment_account_id']);
        $data['payment_method'] = $paymentAccount->type;

        // Handle receipt upload
        if ($request->hasFile('receipt')) {
            if ($payment->receipt) {
                Storage::disk('local')->delete($payment->receipt);
            }
            $data['receipt'] = $request->file('receipt')
                ->store('payments/receipts', 'local');
        } else {
            unset($data['receipt']);
        }

        $oldAmountPaid = (float) $payment->amount_paid;
        $incrementalAmount = (float) $data['amount_paid'] - $oldAmountPaid;

        // Update the triggered payment's own paid amount & status first
        $data['status'] = Payment::calculateStatus(
            (float) $payment->amount,
            (float) $data['amount_paid']
        );

        DB::transaction(function () use ($payment, $data, $incrementalAmount, $paymentAccount) {
            $payment->update($data);

            if ($incrementalAmount <= 0 || ! $payment->tenant_id) {
                return;
            }

            // ── Single voucher for the full incremental amount ──────────────
            $voucherDate = isset($data['paid_at'])
                ? (is_string($data['paid_at']) ? $data['paid_at'] : $data['paid_at']->toDateString())
                : now()->toDateString();

            $voucher = \App\Models\ReceivingVoucher::create([
                'date'               => $voucherDate,
                'amount'             => $incrementalAmount,
                'received_from_type' => 'tenant',
                'tenant_id'          => $payment->tenant_id,
                'payment_method'     => $paymentAccount->type,
                'payment_account_id' => $paymentAccount->id,
                'reference'          => $data['reference'] ?? null,
                'notes'              => 'Auto-generated from Billings page.',
                'user_id'            => auth()->id() ?? 1,
            ]);

            // ── Cascade allocation: oldest outstanding payments first ────────
            // Re-fetch all unpaid/partial payments for this tenant (oldest first).
            // The payment that was just updated may already be paid/partial — that
            // is fine; we skip fully-paid rows automatically via balanceDue().
            $outstandingPayments = Payment::where('tenant_id', $payment->tenant_id)
                ->whereIn('status', ['unpaid', 'partial'])
                ->orderBy('month', 'asc')
                ->orderBy('id', 'asc')
                ->lockForUpdate()
                ->get();

            $remaining = $incrementalAmount;

            foreach ($outstandingPayments as $p) {
                if ($remaining <= 0) {
                    break;
                }

                $balanceDue = $p->balanceDue();
                if ($balanceDue <= 0) {
                    continue; // already paid off (e.g. by the update above)
                }

                $allocated   = min($remaining, $balanceDue);
                $newAmtPaid  = (float) $p->amount_paid + $allocated;

                // Avoid double-updating the payment that was just saved above
                if ($p->id !== $payment->id) {
                    $p->update([
                        'amount_paid'        => $newAmtPaid,
                        'status'             => Payment::calculateStatus((float) $p->amount, $newAmtPaid),
                        'paid_at'            => $p->paid_at ?? $voucherDate,
                        'payment_account_id' => $paymentAccount->id,
                        'payment_method'     => $paymentAccount->type,
                        'reference'          => $data['reference'] ?? $p->reference,
                    ]);
                }

                $voucher->payments()->attach($p->id, ['amount_allocated' => $allocated]);
                $remaining -= $allocated;
            }

            // If the triggered payment itself was not yet linked (e.g. it is now
            // fully paid and no longer in the outstanding list), attach it.
            if (! $voucher->payments->contains($payment->id)) {
                $directAllocation = min($incrementalAmount, (float) $payment->amount);
                $voucher->payments()->syncWithoutDetaching([
                    $payment->id => ['amount_allocated' => $directAllocation],
                ]);
            }
        });

        return redirect()
            ->back()
            ->with('success', 'Payment recorded. One voucher generated and older outstanding payments settled first.');
    }

    // -----------------------------------------------------------------------
    // Bulk generate payments for a month
    // -----------------------------------------------------------------------

    public function bulkGenerate(Request $request): RedirectResponse
    {
        $request->validate([
            'month' => ['required', 'date'],
            'due_date' => ['required', 'date'],
            'types' => ['required', 'array'],
            'types.*' => ['in:rent,maintenance'],
        ]);

        $month = Carbon::parse($request->month)->startOfMonth()->toDateString();
        $dueDate = $request->due_date;

        $billingMonth = Carbon::parse($month)->startOfMonth();

        // Get all active or expired agreements that overlap with the selected month
        $agreements = Agreement::where('start_date', '<=', $billingMonth->copy()->endOfMonth())
            ->where('end_date', '>=', $billingMonth->copy()->startOfMonth())
            ->whereIn('status', ['active', 'expired'])
            ->with(['tenant', 'unit'])
            ->get();

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($agreements, $month, $dueDate, $request, &$created, &$skipped) {
            foreach ($agreements as $agreement) {
                // Ensure a tenant is attached to the agreement
                if (!$agreement->tenant_id) {
                    continue;
                }

                foreach ($request->types as $type) {
                    // Skip rent for is_self units — they are external owners; only maintenance applies
                    if ($type === 'rent' && $agreement->unit?->is_self) {
                        $skipped++;
                        continue;
                    }

                    // Skip if already exists for this tenant, agreement, type, and month
                    $exists = Payment::where('tenant_id', $agreement->tenant_id)
                        ->where('agreement_id', $agreement->id)
                        ->where('type', $type)
                        ->where('month', $month)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    $amount = match ($type) {
                        'rent' => $agreement->monthly_rent,
                        'maintenance' => $agreement->maintenance_charge ?? 0,
                        default => 0,
                    };

                    Payment::create([
                        'tenant_id' => $agreement->tenant_id,
                        'unit_id' => $agreement->unit_id,
                        'agreement_id' => $agreement->id,
                        'type' => $type,
                        'month' => $month,
                        'amount' => $amount,
                        'amount_paid' => 0,
                        'status' => 'unpaid',
                        'due_date' => $dueDate,
                    ]);

                    $created++;
                }
            }

            // ── External owner (is_self) units: maintenance-only ──────────
            if (in_array('maintenance', $request->types)) {
                $selfUnits = Unit::where('is_self', true)
                    ->with(['otherTenant', 'landlord'])
                    ->get();

                foreach ($selfUnits as $selfUnit) {
                    $charge = $selfUnit->default_maintenance_charge;
                    if (!$charge || $charge <= 0) {
                        continue;
                    }

                    // Duplicate check: unit + type + month
                    $exists = Payment::where('unit_id', $selfUnit->id)
                        ->where('type', 'maintenance')
                        ->where('month', $month)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    $otherTenant = $selfUnit->otherTenant;
                    $whatsappNumber = $otherTenant 
                        ? $otherTenant->whatsapp_number 
                        : $selfUnit->landlord?->phone;

                    Payment::create([
                        'tenant_id'        => null,
                        'other_tenant_id'  => $otherTenant?->id,
                        'unit_id'          => $selfUnit->id,
                        'agreement_id'     => null,
                        'type'             => 'maintenance',
                        'month'            => $month,
                        'amount'           => $charge,
                        'amount_paid'      => 0,
                        'status'           => 'unpaid',
                        'due_date'         => $dueDate,
                        'whatsapp_number'  => $whatsappNumber,
                    ]);

                    $created++;
                }
            }
        });

        return redirect()
            ->route('payments.index')
            ->with('success', "{$created} payment records generated. {$skipped} already existed or skipped.");
    }

    // -----------------------------------------------------------------------
    // Bulk edit payments for a month
    // -----------------------------------------------------------------------

    public function bulkEdit(Request $request): RedirectResponse
    {
        $request->validate([
            'source_month'    => ['required', 'date'],
            'type'            => ['required', 'in:all,rent,maintenance'],
            'target_month'    => ['nullable', 'date'],
            'target_due_date' => ['nullable', 'date'],
        ]);

        $sourceMonth = Carbon::parse($request->source_month)->startOfMonth()->toDateString();
        $targetMonth = $request->filled('target_month') 
            ? Carbon::parse($request->target_month)->startOfMonth()->toDateString() 
            : null;
        $targetDueDate = $request->filled('target_due_date') 
            ? Carbon::parse($request->target_due_date)->toDateString() 
            : null;

        if (!$targetMonth && !$targetDueDate) {
            return redirect()
                ->back()
                ->with('error', 'You must specify at least a new Month/Year or a new Due Date to update.');
        }

        $type = $request->type;

        $updatedCount = DB::transaction(function () use ($sourceMonth, $type, $targetMonth, $targetDueDate) {
            $query = Payment::where('month', $sourceMonth)
                ->where('status', 'unpaid');

            if ($type !== 'all') {
                $query->where('type', $type);
            }

            $payments = $query->get();
            $count = 0;

            foreach ($payments as $payment) {
                $updates = [];
                if ($targetMonth) {
                    $updates['month'] = $targetMonth;
                }
                if ($targetDueDate) {
                    $updates['due_date'] = $targetDueDate;
                }

                $payment->update($updates);
                $count++;
            }

            return $count;
        });

        return redirect()
            ->route('payments.index')
            ->with('success', "{$updatedCount} unpaid payment records updated successfully.");
    }

    // -----------------------------------------------------------------------
    // AJAX — get agreement for selected tenant
    // -----------------------------------------------------------------------

    public function getAgreementByTenant(Request $request): JsonResponse
    {
        $agreement = Agreement::with('unit.landlord')
            ->where('tenant_id', $request->tenant_id)
            ->where('status', 'active')
            ->latest('updated_at')
            ->first();

        if (!$agreement) {
            return response()->json(['agreement' => null]);
        }

        return response()->json([
            'agreement' => [
                'id'                 => $agreement->id,
                'monthly_rent'       => $agreement->monthly_rent,
                'maintenance_charge' => $agreement->maintenance_charge ?? 0,
                'unit_id'            => $agreement->unit_id,
                'unit_number'        => $agreement->unit?->unit_number ?? '—',
                'landlord_name'      => $agreement->unit?->landlord?->name ?? '—',
            ],
        ]);
    }

    public function createUtilityReading(): View
    {
        $units = Unit::where('status', 'rented')
            ->orderBy('unit_number')
            ->get();

        return view('payments.create_utility', [
            'title' => 'Record Utility Reading',
            'units' => $units,
        ]);
    }

    public function storeUtilityReading(Request $request): RedirectResponse
    {
        if ($request->has('previous_reading')) {
            $request->merge(['previous_reading' => (float) $request->input('previous_reading')]);
        }
        if ($request->has('current_reading')) {
            $request->merge(['current_reading' => (float) $request->input('current_reading')]);
        }
        if ($request->has('rate_per_unit')) {
            $request->merge(['rate_per_unit' => (float) $request->input('rate_per_unit')]);
        }

        $data = $request->validate([
            'unit_id' => ['required', 'exists:units,id'],
            'type' => ['required', 'in:electricity,water,gas'],
            'month' => ['required', 'date'],
            'previous_reading' => ['required', 'numeric', 'min:0'],
            'current_reading' => ['required', 'numeric', 'gte:previous_reading'],
            'rate_per_unit' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $unit = Unit::findOrFail($data['unit_id']);
        $tenant = $unit->tenant;

        if (!$tenant) {
            return redirect()->back()
                ->withErrors(['unit_id' => 'Selected unit does not have an active tenant.'])
                ->withInput();
        }

        $month = Carbon::parse($data['month'])->startOfMonth()->toDateString();

        $exists = Payment::where('tenant_id', $tenant->id)
            ->where('type', $data['type'])
            ->where('month', $month)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['month' => 'A reading for this unit, type, and month already exists.'])
                ->withInput();
        }

        $unitsConsumed = (float) ($data['current_reading'] - $data['previous_reading']);
        $amount = $unitsConsumed * (float) $data['rate_per_unit'];

        $meterId = $unit->meters()->where('type', $data['type'])->value('id');

        Payment::create([
            'tenant_id' => $tenant->id,
            'unit_id' => $unit->id,
            'agreement_id' => $tenant->activeAgreement?->id ?? 1,
            'type' => $data['type'],
            'month' => $month,
            'amount' => $amount,
            'amount_paid' => 0.00,
            'status' => 'unpaid',
            'due_date' => $data['due_date'],
            'meter_id' => $meterId,
            'previous_reading' => $data['previous_reading'],
            'current_reading' => $data['current_reading'],
            'units_consumed' => $unitsConsumed,
            'rate_per_unit' => $data['rate_per_unit'],
            'notes' => $data['notes'],
        ]);

        return redirect()->route('payments.index')
            ->with('success', 'Utility reading recorded and bill payment generated successfully.');
    }

    public function getPreviousReading(Request $request): JsonResponse
    {
        $unitId = $request->query('unit_id');
        $type = $request->query('type');

        $lastReading = Payment::where('unit_id', $unitId)
            ->where('type', $type)
            ->latest('month')
            ->value('current_reading') ?? 0;

        return response()->json([
            'previous_reading' => (float) $lastReading
        ]);
    }

    public function getTenantByUnit(Request $request): JsonResponse
    {
        $unit = Unit::with('tenant')->find($request->unit_id);
        return response()->json([
            'tenant' => $unit?->tenant ? [
                'id' => $unit->tenant->id,
                'name' => $unit->tenant->name,
            ] : null
        ]);
    }

    public function print(Payment $payment): View
    {
        $payment->load(['tenant', 'unit', 'agreement']);

        if (in_array($payment->type, ['maintenance', 'electricity', 'water', 'gas'])) {
            $groupedPayments = Payment::with(['tenant', 'unit', 'agreement', 'meter'])
                ->where('tenant_id', $payment->tenant_id)
                ->where('month', $payment->month->toDateString())
                ->whereIn('type', ['maintenance', 'electricity', 'water', 'gas'])
                ->get();

            return view('payments.print_maintenance', [
                'title' => 'Print Maintenance Bill — ' . ($payment->tenant->name ?? 'N/A'),
                'payment' => $payment,
                'groupedPayments' => $groupedPayments,
            ]);
        }

        return view('payments.print', [
            'title' => 'Print Receipt — ' . ($payment->tenant->name ?? 'N/A'),
            'payment' => $payment,
        ]);
    }

    public function publicPrint(string $hash): View
    {
        $payment = Payment::where('hash', $hash)->firstOrFail();
        
        $payment->load(['tenant', 'unit', 'agreement']);

        if (in_array($payment->type, ['maintenance', 'electricity', 'water', 'gas'])) {
            $groupedPayments = Payment::with(['tenant', 'unit', 'agreement', 'meter'])
                ->where('tenant_id', $payment->tenant_id)
                ->where('month', $payment->month->toDateString())
                ->whereIn('type', ['maintenance', 'electricity', 'water', 'gas'])
                ->get();

            return view('payments.print_maintenance', [
                'title' => 'Print Maintenance Bill — ' . ($payment->tenant->name ?? 'N/A'),
                'payment' => $payment,
                'groupedPayments' => $groupedPayments,
            ]);
        }

        return view('payments.print', [
            'title' => 'Print Receipt — ' . ($payment->tenant->name ?? 'N/A'),
            'payment' => $payment,
        ]);
    }

    public function toggleStatus(Payment $payment): RedirectResponse
    {
        if ($payment->isPaid()) {
            // ── Revert to unpaid ──────────────────────────────────────────
            DB::transaction(function () use ($payment) {
                // Collect all vouchers linked to this payment
                $voucherIds = DB::table('receiving_voucher_payments')
                    ->where('payment_id', $payment->id)
                    ->pluck('receiving_voucher_id');

                // For each voucher, revert ALL payments it allocated to
                foreach ($voucherIds as $voucherId) {
                    $voucher = \App\Models\ReceivingVoucher::with('payments')->find($voucherId);
                    if (! $voucher) {
                        continue;
                    }

                    foreach ($voucher->payments as $vp) {
                        $allocated      = (float) $vp->pivot->amount_allocated;
                        $revertedPaid   = max(0.00, (float) $vp->amount_paid - $allocated);

                        $vp->update([
                            'amount_paid'        => $revertedPaid,
                            'status'             => $revertedPaid <= 0
                                                        ? 'unpaid'
                                                        : Payment::calculateStatus((float) $vp->amount, $revertedPaid),
                            'paid_at'            => $revertedPaid <= 0 ? null : $vp->paid_at,
                            'payment_account_id' => $revertedPaid <= 0 ? null : $vp->payment_account_id,
                            'payment_method'     => $revertedPaid <= 0 ? null : $vp->payment_method,
                        ]);
                    }

                    $voucher->payments()->detach();
                    $voucher->delete();
                }
            });

            $msg = 'Payment and all associated voucher allocations reverted to unpaid.';
        } else {
            // ── Mark as paid, one voucher, cascade oldest-first ───────────
            DB::transaction(function () use ($payment) {
                $account           = \App\Models\PaymentAccount::where('is_active', true)->first();
                $incrementalAmount = (float) $payment->amount - (float) $payment->amount_paid;

                if ($incrementalAmount <= 0) {
                    return;
                }

                // Create a single voucher for the full balance due
                $voucher = \App\Models\ReceivingVoucher::create([
                    'date'               => now()->toDateString(),
                    'amount'             => $incrementalAmount,
                    'received_from_type' => 'tenant',
                    'tenant_id'          => $payment->tenant_id,
                    'payment_method'     => $account?->type,
                    'payment_account_id' => $account?->id,
                    'notes'              => 'Auto-generated on status toggle.',
                    'user_id'            => auth()->id() ?? 1,
                ]);

                // ── Cascade: settle oldest unpaid/partial payments first ──
                if ($payment->tenant_id) {
                    $outstandingPayments = Payment::where('tenant_id', $payment->tenant_id)
                        ->whereIn('status', ['unpaid', 'partial'])
                        ->orderBy('month', 'asc')
                        ->orderBy('id', 'asc')
                        ->lockForUpdate()
                        ->get();

                    $remaining = $incrementalAmount;

                    foreach ($outstandingPayments as $p) {
                        if ($remaining <= 0) {
                            break;
                        }

                        $balanceDue = $p->balanceDue();
                        if ($balanceDue <= 0) {
                            continue;
                        }

                        $allocated  = min($remaining, $balanceDue);
                        $newAmtPaid = (float) $p->amount_paid + $allocated;

                        $p->update([
                            'amount_paid'        => $newAmtPaid,
                            'status'             => Payment::calculateStatus((float) $p->amount, $newAmtPaid),
                            'paid_at'            => $p->paid_at ?? now(),
                            'payment_account_id' => $account?->id,
                            'payment_method'     => $account?->type,
                        ]);

                        $voucher->payments()->attach($p->id, ['amount_allocated' => $allocated]);
                        $remaining -= $allocated;
                    }
                } else {
                    // Non-tenant payment (e.g. external owner) — settle directly
                    $payment->update([
                        'status'             => 'paid',
                        'amount_paid'        => $payment->amount,
                        'paid_at'            => now(),
                        'payment_account_id' => $account?->id,
                        'payment_method'     => $account?->type,
                    ]);
                    $voucher->payments()->attach($payment->id, ['amount_allocated' => $incrementalAmount]);
                }
            });

            $msg = 'Payment marked as paid. One voucher generated and older outstanding payments settled first.';
        }

        return redirect()->back()->with('success', $msg);
    }
}