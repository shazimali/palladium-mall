<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1E293B;
        }

        .header {
            background: #0B1C3D;
            color: white;
            padding: 16px 20px;
            margin-bottom: 16px;
        }

        .header h1 {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 2px;
        }

        .header p {
            font-size: 10px;
            opacity: 0.75;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            padding: 0 0 12px 0;
        }

        .meta-box {
            background: #F8FAFF;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 8px 12px;
            width: 30%;
        }

        .meta-box .label {
            font-size: 8px;
            color: #64748B;
            margin-bottom: 2px;
        }

        .meta-box .value {
            font-size: 11px;
            font-weight: bold;
            color: #0B1C3D;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }

        thead tr {
            background: #0B1C3D;
            color: white;
        }

        thead th {
            padding: 7px 8px;
            text-align: left;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) {
            background: #F8FAFF;
        }

        tbody td {
            padding: 6px 8px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 9px;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-paid {
            background: #D1FAE5;
            color: #059669;
        }

        .badge-unpaid {
            background: #FEE2E2;
            color: #DC2626;
        }

        .badge-partial {
            background: #FEF3C7;
            color: #DC7609;
        }

        .summary {
            margin-top: 16px;
            display: flex;
            gap: 8px;
        }

        .summary-box {
            flex: 1;
            padding: 10px 12px;
            border-radius: 6px;
        }

        .summary-box.due {
            background: #FEE2E2;
        }

        .summary-box.paid {
            background: #D1FAE5;
        }

        .summary-box.bal {
            background: #DBEAFE;
        }

        .summary-box .s-label {
            font-size: 8px;
            color: #64748B;
        }

        .summary-box .s-value {
            font-size: 13px;
            font-weight: bold;
            margin-top: 2px;
        }

        .footer {
            margin-top: 16px;
            font-size: 8px;
            color: #94A3B8;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1>Palladium Mall Management System</h1>
        <p>Ledger Statement — {{ $subjectName }} — Generated {{ now()->format('d M Y') }}</p>
    </div>

    <div class="meta">
        <div class="meta-box">
            <div class="label">{{ $scopeType === 'tenant' ? 'Tenant' : 'Unit' }}</div>
            <div class="value">{{ $subjectName }}</div>
        </div>
        @if($scopeType === 'tenant' && isset($unitNumber))
            <div class="meta-box">
                <div class="label">Unit</div>
                <div class="value">{{ $unitNumber }}</div>
            </div>
        @endif
        <div class="meta-box">
            <div class="label">Period</div>
            <div class="value">{{ $period }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Date</th>
                <th>Month</th>
                <th>Description</th>
                <th>Type</th>
                <th>Amount Due</th>
                <th>Amount Paid</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $i => $entry)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $entry['date']?->format('d M Y') ?? '—' }}</td>
                    <td>{{ $entry['month']?->format('M Y') ?? '—' }}</td>
                    <td>{{ $entry['description'] }}</td>
                    <td>{{ ucfirst($entry['type']) }}</td>
                    <td>Rs. {{ number_format($entry['amount_due'], 2) }}</td>
                    <td>Rs. {{ number_format($entry['amount_paid'], 2) }}</td>
                    <td>Rs. {{ number_format($entry['balance'], 2) }}</td>
                    <td>
                        <span class="badge badge-{{ $entry['status'] }}">
                            {{ ucfirst($entry['status']) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:16px;color:#94A3B8;">
                        No entries found for the selected period.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <div class="summary-box due">
            <div class="s-label">Total Due</div>
            <div class="s-value" style="color:#DC2626;">Rs. {{ number_format($summary['total_due'], 2) }}</div>
        </div>
        <div class="summary-box paid">
            <div class="s-label">Total Paid</div>
            <div class="s-value" style="color:#059669;">Rs. {{ number_format($summary['total_paid'], 2) }}</div>
        </div>
        <div class="summary-box bal">
            <div class="s-label">Outstanding Balance</div>
            <div class="s-value" style="color:#1A56DB;">Rs. {{ number_format($summary['outstanding'], 2) }}</div>
        </div>
    </div>

    <div class="footer">
        Palladium Mall Management System &bull; Printed on {{ now()->format('d M Y H:i') }}
    </div>

</body>

</html>