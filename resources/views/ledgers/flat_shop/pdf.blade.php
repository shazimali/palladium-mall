<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Flat / Shop Ledger - Palladium Mall</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 15px;
        }

        /* Header Centric Data & Big Font */
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

        /* Filter Tags Styling */
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

        /* Summary Box */
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

        /* Data Table */
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
        
        .bg-blue { background: #eff6ff; }
        .bg-green { background: #ecfdf5; }
        .bg-rose { background: #fff1f2; }
        .bg-amber { background: #fffbeb; }
        .bg-purple { background: #faf5ff; }
    </style>
</head>
<body>

    {{-- Centered Header with Big Font & Active Filter Tags --}}
    <div class="header-container">
        <div class="header-brand">PALLADIUM MALL</div>
        <div class="header-title">
            FLAT / SHOP LEDGER STATEMENT
            @if(!empty($is_security_deposit))
                (SECURITY DEPOSIT MATRIX)
            @endif
        </div>

        {{-- Filter Tags --}}
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

    @if(!empty($is_security_deposit))
        {{-- Security Deposit Matrix KPI Summary Table --}}
        <table class="summary-table">
            <tr>
                <td style="width: 20%; padding: 3px;">
                    <div class="summary-box" style="border-color: #bfdbfe; background: #eff6ff;">
                        <div class="summary-title" style="color: #1d4ed8;">Required Deposit</div>
                        <div class="summary-value" style="color: #1d4ed8;">Rs. {{ number_format($summary['total_required'], 2) }}</div>
                    </div>
                </td>
                <td style="width: 20%; padding: 3px;">
                    <div class="summary-box" style="border-color: #a7f3d0; background: #ecfdf5;">
                        <div class="summary-title" style="color: #047857;">Collected Deposit</div>
                        <div class="summary-value" style="color: #047857;">Rs. {{ number_format($summary['total_collected'], 2) }}</div>
                    </div>
                </td>
                <td style="width: 20%; padding: 3px;">
                    <div class="summary-box" style="border-color: #fecdd3; background: #fff1f2;">
                        <div class="summary-title" style="color: #e11d48;">Pending Deposit</div>
                        <div class="summary-value" style="color: #e11d48;">Rs. {{ number_format($summary['total_pending'], 2) }}</div>
                    </div>
                </td>
                <td style="width: 20%; padding: 3px;">
                    <div class="summary-box" style="border-color: #fde68a; background: #fffbeb;">
                        <div class="summary-title" style="color: #b45309;">Deductions / Damage</div>
                        <div class="summary-value" style="color: #b45309;">Rs. {{ number_format($summary['total_deductions'], 2) }}</div>
                    </div>
                </td>
                <td style="width: 20%; padding: 3px;">
                    <div class="summary-box" style="border-color: #e9d5ff; background: #faf5ff;">
                        <div class="summary-title" style="color: #7e22ce;">Net Refundable</div>
                        <div class="summary-value" style="color: #7e22ce;">Rs. {{ number_format($summary['total_net_refundable'], 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Security Deposit Matrix Table --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center">SR</th>
                    <th>FLAT/SHOP</th>
                    <th>OWNER</th>
                    <th>TENANT</th>
                    <th class="text-center">STATUS</th>
                    <th class="text-right bg-blue">REQUIRED DEPOSIT</th>
                    <th class="text-right bg-green">COLLECTED DEPOSIT</th>
                    <th class="text-right bg-rose">PENDING DEPOSIT</th>
                    <th class="text-right bg-amber">DEDUCTIONS</th>
                    <th class="text-right bg-purple">NET REFUNDABLE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td class="text-center" style="color: #94a3b8;">{{ $r['sr'] }}</td>
                        <td class="font-bold" style="color: #1d4ed8;">{{ $r['unit_number'] }}</td>
                        <td class="font-bold">{{ $r['owner'] }}</td>
                        <td class="font-bold">{{ $r['tenant_name'] }}</td>
                        <td class="text-center font-bold">{{ $r['status'] }}</td>
                        <td class="text-right font-bold bg-blue" style="color: #1d4ed8;">{{ $r['required_deposit'] > 0 ? number_format($r['required_deposit'], 2) : '—' }}</td>
                        <td class="text-right font-bold bg-green" style="color: #047857;">{{ $r['collected_deposit'] > 0 ? number_format($r['collected_deposit'], 2) : '—' }}</td>
                        <td class="text-right font-bold bg-rose" style="color: #e11d48;">{{ $r['pending_deposit'] > 0 ? number_format($r['pending_deposit'], 2) : '—' }}</td>
                        <td class="text-right font-bold bg-amber" style="color: #b45309;">{{ $r['deduction_deposit'] > 0 ? number_format($r['deduction_deposit'], 2) : '—' }}</td>
                        <td class="text-right font-bold bg-purple" style="color: #7e22ce;">{{ $r['net_refundable'] > 0 ? number_format($r['net_refundable'], 2) : '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: 900; font-size: 12px; background: #cbd5e1; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a;">
                    <td colspan="5" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #0f172a;">Total ({{ $summary['total_records'] }} Units)</td>
                    <td class="text-right" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #1d4ed8;">{{ number_format($summary['total_required'], 2) }}</td>
                    <td class="text-right" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #047857;">{{ number_format($summary['total_collected'], 2) }}</td>
                    <td class="text-right" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #e11d48;">{{ number_format($summary['total_pending'], 2) }}</td>
                    <td class="text-right" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #b45309;">{{ number_format($summary['total_deductions'], 2) }}</td>
                    <td class="text-right" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #7e22ce;">{{ number_format($summary['total_net_refundable'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @else
        {{-- Standard Billing Ledger KPI Summary Table --}}
        <table class="summary-table">
            <tr>
                <td style="width: 20%; padding: 3px;">
                    <div class="summary-box">
                        <div class="summary-title">Total Records</div>
                        <div class="summary-value">{{ number_format($summary['total_records']) }}</div>
                    </div>
                </td>
                <td style="width: 20%; padding: 3px;">
                    <div class="summary-box" style="border-color: #fde68a; background: #fffbeb;">
                        <div class="summary-title" style="color: #b45309;">Prev. Unpaid</div>
                        <div class="summary-value" style="color: #b45309;">Rs. {{ number_format($summary['total_prev_unpaid'], 2) }}</div>
                    </div>
                </td>
                <td style="width: 20%; padding: 3px;">
                    <div class="summary-box" style="border-color: #c7d2fe; background: #eef2ff;">
                        <div class="summary-title" style="color: #4338ca;">Amount Due</div>
                        <div class="summary-value" style="color: #4338ca;">Rs. {{ number_format($summary['total_amount_due'], 2) }}</div>
                    </div>
                </td>
                <td style="width: 20%; padding: 3px;">
                    <div class="summary-box" style="border-color: #a7f3d0; background: #ecfdf5;">
                        <div class="summary-title" style="color: #047857;">Amount Paid</div>
                        <div class="summary-value" style="color: #047857;">Rs. {{ number_format($summary['total_amount_paid'], 2) }}</div>
                    </div>
                </td>
                <td style="width: 20%; padding: 3px;">
                    <div class="summary-box" style="border-color: #fecdd3; background: #fff1f2;">
                        <div class="summary-title" style="color: #e11d48;">Net Balance</div>
                        <div class="summary-value" style="color: #e11d48;">Rs. {{ number_format($summary['total_balance'], 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        {{-- Standard Billing Ledger Table --}}
        <table class="data-table">
            <thead>
                <tr>
                    <th class="text-center">SR #</th>
                    <th>FLAT/SHOP</th>
                    <th>TENANT</th>
                    <th>BILLING TYPE</th>
                    <th class="text-right">PREV. UNPAID</th>
                    <th class="text-right">AMOUNT DUE</th>
                    <th class="text-right">AMOUNT PAID</th>
                    <th>PAYMENT METHOD</th>
                    <th>PAYMENT ACCOUNT</th>
                    <th>PAID AT</th>
                    <th class="text-right">BALANCE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($rows as $r)
                    <tr>
                        <td class="text-center" style="color: #94a3b8;">{{ $r['sr'] }}</td>
                        <td class="font-bold">{{ $r['unit_number'] }}</td>
                        <td>{{ $r['tenant_name'] }}</td>
                        @php
                            $pdfTypeCode = strtolower($r['type_label'] ?? '');
                            $pdfTypeColor = match(true) {
                                str_contains($pdfTypeCode, 'rent') => '#1d4ed8',
                                str_contains($pdfTypeCode, 'maint') => '#047857',
                                str_contains($pdfTypeCode, 'security') || str_contains($pdfTypeCode, 'deposit') => '#6b21a8',
                                str_contains($pdfTypeCode, 'extra') || str_contains($pdfTypeCode, 'fine') || str_contains($pdfTypeCode, 'other') => '#b45309',
                                default => '#475569',
                            };
                        @endphp
                        <td class="font-bold" style="color: {{ $pdfTypeColor }};">{{ $r['type_label'] }}</td>
                        <td class="text-right font-bold" style="color: #d97706;">{{ number_format($r['prev_unpaid'], 2) }}</td>
                        <td class="text-right font-bold">{{ number_format($r['amount_due'], 2) }}</td>
                        <td class="text-right font-bold" style="color: #059669;">{{ number_format($r['amount_paid'], 2) }}</td>
                        <td>{{ $r['payment_method'] }}</td>
                        <td>{{ $r['payment_account'] }}</td>
                        <td>{{ $r['paid_at'] }}</td>
                        <td class="text-right font-bold" style="color: {{ $r['balance'] > 0 ? '#dc2626' : '#059669' }};">
                            {{ number_format($r['balance'], 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="font-weight: 900; font-size: 12px; background: #cbd5e1; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a;">
                    <td colspan="4" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #0f172a;">Total ({{ $summary['total_records'] }} Records)</td>
                    <td class="text-right" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #d97706;">{{ number_format($summary['total_prev_unpaid'], 2) }}</td>
                    <td class="text-right" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #0f172a;">{{ number_format($summary['total_amount_due'], 2) }}</td>
                    <td class="text-right" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #059669;">{{ number_format($summary['total_amount_paid'], 2) }}</td>
                    <td colspan="3"></td>
                    <td class="text-right" style="padding: 10px 7px; font-weight: 900; font-size: 12px; color: #1d3461;">{{ number_format($summary['total_balance'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

</body>
</html>
