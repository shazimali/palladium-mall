<div class="rounded-lg border border-gray-200 dark:border-gray-800">
    <table id="reportTable" class="w-full text-xs text-left text-gray-600 dark:text-gray-400">
        <thead class="text-[11px] uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-white sticky top-0 z-10">
            <tr>
                <th class="px-3 py-3 text-center w-12">SR</th>
                <th class="px-3 py-3 w-28">Flat/Shop</th>
                <th class="px-3 py-3 w-24">Type</th>
                <th class="px-3 py-3 w-28">Status</th>
                <th class="px-3 py-3">Owner</th>
                <th class="px-3 py-3 w-36">Rent Source</th>
                <th class="px-3 py-3 text-right bg-blue-50 dark:bg-blue-900/20 text-blue-900 dark:text-blue-200 font-semibold w-32">Monthly Rent</th>
                <th class="px-3 py-3 text-right bg-purple-50 dark:bg-purple-900/20 text-purple-900 dark:text-purple-200 font-semibold w-32">Maintenance</th>
                <th class="px-3 py-3 text-right bg-emerald-50 dark:bg-emerald-900/20 text-emerald-950 dark:text-white font-bold w-36">Total Potential</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($entries as $i => $entry)
                @php
                    $isRented = $entry['status'] === 'rented';
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors {{ !$isRented ? 'bg-gray-50/30 dark:bg-gray-900/5' : '' }}">
                    <td class="px-3 py-2 text-center text-gray-400 font-medium">{{ $i + 1 }}</td>
                    <td class="px-3 py-2 font-bold text-gray-900 dark:text-white">
                        <span class="rounded-lg bg-gray-100 dark:bg-gray-800 px-2.5 py-1">
                            {{ $entry['unit_number'] }}
                        </span>
                    </td>
                    <td class="px-3 py-2 font-medium capitalize">{{ $entry['type'] }}</td>
                    <td class="px-3 py-2">
                        <span class="inline-flex items-center rounded-md px-2 py-0.5 text-[11px] font-medium 
                            {{ $entry['status'] === 'rented' ? 'bg-green-50 text-green-700 dark:bg-green-950/30 dark:text-green-400' : '' }}
                            {{ $entry['status'] === 'vacant' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/30 dark:text-amber-400' : '' }}
                            {{ $entry['status'] === 'self' ? 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-400' : '' }}
                        ">
                            {{ ucfirst($entry['status']) }}
                        </span>
                    </td>
                    <td class="px-3 py-2 font-medium truncate max-w-[150px]" title="{{ $entry['landlord'] }}">{{ $entry['landlord'] }}</td>
                    <td class="px-3 py-2 text-gray-500 dark:text-gray-400 font-medium">
                        <span class="inline-flex items-center gap-1.5">
                            <span class="h-1.5 w-1.5 rounded-full {{ $isRented ? 'bg-green-500' : 'bg-orange-400' }}"></span>
                            {{ $entry['source'] }}
                        </span>
                    </td>
                    <td class="px-3 py-2 text-right bg-blue-50/50 dark:bg-blue-900/10 text-blue-900 dark:text-blue-200 font-semibold">
                        Rs. {{ number_format($entry['rent'], 2) }}
                    </td>
                    <td class="px-3 py-2 text-right bg-purple-50/50 dark:bg-purple-900/10 text-purple-900 dark:text-purple-200 font-semibold">
                        Rs. {{ number_format($entry['maintenance'], 2) }}
                    </td>
                    <td class="px-3 py-2 text-right bg-emerald-50/50 dark:bg-emerald-900/10 text-emerald-950 dark:text-white font-bold">
                        Rs. {{ number_format($entry['total'], 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray-50 dark:bg-gray-850 font-bold sticky bottom-0 border-t border-gray-200 dark:border-gray-700">
            <tr class="divide-y divide-gray-100 dark:divide-gray-800 text-[11px] uppercase">
                <td colspan="6" class="px-3 py-3.5 text-gray-500 dark:text-gray-400">
                    Grand Totals ({{ number_format($summary['count']) }} Flats/Shops)
                </td>
                <td class="px-3 py-3.5 text-right bg-blue-50 dark:bg-blue-900/20 text-blue-900 dark:text-blue-200">
                    Rs. {{ number_format($summary['total_rent'], 2) }}
                </td>
                <td class="px-3 py-3.5 text-right bg-purple-50 dark:bg-purple-900/20 text-purple-900 dark:text-purple-200">
                    Rs. {{ number_format($summary['total_maintenance'], 2) }}
                </td>
                <td class="px-3 py-3.5 text-right bg-emerald-100 dark:bg-emerald-900/40 text-emerald-950 dark:text-white text-xs">
                    Rs. {{ number_format($summary['total_combined'], 2) }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>
