@props(['payments' => collect()])

<div
    class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-4 pb-3 pt-4 dark:border-gray-800 dark:bg-white/[0.03] sm:px-6 transition-all hover:shadow-md">
    <div class="flex flex-col gap-2 mb-4 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">Recent Payments</h3>
        <a href="{{ route('payments.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-theme-sm font-semibold text-gray-700 shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-white/[0.03] transition-colors">
            See All
        </a>
    </div>

    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <table class="min-w-full">
            <thead>
                <tr class="border-t border-gray-100 dark:border-gray-800">
                    <th class="py-3 text-left pr-4">
                        <p class="font-semibold text-gray-400 text-theme-xs uppercase tracking-wider">Tenant / Unit</p>
                    </th>
                    <th class="py-3 text-left pr-4">
                        <p class="font-semibold text-gray-400 text-theme-xs uppercase tracking-wider">Type</p>
                    </th>
                    <th class="py-3 text-left pr-4">
                        <p class="font-semibold text-gray-400 text-theme-xs uppercase tracking-wider">Month</p>
                    </th>
                    <th class="py-3 text-left pr-4">
                        <p class="font-semibold text-gray-400 text-theme-xs uppercase tracking-wider">Amount</p>
                    </th>
                    <th class="py-3 text-left">
                        <p class="font-semibold text-gray-400 text-theme-xs uppercase tracking-wider">Status</p>
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $payment)
                    <tr class="border-t border-gray-100 dark:border-gray-800 hover:bg-gray-50/50 dark:hover:bg-white/[0.01] transition-colors">
                        <td class="py-3 pr-4 whitespace-nowrap">
                            <div>
                                <p class="font-bold text-gray-800 text-theme-sm dark:text-white/90">
                                    {{ $payment->tenant->name }}
                                </p>
                                <span class="text-gray-400 text-theme-xs dark:text-gray-500 font-semibold">
                                    {{ $payment->unit->unit_number }}
                                </span>
                            </div>
                        </td>
                        <td class="py-3 pr-4 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full px-2 py-0.5 text-theme-xs font-semibold
                                    @if($payment->type === 'rent') bg-brand-50 text-brand-600 dark:bg-brand-500/15 dark:text-brand-400
                                    @elseif($payment->type === 'maintenance') bg-theme-purple-500/10 text-theme-purple-500 dark:bg-theme-purple-500/15 dark:text-theme-purple-500
                                    @elseif($payment->type === 'fine') bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500
                                    @else bg-gray-50 text-gray-600 dark:bg-gray-500/15 dark:text-gray-400
                                    @endif">
                                {{ ucfirst($payment->type) }}
                            </span>
                        </td>
                        <td class="py-3 pr-4 whitespace-nowrap">
                            <p class="text-gray-500 text-theme-sm dark:text-gray-400 font-medium">
                                {{ $payment->month->format('M Y') }}
                            </p>
                        </td>
                        <td class="py-3 pr-4 whitespace-nowrap">
                            <p class="font-bold text-gray-800 text-theme-sm dark:text-white/90">
                                Rs. {{ number_format($payment->amount) }}
                            </p>
                        </td>
                        <td class="py-3 whitespace-nowrap">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-theme-xs font-bold
                                    @if($payment->status === 'paid') bg-success-50 text-success-600 dark:bg-success-500/15 dark:text-success-500
                                    @elseif($payment->status === 'partial') bg-warning-50 text-warning-600 dark:bg-warning-500/15 dark:text-warning-400
                                    @else bg-error-50 text-error-600 dark:bg-error-500/15 dark:text-error-500
                                    @endif">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-10 text-center text-sm text-gray-400 border border-dashed border-gray-150 dark:border-gray-800 rounded-xl">No recent payments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>