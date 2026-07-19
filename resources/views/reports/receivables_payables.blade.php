@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Receivables & Payables Summary" />

    <form action="{{ route('reports.receivables-payables') }}" method="GET" id="report-filter-form" class="space-y-6">

        {{-- Segmented Tab Switcher --}}
        <div class="flex justify-center">
            <div
                class="inline-flex rounded-xl p-1.5 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="receivables" class="sr-only"
                        onchange="document.querySelectorAll('input[type=checkbox]').forEach(el => el.checked = false); this.form.submit()"
                        {{ $type === 'receivables' ? 'checked' : '' }}>
                    <span
                        class="inline-flex items-center px-6 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 {{ $type === 'receivables' ? 'bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        Due Receivables 
                    </span>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="payables" class="sr-only"
                        onchange="document.querySelectorAll('input[type=checkbox]').forEach(el => el.checked = false); this.form.submit()"
                        {{ $type === 'payables' ? 'checked' : '' }}>
                    <span
                        class="inline-flex items-center px-6 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 {{ $type === 'payables' ? 'bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        Due Payables
                    </span>
                </label>
            </div>
        </div>

        {{-- Overall Summary Widgets --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
            @if($type === 'receivables')
                <!-- Total Receivables Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] ring-2 ring-brand-500/20 bg-brand-50/10">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Receivables</span>
                    <h4 class="mt-1.5 text-lg font-bold text-green-600 dark:text-green-400">
                        Rs. {{ number_format($totalReceivablesDue, 2) }}
                    </h4>
                </div>
                <!-- Total Received Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Received</span>
                    <h4 class="mt-1.5 text-lg font-bold text-brand-600 dark:text-brand-400">
                        Rs. {{ number_format($totalReceivablesPaid, 2) }}
                    </h4>
                </div>
                <!-- Receivables Balance Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Balance</span>
                    <h4 class="mt-1.5 text-lg font-bold text-green-600 dark:text-green-400">
                        Rs. {{ number_format($totalReceivablesNet, 2) }}
                    </h4>
                </div>
            @else
                <!-- Total Payables Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] ring-2 ring-brand-500/20 bg-brand-50/10">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Payables</span>
                    <h4 class="mt-1.5 text-lg font-bold text-red-500 dark:text-red-400">
                        Rs. {{ number_format($totalPayablesDue, 2) }}
                    </h4>
                </div>
                <!-- Total Paid Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Paid</span>
                    <h4 class="mt-1.5 text-lg font-bold text-brand-600 dark:text-brand-400">
                        Rs. {{ number_format($totalPayablesPaid, 2) }}
                    </h4>
                </div>
                <!-- Payables Balance Card -->
                <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Balance</span>
                    <h4 class="mt-1.5 text-lg font-bold text-red-500 dark:text-red-400">
                        Rs. {{ number_format($totalPayablesNet, 2) }}
                    </h4>
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
                <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}"
                    placeholder="From Date" autocomplete="off" class="{{ $filterInput }} w-36">

                {{-- Date To --}}
                <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}"
                    placeholder="To Date" autocomplete="off" class="{{ $filterInput }} w-36">

                {{-- Divider --}}
                <span class="h-6 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></span>

                {{-- Category Checkboxes --}}
                @if($type === 'receivables')
                    @foreach([
                        'Tenant Rent'        => 'Rent',
                        'Tenant Maintenance' => 'Maintenance',
                        'Tenant Fine'        => 'Fines',
                        'Tenant Utilities'   => 'Utilities',
                        'Tenant Other'       => 'Others',
                        'Landlord Credit'    => 'Landlord Credits',
                        'Party Receivable'   => 'Party Receivables',
                    ] as $value => $label)
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="categories[]" value="{{ $value }}"
                                {{ empty($categories) || in_array($value, $categories) ? 'checked' : '' }}
                                class="h-3.5 w-3.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-400 whitespace-nowrap">{{ $label }}</span>
                        </label>
                    @endforeach
                @else
                    @foreach([
                        'Tenant Security Deposit' => 'Security Deposits',
                        'Party Payable'           => 'Party Payables',
                    ] as $value => $label)
                        <label class="inline-flex items-center gap-1.5 cursor-pointer">
                            <input type="checkbox" name="categories[]" value="{{ $value }}"
                                {{ empty($categories) || in_array($value, $categories) ? 'checked' : '' }}
                                class="h-3.5 w-3.5 rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                            <span class="text-xs font-medium text-gray-700 dark:text-gray-400 whitespace-nowrap">{{ $label }}</span>
                        </label>
                    @endforeach
                @endif

                {{-- Divider --}}
                <span class="h-6 w-px bg-gray-200 dark:bg-gray-700 hidden sm:block"></span>

                {{-- Search Button --}}
                <button type="submit"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-brand-500 hover:bg-brand-600 h-9 px-4 text-xs font-semibold text-white transition-colors shadow-sm cursor-pointer">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8" /><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35" />
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export PDF
                </a>

            </div>
        </x-common.component-card>
    </form>

    {{-- Main Statement Data Card --}}
    <x-common.component-card title="" desc="">
        @if($type === 'receivables')
            {{-- Receivables Table --}}
            <div>
                <div class="mb-4 pb-2">
                    <h3 class="text-sm font-bold text-gray-850 dark:text-white/90">Due Receivables Summary</h3>
                    <p class="text-xs text-gray-400 mt-0.5"></p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                        <thead
                            class="border-b border-gray-100 bg-gray-50/50 text-[10px] uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3">Tenant / Entity Name</th>
                                <th class="px-4 py-3">Flat / Shop</th>
                                <th class="px-4 py-3 text-right">Due / Credit</th>
                                <th class="px-4 py-3 text-right">Paid / Received</th>
                                <th class="px-4 py-3 text-right">Net Receivable</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/80">
                            @forelse($receivables as $row)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                    <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">
                                        {{ $row['name'] }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-950 dark:text-gray-400 font-bold  text-lg">
                                        {{ $row['unit'] ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono">
                                        Rs. {{ number_format($row['due'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-green-600 dark:text-green-400 font-mono">
                                        Rs. {{ number_format($row['paid'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white font-mono">
                                        Rs. {{ number_format($row['net'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400">
                                        No active receivables matching current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot
                            class="border-t-2 border-gray-150 bg-gray-50/20 font-bold dark:border-gray-800 dark:bg-gray-900/10">
                            <tr>
                                <td class="px-4 py-3.5 text-gray-900 dark:text-white" colspan="2">Total Building Receivables
                                </td>
                                <td class="px-4 py-3.5 text-right font-mono">Rs.
                                    {{ number_format(collect($receivables)->sum('due'), 2) }}</td>
                                <td class="px-4 py-3.5 text-right text-green-600 font-mono">Rs.
                                    {{ number_format(collect($receivables)->sum('paid'), 2) }}</td>
                                <td class="px-4 py-3.5 text-right text-gray-950 dark:text-white font-mono">Rs.
                                    {{ number_format($totalReceivables, 2) }}</td>
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
                                <th class="px-4 py-3 text-right">Owed / Held</th>
                                <th class="px-4 py-3 text-right">Paid / Settled</th>
                                <th class="px-4 py-3 text-right">Net Payable</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/80">
                            @forelse($payables as $row)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                    <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">
                                        {{ $row['name'] }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 dark:text-gray-400">
                                        {{ $row['unit'] ?: '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-mono">
                                        Rs. {{ number_format($row['due'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-red-500 dark:text-red-400 font-mono">
                                        Rs. {{ number_format($row['paid'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white font-mono">
                                        Rs. {{ number_format($row['net'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400">
                                        No active payables matching current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot
                            class="border-t-2 border-gray-150 bg-gray-50/20 font-bold dark:border-gray-800 dark:bg-gray-900/10">
                            <tr>
                                <td class="px-4 py-3.5 text-gray-900 dark:text-white" colspan="2">Total Building Payables</td>
                                <td class="px-4 py-3.5 text-right font-mono">Rs.
                                    {{ number_format(collect($payables)->sum('due'), 2) }}</td>
                                <td class="px-4 py-3.5 text-right text-red-500 font-mono">Rs.
                                    {{ number_format(collect($payables)->sum('paid'), 2) }}</td>
                                <td class="px-4 py-3.5 text-right text-gray-950 dark:text-white font-mono">Rs.
                                    {{ number_format($totalPayables, 2) }}</td>
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