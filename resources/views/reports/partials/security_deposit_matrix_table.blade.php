<div class="overflow-x-auto overflow-y-auto rounded-lg border border-gray-200 dark:border-gray-800">
    <table id="reportTable" class="w-full text-xs text-left text-gray-600 dark:text-gray-400">
        <thead class="text-[11px] uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-white sticky top-0 z-10 shadow-sm">
            <tr>
                <th class="px-3 py-2.5 text-center w-10 bg-gray-50 dark:bg-gray-800">SR</th>
                <th class="px-3 py-2.5 w-24 bg-gray-50 dark:bg-gray-800">Flat / Shop</th>
                <th class="px-3 py-2.5 bg-gray-50 dark:bg-gray-800">Owner</th>
                <th class="px-3 py-2.5 bg-gray-50 dark:bg-gray-800">Tenant</th>
                <th class="px-3 py-2.5 w-20 bg-gray-50 dark:bg-gray-800">Status</th>
                <th class="px-3 py-2.5 text-right bg-indigo-100 dark:bg-indigo-900/40 text-indigo-900 dark:text-indigo-200 font-bold">Required Deposit</th>
                <th class="px-3 py-2.5 text-right bg-emerald-100 dark:bg-emerald-900/40 text-emerald-900 dark:text-emerald-200 font-bold">Collected Deposit</th>
                <th class="px-3 py-2.5 text-right bg-rose-100 dark:bg-rose-900/40 text-rose-900 dark:text-rose-200 font-bold">Pending Deposit</th>
                <th class="px-3 py-2.5 text-right bg-amber-100 dark:bg-amber-900/40 text-amber-900 dark:text-amber-200 font-bold">Deductions / Damage</th>
                <th class="px-3 py-2.5 text-right bg-purple-100 dark:bg-purple-900/40 text-purple-900 dark:text-purple-200 font-bold">Net Refundable</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($entries as $entry)
                @php
                    $isPending = $entry['pending_deposit'] > 0;
                    $isVacant = $entry['status'] === 'VACANT';
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors {{ $isVacant ? 'bg-gray-50/50 dark:bg-gray-900/10 text-gray-400 dark:text-gray-500 italic' : '' }}">
                    <td class="px-3 py-2 text-center font-medium">{{ $entry['sr'] }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-md border border-brand-200 bg-brand-50 px-2 py-0.5 text-xs font-extrabold text-brand-800 dark:border-brand-800/60 dark:bg-brand-950/40 dark:text-brand-300">
                            {{ $entry['flat_no'] }}
                        </span>
                    </td>
                    <td class="px-3 py-2 font-medium whitespace-nowrap truncate max-w-[140px]" title="{{ $entry['owner'] }}">{{ $entry['owner'] }}</td>
                    <td class="px-3 py-2 font-medium whitespace-nowrap truncate max-w-[140px]" title="{{ $entry['tenant'] }}">{{ $entry['tenant'] }}</td>
                    <td class="px-3 py-2">
                        @php
                            $statusClass = match($entry['status']) {
                                'RENTED', 'OCCUPIED' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                'VACANT' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                'SELF'   => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                default  => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                            };
                        @endphp
                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[10px] font-semibold {{ $statusClass }}">
                            {{ $entry['status'] }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-right font-semibold bg-indigo-50/50 dark:bg-indigo-950/20 text-indigo-900 dark:text-indigo-300">
                        {{ $entry['required_deposit'] > 0 ? ('Rs. ' . number_format($entry['required_deposit'])) : '—' }}
                    </td>
                    <td class="px-3 py-2 text-right font-semibold bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-700 dark:text-emerald-400">
                        {{ $entry['collected_deposit'] > 0 ? ('Rs. ' . number_format($entry['collected_deposit'])) : '—' }}
                    </td>
                    <td class="px-3 py-2 text-right font-bold bg-rose-50/50 dark:bg-rose-950/20 {{ $isPending ? 'text-rose-600 dark:text-rose-400' : 'text-gray-400' }}">
                        {{ $entry['pending_deposit'] > 0 ? ('Rs. ' . number_format($entry['pending_deposit'])) : '—' }}
                    </td>
                    <td class="px-3 py-2 text-right font-semibold bg-amber-50/50 dark:bg-amber-950/20 text-amber-700 dark:text-amber-400">
                        {{ $entry['deduction_deposit'] > 0 ? ('Rs. ' . number_format($entry['deduction_deposit'])) : '—' }}
                    </td>
                    <td class="px-3 py-2 text-right font-bold bg-purple-50/50 dark:bg-purple-950/20 text-purple-700 dark:text-purple-300">
                        {{ $entry['net_refundable'] > 0 ? ('Rs. ' . number_format($entry['net_refundable'])) : '—' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-gray-50 dark:bg-gray-800 font-bold text-xs">
                <td colspan="5" class="px-3 py-2.5 text-right">Totals</td>
                <td class="px-3 py-2.5 text-right bg-indigo-100 dark:bg-indigo-900/60 text-indigo-950 dark:text-indigo-200">
                    Rs. {{ number_format($summary['total_required'] ?? 0) }}
                </td>
                <td class="px-3 py-2.5 text-right bg-emerald-100 dark:bg-emerald-900/60 text-emerald-950 dark:text-emerald-200">
                    Rs. {{ number_format($summary['total_collected'] ?? 0) }}
                </td>
                <td class="px-3 py-2.5 text-right bg-rose-100 dark:bg-rose-900/60 text-rose-950 dark:text-rose-200">
                    Rs. {{ number_format($summary['total_pending'] ?? 0) }}
                </td>
                <td class="px-3 py-2.5 text-right bg-amber-100 dark:bg-amber-900/60 text-amber-950 dark:text-amber-200">
                    Rs. {{ number_format($summary['total_deductions'] ?? 0) }}
                </td>
                <td class="px-3 py-2.5 text-right bg-purple-100 dark:bg-purple-900/60 text-purple-950 dark:text-purple-200 font-extrabold">
                    Rs. {{ number_format($summary['total_net_refundable'] ?? 0) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
