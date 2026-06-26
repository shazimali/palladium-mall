<table>
    <thead>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 14px; text-align: center;">PALLADIUM MALL MANAGEMENT SYSTEM</th>
        </tr>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 12px; text-align: center;">PROFIT & LOSS STATEMENT</th>
        </tr>
        <tr>
            <th colspan="2" style="font-style: italic; text-align: center;">Period: {{ \Carbon\Carbon::parse($date_from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($date_to)->format('d M Y') }}</th>
        </tr>
        <tr>
            <th colspan="2"></th>
        </tr>
    </thead>
</table>

<table>
    <thead>
        <tr style="background-color: #1D3461; color: #FFFFFF;">
            <th style="font-weight: bold; width: 300px;">Summary Metrics</th>
            <th style="font-weight: bold; width: 150px; text-align: right;">Amount (Rs.)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Total Income (A)</td>
            <td style="text-align: right; font-weight: bold; color: #047857;">{{ $totalIncome }}</td>
        </tr>
        <tr>
            <td>Total Expenses (B)</td>
            <td style="text-align: right; font-weight: bold; color: #B91C1C;">{{ $totalExpenses }}</td>
        </tr>
        <tr style="background-color: #F1F5F9;">
            <td style="font-weight: bold;">Net Profit / Loss (A - B)</td>
            <td style="text-align: right; font-weight: bold; color: {{ $netProfitLoss >= 0 ? '#1D4ED8' : '#B91C1C' }};">{{ $netProfitLoss }}</td>
        </tr>
    </tbody>
</table>

<table>
    <tr>
        <th colspan="2"></th>
    </tr>
</table>

<table>
    <thead>
        <tr style="background-color: #1D3461; color: #FFFFFF;">
            <th style="font-weight: bold; width: 300px;">Income Breakdown</th>
            <th style="font-weight: bold; width: 150px; text-align: right;">Amount (Rs.)</th>
        </tr>
    </thead>
    <tbody>
        @foreach($incomeBreakdown as $type => $amount)
            <tr>
                <td>
                    @switch($type)
                        @case('rent')
                            Rent Collected
                            @break
                        @case('maintenance')
                            Maintenance Charges
                            @break
                        @case('fine')
                            Fines Collected
                            @break
                        @default
                            Utility: {{ ucfirst($type) }}
                    @endswitch
                </td>
                <td style="text-align: right;">{{ $amount }}</td>
            </tr>
        @endforeach
        <tr>
            <td style="font-weight: bold;">Miscellaneous Receipts (Vouchers)</td>
            <td style="text-align: right; font-weight: bold;">{{ $miscIncome }}</td>
        </tr>
        <tr style="background-color: #F1F5F9;">
            <td style="font-weight: bold;">Total Revenue</td>
            <td style="text-align: right; font-weight: bold; color: #047857;">{{ $totalIncome }}</td>
        </tr>
    </tbody>
</table>

<table>
    <tr>
        <th colspan="2"></th>
    </tr>
</table>

<table>
    <thead>
        <tr style="background-color: #1D3461; color: #FFFFFF;">
            <th style="font-weight: bold; width: 300px;">Expense Breakdown</th>
            <th style="font-weight: bold; width: 150px; text-align: right;">Amount (Rs.)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($expensesByHead as $expense)
            <tr>
                <td>{{ $expense['name'] }}</td>
                <td style="text-align: right;">{{ $expense['amount'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" style="text-align: center; font-style: italic; color: #64748B;">No operating expenses recorded for this period.</td>
            </tr>
        @endforelse
        <tr style="background-color: #F1F5F9;">
            <td style="font-weight: bold;">Total Operating Expenses</td>
            <td style="text-align: right; font-weight: bold; color: #B91C1C;">{{ $totalExpenses }}</td>
        </tr>
    </tbody>
</table>

<table>
    <tr>
        <th colspan="3"></th>
    </tr>
</table>

<table>
    <thead>
        <tr style="background-color: #1D3461; color: #FFFFFF;">
            <th style="font-weight: bold; width: 250px;">Partner Name</th>
            <th style="font-weight: bold; width: 120px; text-align: center;">Partnership Share (%)</th>
            <th style="font-weight: bold; width: 150px; text-align: right;">Earning / Loss Split (Rs.)</th>
        </tr>
    </thead>
    <tbody>
        @forelse($distribution as $row)
            <tr>
                <td style="font-weight: bold;">{{ $row['name'] }}</td>
                <td style="text-align: center;">{{ $row['percentage'] }}%</td>
                <td style="text-align: right; font-weight: bold; color: {{ $row['share'] >= 0 ? '#047857' : '#B91C1C' }};">{{ $row['share'] }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" style="text-align: center; font-style: italic; color: #64748B;">No partners registered.</td>
            </tr>
        @endforelse
        <tr style="background-color: #F1F5F9; font-weight: bold;">
            <td>Totals</td>
            <td style="text-align: center;">{{ $totalOwnerSharePct }}%</td>
            <td style="text-align: right; color: {{ $netProfitLoss >= 0 ? '#047857' : '#B91C1C' }};">{{ $netProfitLoss }}</td>
        </tr>
    </tbody>
</table>
