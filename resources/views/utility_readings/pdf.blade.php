<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Utility Meter Readings Report ({{ $selectedMonthName }})</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #1E293B;
            line-height: 1.4;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 12px;
            border-bottom: 2px solid #1D3461;
            padding-bottom: 10px;
        }

        .header h1 {
            font-size: 16px;
            color: #1D3461;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .header p.subtitle {
            font-size: 8px;
            color: #64748B;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 2px;
        }

        .header h2 {
            font-size: 13px;
            color: #1D4ED8;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 6px;
        }

        .summary-bar {
            margin-bottom: 10px;
            padding: 6px 8px;
            border: 1px solid #E2E8F0;
            background: #F8FAFC;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .summary-bar span {
            margin-right: 10px;
        }

        .tag {
            padding: 1px 5px;
            border-radius: 3px;
        }

        .tag-green {
            background: #DCFCE7;
            color: #15803D;
            border: 1px solid #86EFAC;
        }

        .tag-gray {
            background: #F1F5F9;
            color: #475569;
            border: 1px solid #CBD5E1;
        }

        .tag-red {
            background: #FEE2E2;
            color: #B91C1C;
            border: 1px solid #FCA5A5;
        }

        .tag-amber {
            background: #FEF3C7;
            color: #92400E;
            border: 1px solid #FCD34D;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
            border: 1px solid #94A3B8;
        }

        table.data-table th {
            background: #1D3461;
            color: white;
            padding: 6px 5px;
            font-size: 7.5px;
            text-transform: uppercase;
            font-weight: bold;
            text-align: left;
            border: 1px solid #475569;
        }

        table.data-table td {
            padding: 5px;
            border: 1px solid #CBD5E1;
            font-size: 7.5px;
            vertical-align: middle;
        }

        table.data-table tr:nth-child(even) td {
            background: #F9FBFF;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-mono {
            font-family: Courier, monospace;
        }

        .font-bold {
            font-weight: bold;
        }

        tfoot td {
            background: #F1F5F9;
            font-weight: bold;
            border: 1px solid #94A3B8;
        }

        .footer {
            margin-top: 18px;
            padding-top: 8px;
            border-top: 1px solid #E2E8F0;
            display: block;
            width: 100%;
            font-size: 8px;
            color: #475569;
        }

        .footer .sign {
            float: right;
            text-align: center;
            width: 180px;
        }

        .footer .sign .line {
            border-bottom: 1px solid #94A3B8;
            margin-bottom: 3px;
            height: 24px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Palladium Mall</h1>
        <p class="subtitle">Management &amp; Utility Operations</p>
        <h2>Utility Meter Readings Statement — {{ $selectedMonthName }}</h2>
    </div>

    <div class="summary-bar">
        <span>Meter Status:</span>
        <span class="tag tag-green">Active: {{ $activeMeters }}</span>
        <span class="tag tag-gray">Inactive: {{ $inactiveMeters }}</span>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <span>Breaker:</span>
        <span class="tag tag-green">ON: {{ $breakerOn }}</span>
        <span class="tag tag-red">OFF: {{ $breakerOff }}</span>
        &nbsp;&nbsp;|&nbsp;&nbsp;
        <span>Payment:</span>
        <span class="tag tag-green">Paid: {{ $paidCount }}</span>
        <span class="tag tag-red">Unpaid: {{ $unpaidCount }}</span>
        <span class="tag tag-amber">Pending: {{ $pendingCount }}</span>
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Flat / Shop</th>
                <th>Floor &amp; Block</th>
                <th>Meter Type</th>
                <th>Ref Number</th>
                <th>Consumer ID</th>
                <th class="text-center">Breaker</th>
                <th class="text-center">Meter Status</th>
                <th class="text-center">Bill Gen. Date</th>
                <th class="text-right">Prev Reading</th>
                <th class="text-right">Meter Reading</th>
                <th class="text-right">Units Consumed</th>
                <th class="text-center">Available</th>
                <th class="text-right">Amount (Rs.)</th>
                <th class="text-center">Status</th>
                <th class="text-center">Edited By</th>
            </tr>
        </thead>
        <tbody>
            @forelse($readings as $index => $row)
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="font-bold">{{ $row['unit_number'] }}</td>
                    <td>{{ $row['floor'] }}{{ $row['block'] ? ' • ' . $row['block'] : '' }}</td>
                    <td class="font-bold">{{ $row['meter_type_label'] }}</td>
                    <td class="font-mono">{{ $row['meter_ref_no'] }}</td>
                    <td class="font-mono">{{ $row['meter_consumer_id'] }}</td>
                    <td class="text-center font-bold">
                        @if(strtoupper($row['breaker_status'] ?? 'OFF') === 'ON')
                            <span class="tag tag-green">ON</span>
                        @else
                            <span class="tag tag-red">OFF</span>
                        @endif
                    </td>
                    <td class="text-center font-bold">
                        @if($row['is_active'])
                            <span class="tag tag-green">ACTIVE</span>
                        @else
                            <span class="tag tag-gray">INACTIVE</span>
                        @endif
                    </td>
                    <td class="text-center">{{ $row['bill_generate_date_label'] ?? '—' }}</td>
                    <td class="text-right font-mono">{{ number_format($row['previous_reading'], 2) }}</td>
                    <td class="text-right font-mono font-bold">{{ number_format($row['current_reading'], 2) }}</td>
                    <td class="text-right font-mono font-bold">{{ number_format($row['units_consumed'], 2) }}</td>
                    <td class="text-center">{{ $row['available'] ?: '—' }}</td>
                    <td class="text-right font-mono font-bold">Rs. {{ number_format($row['amount'], 2) }}</td>
                    <td class="text-center font-bold">
                        @if(strtolower($row['status']) === 'paid')
                            <span class="tag tag-green">PAID</span>
                        @elseif(strtolower($row['status']) === 'unpaid')
                            <span class="tag tag-red">UNPAID</span>
                        @else
                            <span class="tag tag-amber">PENDING</span>
                        @endif
                    </td>

                    <td class="text-center">{{ $row['edited_by'] ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="16" class="text-center" style="padding: 15px; color: #94A3B8;">
                        No utility readings found for {{ $selectedMonthName }}.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="10" class="text-right">Total Units Consumed:</td>
                <td class="text-right font-mono">{{ number_format($totalUnitsConsumed, 2) }}</td>
                <td></td>
                <td class="text-right font-mono">Rs. {{ number_format($totalBilled, 2) }}</td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>Printed On: {{ now()->format('d M Y, h:i A') }}</p>
        <p>Printed By: {{ auth()->user()->name ?? 'System' }}</p>
        <div class="sign">
            <div class="line"></div>
            <p class="font-bold">Authorized Officer Signature</p>
        </div>
    </div>

</body>

</html>