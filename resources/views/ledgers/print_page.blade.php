<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} — Palladium Mall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 24px; }
        body { font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif; color: #000; background: #fff; padding: 20px 28px; line-height: 1.4; font-weight: 800; }
        .pm-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 4px solid #111827; padding-bottom: 14px; margin-bottom: 20px; }
        .pm-header-left { display: flex; align-items: center; gap: 14px; }
        .pm-logo-icon { width: 50px; height: 50px; background: #111827; border-radius: 10px; display: flex; align-items: flex-end; justify-content: center; gap: 4px; padding: 8px 8px 6px; }
        .pm-logo-icon span { display: block; background: #fff; border-radius: 2px; width: 7px; }
        .pm-logo-icon span:nth-child(1) { height: 100%; }
        .pm-logo-icon span:nth-child(2) { height: 70%; opacity: .9; }
        .pm-logo-icon span:nth-child(3) { height: 82%; opacity: .7; }
        .pm-name { font-size: 2.5rem; font-weight: 900; color: #000; letter-spacing: -.5px; }
        .pm-header-right { text-align: right; }
        .pm-header-right .doc-title { font-size: 2.2rem; font-weight: 900; color: #000; }
        .pm-header-right .doc-date { font-size: 1.4rem; font-weight: 800; color: #111827; margin-top: 4px; }
        .filters-row { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px; }
        .filter-chip { display: inline-flex; align-items: center; gap: 6px; background: #f3f4f6; border: 2px solid #9ca3af; border-radius: 999px; padding: 6px 16px; font-size: 1.3rem; font-weight: 900; color: #000; }
        .filter-chip strong { color: #000; font-weight: 900; }
        table { width: 100%; border-collapse: collapse; font-size: 1.5rem; font-weight: 800; }
        thead tr { background: #d1d5db; }
        thead th { padding: 14px 16px; text-align: left; font-weight: 900; font-size: 1.4rem; text-transform: uppercase; letter-spacing: .05em; color: #000; border-bottom: 3px solid #000; white-space: nowrap; }
        thead th.text-right, tbody td.text-right { text-align: right; }
        tbody tr { border-bottom: 2px solid #e5e7eb; }
        tbody td { padding: 12px 16px; color: #000; font-weight: 800; vertical-align: middle; font-size: 1.5rem; }
        .mono { font-family: 'Courier New', monospace; font-size: 1.5rem; font-weight: 900; }
        .debit { color: #dc2626; font-weight: 900; }
        .credit { color: #16a34a; font-weight: 900; }
        .balance { color: #000; font-weight: 900; font-family: 'Courier New', monospace; font-size: 1.6rem; }
        .empty-row td { text-align: center; color: #4b5563; padding: 40px; font-weight: 900; font-size: 1.5rem; }
        .badge { display: inline-flex; align-items: center; border-radius: 6px; padding: 4px 10px; font-size: 1.2rem; font-weight: 900; }
        .badge-receipt { background: #d1fae5; color: #065f46; }
        .badge-payout  { background: #dbeafe; color: #1e40af; }
        .badge-expense { background: #fef3c7; color: #92400e; }
        .pm-footer { margin-top: 36px; border-top: 3px solid #000; padding-top: 14px; display: flex; justify-content: space-between; font-size: 1.3rem; font-weight: 900; color: #111827; }
        .no-print { text-align: center; margin-bottom: 28px; }
        .print-btn { display: inline-flex; align-items: center; gap: 10px; background: #111827; color: #fff; border: none; border-radius: 12px; padding: 14px 32px; font-size: 1.3rem; font-weight: 900; cursor: pointer; font-family: inherit; }
        .print-btn:hover { background: #000; }
        @media print { body { padding: 0; } .no-print { display: none !important; } }
        @page { size: A4; margin: 10mm; }
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
        @if(count($rows) > 0)
            <tfoot>
                <tr style="background: #e5e7eb; border-top: 3px solid #000; border-bottom: 3px solid #000; font-weight: 900;">
                    @foreach($columns as $index => $col)
                        @php
                            $key = $col['key'];
                            $type = $col['type'] ?? '';
                            $sum = in_array($type, ['debit', 'credit', 'amount']) ? collect($rows)->sum(fn($r) => (float)($r[$key] ?? 0)) : 0;
                            $lastVal = collect($rows)->last()[$key] ?? 0;
                        @endphp
                        <td class="{{ $col['class'] ?? '' }} {{ $col['td_class'] ?? '' }}" style="padding: 14px 16px; font-weight: 900; font-size: 1.8rem; color: #000;">
                            @if($index === 0)
                                <strong style="font-size: 1.8rem; font-weight: 900;">TOTAL SUMMARY</strong>
                            @elseif($type === 'debit')
                                <span class="debit" style="font-size: 1.8rem; font-weight: 900;">Rs. {{ number_format($sum, 2) }}</span>
                            @elseif($type === 'credit')
                                <span class="credit" style="font-size: 1.8rem; font-weight: 900;">Rs. {{ number_format($sum, 2) }}</span>
                            @elseif($type === 'balance')
                                <span class="balance" style="font-size: 1.9rem; font-weight: 900;">Rs. {{ number_format((float)$lastVal, 2) }}</span>
                            @elseif($type === 'amount')
                                <span class="debit" style="font-size: 1.8rem; font-weight: 900;">Rs. {{ number_format($sum, 2) }}</span>
                            @else
                                —
                            @endif
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
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
