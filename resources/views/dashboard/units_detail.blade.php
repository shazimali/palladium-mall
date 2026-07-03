@extends('layouts.app')

@section('content')
    <div class="space-y-8">

        {{-- Breadcrumbs --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('dashboard') }}" class="hover:text-brand-500 transition-colors">Dashboard</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Detail List</span>
        </div>

        {{-- Page Header --}}
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-extrabold text-gray-800 dark:text-white/90">
                    {{ $typeLabel }} Flats & Shops
                </h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                    Floor and block-wise distribution list of all flats and shops.
                </p>
            </div>
            <a href="{{ route('dashboard') }}"
                class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800 transition-colors">
                ← Back to Dashboard
            </a>
        </div>

        {{-- Stats Cards Row --}}
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
                    <span class="text-3xl font-extrabold text-white">{{ $stats['total'] }}</span>
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
                    <span class="text-3xl font-extrabold text-white">{{ $stats['rented'] }}</span>
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
                    <span class="text-3xl font-extrabold text-white">{{ $stats['vacant'] }}</span>
                    <span class="text-xs opacity-75 ml-1">Flats/Shops</span>
                </div>
            </div>
        </div>

        {{-- Grouped Rows (Floor/Block Wise) --}}
        <div class="space-y-8">
            @forelse($grouped as $floorName => $blocks)
                @foreach($blocks as $blockName => $unitsList)
                    <div class="p-6 bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs transition-all duration-300 hover:shadow-md">
                        {{-- Row Header --}}
                        <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-800 pb-3 mb-5">
                            <h3 class="text-base font-bold text-gray-800 dark:text-white flex items-center gap-2">
                                <span>🏢</span> {{ $floorName }} Floor — {{ $blockName }} Block
                            </h3>
                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-3 py-1 text-xs font-semibold text-gray-600 dark:text-gray-400">
                                {{ $unitsList->count() }} Flats/Shops
                            </span>
                        </div>

                        {{-- Units Grid --}}
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 xl:grid-cols-8 gap-4">
                            @foreach($unitsList as $unit)
                                <div class="rounded-xl border p-4 bg-white dark:bg-white/[0.02] shadow-xs flex flex-col justify-between transition-all duration-200 hover:shadow-md hover:scale-[1.02]"
                                    style="border-color: {{ $unit->status === 'rented' ? '#fca5a5' : ($unit->status === 'vacant' ? '#6ee7b7' : '#d1d5db') }};
                                           background-color: {{ $unit->status === 'rented' ? 'rgba(254, 226, 226, 0.15)' : ($unit->status === 'vacant' ? 'rgba(209, 250, 229, 0.15)' : 'transparent') }};">
                                    
                                    {{-- Unit Number --}}
                                    <div class="flex justify-between items-center mb-2">
                                        <span class="text-sm font-extrabold text-gray-900 dark:text-white">{{ $unit->unit_number }}</span>
                                        @if($unit->status === 'rented')
                                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-red-500" title="Rented"></span>
                                        @elseif($unit->status === 'vacant')
                                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500" title="Vacant"></span>
                                        @else
                                            <span class="inline-flex h-2.5 w-2.5 rounded-full bg-gray-400" title="Other"></span>
                                        @endif
                                    </div>

                                    {{-- Type and Area (Single/Double) details --}}
                                    <div class="space-y-1.5">
                                        {{-- Type --}}
                                        <div class="flex items-center gap-1.5">
                                            @if($unit->type === 'shop')
                                                <span class="text-xs text-gray-500 dark:text-gray-400">🏢 Shop</span>
                                            @elseif($unit->type === 'flat')
                                                <span class="text-xs text-gray-500 dark:text-gray-400">🏠 Flat</span>
                                            @else
                                                <span class="text-xs text-gray-500 dark:text-gray-400">💼 Office</span>
                                            @endif
                                        </div>

                                        {{-- Single/Double --}}
                                        <div class="flex items-center gap-1.5">
                                            @if($unit->area && strtolower($unit->area->name) === 'double')
                                                <span class="inline-flex items-center rounded-md bg-purple-50 dark:bg-purple-950/20 px-1.5 py-0.5 text-[10px] font-bold text-purple-700 dark:text-purple-400 border border-purple-200/50 dark:border-purple-900/30">
                                                    👥 Double
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-md bg-blue-50 dark:bg-blue-950/20 px-1.5 py-0.5 text-[10px] font-bold text-blue-700 dark:text-blue-400 border border-blue-200/50 dark:border-blue-900/30">
                                                    👤 Single
                                                </span>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Status Text Badge --}}
                                    <div class="mt-3 pt-2.5 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between">
                                        <span class="text-[10px] font-bold uppercase tracking-wider
                                            {{ $unit->status === 'rented' ? 'text-red-600 dark:text-red-400' : ($unit->status === 'vacant' ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-500') }}">
                                            {{ ucfirst($unit->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @empty
                <div class="p-12 text-center bg-white dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
                    <svg class="mx-auto mb-4 h-12 w-12 text-gray-400 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12c0-1.232-.046-2.453-.138-3.662a4.006 4.006 0 00-3.7-3.7 48.656 48.656 0 00-7.324 0 4.006 4.006 0 00-3.7 3.7C4.547 9.547 4.5 10.768 4.5 12s.047 2.453.138 3.662a4.006 4.006 0 003.7 3.7 48.656 48.656 0 007.324 0 4.006 4.006 0 003.7-3.7c.092-1.209.138-2.43.138-3.662z" />
                    </svg>
                    <p class="text-sm text-gray-500">No flats or shops found for this category.</p>
                </div>
            @endforelse
        </div>

    </div>
@endsection
