@props([
    'chartMonths' => [],
    'chartDue' => [],
    'chartPaid' => [],
])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white px-5 pt-5 sm:px-6 sm:pt-6 dark:border-gray-800 dark:bg-white/[0.03]">
    <div class="flex items-center justify-between mb-2">
        <div>
            <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
                Rent Collection — Last 6 Months
            </h3>
            <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
                Collected vs Due comparison
            </p>
        </div>
        <a href="{{ route('reports.index', ['report_type' => 'rent']) }}"
            class="text-sm font-medium text-brand-500 hover:text-brand-600 transition-colors">
            View Report →
        </a>
</div>
    <div class="max-w-full overflow-x-auto custom-scrollbar">
        <div id="pmmsRentChart" class="-ml-5 h-[280px] min-w-[500px] pl-2 xl:min-w-full"></div>
    </div>
    </div>