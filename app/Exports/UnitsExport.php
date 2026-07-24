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
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UnitsExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnWidths,
    WithTitle,
    ShouldAutoSize
{
    public function __construct(
        protected Collection $units
    ) {}

    public function collection(): Collection
    {
        return $this->units->map(function ($unit, $index) {
            return [
                '#'                  => $index + 1,
                'Flat No.'           => $unit->unit_number,
                'Owner'              => $unit->landlord->name ?? '—',
                'Contact Number'     => $unit->landlord->phone ?? '—',
                'Floor'              => $unit->floor->name ?? '—',
                'Block'              => $unit->block->name ?? '—',
                'Area / Zone'        => $unit->area->name ?? '—',
                'Status'             => ($unit->is_self && $unit->otherTenant) ? 'Rented' : ucfirst($unit->status ?? '—'),
                'Ownership'          => $unit->is_self ? 'Other-Owned' : 'Managed by PM Mall',
                'Area (Sq. Ft.)'     => $unit->area_sqft ? number_format($unit->area_sqft, 2) : '—',
                'Default Rent (Rs.)' => $unit->default_monthly_rent ? number_format($unit->default_monthly_rent, 2) : '0.00',
                'Maintenance (Rs.)'  => $unit->default_maintenance_charge ? number_format($unit->default_maintenance_charge, 2) : '0.00',
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Flat No.',
            'Owner',
            'Contact Number',
            'Floor',
            'Block',
            'Area / Zone',
            'Status',
            'Ownership',
            'Area (Sq. Ft.)',
            'Default Monthly Rent (Rs.)',
            'Default Maintenance (Rs.)',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 16,
            'C' => 25,
            'D' => 18,
            'E' => 16,
            'F' => 16,
            'G' => 18,
            'H' => 14,
            'I' => 22,
            'J' => 16,
            'K' => 24,
            'L' => 24,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        $fullRange = "A1:{$highestColumn}{$highestRow}";

        $sheet->getStyle($fullRange)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('94A3B8');

        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D3461']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function title(): string
    {
        return 'Units Master List';
    }
}
