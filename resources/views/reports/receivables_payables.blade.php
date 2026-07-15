@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Receivables & Payables Summary" />

    <form action="{{ route('reports.receivables-payables') }}" method="GET" id="report-filter-form" class="space-y-6">
        
        {{-- Segmented Tab Switcher --}}
        <div class="flex justify-center">
            <div class="inline-flex rounded-xl p-1.5 bg-gray-100 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm">
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="receivables" class="sr-only" onchange="this.form.submit()" {{ $type === 'receivables' ? 'checked' : '' }}>
                    <span class="inline-flex items-center px-6 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 {{ $type === 'receivables' ? 'bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        Receivables View
                    </span>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="type" value="payables" class="sr-only" onchange="this.form.submit()" {{ $type === 'payables' ? 'checked' : '' }}>
                    <span class="inline-flex items-center px-6 py-2.5 rounded-lg text-sm font-bold transition-all duration-200 {{ $type === 'payables' ? 'bg-white dark:bg-gray-800 text-brand-600 dark:text-brand-400 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}">
                        Payables View
                    </span>
                </label>
            </div>
        </div>

        {{-- Overall Summary Widgets --}}
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4">
            <!-- System Cash Card -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">System Cash Balance</span>
                <h4 class="mt-1.5 text-lg font-bold text-gray-900 dark:text-white/90">
                    Rs. {{ number_format($totalCashBalance, 2) }}
                </h4>
            </div>

            <!-- Total Payables Card -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] {{ $type === 'payables' ? 'ring-2 ring-brand-500/20 bg-brand-50/10' : '' }}">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Payables</span>
                <h4 class="mt-1.5 text-lg font-bold text-red-500 dark:text-red-400">
                    Rs. {{ number_format($totalPayables, 2) }}
                </h4>
            </div>

            <!-- Total Receivables Card -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03] {{ $type === 'receivables' ? 'ring-2 ring-brand-500/20 bg-brand-50/10' : '' }}">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Total Receivables</span>
                <h4 class="mt-1.5 text-lg font-bold text-green-600 dark:text-green-400">
                    Rs. {{ number_format($totalReceivables, 2) }}
                </h4>
            </div>

            <!-- Net Financial Position Card -->
            <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Net Position</span>
                <h4 class="mt-1.5 text-lg font-bold {{ $totalReceivables - $totalPayables >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-500 dark:text-red-400' }}">
                    Rs. {{ number_format($totalReceivables - $totalPayables, 2) }}
                </h4>
            </div>
        </div>

        {{-- Filters & Options --}}
        <x-common.component-card title="Statement Parameters" desc="Configure dates and select categories to filter data instantly">
            
            <div class="flex flex-col gap-6 md:flex-row md:items-center md:justify-between">
                
                {{-- Date Pickers --}}
                <div class="flex flex-wrap items-end gap-4 flex-1">
                    @php
                        $filterInput = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-xl border border-gray-300 bg-transparent px-4 py-2 text-xs text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
                        $filterLabel = 'mb-1 block text-[10px] font-bold uppercase tracking-wider text-gray-400';
                    @endphp

                    <div class="w-full sm:w-44">
                        <label class="{{ $filterLabel }}">From Date</label>
                        <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="YYYY-MM-DD" autocomplete="off" class="{{ $filterInput }} w-full" onchange="this.form.submit()">
                    </div>

                    <div class="w-full sm:w-44">
                        <label class="{{ $filterLabel }}">To Date</label>
                        <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="YYYY-MM-DD" autocomplete="off" class="{{ $filterInput }} w-full" onchange="this.form.submit()">
                    </div>

                    <div>
                        @if($dateFrom || $dateTo || !empty($categories))
                            <a href="{{ route('reports.receivables-payables', ['type' => $type]) }}" class="h-10 inline-flex items-center justify-center rounded-xl border border-gray-300 px-4 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                                Reset Filters
                            </a>
                        @endif
                    </div>
                </div>

                {{-- PDF Export action --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('reports.receivables-payables.pdf', request()->query()) }}"
                       class="inline-flex items-center gap-2 rounded-xl border border-red-300 bg-red-50 px-4 py-2.5 text-xs font-semibold text-red-700 hover:bg-red-100 dark:border-red-900/30 dark:bg-red-950/10 dark:text-red-400 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export Statement PDF
                    </a>
                </div>
            </div>

            {{-- Category Filter Pill Checkbox Group --}}
            <div class="mt-6 pt-5 border-t border-gray-100 dark:border-gray-800">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2">Category Filtering Options</span>
                <div class="flex flex-wrap gap-2.5">
                    @if($type === 'receivables')
                        <label class="cursor-pointer">
                            <input type="checkbox" name="categories[]" value="Tenant Rent & Charges" class="sr-only peer" onchange="this.form.submit()" {{ empty($categories) || in_array('Tenant Rent & Charges', $categories) ? 'checked' : '' }}>
                            <span class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-400 bg-white dark:bg-gray-800 peer-checked:bg-brand-500 peer-checked:text-white peer-checked:border-brand-500 peer-checked:shadow-sm transition-all duration-200">
                                Rent / Maint / Other / Fine Dues
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="checkbox" name="categories[]" value="Landlord Credit" class="sr-only peer" onchange="this.form.submit()" {{ empty($categories) || in_array('Landlord Credit', $categories) ? 'checked' : '' }}>
                            <span class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-400 bg-white dark:bg-gray-800 peer-checked:bg-brand-500 peer-checked:text-white peer-checked:border-brand-500 peer-checked:shadow-sm transition-all duration-200">
                                Landlord purchase Credits
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="checkbox" name="categories[]" value="Party Receivable" class="sr-only peer" onchange="this.form.submit()" {{ empty($categories) || in_array('Party Receivable', $categories) ? 'checked' : '' }}>
                            <span class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-400 bg-white dark:bg-gray-800 peer-checked:bg-brand-500 peer-checked:text-white peer-checked:border-brand-500 peer-checked:shadow-sm transition-all duration-200">
                                Party Receivables
                            </span>
                        </label>
                    @else
                        <label class="cursor-pointer">
                            <input type="checkbox" name="categories[]" value="Tenant Security Deposit" class="sr-only peer" onchange="this.form.submit()" {{ empty($categories) || in_array('Tenant Security Deposit', $categories) ? 'checked' : '' }}>
                            <span class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-400 bg-white dark:bg-gray-800 peer-checked:bg-brand-500 peer-checked:text-white peer-checked:border-brand-500 peer-checked:shadow-sm transition-all duration-200">
                                Security Deposit Payables
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="checkbox" name="categories[]" value="Party Payable" class="sr-only peer" onchange="this.form.submit()" {{ empty($categories) || in_array('Party Payable', $categories) ? 'checked' : '' }}>
                            <span class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-400 bg-white dark:bg-gray-800 peer-checked:bg-brand-500 peer-checked:text-white peer-checked:border-brand-500 peer-checked:shadow-sm transition-all duration-200">
                                Party Payables
                            </span>
                        </label>
                        <label class="cursor-pointer">
                            <input type="checkbox" name="categories[]" value="Landlord Payable" class="sr-only peer" onchange="this.form.submit()" {{ empty($categories) || in_array('Landlord Payable', $categories) ? 'checked' : '' }}>
                            <span class="inline-flex items-center px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-700 text-xs font-semibold text-gray-700 dark:text-gray-400 bg-white dark:bg-gray-800 peer-checked:bg-brand-500 peer-checked:text-white peer-checked:border-brand-500 peer-checked:shadow-sm transition-all duration-200">
                                Landlord Payables
                            </span>
                        </label>
                    @endif
                </div>
            </div>

        </x-common.component-card>
    </form>

    {{-- Main Statement Data Card --}}
    <x-common.component-card title="Ledger Statement Details" desc="Breakdown of filtered transactions">
        @if($type === 'receivables')
            {{-- Receivables Table --}}
            <div>
                <div class="mb-4 pb-2">
                    <h3 class="text-sm font-bold text-gray-850 dark:text-white/90">Receivables Summary (Owed to Building)</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Summary breakdown of dues from tenants, landlord credits, and party receivables.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                        <thead class="border-b border-gray-100 bg-gray-50/50 text-[10px] uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3">Entity Name</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Description</th>
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
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-400">
                                            {{ $row['category'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-400">
                                        {{ $row['details'] }}
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
                                    <td colspan="6" class="py-8 text-center text-gray-400">
                                        No active receivables matching current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="border-t-2 border-gray-150 bg-gray-50/20 font-bold dark:border-gray-800 dark:bg-gray-900/10">
                            <tr>
                                <td class="px-4 py-3.5 text-gray-900 dark:text-white" colspan="3">Total Building Receivables</td>
                                <td class="px-4 py-3.5 text-right font-mono">Rs. {{ number_format(collect($receivables)->sum('due'), 2) }}</td>
                                <td class="px-4 py-3.5 text-right text-green-600 font-mono">Rs. {{ number_format(collect($receivables)->sum('paid'), 2) }}</td>
                                <td class="px-4 py-3.5 text-right text-gray-950 dark:text-white font-mono">Rs. {{ number_format($totalReceivables, 2) }}</td>
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
                    <p class="text-xs text-gray-400 mt-0.5">Summary breakdown of tenant security deposits, contractor payables, and landlord installments.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                        <thead class="border-b border-gray-100 bg-gray-50/50 text-[10px] uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-3">Entity Name</th>
                                <th class="px-4 py-3">Category</th>
                                <th class="px-4 py-3">Description</th>
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
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-950/30 dark:text-indigo-400">
                                            {{ $row['category'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-400">
                                        {{ $row['details'] }}
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
                                    <td colspan="6" class="py-8 text-center text-gray-400">
                                        No active payables matching current filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="border-t-2 border-gray-150 bg-gray-50/20 font-bold dark:border-gray-800 dark:bg-gray-900/10">
                            <tr>
                                <td class="px-4 py-3.5 text-gray-900 dark:text-white" colspan="3">Total Building Payables</td>
                                <td class="px-4 py-3.5 text-right font-mono">Rs. {{ number_format(collect($payables)->sum('due'), 2) }}</td>
                                <td class="px-4 py-3.5 text-right text-red-500 font-mono">Rs. {{ number_format(collect($payables)->sum('paid'), 2) }}</td>
                                <td class="px-4 py-3.5 text-right text-gray-950 dark:text-white font-mono">Rs. {{ number_format($totalPayables, 2) }}</td>
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
