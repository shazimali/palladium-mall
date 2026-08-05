<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} — Palladium Mall</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            font-size: 15px;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #000;
            background: #fff;
            padding: 24px 32px;
            line-height: 1.5;
            font-weight: 700;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-text {
            font-size: 1.4rem;
            font-weight: 900;
            color: #0f172a;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title h2 {
            font-size: 1.15rem;
            font-weight: 900;
            color: #0f172a;
        }

        .doc-title p {
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            margin-top: 2px;
        }

        .party-info {
            margin-bottom: 16px;
            font-size: 0.95rem;
            font-weight: 800;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 14px;
            background: #f8fafc;
        }

        .party-info p {
            margin-bottom: 4px;
        }

        .party-info strong {
            color: #0f172a;
            font-weight: 900;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.92rem;
            font-weight: 700;
            margin-bottom: 24px;
        }

        thead tr {
            background: #e2e8f0;
        }

        thead th {
            padding: 10px;
            text-align: left;
            font-weight: 900;
            font-size: 0.82rem;
            text-transform: uppercase;
            color: #0f172a;
            border-bottom: 2px solid #0f172a;
        }

        thead th.text-right,
        tbody td.text-right,
        tfoot td.text-right {
            text-align: right;
        }

        tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }

        tbody td {
            padding: 9px 10px;
            color: #000;
            font-weight: 700;
        }

        .mono {
            font-family: monospace;
            font-size: 0.9rem;
            font-weight: 800;
        }

        .debit {
            color: #16a34a;
            font-weight: 900;
        }

        .credit {
            color: #dc2626;
            font-weight: 900;
        }

        .balance {
            color: #0f172a;
            font-weight: 900;
        }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 0.75rem;
            font-weight: 800;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .badge-receipt {
            background: #dcfce7;
            color: #15803d;
        }

        .badge-expense {
            background: #fee2e2;
            color: #b91c1c;
        }

        .badge-payout {
            background: #e0e7ff;
            color: #3730a3;
        }

        .footer {
            margin-top: 30px;
            border-top: 2px solid #0f172a;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            font-weight: 800;
            color: #475569;
        }

        .no-print {
            text-align: center;
            margin-bottom: 24px;
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #0f172a;
            color: #fff;
            border: none;
            border-radius: 6px;
            padding: 10px 24px;
            font-size: 0.95rem;
            font-weight: 800;
            cursor: pointer;
        }

        .print-btn:hover {
            background: #000;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0.5cm;

                @bottom-right {
                    content: "Page " counter(page) " of " counter(pages);
                    font-size: 0.8rem;
                    font-weight: 800;
                    color: #475569;
                }
            }

            .no-print {
                display: none !important;
            }

            body {
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
                font-weight: bold !important;
                zoom: 0.8;
            }

            .max-w-3xl,
            .max-w-5xl,
            .max-w-6xl {
                max-width: 100% !important;
                padding: 5px !important;
                margin: 0 !important;
                border: none !important;
                box-shadow: none !important;
            }
        }
    </style>
</head>

<body>

    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save Statement</button>
    </div>

    <!-- Statement Header (Party Print Strategy) -->
    <div class="header">
        <div class="logo-section">
            <span class="logo-text">PALLADIUM MALL</span>
        </div>
        <div class="doc-title">
            <h2>{{ $pageTitle }}</h2>
            <p>Printed: {{ now()->format('d M Y, h:i A') }}</p>
        </div>
    </div>

    <!-- Statement Details / Filter Chips -->
    @if(!empty($filterChips))
        <div class="party-info">
            <div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: space-between; align-items: center;">
                @foreach($filterChips as $chip)
                    <p style="margin: 0;"><strong>{{ $chip['label'] }}:</strong> {{ $chip['value'] }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Statement Transactions Table -->
    <table>
        <thead>
            <tr>
                @foreach($columns as $col)
                    <th
                        class="{{ $col['class'] ?? '' }} {{ str_contains($col['class'] ?? '', 'text-right') ? 'text-right' : '' }}">
                        {{ $col['label'] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $row)
                <tr>
                    @foreach($columns as $col)
                        @php
                            $key = $col['key'];
                            $val = $row[$key] ?? null;
                        @endphp
                        <td
                            class="{{ $col['class'] ?? '' }} {{ str_contains($col['class'] ?? '', 'text-right') ? 'text-right' : '' }}">
                            @if(($col['type'] ?? '') === 'date')
                                <span
                                    class="mono">{{ $val ? ($val instanceof \Carbon\Carbon ? $val->format('d M Y') : \Carbon\Carbon::parse($val)->format('d M Y')) : '—' }}</span>
                            @elseif(($col['type'] ?? '') === 'debit')
                                <span class="mono debit">{{ (float) $val > 0 ? number_format((float) $val, 2) : '—' }}</span>
                            @elseif(($col['type'] ?? '') === 'credit')
                                <span class="mono credit">{{ (float) $val > 0 ? number_format((float) $val, 2) : '—' }}</span>
                            @elseif(($col['type'] ?? '') === 'balance')
                                <span class="mono balance">{{ number_format((float) $val, 2) }}</span>
                            @elseif(($col['type'] ?? '') === 'amount')
                                <span class="mono debit">{{ (float) $val > 0 ? number_format((float) $val, 2) : '—' }}</span>
                            @elseif(($col['type'] ?? '') === 'badge')
                                @php $badgeClass = match (true) { str_contains($val, 'Receipt') => 'badge-receipt', str_contains($val, 'Expense') => 'badge-expense', default => 'badge-payout'}; @endphp
                                <span class="badge {{ $badgeClass }}">{{ $val ?? '—' }}</span>
                            @else
                                {{ $val ?? '—' }}
                            @endif
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" style="text-align: center; color: #94a3b8; padding: 40px 0;">No
                        entries found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($rows) > 0)
            <tfoot>
                <tr
                    style="background: #e2e8f0; border-top: 3px solid #0f172a; border-bottom: 3px solid #0f172a; font-weight: 900;">
                    @foreach($columns as $index => $col)
                        @php
                            $key = $col['key'];
                            $type = $col['type'] ?? '';
                            $sum = in_array($type, ['debit', 'credit', 'amount']) ? collect($rows)->sum(fn($r) => (float) ($r[$key] ?? 0)) : 0;
                            $lastVal = collect($rows)->last()[$key] ?? 0;
                        @endphp
                        <td class="{{ $col['class'] ?? '' }} {{ str_contains($col['class'] ?? '', 'text-right') ? 'text-right' : '' }}"
                            style="padding: 12px 10px; font-weight: 900; font-size: 1.05rem;">
                            @if($index === 0)
                                <strong style="font-size: 1.05rem; font-weight: 900; color: #0f172a;">TOTAL SUMMARY</strong>
                            @elseif($type === 'debit')
                                <span class="mono debit" style="font-size: 1.05rem;">{{ number_format($sum, 2) }}</span>
                            @elseif($type === 'credit')
                                <span class="mono credit" style="font-size: 1.05rem;">{{ number_format($sum, 2) }}</span>
                            @elseif($type === 'balance')
                                <span class="mono balance"
                                    style="font-size: 1.05rem;">{{ number_format((float) $lastVal, 2) }}</span>
                            @elseif($type === 'amount')
                                <span class="mono debit" style="font-size: 1.05rem;">{{ number_format($sum, 2) }}</span>
                            @else
                                —
                            @endif
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Printed Footer (Party Print Strategy) -->
    <div class="footer">
        <span>Palladium Mall Management Office, Islamabad</span>
        <span>Generated on {{ now()->format('d M Y \a\t h:i A') }}</span>
    </div>

    <script>
        window.addEventListener('load', function () {
            if (window.opener) { setTimeout(function () { window.print(); }, 400); }
        });
    </script>
</body>

</html>