<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccountSummaryExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected $summary;
    protected $dateFrom;
    protected $dateTo;

    public function __construct($summary, $dateFrom, $dateTo)
    {
        $this->summary = $summary;
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function array(): array
    {
        $data = [];

        foreach ($this->summary as $entry) {
            $data[] = [
                $entry['name'],
                $entry['payable'],
                $entry['receivable'],
                $entry['closing'],
            ];
        }

        if ($this->summary->isNotEmpty()) {
            $data[] = [
                'Grand Total',
                $this->summary->sum('payable'),
                $this->summary->sum('receivable'),
                $this->summary->sum('closing'),
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            ['Palladium Mall - Balance Sheet (as per Accounts)'],
            ['Statement Period: ' . ($this->dateFrom ?: 'Start') . ' to ' . ($this->dateTo ?: 'End')],
            [],
            [
                'Account Name',
                'Payables',
                'Receivables',
                'Balance',
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40,
            'B' => 20,
            'C' => 20,
            'D' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A2:D2');

        $sheet->getStyle('A1:D2')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A4:D4')->getFont()->setBold(true);
        $sheet->getStyle('A4:D4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('EAEAEA');

        $highestRow = $sheet->getHighestRow();

        for ($row = 5; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            if ($cellValue === 'Grand Total') {
                $sheet->getStyle('A' . $row . ':D' . $row)->getFont()->setBold(true);
                $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F0F0F0');
            }

            if ($sheet->getCell('B' . $row)->getValue() !== '' && is_numeric($sheet->getCell('B' . $row)->getValue())) {
                $sheet->getStyle('B' . $row . ':D' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            }
        }

        return [];
    }
}
