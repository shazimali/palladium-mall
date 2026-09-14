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
                $entry['opening'],
                $entry['debit'],
                $entry['credit'],
                $entry['closing'],
            ];
        }

        if ($this->summary->isNotEmpty()) {
            $data[] = [
                'Grand Total',
                $this->summary->sum('opening'),
                $this->summary->sum('debit'),
                $this->summary->sum('credit'),
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
                'Opening Balance',
                'Total Debit',
                'Total Credit',
                'Closing Balance',
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
            'E' => 20,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:E1');
        $sheet->mergeCells('A2:E2');

        $sheet->getStyle('A1:E2')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true);
        $sheet->getStyle('A4:E4')->getFont()->setBold(true);
        $sheet->getStyle('A4:E4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('EAEAEA');

        $highestRow = $sheet->getHighestRow();

        for ($row = 5; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            if ($cellValue === 'Grand Total') {
                $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
                $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F0F0F0');
            }

            if ($sheet->getCell('B' . $row)->getValue() !== '' && is_numeric($sheet->getCell('B' . $row)->getValue())) {
                $sheet->getStyle('B' . $row . ':E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            }
        }

        return [];
    }
}
