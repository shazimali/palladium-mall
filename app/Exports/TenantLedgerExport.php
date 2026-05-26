<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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
        protected string $subjectName,   // tenant name or unit number
        protected array $summary,
    ) {
    }

    public function collection(): Collection
    {
        return $this->entries->map(fn($e) => [
            'Date' => $e['date']?->format('d M Y') ?? '—',
            'Month' => $e['month']?->format('M Y') ?? '—',
            'Description' => $e['description'],
            'Type' => ucfirst($e['type']),
            'Amount Due' => number_format($e['amount_due'], 2),
            'Amount Paid' => number_format($e['amount_paid'], 2),
            'Balance' => number_format($e['balance'], 2),
            'Status' => ucfirst($e['status']),
            'Paid At' => $e['paid_at'] ? $e['paid_at']->format('d M Y') : '—',
        ]);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Month',
            'Description',
            'Type',
            'Amount Due (Rs.)',
            'Amount Paid (Rs.)',
            'Balance (Rs.)',
            'Status',
            'Paid At',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Header row styling
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0B1C3D']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 12,
            'C' => 32,
            'D' => 14,
            'E' => 18,
            'F' => 18,
            'G' => 16,
            'H' => 12,
            'I' => 14,
        ];
    }

    public function title(): string
    {
        return 'Ledger — ' . $this->subjectName;
    }
}