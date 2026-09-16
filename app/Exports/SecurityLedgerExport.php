<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SecurityLedgerExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize
{
    protected array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data['all_rows'])->map(fn($row) => [
            'SR #'        => $row['sr'],
            'DATE'        => $row['date'],
            'FLAT/SHOP'   => $row['unit_number'],
            'OWNER'       => $row['owner'],
            'TENANT'      => $row['tenant_name'],
            'TRANSACTION' => $row['type'],
            'REFERENCE'   => $row['reference'],
            'DEBIT'       => number_format($row['debit'], 2),
            'CREDIT'      => number_format($row['credit'], 2),
            'BALANCE'     => number_format($row['balance'], 2),
        ]);
    }

    public function headings(): array
    {
        return [
            'SR #',
            'DATE',
            'FLAT/SHOP',
            'OWNER',
            'TENANT',
            'TRANSACTION',
            'REFERENCE',
            'DEBIT',
            'CREDIT',
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
