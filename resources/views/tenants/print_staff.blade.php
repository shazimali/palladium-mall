<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} — Palladium Mall</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, ui-sans-serif, system-ui, -apple-system, sans-serif;
            font-size: 12px;
            color: #0F172A;
            line-height: 1.4;
            padding: 16px 24px;
            background: #fff;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #0F172A;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .header h1 {
            font-size: 20px;
            font-weight: 900;
            color: #0F172A;
            margin-bottom: 4px;
            text-transform: uppercase;
            text-align: center;
        }

        .header p {
            font-size: 11px;
            font-weight: 600;
            color: #475569;
            text-align: center;
        }

        .inline-summary-container {
            padding: 0 12px;
            margin-bottom: 16px;
            font-size: 11px;
            color: #1E293B;
            line-height: 1.6;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }

        thead tr {
            background: #F1F5F9;
            color: #0F172A;
            border-bottom: 2px solid #334155;
        }

        thead th {
            padding: 9px 10px;
            text-align: left;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            color: #0F172A;
            border: 1px solid #CBD5E1;
        }

        thead th.text-right, tbody td.text-right {
            text-align: right;
        }

        tbody tr:nth-child(even) {
            background: #F8FAFC;
        }

        tbody td {
            padding: 8px 10px;
            border: 1px solid #E2E8F0;
            font-size: 11px;
            color: #0F172A;
            vertical-align: middle;
        }

        .unit-badge {
            display: inline-block;
            background: #0F172A;
            color: #fff;
            font-weight: 900;
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 6px;
            font-family: monospace;
        }

        .other-badge {
            display: inline-block;
            background: #8b5cf6;
            color: #fff;
            font-weight: 800;
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 4px;
            margin-left: 6px;
            text-transform: uppercase;
        }

        .contact-box {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .primary-phone {
            font-family: monospace;
            font-size: 11px;
            font-weight: 900;
            color: #0F172A;
        }

        .emergency-contact {
            font-size: 10px;
            font-weight: 800;
            color: #dc2626;
        }

        .amount {
            font-family: monospace;
            font-weight: 900;
            font-size: 11px;
        }

        .footer {
            margin-top: 18px;
            font-size: 10px;
            font-weight: 600;
            color: #64748B;
            text-align: center;
            border-top: 1px solid #CBD5E1;
            padding-top: 10px;
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0F172A;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-size: 13px;
            font-weight: 900;
            cursor: pointer;
        }

        @media print {
            @page {
                size: landscape;
                margin: 8mm;
            }
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                color: black !important;
                font-size: 12px !important;
                padding: 0 !important;
            }
            .header {
                background: transparent !important;
                border-bottom: 2px solid #000 !important;
                padding-bottom: 8px !important;
            }
            .header h1 {
                color: black !important;
                font-size: 22px !important;
                font-weight: 900 !important;
            }
            .header p {
                color: #222 !important;
                font-size: 11px !important;
            }
            thead th {
                background: #f1f5f9 !important;
                color: black !important;
                font-size: 11px !important;
                font-weight: 900 !important;
                border: 1px solid #94a3b8 !important;
            }
            tbody td {
                font-size: 11px !important;
                color: black !important;
                border: 1px solid #cbd5e1 !important;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    {{-- Centered Header --}}
    <div class="header">
        <h1>Palladium Mall Management System</h1>
        <p>
            {{ $pageTitle }} &bull; Generated: {{ now()->format('d M Y, H:i') }}
        </p>
    </div>

    {{-- Clean Inline Metadata & Summary Section --}}
    <div class="inline-summary-container">
        <div>
            <span style="font-weight: bold; color: #64748B;">Directory Type:</span> <span style="font-weight: 800; color: #0F172A;">{{ $pageTitle }}</span>
            <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
            <span style="font-weight: bold; color: #64748B;">Total Tenants Listed:</span> <span style="font-weight: 800; color: #0F172A;">{{ number_format(count($occupants)) }}</span>
            <span style="margin: 0 6px; color: #94A3B8;">&bull;</span>
            <span style="font-weight: bold; color: #64748B;">Printed On:</span> <span style="font-weight: 800; color: #0F172A;">{{ now()->format('d M Y, H:i') }}</span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 45px; text-align: center;">Sr #</th>
                <th>Flat / Shop</th>
                <th>Tenant Name</th>
                <th>Contact Details</th>
                <th>Landlord</th>
                <th>Start Date</th>
                <th class="text-right">Monthly Rent</th>
                <th class="text-right">Security Deposit</th>
            </tr>
        </thead>
        <tbody>
            @forelse($occupants as $index => $occupant)
                <tr>
                    <td style="text-align: center; font-weight: 800; color: #475569;">{{ $index + 1 }}</td>
                    <td>
                        <span class="unit-badge">{{ $occupant['unit_number'] }}</span>
                    </td>
                    <td style="font-weight: 900;">
                        {{ $occupant['tenant_name'] }}
                        @if($occupant['is_other_owned'])
                            <span class="other-badge">Other-Owned</span>
                        @endif
                    </td>
                    <td>
                        <div class="contact-box">
                            <span class="primary-phone">📞 {{ $occupant['phone'] }}</span>
                            @if(!empty($occupant['emergency_contact']) && $occupant['emergency_contact'] !== '—')
                                <span class="emergency-contact">🚨 Emer: {{ $occupant['emergency_contact'] }}</span>
                            @endif
                        </div>
                    </td>
                    <td style="font-weight: 800;">
                        {{ $occupant['landlord_name'] }}
                    </td>
                    <td style="font-family: monospace; font-weight: 800;">
                        {{ $occupant['start_date'] }}
                    </td>
                    <td class="text-right amount" style="color: #059669;">
                        Rs. {{ number_format($occupant['monthly_rent'], 2) }}
                    </td>
                    <td class="text-right amount" style="color: #2563eb;">
                        Rs. {{ number_format($occupant['security_deposit'], 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 30px; font-size: 12px; font-weight: 800; color: #64748B;">
                        No tenants found matching current filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Palladium Mall Management Office &bull; {{ $pageTitle }} &bull; Printed on {{ now()->format('d M Y, H:i') }}
    </div>

    <script>
        window.addEventListener('load', function () {
            if (window.opener) { setTimeout(function () { window.print(); }, 400); }
        });
    </script>
</body>
</html>
