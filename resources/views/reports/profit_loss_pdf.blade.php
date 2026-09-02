<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Profit & Loss Statement</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1E293B;
            line-height: 1.4;
        }

        .header {
            background: #1D3461;
            color: white;
            padding: 16px 20px;
            margin-bottom: 15px;
        }

        .header h1 { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
        .header p  { font-size: 9px; opacity: 0.85; }

        .meta {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-spacing: 6px;
        }

        .meta-cell {
            display: table-cell;
            background: #F8FAFF;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 8px 12px;
            width: 50%;
        }

        .meta-cell .label { font-size: 8px; color: #64748B; text-transform: uppercase; margin-bottom: 2px; }
        .meta-cell .value { font-size: 11px; font-weight: bold; color: #1D3461; }

        /* Summary Boxes */
        .summary-container {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-spacing: 6px;
        }

        .summary-card {
            display: table-cell;
            padding: 12px 14px;
            border-radius: 6px;
            width: 33.33%;
            border: 1px solid #E2E8F0;
        }

        .summary-card.income { background: #ECFDF5; border-color: #A7F3D0; }
        .summary-card.expense { background: #FEF2F2; border-color: #FCA5A5; }
        .summary-card.profit { background: #EFF6FF; border-color: #BFDBFE; }

        .summary-card .s-label { font-size: 8px; color: #64748B; text-transform: uppercase; font-weight: bold; }
        .summary-card .s-value { font-size: 14px; font-weight: bold; margin-top: 4px; }
        .summary-card.income .s-value { color: #047857; }
        .summary-card.expense .s-value { color: #B91C1C; }
        .summary-card.profit .s-value { color: #1D4ED8; }

        /* Sections */
        .section-title {
            font-size: 11px;
            font-weight: bold;
            color: #1D3461;
            margin-bottom: 6px;
            text-transform: uppercase;
            border-bottom: 1.5px solid #1D3461;
            padding-bottom: 3px;
        }

        .row-container {
            margin-bottom: 20px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        thead tr { background: #1D3461; color: white; }

        thead th {
            padding: 6px 8px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) { background: #F8FAFF; }

        tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 8.5px;
        }

        tfoot tr { background: #F1F5F9; font-weight: bold; }

        tfoot td {
            padding: 7px 8px;
            font-size: 9px;
            border-top: 1.5px solid #1D3461;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }

        /* Alerts */
        .alert {
            background: #FFF7ED;
            border: 1px solid #FED7AA;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 15px;
            color: #C2410C;
            font-size: 8.5px;
        }
        .alert-bold { font-weight: bold; }

        .footer {
            margin-top: 30px;
            font-size: 8px;
            color: #94A3B8;
            text-align: center;
            border-top: 1px solid #E2E8F0;
            padding-top: 10px;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h1>Palladium Mall Management System</h1>
        <p>
            Profit & Loss Statement &bull;
            Generated: {{ now()->format('d M Y, H:i') }}
        </p>
    </div>

    {{-- Meta info row --}}
    <div class="meta">
        <div class="meta-cell">
            <div class="label">Statement Period</div>
            <div class="value">
                {{ \Carbon\Carbon::parse($date_from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($date_to)->format('d M Y') }}
            </div>
        </div>
        <div class="meta-cell">
            <div class="label">Report Type</div>
            <div class="value">Managing Partners Profit & Loss Distribution</div>
        </div>
    </div>

    {{-- Summary boxes --}}
    <div class="summary-container">
        <div class="summary-card income" style="width: 25%;">
            <div class="s-label">Collected Income</div>
            <div class="s-value">Rs. {{ number_format($totalIncome, 2) }}</div>
        </div>
        <div class="summary-card" style="width: 25%; background: #FFFBEB; border: 1px solid #FCD34D;">
            <div class="s-label">Unpaid / Outstanding</div>
            <div class="s-value" style="color: #D97706;">Rs. {{ number_format($totalUnpaidIncome ?? 0, 2) }}</div>
        </div>
        <div class="summary-card expense" style="width: 25%;">
            <div class="s-label">Total Expenses</div>
            <div class="s-value">Rs. {{ number_format($totalExpenses, 2) }}</div>
        </div>
        <div class="summary-card profit" style="width: 25%;">
            <div class="s-label">Net Profit / Loss</div>
            <div class="s-value" style="color: {{ $netProfitLoss >= 0 ? '#1D4ED8' : '#B91C1C' }}">
                Rs. {{ number_format($netProfitLoss, 2) }}
            </div>
        </div>
    </div>

    {{-- Income Breakdown --}}
    <div class="row-container">
        <div class="section-title">Income Breakdown</div>
        <table>
            <thead>
                <tr>
                    <th>Revenue Category</th>
                    <th class="text-right">Prev. Unpaid (Rs.)</th>
                    <th class="text-right">Billed (Rs.)</th>
                    <th class="text-right">Collected (Rs.)</th>
                    <th class="text-right">Total Outstanding (Rs.)</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($incomeDetailed))
                    @foreach($incomeDetailed as $type => $item)
                        @if(($item['prev_unpaid'] ?? 0) > 0 || ($item['billed'] ?? 0) > 0 || ($item['collected'] ?? 0) > 0)
                            <tr>
                                <td>{{ str_replace(['🏠 ', '🛠️ ', '💵 ', '📑 '], '', $item['label']) }}</td>
                                <td class="text-right font-medium" style="color: #b45309;">{{ ($item['prev_unpaid'] ?? 0) > 0 ? number_format($item['prev_unpaid'], 2) : '—' }}</td>
                                <td class="text-right">{{ number_format($item['billed'], 2) }}</td>
                                <td class="text-right font-medium" style="color: #047857;">{{ number_format($item['collected'], 2) }}</td>
                                <td class="text-right font-medium" style="color: #dc2626;">{{ number_format($item['unpaid'], 2) }}</td>
                            </tr>
                        @endif
                    @endforeach
                @else
                    @foreach($incomeBreakdown as $type => $amount)
                        @if($amount > 0 || in_array($type, ['rent_pm_mall', 'maint_pm_mall']))
                            <tr>
                                <td>{{ ucfirst(str_replace('_', ' ', $type)) }}</td>
                                <td class="text-right" style="color: #b45309;">—</td>
                                <td class="text-right">—</td>
                                <td class="text-right font-medium" style="color: #047857;">{{ number_format($amount, 2) }}</td>
                                <td class="text-right" style="color: #dc2626;">—</td>
                            </tr>
                        @endif
                    @endforeach
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td>Total Revenue:</td>
                    <td class="text-right" style="color: #b45309;">Rs. {{ number_format($totalPrevUnpaid ?? 0, 2) }}</td>
                    <td class="text-right">Rs. {{ number_format($totalBilledIncome ?? $totalIncome, 2) }}</td>
                    <td class="text-right" style="color: #047857;">Rs. {{ number_format($totalIncome, 2) }}</td>
                    <td class="text-right" style="color: #dc2626;">Rs. {{ number_format($totalUnpaidIncome ?? 0, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Expense Breakdown --}}
    <div class="row-container">
        <div class="section-title">Expense Breakdown</div>
        @if(empty($expensesByHead))
            <div class="text-center" style="padding: 15px; color: #64748B; border: 1px dashed #E2E8F0; border-radius: 6px;">
                No operating expenses recorded for this period.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Expense Category (Head)</th>
                        <th class="text-right">Spent Amount (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expensesByHead as $expense)
                        <tr>
                            <td>{{ $expense['name'] }}</td>
                            <td class="text-right font-medium">{{ number_format($expense['amount'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Total Operating Expenses:</td>
                        <td class="text-right" style="color: #B91C1C;">Rs. {{ number_format($totalExpenses, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

    {{-- Partners Distribution --}}
    <div class="row-container">
        <div class="section-title">Managing Partners Earnings Distribution</div>

        @if(abs($totalOwnerSharePct - 100.00) > 0.01)
            <div class="alert">
                <span class="alert-bold">⚠️ Warning:</span> The active owners partnership percentages sum is <strong>{{ number_format($totalOwnerSharePct, 2) }}%</strong>, not 100.00%. Correct calculations require partnership shares to distribute fully to 100%. Adjust shares in Owners Profile settings.
            </div>
        @endif

        @if(empty($distribution))
            <div class="text-center" style="padding: 15px; color: #64748B; border: 1px dashed #E2E8F0; border-radius: 6px;">
                No partners registered. Add mall partners in Owners Profile to calculate profit splits.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Partner Name</th>
                        <th>Partnership Share (%)</th>
                        <th class="text-right">Earning / Loss Split (Rs.)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($distribution as $row)
                        <tr>
                            <td style="font-weight: bold;">{{ $row['name'] }}</td>
                            <td style="color: #1D4ED8;">{{ number_format($row['percentage'], 2) }}%</td>
                            <td class="text-right font-bold" style="color: {{ $row['share'] >= 0 ? '#047857' : '#B91C1C' }}">
                                Rs. {{ number_format($row['share'], 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td>Totals</td>
                        <td style="color: #1D4ED8;">{{ number_format($totalOwnerSharePct, 2) }}%</td>
                        <td class="text-right" style="color: {{ $netProfitLoss >= 0 ? '#047857' : '#B91C1C' }}">
                            Rs. {{ number_format($netProfitLoss, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        @endif
    </div>

    <div class="footer">
        Palladium Mall Management System &bull; Profit & Loss Report &bull;
        Printed on {{ now()->format('d M Y, H:i') }}
    </div>

</body>
</html>
