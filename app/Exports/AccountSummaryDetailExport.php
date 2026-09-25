<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AccountSummaryDetailExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths
{
    protected $summary;
    protected $dateFrom;
    protected $dateTo;

    protected $groupLabels = [
        'asset' => 'Assets (Bank & Cash)',
        'liability' => 'Equity & Liabilities (Owners)',
        'receivable' => 'Tenants',
        'tenant_security_deposit' => 'Tenant Security Deposits',
        'tenant_security_deposit_pending' => 'Pending Security Deposits',
        'expense' => 'Expenses',
        'landlord_receivable' => 'Landlord Receivables',
        'landlord_payable' => 'Landlord Payables',
        'party_receivable' => 'Party Receivables',
        'party_payable' => 'Party Payables',
        'jv_payable' => 'JV Payables',
    ];

    public function __construct($summary, $dateFrom, $dateTo)
    {
        $this->summary = $summary->groupBy('group');
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function array(): array
    {
        $data = [];

        $grandTotalOpening = 0;
        $grandTotalDebit = 0;
        $grandTotalCredit = 0;
        $grandTotalClosing = 0;

        foreach ($this->summary as $groupName => $entries) {
            $groupLabel = $this->groupLabels[$groupName] ?? ucfirst(str_replace('_', ' ', $groupName));

            // Group Header
            $data[] = [
                $groupLabel,
                '', '', '', ''
            ];

            $groupOpening = 0;
            $groupDebit = 0;
            $groupCredit = 0;
            $groupClosing = 0;

            foreach ($entries as $entry) {
                $groupOpening += $entry['opening'];
                $groupDebit += $entry['debit'];
                $groupCredit += $entry['credit'];
                $groupClosing += $entry['closing'];

                $data[] = [
                    $entry['name'],
                    $entry['opening'],
                    $entry['debit'],
                    $entry['credit'],
                    $entry['closing'],
                ];
            }

            // Group Total
            $data[] = [
                'Group Total:',
                $groupOpening,
                $groupDebit,
                $groupCredit,
                $groupClosing,
            ];

            // Empty row after group
            $data[] = ['', '', '', '', ''];

            $grandTotalOpening += $groupOpening;
            $grandTotalDebit += $groupDebit;
            $grandTotalCredit += $groupCredit;
            $grandTotalClosing += $groupClosing;
        }

        if ($this->summary->isNotEmpty()) {
            $data[] = [
                'Grand Total',
                $grandTotalOpening,
                $grandTotalDebit,
                $grandTotalCredit,
                $grandTotalClosing,
            ];
        }

        return $data;
    }

    public function headings(): array
    {
        return [
            ['Palladium Mall - Account Summary'],
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

        // Find group rows and total rows to bold them
        for ($row = 5; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            if ($cellValue && !in_array($cellValue, ['Account Name', 'Group Total:', 'Grand Total']) && $sheet->getCell('B' . $row)->getValue() === '') {
                // Group Header
                $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
                $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F5F5F5');
            } elseif (in_array($cellValue, ['Group Total:', 'Grand Total'])) {
                // Total Rows
                $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
                $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('F0F0F0');
            }

            // Format numbers
            if ($sheet->getCell('B' . $row)->getValue() !== '' && is_numeric($sheet->getCell('B' . $row)->getValue())) {
                $sheet->getStyle('B' . $row . ':E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');
            }
        }

        return [];
    }
}
