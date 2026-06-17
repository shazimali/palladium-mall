@php
    $paymentAccountsList = \App\Models\PaymentAccount::orderBy('name')->get(['id', 'name']);
@endphp

<div class="overflow-x-auto">
    <table id="reportTable" class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-white">
            <tr>
                <th class="px-4 py-3 text-center">SR</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">RSV</th>
                <th class="px-4 py-3">Flat No</th>
                <th class="px-4 py-3">Owner</th>
                <th class="px-4 py-3">Status</th>
                <th class="px-4 py-3">Serv</th>
                <th class="px-4 py-3">Extra</th>
                <th class="px-4 py-3">Rent</th>
                <th class="px-4 py-3">Total Amount</th>
                <th class="px-4 py-3 text-emerald-600 dark:text-emerald-400">Received</th>
                
                @foreach($paymentAccountsList as $account)
                    <th class="px-4 py-3">{{ $account->name }}</th>
                @endforeach
                
                <th class="px-4 py-3 text-emerald-600 dark:text-emerald-400">Total</th>
                <th class="px-4 py-3 text-red-500">Pending</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($entries as $entry)
                @php
                    $isPending = $entry['pending'] > 0;
                    $isVacant = $entry['status'] === 'VACANT';
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors {{ $isVacant ? 'bg-gray-50/50 dark:bg-gray-900/10 text-gray-400 dark:text-gray-500 italic' : '' }}">
                    <td class="px-4 py-3 text-center font-medium">{{ $entry['sr'] }}</td>
                    <td class="px-4 py-3 font-medium text-xs">{{ $entry['date'] ?: '—' }}</td>
                    <td class="px-4 py-3 text-xs font-semibold">{{ $entry['rsv'] ?: '—' }}</td>
                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                        <span class="rounded bg-gray-100 dark:bg-gray-800 px-2 py-0.5">
                            {{ $entry['flat_no'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-medium">{{ $entry['owner'] }}</td>
                    <td class="px-4 py-3 text-xs">
                        @php
                            $statusClass = match($entry['status']) {
                                'RENTED' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                'VACANT' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                'SELF'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                default  => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                            };
                        @endphp
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ $statusClass }}">
                            {{ $entry['status'] }}
                        </span>
                    </td>
                    <td class="px-4 py-3">Rs. {{ number_format($entry['serv']) }}</td>
                    <td class="px-4 py-3">Rs. {{ number_format($entry['extra']) }}</td>
                    <td class="px-4 py-3">Rs. {{ number_format($entry['rent']) }}</td>
                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">Rs. {{ number_format($entry['total_amount']) }}</td>
                    <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($entry['received']) }}</td>
                    
                    @foreach($paymentAccountsList as $account)
                        <td class="px-4 py-3 text-xs text-gray-500">
                            @if(($entry['payment_accounts'][$account->name] ?? 0) > 0)
                                <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                    Rs. {{ number_format($entry['payment_accounts'][$account->name]) }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                    @endforeach
                    
                    <td class="px-4 py-3 font-bold text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($entry['received']) }}</td>
                    <td class="px-4 py-3 font-bold {{ $isPending ? 'text-red-500' : 'text-gray-400 dark:text-gray-600' }}">
                        @if($isPending)
                            Rs. {{ number_format($entry['pending']) }}
                        @else
                            Rs. 0
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-gray-50 dark:bg-gray-800 font-bold text-sm">
                <td colspan="6" class="px-4 py-3 text-right">Totals</td>
                <td class="px-4 py-3">Rs. {{ number_format($summary['total_serv']) }}</td>
                <td class="px-4 py-3">Rs. {{ number_format($summary['total_extra']) }}</td>
                <td class="px-4 py-3">Rs. {{ number_format($summary['total_rent']) }}</td>
                <td class="px-4 py-3">Rs. {{ number_format($summary['total_amount']) }}</td>
                <td class="px-4 py-3 text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($summary['total_received']) }}</td>
                
                @foreach($paymentAccountsList as $account)
                    <td class="px-4 py-3">Rs. {{ number_format($summary['accounts_total'][$account->name] ?? 0) }}</td>
                @endforeach
                
                <td class="px-4 py-3 text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($summary['total_received']) }}</td>
                <td class="px-4 py-3 text-red-500">Rs. {{ number_format($summary['total_pending']) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
