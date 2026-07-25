@extends('layouts.app')

@section('content')
    <div class="space-y-8">

        {{-- Breadcrumbs & Header --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('dashboard', array_filter(['from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                class="hover:text-brand-500 transition-colors">Dashboard</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Detail List</span>
        </div>

        {{-- Page Header & Date Range Filter --}}
        <div
            class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
            <div>
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white/90">
                        {{ $typeLabel }} Flats & Shops
                    </h1>

                </div>
            </div>

            <form action="{{ route('dashboard.units-detail') }}" method="GET" class="flex flex-wrap items-center gap-3"
                id="units-detail-filter-form">
                <input type="hidden" name="type" value="{{ $type }}">
                @if($status)
                    <input type="hidden" name="status" value="{{ $status }}">
                @endif

                <div class="flex items-center gap-2">
                    <label for="detail_from_date"
                        class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400">From:</label>
                    <input type="text" id="detail_from_date" name="from_date" value="{{ $fromDate }}"
                        placeholder="From Date" readonly
                        class="w-36 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 cursor-pointer">
                </div>

                <div class="flex items-center gap-2">
                    <label for="detail_to_date"
                        class="text-xs font-bold uppercase text-gray-500 dark:text-gray-400">To:</label>
                    <input type="text" id="detail_to_date" name="to_date" value="{{ $toDate }}" placeholder="To Date"
                        readonly
                        class="w-36 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 cursor-pointer">
                </div>

                @if(request()->filled('from_date') || request()->filled('to_date'))
                    <a href="{{ route('dashboard.units-detail', array_filter(['type' => $type, 'status' => $status])) }}"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Reset
                    </a>
                @endif

                <a href="{{ route('dashboard', array_filter(['from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                    class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                    ← Back to Dashboard
                </a>
            </form>
        </div>

        {{-- Stats Cards Row (Shown only when viewing all units of this type) --}}
        @if(empty($status))
            <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
                {{-- Total --}}
                <a href="{{ route('dashboard.units-detail', array_filter(['type' => $type, 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                    class="group relative block overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                    style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); min-height: 150px;">
                    <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                    <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                    <div class="relative flex justify-between items-center mb-2">
                        <p class="text-sm font-extrabold uppercase tracking-wider text-white">Total Flats / Shops</p>
                        <span class="text-xl text-1xl">🏢</span>
                    </div>
                    <div class="relative mt-2 space-y-1.5">
                        <div class="flex justify-between items-baseline">
                            <span class="text-xs font-bold uppercase text-white/90">Total Units</span>
                            <span class="font-extrabold text-white text-xl text-1xl">{{ $counts['total'] }}</span>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <span class="text-xs font-bold uppercase text-white/90">Flats</span>
                            <span class="font-extrabold text-white text-xl text-1xl">{{ $counts['flats'] }}</span>
                        </div>
                        <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                            <span class="text-xs font-bold uppercase text-white/90">Shops</span>
                            <span class="font-extrabold text-white text-xl text-1xl">{{ $counts['shops'] }}</span>
                        </div>
                        @if(($counts['offices'] ?? 0) > 0)
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs font-bold uppercase text-white/90">Offices</span>
                                <span class="font-extrabold text-white text-xl text-1xl">{{ $counts['offices'] }}</span>
                            </div>
                        @endif
                    </div>
                </a>
                {{-- Rented (Green) --}}
                <a href="{{ route('dashboard.units-detail', array_filter(['type' => $type, 'status' => 'rented', 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                    class="group relative block overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                    style="background: linear-gradient(135deg, #10b981 0%, #047857 100%); min-height: 150px;">
                    <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                    <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                    <div class="relative flex justify-between items-center mb-2">
                        <p class="text-sm font-extrabold uppercase tracking-wider text-white">Rented Flats / Shops</p>
                        <span class="text-xl text-1xl">🔑</span>
                    </div>
                    <div class="relative mt-2 space-y-1.5">
                        <div class="flex justify-between items-baseline">
                            <span class="text-xs font-bold uppercase text-white/90">Total Rented</span>
                            <span class="font-extrabold text-white text-xl text-1xl">{{ $stats['rented'] }}</span>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <span class="text-xs font-bold uppercase text-white/90">Flats</span>
                            <span class="font-extrabold text-white text-xl text-1xl">{{ $counts['rented_flats'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                            <span class="text-xs font-bold uppercase text-white/90">Shops</span>
                            <span class="font-extrabold text-white text-xl text-1xl">{{ $counts['rented_shops'] ?? 0 }}</span>
                        </div>
                        @if(($counts['rented_offices'] ?? 0) > 0)
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs font-bold uppercase text-white/90">Offices</span>
                                <span class="font-extrabold text-white text-xl text-1xl">{{ $counts['rented_offices'] }}</span>
                            </div>
                        @endif
                    </div>
                </a>
                {{-- Vacant (Red) --}}
                <a href="{{ route('dashboard.units-detail', array_filter(['type' => $type, 'status' => 'vacant', 'from_date' => request('from_date'), 'to_date' => request('to_date')])) }}"
                    class="group relative block overflow-hidden rounded-2xl p-5 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                    style="background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%); min-height: 150px;">
                    <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full opacity-10 bg-white"></div>
                    <div class="absolute -bottom-4 -left-2 h-16 w-16 rounded-full opacity-10 bg-white"></div>

                    <div class="relative flex justify-between items-center mb-2">
                        <p class="text-sm font-extrabold uppercase tracking-wider text-white">Vacant Flats / Shops</p>
                        <span class="text-xl text-1xl">🟢</span>
                    </div>
                    <div class="relative mt-2 space-y-1.5">
                        <div class="flex justify-between items-baseline">
                            <span class="text-xs font-bold uppercase text-white/90">Total Vacant</span>
                            <span class="font-extrabold text-white text-xl text-1xl">{{ $stats['vacant'] }}</span>
                        </div>
                        <div class="flex justify-between items-baseline">
                            <span class="text-xs font-bold uppercase text-white/90">Flats</span>
                            <span class="font-extrabold text-white text-xl text-1xl">{{ $counts['vacant_flats'] ?? 0 }}</span>
                        </div>
                        <div class="flex justify-between items-baseline border-t border-white/10 pt-1.5 mt-1">
                            <span class="text-xs font-bold uppercase text-white/90">Shops</span>
                            <span class="font-extrabold text-white text-xl text-1xl">{{ $counts['vacant_shops'] ?? 0 }}</span>
                        </div>
                        @if(($counts['vacant_offices'] ?? 0) > 0)
                            <div class="flex justify-between items-baseline">
                                <span class="text-xs font-bold uppercase text-white/90">Offices</span>
                                <span class="font-extrabold text-white text-xl text-1xl">{{ $counts['vacant_offices'] }}</span>
                            </div>
                        @endif
                    </div>
                </a>
            </div>
        @endif

        {{-- Grouped Rows (Floor/Block Wise) --}}
        <div class="space-y-8">
            @forelse($grouped as $floorName => $blocks)
                @foreach($blocks as $blockName => $unitsList)
                    <div
                        class="p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs transition-all duration-300 hover:shadow-md">
                        {{-- Row Header --}}
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-5">
                            <h3 class="text-xl text-1xl font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                <span>🏢</span> {{ $floorName }} Floor — {{ $blockName }} Block
                            </h3>
                            <span
                                class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-3 py-1 text-xs font-semibold text-gray-600 dark:text-gray-400">
                                {{ $unitsList->count() }} Flats/Shops
                            </span>
                        </div>

                        {{-- Units Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                            @foreach($unitsList as $unit)
                                @php
                                    $isEffectiveRented = in_array($unit->id, $rentedUnitIds);
                                    $isEffectiveVacant = !$isEffectiveRented;
                                    $cardStyle = $isEffectiveRented
                                        ? 'background: linear-gradient(135deg, #10b981 0%, #047857 100%);'
                                        : 'background: linear-gradient(135deg, #ef4444 0%, #b91c1c 100%);';
                                @endphp
                                <div class="rounded-xl p-4 text-white shadow-md flex flex-col justify-between transition-all duration-200 hover:shadow-xl hover:scale-[1.03]"
                                    style="{{ $cardStyle }}">

                                    {{-- Unit Number --}}
                                    <div class="flex justify-between items-center mb-2">
                                        <span
                                            class="text-xl text-1xl font-black text-white tracking-wide">{{ $unit->unit_number }}</span>
                                        @if($isEffectiveRented)
                                            <span class="inline-flex h-3 w-3 rounded-full bg-white shadow-xs" title="Rented"></span>
                                        @else
                                            <span class="inline-flex h-3 w-3 rounded-full bg-white/90 shadow-xs" title="Vacant"></span>
                                        @endif
                                    </div>

                                    {{-- Type and Area (Single/Double) details --}}
                                    <div class="space-y-1.5">
                                        {{-- Type --}}
                                        <div class="flex items-center gap-1.5">
                                            @if($unit->type === 'shop')
                                                <span class="text-xs font-bold text-white/95">🏢 Shop</span>
                                            @elseif($unit->type === 'flat')
                                                <span class="text-xs font-bold text-white/95">🏠 Flat</span>
                                            @else
                                                <span class="text-xs font-bold text-white/95">💼 Office</span>
                                            @endif
                                        </div>

                                        {{-- Single/Double --}}
                                        <div class="flex items-center gap-1.5">
                                            @if($unit->area && strtolower($unit->area->name) === 'double')
                                                <span
                                                    class="inline-flex items-center rounded-md bg-white/20 px-2 py-0.5 text-xs font-black text-white border border-white/30 backdrop-blur-xs">
                                                    👥 Double
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center rounded-md bg-white/20 px-2 py-0.5 text-xs font-black text-white border border-white/30 backdrop-blur-xs">
                                                    👤 Single
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Status Text Badge --}}
                                    <div class="mt-3 pt-2 border-t border-white/20 flex items-center justify-between">
                                        <span class="text-xs font-black uppercase tracking-wider text-white">
                                            {{ $isEffectiveRented ? 'Rented' : 'Vacant' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @empty
                <div
                    class="p-12 text-center bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
                    <svg class="mx-auto mb-4 h-12 w-12 text-gray-400 opacity-50" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.656 48.656 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7C4.547 9.547 4.5 10.768 4.5 12s.047 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.092-1.209.138-2.43.138-3.662z" />
                    </svg>
                    <p class="text-sm text-gray-500">No flats or shops found for this category.</p>
                </div>
            @endforelse
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#detail_from_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: false,
                    disableMobile: true,
                    onChange: function () {
                        document.getElementById('units-detail-filter-form').submit();
                    }
                });

                flatpickr('#detail_to_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: false,
                    disableMobile: true,
                    onChange: function () {
                        document.getElementById('units-detail-filter-form').submit();
                    }
                });
            }
        });
    </script>
@endpush