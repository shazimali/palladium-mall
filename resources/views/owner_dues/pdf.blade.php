<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Owner Dues Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; margin: 0; padding: 20px; }
        h1 { font-size: 16px; font-weight: 700; color: #111827; margin-bottom: 2px; }
        .subtitle { font-size: 10px; color: #6b7280; margin-bottom: 16px; }
        .section-title { font-size: 11px; font-weight: 700; color: #374151; margin: 16px 0 6px; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        th { background: #f3f4f6; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; padding: 6px 8px; text-align: left; }
        td { padding: 7px 8px; border-bottom: 1px solid #f3f4f6; font-size: 10px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge-clear { background: #d1fae5; color: #065f46; padding: 2px 8px; border-radius: 12px; font-size: 9px; font-weight: 600; }
        .badge-pending { background: #ffedd5; color: #9a3412; padding: 2px 8px; border-radius: 12px; font-size: 9px; font-weight: 600; }
        .totals-row { background: #f9fafb; font-weight: 700; border-top: 2px solid #d1d5db; }
        .cards { display: table; width: 100%; margin-bottom: 14px; }
        .card { display: table-cell; width: 25%; padding: 10px 12px; border: 1px solid #e5e7eb; border-radius: 6px; background: #f9fafb; }
        .card-label { font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #9ca3af; }
        .card-value { font-size: 14px; font-weight: 800; margin-top: 4px; }
        .card-note { font-size: 8px; color: #9ca3af; margin-top: 2px; }
        .green { color: #16a34a; }
        .orange { color: #ea580c; }
        .blue { color: #2563eb; }
        .disposable-box { border: 1px solid #d1fae5; background: #f0fdf4; border-radius: 6px; padding: 10px 14px; margin-bottom: 14px; }
        .disposable-title { font-size: 10px; font-weight: 700; color: #065f46; margin-bottom: 4px; }
        .disposable-line { font-size: 10px; color: #374151; margin-bottom: 2px; }
        .disposable-total { font-size: 12px; font-weight: 800; border-top: 1px solid #d1fae5; margin-top: 4px; padding-top: 4px; }
        .footer { margin-top: 16px; font-size: 9px; color: #9ca3af; text-align: right; }
    </style>
</head>
<body>
    <h1>Palladium Mall — Owner Dues Report</h1>
    <p class="subtitle">Generated: {{ $generatedAt->format('d M Y, h:i A') }}</p>

    {{-- Summary Cards --}}
    <table class="cards" style="border-spacing: 6px;">
        <tr>
            <td class="card">
                <div class="card-label">Total Income</div>
                <div class="card-value blue">Rs. {{ number_format($totalIncome, 2) }}</div>
                <div class="card-note">Rent + Party collections</div>
            </td>
            <td class="card" style="width: 6px; border: none; background: none;"></td>
            <td class="card">
                <div class="card-label">Total Paid Out</div>
                <div class="card-value green">Rs. {{ number_format($totalOwnersPaid, 2) }}</div>
                <div class="card-note">Via Payment Vouchers</div>
            </td>
            <td class="card" style="width: 6px; border: none; background: none;"></td>
            <td class="card">
                <div class="card-label">Pending Dues</div>
                <div class="card-value orange">Rs. {{ number_format($totalOwnersPending, 2) }}</div>
                <div class="card-note">Still owed to owners</div>
            </td>
            <td class="card" style="width: 6px; border: none; background: none;"></td>
            <td class="card" style="{{ $disposableAmount >= 0 ? 'border-color:#d1fae5; background:#f0fdf4;' : 'border-color:#fee2e2; background:#fef2f2;' }}">
                <div class="card-label" style="{{ $disposableAmount >= 0 ? 'color:#065f46' : 'color:#b91c1c' }}">Disposable</div>
                <div class="card-value {{ $disposableAmount >= 0 ? 'green' : '' }}" style="{{ $disposableAmount < 0 ? 'color:#dc2626' : '' }}">Rs. {{ number_format($disposableAmount, 2) }}</div>
                <div class="card-note">After all dues</div>
            </td>
        </tr>
    </table>

    {{-- Disposable Breakdown --}}
    <div class="disposable-box">
        <div class="disposable-title">Disposable Amount Breakdown</div>
        <div class="disposable-line">Cash in Accounts: <strong>Rs. {{ number_format($totalCashBalance, 2) }}</strong></div>
        <div class="disposable-line">− Pending Owner Dues: <strong style="color:#ea580c;">Rs. {{ number_format($totalOwnersPending, 2) }}</strong></div>
        <div class="disposable-line">− Pending Landlord Dues: <strong style="color:#dc2626;">Rs. {{ number_format($pendingLandlordDues, 2) }}</strong></div>
        <div class="disposable-total {{ $disposableAmount >= 0 ? 'green' : '' }}" style="{{ $disposableAmount < 0 ? 'color:#dc2626' : '' }}">
            = Disposable Amount: Rs. {{ number_format($disposableAmount, 2) }}
        </div>
    </div>

    {{-- Per-Owner Table --}}
    <div class="section-title">Owner Share Summary</div>
    <table>
        <thead>
            <tr>
                <th>Owner / Partner</th>
                <th class="text-center">Share %</th>
                <th class="text-right">Total Income Due</th>
                <th class="text-right">Total Paid Out</th>
                <th class="text-right">Pending Balance</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ownerRows as $row)
                <tr>
                    <td>
                        <strong>{{ $row['owner']->name }}</strong>
                        @if($row['owner']->phone)<br><span style="color:#9ca3af;">{{ $row['owner']->phone }}</span>@endif
                    </td>
                    <td class="text-center">{{ number_format($row['percentage'], 2) }}%</td>
                    <td class="text-right">Rs. {{ number_format($row['due'], 2) }}</td>
                    <td class="text-right green">Rs. {{ number_format($row['paid'], 2) }}</td>
                    <td class="text-right {{ $row['pending'] > 0 ? 'orange' : '' }}">Rs. {{ number_format($row['pending'], 2) }}</td>
                    <td class="text-center">
                        @if($row['pending'] <= 0)
                            <span class="badge-clear">Clear</span>
                        @else
                            <span class="badge-pending">Pending</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center; color:#9ca3af; padding: 16px;">No owners found.</td></tr>
            @endforelse
        </tbody>
        @if(count($ownerRows) > 0)
        <tfoot>
            <tr class="totals-row">
                <td><strong>TOTALS</strong></td>
                <td class="text-center">{{ number_format(collect($ownerRows)->sum('percentage'), 2) }}%</td>
                <td class="text-right"><strong>Rs. {{ number_format($totalOwnersDue, 2) }}</strong></td>
                <td class="text-right green"><strong>Rs. {{ number_format($totalOwnersPaid, 2) }}</strong></td>
                <td class="text-right {{ $totalOwnersPending > 0 ? 'orange' : '' }}"><strong>Rs. {{ number_format($totalOwnersPending, 2) }}</strong></td>
                <td></td>
            </tr>
        </tfoot>
        @endif
    </table>

    {{-- Income Sources --}}
    <div class="section-title">Income Source Breakdown</div>
    <table>
        <thead>
            <tr>
                <th>Source</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Tenant Rent (Receiving Vouchers)</td>
                <td class="text-right">Rs. {{ number_format($totalTenantIncome, 2) }}</td>
            </tr>
            <tr>
                <td>Party / External Income (General Receiving Vouchers)</td>
                <td class="text-right">Rs. {{ number_format($totalPartyIncome, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="totals-row">
                <td><strong>Total Income</strong></td>
                <td class="text-right blue"><strong>Rs. {{ number_format($totalIncome, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">Palladium Mall Management System &bull; Owner Dues Report &bull; {{ $generatedAt->format('d M Y') }}</div>
</body>
</html>
