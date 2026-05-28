<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Tenant;
use App\Http\Requests\StoreInvoiceRequest;
use App\Services\InvoiceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Carbon\Carbon;

class InvoiceController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService)
    {
    }

    // -----------------------------------------------------------------------
    // Index
    // -----------------------------------------------------------------------

    public function index(Request $request): View
    {
        $invoices = Invoice::with(['tenant', 'unit'])
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->month, fn($q) => $q->where('month', Carbon::parse($request->month)->startOfMonth()))
            ->latest('month')
            ->paginate(20)
            ->withQueryString();

        return view('invoices.index', [
            'title' => 'Invoices',
            'invoices' => $invoices,
        ]);
    }

    // -----------------------------------------------------------------------
    // Create
    // -----------------------------------------------------------------------

    public function create(): View
    {
        $tenants = Tenant::where('status', 'active')
            ->with(['unit', 'activeAgreement'])
            ->orderBy('name')
            ->get()
            ->filter(fn($t) => $t->activeAgreement !== null);

        return view('invoices.create', [
            'title' => 'Generate Invoice',
            'tenants' => $tenants,
        ]);
    }

    // -----------------------------------------------------------------------
    // Store
    // -----------------------------------------------------------------------

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $tenant = Tenant::with(['unit', 'activeAgreement'])->findOrFail($request->tenant_id);

        $invoice = $this->invoiceService->create(
            tenant: $tenant,
            month: $request->month,
            dueDate: $request->due_date,
            items: $request->items,
            notes: $request->notes,
        );

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice ' . $invoice->invoice_number . ' generated successfully.');
    }

    // -----------------------------------------------------------------------
    // Show
    // -----------------------------------------------------------------------

    public function show(Invoice $invoice): View
    {
        $invoice->load(['tenant', 'unit', 'agreement', 'items']);

        return view('invoices.show', [
            'title' => 'Invoice — ' . $invoice->invoice_number,
            'invoice' => $invoice,
        ]);
    }

    // -----------------------------------------------------------------------
    // Edit
    // -----------------------------------------------------------------------

    public function edit(Invoice $invoice): View|RedirectResponse
    {
        if (!$invoice->isDraft()) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $invoice->load(['tenant', 'unit', 'items']);

        return view('invoices.edit', [
            'title' => 'Edit Invoice — ' . $invoice->invoice_number,
            'invoice' => $invoice,
        ]);
    }

    // -----------------------------------------------------------------------
    // Update
    // -----------------------------------------------------------------------

    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        if (!$invoice->isDraft()) {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('error', 'Only draft invoices can be edited.');
        }

        $request->validate([
            'due_date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.type' => ['required', 'in:rent,maintenance,electricity,water,gas,fine,other'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
        ]);

        // Update items
        $invoice->items()->delete();

        $subtotal = 0;
        foreach ($request->items as $item) {
            $invoice->items()->create($item);
            $subtotal += (float) $item['amount'];
        }

        $invoice->update([
            'due_date' => $request->due_date,
            'notes' => $request->notes,
            'subtotal' => $subtotal,
            'total' => $subtotal,
        ]);

        // Regenerate PDF
        $this->invoiceService->generatePdf($invoice);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    // -----------------------------------------------------------------------
    // Destroy
    // -----------------------------------------------------------------------

    public function destroy(Invoice $invoice): RedirectResponse
    {
        if (!$invoice->isDraft()) {
            return redirect()
                ->route('invoices.index')
                ->with('error', 'Only draft invoices can be deleted.');
        }

        if ($invoice->pdf_path) {
            Storage::disk('local')->delete($invoice->pdf_path);
        }

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice deleted successfully.');
    }

    // -----------------------------------------------------------------------
    // Download PDF
    // -----------------------------------------------------------------------

    public function download(Invoice $invoice): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless($invoice->pdf_path && Storage::disk('local')->exists($invoice->pdf_path), 404);

        return Storage::disk('local')->download(
            $invoice->pdf_path,
            $invoice->invoice_number . '.pdf'
        );
    }

    // -----------------------------------------------------------------------
    // Mark as sent
    // -----------------------------------------------------------------------

    public function markSent(Invoice $invoice): RedirectResponse
    {
        $invoice->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return redirect()
            ->back()
            ->with('success', 'Invoice marked as sent.');
    }

    // -----------------------------------------------------------------------
    // Mark as paid
    // -----------------------------------------------------------------------

    public function markPaid(Invoice $invoice): RedirectResponse
    {
        $invoice->update(['status' => 'paid']);

        return redirect()
            ->back()
            ->with('success', 'Invoice marked as paid.');
    }

    // -----------------------------------------------------------------------
    // AJAX — pull items for tenant + month
    // -----------------------------------------------------------------------

    public function pullItems(Request $request): JsonResponse
    {
        $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'month' => ['required', 'date'],
        ]);

        $items = $this->invoiceService->pullItems(
            $request->tenant_id,
            $request->month,
        );

        return response()->json(['items' => $items]);
    }
}