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
@endphp

<div class="header-container">
    <div class="header-brand">PALLADIUM MALL</div>
    <div class="header-title">{{ $pageTitle }}</div>

    @if(!empty($filterChips))
        <div class="tags-container">
            @foreach($filterChips as $t)
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
