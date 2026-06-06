<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #1E293B;
        }

        .header {
            background: #1D3461;
            color: white;
            padding: 16px 20px;
            margin-bottom: 12px;
        }

        .header h1 { font-size: 16px; font-weight: bold; margin-bottom: 2px; }
        .header p  { font-size: 9px; opacity: 0.75; }

        .meta {
            display: flex;
            gap: 8px;
            padding-bottom: 12px;
        }

        .meta-box {
            flex: 1;
            background: #F8FAFF;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 8px 12px;
        }

        .meta-box .label { font-size: 8px; color: #64748B; margin-bottom: 2px; }
        .meta-box .value { font-size: 11px; font-weight: bold; color: #1D3461; }

        /* Summary boxes */
        .summary {
            display: flex;
            gap: 6px;
            margin-bottom: 12px;
        }

        .s-box {
            flex: 1;
            padding: 8px 10px;
            border-radius: 6px;
        }

        .s-box.total-due   { background: #FEE2E2; }
        .s-box.total-paid  { background: #D1FAE5; }
        .s-box.outstanding { background: #DBEAFE; }
        .s-box.rent        { background: #EEF2FF; }
        .s-box.utilities   { background: #FEF3C7; }
        .s-box.fines       { background: #FCE7F3; }

        .s-box .s-label { font-size: 7.5px; color: #64748B; }
        .s-box .s-value { font-size: 12px; font-weight: bold; margin-top: 2px; }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }

        thead tr { background: #1D3461; color: white; }

        thead th {
            padding: 7px 7px;
            text-align: left;
            font-size: 7.5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        tbody tr:nth-child(even) { background: #F8FAFF; }

        tbody td {
            padding: 5px 7px;
            border-bottom: 1px solid #E2E8F0;
            font-size: 8.5px;
        }

        tfoot tr { background: #E8F0FE; font-weight: bold; }

        tfoot td {
            padding: 6px 7px;
            font-size: 9px;
            border-top: 2px solid #1D3461;
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5px;
            font-weight: bold;
        }

        .badge-paid    { background: #D1FAE5; color: #059669; }
        .badge-unpaid  { background: #FEE2E2; color: #DC2626; }
        .badge-partial { background: #FEF3C7; color: #DC7609; }

        .type-rent        { background: #DBEAFE; color: #1D4ED8; }
        .type-fine        { background: #FEE2E2; color: #DC2626; }
        .type-electricity { background: #FEF3C7; color: #B45309; }
        .type-water       { background: #CFFAFE; color: #0E7490; }
        .type-gas         { background: #FFEDD5; color: #C2410C; }
        .type-maintenance { background: #EDE9FE; color: #7C3AED; }
        .type-other       { background: #F1F5F9; color: #475569; }

        .footer {
            margin-top: 14px;
            font-size: 7.5px;
            color: #94A3B8;
            text-align: center;
            border-top: 1px solid #E2E8F0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <div class="header">
        <h1>Palladium Mall Management System</h1>
        <p>
            {{ $label }} Report &bull;
            Period: {{ $period }} &bull;
            Generated: {{ now()->format('d M Y, H:i') }}
        </p>
    </div>

    {{-- Meta info row --}}
    <div class="meta">
        <div class="meta-box">
            <div class="label">Report Type</div>
            <div class="value">{{ $label }}</div>
        </div>
        <div class="meta-box">
            <div class="label">Period</div>
            <div class="value">{{ $period }}</div>
        </div>
        @if(!empty($filters['unit_id']))
            <div class="meta-box">
                <div class="label">Flat/Shop Filter</div>
                <div class="value">Applied</div>
            </div>
        @endif
        @if(!empty($filters['tenant_id']))
            <div class="meta-box">
                <div class="label">Tenant Filter</div>
                <div class="value">Applied</div>
            </div>
        @endif
        @if(!empty($filters['status']))
            <div class="meta-box">
                <div class="label">Status</div>
                <div class="value">{{ ucfirst($filters['status']) }}</div>
            </div>
        @endif
        <div class="meta-box">
            <div class="label">Total Records</div>
            <div class="value">{{ number_format($summary['count']) }}</div>
        </div>
    </div>

    {{-- Summary boxes --}}
    <div class="summary">
        <div class="s-box total-due">
            <div class="s-label">Total Due</div>
            <div class="s-value" style="color:#DC2626;">Rs. {{ number_format($summary['total_due'], 2) }}</div>
        </div>
        <div class="s-box total-paid">
            <div class="s-label">Total Collected</div>
            <div class="s-value" style="color:#059669;">Rs. {{ number_format($summary['total_paid'], 2) }}</div>
        </div>
        <div class="s-box outstanding">
            <div class="s-label">Outstanding</div>
            <div class="s-value" style="color:#1A56DB;">Rs. {{ number_format($summary['outstanding'], 2) }}</div>
        </div>
        <div class="s-box rent">
            <div class="s-label">🏠 Rent Collected</div>
            <div class="s-value" style="color:#3730A3;">Rs. {{ number_format($summary['rent_collected'], 2) }}</div>
        </div>
        <div class="s-box utilities">
            <div class="s-label">⚡ Utilities Paid</div>
            <div class="s-value" style="color:#92400E;">Rs. {{ number_format($summary['utilities_paid'], 2) }}</div>
        </div>
        <div class="s-box fines">
            <div class="s-label">⚠️ Fines Collected</div>
            <div class="s-value" style="color:#9D174D;">Rs. {{ number_format($summary['fines_collected'], 2) }}</div>
        </div>
    </div>

    {{-- Data Table --}}
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Month</th>
                <th>Date</th>
                <th>Flat/Shop</th>
                <th>Tenant</th>
                <th>Landlord</th>
                <th>Type</th>
                <th>Payment Method</th>
                <th>Payment Account</th>
                <th>Amount Due</th>
                <th>Amount Paid</th>
                <th>Balance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($entries as $i => $entry)
                <tr>
                    <td style="color:#94A3B8;">{{ $i + 1 }}</td>
                    <td style="font-weight:600;">{{ $entry['month']?->format('M Y') ?? '—' }}</td>
                    <td>{{ $entry['date']?->format('d M Y') ?? '—' }}</td>
                    <td style="font-weight:600;">{{ $entry['unit'] ?? '—' }}</td>
                    <td>{{ $entry['tenant'] ?? '—' }}</td>
                    <td>{{ $entry['landlord'] ?? '—' }}</td>
                    <td>
                        <span class="badge type-{{ $entry['type'] ?? 'other' }}">
                            {{ ucfirst($entry['type'] ?? '') }}
                        </span>
                    </td>
                    <td>{{ $entry['payment_method'] ?? '—' }}</td>
                    <td>{{ $entry['payment_account'] ?? '—' }}</td>
                    <td style="font-weight:600;">Rs. {{ number_format($entry['amount_due'], 2) }}</td>
                    <td style="color:#059669;font-weight:600;">Rs. {{ number_format($entry['amount_paid'], 2) }}</td>
                    <td style="font-weight:700;color:{{ $entry['balance'] > 0 ? '#DC2626' : '#059669' }};">
                        Rs. {{ number_format($entry['balance'], 2) }}
                    </td>
                    <td>
                        <span class="badge badge-{{ $entry['status'] ?? 'unpaid' }}">
                            {{ ucfirst($entry['status'] ?? '') }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" style="text-align:center;padding:16px;color:#94A3B8;">
                        No records found for the selected filters.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="9" style="color:#1D3461;">
                    Totals — {{ number_format($summary['count']) }} records
                </td>
                <td style="color:#1D3461;">Rs. {{ number_format($summary['total_due'], 2) }}</td>
                <td style="color:#059669;">Rs. {{ number_format($summary['total_paid'], 2) }}</td>
                <td style="color:{{ $summary['outstanding'] > 0 ? '#DC2626' : '#059669' }};">
                    Rs. {{ number_format($summary['outstanding'], 2) }}
                </td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Palladium Mall Management System &bull; {{ $label }} Report &bull;
        Printed on {{ now()->format('d M Y, H:i') }}
    </div>

</body>
</html>
