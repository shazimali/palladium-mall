@props(['payments' => collect()])

<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 transition-all hover:shadow-md">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Overdue Payments
            </h3>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                Past due date
            </p>
        </div>
        <a href="{{ route('payments.index', ['status' => 'unpaid']) }}"
            class="text-sm font-semibold text-error-600 hover:text-error-700 dark:text-error-400 dark:hover:text-error-300 transition-colors">
            View All →
        </a>
    </div>

    <div class="space-y-3.5">
        @forelse($payments as $payment)
            <div class="flex items-center justify-between p-3.5 rounded-xl border border-error-100 bg-error-50/20 dark:border-error-950/30 dark:bg-error-950/10 transition-all hover:bg-error-50/40 dark:hover:bg-error-950/20">
                <div class="flex items-center gap-3">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full bg-error-100 dark:bg-error-950/40 text-error-600 shrink-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="stroke-current">
                            <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <div>
                        <p class="text-theme-sm font-bold text-gray-800 dark:text-white/90">
                            {{ $payment->tenant->name }}
                        </p>
                        <span class="block text-theme-xs text-gray-500 dark:text-gray-400 font-medium">
                            {{ $payment->unit->unit_number }} · {{ ucfirst($payment->type) }}
                        </span>
                    </div>
                </div>

                <div class="text-right">
                    <p class="text-theme-sm font-extrabold text-error-600 dark:text-error-400">
                        Rs. {{ number_format($payment->balanceDue()) }}
                    </p>
                    <span class="text-theme-xs text-gray-400 dark:text-gray-500 font-medium">
                        Due {{ $payment->due_date->format('d M Y') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="py-10 text-center border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                <p class="text-sm text-gray-400 dark:text-gray-600">No overdue payments. All clear!</p>
            </div>
        @endforelse
    </div>
</div>