<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $pageTitle }} - Palladium Mall</title>
    @include('reports.partials.account_summary_print_styles')
</head>
<body>

    @include('reports.partials.account_summary_print_header')

    @php
        $groupLabels = \App\Services\AccountSummaryService::sectionLabels();
        $all = $summary->flatten(1);
    @endphp

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center">SR #</th>
                <th>Account Name</th>
                <th class="text-right">Opening Balance</th>
                <th class="text-right">Total Debit</th>
                <th class="text-right">Total Credit</th>
                <th class="text-right">Closing Balance</th>
            </tr>
        </thead>
        <tbody>
            @forelse($summary as $groupName => $entries)
                <tr class="group-header">
                    <td colspan="6">{{ $groupLabels[$groupName] ?? ucfirst(str_replace('_', ' ', $groupName)) }}</td>
                </tr>

                @foreach($entries as $entry)
                    <tr>
                        <td class="text-center" style="color: #94a3b8;">{{ $loop->iteration }}</td>
                        <td class="font-bold">{{ $entry['name'] }}</td>
                        <td class="text-right font-bold nowrap">{{ number_format($entry['opening'], 2) }}</td>
                        <td class="text-right font-bold nowrap text-red">{{ number_format($entry['debit'], 2) }}</td>
                        <td class="text-right font-bold nowrap text-green">{{ number_format($entry['credit'], 2) }}</td>
                        <td class="text-right font-bold nowrap {{ $entry['closing'] >= 0 ? 'text-green' : 'text-red' }}">{{ number_format($entry['closing'], 2) }}</td>
                    </tr>
                @endforeach

                <tr class="group-total">
                    <td colspan="2" class="text-right">Group Total:</td>
                    <td class="text-right nowrap">{{ number_format($entries->sum('opening'), 2) }}</td>
                    <td class="text-right nowrap text-red">{{ number_format($entries->sum('debit'), 2) }}</td>
                    <td class="text-right nowrap text-green">{{ number_format($entries->sum('credit'), 2) }}</td>
                    <td class="text-right nowrap">{{ number_format($entries->sum('closing'), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="color: #94a3b8; padding: 30px 0;">No accounts found for the selected filters.</td>
                </tr>
            @endforelse
        </tbody>
        @if($summary->isNotEmpty())
            <tfoot>
                <tr style="background: #cbd5e1; border-top: 2px solid #0f172a; border-bottom: 2px solid #0f172a;">
                    <td colspan="2" style="color: #0f172a;">Grand Total ({{ $all->count() }} Accounts)</td>
                    <td class="text-right nowrap">{{ number_format($all->sum('opening'), 2) }}</td>
                    <td class="text-right nowrap text-red">{{ number_format($all->sum('debit'), 2) }}</td>
                    <td class="text-right nowrap text-green">{{ number_format($all->sum('credit'), 2) }}</td>
                    <td class="text-right nowrap" style="color: #7e22ce;">{{ number_format($all->sum('closing'), 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

</body>
</html>
