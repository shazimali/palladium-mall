<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle }} - Palladium Mall</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #1e293b;
            margin: 0;
            padding: 15px;
        }

        .header-container {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2.5px solid #0f172a;
            padding-bottom: 16px;
        }
        .header-brand {
            font-size: 16px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin: 0 0 4px 0;
        }
        .header-title {
            font-size: 22px;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin: 0 0 6px 0;
        }
        .header-highlight {
            font-size: 28px;
            font-weight: bold;
            color: #1d4ed8;
            margin: 4px 0 10px 0;
        }

        .tags-container {
            text-align: center;
            margin-top: 8px;
        }
        .tag-pill {
            display: inline-block;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            color: #334155;
            padding: 5px 12px;
            border-radius: 14px;
            font-size: 10px;
            font-weight: bold;
            margin: 3px 4px;
            text-transform: uppercase;
        }
        .tag-pill strong {
            color: #0f172a;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: collapse;
        }
        .summary-box {
            background: #f8fafc;
            padding: 9px 12px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
            text-align: center;
        }
        .summary-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            color: #64748b;
        }
        .summary-value {
            font-size: 14px;
            font-weight: 900;
            color: #0f172a;
            margin-top: 2px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        .data-table th {
            background: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
            padding: 9px 7px;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        .data-table td {
            padding: 8px 7px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 13px;
        }
        .data-table tfoot td {
            padding: 10px 7px;
            font-weight: 900;
            font-size: 12px;
        }
        .text-right, .data-table th.text-right { text-align: right; }
        .text-center, .data-table th.text-center { text-align: center; }
        .nowrap { white-space: nowrap; }
        .font-bold { font-weight: bold; }
    </style>
</head>
<body>

    @php
        $summaryColors = [
            's-green'   => ['border' => '#a7f3d0', 'bg' => '#ecfdf5', 'fg' => '#047857'],
            's-blue'    => ['border' => '#bfdbfe', 'bg' => '#eff6ff', 'fg' => '#1d4ed8'],
            's-orange'  => ['border' => '#fed7aa', 'bg' => '#fff7ed', 'fg' => '#c2410c'],
            's-amber'   => ['border' => '#fde68a', 'bg' => '#fffbeb', 'fg' => '#b45309'],
            's-red'     => ['border' => '#fecdd3', 'bg' => '#fff1f2', 'fg' => '#e11d48'],
            's-purple'  => ['border' => '#e9d5ff', 'bg' => '#faf5ff', 'fg' => '#7e22ce'],
            's-neutral' => ['border' => '#cbd5e1', 'bg' => '#f8fafc', 'fg' => '#0f172a'],
        ];
        $tags = array_merge($metaItems ?? [], $filterChips ?? []);
        $formatDate = fn($v) => $v ? ($v instanceof \Carbon\Carbon ? $v : \Carbon\Carbon::parse($v))->format('d M Y') : '—';
    @endphp

    <div class="header-container">
        <div class="header-brand">PALLADIUM MALL</div>
        <div class="header-title">{{ $pageTitle }}</div>

        @if(!empty($highlightName))
            <div class="header-highlight">{{ $highlightName }}</div>
        @endif

        @if(!empty($tags))
            <div class="tags-container">
                @foreach($tags as $t)
                    <span class="tag-pill">
                        <strong>{{ $t['label'] }}:</strong> {{ $t['value'] }}
                    </span>
                @endforeach
            </div>
        @endif
    </div>

    @if(!empty($summaryCards))
        <table class="summary-table">
            <tr>
                @foreach($summaryCards as $card)
                    @php $c = $summaryColors[$card['color'] ?? 's-neutral'] ?? $summaryColors['s-neutral']; @endphp
                    <td style="width: {{ round(100 / count($summaryCards), 2) }}%; padding: 3px;">
                        <div class="summary-box" style="border-color: {{ $c['border'] }}; background: {{ $c['bg'] }};">
                            <div class="summary-title" style="color: {{ $c['fg'] }};">{{ $card['label'] }}</div>
                            <div class="summary-value" style="color: {{ $c['fg'] }};">{{ $card['value'] }}</div>
                        </div>
                    </td>
                @endforeach
            </tr>
        </table>
    @endif

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center">SR #</th>
                @foreach($columns as $col)
                    <th class="{{ str_contains($col['class'] ?? '', 'text-right') ? 'text-right' : '' }}">{{ $col['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $i => $row)
                <tr>
                    <td class="text-center" style="color: #94a3b8;">{{ $loop->iteration }}</td>
                    @foreach($columns as $col)
                        @php
                            $val = $row[$col['key']] ?? null;
                            $type = $col['type'] ?? '';
                        @endphp
                        @if($type === 'date')
                            <td class="nowrap">{{ $formatDate($val) }}</td>
                        @elseif($type === 'debit')
                            <td class="text-right font-bold nowrap" style="color: #e11d48;">{{ (float) $val > 0 ? number_format((float) $val, 2) : '—' }}</td>
                        @elseif($type === 'credit' || $type === 'amount')
                            <td class="text-right font-bold nowrap" style="color: #059669;">{{ (float) $val > 0 ? number_format((float) $val, 2) : '—' }}</td>
                        @elseif($type === 'balance')
                            <td class="text-right font-bold nowrap">{{ number_format((float) $val, 2) }}</td>
                        @elseif($type === 'badge')
                            @php
                                $badgeColor = match (true) {
                                    str_contains((string) $val, 'Receipt') => '#047857',
                                    str_contains((string) $val, 'Expense') => '#e11d48',
                                    default => '#4338ca',
                                };
                            @endphp
                            <td class="font-bold" style="color: {{ $badgeColor }};">{{ $val ?? '—' }}</td>
                        @else
                            <td class="{{ str_contains($col['class'] ?? '', 'text-right') ? 'text-right' : '' }}">{{ ($val === null || $val === '') ? '—' : $val }}</td>
                        @endif
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) + 1 }}" class="text-center" style="color: #94a3b8; padding: 30px 0;">No entries found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($rows) > 0)
            @php
                $firstTotalIndex = collect($columns)->search(fn($c) => in_array($c['type'] ?? '', ['debit', 'credit', 'amount', 'balance']));
                $labelSpan = ($firstTotalIndex === false ? count($columns) : $firstTotalIndex) + 1;
                $lastRow = collect($rows)->last();
            @endphp
            <tfoot>
                <tr style="background: #cbd5e1; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a;">
                    <td colspan="{{ $labelSpan }}" style="color: #0f172a;">Total ({{ count($rows) }} Records)</td>
                    @if($firstTotalIndex !== false)
                        @foreach(array_slice($columns, $firstTotalIndex) as $col)
                            @php
                                $type = $col['type'] ?? '';
                                $sum = collect($rows)->sum(fn($r) => (float) ($r[$col['key']] ?? 0));
                            @endphp
                            @if($type === 'debit')
                                <td class="text-right" style="color: #e11d48;">{{ number_format($sum, 2) }}</td>
                            @elseif($type === 'credit' || $type === 'amount')
                                <td class="text-right" style="color: #059669;">{{ number_format($sum, 2) }}</td>
                            @elseif($type === 'balance')
                                <td class="text-right" style="color: #7e22ce;">{{ number_format((float) ($lastRow[$col['key']] ?? 0), 2) }}</td>
                            @else
                                <td></td>
                            @endif
                        @endforeach
                    @endif
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>
