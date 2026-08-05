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
            font-size: 13px;
        }

        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            color: #000;
            background: #fff;
            padding: 16px 20px;
            line-height: 1.35;
            font-weight: 700;
        }

        .pm-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #111827;
            padding-bottom: 10px;
            margin-bottom: 14px;
        }

        .pm-header-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .pm-logo-icon {
            width: 38px;
            height: 38px;
            background: #111827;
            border-radius: 8px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 3px;
            padding: 6px 6px 5px;
        }

        .pm-logo-icon span {
            display: block;
            background: #fff;
            border-radius: 2px;
            width: 5px;
        }

        .pm-logo-icon span:nth-child(1) {
            height: 100%;
        }

        .pm-logo-icon span:nth-child(2) {
            height: 70%;
            opacity: .9;
        }

        .pm-logo-icon span:nth-child(3) {
            height: 82%;
            opacity: .7;
        }

        .pm-name {
            font-size: 1.5rem;
            font-weight: 900;
            color: #000;
            letter-spacing: -.5px;
        }

        .pm-header-right {
            text-align: right;
        }

        .pm-header-right .doc-title {
            font-size: 1.85rem;
            font-weight: 900;
            color: #000;
            line-height: 1.2;
        }

        .pm-header-right .doc-date {
            font-size: 1.45rem;
            font-weight: 900;
            color: #000;
            margin-top: 4px;
        }

        .filters-row {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 18px;
        }

        .filter-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f9fafb;
            border: 2px solid #000;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 1.4rem;
            font-weight: 900;
            color: #000;
        }

        .filter-chip span {
            font-size: 1.4rem;
            font-weight: 900;
            color: #000;
        }

        .filter-chip strong {
            color: #000;
            font-size: 1.4rem;
            font-weight: 900;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 19px;
            font-weight: 700;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
        }

        thead tr {
            background: #d1d5db;
        }

        thead th {
            padding: 8px 10px;
            text-align: left;
            font-weight: 900;
            font-size: 19px;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: #000;
            border-bottom: 2px solid #000;
            white-space: nowrap;
        }

        thead th.col-compact, tbody td.col-compact, tfoot td.col-compact { white-space: nowrap; width: 1%; }
        thead th.col-tight, tbody td.col-tight, tfoot td.col-tight { white-space: nowrap; width: 1%; padding-left: 4px; padding-right: 4px; font-size: 14px; }
        thead th.col-desc, tbody td.col-desc, tfoot td.col-desc { width: auto; word-break: break-word; }

        thead th.text-right,
        tbody td.text-right {
            text-align: right;
        }

        tbody tr {
            border-bottom: 1px solid #e5e7eb;
        }

        tbody td {
            padding: 6px 10px;
            color: #000;
            font-weight: 700;
            vertical-align: middle;
            font-size: 19px;
        }

        tfoot td {
            padding: 8px 10px;
        }

        .mono {
            font-size: 19px;
            font-weight: 900;
        }

        .debit {
            color: #dc2626;
            font-weight: 900;
        }

        .credit {
            color: #16a34a;
            font-weight: 900;
        }

        .balance {
            color: #000;
            font-weight: 900;
            font-size: 19px;
        }

        .empty-row td {
            text-align: center;
            color: #4b5563;
            padding: 24px;
            font-weight: 800;
            font-size: 19px;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            border-radius: 4px;
            padding: 2px 6px;
            font-size: 19px;
            font-weight: 900;
        }

        .badge-receipt {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-payout {
            background: #dbeafe;
            color: #1e40af;
        }

        .badge-expense {
            background: #fee2e2;
            color: #991b1b;
        }

        .pm-footer {
            margin-top: 24px;
            border-top: 2px solid #000;
            padding-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            font-weight: 900;
            color: #111827;
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #111827;
            color: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 24px;
            font-size: 16px;
            font-weight: 900;
            cursor: pointer;
            font-family: inherit;
        }

        .print-btn:hover {
            background: #000;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 0.5cm;
            }

            .no-print {
                display: none !important;
            }

            tfoot {
                display: table-row-group;
            }

            body {
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
                font-weight: bold !important;
                zoom: 0.9;
            }

            table {
                border-top: 2px solid #000 !important;
                border-bottom: 2px solid #000 !important;
                border-left: none !important;
                border-right: none !important;
            }

            th,
            td {
                border-left: none !important;
                border-right: none !important;
                font-size: 19px !important;
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

            .print-border {
                border-width: 1px !important;
                border-color: #000 !important;
            }

            .filter-chip, .filter-chip span, .filter-chip strong, .doc-date {
                font-size: 1.4rem !important;
                font-weight: 900 !important;
            }
        }
    </style>
</head>

<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    <div class="pm-header">
        <div class="pm-header-left">
            <div class="pm-logo-icon"><span></span><span></span><span></span></div>
            <div>
                <h1 class="pm-company-name">Palladium Mall Executive Services</h1>
                <p class="pm-company-tagline">Financial Statement Ledger</p>
            </div>
        </div>
        <div class="pm-header-right">
            <div class="doc-title">{{ $pageTitle ?? 'Financial Ledger Statement' }}</div>
            <div class="doc-date">As of {{ now()->format('d M Y') }}</div>
        </div>
    </div>

    @if(isset($filterChips) && count($filterChips) > 0)
        <div class="filters-row">
            @foreach($filterChips as $chip)
                <div class="filter-chip">
                    <span>{{ $chip['label'] }}:</span>
                    <strong>{{ $chip['value'] }}</strong>
                </div>
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
                                <span
                                    class="{{ $val > 0 ? 'debit' : '' }}">{{ $val > 0 ? number_format($val, 2) : '—' }}</span>
                            @elseif(($col['type'] ?? '') === 'credit')
                                <span
                                    class="{{ $val > 0 ? 'credit' : '' }}">{{ $val > 0 ? number_format($val, 2) : '—' }}</span>
                            @elseif(($col['type'] ?? '') === 'balance')
                                <span class="balance">{{ number_format($val, 2) }}</span>
                            @elseif(($col['type'] ?? '') === 'amount')
                                <span class="debit">{{ number_format($val, 2) }}</span>
                            @elseif(($col['type'] ?? '') === 'date')
                                <span
                                    class="mono">{{ $val instanceof \Carbon\Carbon ? $val->format('d M Y') : ($val ?? '—') }}</span>
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
                <tr class="empty-row">
                    <td colspan="{{ count($columns) }}">No entries found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($rows) > 0)
            <tfoot>
                <tr
                    style="background: #e5e7eb; border-top: 2px solid #000; border-bottom: 2px solid #000; font-weight: 900;">
                    @foreach($columns as $index => $col)
                        @php
                            $key = $col['key'];
                            $type = $col['type'] ?? '';
                            $sum = in_array($type, ['debit', 'credit', 'amount']) ? collect($rows)->sum(fn($r) => (float) ($r[$key] ?? 0)) : 0;
                            $lastVal = collect($rows)->last()[$key] ?? 0;
                        @endphp
                        <td class="{{ $col['class'] ?? '' }} {{ $col['td_class'] ?? '' }}"
                            style="padding: 8px 10px; font-weight: 900; font-size: 19px; color: #000;">
                            @if($index === 0)
                                <strong style="font-size: 19px; font-weight: 900;">TOTAL SUMMARY</strong>
                            @elseif($type === 'debit')
                                <span class="debit" style="font-size: 19px; font-weight: 900;">{{ number_format($sum, 2) }}</span>
                            @elseif($type === 'credit')
                                <span class="credit" style="font-size: 19px; font-weight: 900;">{{ number_format($sum, 2) }}</span>
                            @elseif($type === 'balance')
                                <span class="balance" style="font-size: 19px; font-weight: 900;">{{ number_format((float) $lastVal, 2) }}</span>
                            @elseif($type === 'amount')
                                <span class="debit" style="font-size: 19px; font-weight: 900;">{{ number_format($sum, 2) }}</span>
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