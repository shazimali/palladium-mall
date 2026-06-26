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

class AccountLedgerExport implements
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
            'Voucher #' => $e['voucher_no'] ?? '—',
            'Type' => $e['type'] ?? '—',
            'Description / Ref' => $e['description'] ?? '—',
            'Debit (Inflow)' => number_format($e['debit'], 2),
            'Credit (Outflow)' => number_format($e['credit'], 2),
            'Running Balance' => number_format($e['running_balance'], 2)
        ]);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Voucher #',
            'Type',
            'Description / Ref',
            'Debit (Inflow Rs.)',
            'Credit (Outflow Rs.)',
            'Running Balance (Rs.)'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,
            'B' => 18,
            'C' => 16,
            'D' => 35,
            'E' => 18,
            'F' => 18,
            'G' => 22,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->entries->count() + 1;
        $summaryRow = $lastRow + 2;

        $sheet->setCellValue("A{$summaryRow}", 'Summary');
        $sheet->setCellValue("A" . ($summaryRow + 1), 'Total Inflows (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 1), number_format($this->summary['total_inflow'], 2));
        $sheet->setCellValue("A" . ($summaryRow + 2), 'Total Outflows (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 2), number_format($this->summary['total_outflow'], 2));
        $sheet->setCellValue("A" . ($summaryRow + 3), 'Net Balance (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 3), number_format($this->summary['net_balance'], 2));

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
