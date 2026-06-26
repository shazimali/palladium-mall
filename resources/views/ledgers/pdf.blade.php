<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #1E293B; line-height: 1.4; padding: 25px; }
        
        .header { margin-bottom: 20px; border-bottom: 2px solid #1D3461; padding-bottom: 12px; }
        .header h1 { font-size: 16px; color: #1D3461; font-weight: bold; margin-bottom: 4px; }
        .header p { font-size: 8.5px; color: #64748B; margin-top: 2px; }
        
        .summary-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .summary-td { width: 33.33%; padding: 0 10px 0 0; }
        .summary-td:last-child { padding-right: 0; }
        .summary-card { background: #F8FAFF; border: 1px solid #E2E8F0; padding: 10px 12px; border-radius: 6px; }
        .summary-card .label { font-size: 7.5px; color: #64748B; text-transform: uppercase; font-weight: bold; }
        .summary-card .value { font-size: 12px; font-weight: bold; color: #1D3461; margin-top: 4px; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th { background: #1D3461; color: white; padding: 8px 10px; font-size: 7.5px; text-transform: uppercase; font-weight: bold; text-align: left; }
        table.data-table td { padding: 6px 10px; border-bottom: 1px solid #E2E8F0; font-size: 8px; vertical-align: top; }
        table.data-table tr:nth-child(even) td { background: #F9FBFF; }
        
        .text-right { text-align: right; }
        .text-green { color: #10B981; font-weight: bold; }
        .text-red { color: #EF4444; font-weight: bold; }
        .font-mono { font-family: Courier, monospace; }
        .footer { position: fixed; bottom: 15px; left: 25px; right: 25px; border-top: 1px solid #E2E8F0; padding-top: 8px; text-align: center; font-size: 7.5px; color: #94A3B8; }
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

    {{-- Tenant Statement Summary & Table --}}
    @if($type === 'tenant')
        <table class="summary-table">
            <tr>
                <td class="summary-td">
                    <div class="summary-card" style="border-left: 3px solid #3B82F6;">
                        <div class="label">Total Billed / Charges</div>
                        <div class="value">Rs. {{ number_format($summary['total_invoiced'], 2) }}</div>
                    </div>
                </td>
                <td class="summary-td">
                    <div class="summary-card" style="border-left: 3px solid #10B981;">
                        <div class="label">Total Paid / Credits</div>
                        <div class="value" style="color: #10B981;">Rs. {{ number_format($summary['total_paid'], 2) }}</div>
                    </div>
                </td>
                <td class="summary-td">
                    <div class="summary-card" style="border-left: 3px solid #F59E0B;">
                        <div class="label">Balance Outstanding</div>
                        <div class="value" style="color: #F59E0B;">Rs. {{ number_format($summary['balance_due'], 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 40%;">Description</th>
                    <th style="width: 15%;">Ref / Voucher #</th>
                    <th style="width: 10%;" class="text-right">Debit (Charged)</th>
                    <th style="width: 10%;" class="text-right">Credit (Paid)</th>
                    <th style="width: 10%;" class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td class="font-mono">{{ $entry['date']->format('d M Y') }}</td>
                        <td>{{ $entry['description'] }}</td>
                        <td class="font-mono">{{ $entry['reference'] }}</td>
                        <td class="text-right text-red">{{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '—' }}</td>
                        <td class="text-right text-green">{{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '—' }}</td>
                        <td class="text-right font-mono" style="font-weight: bold;">{{ number_format($entry['running_balance'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- Owner Statement Summary & Table --}}
    @elseif($type === 'owner')
        <table class="summary-table">
            <tr>
                <td class="summary-td">
                    <div class="summary-card" style="border-left: 3px solid #3B82F6;">
                        <div class="label">Total Payouts (Debits)</div>
                        <div class="value">Rs. {{ number_format($summary['total_debit'], 2) }}</div>
                    </div>
                </td>
                <td class="summary-td">
                    <div class="summary-card" style="border-left: 3px solid #10B981;">
                        <div class="label">Total Deposits (Credits)</div>
                        <div class="value" style="color: #10B981;">Rs. {{ number_format($summary['total_credit'], 2) }}</div>
                    </div>
                </td>
                <td class="summary-td">
                    <div class="summary-card" style="border-left: 3px solid #6366F1;">
                        <div class="label">Net Balance</div>
                        <div class="value">Rs. {{ number_format($summary['net_balance'], 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Voucher #</th>
                    <th>Account</th>
                    <th>Reference</th>
                    <th>Notes</th>
                    <th class="text-right">Debit (Payout)</th>
                    <th class="text-right">Credit (Deposit)</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td class="font-mono">{{ $entry['date']->format('d M Y') }}</td>
                        <td class="font-mono">{{ $entry['voucher_no'] }}</td>
                        <td>{{ $entry['account'] }}</td>
                        <td>{{ $entry['reference'] }}</td>
                        <td>{{ $entry['notes'] }}</td>
                        <td class="text-right text-red">{{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '—' }}</td>
                        <td class="text-right text-green">{{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '—' }}</td>
                        <td class="text-right font-mono" style="font-weight: bold;">{{ number_format($entry['running_balance'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- Payment Account Statement Summary & Table --}}
    @elseif($type === 'account')
        <table class="summary-table">
            <tr>
                <td class="summary-td">
                    <div class="summary-card" style="border-left: 3px solid #10B981;">
                        <div class="label">Total Inflows (Debits)</div>
                        <div class="value" style="color: #10B981;">Rs. {{ number_format($summary['total_inflow'], 2) }}</div>
                    </div>
                </td>
                <td class="summary-td">
                    <div class="summary-card" style="border-left: 3px solid #3B82F6;">
                        <div class="label">Total Outflows (Credits)</div>
                        <div class="value">Rs. {{ number_format($summary['total_outflow'], 2) }}</div>
                    </div>
                </td>
                <td class="summary-td">
                    <div class="summary-card" style="border-left: 3px solid #6366F1;">
                        <div class="label">Net Balance</div>
                        <div class="value">Rs. {{ number_format($summary['net_balance'], 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Voucher #</th>
                    <th>Type</th>
                    <th>Description / Ref</th>
                    <th class="text-right">Debit (Inflow)</th>
                    <th class="text-right">Credit (Outflow)</th>
                    <th class="text-right">Balance</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td class="font-mono">{{ $entry['date']->format('d M Y') }}</td>
                        <td class="font-mono">{{ $entry['voucher_no'] }}</td>
                        <td>{{ $entry['type'] }}</td>
                        <td>{{ $entry['description'] }}</td>
                        <td class="text-right text-green">{{ $entry['debit'] > 0 ? number_format($entry['debit'], 2) : '—' }}</td>
                        <td class="text-right text-red">{{ $entry['credit'] > 0 ? number_format($entry['credit'], 2) : '—' }}</td>
                        <td class="text-right font-mono" style="font-weight: bold;">{{ number_format($entry['running_balance'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

    {{-- Expense Statement Summary & Table --}}
    @elseif($type === 'expense')
        <table class="summary-table" style="width: 33.33%;">
            <tr>
                <td>
                    <div class="summary-card" style="border-left: 3px solid #EF4444;">
                        <div class="label">Total Spent Category Expenses</div>
                        <div class="value" style="color: #EF4444;">Rs. {{ number_format($summary['total_amount'], 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Voucher #</th>
                    <th>Spent On / Notes</th>
                    <th>Payment Account</th>
                    <th>Reference</th>
                    <th class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $entry)
                    <tr>
                        <td class="font-mono">{{ $entry['date']->format('d M Y') }}</td>
                        <td class="font-mono">{{ $entry['voucher_no'] }}</td>
                        <td>{{ $entry['notes'] }}</td>
                        <td>{{ $entry['payment_account'] }}</td>
                        <td class="font-mono">{{ $entry['reference'] }}</td>
                        <td class="text-right text-red" style="font-weight: bold;">Rs. {{ number_format($entry['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Palladium Mall Management Software &bull; Confidential Financial Document
    </div>

</body>
</html>
