<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Party Receivables & Payables Summary</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1f2937; margin: 0; padding: 15px; }
        h1 { font-size: 14px; font-weight: 700; color: #111827; margin-bottom: 2px; }
        .subtitle { font-size: 8.5px; color: #6b7280; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th { background: #f3f4f6; font-size: 8.5px; font-weight: 700; text-transform: uppercase; color: #4b5563; padding: 6px; border: 1px solid #e5e7eb; }
        td { padding: 6px; border: 1px solid #e5e7eb; font-size: 9px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .totals-row { background: #f9fafb; font-weight: 700; }
        .green { color: #16a34a; font-weight: bold; }
        .red { color: #dc2626; font-weight: bold; }
        .summary-box { border: 1px solid #e5e7eb; border-radius: 4px; background: #f9fafb; padding: 8px 12px; margin-bottom: 15px; }
        .summary-title { font-size: 10px; font-weight: 700; color: #374151; margin-bottom: 4px; }
        .summary-line { font-size: 9px; color: #4b5563; margin-bottom: 2px; }
        .footer { margin-top: 20px; font-size: 8px; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>
    <h1>Party Receivables & Payables Summary</h1>
    <p class="subtitle">
        Generated: {{ $generatedAt->format('d M Y, h:i A') }}
        @if($search) &bull; Search: "{{ $search }}" @endif
        @if($dateFrom || $dateTo) &bull; Period: {{ $dateFrom ?? 'All Time' }} to {{ $dateTo ?? 'Present' }} @endif
    </p>

    {{-- Net Balances Overview Box --}}
    <div class="summary-box">
        <div class="summary-title">Overall Party Balances Summary</div>
        <div class="summary-line">Net Receivables (Owed by Parties to Mall): <span class="green">Rs. {{ number_format($totals['net_rec'], 2) }}</span></div>
        <div class="summary-line">Net Payables (Owed by Mall to Parties): <span class="red">Rs. {{ number_format($totals['net_pay'], 2) }}</span></div>
    </div>

    {{-- Main Table --}}
    <table>
        <thead>
            <tr>
                <th rowspan="2" style="text-align: left; vertical-align: bottom;">Party Name</th>
                <th colspan="3">Receivables (Owed to Mall)</th>
                <th colspan="3">Payables (Owed by Mall)</th>
            </tr>
            <tr>
                <th>Gross Due</th>
                <th>Received</th>
                <th style="color: #16a34a;">Net Outstanding</th>
                <th>Gross Due</th>
                <th>Paid</th>
                <th style="color: #dc2626;">Net Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    <td>
                        <strong>{{ $row['party']->name }}</strong>
                        @if($row['party']->phone)<br><span style="color:#6b7280; font-size:8px;">{{ $row['party']->phone }}</span>@endif
                    </td>
                    
                    {{-- Receivables --}}
                    <td class="text-right">
                        {{ $row['rec_due'] > 0 ? 'Rs. ' . number_format($row['rec_due'], 2) : '—' }}
                    </td>
                    <td class="text-right">
                        {{ $row['rec_paid'] > 0 ? 'Rs. ' . number_format($row['rec_paid'], 2) : '—' }}
                    </td>
                    <td class="text-right {{ $row['net_rec'] > 0 ? 'green' : ($row['net_rec'] < 0 ? 'blue' : '') }}">
                        {{ $row['net_rec'] != 0 ? 'Rs. ' . number_format($row['net_rec'], 2) : '—' }}
                    </td>
                    
                    {{-- Payables --}}
                    <td class="text-right">
                        {{ $row['pay_due'] > 0 ? 'Rs. ' . number_format($row['pay_due'], 2) : '—' }}
                    </td>
                    <td class="text-right">
                        {{ $row['pay_paid'] > 0 ? 'Rs. ' . number_format($row['pay_paid'], 2) : '—' }}
                    </td>
                    <td class="text-right {{ $row['net_pay'] > 0 ? 'red' : ($row['net_pay'] < 0 ? 'blue' : '') }}">
                        {{ $row['net_pay'] != 0 ? 'Rs. ' . number_format($row['net_pay'], 2) : '—' }}
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center" style="padding: 15px; color: #9ca3af;">No party records found.</td></tr>
            @endforelse
        </tbody>
        @if(count($rows) > 0)
        <tfoot>
            <tr class="totals-row">
                <td><strong>TOTALS</strong></td>
                <td class="text-right">Rs. {{ number_format($totals['rec_due'], 2) }}</td>
                <td class="text-right">Rs. {{ number_format($totals['rec_paid'], 2) }}</td>
                <td class="text-right green">Rs. {{ number_format($totals['net_rec'], 2) }}</td>
                <td class="text-right">Rs. {{ number_format($totals['pay_due'], 2) }}</td>
                <td class="text-right">Rs. {{ number_format($totals['pay_paid'], 2) }}</td>
                <td class="text-right red">Rs. {{ number_format($totals['net_pay'], 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

    <div class="footer">Palladium Mall Management System &bull; Party Receivables & Payables Summary &bull; {{ $generatedAt->format('d M Y') }}</div>
</body>
</html>
