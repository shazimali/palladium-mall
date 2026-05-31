@props([
    'totalUnits'    => 0,
    'occupiedUnits' => 0,
    'vacantUnits'   => 0,
    'rentDue'       => 0,
    'utilitiesDue'  => 0,
    'occupancyRate' => 0,
])

<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5 md:gap-5">

    {{-- Total Units — Blue/Brand --}}
    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
         style="background: linear-gradient(135deg, #465fff 0%, #2a31d8 100%);">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10" style="background: #fff;"></div>
        <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10" style="background: #fff;"></div>
        <div class="relative">
            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.18);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M3 6a3 3 0 013-3h12a3 3 0 013 3v12a3 3 0 01-3 3H6a3 3 0 01-3-3V6zm3-1.5A1.5 1.5 0 004.5 6v12A1.5 1.5 0 006 19.5h12a1.5 1.5 0 001.5-1.5V6A1.5 1.5 0 0018 4.5H6zM8 8a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1zm5 0a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1zm-5 4a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1zm5 0a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.75);">Total Units</p>
            <h4 class="mt-1 text-3xl font-extrabold">{{ number_format($totalUnits) }}</h4>
            <span class="mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold" style="background: rgba(255,255,255,0.2);">
                {{ $occupancyRate }}% Occupied
            </span>
        </div>
    </div>

    {{-- Occupied Units — Emerald Green --}}
    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
         style="background: linear-gradient(135deg, #12b76a 0%, #027a48 100%);">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10" style="background: #fff;"></div>
        <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10" style="background: #fff;"></div>
        <div class="relative">
            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.18);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22ZM16.0303 9.46967C16.3232 9.76256 16.3232 10.2374 16.0303 10.5303L11.0303 15.5303C10.7374 15.8232 10.2626 15.8232 9.96967 15.5303L7.96967 13.5303C7.67678 13.2374 7.67678 12.7626 7.96967 12.4697C8.26256 12.1768 8.73744 12.1768 9.03033 12.4697L10.5 13.9393L14.9697 9.46967C15.2626 9.17678 15.7374 9.17678 16.0303 9.46967Z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.75);">Occupied</p>
            <h4 class="mt-1 text-3xl font-extrabold">{{ number_format($occupiedUnits) }}</h4>
            <span class="mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold" style="background: rgba(255,255,255,0.2);">
                Active Tenants
            </span>
        </div>
    </div>

    {{-- Vacant Units — Amber/Orange --}}
    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
         style="background: linear-gradient(135deg, #f79009 0%, #b54708 100%);">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10" style="background: #fff;"></div>
        <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10" style="background: #fff;"></div>
        <div class="relative">
            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.18);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2ZM12 6C12.5523 6 13 6.44772 13 7V13C13 13.5523 12.5523 14 12 14C11.4477 14 11 13.5523 11 13V7C11 6.44772 11.4477 6 12 6ZM12 16C11.4477 16 11 16.4477 11 17C11 17.5523 11.4477 18 12 18C12.5523 18 13 17.5523 13 17C13 16.4477 12.5523 16 12 16Z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.75);">Vacant</p>
            <h4 class="mt-1 text-3xl font-extrabold">{{ number_format($vacantUnits) }}</h4>
            <span class="mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold" style="background: rgba(255,255,255,0.2);">
                Available
            </span>
        </div>
    </div>

    {{-- Rent Due — Red --}}
    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
         style="background: linear-gradient(135deg, #f04438 0%, #912018 100%);">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10" style="background: #fff;"></div>
        <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10" style="background: #fff;"></div>
        <div class="relative">
            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.18);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            <p class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.75);">Rent Due</p>
            <h4 class="mt-1 text-xl font-extrabold">Rs. {{ number_format($rentDue) }}</h4>
            <span class="mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold" style="background: rgba(255,255,255,0.2);">
                This Month
            </span>
        </div>
    </div>

    {{-- Utilities Due — Purple --}}
    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl"
         style="background: linear-gradient(135deg, #7a5af8 0%, #2a31d8 100%);">
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10" style="background: #fff;"></div>
        <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10" style="background: #fff;"></div>
        <div class="relative">
            <div class="mb-4 flex h-11 w-11 items-center justify-center rounded-xl" style="background: rgba(255,255,255,0.18);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg">
                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                </svg>
            </div>
            <p class="text-xs font-semibold uppercase tracking-widest" style="color: rgba(255,255,255,0.75);">Utilities Due</p>
            <h4 class="mt-1 text-xl font-extrabold">Rs. {{ number_format($utilitiesDue) }}</h4>
            <span class="mt-2 inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-semibold" style="background: rgba(255,255,255,0.2);">
                Pending
            </span>
        </div>
    </div>

</div>