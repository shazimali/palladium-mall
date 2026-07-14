<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #1E293B; line-height: 1.35; padding: 20px; }
        
        .header { margin-bottom: 15px; border-bottom: 2px solid #1D3461; padding-bottom: 8px; }
        .header h1 { font-size: 14px; color: #1D3461; font-weight: bold; margin-bottom: 3px; }
        .header p { font-size: 8px; color: #64748B; margin-top: 1px; }
        
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .summary-td { width: 20%; padding-right: 8px; }
        .summary-td:last-child { padding-right: 0; }
        .summary-card { background: #F8FAFF; border: 1px solid #E2E8F0; padding: 8px 10px; border-radius: 4px; }
        .summary-card .label { font-size: 6.5px; color: #64748B; text-transform: uppercase; font-weight: bold; }
        .summary-card .value { font-size: 10px; font-weight: bold; color: #1D3461; margin-top: 3px; }
        
        .section-title { font-size: 10px; font-weight: bold; color: #1D3461; margin: 15px 0 6px 0; border-bottom: 1px solid #CBD5E1; padding-bottom: 3px; text-transform: uppercase; }
        .section-desc { font-size: 7.5px; color: #64748B; margin-bottom: 8px; }

        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th { background: #1D3461; color: white; padding: 6px 8px; font-size: 7px; text-transform: uppercase; font-weight: bold; text-align: left; }
        table.data-table td { padding: 5px 8px; border-bottom: 1px solid #E2E8F0; font-size: 7.5px; vertical-align: middle; }
        table.data-table tr:nth-child(even) td { background: #F9FBFF; }
        table.data-table tfoot td { background: #F1F5F9; font-weight: bold; border-top: 2px solid #CBD5E1; }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-green { color: #10B981; font-weight: bold; }
        .text-red { color: #EF4444; font-weight: bold; }
        .font-mono { font-family: Courier, monospace; }
        .footer { position: fixed; bottom: 15px; left: 20px; right: 20px; border-top: 1px solid #E2E8F0; padding-top: 6px; text-align: center; font-size: 7px; color: #94A3B8; }
    </style>
</head>
<body>

    {{-- Report Header --}}
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>
            <strong>Palladium Mall Management System</strong> &bull; 
            Statement Period: {{ $dateFrom ? Carbon\Carbon::parse($dateFrom)->format('d M Y') : 'Beginning' }} to {{ $dateTo ? Carbon\Carbon::parse($dateTo)->format('d M Y') : 'Present' }}
        </p>
        <p>Generated on: {{ now()->format('d M Y h:i A') }}</p>
    </div>

    {{-- Summary Widgets Cards --}}
    <table class="summary-table">
        <tr>
            <td class="summary-td">
                <div class="summary-card" style="border-left: 3px solid #1D3461;">
                    <div class="label">Cash Balance</div>
                    <div class="value">Rs. {{ number_format($totalCashBalance, 2) }}</div>
                </div>
            </td>
            <td class="summary-td">
                <div class="summary-card" style="border-left: 3px solid #3B82F6;">
                    <div class="label">Disposable Cash</div>
                    <div class="value" style="color: #3B82F6;">Rs. {{ number_format($disposableAmount, 2) }}</div>
                </div>
            </td>
            <td class="summary-td">
                <div class="summary-card" style="border-left: 3px solid #EF4444;">
                    <div class="label">Owed to Partners</div>
                    <div class="value" style="color: #EF4444;">Rs. {{ number_format($totalOwnersPending, 2) }}</div>
                </div>
            </td>
            <td class="summary-td">
                <div class="summary-card" style="border-left: 3px solid #F59E0B;">
                    <div class="label">Owed to Parties</div>
                    <div class="value" style="color: #F59E0B;">Rs. {{ number_format($partyTotals['net_pay'], 2) }}</div>
                </div>
            </td>
            <td class="summary-td">
                <div class="summary-card" style="border-left: 3px solid #10B981;">
                    <div class="label">Owed by Parties</div>
                    <div class="value" style="color: #10B981;">Rs. {{ number_format($partyTotals['net_rec'], 2) }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- SECTION 1: Partner Dues --}}
    <div class="section-title">Section 1: Managing Owners (Partners) Accounts</div>
    <div class="section-desc">Summary of collected earnings, payouts, and outstanding balances due to partners.</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 30%;">Partner Name</th>
                <th style="width: 15%; text-align: center;">Share Pct</th>
                <th style="width: 18%;" class="text-right">Total Earned</th>
                <th style="width: 18%;" class="text-right">Total Paid</th>
                <th style="width: 19%;" class="text-right">Net Pending Dues</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ownerRows as $row)
                <tr>
                    <td style="font-weight: bold;">{{ $row['owner']->name }}</td>
                    <td class="text-center font-mono">{{ number_format($row['percentage'], 2) }}%</td>
                    <td class="text-right">Rs. {{ number_format($row['due'], 2) }}</td>
                    <td class="text-right text-red">Rs. {{ number_format($row['paid'], 2) }}</td>
                    <td class="text-right font-mono" style="font-weight: bold;">Rs. {{ number_format($row['pending'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total Stakeholders Dues</td>
                <td></td>
                <td class="text-right">Rs. {{ number_format($totalOwnersDue, 2) }}</td>
                <td class="text-right">Rs. {{ number_format($totalOwnersPaid, 2) }}</td>
                <td class="text-right font-mono">Rs. {{ number_format($totalOwnersPending, 2) }}</td>
            </tr>
        </tfoot>
    </table>

    {{-- SECTION 2: Party Heads --}}
    <div class="section-title" style="page-break-before: auto;">Section 2: Party Heads Ledger Summary</div>
    <div class="section-desc">Outstanding balances owed by third parties (Receivables) and to third parties (Payables).</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 28%;" rowspan="2">Party Head Name</th>
                <th style="width: 36%; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.2);" colspan="3">Receivables (Owed to Mall)</th>
                <th style="width: 36%; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.2);" colspan="3">Payables (Owed by Mall)</th>
            </tr>
            <tr>
                <th class="text-right">Billed</th>
                <th class="text-right">Collected</th>
                <th class="text-right">Net Receivable</th>
                <th class="text-right">Owed</th>
                <th class="text-right">Paid</th>
                <th class="text-right">Net Payable</th>
            </tr>
        </thead>
        <tbody>
            @foreach($partyRows as $row)
                <tr>
                    <td style="font-weight: bold;">{{ $row['party']->name }}</td>
                    <td class="text-right">Rs. {{ number_format($row['rec_due'], 2) }}</td>
                    <td class="text-right text-green">Rs. {{ number_format($row['rec_paid'], 2) }}</td>
                    <td class="text-right font-mono" style="font-weight: bold;">
                        {{ $row['net_rec'] > 0.01 ? 'Rs. ' . number_format($row['net_rec'], 2) : '—' }}
                    </td>
                    <td class="text-right">Rs. {{ number_format($row['pay_due'], 2) }}</td>
                    <td class="text-right text-red">Rs. {{ number_format($row['pay_paid'], 2) }}</td>
                    <td class="text-right font-mono" style="font-weight: bold;">
                        {{ $row['net_pay'] > 0.01 ? 'Rs. ' . number_format($row['net_pay'], 2) : '—' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>Total Party Balances</td>
                <td class="text-right">Rs. {{ number_format($partyTotals['rec_due'], 2) }}</td>
                <td class="text-right">Rs. {{ number_format($partyTotals['rec_paid'], 2) }}</td>
                <td class="text-right font-mono">Rs. {{ number_format($partyTotals['net_rec'], 2) }}</td>
                <td class="text-right">Rs. {{ number_format($partyTotals['pay_due'], 2) }}</td>
                <td class="text-right">Rs. {{ number_format($partyTotals['pay_paid'], 2) }}</td>
                <td class="text-right font-mono">Rs. {{ number_format($partyTotals['net_pay'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Palladium Mall Summary Report &bull; Page 1 of 1
    </div>

</body>
</html>
