@extends('layouts.app')

@section('containerClass', 'max-w-none w-full')

@section('content')
    <x-common.page-breadcrumb pageTitle="Billing & Payment History" />

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

    {{-- Filters & Options Header --}}
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between bg-white p-4 dark:bg-white/[0.03] rounded-2xl border border-gray-200 dark:border-gray-800 shadow-theme-xs">
        
        <!-- Filters Form -->
        <form id="filter-form" action="{{ route('payments.history') }}" method="GET"
            class="flex flex-wrap items-center gap-3" onsubmit="event.preventDefault();">
            <input type="hidden" name="owner_type" id="owner-type-filter" value="{{ request('owner_type') }}">

            <!-- Year Filter -->
            <div class="relative">
                <select name="year" onchange="fetchResults()"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 font-semibold">
                    @foreach($years as $y)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>
                            Year: {{ $y }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Unit Filter -->
            <div class="relative">
                <select name="unit_id" onchange="fetchResults()"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">All Units</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                            Unit: {{ $unit->unit_number }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Owner Type tabs inside form container -->
            <div class="flex rounded-lg border border-gray-200 dark:border-gray-800 p-0.5 bg-gray-50 dark:bg-gray-900">
                @php
                    $activeOwner = request('owner_type', '');
                @endphp
                <button type="button" onclick="setOwnerFilter('')"
                    class="owner-type-btn rounded-md px-3 py-1.5 text-xs font-semibold transition-all {{ $activeOwner === '' ? 'bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}"
                    data-owner="">
                    All
                </button>
                <button type="button" onclick="setOwnerFilter('other')"
                    class="owner-type-btn rounded-md px-3 py-1.5 text-xs font-semibold transition-all {{ $activeOwner === 'other' ? 'bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}"
                    data-owner="other">
                    Other-Owned
                </button>
                <button type="button" onclick="setOwnerFilter('pm_mall')"
                    class="owner-type-btn rounded-md px-3 py-1.5 text-xs font-semibold transition-all {{ $activeOwner === 'pm_mall' ? 'bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 shadow-xs' : 'text-gray-500 hover:text-gray-900 dark:hover:text-white' }}"
                    data-owner="pm_mall">
                    PM Mall
                </button>
            </div>

            @php
                $hasActiveFilters = request()->filled('unit_id')
                    || request()->filled('owner_type')
                    || (request()->filled('year') && (int)request('year') !== (int)\Carbon\Carbon::now()->year);
            @endphp
            <button type="button" id="clear-filters-btn" onclick="clearFilters()"
                class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors {{ $hasActiveFilters ? '' : 'hidden' }}">
                Clear
            </button>
        </form>

        <a href="{{ route('payments.index') }}"
            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors shadow-theme-xs shrink-0 self-start lg:self-center">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Monthly View
        </a>
    </div>

    {{-- Widgets Container --}}
    <div id="widgets-container" class="transition-opacity duration-200">
        @include('payments.partials.history_widgets')
    </div>
@endsection

@push('scripts')
    <script>
        function fetchResults() {
            const form = document.getElementById('filter-form');
            if (!form) return;
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            const newUrl = `${window.location.pathname}?${params.toString()}`;
            window.history.pushState({ path: newUrl }, '', newUrl);

            const container = document.getElementById('widgets-container');
            if (container) container.classList.add('opacity-50');

            // Toggle clear button visibility
            const clearBtn = document.getElementById('clear-filters-btn');
            if (clearBtn) {
                const currentYear = new Date().getFullYear();
                const hasFilters = params.get('unit_id')
                    || params.get('owner_type')
                    || (params.get('year') && parseInt(params.get('year')) !== currentYear);

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
                    updateOwnerTabsActiveState();
                })
                .catch(err => {
                    if (container) container.classList.remove('opacity-50');
                    console.error('Error fetching billing history:', err);
                });
        }

        function setOwnerFilter(ownerType) {
            const input = document.getElementById('owner-type-filter');
            if (input) input.value = ownerType;
            fetchResults();
        }

        function updateOwnerTabsActiveState() {
            const input = document.getElementById('owner-type-filter');
            const currentOwner = input ? input.value : '';
            const buttons = document.querySelectorAll('.owner-type-btn');

            buttons.forEach(btn => {
                const owner = btn.getAttribute('data-owner');
                if (owner === currentOwner) {
                    btn.className = 'owner-type-btn rounded-md px-3 py-1.5 text-xs font-semibold transition-all bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 shadow-xs';
                } else {
                    btn.className = 'owner-type-btn rounded-md px-3 py-1.5 text-xs font-semibold transition-all text-gray-500 hover:text-gray-900 dark:hover:text-white';
                }
            });
        }

        function clearFilters() {
            const form = document.getElementById('filter-form');
            if (form) {
                form.reset();

                const ownerInput = document.getElementById('owner-type-filter');
                if (ownerInput) ownerInput.value = '';

                const unitSelect = form.querySelector('select[name="unit_id"]');
                if (unitSelect) unitSelect.value = '';

                // Reset year filter to current year
                const currentYear = new Date().getFullYear();
                const yearSelect = form.querySelector('select[name="year"]');
                if (yearSelect) yearSelect.value = currentYear;
            }

            const clearBtn = document.getElementById('clear-filters-btn');
            if (clearBtn) clearBtn.classList.add('hidden');

            fetchResults();
        }
    </script>
@endpush
