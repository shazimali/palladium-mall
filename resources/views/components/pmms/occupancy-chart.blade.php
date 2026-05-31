@props([
    'occupiedUnits' => 0,
    'vacantUnits'   => 0,
    'soldUnits'     => 0,
    'occupancyRate' => 0,
])

<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 transition-all hover:shadow-md">

    {{-- Header --}}
    <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800"
         style="border-left: 4px solid #465fff;">
        <div>
            <h3 class="text-base font-bold text-gray-800 dark:text-white/90">Unit Occupancy</h3>
            <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">Current status breakdown</p>
        </div>
        <span class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-bold"
              style="background: #ecf3ff; color: #465fff;">
            {{ $occupancyRate }}% Rate
        </span>
    </div>

    {{-- Donut Chart --}}
    <div class="relative flex items-center justify-center px-5 py-6">
        <div id="pmmsOccupancyChart" class="h-[190px] w-full"></div>
        <div class="absolute pointer-events-none text-center">
            <span class="block text-2xl font-extrabold text-gray-800 dark:text-white/90">{{ $occupancyRate }}%</span>
            <span class="block text-xs font-medium text-gray-400 dark:text-gray-500 mt-0.5">Occupancy</span>
        </div>
    </div>

    {{-- Legend / Stats --}}
    <div class="grid grid-cols-3 divide-x divide-gray-100 dark:divide-gray-800 border-t border-gray-100 dark:border-gray-800">
        <div class="py-4 text-center">
            <div class="mx-auto mb-1.5 h-2 w-8 rounded-full" style="background: #12b76a;"></div>
            <p class="text-xs text-gray-400 dark:text-gray-500">Occupied</p>
            <p class="mt-0.5 text-lg font-extrabold" style="color: #12b76a;">{{ $occupiedUnits }}</p>
        </div>
        <div class="py-4 text-center">
            <div class="mx-auto mb-1.5 h-2 w-8 rounded-full" style="background: #f79009;"></div>
            <p class="text-xs text-gray-400 dark:text-gray-500">Vacant</p>
            <p class="mt-0.5 text-lg font-extrabold" style="color: #f79009;">{{ $vacantUnits }}</p>
        </div>
        <div class="py-4 text-center">
            <div class="mx-auto mb-1.5 h-2 w-8 rounded-full" style="background: #94a3b8;"></div>
            <p class="text-xs text-gray-400 dark:text-gray-500">Sold</p>
            <p class="mt-0.5 text-lg font-extrabold text-gray-500 dark:text-gray-400">{{ $soldUnits }}</p>
        </div>
    </div>
</div>