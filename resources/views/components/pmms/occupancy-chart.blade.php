@props([
    'occupiedUnits' => 0,
    'vacantUnits' => 0,
    'soldUnits' => 0,
    'occupancyRate' => 0,
])

<div class="rounded-2xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03] sm:p-6 transition-all hover:shadow-md">
    <div>
        <h3 class="text-lg font-semibold text-gray-800 dark:text-white/90">
            Unit Occupancy
        </h3>
        <p class="mt-1 text-theme-sm text-gray-500 dark:text-gray-400">
            Current status breakdown
        </p>
    </div>

    <div class="my-6 overflow-hidden rounded-2xl border border-gray-100 bg-gray-50 px-4 py-4 dark:border-gray-800 dark:bg-gray-900/50 sm:px-6">
        <div class="relative flex items-center justify-center">
            <div id="pmmsOccupancyChart" class="h-[200px] w-full"></div>
            <span class="absolute text-center pointer-events-none">
                <span class="block text-2xl font-bold text-gray-800 dark:text-white/90">{{ $occupancyRate }}%</span>
                <span class="block text-xs text-gray-500 dark:text-gray-400">Occupancy</span>
            </span>
        </div>
    </div>

    <div class="flex items-center justify-center gap-5 sm:gap-8 mt-4">
        <div class="text-center">
            <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">Occupied</p>
            <p class="text-base font-semibold text-emerald-600 dark:text-emerald-400 sm:text-lg">{{ $occupiedUnits }}</p>
        </div>
        <div class="h-7 w-px bg-gray-200 dark:bg-gray-800"></div>
        <div class="text-center">
            <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">Vacant</p>
            <p class="text-base font-semibold text-amber-600 dark:text-amber-400 sm:text-lg">{{ $vacantUnits }}</p>
        </div>
        <div class="h-7 w-px bg-gray-200 dark:bg-gray-800"></div>
        <div class="text-center">
            <p class="mb-1 text-theme-xs text-gray-500 dark:text-gray-400">Sold</p>
            <p class="text-base font-semibold text-gray-600 dark:text-gray-400 sm:text-lg">{{ $soldUnits }}</p>
        </div>
    </div>
</div>