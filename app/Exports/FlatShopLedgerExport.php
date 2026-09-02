<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class FlatShopLedgerExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        if (!empty($this->data['is_security_deposit'])) {
            return collect($this->data['all_rows'])->map(fn($row) => [
                'SR #'                 => $row['sr'],
                'FLAT/SHOP'            => $row['unit_number'],
                'OWNER'                => $row['owner'],
                'TENANT'               => $row['tenant_name'],
                'STATUS'               => $row['status'],
                'REQUIRED DEPOSIT'     => number_format($row['required_deposit'], 2),
                'COLLECTED DEPOSIT'    => number_format($row['collected_deposit'], 2),
                'PENDING DEPOSIT'      => number_format($row['pending_deposit'], 2),
                'DEDUCTIONS / DAMAGE'  => number_format($row['deduction_deposit'], 2),
                'NET REFUNDABLE'       => number_format($row['net_refundable'], 2),
            ]);
        }

        return collect($this->data['all_rows'])->map(fn($row) => [
            'SR #'             => $row['sr'],
            'FLAT/SHOP'        => $row['unit_number'],
            'TENANT'           => $row['tenant_name'],
            'BILLING TYPE'     => $row['type_label'],
            'PREV. UNPAID'     => number_format($row['prev_unpaid'], 2),
            'AMOUNT DUE'       => number_format($row['amount_due'], 2),
            'AMOUNT PAID'      => number_format($row['amount_paid'], 2),
            'PAYMENT METHOD'   => $row['payment_method'],
            'PAYMENT ACCOUNT'  => $row['payment_account'],
            'PAID AT'          => $row['paid_at'],
            'BALANCE'          => number_format($row['balance'], 2),
        ]);
    }

    public function headings(): array
    {
        if (!empty($this->data['is_security_deposit'])) {
            return [
                'SR #',
                'FLAT/SHOP',
                'OWNER',
                'TENANT',
                'STATUS',
                'REQUIRED DEPOSIT',
                'COLLECTED DEPOSIT',
                'PENDING DEPOSIT',
                'DEDUCTIONS / DAMAGE',
                'NET REFUNDABLE',
            ];
        }

        return [
            'SR #',
            'FLAT/SHOP',
            'TENANT',
            'BILLING TYPE',
            'PREV. UNPAID',
            'AMOUNT DUE',
            'AMOUNT PAID',
            'PAYMENT METHOD',
            'PAYMENT ACCOUNT',
            'PAID AT',
            'BALANCE',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
