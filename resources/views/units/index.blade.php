@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Flat / Shop Master" />

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card title="All Flats / Shops" desc="Manage flats and shops — add, edit or update status">

        {{-- Top bar --}}
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between mb-6">
            {{-- Stats strip --}}
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                @php
                    $total    = $counts['total'];
                    $vacant   = $counts['vacant'];
                    $rented   = $counts['rented'];
                    $self     = $counts['self'];
                    $isSelf   = $counts['is_self'];
                @endphp
                <button type="button" onclick="setStatFilter('all', '')"
                    class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm sm:text-base font-extrabold text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all cursor-pointer">
                    <span>Total:</span>
                    <span class="text-base sm:text-lg font-black font-mono">{{ $total }}</span>
                </button>
                <button type="button" onclick="setStatFilter('status', 'rented')"
                    class="inline-flex items-center gap-2 rounded-xl bg-green-100 px-4 py-2.5 text-sm sm:text-base font-extrabold text-green-700 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/50 transition-all cursor-pointer">
                    <span>Rented:</span>
                    <span class="text-base sm:text-lg font-black font-mono">{{ $rented }}</span>
                </button>
                <button type="button" onclick="setStatFilter('status', 'self')"
                    class="inline-flex items-center gap-2 rounded-xl bg-indigo-100 px-4 py-2.5 text-sm sm:text-base font-extrabold text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 hover:bg-indigo-200 dark:hover:bg-indigo-900/50 transition-all cursor-pointer">
                    <span>Other Status:</span>
                    <span class="text-base sm:text-lg font-black font-mono">{{ $self }}</span>
                </button>
                <button type="button" onclick="setStatFilter('is_self', '1')"
                    class="inline-flex items-center gap-2 rounded-xl bg-violet-100 px-4 py-2.5 text-sm sm:text-base font-extrabold text-violet-700 dark:bg-violet-900/30 dark:text-violet-400 hover:bg-violet-200 dark:hover:bg-violet-900/50 transition-all cursor-pointer">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Other-Owned:</span>
                    <span class="text-base sm:text-lg font-black font-mono">{{ $isSelf }}</span>
                </button>
                <button type="button" onclick="setStatFilter('status', 'vacant')"
                    class="inline-flex items-center gap-2 rounded-xl bg-yellow-100 px-4 py-2.5 text-sm sm:text-base font-extrabold text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 hover:bg-yellow-200 dark:hover:bg-yellow-900/50 transition-all cursor-pointer">
                    <span>Vacant:</span>
                    <span class="text-base sm:text-lg font-black font-mono">{{ $vacant }}</span>
                </button>
            </div>

            <div class="flex items-center gap-3">
                @php
                    $hasActiveFilters = request()->anyFilled(['search', 'status', 'type', 'floor_id', 'block_id', 'area_id', 'is_self']);
                @endphp
                <button type="button" id="clear-filters-btn" onclick="clearFilters()"
                    class="rounded-xl border-2 border-gray-300 px-5 py-2.5 text-sm font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors cursor-pointer {{ $hasActiveFilters ? '' : 'hidden' }}">
                    Clear
                </button>
                <a href="{{ route('units.print', request()->all()) }}" target="_blank"
                    class="inline-flex items-center gap-2.5 rounded-xl border-2 border-gray-300 bg-white px-5 py-2.5 text-sm font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-white/5 transition-colors shadow-xs">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                    </svg>
                    Print List
                </a>
                @if(auth()->user()->hasPermission('units.create') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('units.create') }}"
                        class="inline-flex items-center gap-2.5 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Flat/Shop
                    </a>
                @endif
            </div>
        </div>

        <!-- Filters & Search -->
        <div
            class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form id="filter-form" action="{{ route('units.index') }}" method="GET"
                class="flex flex-col gap-4 sm:flex-row sm:items-center" onsubmit="event.preventDefault(); fetchResults();">

                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20"
                            fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                        </svg>
                    </span>
                    <input type="text" name="search" id="search-input" value="{{ request('search') }}"
                        placeholder="Search by unit no, landlord, floor..." autocomplete="off"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <!-- Status Filter -->
                <div class="relative">
                    <select name="status" onchange="fetchResults()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Statuses</option>
                        <option value="vacant" {{ request('status') === 'vacant' ? 'selected' : '' }}>Vacant</option>
                        <option value="rented" {{ request('status') === 'rented' ? 'selected' : '' }}>Rented</option>
                        <option value="self" {{ request('status') === 'self' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div class="relative">
                    <select name="type" onchange="fetchResults()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Types</option>
                        <option value="shop" {{ request('type') === 'shop' ? 'selected' : '' }}>Shop</option>
                        <option value="flat" {{ request('type') === 'flat' ? 'selected' : '' }}>Flat</option>
                    </select>
                </div>

                <!-- Floor Filter -->
                <div class="relative">
                    <select name="floor_id" onchange="fetchResults()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Floors</option>
                        @foreach($floors ?? [] as $floor)
                            <option value="{{ $floor->id }}" {{ request('floor_id') == $floor->id ? 'selected' : '' }}>
                                {{ $floor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Block Filter -->
                <div class="relative">
                    <select name="block_id" onchange="fetchResults()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Blocks</option>
                        @foreach($blocks ?? [] as $block)
                            <option value="{{ $block->id }}" {{ request('block_id') == $block->id ? 'selected' : '' }}>
                                {{ $block->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Area Filter -->
                <div class="relative">
                    <select name="area_id" onchange="fetchResults()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Areas</option>
                        @foreach($areas ?? [] as $area)
                            <option value="{{ $area->id }}" {{ request('area_id') == $area->id ? 'selected' : '' }}>
                                {{ $area->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- External Owner Filter -->
                <div class="relative">
                    <select name="is_self" onchange="fetchResults()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Owners</option>
                        <option value="1" {{ request('is_self') === '1' ? 'selected' : '' }}>Other-Owned</option>
                        <option value="0" {{ request('is_self') === '0' ? 'selected' : '' }}>Managed by PM Mall</option>
                    </select>
                </div>

                <button type="submit" class="hidden">Submit</button>
            </form>
        </div>

        {{-- DataTable Container --}}
        <div id="table-container" class="transition-opacity duration-200">
            @include('units._table')
        </div>
    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        let ajaxTimeout = null;

        function fetchResults() {
            const form = document.getElementById('filter-form');
            if (!form) return;
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({ path: newUrl }, '', newUrl);

            const container = document.getElementById('table-container');
            if (container) container.classList.add('opacity-50');

            const clearBtn = document.getElementById('clear-filters-btn');
            if (clearBtn) {
                const hasFilters = Array.from(formData.values()).some(v => v !== '');
                if (hasFilters) {
                    clearBtn.classList.remove('hidden');
                } else {
                    clearBtn.classList.add('hidden');
                }
            }

            params.append('ajax', '1');

            fetch(`${window.location.pathname}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(res => res.text())
                .then(html => {
                    if (container) {
                        container.classList.remove('opacity-50');
                        container.innerHTML = html;
                    }
                })
                .catch(err => {
                    if (container) container.classList.remove('opacity-50');
                    console.error('Error fetching units search results:', err);
                });
        }

        function setStatFilter(key, val) {
            const form = document.getElementById('filter-form');
            if (!form) return;

            if (key === 'all') {
                Array.from(form.elements).forEach(el => {
                    if (el.name) el.value = '';
                });
                fetchResults();
                return;
            }

            if (key === 'status') {
                const isSelfInput = form.querySelector('[name="is_self"]');
                if (isSelfInput) isSelfInput.value = '';
            } else if (key === 'is_self') {
                const statusInput = form.querySelector('[name="status"]');
                if (statusInput) statusInput.value = '';
            }

            const input = form.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = val;
                fetchResults();
            }
        }

        function clearFilters() {
            const form = document.getElementById('filter-form');
            if (!form) return;
            form.reset();
            Array.from(form.elements).forEach(el => {
                if (el.name) el.value = '';
            });
            fetchResults();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(ajaxTimeout);
                    ajaxTimeout = setTimeout(fetchResults, 250);
                });
            }

            window.addEventListener('popstate', function() {
                location.reload();
            });
        });
    </script>
@endpush