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

class ExpenseLedgerExport implements
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
        protected array $summary,
        protected bool $isAll = false
    ) {}

    public function collection(): Collection
    {
        return $this->entries->map(function ($e) {
            $row = [
                'Date' => $e['date'] ? $e['date']->format('d M Y') : '—',
                'Voucher #' => $e['voucher_no'] ?? '—',
                'Spent On / Notes' => $e['notes'] ?? '—',
            ];
            if ($this->isAll) {
                $row['Expense Category'] = $e['expense_head'] ?? '—';
            }
            $row['Payment Account'] = $e['payment_account'] ?? '—';
            $row['Reference'] = $e['reference'] ?? '—';
            $row['Amount'] = number_format($e['amount'], 2);

            return $row;
        });
    }

    public function headings(): array
    {
        $headings = ['Date', 'Voucher #', 'Spent On / Notes'];
        if ($this->isAll) {
            $headings[] = 'Expense Category';
        }
        $headings[] = 'Payment Account';
        $headings[] = 'Reference';
        $headings[] = 'Amount (Rs.)';

        return $headings;
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 16, 'B' => 18, 'C' => 35];

        if ($this->isAll) {
            $widths['D'] = 22;
            $widths['E'] = 20;
            $widths['F'] = 20;
            $widths['G'] = 20;
        } else {
            $widths['D'] = 20;
            $widths['E'] = 20;
            $widths['F'] = 20;
        }

        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $this->entries->count() + 1;
        $summaryRow = $lastRow + 2;

        $sheet->setCellValue("A{$summaryRow}", 'Summary');
        $sheet->setCellValue("A" . ($summaryRow + 1), 'Total Spent (Rs.)');
        $sheet->setCellValue("B" . ($summaryRow + 1), number_format($this->summary['total_amount'], 2));

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
