<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReportExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnWidths,
    WithTitle,
    ShouldAutoSize
{
    public function __construct(
        protected Collection $entries,
        protected string     $label,
        protected array      $summary,
    ) {}

    // -------------------------------------------------------------------------
    // Data rows
    // -------------------------------------------------------------------------

    public function collection(): Collection
    {
        return $this->entries->map(fn($e) => [
            'Month'           => $e['month']?->format('M Y')    ?? '—',
            'Date'            => $e['date']?->format('d M Y')   ?? '—',
            'Flat/Shop'       => $e['unit']                     ?? '—',
            'Tenant'          => $e['tenant']                   ?? '—',
            'Landlord'        => $e['landlord']                 ?? '—',
            'Category'        => ucfirst($e['category']         ?? ''),
            'Type'            => ucfirst($e['type']             ?? ''),
            'Payment Method'  => $e['payment_method']           ?? '—',
            'Payment Account' => $e['payment_account']          ?? '—',
            'Amount Due'      => number_format((float) $e['amount_due'],  2),
            'Amount Paid'     => number_format((float) $e['amount_paid'], 2),
            'Balance'         => number_format((float) $e['balance'],     2),
            'Status'          => ucfirst($e['status']           ?? ''),
            'Paid At'         => $e['paid_at'] ? $e['paid_at']->format('d M Y') : '—',
        ]);
    }

    // -------------------------------------------------------------------------
    // Column headings
    // -------------------------------------------------------------------------

    public function headings(): array
    {
        return [
            'Month',
            'Date',
            'Flat/Shop',
            'Tenant',
            'Landlord',
            'Category',
            'Type',
            'Payment Method',
            'Payment Account',
            'Amount Due (Rs.)',
            'Amount Paid (Rs.)',
            'Balance (Rs.)',
            'Status',
            'Paid At',
        ];
    }

    // -------------------------------------------------------------------------
    // Styles
    // -------------------------------------------------------------------------

    public function styles(Worksheet $sheet): array
    {
        // Summary rows inserted after data
        $lastDataRow = $this->entries->count() + 1;  // +1 for heading
        $summaryRow  = $lastDataRow + 2;

        // Write summary block below the data
        $sheet->setCellValue("A{$summaryRow}", 'Summary');
        $sheet->setCellValue("A" . ($summaryRow + 1), 'Total Due (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 1), number_format($this->summary['total_due'], 2));
        $sheet->setCellValue("A" . ($summaryRow + 2), 'Total Paid (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 2), number_format($this->summary['total_paid'], 2));
        $sheet->setCellValue("A" . ($summaryRow + 3), 'Outstanding (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 3), number_format($this->summary['outstanding'], 2));
        $sheet->setCellValue("A" . ($summaryRow + 4), 'Rent Collected (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 4), number_format($this->summary['rent_collected'], 2));
        $sheet->setCellValue("A" . ($summaryRow + 5), 'Utilities Paid (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 5), number_format($this->summary['utilities_paid'], 2));
        $sheet->setCellValue("A" . ($summaryRow + 6), 'Fines Collected (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 6), number_format($this->summary['fines_collected'], 2));
        $sheet->setCellValue("A" . ($summaryRow + 7), 'Total Records');
        $sheet->setCellValue("B" . ($summaryRow + 7), $this->summary['count']);

        return [
            // Header row
            1 => [
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D3461']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Summary label row
            $summaryRow => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1D3461']],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Column widths
    // -------------------------------------------------------------------------

    public function columnWidths(): array
    {
        return [
            'A' => 12,  // Month
            'B' => 14,  // Date
            'C' => 14,  // Flat/Shop
            'D' => 22,  // Tenant
            'E' => 22,  // Landlord
            'F' => 12,  // Category
            'G' => 14,  // Type
            'H' => 18,  // Payment Method
            'I' => 22,  // Payment Account
            'J' => 18,  // Amount Due
            'K' => 18,  // Amount Paid
            'L' => 16,  // Balance
            'M' => 12,  // Status
            'N' => 14,  // Paid At
        ];
    }

    // -------------------------------------------------------------------------
    // Sheet title
    // -------------------------------------------------------------------------

    public function title(): string
    {
        return $this->label;
    }
}
