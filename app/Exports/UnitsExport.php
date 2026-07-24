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
                'Unit Number'        => $unit->unit_number,
                'Type'               => ucfirst($unit->type ?? '—'),
                'Status'             => ucfirst($unit->status ?? '—'),
                'Ownership'          => $unit->is_self ? 'Other-Owned' : 'Managed by PM Mall',
                'Landlord / Owner'   => $unit->landlord->name ?? '—',
                'Floor'              => $unit->floor->name ?? '—',
                'Block'              => $unit->block->name ?? '—',
                'Area / Zone'        => $unit->area->name ?? '—',
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
            'Unit Number',
            'Type',
            'Status',
            'Ownership',
            'Landlord / Owner',
            'Floor',
            'Block',
            'Area / Zone',
            'Area (Sq. Ft.)',
            'Default Monthly Rent (Rs.)',
            'Default Maintenance (Rs.)',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 18,
            'C' => 14,
            'D' => 14,
            'E' => 22,
            'F' => 25,
            'G' => 18,
            'H' => 16,
            'I' => 18,
            'J' => 16,
            'K' => 24,
            'L' => 24,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
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
