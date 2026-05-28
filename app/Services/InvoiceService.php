<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Tenant;
use App\Models\Payment;
use App\Models\UtilityReading;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class InvoiceService
{
    // -----------------------------------------------------------------------
    // Pull items from existing records for a tenant + month
    // -----------------------------------------------------------------------

    public function pullItems(int $tenantId, string $month): array
    {
        $monthStart = Carbon::parse($month)->startOfMonth()->toDateString();
        $items = [];

        // Payments (rent, maintenance, fine, other)
        Payment::where('tenant_id', $tenantId)
            ->where('month', $monthStart)
            ->get()
            ->each(function ($p) use (&$items) {
                $items[] = [
                    'description' => ucfirst($p->type) . ' — ' . $p->month->format('F Y'),
                    'type' => $p->type,
                    'amount' => (float) $p->amount,
                ];
            });

        // Utility readings
        UtilityReading::where('tenant_id', $tenantId)
            ->where('month', $monthStart)
            ->get()
            ->each(function ($u) use (&$items) {
                $items[] = [
                    'description' => ucfirst($u->type) . ' Bill — ' . $u->month->format('F Y'),
                    'type' => $u->type,
                    'amount' => (float) $u->bill_amount,
                ];
            });

        return $items;
    }

    // -----------------------------------------------------------------------
    // Create invoice + items + generate PDF
    // -----------------------------------------------------------------------

    public function create(
        Tenant $tenant,
        string $month,
        string $dueDate,
        array $items,
        ?string $notes = null,
    ): Invoice {
        $monthStart = Carbon::parse($month)->startOfMonth()->toDateString();
        $subtotal = collect($items)->sum('amount');

        $invoice = Invoice::create([
            'tenant_id' => $tenant->id,
            'unit_id' => $tenant->unit_id,
            'agreement_id' => $tenant->activeAgreement->id,
            'invoice_number' => Invoice::generateNumber(),
            'month' => $monthStart,
            'due_date' => $dueDate,
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'status' => 'draft',
            'notes' => $notes,
        ]);

        // Create items
        foreach ($items as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'],
                'type' => $item['type'],
                'amount' => $item['amount'],
            ]);
        }

        // Generate PDF
        $this->generatePdf($invoice);

        return $invoice->fresh(['items', 'tenant', 'unit', 'agreement']);
    }

    // -----------------------------------------------------------------------
    // Regenerate PDF for an existing invoice
    // -----------------------------------------------------------------------

    public function generatePdf(Invoice $invoice): void
    {
        $invoice->loadMissing(['items', 'tenant', 'unit', 'agreement']);

        $pdf = Pdf::loadView('invoices.pdf', [
            'invoice' => $invoice,
        ])->setPaper('a4', 'portrait');

        $path = 'invoices/' . $invoice->invoice_number . '.pdf';

        // Delete old if exists
        if ($invoice->pdf_path && Storage::disk('local')->exists($invoice->pdf_path)) {
            Storage::disk('local')->delete($invoice->pdf_path);
        }

        Storage::disk('local')->put($path, $pdf->output());

        $invoice->update(['pdf_path' => $path]);
    }
}