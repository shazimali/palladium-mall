@extends('layouts.app')

@section('content')
    <div class="space-y-8">

        {{-- Page Header & Month/Year Filter --}}
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white/90">Dashboard</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    Welcome back! Here's what's happening at Palladium Mall for <span class="font-bold text-brand-500">{{ $currentMonthLabel }}</span>.
                </p>
            </div>
            
            <form action="{{ route('dashboard') }}" method="GET" class="flex items-center gap-2" id="filter-form">
                <div class="relative">
                    <label class="sr-only">Filter Month/Year</label>
                    <input type="text" id="dashboard_month" name="month" value="{{ $selectedMonth }}-01" readonly
                        class="w-44 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 cursor-pointer">
                </div>
            </form>
        </div>

        {{-- Financial Summary Section --}}
        <div class="p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
            <h3 class="text-base font-bold text-gray-800 dark:text-white mb-5 flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
                <span>📅</span> Billing & Recovery Summary — <span class="text-brand-500">{{ $currentMonthLabel }}</span>
            </h3>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach($financialWidgets as $wKey => $data)
                    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                        style="background: {{ $data['gradient'] }}; min-height: 150px;">
                        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                        <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                        <div class="relative flex justify-between items-center mb-2">
                            <p class="text-xs font-bold uppercase tracking-wider text-white/75">{{ $data['label'] }}</p>
                            <span class="text-base">{{ $data['icon'] }}</span>
                        </div>
                        <div class="relative mt-2 space-y-1.5">
                            <div class="flex justify-between items-baseline">
                                <span class="text-[10px] uppercase text-white/70">Expected Total</span>
                                <span class="font-bold text-white text-sm sm:text-base">
                                    Rs. {{ number_format($data['due']) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-baseline">
                                <span class="text-[10px] uppercase text-white/70">Received</span>
                                <span class="font-bold text-emerald-300 text-sm sm:text-base">
                                    Rs. {{ number_format($data['paid']) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                                <span class="text-[10px] uppercase text-white/70">Pending</span>
                                <span class="font-bold text-rose-300 text-sm sm:text-base">
                                    Rs. {{ number_format($data['unpaid']) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Flats/Shops Status Rows --}}
        <div class="space-y-6">

            {{-- Row 1: Overall --}}
            <div class="p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
                <h3 class="text-base font-bold text-gray-800 dark:text-white mb-4 flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
                    <span>🏢</span> Overall Flats & Shops Status
                </h3>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    {{-- Total --}}
                    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                        style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); min-height: 110px;">
                        <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-10 bg-white"></div>
                        <div class="relative flex justify-between items-center mb-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-white/80">Total Flats / Shops</p>
                            <span class="text-lg">🏢</span>
                        </div>
                        <div class="relative mt-2">
                            <span class="text-3xl font-extrabold text-white">{{ $overall['total'] }}</span>
                            <span class="text-xs opacity-75 ml-1">Flats/Shops</span>
                        </div>
                    </div>
                    {{-- Rented --}}
                    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                        style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); min-height: 110px;">
                        <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-10 bg-white"></div>
                        <div class="relative flex justify-between items-center mb-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-white/80">Rented Flats / Shops</p>
                            <span class="text-lg">🔑</span>
                        </div>
                        <div class="relative mt-2">
                            <span class="text-3xl font-extrabold text-white">{{ $overall['rented'] }}</span>
                            <span class="text-xs opacity-75 ml-1">Flats/Shops</span>
                        </div>
                    </div>
                    {{-- Vacant --}}
                    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                        style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); min-height: 110px;">
                        <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-10 bg-white"></div>
                        <div class="relative flex justify-between items-center mb-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-white/80">Vacant Flats / Shops</p>
                            <span class="text-lg">🟢</span>
                        </div>
                        <div class="relative mt-2">
                            <span class="text-3xl font-extrabold text-white">{{ $overall['vacant'] }}</span>
                            <span class="text-xs opacity-75 ml-1">Flats/Shops</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 2: PM Mall Managed --}}
            <div class="p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                    <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <span>🏢</span> Palladium Mall Managed Flats & Shops
                    </h3>
                    <a href="{{ route('dashboard.units-detail', ['type' => 'pm_mall']) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                        <span>👁️</span> Detail
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    {{-- Total --}}
                    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                        style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); min-height: 110px;">
                        <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-10 bg-white"></div>
                        <div class="relative flex justify-between items-center mb-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-white/80">Total PM Mall Managed</p>
                            <span class="text-lg">🏢</span>
                        </div>
                        <div class="relative mt-2">
                            <span class="text-3xl font-extrabold text-white">{{ $pmMall['total'] }}</span>
                            <span class="text-xs opacity-75 ml-1">Flats/Shops</span>
                        </div>
                    </div>
                    {{-- Rented --}}
                    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                        style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); min-height: 110px;">
                        <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-10 bg-white"></div>
                        <div class="relative flex justify-between items-center mb-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-white/80">Rented PM Mall Managed</p>
                            <span class="text-lg">🔑</span>
                        </div>
                        <div class="relative mt-2">
                            <span class="text-3xl font-extrabold text-white">{{ $pmMall['rented'] }}</span>
                            <span class="text-xs opacity-75 ml-1">Flats/Shops</span>
                        </div>
                    </div>
                    {{-- Vacant --}}
                    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                        style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); min-height: 110px;">
                        <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-10 bg-white"></div>
                        <div class="relative flex justify-between items-center mb-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-white/80">Vacant PM Mall Managed</p>
                            <span class="text-lg">🟢</span>
                        </div>
                        <div class="relative mt-2">
                            <span class="text-3xl font-extrabold text-white">{{ $pmMall['vacant'] }}</span>
                            <span class="text-xs opacity-75 ml-1">Flats/Shops</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Row 3: Other Owned Units --}}
            <div class="p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                    <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                        <span>🏢</span> Other-Owned Flats & Shops
                    </h3>
                    <a href="{{ route('dashboard.units-detail', ['type' => 'other_owned']) }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                        <span>👁️</span> Detail
                    </a>
                </div>
                <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                    {{-- Total --}}
                    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                        style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); min-height: 110px;">
                        <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-10 bg-white"></div>
                        <div class="relative flex justify-between items-center mb-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-white/80">Total Other-Owned</p>
                            <span class="text-lg">🏢</span>
                        </div>
                        <div class="relative mt-2">
                            <span class="text-3xl font-extrabold text-white">{{ $otherOwned['total'] }}</span>
                            <span class="text-xs opacity-75 ml-1">Flats/Shops</span>
                        </div>
                    </div>
                    {{-- Rented --}}
                    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                        style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); min-height: 110px;">
                        <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-10 bg-white"></div>
                        <div class="relative flex justify-between items-center mb-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-white/80">Rented Other-Owned</p>
                            <span class="text-lg">🔑</span>
                        </div>
                        <div class="relative mt-2">
                            <span class="text-3xl font-extrabold text-white">{{ $otherOwned['rented'] }}</span>
                            <span class="text-xs opacity-75 ml-1">Flats/Shops</span>
                        </div>
                    </div>
                    {{-- Vacant --}}
                    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                        style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); min-height: 110px;">
                        <div class="absolute -right-4 -top-4 h-20 w-20 rounded-full opacity-10 bg-white"></div>
                        <div class="relative flex justify-between items-center mb-1">
                            <p class="text-xs font-bold uppercase tracking-wider text-white/80">Vacant Other-Owned</p>
                            <span class="text-lg">🟢</span>
                        </div>
                        <div class="relative mt-2">
                            <span class="text-3xl font-extrabold text-white">{{ $otherOwned['vacant'] }}</span>
                            <span class="text-xs opacity-75 ml-1">Flats/Shops</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#dashboard_month', {
                    dateFormat: 'Y-m-01',
                    altInput: true,
                    altFormat: 'F Y',
                    allowInput: false,
                    disableMobile: true,
                    plugins: [
                        new monthSelectPlugin({
                            shorthand: false,
                            dateFormat: 'Y-m-01',
                            altFormat: 'F Y',
                            theme: 'light',
                        })
                    ],
                    onChange: function(selectedDates, dateStr, instance) {
                        document.getElementById('filter-form').submit();
                    }
                });
            }
        });
    </script>
@endpush