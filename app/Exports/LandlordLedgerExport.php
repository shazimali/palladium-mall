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

class LandlordLedgerExport implements
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
            'Date' => $e['date'] ? (\Carbon\Carbon::parse($e['date'])->format('d M Y')) : '—',
            'Flat/Shop' => !empty($e['unit_number']) ? $e['unit_number'] : '—',
            'Description' => $e['description'] ?? '—',
            'Voucher / Ref #' => $e['voucher_no'] ?? '—',
            'Debit (Payable Rs.)' => $e['debit'] > 0 ? number_format($e['debit'], 2) : '—',
            'Credit (Paid Rs.)' => $e['credit'] > 0 ? number_format($e['credit'], 2) : '—',
            'Running Balance (Rs.)' => number_format($e['running_balance'], 2)
        ]);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Flat/Shop',
            'Description',
            'Voucher / Ref #',
            'Debit (Payable Rs.)',
            'Credit (Paid Rs.)',
            'Running Balance (Rs.)'
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 16,
            'B' => 18,
            'C' => 45,
            'D' => 20,
            'E' => 20,
            'F' => 20,
            'G' => 22,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->entries->count() + 1;
        $summaryRow = $lastRow + 2;

        $sheet->setCellValue("A{$summaryRow}", 'Summary');
        $sheet->setCellValue("A" . ($summaryRow + 1), 'Total Unit Value (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 1), number_format($this->summary['total_debit'], 2));
        $sheet->setCellValue("A" . ($summaryRow + 2), 'Total Payments Received (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 2), number_format($this->summary['total_credit'], 2));
        $sheet->setCellValue("A" . ($summaryRow + 3), 'Outstanding Balance (Rs.)');
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
