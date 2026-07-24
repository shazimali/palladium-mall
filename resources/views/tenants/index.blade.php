@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Tenants" />

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

    <x-common.component-card title="All Tenants" desc="Manage tenant agreements, registrations, and move-outs">

        {{-- Top bar --}}
        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between mb-6">
            {{-- Stats strip --}}
            <div class="flex flex-wrap items-center gap-2 sm:gap-3">
                @php
                    $total    = $counts['total'];
                    $active   = $counts['active'];
                    $inactive = $counts['inactive'];
                    $draft    = $counts['draft'];
                @endphp
                <button type="button" onclick="setStatFilter('status', '')"
                    class="inline-flex items-center gap-2 rounded-xl bg-gray-100 px-4 py-2.5 text-sm sm:text-base font-extrabold text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all cursor-pointer">
                    <span>Total Tenants:</span>
                    <span class="text-base sm:text-lg font-black font-mono">{{ $total }}</span>
                </button>
                <button type="button" onclick="setStatFilter('status', 'active')"
                    class="inline-flex items-center gap-2 rounded-xl bg-green-100 px-4 py-2.5 text-sm sm:text-base font-extrabold text-green-700 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-200 dark:hover:bg-green-900/50 transition-all cursor-pointer">
                    <span>Active:</span>
                    <span class="text-base sm:text-lg font-black font-mono">{{ $active }}</span>
                </button>
                <button type="button" onclick="setStatFilter('status', 'inactive')"
                    class="inline-flex items-center gap-2 rounded-xl bg-red-100 px-4 py-2.5 text-sm sm:text-base font-extrabold text-red-600 dark:bg-red-900/30 dark:text-red-400 hover:bg-red-200 dark:hover:bg-red-900/50 transition-all cursor-pointer">
                    <span>Inactive / Terminated:</span>
                    <span class="text-base sm:text-lg font-black font-mono">{{ $inactive }}</span>
                </button>
                <button type="button" onclick="setStatFilter('status', 'draft')"
                    class="inline-flex items-center gap-2 rounded-xl bg-yellow-100 px-4 py-2.5 text-sm sm:text-base font-extrabold text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400 hover:bg-yellow-200 dark:hover:bg-yellow-900/50 transition-all cursor-pointer">
                    <span>Draft:</span>
                    <span class="text-base sm:text-lg font-black font-mono">{{ $draft }}</span>
                </button>
            </div>

            <div class="flex items-center gap-3">
                @php
                    $hasActiveFilters = request()->anyFilled(['search', 'status', 'landlord_id', 'date_from', 'date_to']);
                @endphp
                <button type="button" id="clear-filters-btn" onclick="clearFilters()"
                    class="rounded-xl border-2 border-gray-300 px-5 py-2.5 text-sm font-extrabold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors cursor-pointer {{ $hasActiveFilters ? '' : 'hidden' }}">
                    Clear
                </button>
                @if(auth()->user()->hasPermission('tenants.create') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('tenants.create') }}"
                        class="inline-flex items-center gap-2.5 rounded-xl bg-brand-600 px-5 py-2.5 text-sm font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Register New Tenant
                    </a>
                @endif
            </div>
        </div>

        <!-- Filters & Search -->
        <div
            class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form id="filter-form" action="{{ route('tenants.index') }}" method="GET"
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
                        placeholder="Search by tenant name, phone, unit..." autocomplete="off"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <!-- Status Filter -->
                <div class="relative">
                    <select name="status" onchange="fetchResults()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive / Terminated</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>

                <!-- Landlord Filter -->
                <div class="relative">
                    <select name="landlord_id" onchange="fetchResults()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        <option value="">All Landlords</option>
                        @foreach($landlords ?? [] as $landlord)
                            <option value="{{ $landlord->id }}" {{ request('landlord_id') == $landlord->id ? 'selected' : '' }}>
                                {{ $landlord->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range Filters -->
                <div class="flex items-center gap-2">
                    <input type="text" id="date_from" name="date_from" value="{{ request('date_from') }}"
                        placeholder="From Date" autocomplete="off" onchange="fetchResults()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-32 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    <span class="text-gray-400">-</span>
                    <input type="text" id="date_to" name="date_to" value="{{ request('date_to') }}"
                        placeholder="To Date" autocomplete="off" onchange="fetchResults()"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-32 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <button type="submit" class="hidden">Submit</button>
            </form>
        </div>

        {{-- DataTable Container --}}
        <div id="table-container" class="transition-opacity duration-200">
            @include('tenants._table')
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
                    console.error('Error fetching tenants search results:', err);
                });
        }

        function setStatFilter(key, val) {
            const form = document.getElementById('filter-form');
            if (!form) return;

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

        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('search-input');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    clearTimeout(ajaxTimeout);
                    ajaxTimeout = setTimeout(fetchResults, 250);
                });
            }

            if (typeof flatpickr !== 'undefined') {
                flatpickr('#date_from', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    onChange: function() { fetchResults(); }
                });
                flatpickr('#date_to', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    onChange: function() { fetchResults(); }
                });
            }

            window.addEventListener('popstate', function () {
                location.reload();
            });
        });
    </script>
@endpush