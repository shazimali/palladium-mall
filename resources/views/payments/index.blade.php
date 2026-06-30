@extends('layouts.app')

@section('containerClass', 'max-w-none w-full')

@section('content')
    <x-common.page-breadcrumb pageTitle="" />

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

    {{-- Owner Type Filter Tabs --}}
    <div class="mb-6 flex flex-wrap gap-2">
        @php
            $activeOwner = request('owner_type', '');
        @endphp
        <button type="button" onclick="setOwnerFilter('')"
            class="owner-type-btn inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all {{ $activeOwner === '' ? 'bg-brand-500 text-white shadow-sm' : 'border border-gray-200 bg-white text-gray-600 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300' }}"
            data-owner="">
            <span>💳</span> All Payments
        </button>
        <button type="button" onclick="setOwnerFilter('other')"
            class="owner-type-btn inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all {{ $activeOwner === 'other' ? 'bg-brand-500 text-white shadow-sm' : 'border border-gray-200 bg-white text-gray-600 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300' }}"
            data-owner="other">
            <span>🏠</span> Other-Owned Payments
        </button>
        <button type="button" onclick="setOwnerFilter('pm_mall')"
            class="owner-type-btn inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all {{ $activeOwner === 'pm_mall' ? 'bg-brand-500 text-white shadow-sm' : 'border border-gray-200 bg-white text-gray-600 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300' }}"
            data-owner="pm_mall">
            <span>🏢</span> PM Mall Payments
        </button>

        {{-- Summary cards --}}
        @php
            $monthLabel = request('month') ? Carbon\Carbon::parse(request('month'))->format('F Y') : 'This Month';
        @endphp
        <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-8">
            @php
                $widgetTypes = [
                    'rent' => [
                        'label' => 'Rent',
                        'gradient' => 'linear-gradient(135deg, #465fff 0%, #2a31d8 100%)',
                        'paidColor' => 'text-emerald-300',
                    ],
                    'maintenance' => [
                        'label' => 'Maintenance',
                        'gradient' => 'linear-gradient(135deg, #12b76a 0%, #027a48 100%)',
                        'paidColor' => 'text-emerald-200',
                    ],
                    'security_deposit' => [
                        'label' => 'Security Deposit',
                        'gradient' => 'linear-gradient(135deg, #6366f1 0%, #312e81 100%)',
                        'paidColor' => 'text-emerald-200',
                    ],
                    'fine' => [
                        'label' => 'Fine',
                        'gradient' => 'linear-gradient(135deg, #f04438 0%, #912018 100%)',
                        'paidColor' => 'text-emerald-200',
                    ],
                    'electricity' => [
                        'label' => 'Electricity',
                        'gradient' => 'linear-gradient(135deg, #f79009 0%, #b54708 100%)',
                        'paidColor' => 'text-emerald-200',
                    ],
                    'water' => [
                        'label' => 'Water',
                        'gradient' => 'linear-gradient(135deg, #06b6d4 0%, #0891b2 100%)',
                        'paidColor' => 'text-emerald-200',
                    ],
                    'gas' => [
                        'label' => 'Gas',
                        'gradient' => 'linear-gradient(135deg, #a855f7 0%, #701a75 100%)',
                        'paidColor' => 'text-emerald-200',
                    ],
                    'other' => [
                        'label' => 'Other',
                        'gradient' => 'linear-gradient(135deg, #64748b 0%, #334155 100%)',
                        'paidColor' => 'text-emerald-200',
                    ],
                ];
            @endphp
            @foreach($widgetTypes as $key => $cfg)
                <div class="group relative overflow-hidden rounded-2xl p-4 text-white shadow-lg transition-all duration-300 hover:-translate-y-1 hover:shadow-xl flex flex-col justify-between"
                    style="background: {{ $cfg['gradient'] }}; min-height: 110px;">
                    <div class="absolute -right-4 -top-4 h-16 w-16 rounded-full opacity-10 bg-white"></div>
                    <div class="absolute -bottom-4 -left-2 h-12 w-12 rounded-full opacity-10 bg-white"></div>

                    <div class="relative">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-white/75">{{ $cfg['label'] }}</p>
                        <p class="mt-1 text-base font-extrabold text-white" id="widget-{{ $key }}-unpaid">
                            Rs. {{ number_format($summary[$key . '_unpaid']) }}
                        </p>
                    </div>
                    <div class="relative mt-3 space-y-2 text-white/80 border-t border-white/10 pt-2">
                        <div>
                            <span class="block text-[8px] uppercase tracking-wider text-white/60">Total</span>
                            <span class="font-bold text-white text-xs sm:text-[13px]" id="widget-{{ $key }}-due">
                                Rs. {{ number_format($summary[$key . '_due']) }}
                            </span>
                        </div>
                        <div>
                            <span class="block text-[8px] uppercase tracking-wider text-white/60">Paid</span>
                            <span class="font-bold {{ $cfg['paidColor'] }} text-xs sm:text-[13px]" id="widget-{{ $key }}-paid">
                                Rs. {{ number_format($summary[$key . '_paid']) }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @php
            $cardTitle = match (request('owner_type')) {
                'pm_mall' => 'PM Mall Payments',
                'other' => 'Other-Owned Payments',
                default => 'All Payments',
            };
        @endphp
        <x-common.component-card :title="$cardTitle" desc="Track rent, maintenance and fine payments">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap gap-2 items-center">
                    <span
                        class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-theme-xs">
                        Total: <span id="badge-total-count"
                            class="ml-1.5 rounded-full bg-brand-50 px-2 py-0.5 text-xs font-bold text-brand-600 dark:bg-brand-950/50 dark:text-brand-400">{{ $payments->total() }}</span>
                    </span>
                </div>
                @php
                    $today = \Carbon\Carbon::now()->startOfMonth()->toDateString();
                    $hasActiveFilters = request()->filled('search')
                        || request()->filled('status')
                        || request()->filled('type')
                        || request()->filled('unit_id')
                        || (request()->filled('month') && request('month') !== $today);
                @endphp
                <div class="flex items-center gap-2">
                    <button type="button" id="clear-filters-btn" onclick="clearFilters()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors {{ $hasActiveFilters ? '' : 'hidden' }}">
                        Clear
                    </button>
                    {{-- Bulk Generate button --}}
                    @if(auth()->user()->hasPermission('payments.create') || auth()->user()->isSuperAdmin())
                        <button type="button" x-data @click="$dispatch('open-bulk-generate')"
                            class="inline-flex items-center gap-2 rounded-lg border border-brand-500 px-4 py-2 text-sm font-medium text-brand-500 hover:bg-brand-50 dark:hover:bg-brand-900/20 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            Bulk Generate
                        </button>

                        @if(auth()->user()->hasPermission('payments.bulk-generate') || auth()->user()->isSuperAdmin())
                            <button type="button" x-data @click="$dispatch('open-bulk-edit')"
                                class="inline-flex items-center gap-2 rounded-lg border border-amber-600 px-4 py-2 text-sm font-medium text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/20 transition-colors">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Bulk Edit
                            </button>
                        @endif

                        <a href="{{ route('payments.utilities.create') }}"
                            class="inline-flex items-center gap-2 rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.03] transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Record Utility
                        </a>

                        <a href="{{ route('payments.create') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Add Payment
                        </a>
                    @endif
                </div>
            </div>
            <!-- Filters & Search -->
            <div
                class="my-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <form id="filter-form" action="{{ route('payments.index') }}" method="GET"
                    class="flex flex-col gap-4 sm:flex-row sm:items-center" onsubmit="event.preventDefault();">
                    <input type="hidden" name="owner_type" id="owner-type-filter" value="{{ request('owner_type') }}">

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
                            placeholder="Search tenant, unit, ref..." autocomplete="off"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <!-- Unit Filter -->
                    <div class="relative">
                        <select name="unit_id" onchange="fetchResults()"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Units</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->unit_number }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="relative">
                        <select name="status" onchange="fetchResults()"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Statuses</option>
                            <option value="unpaid" {{ request('status') === 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                            <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Partial</option>
                            <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div class="relative">
                        <select name="type" onchange="fetchResults()"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <option value="">All Types</option>
                            <option value="rent" {{ request('type') === 'rent' ? 'selected' : '' }}>Rent</option>
                            <option value="maintenance" {{ request('type') === 'maintenance' ? 'selected' : '' }}>Maintenance
                            </option>
                            <option value="security_deposit" {{ request('type') === 'security_deposit' ? 'selected' : '' }}>
                                Security Deposit</option>
                            <option value="fine" {{ request('type') === 'fine' ? 'selected' : '' }}>Fine</option>
                            <option value="electricity" {{ request('type') === 'electricity' ? 'selected' : '' }}>Electricity
                            </option>
                            <option value="water" {{ request('type') === 'water' ? 'selected' : '' }}>Water</option>
                            <option value="gas" {{ request('type') === 'gas' ? 'selected' : '' }}>Gas</option>
                            <option value="other" {{ request('type') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- Month & Year Filter Datepicker -->
                    <div class="relative max-w-[180px]">
                        <input type="text" id="filter_month" name="month"
                            value="{{ request('month', \Carbon\Carbon::now()->startOfMonth()->toDateString()) }}"
                            placeholder="Select Month/Year" autocomplete="off"
                            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    </div>

                    <button type="submit" class="hidden">Submit</button>
                </form>
            </div>

            <div id="table-container" class="transition-opacity duration-200">
                @include('payments._table')
            </div>
        </x-common.component-card>


        {{-- Bulk Generate Modal --}}
        <div x-data="{
                                                                                show: false,
                                                                                init() {
                                                                                    window.addEventListener('open-bulk-generate', () => { this.show = true; });
                                                                                }
                                                                            }" x-show="show" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900"
                @click.outside="if (document.body.contains($event.target) && !$event.target.closest('.flatpickr-calendar')) show = false">
                <h3 class="mb-1 text-base font-semibold text-gray-800 dark:text-white">Bulk Generate Payments</h3>
                <p class="mb-4 text-sm text-gray-500">Creates payment records for all active tenants with active agreements.
                </p>
                <form action="{{ route('payments.bulk-generate') }}" method="POST">
                    @csrf
                    <div class="space-y-4">

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Billing Month <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="bulk_month" name="month" placeholder="Select month" autocomplete="off"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>

                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="bulk_due_date" name="due_date" placeholder="Select due date"
                                autocomplete="off"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        </div>

                        <div>
                            <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Generate For <span class="text-red-500">*</span>
                            </label>
                            <div class="flex flex-col gap-3">
                                <div class="flex items-center gap-4">
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="types[]" value="rent" checked
                                            class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                        Rent
                                    </label>
                                    <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="types[]" value="maintenance"
                                            class="rounded border-gray-300 text-brand-500 focus:ring-brand-500">
                                        Maintenance
                                    </label>
                                </div>

                                {{-- External owner units note --}}
                                <div
                                    class="rounded-lg border border-blue-200 bg-blue-50/50 px-3.5 py-2.5 dark:border-blue-900/40 dark:bg-blue-950/10">
                                    <p class="text-xs text-blue-700 dark:text-blue-300">
                                        <strong>Note:</strong> Other-owned units with active maintenance charges are
                                        automatically included when generating <strong>Maintenance</strong> payments.
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-5 flex items-center gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                            Generate Now
                        </button>
                        <button type="button" @click="show = false"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-colors">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Bulk Edit Modal ── --}}
        @if(auth()->user()->hasPermission('payments.bulk-generate') || auth()->user()->isSuperAdmin())
            <div x-data="{
                                                                                                                                                show: false,
                                                                                                                                                init() {
                                                                                                                                                    window.addEventListener('open-bulk-edit', () => { this.show = true; });
                                                                                                                                                }
                                                                                                                                            }"
                x-show="show" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl dark:bg-gray-900"
                    @click.outside="if (document.body.contains($event.target) && !$event.target.closest('.flatpickr-calendar')) show = false">
                    <h3 class="mb-1 text-base font-semibold text-gray-800 dark:text-white">Bulk Edit Payments</h3>
                    <p class="mb-4 text-sm text-gray-500">Correct the Month/Year or Due Date of bulk payments in one batch.</p>
                    <form action="{{ route('payments.bulk-edit') }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to bulk edit matching unpaid payments?')">
                        @csrf
                        <div class="space-y-4">

                            {{-- Section: Source filter criteria --}}
                            <div class="border-b border-gray-250 pb-4 dark:border-gray-800">
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">1.
                                    Target Wrong Month/Type</span>

                                <div class="mt-3">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Incorrect Generated Month <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" id="bulk_edit_source_month" name="source_month"
                                        placeholder="Select month to fix" autocomplete="off" required
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>

                                <div class="mt-3">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Payment Type <span class="text-red-500">*</span>
                                    </label>
                                    <select name="type" required
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                        <option value="all">Both (Rent & Maintenance)</option>
                                        <option value="rent">Rent Only</option>
                                        <option value="maintenance">Maintenance Only</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Section: Target new values --}}
                            <div>
                                <span
                                    class="text-xs font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">2.
                                    Change To Correct Values</span>

                                <div class="mt-3">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        New Month/Year (Optional)
                                    </label>
                                    <input type="text" id="bulk_edit_target_month" name="target_month"
                                        placeholder="Leave empty if unchanged" autocomplete="off"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>

                                <div class="mt-3">
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        New Due Date (Optional)
                                    </label>
                                    <input type="text" id="bulk_edit_target_due_date" name="target_due_date"
                                        placeholder="Leave empty if unchanged" autocomplete="off"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                </div>
                            </div>

                            <div
                                class="rounded-lg border border-amber-200 bg-amber-50/50 px-3.5 py-2.5 dark:border-amber-900/40 dark:bg-amber-950/10">
                                <p class="text-xs text-amber-700 dark:text-amber-300">
                                    <strong>Note:</strong> This bulk update will only affect matching payments with
                                    <strong>Unpaid</strong> status to preserve accounting records.
                                </p>
                            </div>

                        </div>

                        <div class="mt-5 flex items-center gap-3">
                            <button type="submit"
                                class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-medium text-white hover:bg-amber-700 transition-colors">
                                Update Payments
                            </button>
                            <button type="button" @click="show = false"
                                class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-colors">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif

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

                // Toggle clear button
                const clearBtn = document.getElementById('clear-filters-btn');
                if (clearBtn) {
                    const today = new Date();
                    const currentMonthStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-01';

                    const hasFilters = params.get('search')
                        || params.get('status')
                        || params.get('type')
                        || params.get('unit_id')
                        || (params.get('month') && params.get('month') !== currentMonthStr);

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
                        updateBadgeCount();
                    })
                    .catch(err => {
                        if (container) container.classList.remove('opacity-50');
                        console.error('Error fetching search results:', err);
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
                        btn.className = 'owner-type-btn inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all bg-brand-500 text-white shadow-sm';
                    } else {
                        btn.className = 'owner-type-btn inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-medium transition-all border border-gray-200 bg-white text-gray-600 hover:border-brand-400 hover:text-brand-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300';
                    }
                });
            }

            function updateBadgeCount() {
                const meta = document.getElementById('ajax-paginator-meta');
                if (meta) {
                    const total = meta.getAttribute('data-total');
                    const totalBadge = document.getElementById('badge-total-count');
                    if (totalBadge && total !== null) totalBadge.innerText = total;

                    const types = ['rent', 'maintenance', 'fine', 'electricity', 'water', 'gas', 'other', 'security_deposit'];
                    types.forEach(key => {
                        const dueVal = meta.getAttribute(`data-${key}-due`);
                        const paidVal = meta.getAttribute(`data-${key}-paid`);
                        const unpaidVal = meta.getAttribute(`data-${key}-unpaid`);

                        const dueEl = document.getElementById(`widget-${key}-due`);
                        const paidEl = document.getElementById(`widget-${key}-paid`);
                        const unpaidEl = document.getElementById(`widget-${key}-unpaid`);

                        if (dueEl && dueVal !== null) dueEl.innerText = dueVal;
                        if (paidEl && paidVal !== null) paidEl.innerText = paidVal;
                        if (unpaidEl && unpaidVal !== null) unpaidEl.innerText = unpaidVal;
                    });
                }
            }

            function clearFilters() {
                const form = document.getElementById('filter-form');
                if (form) {
                    form.reset();

                    const searchInput = document.getElementById('search-input');
                    if (searchInput) searchInput.value = '';

                    const ownerInput = document.getElementById('owner-type-filter');
                    if (ownerInput) ownerInput.value = '';

                    const unitSelect = form.querySelector('select[name="unit_id"]');
                    if (unitSelect) unitSelect.value = '';

                    const statusSelect = form.querySelector('select[name="status"]');
                    if (statusSelect) statusSelect.value = '';

                    const typeSelect = form.querySelector('select[name="type"]');
                    if (typeSelect) typeSelect.value = '';

                    const monthInput = document.getElementById('filter_month');
                    if (monthInput) {
                        const today = new Date();
                        const currentMonthStr = today.getFullYear() + '-' + String(today.getMonth() + 1).padStart(2, '0') + '-01';
                        monthInput.value = currentMonthStr;
                        if (monthInput._flatpickr) monthInput._flatpickr.setDate(currentMonthStr);
                    }
                }

                const clearBtn = document.getElementById('clear-filters-btn');
                if (clearBtn) clearBtn.classList.add('hidden');

                fetchResults();
            }

            document.addEventListener('DOMContentLoaded', function () {
                // Initial active state update for owner type tabs
                updateOwnerTabsActiveState();

                // Search input typing debounce listener
                const searchInput = document.getElementById('search-input');
                if (searchInput) {
                    searchInput.addEventListener('input', function () {
                        clearTimeout(ajaxTimeout);
                        ajaxTimeout = setTimeout(fetchResults, 300);
                    });
                }

                // Pagination link click delegation
                document.addEventListener('click', function (e) {
                    const link = e.target.closest('#table-container .pagination a');
                    if (link) {
                        e.preventDefault();
                        const url = new URL(link.href);
                        const params = new URLSearchParams(url.search);

                        // Sync current input states to query parameters
                        const form = document.getElementById('filter-form');
                        if (form) {
                            const searchVal = document.getElementById('search-input')?.value;
                            if (searchVal) params.set('search', searchVal);

                            const ownerVal = document.getElementById('owner-type-filter')?.value;
                            if (ownerVal) params.set('owner_type', ownerVal);

                            const unitVal = form.querySelector('select[name="unit_id"]')?.value;
                            if (unitVal) params.set('unit_id', unitVal);

                            const statusVal = form.querySelector('select[name="status"]')?.value;
                            if (statusVal) params.set('status', statusVal);

                            const typeVal = form.querySelector('select[name="type"]')?.value;
                            if (typeVal) params.set('type', typeVal);

                            const monthVal = document.getElementById('filter_month')?.value;
                            if (monthVal) params.set('month', monthVal);
                        }

                        const newUrl = `${window.location.pathname}?${params.toString()}`;
                        window.history.pushState({ path: newUrl }, '', newUrl);

                        const container = document.getElementById('table-container');
                        if (container) container.classList.add('opacity-50');

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
                                updateBadgeCount();
                                window.scrollTo({ top: document.getElementById('table-container').offsetTop - 100, behavior: 'smooth' });
                            })
                            .catch(err => {
                                if (container) container.classList.remove('opacity-50');
                                console.error('Error fetching paginated results:', err);
                            });
                    }
                });

                flatpickr('#filter_month', {
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
                    onChange: function (selectedDates, dateStr, instance) {
                        fetchResults();
                    }
                });

                flatpickr('#bulk_month', {
                    dateFormat: 'Y-m-01',
                    altInput: true,
                    altFormat: 'F Y',
                    allowInput: false,
                    disableMobile: true,
                    static: true,
                    plugins: [
                        new monthSelectPlugin({
                            shorthand: false,
                            dateFormat: 'Y-m-01',
                            altFormat: 'F Y',
                            theme: 'light',
                        })
                    ],
                });

                flatpickr('#bulk_due_date', {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    disableMobile: true,
                    static: true,
                });

                flatpickr('#bulk_edit_source_month', {
                    dateFormat: 'Y-m-01',
                    altInput: true,
                    altFormat: 'F Y',
                    allowInput: false,
                    disableMobile: true,
                    static: true,
                    plugins: [
                        new monthSelectPlugin({
                            shorthand: false,
                            dateFormat: 'Y-m-01',
                            altFormat: 'F Y',
                            theme: 'light',
                        })
                    ],
                });

                flatpickr('#bulk_edit_target_month', {
                    dateFormat: 'Y-m-01',
                    altInput: true,
                    altFormat: 'F Y',
                    allowInput: false,
                    disableMobile: true,
                    static: true,
                    plugins: [
                        new monthSelectPlugin({
                            shorthand: false,
                            dateFormat: 'Y-m-01',
                            altFormat: 'F Y',
                            theme: 'light',
                        })
                    ],
                });

                flatpickr('#bulk_edit_target_due_date', {
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    disableMobile: true,
                    static: true,
                });
            });
        </script>
    @endpush