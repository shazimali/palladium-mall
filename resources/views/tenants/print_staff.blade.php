<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} — Palladium Mall</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            font-size: 15px;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #000;
            background: #fff;
            padding: 24px 32px;
            line-height: 1.5;
            font-weight: 700;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .logo-text {
            font-size: 1.4rem;
            font-weight: 900;
            color: #0f172a;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title h2 {
            font-size: 1.15rem;
            font-weight: 900;
            color: #0f172a;
        }

        .doc-title p {
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            margin-top: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 24px;
        }

        thead tr {
            background: #e2e8f0;
        }

        thead th {
            padding: 10px;
            text-align: left;
            font-weight: 900;
            font-size: 0.82rem;
            text-transform: uppercase;
            color: #0f172a;
            border-bottom: 2px solid #0f172a;
        }

        thead th.text-right,
        tbody td.text-right,
        tfoot td.text-right {
            text-align: right;
        }

        thead th.text-center,
        tbody td.text-center {
            text-align: center;
        }

        tbody tr {
            border-bottom: 1px solid #e2e8f0;
            page-break-inside: avoid;
        }

        tbody td {
            padding: 9px 10px;
            color: #000;
            font-weight: 700;
            vertical-align: middle;
        }

        .mono {
            font-family: monospace;
            font-size: 0.9rem;
            font-weight: 800;
        }

        .amount {
            font-family: monospace;
            font-weight: 900;
            font-size: 1.05rem;
            color: #0f172a;
        }

        .unit {
            font-size: 1.05rem;
            font-weight: 900;
            color: #0f172a;
        }

        .name {
            font-weight: 900;
            color: #0f172a;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 0.7rem;
            font-weight: 800;
            border-radius: 4px;
            text-transform: uppercase;
            background: #e0e7ff;
            color: #3730a3;
            margin-left: 4px;
        }

        .sub-line {
            display: block;
            font-size: 0.8rem;
            font-weight: 800;
            color: #475569;
        }

        .emergency {
            color: #dc2626;
        }

        .footer {
            margin-top: 30px;
            border-top: 2px solid #0f172a;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            font-weight: 800;
            color: #475569;
        }

        .no-print {
            text-align: center;
            margin-bottom: 24px;
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-size: 0.95rem;
            font-weight: 800;
            cursor: pointer;
        }

        .print-btn:hover {
            background: #000;
        }

        /* @page MUST be top-level — nesting inside @media print breaks @bottom-right in all browsers */
        @page {
            size: A4 portrait;
            margin: 1.5cm 0.5cm 1.8cm 0.5cm;

            @bottom-right {
                content: "Page " counter(page) " of " counter(pages);
                font-size: 0.75rem;
                font-weight: 800;
                color: #475569;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
                font-weight: bold !important;
                zoom: 0.8;
            }

            thead {
                display: table-header-group;
            }

            /* position:fixed repeats on every printed page — cross-browser fallback */
            .print-page-number {
                display: block !important;
                position: fixed;
                bottom: 6px;
                right: 10px;
                font-size: 0.72rem;
                font-weight: 800;
                color: #475569;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    <div class="header">
        <span class="logo-text">PALLADIUM MALL</span>
        <div class="doc-title">
            <h2>{{ $pageTitle }}</h2>
            <p>Printed: {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 40px;">Sr #</th>
                <th style="width: 70px;">Flat No</th>
                <th style="width: 80px;">Floor</th>
                <th>Tenant Name</th>
                <th style="width: 150px;">Contact No / Emer No</th>
                <th>Landlord</th>
                <th style="width: 95px;">Start Date</th>
                <th class="text-right" style="width: 110px;">Rent / Sec</th>
            </tr>
        </thead>
        <tbody>
            @forelse($occupants as $index => $occupant)
                <tr>
                    <td class="text-center" style="color: #475569;">{{ $index + 1 }}</td>
                    <td class="unit">{{ $occupant['unit_number'] }}</td>
                    <td>{{ $occupant['floor'] }}</td>
                    <td class="name">
                        {{ $occupant['tenant_name'] }}
                        @if($occupant['is_other_owned'])
                            <span class="badge">Other-Owned</span>
                        @endif
                    </td>
                    <td class="mono">
                        @foreach(array_unique(array_filter([$occupant['phone'], $occupant['secondary_phone']], fn($n) => $n !== '—')) as $number)
                            <div>{{ $number }}</div>
                        @endforeach
                        @if($occupant['emergency_phone'] !== '—')
                            <div class="emergency">Emer: {{ $occupant['emergency_phone'] }}</div>
                        @endif
                        @if($occupant['phone'] === '—' && $occupant['secondary_phone'] === '—' && $occupant['emergency_phone'] === '—')
                            <div>—</div>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight: 900;">{{ $occupant['landlord_name'] }}</span>
                        @if(!empty($occupant['landlord_phone']) && $occupant['landlord_phone'] !== '—')
                            <span class="sub-line mono">{{ $occupant['landlord_phone'] }}</span>
                        @endif
                    </td>
                    <td><span class="mono">{{ $occupant['start_date'] }}</span></td>
                    <td class="text-right">
                        <span class="amount">{{ number_format($occupant['monthly_rent'], 0) }}</span>
                        <span class="sub-line mono">{{ $occupant['security_deposit'] > 0 ? number_format($occupant['security_deposit'], 0) : '—' }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: #94a3b8; padding: 40px 0;">No tenants found matching current filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($occupants) > 0)
            <tfoot>
                <tr style="background: #e2e8f0; border-top: 3px solid #0f172a; border-bottom: 3px solid #0f172a;">
                    <td colspan="7" style="padding: 12px 10px; font-weight: 900; font-size: 1.05rem; color: #0f172a;">TOTAL (Rent / Security)</td>
                    <td class="text-right" style="padding: 12px 10px;">
                        <span class="amount">{{ number_format(collect($occupants)->sum('monthly_rent'), 0) }}</span>
                        <span class="sub-line mono">{{ number_format(collect($occupants)->sum('security_deposit'), 0) }}</span>
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        <span>Palladium Mall Management Office</span>
        <span>Generated on {{ now()->format('d M Y \a\t h:i A') }}</span>
    </div>

    <div class="print-page-number" style="display:none;"></div>
</body>

</html>
