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

class ReportExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    WithColumnWidths,
    WithTitle,
    ShouldAutoSize
{
    public function __construct(
        protected Collection $entries,
        protected string $label,
        protected array $summary,
        protected string $reportType = 'all',
    ) {
    }

    // -------------------------------------------------------------------------
    // Data rows
    // -------------------------------------------------------------------------

    public function collection(): Collection
    {
        if ($this->reportType === 'monthly_matrix') {
            return $this->entries->map(function ($e) {
                $row = [
                    'SR' => $e['sr'],
                    'Date' => $e['date'],
                    'RSV' => $e['rsv'],
                    'Flat No' => $e['flat_no'],
                    'Owner' => $e['owner'],
                    'Tenant' => $e['tenant'],
                    'Status' => $e['status'],
                    'Serv' => number_format((float) $e['serv'], 2),
                    'Extra' => number_format((float) $e['extra'], 2),
                    'Sec. Dep' => number_format((float) $e['security_deposit'], 2),
                    'Rent' => number_format((float) $e['rent'], 2),
                    'Total Amount' => number_format((float) $e['total_amount'], 2),
                    'Received' => number_format((float) $e['received'], 2),
                ];

                foreach ($e['payment_accounts'] as $accName => $amount) {
                    $row[$accName] = number_format((float) $amount, 2);
                }

                $row['Total'] = number_format((float) $e['received'], 2);
                $row['Prev. Unpaid'] = number_format((float) $e['prev_unpaid'], 2);
                $row['Pending'] = number_format((float) $e['pending'], 2);

                return $row;
            });
        }

        if ($this->reportType === 'potential_revenue') {
            return $this->entries->map(fn($e) => [
                'Flat/Shop' => $e['unit_number'],
                'Type' => ucfirst($e['type']),
                'Status' => ucfirst($e['status']),
                'Owner' => $e['landlord'] ?? '—',
                'Rent Source' => $e['source'],
                'Rent (PKR)' => number_format((float) $e['rent'], 2),
                'Maintenance (PKR)' => number_format((float) $e['maintenance'], 2),
                'Total Potential (PKR)' => number_format((float) $e['total'], 2),
            ]);
        }

        return $this->entries->map(fn($e) => [
            'Created Date' => $e['created_date']?->format('d M Y') ?? '—',
            'Voucher #' => $e['voucher_number'] ?? '—',
            'Flat/Shop' => $e['unit'] ?? '—',
            'Type' => ucfirst($e['type'] ?? ''),
            'Landlord' => $e['landlord'] ?? '—',
            'Tenant' => $e['tenant'] ?? '—',
            'Amount Due' => number_format((float) $e['amount_due'], 2),
            'Amount Paid' => number_format((float) $e['amount_paid'], 2),
            'Balance' => number_format((float) $e['balance'], 2),
            'Payment Status' => ucfirst($e['status'] ?? ''),
            'Paid At' => $e['paid_at'] ? $e['paid_at']->format('d M Y') : '—',
            'Payment Account' => $e['payment_account'] ?? '—',
        ]);
    }

    // -------------------------------------------------------------------------
    // Column headings
    // -------------------------------------------------------------------------

    public function headings(): array
    {
        if ($this->reportType === 'potential_revenue') {
            return [
                'Flat/Shop',
                'Type',
                'Status',
                'Owner',
                'Rent Source',
                'Rent (PKR)',
                'Maintenance (PKR)',
                'Total Potential (PKR)',
            ];
        }

        if ($this->reportType === 'monthly_matrix') {
            $headers = [
                'SR',
                'Date',
                'RSV',
                'Flat No',
                'Owner',
                'Tenant',
                'Status',
                'Serv (Rs.)',
                'Extra (Rs.)',
                'Sec. Dep (Rs.)',
                'Rent (Rs.)',
                'Total Amount (Rs.)',
                'Received (Rs.)',
            ];

            $paymentAccounts = \App\Models\PaymentAccount::orderBy('name')->pluck('name')->toArray();
            foreach ($paymentAccounts as $accName) {
                $headers[] = $accName . ' (Rs.)';
            }

            $headers[] = 'Total (Rs.)';
            $headers[] = 'Prev. Unpaid (Rs.)';
            $headers[] = 'Pending (Rs.)';

            return $headers;
        }

        return [
            'Created Date',
            'Voucher #',
            'Flat/Shop',
            'Type',
            'Landlord',
            'Tenant',
            'Amount Due (Rs.)',
            'Amount Paid (Rs.)',
            'Balance (Rs.)',
            'Payment Status',
            'Paid At',
            'Payment Account',
        ];
    }

    // -------------------------------------------------------------------------
    // Styles
    // -------------------------------------------------------------------------

    public function styles(Worksheet $sheet): array
    {
        // Summary rows inserted after data
        $lastDataRow = $this->entries->count() + 1;  // +1 for heading
        $summaryRow = $lastDataRow + 2;

        if ($this->reportType === 'potential_revenue') {
            $sheet->setCellValue("A{$summaryRow}", 'Summary');
            $sheet->setCellValue("A" . ($summaryRow + 1), 'Total Flats/Shops');
            $sheet->setCellValue("B" . ($summaryRow + 1), $this->summary['count']);
            $sheet->setCellValue("A" . ($summaryRow + 2), 'Rented Units');
            $sheet->setCellValue("B" . ($summaryRow + 2), $this->summary['rented_count']);
            $sheet->setCellValue("A" . ($summaryRow + 3), 'Vacant/Other Units');
            $sheet->setCellValue("B" . ($summaryRow + 3), $this->summary['vacant_count']);
            $sheet->setCellValue("A" . ($summaryRow + 4), 'Total Potential Rent (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + 4), number_format($this->summary['total_rent'], 2));
            $sheet->setCellValue("A" . ($summaryRow + 5), 'Total Potential Maintenance (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + 5), number_format($this->summary['total_maintenance'], 2));
            $sheet->setCellValue("A" . ($summaryRow + 6), 'Combined Potential Monthly Revenue (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + 6), number_format($this->summary['total_combined'], 2));
        } elseif ($this->reportType === 'monthly_matrix') {
            // Write summary block below the data for Monthly Matrix
            $sheet->setCellValue("A{$summaryRow}", 'Summary');
            $sheet->setCellValue("A" . ($summaryRow + 1), 'Total Serv (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + 1), number_format($this->summary['total_serv'], 2));
            $sheet->setCellValue("A" . ($summaryRow + 2), 'Total Extra (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + 2), number_format($this->summary['total_extra'], 2));
            $sheet->setCellValue("A" . ($summaryRow + 3), 'Total Security Deposit (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + 3), number_format($this->summary['total_security_deposit'], 2));
            $sheet->setCellValue("A" . ($summaryRow + 4), 'Total Rent (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + 4), number_format($this->summary['total_rent'], 2));
            $sheet->setCellValue("A" . ($summaryRow + 5), 'Total Amount (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + 5), number_format($this->summary['total_amount'], 2));
            $sheet->setCellValue("A" . ($summaryRow + 6), 'Total Received (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + 6), number_format($this->summary['total_received'], 2));

            $idx = 7;
            foreach ($this->summary['accounts_total'] as $accName => $total) {
                $sheet->setCellValue("A" . ($summaryRow + $idx), "Received in {$accName} (Rs.)");
                $sheet->setCellValue("B" . ($summaryRow + $idx), number_format($total, 2));
                $idx++;
            }

            $sheet->setCellValue("A" . ($summaryRow + $idx), 'Total Prev. Unpaid (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + $idx), number_format($this->summary['total_prev_unpaid'], 2));
            $idx++;

            $sheet->setCellValue("A" . ($summaryRow + $idx), 'Total Pending (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + $idx), number_format($this->summary['total_pending'], 2));

            $sheet->setCellValue("A" . ($summaryRow + $idx + 1), 'Total Records');
            $sheet->setCellValue("B" . ($summaryRow + $idx + 1), $this->summary['count']);
        } else {
            // Write summary block below the data for flat report
            $sheet->setCellValue("A{$summaryRow}", 'Summary');
            $sheet->setCellValue("A" . ($summaryRow + 1), 'Total Due (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + 1), number_format($this->summary['total_due'], 2));
            $sheet->setCellValue("A" . ($summaryRow + 2), 'Total Paid (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + 2), number_format($this->summary['total_paid'], 2));
            $sheet->setCellValue("A" . ($summaryRow + 3), 'Outstanding (Rs.)');
            $sheet->setCellValue("B" . ($summaryRow + 3), number_format($this->summary['outstanding'], 2));

            $t = $this->reportType;
            if ($t === 'other_owned' || $t === 'occupied' || $t === 'occupide' || $t === 'non_occupied' || $t === 'non_occupide') {
                $label = match ($t) {
                    'occupied', 'occupide' => 'Occupied (Ext) Collected (Rs.)',
                    'non_occupied', 'non_occupide' => 'Vacant (Ext) Collected (Rs.)',
                    default => 'Other Owned Collected (Rs.)',
                };
                $sheet->setCellValue("A" . ($summaryRow + 4), $label);
                $sheet->setCellValue("B" . ($summaryRow + 4), number_format($this->summary['maintenance_collected'], 2));

                $sheet->setCellValue("A" . ($summaryRow + 5), 'Total Records');
                $sheet->setCellValue("B" . ($summaryRow + 5), $this->summary['count']);
            } else {
                $sheet->setCellValue("A" . ($summaryRow + 4), 'Rent Collected (Rs.)');
                $sheet->setCellValue("B" . ($summaryRow + 4), number_format($this->summary['rent_collected'], 2));
                $sheet->setCellValue("A" . ($summaryRow + 5), 'Maintenance Collected (Rs.)');
                $sheet->setCellValue("B" . ($summaryRow + 5), number_format($this->summary['maintenance_collected'], 2));
                $sheet->setCellValue("A" . ($summaryRow + 6), 'Utilities Paid (Rs.)');
                $sheet->setCellValue("B" . ($summaryRow + 6), number_format($this->summary['utilities_paid'], 2));
                $sheet->setCellValue("A" . ($summaryRow + 7), 'Fines Collected (Rs.)');
                $sheet->setCellValue("B" . ($summaryRow + 7), number_format($this->summary['fines_collected'], 2));
                $sheet->setCellValue("A" . ($summaryRow + 8), 'Total Records');
                $sheet->setCellValue("B" . ($summaryRow + 8), $this->summary['count']);
            }
        }

        return [
            // Header row
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1D3461']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
            // Summary label row
            $summaryRow => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => '1D3461']],
            ],
        ];
    }

    // -------------------------------------------------------------------------
    // Column widths
    // -------------------------------------------------------------------------

    public function columnWidths(): array
    {
        if ($this->reportType === 'monthly_matrix') {
            return [];
        }

        return [
            'A' => 16,  // Created Date
            'B' => 18,  // Voucher #
            'C' => 14,  // Flat/Shop
            'D' => 14,  // Type
            'E' => 22,  // Landlord
            'F' => 22,  // Tenant
            'G' => 18,  // Amount Due
            'H' => 18,  // Amount Paid
            'I' => 16,  // Balance
            'J' => 16,  // Payment Status
            'K' => 14,  // Paid At
            'L' => 22,  // Payment Account
        ];
    }

    // -------------------------------------------------------------------------
    // Sheet title
    // -------------------------------------------------------------------------

    public function title(): string
    {
        return $this->label;
    }
}
