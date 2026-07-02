@php
    $paymentAccountsList = \App\Models\PaymentAccount::orderBy('name')->get(['id', 'name']);
@endphp

<div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
    <table id="reportTable" class="w-full text-xs text-left text-gray-600 dark:text-gray-400">
        <thead class="text-[11px] uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-white sticky top-0 z-10">
            <tr>
                <th class="px-2 py-2 text-center w-8">SR</th>
                <th class="px-2 py-2 w-20">Date</th>
                <th class="px-2 py-2 w-16">RSV</th>
                <th class="px-2 py-2 w-20">Flat No</th>
                <th class="px-2 py-2">Owner</th>
                <th class="px-2 py-2 w-20">Status</th>
                
                <!-- Dues Group (Darker Blue) -->
                <th class="px-2 py-2 text-right bg-blue-100 dark:bg-blue-900/40 text-blue-900 dark:text-blue-200 font-semibold w-16">Serv</th>
                <th class="px-2 py-2 text-right bg-blue-100 dark:bg-blue-900/40 text-blue-900 dark:text-blue-200 font-semibold w-16">Extra</th>
                <th class="px-2 py-2 text-right bg-blue-100 dark:bg-blue-900/40 text-blue-900 dark:text-blue-200 font-semibold w-16">Sec. Dep</th>
                <th class="px-2 py-2 text-right bg-blue-100 dark:bg-blue-900/40 text-blue-900 dark:text-blue-200 font-semibold w-16">Rent</th>
                
                <!-- Collections Group (Darker Emerald) -->
                <th class="px-2 py-2 text-right bg-emerald-100 dark:bg-emerald-900/40 text-emerald-900 dark:text-emerald-200 font-semibold w-20">Received</th>
                @foreach($paymentAccountsList as $account)
                    <th class="px-2 py-2 text-right bg-emerald-100 dark:bg-emerald-900/40 text-emerald-900 dark:text-emerald-200 font-semibold">{{ $account->name }}</th>
                @endforeach
                
                <!-- Pending Group (Darker Rose) -->
                <th class="px-2 py-2 text-right bg-rose-200/80 dark:bg-rose-900/60 text-rose-950 dark:text-white font-bold w-20">Pending</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($entries as $entry)
                @php
                    $isPending = $entry['pending'] > 0;
                    $isVacant = $entry['status'] === 'VACANT';
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors {{ $isVacant ? 'bg-gray-50/50 dark:bg-gray-900/10 text-gray-400 dark:text-gray-500 italic' : '' }}">
                    <td class="px-2 py-1.5 text-center font-medium">{{ $entry['sr'] }}</td>
                    <td class="px-2 py-1.5 font-medium whitespace-nowrap">{{ $entry['date'] ?: '—' }}</td>
                    <td class="px-2 py-1.5 font-semibold">
                        @if($entry['rsv'])
                            <div class="flex flex-col gap-0.5 leading-tight">
                                @foreach(explode('/', $entry['rsv']) as $ref)
                                    <span class="whitespace-nowrap">{{ $ref }}</span>
                                @endforeach
                            </div>
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-2 py-1.5 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                        <span class="rounded bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5">
                            {{ $entry['flat_no'] }}
                        </span>
                    </td>
                    <td class="px-2 py-1.5 font-medium whitespace-nowrap truncate max-w-[120px]" title="{{ $entry['owner'] }}">{{ $entry['owner'] }}</td>
                    <td class="px-2 py-1.5">
                        <div class="flex flex-col gap-0.5 items-start">
                            @php
                                $statusClass = match($entry['status']) {
                                    'RENTED', 'OCCUPIED' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                    'VACANT' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'SELF'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                    default  => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded px-1 py-0.2 text-[10px] font-semibold {{ $statusClass }}">
                                {{ $entry['status'] }}
                            </span>
                            @if(!empty($entry['is_self']))
                                <span class="inline-flex items-center rounded bg-violet-100 px-1 py-0.2 text-[9px] font-bold text-violet-700 dark:bg-violet-900/30 dark:text-violet-400">
                                    Other-Owned
                                </span>
                            @endif
                        </div>
                    </td>
                    
                    <!-- Dues Group (Blue Background) -->
                    <td class="px-2 py-1.5 text-right whitespace-nowrap bg-blue-100/50 dark:bg-blue-950/20 text-blue-900 dark:text-blue-300 font-medium">{{ $entry['serv'] ? number_format($entry['serv']) : '0' }}</td>
                    <td class="px-2 py-1.5 text-right whitespace-nowrap bg-blue-100/50 dark:bg-blue-950/20 text-blue-900 dark:text-blue-300 font-medium">{{ $entry['extra'] ? number_format($entry['extra']) : '0' }}</td>
                    <td class="px-2 py-1.5 text-right whitespace-nowrap bg-blue-100/50 dark:bg-blue-950/20 text-indigo-700 dark:text-indigo-400 font-semibold">{{ $entry['security_deposit'] ? number_format($entry['security_deposit']) : '0' }}</td>
                    <td class="px-2 py-1.5 text-right whitespace-nowrap bg-blue-100/50 dark:bg-blue-950/20 text-blue-900 dark:text-blue-300 font-medium">{{ $entry['rent'] ? number_format($entry['rent']) : '0' }}</td>
                    
                    <!-- Collections Group (Emerald Background) -->
                    <td class="px-2 py-1.5 text-right font-semibold text-emerald-900 dark:text-emerald-300 whitespace-nowrap bg-emerald-100/50 dark:bg-emerald-950/20">{{ number_format($entry['received']) }}</td>
                    @foreach($paymentAccountsList as $account)
                        <td class="px-2 py-1.5 text-right text-gray-700 dark:text-gray-300 whitespace-nowrap bg-emerald-100/50 dark:bg-emerald-950/20">
                            @if(($entry['payment_accounts'][$account->name] ?? 0) > 0)
                                <span class="font-bold text-emerald-800 dark:text-emerald-400">
                                    {{ number_format($entry['payment_accounts'][$account->name]) }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                    @endforeach
                    
                    <!-- Pending Group (Rose Background) -->
                    <td class="px-2 py-1.5 text-right font-bold whitespace-nowrap bg-rose-100/50 dark:bg-rose-950/20 {{ $isPending ? 'text-red-700 dark:text-red-400 font-black' : 'text-gray-500 dark:text-gray-500' }}">
                        {{ $isPending ? number_format($entry['pending']) : '0' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-gray-50 dark:bg-gray-800 font-bold text-xs">
                <td colspan="6" class="px-2 py-2 text-right">Totals</td>
                
                <!-- Dues Totals -->
                <td class="px-2 py-2 text-right bg-blue-150 dark:bg-blue-900/50 text-blue-900 dark:text-blue-200">Rs. {{ number_format($summary['total_serv']) }}</td>
                <td class="px-2 py-2 text-right bg-blue-150 dark:bg-blue-900/50 text-blue-900 dark:text-blue-200">Rs. {{ number_format($summary['total_extra']) }}</td>
                <td class="px-2 py-2 text-right bg-blue-150 dark:bg-blue-900/50 text-indigo-700 dark:text-indigo-350">Rs. {{ number_format($summary['total_security_deposit'] ?? 0) }}</td>
                <td class="px-2 py-2 text-right bg-blue-150 dark:bg-blue-900/50 text-blue-900 dark:text-blue-200">Rs. {{ number_format($summary['total_rent']) }}</td>
                
                <!-- Collections Totals -->
                <td class="px-2 py-2 text-right bg-emerald-150 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-200">Rs. {{ number_format($summary['total_received']) }}</td>
                @foreach($paymentAccountsList as $account)
                    <td class="px-2 py-2 text-right bg-emerald-150 dark:bg-emerald-900/50 text-emerald-900 dark:text-emerald-200">Rs. {{ number_format($summary['accounts_total'][$account->name] ?? 0) }}</td>
                @endforeach
                
                <!-- Pending Totals -->
                <td class="px-2 py-2 text-right bg-rose-200 dark:bg-rose-900/70 text-rose-950 dark:text-white">Rs. {{ number_format($summary['total_pending']) }}</td>
            </tr>
        </tfoot>
    </table>
</div>
