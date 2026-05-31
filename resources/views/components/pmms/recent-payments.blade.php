@props(['payments' => collect()])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-gray-800">
        <div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white/90">Recent Payments</h3>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Latest transactions across all units</p>
        </div>
        <a href="{{ route('payments.index') }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition-all hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
            See All →
        </a>
    </div>

    {{-- Table --}}
    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <table class="min-w-full">
            <thead>
                <tr class="bg-gray-50 dark:bg-white/[0.02]">
                    <th class="px-6 py-3 text-left">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Tenant / Unit</p>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Type</p>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Month</p>
                    </th>
                    <th class="px-4 py-3 text-left">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Amount</p>
                    </th>
                    <th class="px-6 py-3 text-left">
                        <p class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">Status</p>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                @forelse($payments as $payment)
                    <tr class="group hover:bg-gray-50/70 dark:hover:bg-white/[0.02] transition-colors">
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            <p class="text-sm font-bold text-gray-800 dark:text-white/90">
                                {{ $payment->tenant->name }}
                            </p>
                            <span class="text-xs font-semibold text-gray-400 dark:text-gray-500">
                                {{ $payment->unit->unit_number }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @php
                                $typeStyles = match($payment->type) {
                                    'rent'        => 'background:#ecf3ff; color:#465fff;',
                                    'maintenance' => 'background:#f3f0ff; color:#7a5af8;',
                                    'fine'        => 'background:#fef3f2; color:#f04438;',
                                    default       => 'background:#f2f4f7; color:#475467;',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold"
                                  style="{{ $typeStyles }}">
                                {{ ucfirst($payment->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                                {{ $payment->month->format('M Y') }}
                            </p>
                        </td>
                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <p class="text-sm font-bold text-gray-800 dark:text-white/90">
                                Rs. {{ number_format($payment->amount) }}
                            </p>
                        </td>
                        <td class="px-6 py-3.5 whitespace-nowrap">
                            @php
                                $statusStyles = match($payment->status) {
                                    'paid'    => 'background:#ecfdf3; color:#039855;',
                                    'partial' => 'background:#fffaeb; color:#b54708;',
                                    default   => 'background:#fef3f2; color:#f04438;',
                                };
                            @endphp
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-bold"
                                  style="{{ $statusStyles }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center">
                            <div class="mx-auto mb-2 flex h-10 w-10 items-center justify-center rounded-full" style="background: #f2f4f7;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#98a2b3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-400 dark:text-gray-600">No recent payments found.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>