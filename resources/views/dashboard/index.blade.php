@extends('layouts.app')

@section('content')
    <div class="space-y-8">

        {{-- Page Header & Date Range Filter --}}
        <div
            class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white/90">Dashboard</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    Welcome back! Here's what's happening at Palladium Mall for <span
                        class="font-bold text-brand-500">{{ $currentMonthLabel }}</span>.
                </p>
            </div>

            <form action="{{ route('dashboard') }}" method="GET" class="flex flex-wrap items-center gap-3" id="filter-form">
                <div class="flex items-center gap-2">
                    <label for="from_date"
                        class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400">From:</label>
                    <input type="text" id="from_date" name="from_date" value="{{ $fromDate }}" placeholder="From Date"
                        readonly
                        class="w-36 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 cursor-pointer">
                </div>

                <div class="flex items-center gap-2">
                    <label for="to_date" class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400">To:</label>
                    <input type="text" id="to_date" name="to_date" value="{{ $toDate }}" placeholder="To Date" readonly
                        class="w-36 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 cursor-pointer">
                </div>

                @if(request()->filled('from_date') || request()->filled('to_date') || request()->filled('month'))
                    <a href="{{ route('dashboard') }}"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Expiring Agreements Top Alert Widget --}}
        @if(isset($expiringAgreements) && $expiringAgreements->isNotEmpty())
            <div
                class="rounded-2xl border-2 border-amber-300 bg-amber-50/90 p-5 dark:border-amber-800/80 dark:bg-amber-950/40 shadow-sm">
                <div
                    class="flex items-center justify-between flex-wrap gap-4 mb-4 pb-3 border-b border-amber-200 dark:border-amber-900/60">
                    <div class="flex items-center gap-3">
                        <span
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-500 text-white font-black text-xl shadow-xs">⚠️</span>
                        <div>
                            <h3 class="text-base font-black text-amber-950 dark:text-amber-200">
                                Expiring Agreements Alert ({{ $expiringAgreements->count() }}
                                {{ Str::plural('Agreement', $expiringAgreements->count()) }} Ending Soon)
                            </h3>
                            <p class="text-xs font-semibold text-amber-700 dark:text-amber-400">
                                The following tenant agreements are expiring in the next 60 days. Please take action to renew,
                                extend, or process move-out.
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('tenants.index', ['expiring_days' => 60]) }}"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2 text-xs font-extrabold text-white hover:bg-amber-700 transition-colors shadow-xs">
                        View All Tenants / Agreements →
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    @foreach($expiringAgreements->take(8) as $ag)
                        @php
                            $days = $ag->daysRemaining();
                            $badgeColor = $days <= 7 ? 'bg-red-100 text-red-800 border-red-300 dark:bg-red-950/80 dark:text-red-300' : ($days <= 15 ? 'bg-amber-100 text-amber-800 border-amber-300 dark:bg-amber-950/80 dark:text-amber-300' : 'bg-yellow-100 text-yellow-800 border-yellow-300 dark:bg-yellow-950/80 dark:text-yellow-300');
                        @endphp
                        <div
                            class="rounded-xl border border-amber-200/80 bg-white p-3.5 shadow-2xs dark:border-amber-900/40 dark:bg-gray-900 flex items-center justify-between">
                            <div class="truncate mr-2">
                                <p class="text-sm font-extrabold text-gray-900 dark:text-white truncate">
                                    {{ $ag->unit?->unit_number ?? '—' }}
                                </p>
                                <p class="text-sm font-extrabold text-gray-900 dark:text-white truncate"
                                    title="{{ $ag->tenant?->name }}">{{ $ag->tenant?->name ?? '—' }}</p>
                                <p class="text-[11px] font-semibold text-gray-400">End:
                                    {{ $ag->end_date ? $ag->end_date->format('d M Y') : '—' }}
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <span
                                    class="inline-flex items-center rounded-lg border px-2 py-1 text-xs font-black {{ $badgeColor }}">
                                    {{ $days }}d left
                                </span>
                                <a href="{{ route('tenants.index', ['search' => $ag->unit?->unit_number ?? $ag->unit_id]) }}"
                                    class="block mt-1 text-[11px] font-black text-brand-600 hover:underline">
                                    Manage →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Financial Summary Section --}}
        <div
            class="p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
            <h3
                class="text-base font-bold text-gray-800 dark:text-white mb-5 flex items-center gap-2 border-b border-gray-100 dark:border-gray-800 pb-3">
                <span>📅</span> Billing & Recovery Summary — <span class="text-brand-500">{{ $currentMonthLabel }}</span>
            </h3>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
                @foreach($financialWidgets as $wKey => $data)
                    <div class="group relative overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                        style="background: {{ $data['gradient'] }}; min-height: 150px;">
                        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                        <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                        <div class="relative flex justify-between items-center mb-2">
                            <p class="text-sm font-extrabold uppercase tracking-wider text-white">{{ $data['label'] }}</p>
                            <span class="text-base">{{ $data['icon'] }}</span>
                        </div>
                        <div class="relative mt-2 space-y-1.5">
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs font-bold uppercase text-white/90">Expected Total</span>
                                <span class="font-bold text-white text-sm">
                                    Rs. {{ number_format($data['due']) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs font-bold uppercase text-white/90">Received</span>
                                <span class="font-bold text-white text-sm">
                                    Rs. {{ number_format($data['paid']) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                                <span class="text-xs font-bold uppercase text-white/90">Pending</span>
                                <span class="font-extrabold text-white text-xl text-1xl">
                                    Rs. {{ number_format($data['unpaid']) }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Flats/Shops Status Rows --}}
            <div class="space-y-6">
                {{-- Row 1: Overall Units --}}
                <div
                    class="p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                        <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <span>🏢</span> Overall Flats & Shops Status
                        </h3>
                        <a href="{{ route('dashboard.units-detail', array_filter(['from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-extrabold text-white shadow-md hover:bg-blue-700 hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                            <span>👁️</span> Detail
                        </a>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                        {{-- Total --}}
                        <a href="{{ route('dashboard.units-detail', array_filter(['from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                            class="group relative block overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                            style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); min-height: 150px;">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                            <div class="relative flex justify-between items-center mb-2">
                                <p class="text-sm font-extrabold uppercase tracking-wider text-white">Total Flats / Shops
                                </p>
                                <span class="text-base">🏢</span>
                            </div>
                            <div class="relative mt-2 space-y-1.5">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Total Units</span>
                                    <span class="font-extrabold text-white text-xl text-1xl">{{ $overall['total'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Flats</span>
                                    <span class="font-extrabold text-white text-xl text-1xl">{{ $overall['flat'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                                    <span class="text-xs font-bold uppercase text-white/90">Shops</span>
                                    <span class="font-extrabold text-white text-xl text-1xl">{{ $overall['shop'] }}</span>
                                </div>
                                @if($overall['office'] > 0)
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-xs font-bold uppercase text-white/90">Offices</span>
                                        <span class="font-extrabold text-white text-xl text-1xl">{{ $overall['office'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                        {{-- Rented (Green) --}}
                        <a href="{{ route('dashboard.units-detail', array_filter(['status' => 'rented', 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                            class="group relative block overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                            style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); min-height: 150px;">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                            <div class="relative flex justify-between items-center mb-2">
                                <p class="text-sm font-extrabold uppercase tracking-wider text-white">Rented Flats / Shops
                                </p>
                                <span class="text-base">🔑</span>
                            </div>
                            <div class="relative mt-2 space-y-1.5">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Total Rented</span>
                                    <span class="font-extrabold text-white text-xl text-1xl">{{ $overall['rented'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Flats</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $overall['rented_flat'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                                    <span class="text-xs font-bold uppercase text-white/90">Shops</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $overall['rented_shop'] }}</span>
                                </div>
                                @if($overall['rented_office'] > 0)
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-xs font-bold uppercase text-white/90">Offices</span>
                                        <span
                                            class="font-extrabold text-white text-xl text-1xl">{{ $overall['rented_office'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                        {{-- Vacant (Red) --}}
                        <a href="{{ route('dashboard.units-detail', array_filter(['status' => 'vacant', 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                            class="group relative block overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                            style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); min-height: 150px;">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                            <div class="relative flex justify-between items-center mb-2">
                                <p class="text-sm font-extrabold uppercase tracking-wider text-white">Vacant Flats / Shops
                                </p>
                                <span class="text-base">🟢</span>
                            </div>
                            <div class="relative mt-2 space-y-1.5">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Total Vacant</span>
                                    <span class="font-extrabold text-white text-xl text-1xl">{{ $overall['vacant'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Flats</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $overall['vacant_flat'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                                    <span class="text-xs font-bold uppercase text-white/90">Shops</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $overall['vacant_shop'] }}</span>
                                </div>
                                @if($overall['vacant_office'] > 0)
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-xs font-bold uppercase text-white/90">Offices</span>
                                        <span
                                            class="font-extrabold text-white text-xl text-1xl">{{ $overall['vacant_office'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Row 2: PM Mall Managed --}}
                <div
                    class="p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                        <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <span>🏢</span> Palladium Mall Managed Flats & Shops
                        </h3>
                        <a href="{{ route('dashboard.units-detail', array_filter(['type' => 'pm_mall', 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-extrabold text-white shadow-md hover:bg-blue-700 hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                            <span>👁️</span> Detail
                        </a>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                        {{-- Total PM Mall --}}
                        <a href="{{ route('dashboard.units-detail', array_filter(['type' => 'pm_mall', 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                            class="group relative block overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                            style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); min-height: 150px;">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                            <div class="relative flex justify-between items-center mb-2">
                                <p class="text-sm font-extrabold uppercase tracking-wider text-white">Total PM Mall Managed
                                </p>
                                <span class="text-base">🏢</span>
                            </div>
                            <div class="relative mt-2 space-y-1.5">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Total Units</span>
                                    <span class="font-extrabold text-white text-xl text-1xl">{{ $pmMall['total'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Flats</span>
                                    <span class="font-extrabold text-white text-xl text-1xl">{{ $pmMall['flat'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                                    <span class="text-xs font-bold uppercase text-white/90">Shops</span>
                                    <span class="font-extrabold text-white text-xl text-1xl">{{ $pmMall['shop'] }}</span>
                                </div>
                                @if($pmMall['office'] > 0)
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-xs font-bold uppercase text-white/90">Offices</span>
                                        <span class="font-extrabold text-white text-xl text-1xl">{{ $pmMall['office'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                        {{-- Rented PM Mall (Green) --}}
                        <a href="{{ route('dashboard.units-detail', array_filter(['type' => 'pm_mall', 'status' => 'rented', 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                            class="group relative block overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                            style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); min-height: 150px;">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                            <div class="relative flex justify-between items-center mb-2">
                                <p class="text-sm font-extrabold uppercase tracking-wider text-white">Rented PM Mall Managed
                                </p>
                                <span class="text-base">🔑</span>
                            </div>
                            <div class="relative mt-2 space-y-1.5">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Total Rented</span>
                                    <span class="font-extrabold text-white text-xl text-1xl">{{ $pmMall['rented'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Flats</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $pmMall['rented_flat'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                                    <span class="text-xs font-bold uppercase text-white/90">Shops</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $pmMall['rented_shop'] }}</span>
                                </div>
                                @if($pmMall['rented_office'] > 0)
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-xs font-bold uppercase text-white/90">Offices</span>
                                        <span
                                            class="font-extrabold text-white text-xl text-1xl">{{ $pmMall['rented_office'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                        {{-- Vacant PM Mall (Red) --}}
                        <a href="{{ route('dashboard.units-detail', array_filter(['type' => 'pm_mall', 'status' => 'vacant', 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                            class="group relative block overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                            style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); min-height: 150px;">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                            <div class="relative flex justify-between items-center mb-2">
                                <p class="text-sm font-extrabold uppercase tracking-wider text-white">Vacant PM Mall Managed
                                </p>
                                <span class="text-base">🟢</span>
                            </div>
                            <div class="relative mt-2 space-y-1.5">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Total Vacant</span>
                                    <span class="font-extrabold text-white text-xl text-1xl">{{ $pmMall['vacant'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Flats</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $pmMall['vacant_flat'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                                    <span class="text-xs font-bold uppercase text-white/90">Shops</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $pmMall['vacant_shop'] }}</span>
                                </div>
                                @if($pmMall['vacant_office'] > 0)
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-xs font-bold uppercase text-white/90">Offices</span>
                                        <span
                                            class="font-extrabold text-white text-xl text-1xl">{{ $pmMall['vacant_office'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                    </div>
                </div>

                {{-- Row 3: Other Owned Units --}}
                <div
                    class="p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-4">
                        <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <span>🏢</span> Other-Owned Flats & Shops
                        </h3>
                        <a href="{{ route('dashboard.units-detail', array_filter(['type' => 'other_owned', 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                            class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-extrabold text-white shadow-md hover:bg-blue-700 hover:shadow-lg transition-all transform hover:-translate-y-0.5">
                            <span>👁️</span> Detail
                        </a>
                    </div>
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                        {{-- Total Other-Owned --}}
                        <a href="{{ route('dashboard.units-detail', array_filter(['type' => 'other_owned', 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                            class="group relative block overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                            style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); min-height: 150px;">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                            <div class="relative flex justify-between items-center mb-2">
                                <p class="text-sm font-extrabold uppercase tracking-wider text-white">Total Other-Owned</p>
                                <span class="text-base">🏢</span>
                            </div>
                            <div class="relative mt-2 space-y-1.5">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Total Units</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $otherOwned['total'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Flats</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $otherOwned['flat'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                                    <span class="text-xs font-bold uppercase text-white/90">Shops</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $otherOwned['shop'] }}</span>
                                </div>
                                @if($otherOwned['office'] > 0)
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-xs font-bold uppercase text-white/90">Offices</span>
                                        <span
                                            class="font-extrabold text-white text-xl text-1xl">{{ $otherOwned['office'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                        {{-- Rented Other-Owned (Green) --}}
                        <a href="{{ route('dashboard.units-detail', array_filter(['type' => 'other_owned', 'status' => 'rented', 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                            class="group relative block overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                            style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); min-height: 150px;">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                            <div class="relative flex justify-between items-center mb-2">
                                <p class="text-sm font-extrabold uppercase tracking-wider text-white">Rented Other-Owned</p>
                                <span class="text-base">🔑</span>
                            </div>
                            <div class="relative mt-2 space-y-1.5">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Total Rented</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $otherOwned['rented'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Flats</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $otherOwned['rented_flat'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                                    <span class="text-xs font-bold uppercase text-white/90">Shops</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $otherOwned['rented_shop'] }}</span>
                                </div>
                                @if($otherOwned['rented_office'] > 0)
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-xs font-bold uppercase text-white/90">Offices</span>
                                        <span
                                            class="font-extrabold text-white text-xl text-1xl">{{ $otherOwned['rented_office'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                        {{-- Vacant Other-Owned (Red) --}}
                        <a href="{{ route('dashboard.units-detail', array_filter(['type' => 'other_owned', 'status' => 'vacant', 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                            class="group relative block overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                            style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); min-height: 150px;">
                            <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                            <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                            <div class="relative flex justify-between items-center mb-2">
                                <p class="text-sm font-extrabold uppercase tracking-wider text-white">Vacant Other-Owned</p>
                                <span class="text-base">🟢</span>
                            </div>
                            <div class="relative mt-2 space-y-1.5">
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Total Vacant</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $otherOwned['vacant'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline">
                                    <span class="text-xs font-bold uppercase text-white/90">Flats</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $otherOwned['vacant_flat'] }}</span>
                                </div>
                                <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                                    <span class="text-xs font-bold uppercase text-white/90">Shops</span>
                                    <span
                                        class="font-extrabold text-white text-xl text-1xl">{{ $otherOwned['vacant_shop'] }}</span>
                                </div>
                                @if($otherOwned['vacant_office'] > 0)
                                    <div class="flex justify-between items-baseline">
                                        <span class="text-xs font-bold uppercase text-white/90">Offices</span>
                                        <span
                                            class="font-extrabold text-white text-xl text-1xl">{{ $otherOwned['vacant_office'] }}</span>
                                    </div>
                                @endif
                            </div>
                        </a>
                    </div>
                </div>
            </div>

        </div>
@endsection

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof flatpickr !== 'undefined') {
                    flatpickr('#from_date', {
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'd M Y',
                        allowInput: false,
                        disableMobile: true,
                        onChange: function () {
                            document.getElementById('filter-form').submit();
                        }
                    });

                    flatpickr('#to_date', {
                        dateFormat: 'Y-m-d',
                        altInput: true,
                        altFormat: 'd M Y',
                        allowInput: false,
                        disableMobile: true,
                        onChange: function () {
                            document.getElementById('filter-form').submit();
                        }
                    });
                }
            });
        </script>
    @endpush