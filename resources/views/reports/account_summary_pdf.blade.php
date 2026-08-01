<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; margin: 0; padding: 0; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header h2 { margin: 5px 0 0 0; font-size: 14px; color: #555; }
        .header p { margin: 5px 0 0 0; font-size: 11px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: right; }
        th.text-left, td.text-left { text-align: left; }
        th { background-color: #eee; font-weight: bold; }
        .group-header { background-color: #f5f5f5; font-weight: bold; text-align: left; text-transform: uppercase; font-size: 12px; }
        .group-total { background-color: #f9f9f9; font-weight: bold; }
        .grand-total { background-color: #e0e0e0; font-weight: bold; font-size: 12px; }
        .text-green { color: #059669; }
        .text-red { color: #dc2626; }
    </style>
</head>
<body>

    <div class="header">
        <h1>PALLADIUM MALL</h1>
        <h2>Management Office &mdash; Islamabad</h2>
        <h2 style="color: #000; margin-top: 10px; font-size: 16px;">{{ $title }}</h2>
        <p>Statement Period: {{ $dateFrom ? date('d M Y', strtotime($dateFrom)) : 'Start' }} &mdash; {{ $dateTo ? date('d M Y', strtotime($dateTo)) : 'End' }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th class="text-left" style="width: 40%">Account Name</th>
                <th style="width: 15%">Opening Balance</th>
                <th style="width: 15%">Total Debit</th>
                <th style="width: 15%">Total Credit</th>
                <th style="width: 15%">Closing Balance</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $grandTotalOpening = 0;
                $grandTotalDebit = 0;
                $grandTotalCredit = 0;
                $grandTotalClosing = 0;
            @endphp

            @forelse($summary as $groupName => $entries)
                @php 
                    $groupLabel = '';
                    if ($groupName === 'asset') $groupLabel = 'Assets (Bank & Cash)';
                    elseif ($groupName === 'liability') $groupLabel = 'Equity & Liabilities (Owners)';
                    elseif ($groupName === 'receivable') $groupLabel = 'Receivables (Tenants)';
                    elseif ($groupName === 'expense') $groupLabel = 'Expenses';

                    $groupOpening = $entries->sum('opening');
                    $groupDebit = $entries->sum('debit');
                    $groupCredit = $entries->sum('credit');
                    $groupClosing = $entries->sum('closing');

                    $grandTotalOpening += $groupOpening;
                    $grandTotalDebit += $groupDebit;
                    $grandTotalCredit += $groupCredit;
                    $grandTotalClosing += $groupClosing;
                @endphp

                <tr>
                    <td colspan="5" class="group-header">
                        {{ $groupLabel }}
                    </td>
                </tr>

                @foreach($entries as $entry)
                    <tr>
                        <td class="text-left">{{ $entry['name'] }}</td>
                        <td>{{ number_format($entry['opening'], 2) }}</td>
                        <td>{{ number_format($entry['debit'], 2) }}</td>
                        <td>{{ number_format($entry['credit'], 2) }}</td>
                        <td style="font-weight: bold;">
                            {{ number_format($entry['closing'], 2) }}
                        </td>
                    </tr>
                @endforeach
                
                <tr class="group-total">
                    <td class="text-left" style="text-align: right;">Group Total:</td>
                    <td>{{ number_format($groupOpening, 2) }}</td>
                    <td>{{ number_format($groupDebit, 2) }}</td>
                    <td>{{ number_format($groupCredit, 2) }}</td>
                    <td>{{ number_format($groupClosing, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">No accounts found.</td>
                </tr>
            @endforelse
        </tbody>
        @if($summary->isNotEmpty())
        <tfoot>
            <tr class="grand-total">
                <th class="text-left" style="text-align: left;">Grand Total</th>
                <td style="text-align: right;">{{ number_format($grandTotalOpening, 2) }}</td>
                <td style="text-align: right;">{{ number_format($grandTotalDebit, 2) }}</td>
                <td style="text-align: right;">{{ number_format($grandTotalCredit, 2) }}</td>
                <td style="text-align: right;">{{ number_format($grandTotalClosing, 2) }}</td>
            </tr>
        </tfoot>
        @endif
    </table>

</body>
</html>
