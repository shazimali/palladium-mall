@props([
    'totalUnits' => 0,
    'occupiedUnits' => 0,
    'vacantUnits' => 0,
    'rentDue' => 0,
    'utilitiesDue' => 0,
    'occupancyRate' => 0,
])

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 md:gap-6">

    {{-- Total Units (Blue/Brand theme) --}}
    <div class="rounded-2xl bg-gradient-to-br from-brand-500 to-brand-700 p-5 md:p-6 shadow-lg shadow-brand-500/10 text-white transition-all hover:scale-[1.02] hover:shadow-xl">
        <div class="flex items-center justify-center w-12 h-12 bg-white/15 rounded-xl shrink-0">
            <svg class="fill-white" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6zm3-1.5A1.5 1.5 0 004.5 6v12A1.5 1.5 0 006 19.5h12a1.5 1.5 0 001.5-1.5V6A1.5 1.5 0 0018 4.5H6zM8 8a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1zm5 0a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1zm-5 4a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1zm5 0a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1zm-5 4a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1zm5 0a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z"/>
            </svg>
        </div>
        <div class="flex items-end justify-between mt-4">
            <div>
                <span class="text-sm text-brand-100/90 font-medium">Total Units</span>
                <h4 class="mt-1 text-3xl font-extrabold">{{ number_format($totalUnits) }}</h4>
            </div>
            <span class="flex items-center gap-1 rounded-full bg-white/20 py-0.5 px-2.5 text-xs font-semibold">
                {{ $occupancyRate }}% Occupied
            </span>
        </div>
    </div>

    {{-- Occupied (Success theme) --}}
    <div class="rounded-2xl bg-gradient-to-br from-success-500 to-success-700 p-5 md:p-6 shadow-lg shadow-success-500/10 text-white transition-all hover:scale-[1.02] hover:shadow-xl">
        <div class="flex items-center justify-center w-12 h-12 bg-white/15 rounded-xl shrink-0">
            <svg class="fill-white" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22ZM16.0303 9.46967C16.3232 9.76256 16.3232 10.2374 16.0303 10.5303L11.0303 15.5303C10.7374 15.8232 10.2626 15.8232 9.96967 15.5303L7.96967 13.5303C7.67678 13.2374 7.67678 12.7626 7.96967 12.4697C8.26256 12.1768 8.73744 12.1768 9.03033 12.4697L10.5 13.9393L14.9697 9.46967C15.2626 9.17678 15.7374 9.17678 16.0303 9.46967Z"/>
            </svg>
        </div>
        <div class="flex items-end justify-between mt-4">
            <div>
                <span class="text-sm text-success-100/90 font-medium">Occupied Units</span>
                <h4 class="mt-1 text-3xl font-extrabold">{{ number_format($occupiedUnits) }}</h4>
            </div>
            <span class="flex items-center gap-1 rounded-full bg-white/20 py-0.5 px-2.5 text-xs font-semibold">
                Active
            </span>
        </div>
    </div>

    {{-- Vacant (Warning/Amber theme) --}}
    <div class="rounded-2xl bg-gradient-to-br from-warning-500 to-warning-700 p-5 md:p-6 shadow-lg shadow-warning-500/10 text-white transition-all hover:scale-[1.02] hover:shadow-xl">
        <div class="flex items-center justify-center w-12 h-12 bg-white/15 rounded-xl shrink-0">
            <svg class="fill-white" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM12 6C12.5523 6 13 6.44772 13 7V13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13V7C11 6.44772 11.4477 6 12 6ZM12 16C11.4477 16 11 16.4477 11 17C11 17.5523 11.4477 18 12 18C12.5523 18 13 17.5523 13 17C13 16.4477 12.5523 16 12 16Z"/>
            </svg>
        </div>
        <div class="flex items-end justify-between mt-4">
            <div>
                <span class="text-sm text-warning-100/90 font-medium">Vacant Units</span>
                <h4 class="mt-1 text-3xl font-extrabold">{{ number_format($vacantUnits) }}</h4>
            </div>
            <span class="flex items-center gap-1 rounded-full bg-white/20 py-0.5 px-2.5 text-xs font-semibold">
                Available
            </span>
        </div>
    </div>

    {{-- Rent Due (Error theme) --}}
    <div class="rounded-2xl bg-gradient-to-br from-error-500 to-error-700 p-5 md:p-6 shadow-lg shadow-error-500/10 text-white transition-all hover:scale-[1.02] hover:shadow-xl">
        <div class="flex items-center justify-center w-12 h-12 bg-white/15 rounded-xl shrink-0">
            <svg class="fill-white" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
        </div>
        <div class="flex items-end justify-between mt-4">
            <div>
                <span class="text-sm text-error-100/90 font-medium">Rent Due (Month)</span>
                <h4 class="mt-1 text-2xl font-extrabold">Rs. {{ number_format($rentDue) }}</h4>
            </div>
            <span class="flex items-center gap-1 rounded-full bg-white/20 py-0.5 px-2.5 text-xs font-semibold">
                Pending
            </span>
        </div>
    </div>

    {{-- Utilities Due (Purple theme) --}}
    <div class="rounded-2xl bg-gradient-to-br from-theme-purple-500 to-brand-700 p-5 md:p-6 shadow-lg shadow-violet-500/10 text-white transition-all hover:scale-[1.02] hover:shadow-xl">
        <div class="flex items-center justify-center w-12 h-12 bg-white/15 rounded-xl shrink-0">
            <svg class="stroke-white" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
            </svg>
        </div>
        <div class="flex items-end justify-between mt-4">
            <div>
                <span class="text-sm text-violet-100/90 font-medium">Utilities Due</span>
                <h4 class="mt-1 text-2xl font-extrabold font-mono">Rs. {{ number_format($utilitiesDue) }}</h4>
            </div>
            <span class="flex items-center gap-1 rounded-full bg-white/20 py-0.5 px-2.5 text-xs font-semibold">
                Pending
            </span>
        </div>
    </div>

</div>