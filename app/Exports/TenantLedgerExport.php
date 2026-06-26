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

class TenantLedgerExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnWidths,
    WithTitle,
    ShouldAutoSize
{
    public function __construct(
        protected Collection $entries,
        protected string $title,
        protected array $summary
    ) {}

    public function collection(): Collection
    {
        return $this->entries->map(fn($e) => [
            'Date' => $e['date'] ? $e['date']->format('d M Y') : '—',
            'Description' => $e['description'] ?? '—',
            'Ref / Voucher #' => $e['reference'] ?? '—',
            'Debit (Charged)' => number_format($e['debit'], 2),
            'Credit (Paid)' => number_format($e['credit'], 2),
            'Running Balance' => number_format($e['running_balance'], 2)
        ]);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Description',
            'Ref / Voucher #',
            'Debit (Charged Rs.)',
            'Credit (Paid Rs.)',
            'Running Balance (Rs.)'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,
            'B' => 35,
            'C' => 20,
            'D' => 20,
            'E' => 20,
            'F' => 22,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->entries->count() + 1;
        $summaryRow = $lastRow + 2;

        $sheet->setCellValue("A{$summaryRow}", 'Summary');
        $sheet->setCellValue("A" . ($summaryRow + 1), 'Total Invoiced (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 1), number_format($this->summary['total_invoiced'], 2));
        $sheet->setCellValue("A" . ($summaryRow + 2), 'Total Paid (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 2), number_format($this->summary['total_paid'], 2));
        $sheet->setCellValue("A" . ($summaryRow + 3), 'Balance Due (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 3), number_format($this->summary['balance_due'], 2));

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D3461']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            $summaryRow => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1D3461']],
            ],
        ];
    }

    public function title(): string
    {
        return $this->title;
    }
}
