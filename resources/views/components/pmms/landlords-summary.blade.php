@props(['landlords' => collect()])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white/90">Landlord Portfolio Overview</h3>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Total units owned and revenue collections by owner</p>
        </div>
        <a href="{{ route('landlords.index') }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
            See All Owners →
        </a>
    </div>

    {{-- Table --}}
    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-50 dark:bg-white/[0.02]">
                    <th class="px-6 py-3 text-left">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Landlord / Owner</p>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Flats / Shops Owned</p>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Total Earnings Generated</p>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($landlords as $landlord)
                    <tr class="group hover:bg-gray-50/70 dark:hover:bg-white/[0.02] transition-colors">
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <a href="{{ route('landlords.show', $landlord['id']) }}" class="text-sm font-bold text-brand-500 hover:underline">
                                {{ $landlord['name'] }}
                            </a>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-0.5 text-xs font-bold text-blue-700 dark:bg-blue-500/15 dark:text-blue-400">
                                {{ $landlord['units_count'] }} {{ Str::plural('unit', $landlord['units_count']) }}
                            </span>
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <p class="text-sm font-bold text-green-600 dark:text-green-400">
                                Rs. {{ number_format($landlord['earnings']) }}
                            </p>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="py-12 text-center">
                            <p class="text-sm text-gray-400 dark:text-gray-600">No landlord portfolios found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
