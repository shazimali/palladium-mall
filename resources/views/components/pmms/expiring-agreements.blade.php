@props(['agreements' => collect()])

<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 transition-all hover:shadow-md">
    <div class="flex items-center justify-between mb-5">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Expiring Agreements
            </h3>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                Due in next 30 days
            </p>
        </div>
        <a href="{{ route('agreements.index') }}"
            class="text-sm font-semibold text-warning-600 hover:text-warning-700 dark:text-warning-400 dark:hover:text-warning-300 transition-colors">
            View All →
        </a>
    </div>

    <div class="space-y-3.5">
        @forelse($agreements as $agreement)
            <div class="flex items-center justify-between p-3.5 rounded-xl border border-warning-100 bg-warning-50/20 dark:border-warning-950/30 dark:bg-warning-950/10 transition-all hover:bg-warning-50/40 dark:hover:bg-warning-950/20">
                <div class="flex items-center gap-3">
                    <div
                        class="flex items-center justify-center w-10 h-10 rounded-full bg-warning-100 dark:bg-warning-950/40 text-warning-600 shrink-0">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="stroke-current">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    <div>
                        <p class="text-theme-sm font-bold text-gray-800 dark:text-white/90">
                            {{ $agreement->tenant->name }}
                        </p>
                        <span class="block text-theme-xs text-gray-500 dark:text-gray-400 font-medium">
                            {{ $agreement->unit->unit_number }}
                        </span>
                    </div>
                </div>

                <div class="text-right">
                    <p class="text-theme-sm font-extrabold text-warning-600 dark:text-warning-400">
                        {{ $agreement->daysRemaining() }} days
                    </p>
                    <span class="text-theme-xs text-gray-400 dark:text-gray-500 font-medium">
                        {{ $agreement->end_date->format('d M Y') }}
                    </span>
                </div>
            </div>
        @empty
            <div class="py-10 text-center border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                <p class="text-sm text-gray-400 dark:text-gray-600">No agreements expiring soon.</p>
            </div>
        @endforelse
    </div>
</div>