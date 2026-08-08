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

{{-- Printable Header --}}
<div class="hidden print:block mb-6 text-center border-b-2 border-black pb-4">
    <h1 class="text-3xl font-black uppercase tracking-wider text-black">PALLADIUM MALL</h1>
    <p class="text-sm font-bold text-gray-700 uppercase">Management Office — Islamabad</p>
    <h2 class="text-xl font-black uppercase text-black mt-2">Payables Report Summary</h2>
    <p class="text-base font-bold text-black mt-1">
        Statement Period: {{ !empty($dateFrom) ? date('d M Y', strtotime($dateFrom)) : 'Beginning' }} —
        {{ !empty($dateTo) ? date('d M Y', strtotime($dateTo)) : 'Present' }}
    </p>
    <p class="text-xs text-gray-600 mt-0.5">Printed on: {{ now()->format('d M Y, h:i A') }}</p>
</div>

    <div class="no-print">
        <x-common.page-breadcrumb pageTitle="Payables Report" />
    </div>

    <form action="{{ route('reports.payables') }}" method="GET" id="report-filter-form" class="space-y-6 no-print">

        {{-- Single Line Filters & Options --}}
        <x-common.component-card>
            @php
                $filterInput = 'shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-xl border border-gray-300 bg-transparent px-3 py-1.5 text-xs text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
                $catMap = [
                    'Tenant Security Deposit' => 'Security Deposits',
                    'Party Payable' => 'Party Payables',
                    'Landlord Payable' => 'Landlord Payables',
                    'Expenses' => 'Expenses',
                ];
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
            }" class="flex flex-wrap items-center gap-3 w-full">

                {{-- Date From --}}
                <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="From Date"
                    autocomplete="off" class="{{ $filterInput }} w-32 shrink-0">

                {{-- Date To --}}
                <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="To Date"
                    autocomplete="off" class="{{ $filterInput }} w-32 shrink-0">

                {{-- Divider --}}
                <span class="h-6 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block shrink-0"></span>

                {{-- Inline Category Checkboxes --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    <template x-for="(label, val) in options" :key="val">
                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg border text-xs font-semibold cursor-pointer transition-all select-none"
                            :class="selected.includes(val) 
                                ? 'border-brand-300 bg-brand-50/70 text-brand-700 dark:border-brand-800 dark:bg-brand-950/40 dark:text-brand-300 shadow-2xs' 
                                : 'border-gray-200 bg-gray-50/50 text-gray-600 dark:border-gray-700/80 dark:bg-gray-900/50 dark:text-gray-400 hover:bg-gray-100'">
                            <input type="checkbox" name="categories[]" :value="val" x-model="selected"
                                class="h-3.5 w-3.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span x-text="label"></span>
                        </label>
                    </template>
                </div>

                {{-- Select All / Clear Shortcuts --}}
                <div class="flex items-center gap-1 text-xs font-semibold shrink-0">
                    <button type="button" @click="selectAll()" class="text-brand-600 hover:text-brand-700 dark:text-brand-400 cursor-pointer text-[11px]">
                        All
                    </button>
                    <span class="text-gray-300 dark:text-gray-700">&bull;</span>
                    <button type="button" @click="clearAll()" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 cursor-pointer text-[11px]">
                        None
                    </button>
                </div>

                {{-- Divider --}}
                <span class="h-6 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block shrink-0"></span>

                {{-- Search Button --}}
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-brand-500 hover:bg-brand-600 h-9 px-4 text-xs font-semibold text-white transition-colors shadow-sm cursor-pointer shrink-0">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35" />
                    </svg>
                    Search
                </button>

                {{-- Reset --}}
                @if($dateFrom || $dateTo || !empty($categories))
                    <a href="{{ route('reports.payables') }}"
                        class="inline-flex items-center h-9 px-3 rounded-xl border border-gray-300 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors shrink-0">
                        Reset
                    </a>
                @endif

                {{-- Print Button --}}
                <button type="button" onclick="window.print()"
                    class="ml-auto inline-flex items-center gap-1.5 rounded-xl border border-gray-300 bg-white hover:bg-gray-50 dark:bg-gray-900 dark:border-gray-700 h-9 px-4 text-xs font-bold text-gray-700 dark:text-gray-200 transition-colors shadow-xs cursor-pointer shrink-0">
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </button>

            </div>
        </x-common.component-card>
    </form>

    {{-- Main Statement Data Card --}}
    <x-common.component-card title="" desc="">
        <div>
            <div class="mb-4 pb-2 no-print">
                <h3 class="text-base font-bold text-gray-850 dark:text-white/90">Payables Summary (Owed/Held by Building)</h3>
                <p class="text-xs text-gray-400 mt-0.5">Summary breakdown of tenant security deposits, contractor payables, and landlord installments.</p>
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
                        @forelse($payables as $row)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                <td class="px-5 py-4 font-semibold text-gray-950 dark:text-white">
                                    <div class="text-base font-bold text-gray-900 dark:text-white">{{ $row['name'] }}</div>
                                    @if(!empty($row['types']))
                                        <div class="flex flex-wrap gap-1.5 mt-1.5 no-print">
                                            @foreach($row['types'] as $t)
                                                @php
                                                    $badgeStyle = match ($t) {
                                                        'Security Deposit' => 'bg-amber-50 text-amber-700 dark:bg-amber-950/60 dark:text-amber-300 border-amber-200/60 dark:border-amber-800/60',
                                                        'Party Payable' => 'bg-sky-50 text-sky-700 dark:bg-sky-950/60 dark:text-sky-300 border-sky-200/60 dark:border-sky-800/60',
                                                        'Landlord Payable' => 'bg-rose-50 text-rose-700 dark:bg-rose-950/60 dark:text-rose-300 border-rose-200/60 dark:border-rose-800/60',
                                                        'Expenses' => 'bg-purple-50 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300 border-purple-200/60 dark:border-purple-800/60',
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
                                <td class="px-5 py-4 text-right font-black text-red-600 dark:text-red-400 font-mono text-base">
                                    Rs. {{ number_format($row['net'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="py-10 text-center text-sm font-semibold text-gray-400">
                                    No active payables matching current filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot
                        class="border-t-2 border-gray-300 bg-gray-100/50 font-bold dark:border-gray-700 dark:bg-gray-900/30">
                        <tr>
                            <td class="px-5 py-4 text-base font-bold text-gray-900 dark:text-white" colspan="2">Total Building Payables</td>
                            <td class="px-5 py-4 text-right text-red-600 dark:text-red-400 font-mono text-lg font-black">
                                Rs. {{ number_format($totalPayablesNet, 2) }}
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
