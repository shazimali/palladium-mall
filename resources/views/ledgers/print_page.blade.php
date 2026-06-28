<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} — Palladium Mall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 13px; }
        body { font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif; color: #111827; background: #fff; padding: 32px 40px 48px; line-height: 1.5; }
        .pm-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid #465FFF; padding-bottom: 14px; margin-bottom: 18px; }
        .pm-header-left { display: flex; align-items: center; gap: 12px; }
        .pm-logo-icon { width: 36px; height: 36px; background: #465FFF; border-radius: 8px; display: flex; align-items: flex-end; justify-content: center; gap: 3px; padding: 6px 6px 5px; }
        .pm-logo-icon span { display: block; background: #fff; border-radius: 2px; width: 5px; }
        .pm-logo-icon span:nth-child(1) { height: 100%; }
        .pm-logo-icon span:nth-child(2) { height: 70%; opacity: .9; }
        .pm-logo-icon span:nth-child(3) { height: 82%; opacity: .7; }
        .pm-name { font-size: 1.25rem; font-weight: 800; color: #111827; letter-spacing: -.3px; }
        .pm-header-right { text-align: right; }
        .pm-header-right .doc-title { font-size: 1rem; font-weight: 700; color: #111827; }
        .pm-header-right .doc-date { font-size: .78rem; color: #6b7280; margin-top: 2px; }
        .filters-row { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
        .filter-chip { display: inline-flex; align-items: center; gap: 4px; background: #f3f4f6; border: 1px solid #e5e7eb; border-radius: 999px; padding: 3px 10px; font-size: .72rem; color: #374151; }
        .filter-chip strong { color: #111827; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin-bottom: 20px; }
        .summary-card { border: 1px solid #e5e7eb; border-radius: 10px; padding: 12px 14px; }
        .summary-card .s-label { font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; }
        .summary-card .s-value { font-size: 1.25rem; font-weight: 800; margin-top: 4px; font-variant-numeric: tabular-nums; }
        .s-blue { border-color: #bfdbfe; background: #eff6ff; } .s-blue .s-label { color: #2563eb; } .s-blue .s-value { color: #1d4ed8; }
        .s-green { border-color: #bbf7d0; background: #f0fdf4; } .s-green .s-label { color: #16a34a; } .s-green .s-value { color: #15803d; }
        .s-orange { border-color: #fed7aa; background: #fff7ed; } .s-orange .s-label { color: #ea580c; } .s-orange .s-value { color: #c2410c; }
        .s-neutral { border-color: #e5e7eb; background: #f9fafb; } .s-neutral .s-label { color: #6b7280; } .s-neutral .s-value { color: #111827; }
        .s-amber { border-color: #fde68a; background: #fffbeb; } .s-amber .s-label { color: #d97706; } .s-amber .s-value { color: #b45309; }
        table { width: 100%; border-collapse: collapse; font-size: .78rem; }
        thead tr { background: #f3f4f6; }
        thead th { padding: 8px 10px; text-align: left; font-weight: 700; font-size: .68rem; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; border-bottom: 1px solid #e5e7eb; white-space: nowrap; }
        thead th.text-right, tbody td.text-right { text-align: right; }
        tbody tr { border-bottom: 1px solid #f3f4f6; }
        tbody tr:last-child { border-bottom: none; }
        tbody td { padding: 7px 10px; color: #374151; vertical-align: middle; }
        .mono { font-family: 'Courier New', monospace; font-size: .75rem; }
        .debit { color: #dc2626; font-weight: 600; }
        .credit { color: #16a34a; font-weight: 600; }
        .balance { color: #111827; font-weight: 700; font-family: 'Courier New', monospace; }
        .empty-row td { text-align: center; color: #9ca3af; padding: 32px; }
        .badge { display: inline-flex; align-items: center; border-radius: 4px; padding: 2px 6px; font-size: .68rem; font-weight: 600; }
        .badge-receipt { background: #ecfdf5; color: #059669; }
        .badge-payout  { background: #eff6ff; color: #2563eb; }
        .badge-expense { background: #fffbeb; color: #d97706; }
        .pm-footer { margin-top: 28px; border-top: 1px solid #e5e7eb; padding-top: 10px; display: flex; justify-content: space-between; font-size: .68rem; color: #9ca3af; }
        .no-print { text-align: center; margin-bottom: 24px; }
        .print-btn { display: inline-flex; align-items: center; gap: 8px; background: #465FFF; color: #fff; border: none; border-radius: 8px; padding: 10px 24px; font-size: .875rem; font-weight: 600; cursor: pointer; font-family: inherit; }
        .print-btn:hover { background: #3b50e0; }
        @media print { body { padding: 0; } .no-print { display: none !important; } }
        @page { size: A4; margin: 18mm 16mm; }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    <div class="pm-header">
        <div class="pm-header-left">
            <div class="pm-logo-icon"><span></span><span></span><span></span></div>
            <span class="pm-name">Palladium Mall</span>
        </div>
        <div class="pm-header-right">
            <div class="doc-title">{{ $pageTitle }}</div>
            <div class="doc-date">Printed: {{ now()->format('d M Y, h:i A') }}</div>
        </div>
    </div>

    @if(!empty($filterChips))
        <div class="filters-row">
            @foreach($filterChips as $chip)
                <span class="filter-chip"><strong>{{ $chip['label'] }}:</strong> {{ $chip['value'] }}</span>
            @endforeach
        </div>
    @endif


    <table>
        <thead>
            <tr>
                @foreach($columns as $col)
                    <th class="{{ $col['class'] ?? '' }}">{{ $col['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($columns as $col)
                        @php $val = $row[$col['key']] ?? null; @endphp
                        <td class="{{ $col['class'] ?? '' }} {{ $col['td_class'] ?? '' }}">
                            @if(($col['type'] ?? '') === 'debit')
                                <span class="{{ $val > 0 ? 'debit' : '' }}">{{ $val > 0 ? 'Rs. ' . number_format($val, 2) : '—' }}</span>
                            @elseif(($col['type'] ?? '') === 'credit')
                                <span class="{{ $val > 0 ? 'credit' : '' }}">{{ $val > 0 ? 'Rs. ' . number_format($val, 2) : '—' }}</span>
                            @elseif(($col['type'] ?? '') === 'balance')
                                <span class="balance">Rs. {{ number_format($val, 2) }}</span>
                            @elseif(($col['type'] ?? '') === 'amount')
                                <span class="debit">Rs. {{ number_format($val, 2) }}</span>
                            @elseif(($col['type'] ?? '') === 'date')
                                <span class="mono">{{ $val instanceof \Carbon\Carbon ? $val->format('d M Y') : ($val ?? '—') }}</span>
                            @elseif(($col['type'] ?? '') === 'badge')
                                @php $badgeClass = match($val) { 'Receipt' => 'badge-receipt', 'Payout' => 'badge-payout', default => 'badge-expense' }; @endphp
                                <span class="badge {{ $badgeClass }}">{{ $val ?? '—' }}</span>
                            @else
                                {{ $val ?? '—' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="{{ count($columns) }}">No entries found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pm-footer">
        <span>Palladium Mall Management System</span>
        <span>Generated on {{ now()->format('d M Y \a\t h:i A') }}</span>
    </div>

    <script>
        window.addEventListener('load', function () {
            if (window.opener) { setTimeout(function () { window.print(); }, 400); }
        });
    </script>
</body>
</html>
