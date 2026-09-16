<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Security Ledgers - Palladium Mall</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 15px;
        }

        .header-container {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 16px;
        }
        .header-brand {
            font-size: 28px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 4px 0;
        }
        .header-title {
            font-size: 15px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 12px 0;
        }

        .tags-container {
            text-align: center;
            margin-top: 8px;
        }
        .tag-pill {
            display: inline-block;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #334155;
            padding: 5px 12px;
            border-radius: 14px;
            font-size: 10px;
            font-weight: bold;
            margin: 3px 4px;
            text-transform: uppercase;
        }
        .tag-pill strong {
            color: #0f172a;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }
        .summary-box {
            background: #f8fafc;
            padding: 9px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }
        .summary-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
        }
        .summary-value {
            font-size: 14px;
            font-weight: 900;
            color: #0f172a;
            margin-top: 2px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .data-table th {
            background: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
            padding: 9px 7px;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        .data-table td {
            padding: 8px 7px;
            border-bottom: 1px solid #e2e8f0;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    <div class="header-container">
        <div class="header-brand">PALLADIUM MALL</div>
        <div class="header-title">SECURITY LEDGERS</div>

        @if(!empty($filter_tags))
            <div class="tags-container">
                @foreach($filter_tags as $t)
                    <span class="tag-pill">
                        <strong>{{ $t['label'] }}:</strong> {{ $t['value'] }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    <table class="summary-table">
        <tr>
            <td style="width: 25%; padding: 3px;">
                <div class="summary-box" style="border-color: #a7f3d0; background: #ecfdf5;">
                    <div class="summary-title" style="color: #047857;">Deposit Received</div>
                    <div class="summary-value" style="color: #047857;">Rs. {{ number_format($summary['total_received'], 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding: 3px;">
                <div class="summary-box" style="border-color: #fde68a; background: #fffbeb;">
                    <div class="summary-title" style="color: #b45309;">Deducted / Damage</div>
                    <div class="summary-value" style="color: #b45309;">Rs. {{ number_format($summary['total_deducted'], 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding: 3px;">
                <div class="summary-box" style="border-color: #fecdd3; background: #fff1f2;">
                    <div class="summary-title" style="color: #e11d48;">Deposit Refunded</div>
                    <div class="summary-value" style="color: #e11d48;">Rs. {{ number_format($summary['total_refunded'], 2) }}</div>
                </div>
            </td>
            <td style="width: 25%; padding: 3px;">
                <div class="summary-box" style="border-color: #e9d5ff; background: #faf5ff;">
                    <div class="summary-title" style="color: #7e22ce;">Currently Held</div>
                    <div class="summary-value" style="color: #7e22ce;">Rs. {{ number_format($summary['total_balance'], 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center">SR #</th>
                <th>DATE</th>
                <th>FLAT/SHOP</th>
                <th>OWNER</th>
                <th>TENANT</th>
                <th>TRANSACTION</th>
                <th>REFERENCE</th>
                <th class="text-right">DEBIT</th>
                <th class="text-right">CREDIT</th>
                <th class="text-right">BALANCE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rows as $r)
                <tr>
                    <td class="text-center" style="color: #94a3b8;">{{ $r['sr'] }}</td>
                    <td>{{ $r['date'] }}</td>
                    <td class="font-bold" style="color: #1d4ed8;">{{ $r['unit_number'] }}</td>
                    <td class="font-bold">{{ $r['owner'] }}</td>
                    <td>{{ $r['tenant_name'] }}</td>
                    @php
                        $pdfTypeColor = match($r['type']) {
                            'Received' => '#047857',
                            'Deducted' => '#b45309',
                            'Refunded' => '#e11d48',
                            default => '#475569',
                        };
                    @endphp
                    <td class="font-bold" style="color: {{ $pdfTypeColor }};">{{ $r['type'] }}</td>
                    <td>{{ $r['reference'] }}</td>
                    <td class="text-right font-bold" style="color: #e11d48;">{{ $r['debit'] > 0 ? number_format($r['debit'], 2) : '—' }}</td>
                    <td class="text-right font-bold" style="color: #059669;">{{ $r['credit'] > 0 ? number_format($r['credit'], 2) : '—' }}</td>
                    <td class="text-right font-bold">{{ number_format($r['balance'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: 900; font-size: 12px; background: #cbd5e1; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a;">
                <td colspan="7" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #0f172a;">Total ({{ $summary['total_records'] }} Records)</td>
                <td class="text-right" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #e11d48;">{{ number_format($summary['total_deducted'] + $summary['total_refunded'], 2) }}</td>
                <td class="text-right" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #059669;">{{ number_format($summary['total_received'], 2) }}</td>
                <td class="text-right" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #7e22ce;">{{ number_format($summary['total_balance'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
