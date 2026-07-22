@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Receivables & Payables Summary" />

    <form action="{{ route('reports.receivables-payables') }}" method="GET" id="report-filter-form" class="space-y-6">

        {{-- Segmented Tab Switcher --}}
        <div class="flex flex-col items-center gap-3">
            <div
                class="inline-flex rounded-xl p-1.5 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="receivables" class="sr-only"
                        onchange="document.querySelectorAll('input[name=\'categories[]\']').forEach(el => el.remove()); this.form.submit()"
                        {{ $type === 'receivables' ? 'checked' : '' }}>
                    <span
                        class="inline-flex items-center px-6 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 {{ $type === 'receivables' ? 'bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        Due Receivables
                    </span>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="payables" class="sr-only"
                        onchange="document.querySelectorAll('input[name=\'categories[]\']').forEach(el => el.remove()); this.form.submit()"
                        {{ $type === 'payables' ? 'checked' : '' }}>
                    <span
                        class="inline-flex items-center px-6 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 {{ $type === 'payables' ? 'bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        Due Payables
                    </span>
                </label>
            </div>

            {{-- Secondary Sub-Tabs for Receivables (PM Mall vs Other Receivables) --}}
            @if($type === 'receivables')
                <input type="hidden" name="receivable_scope" id="receivable_scope_input" value="{{ $receivableScope }}">
                <div
                    class="inline-flex rounded-xl p-1 bg-gray-200/80 dark:bg-gray-800 border border-gray-300/60 dark:border-gray-700/60 shadow-inner">
                    <button type="button"
                        onclick="document.getElementById('receivable_scope_input').value='pm_mall'; document.getElementById('report-filter-form').submit();"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer {{ $receivableScope === 'pm_mall' ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-sm ring-1 ring-brand-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                        <span>🏢 PM Mall Receivables</span>
                        <span
                            class="rounded-full px-2 py-0.5 text-[10px] font-extrabold font-mono {{ $receivableScope === 'pm_mall' ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300' : 'bg-gray-300/60 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                            Rs. {{ number_format($pmMallReceivablesNet, 0) }}
                        </span>
                    </button>
                    <button type="button"
                        onclick="document.getElementById('receivable_scope_input').value='other'; document.getElementById('report-filter-form').submit();"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer {{ $receivableScope === 'other' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm ring-1 ring-amber-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                        <span>🏘️ Other Receivables (Not Managed by PM Mall)</span>
                        <span
                            class="rounded-full px-2 py-0.5 text-[10px] font-extrabold font-mono {{ $receivableScope === 'other' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-gray-300/60 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                            Rs. {{ number_format($otherReceivablesNet, 0) }}
                        </span>
                    </button>
                </div>
            @endif
        </div>

        {{-- Filters & Options — all inline --}}
        <x-common.component-card>
            @php
                $filterInput = 'shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-xl border border-gray-300 bg-transparent px-3 py-1.5 text-xs text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
            @endphp

            <div class="flex flex-wrap items-center gap-3">

                {{-- Date From --}}
                <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="From Date"
                    autocomplete="off" class="{{ $filterInput }} w-36">

                {{-- Date To --}}
                <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="To Date"
                    autocomplete="off" class="{{ $filterInput }} w-36">

                {{-- Divider --}}
                <span class="h-6 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></span>

                {{-- Multi-Select Category Dropdown --}}
                @php
                    $catMap = $type === 'receivables' ? [
                        'Tenant Rent' => 'Rent',
                        'Tenant Maintenance' => 'Maintenance',
                        'Tenant Extra' => 'Extra Payments',
                        'Tenant Fine' => 'Fines',
                        'Tenant Utilities' => 'Utilities',
                        'Tenant Other' => 'Others',
                        'Landlord Credit' => 'Landlord Credits',
                        'Party Receivable' => 'Party Receivables',
                    ] : [
                        'Tenant Security Deposit' => 'Security Deposits',
                        'Party Payable' => 'Party Payables',
                        'Landlord Payable' => 'Landlord Payables',
                    ];
                @endphp

                <div x-data="{
                    open: false,
                    selected: @js(empty($categories) ? array_keys($catMap) : $categories),
                    options: @js($catMap),
                    selectAll() {
                        this.selected = Object.keys(this.options);
                    },
                    clearAll() {
                        this.selected = [];
                    },
                    removeTag(val) {
                        this.selected = this.selected.filter(v => v !== val);
                    },
                    get displayText() {
                        const total = Object.keys(this.options).length;
                        if (this.selected.length === 0) return 'Select Categories';
                        if (this.selected.length === total) return 'All Categories';
                        if (this.selected.length === 1) return this.options[this.selected[0]];
                        return this.selected.length + ' Categories Selected';
                    }
                }" class="relative" @click.away="open = false">

                    {{-- Hidden inputs for form submit --}}
                    <template x-for="val in selected" :key="val">
                        <input type="hidden" name="categories[]" :value="val">
                    </template>

                    {{-- Dropdown Trigger Button --}}
                    <button type="button" @click="open = !open"
                        class="{{ $filterInput }} inline-flex items-center justify-between gap-2.5 min-w-48 cursor-pointer bg-white dark:bg-gray-900 shadow-xs hover:border-brand-300">
                        <div class="flex items-center gap-2 truncate">
                            <svg class="h-4 w-4 text-gray-400 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.707 7.293A1 1 0 013.414 6.586V4z" />
                            </svg>
                            <span class="truncate font-semibold text-gray-800 dark:text-white/90"
                                x-text="displayText"></span>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0">
                            <span x-show="selected.length > 0 && selected.length < Object.keys(options).length"
                                class="rounded-full bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300 px-1.5 py-0.5 text-[10px] font-extrabold font-mono"
                                x-text="selected.length" style="display: none;"></span>
                            <svg class="h-3.5 w-3.5 text-gray-400 transition-transform duration-200"
                                :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </button>

                    {{-- Dropdown Menu Panel --}}
                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute left-0 z-50 mt-1.5 w-64 rounded-xl border border-gray-200 bg-white p-2 shadow-xl dark:border-gray-800 dark:bg-gray-900"
                        style="display: none;">

                        {{-- Top Bar Actions --}}
                        <div
                            class="flex items-center justify-between px-2.5 py-1.5 border-b border-gray-100 dark:border-gray-800 mb-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Filter
                                Categories</span>
                            <div class="flex items-center gap-2 text-[11px] font-semibold">
                                <button type="button" @click="selectAll()"
                                    class="text-brand-600 hover:text-brand-700 dark:text-brand-400 cursor-pointer">Select
                                    All</button>
                                <span class="text-gray-300 dark:text-gray-700">&bull;</span>
                                <button type="button" @click="clearAll()"
                                    class="text-gray-500 hover:text-gray-700 dark:text-gray-400 cursor-pointer">Clear</button>
                            </div>
                        </div>

                        {{-- Checkboxes List --}}
                        <div class="max-h-60 overflow-y-auto space-y-0.5 py-1">
                            <template x-for="(label, val) in options" :key="val">
                                <label
                                    class="flex items-center gap-2.5 px-2.5 py-2 rounded-lg text-xs cursor-pointer transition-colors hover:bg-gray-100/70 dark:hover:bg-gray-800/70 select-none"
                                    :class="selected.includes(val) ? 'text-brand-600 dark:text-brand-400 font-bold bg-brand-50/50 dark:bg-brand-950/20' : 'text-gray-700 dark:text-gray-300 font-medium'">
                                    <input type="checkbox" :value="val" x-model="selected"
                                        class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                    <span x-text="label" class="truncate"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Divider --}}
                <span class="h-6 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></span>

                {{-- Search Button --}}
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-brand-500 hover:bg-brand-600 h-9 px-4 text-xs font-semibold text-white transition-colors shadow-sm cursor-pointer">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35" />
                    </svg>
                    Search
                </button>

                {{-- Reset --}}
                @if($dateFrom || $dateTo || !empty($categories))
                    <a href="{{ route('reports.receivables-payables', ['type' => $type]) }}"
                        class="inline-flex items-center h-9 px-4 rounded-xl border border-gray-300 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Reset
                    </a>
                @endif

                {{-- PDF Export --}}
                <a href="{{ route('reports.receivables-payables.pdf', request()->query()) }}"
                    class="ml-auto inline-flex items-center gap-1.5 rounded-xl border border-red-300 bg-red-50 h-9 px-4 text-xs font-semibold text-red-700 hover:bg-red-100 dark:border-red-900/30 dark:bg-red-950/10 dark:text-red-400 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export PDF
                </a>

                {{-- Active Filter Tags Row --}}
                <div x-show="selected.length > 0"
                    class="flex flex-wrap items-center gap-1.5 pt-2.5 mt-1 border-t border-gray-100 dark:border-gray-800/80 w-full"
                    x-transition>
                    <span class="text-[11px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500 mr-1">Active Categories:</span>
                    <template x-for="val in selected" :key="val">
                        <span
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold bg-brand-50 text-brand-700 dark:bg-brand-950/50 dark:text-brand-300 border border-brand-200/60 dark:border-brand-800/60 shadow-2xs">
                            <span x-text="options[val]"></span>
                            <button type="button" @click="removeTag(val)"
                                class="text-brand-400 hover:text-brand-600 dark:hover:text-white cursor-pointer rounded-full p-0.5 transition-colors">
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </span>
                    </template>
                    <button type="button" @click="clearAll()"
                        class="text-[11px] font-semibold text-gray-400 hover:text-red-500 dark:hover:text-red-400 ml-1 cursor-pointer">
                        Clear All
                    </button>
                </div>

            </div>
        </x-common.component-card>
    </form>

    {{-- Main Statement Data Card --}}
    <x-common.component-card title="" desc="">
        @if($type === 'receivables')
            {{-- Receivables Table --}}
            <div>
                <div class="mb-4 pb-2">
                    <h3 class="text-sm font-bold text-gray-850 dark:text-white/90">
                        {{ $receivableScope === 'other' ? 'Other Receivables Summary (Not Managed by PM Mall)' : 'PM Mall Managed Receivables Summary' }}
                    </h3>
                    <p class="text-xs text-gray-400 mt-0.5">Breakdown of outstanding dues for units and accounts managed by PM
                        Mall.
                        {{ $receivableScope === 'other' ? 'Breakdown of outstanding dues for self-owned / external units not managed by PM Mall.' : '' }}
                    </p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                        <thead
                            class="border-b border-gray-100 bg-gray-50/50 text-[10px] uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3">Tenant / Entity Name</th>
                                <th class="px-4 py-3">Flat / Shop</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/80">
                            @forelse($receivables as $row)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                    <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $row['name'] }}</div>
                                        @if(!empty($row['types']))
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($row['types'] as $t)
                                                    @php
                                                        $badgeStyle = match($t) {
                                                            'Rent' => 'bg-blue-50 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300 border-blue-200/60 dark:border-blue-800/60',
                                                            'Maintenance' => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border-emerald-200/60 dark:border-emerald-800/60',
                                                            'Extra Payments' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/60 dark:border-purple-800/60',
                                                            'Fine', 'Fines' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200/60 dark:border-rose-800/60',
                                                            'Utilities' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200/60 dark:border-amber-800/60',
                                                            'Landlord Credit' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-950/60 dark:text-indigo-300 border-indigo-200/60 dark:border-indigo-800/60',
                                                            'Party Receivable' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border-sky-200/60 dark:border-sky-800/60',
                                                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200',
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $badgeStyle }}">
                                                        {{ $t }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-950 dark:text-gray-400 font-bold text-sm">
                                        {{ $row['unit'] ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-green-600 dark:text-green-400 font-mono text-sm">
                                        Rs. {{ number_format($row['net'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-gray-400">
                                        No active receivables matching current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot
                            class="border-t-2 border-gray-150 bg-gray-50/20 font-bold dark:border-gray-800 dark:bg-gray-900/10">
                            <tr>
                                <td class="px-4 py-3.5 text-gray-900 dark:text-white" colspan="2">
                                    {{ $receivableScope === 'other' ? 'Total Other Receivables (Not Managed by PM Mall)' : 'Total PM Mall Receivables' }}
                                </td>
                                <td
                                    class="px-4 py-3.5 text-right text-green-600 dark:text-green-400 font-mono text-sm font-bold">
                                    Rs.
                                    {{ number_format($totalReceivablesNet, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @else
            {{-- Payables Table --}}
            <div>
                <div class="mb-4 pb-2">
                    <h3 class="text-sm font-bold text-gray-850 dark:text-white/90">Payables Summary (Owed/Held by Building)</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Summary breakdown of tenant security deposits, contractor payables,
                        and landlord installments.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                        <thead
                            class="border-b border-gray-100 bg-gray-50/50 text-[10px] uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3">Tenant / Entity Name</th>
                                <th class="px-4 py-3">Flat / Shop</th>
                                <th class="px-4 py-3 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/80">
                            @forelse($payables as $row)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                    <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $row['name'] }}</div>
                                        @if(!empty($row['types']))
                                            <div class="flex flex-wrap gap-1 mt-1">
                                                @foreach($row['types'] as $t)
                                                    @php
                                                        $badgeStyle = match($t) {
                                                            'Security Deposit' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200/60 dark:border-amber-800/60',
                                                            'Party Payable' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border-sky-200/60 dark:border-sky-800/60',
                                                            'Landlord Payable' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200/60 dark:border-rose-800/60',
                                                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200',
                                                        };
                                                    @endphp
                                                    <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider border {{ $badgeStyle }}">
                                                        {{ $t }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-950 dark:text-gray-400 font-bold text-sm">
                                        {{ $row['unit'] ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-red-600 dark:text-red-400 font-mono text-sm">
                                        Rs. {{ number_format($row['net'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="py-8 text-center text-gray-400">
                                        No active payables matching current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot
                            class="border-t-2 border-gray-150 bg-gray-50/20 font-bold dark:border-gray-800 dark:bg-gray-900/10">
                            <tr>
                                <td class="px-4 py-3.5 text-gray-900 dark:text-white" colspan="2">Total Building Payables</td>
                                <td class="px-4 py-3.5 text-right text-red-600 dark:text-red-400 font-mono text-sm font-bold">
                                    Rs.
                                    {{ number_format($totalPayablesNet, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#date_from', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true
                });
                flatpickr('#date_to', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true
                });
            }
        });
    </script>
@endpush