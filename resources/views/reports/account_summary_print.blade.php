<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle }} - Palladium Mall</title>
    @include('reports.partials.account_summary_print_styles')
</head>
<body>

    @include('reports.partials.account_summary_print_header')

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center">SR #</th>
                <th>Account Name</th>
                <th class="text-right">Payables</th>
                <th class="text-right">Receivables</th>
                <th class="text-right">Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summary as $entry)
                <tr>
                    <td class="text-center" style="color: #94a3b8;">{{ $loop->iteration }}</td>
                    <td class="font-bold">{{ $entry['name'] }}</td>
                    <td class="text-right font-bold nowrap text-red">{{ $entry['payable'] > 0 ? number_format($entry['payable'], 2) : '—' }}</td>
                    <td class="text-right font-bold nowrap text-green">{{ $entry['receivable'] > 0 ? number_format($entry['receivable'], 2) : '—' }}</td>
                    <td class="text-right font-bold nowrap {{ $entry['closing'] >= 0 ? 'text-green' : 'text-red' }}">{{ number_format($entry['closing'], 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="color: #94a3b8; padding: 30px 0;">No accounts found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if($summary->isNotEmpty())
            <tfoot>
                <tr style="background: #cbd5e1; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a;">
                    <td colspan="2" style="color: #0f172a;">Grand Total ({{ $summary->count() }} Accounts)</td>
                    <td class="text-right nowrap text-red">{{ number_format($summary->sum('payable'), 2) }}</td>
                    <td class="text-right nowrap text-green">{{ number_format($summary->sum('receivable'), 2) }}</td>
                    <td class="text-right nowrap" style="color: #7e22ce;">{{ number_format($summary->sum('closing'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>
