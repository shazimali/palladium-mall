@props([
    'chartMonths' => [],
    'chartDue'    => [],
    'chartPaid'   => [],
])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">

    {{-- Header --}}
    <div class="flex items-center justify-between px-6 pt-5 pb-3">
        <div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white/90">
                Rent Collection
            </h3>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                Collected vs Due — last 6 months
            </p>
        </div>
        <a href="{{ route('reports.index', ['report_type' => 'rent']) }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 px-3 py-1.5 text-xs font-semibold text-gray-600 transition-all hover:border-brand-300 hover:text-brand-600 dark:border-gray-700 dark:text-gray-400 dark:hover:text-brand-400">
            View Report
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 12h14M12 5l7 7-7 7"/>
            </svg>
        </a>
    </div>

    {{-- Legend --}}
    <div class="flex items-center gap-5 px-6 pb-2">
        <span class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
            <span class="inline-block h-2.5 w-5 rounded-full" style="background: #e2e8f0;"></span>
            Rent Due
        </span>
        <span class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-gray-400">
            <span class="inline-block h-2.5 w-5 rounded-full" style="background: #465fff;"></span>
            Collected
        </span>
    </div>

    {{-- Chart --}}
    <div class="w-full px-2 pb-4">
        <div id="pmmsRentChart" class="h-[270px] w-full"></div>
    </div>
</div>