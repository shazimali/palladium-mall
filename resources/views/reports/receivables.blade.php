@extends('layouts.app')

@section('content')
    <style>
        @media print {

            .no-print,
            nav,
            aside,
            header,
            .sticky,
            .page-breadcrumb,
            #report-filter-form {
                display: none !important;
            }

            body {
                background-color: white !important;
                color: black !important;
                padding: 0 !important;
                margin: 0 !important;
                font-size: 15px !important;
            }

            .print-container {
                padding: 15px !important;
            }

            table {
                width: 100% !important;
                border-collapse: collapse !important;
                font-size: 14px !important;
            }

            th {
                font-size: 13px !important;
                font-weight: 900 !important;
                border: 1px solid #9ca3af !important;
                padding: 8px 10px !important;
                color: black !important;
            }

            td {
                border: 1px solid #9ca3af !important;
                padding: 8px 10px !important;
                color: black !important;
                font-size: 14px !important;
            }

            tfoot tr td {
                font-weight: 900 !important;
                font-size: 15px !important;
                background-color: #f3f4f6 !important;
            }
        }
    </style>

    @php
        $catMap = [
            'Tenant Rent' => 'Rent',
            'Tenant Maintenance' => 'Maintenance',
            'Party Receivable' => 'Party Receivable',
            'Landlord Credit' => 'Landlord Credit',
            'Tenant Fine' => 'Fine',
            'Tenant Utilities' => 'Utilities',
            'Tenant Other' => 'Others',
            'Tenant Extra' => 'Other Client Charges',
        ];
        $selectedCategoryLabels = collect(empty($categories) ? array_keys($catMap) : $categories)
            ->map(fn($c) => $catMap[$c] ?? $c)->values();
    @endphp

    {{-- Printable Header --}}
    <div class="hidden print:block mb-6 text-center border-b-2 border-black pb-4">
        <p class="text-sm font-bold uppercase tracking-wider text-gray-700">PALLADIUM MALL</p>
        <h1 class="text-3xl font-black uppercase tracking-wider text-black mt-1">
            {{ $receivableScope === 'other' ? 'Other Due Receivable Report (Not Managed by PM Mall)' : 'PM Mall Due Receivable Report' }}
        </h1>
        <p class="text-base font-bold text-black mt-1">
            Statement Period: {{ !empty($dateFrom) ? date('d M Y', strtotime($dateFrom)) : 'Beginning' }} —
            {{ !empty($dateTo) ? date('d M Y', strtotime($dateTo)) : 'Present' }}
        </p>
        <p class="text-sm font-semibold text-black mt-1">
            Categories: {{ $selectedCategoryLabels->implode(', ') }}
        </p>
    </div>

    <div class="no-print">
        <x-common.page-breadcrumb pageTitle="Due Receivable Report" />
    </div>

    <form action="{{ route('reports.receivables') }}" method="GET" id="report-filter-form" class="space-y-6 no-print">

        {{-- Sub-Tabs for Receivables (PM Mall vs Other Receivables) --}}
        <div class="flex flex-col items-center gap-3">
            <input type="hidden" name="receivable_scope" id="receivable_scope_input" value="{{ $receivableScope }}">
            <div
                class="inline-flex rounded-xl p-1 bg-gray-200/80 dark:bg-gray-800 border border-gray-300/60 dark:border-gray-700/60 shadow-inner">
                <button type="button"
                    onclick="document.getElementById('receivable_scope_input').value='pm_mall'; document.getElementById('report-filter-form').submit();"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition-all duration-200 cursor-pointer {{ $receivableScope === 'pm_mall' ? 'bg-white dark:bg-gray-900 text-brand-600 dark:text-brand-400 shadow-sm ring-1 ring-brand-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                    <span>🏢 PM Mall Receivables</span>
                    <span
                        class="rounded-full px-2.5 py-0.5 text-xs font-extrabold font-mono {{ $receivableScope === 'pm_mall' ? 'bg-brand-50 text-brand-700 dark:bg-brand-950/40 dark:text-brand-300' : 'bg-gray-300/60 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        Rs. {{ number_format($pmMallReceivablesNet, 0) }}
                    </span>
                </button>
                <button type="button"
                    onclick="document.getElementById('receivable_scope_input').value='other'; document.getElementById('report-filter-form').submit();"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-lg text-sm font-bold transition-all duration-200 cursor-pointer {{ $receivableScope === 'other' ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-sm ring-1 ring-amber-500/20' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900' }}">
                    <span>🏘️ Other Receivables (Not Managed by PM Mall)</span>
                    <span
                        class="rounded-full px-2.5 py-0.5 text-xs font-extrabold font-mono {{ $receivableScope === 'other' ? 'bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-300' : 'bg-gray-300/60 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        Rs. {{ number_format($otherReceivablesNet, 0) }}
                    </span>
                </button>
            </div>
        </div>

        {{-- Filters: dates, categories, actions --}}
        <x-common.component-card>
            @php
                $filterInput = 'shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-xl border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
            @endphp

            <div x-data="{
                    selected: @js(empty($categories) ? array_keys($catMap) : $categories),
                    options: @js($catMap),
                    selectAll() {
                        this.selected = Object.keys(this.options);
                    },
                    clearAll() {
                        this.selected = [];
                    }
                }" class="space-y-5">

                {{-- Row 1: Start / End Date --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 max-w-2xl mx-auto">
                    <div>
                        <label for="date_from" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Start Date</label>
                        <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="Start Date"
                            autocomplete="off" class="{{ $filterInput }}">
                    </div>
                    <div>
                        <label for="date_to" class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">End Date</label>
                        <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="End Date"
                            autocomplete="off" class="{{ $filterInput }}">
                    </div>
                </div>

                {{-- Row 2: Category boxes --}}
                <div>
                    <div class="mb-2 flex items-center justify-between">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-400">Categories</span>
                        <div class="flex items-center gap-1 text-xs font-semibold">
                            <button type="button" @click="selectAll()"
                                class="text-brand-600 hover:text-brand-700 dark:text-brand-400 cursor-pointer">All</button>
                            <span class="text-gray-300 dark:text-gray-700">&bull;</span>
                            <button type="button" @click="clearAll()"
                                class="text-gray-400 hover:text-gray-600 dark:text-gray-500 cursor-pointer">None</button>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <template x-for="(label, val) in options" :key="val">
                            <label
                                class="flex items-center gap-2.5 h-11 px-4 rounded-xl border-2 text-sm font-bold cursor-pointer transition-all select-none"
                                :class="selected.includes(val)
                                        ? 'border-brand-400 bg-brand-50/70 text-brand-700 dark:border-brand-700 dark:bg-brand-950/40 dark:text-brand-300 shadow-2xs'
                                        : 'border-gray-200 bg-gray-50/50 text-gray-600 dark:border-gray-700/80 dark:bg-gray-900/50 dark:text-gray-400 hover:bg-gray-100'">
                                <input type="checkbox" name="categories[]" :value="val" x-model="selected"
                                    class="h-4 w-4 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                                <span x-text="label"></span>
                            </label>
                        </template>
                    </div>
                </div>

                {{-- Row 3: Actions --}}
                <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <button type="submit"
                        class="inline-flex items-center gap-1.5 rounded-xl bg-brand-500 hover:bg-brand-600 h-10 px-6 text-sm font-semibold text-white transition-colors shadow-sm cursor-pointer">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35" />
                        </svg>
                        Search
                    </button>

                    <a href="{{ route('reports.receivables') }}"
                        class="inline-flex items-center h-10 px-6 rounded-xl border border-gray-300 text-sm font-semibold text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Reset
                    </a>

                    <button type="button" onclick="window.print()"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-700 h-10 px-6 text-sm font-bold text-gray-700 dark:text-gray-200 transition-colors shadow-xs cursor-pointer">
                        <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </button>
                </div>

            </div>
        </x-common.component-card>
    </form>

    {{-- Main Statement Data Card --}}
    <x-common.component-card title="" desc="">
        <div>
            <div class="mb-4 pb-2 no-print">
                <h3 class="text-base font-bold text-gray-850 dark:text-white/90">
                    {{ $receivableScope === 'other' ? 'Other Receivables Summary (Not Managed by PM Mall)' : 'PM Mall Managed Receivables Summary' }}
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    {{ $receivableScope === 'other' ? 'Breakdown of outstanding dues for self-owned / external units not managed by PM Mall.' : 'Breakdown of outstanding dues for units and accounts managed by PM Mall.' }}
                </p>
            </div>

            <div class="overflow-x-auto print-container">
                <table class="w-full text-left text-sm text-gray-700 dark:text-gray-300">
                    <thead
                        class="border-b-2 border-gray-200 bg-gray-50/70 text-xs font-extrabold uppercase tracking-wider text-gray-600 dark:border-gray-800 dark:bg-gray-900/60 dark:text-gray-400">
                        <tr>
                            <th class="px-5 py-3.5">Tenant / Entity Name</th>
                            <th class="px-5 py-3.5">Flat / Shop</th>
                            <th class="px-5 py-3.5 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                        @forelse($receivables as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                <td class="px-5 py-4 font-semibold text-gray-950 dark:text-white">
                                    <div class="text-base font-bold text-gray-900 dark:text-white">{{ $row['name'] }}</div>
                                    @if(!empty($row['types']))
                                        <div class="flex flex-wrap gap-1.5 mt-1.5">
                                            @foreach($row['types'] as $t)
                                                @php
                                                    $badgeStyle = match ($t) {
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
                                                <span
                                                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider border {{ $badgeStyle }}">
                                                    {{ $t }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-gray-950 dark:text-gray-300 font-bold text-base">
                                    {{ $row['unit'] ?: '—' }}
                                </td>
                                <td
                                    class="px-5 py-4 text-right font-black text-green-600 dark:text-green-400 font-mono text-base">
                                    Rs. {{ number_format($row['net'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-10 text-center text-sm font-semibold text-gray-400">
                                    No active receivables matching current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot
                        class="border-t-2 border-gray-300 bg-gray-100/50 font-bold dark:border-gray-700 dark:bg-gray-900/30">
                        <tr>
                            <td class="px-5 py-4 text-base font-bold text-gray-900 dark:text-white" colspan="2">
                                {{ $receivableScope === 'other' ? 'Total Other Receivables (Not Managed by PM Mall)' : 'Total PM Mall Receivables' }}
                            </td>
                            <td
                                class="px-5 py-4 text-right text-green-600 dark:text-green-400 font-mono text-lg font-black">
                                Rs. {{ number_format($totalReceivablesNet, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
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