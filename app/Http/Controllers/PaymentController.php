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
        $payments = Payment::with(['tenant', 'unit', 'agreement'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->type, fn($q) => $q->ofType($request->type))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->month, fn($q) => $q->forMonth($request->month))
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

        return view('payments.index', [
            'title' => 'Rent & Payments',
            'payments' => $payments,
            'summary' => $summary,
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
        $payment->load(['tenant', 'unit', 'agreement']);

        return view('payments.show', [
            'title' => 'Payment — ' . $payment->tenant->name,
            'payment' => $payment,
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
                'unit_number' => $tenant->unit->unit_number,
            ],
        ]);
    }
}