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
    public function index(Request $request): View
    {
        $month = null;
        if ($request->filled('month')) {
            try {
                $month = Carbon::parse($request->month)->startOfMonth()->toDateString();
            } catch (\Exception $e) {
                // Ignore invalid date formats
            }
        }

        $payments = Payment::with(['tenant', 'unit', 'agreement', 'paymentAccount'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->type, fn($q) => $q->ofType($request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($month, fn($q) => $q->forMonth($month))
            ->latest('month')
            ->paginate(20)
            ->withQueryString();

        // Summary counts for current month
        $currentMonth = Carbon::now()->startOfMonth()->toDateString();
        $summary = [
            'total_due' => Payment::forMonth($currentMonth)->sum('amount'),
            'total_paid' => Payment::forMonth($currentMonth)->sum('amount_paid'),
            'unpaid_count' => Payment::forMonth($currentMonth)->unpaid()->count(),
            'overdue_count' => Payment::overdue()->count(),
        ];

        $paymentAccounts = \App\Models\PaymentAccount::where('is_active', true)->orderBy('name')->get();

        return view('payments.index', [
            'title' => 'Rent & Payments',
            'payments' => $payments,
            'summary' => $summary,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    public function create(Request $request): View
    {
        $tenants = Tenant::where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('payments.create', [
            'title' => 'Add Payment Record',
            'tenants' => $tenants,
        ]);
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
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
 
        return view('payments.show', [
            'title' => 'Payment — ' . $payment->tenant->name,
            'payment' => $payment,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    public function edit(Payment $payment): View
    {
        $payment->load(['tenant', 'unit', 'agreement']);

        $tenants = Tenant::where('status', 'active')->orderBy('name')->get();

        return view('payments.edit', [
            'title' => 'Edit Payment',
            'payment' => $payment,
            'tenants' => $tenants,
        ]);
    }

    public function update(UpdatePaymentRequest $request, Payment $payment): RedirectResponse
    {
        $data = $request->validated();
        $data['month'] = Carbon::parse($data['month'])->startOfMonth()->toDateString();

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

        // Calculate new status
        $data['status'] = Payment::calculateStatus(
            (float) $payment->amount,
            (float) $data['amount_paid']
        );

        $payment->update($data);

        return redirect()
            ->back()
            ->with('success', 'Payment recorded successfully.');
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

        // Get all active tenants with active agreements
        $tenants = Tenant::where('status', 'active')
            ->with(['activeAgreement', 'unit'])
            ->get()
            ->filter(fn($t) => $t->activeAgreement !== null);

        $created = 0;
        $skipped = 0;

        DB::transaction(function () use ($tenants, $month, $dueDate, $request, &$created, &$skipped) {
            foreach ($tenants as $tenant) {
                $agreement = $tenant->activeAgreement;

                foreach ($request->types as $type) {
                    // Skip if already exists
                    $exists = Payment::where('tenant_id', $tenant->id)
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
                        'tenant_id' => $tenant->id,
                        'unit_id' => $tenant->unit_id,
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
        });

        return redirect()
            ->route('payments.index')
            ->with('success', "{$created} payment records generated. {$skipped} already existed and were skipped.");
    }

    // -----------------------------------------------------------------------
    // AJAX — get agreement for selected tenant
    // -----------------------------------------------------------------------

    public function getAgreementByTenant(Request $request): JsonResponse
    {
        $tenant = Tenant::with('activeAgreement')->find($request->tenant_id);

        if (!$tenant || !$tenant->activeAgreement) {
            return response()->json(['agreement' => null]);
        }

        $agreement = $tenant->activeAgreement;

        return response()->json([
            'agreement' => [
                'id' => $agreement->id,
                'monthly_rent' => $agreement->monthly_rent,
                'maintenance_charge' => $agreement->maintenance_charge ?? 0,
                'unit_id' => $tenant->unit_id,
                'unit_number' => $tenant->unit?->unit_number ?? '—',
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
        return view('payments.print', [
            'title' => 'Print Receipt — ' . ($payment->tenant->name ?? 'N/A'),
            'payment' => $payment,
        ]);
    }
}