@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Receivables & Payables Summary" />

    {{-- Overall Summary Widgets --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-5">
        <!-- System Cash Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">System Cash Balance</span>
            <h4 class="mt-1.5 text-lg font-bold text-gray-900 dark:text-white/90">
                Rs. {{ number_format($totalCashBalance, 2) }}
            </h4>
        </div>

        <!-- Disposable Cash Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Disposable Cash</span>
            <h4 class="mt-1.5 text-lg font-bold text-blue-600 dark:text-blue-400">
                Rs. {{ number_format($disposableAmount, 2) }}
            </h4>
        </div>

        <!-- Total Owners Due Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Owed to Partners (Dues)</span>
            <h4 class="mt-1.5 text-lg font-bold text-red-500 dark:text-red-400">
                Rs. {{ number_format($totalOwnersPending, 2) }}
            </h4>
        </div>

        <!-- Total Party Payables Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Owed to Parties (Payables)</span>
            <h4 class="mt-1.5 text-lg font-bold text-amber-500 dark:text-amber-400">
                Rs. {{ number_format($partyTotals['net_pay'], 2) }}
            </h4>
        </div>

        <!-- Total Party Receivables Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <span class="text-[10px] font-bold uppercase tracking-wider text-gray-400">Owed by Parties (Receivables)</span>
            <h4 class="mt-1.5 text-lg font-bold text-green-600 dark:text-green-400">
                Rs. {{ number_format($partyTotals['net_rec'], 2) }}
            </h4>
        </div>
    </div>

    {{-- Filters & Options --}}
    <x-common.component-card title="Receivables & Payables Ledger Summaries" desc="Chronological statement summary of balances for internal partners and third-party agencies">
        
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between mb-6">
            {{-- Filtering form --}}
            <form action="{{ route('reports.receivables-payables') }}" method="GET" class="flex flex-wrap items-end gap-3 flex-1">
                @php
                    $filterInput = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-9 rounded-lg border border-gray-300 bg-transparent px-3 py-1.5 text-xs text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
                    $filterLabel = 'mb-0.5 block text-[10px] font-bold uppercase tracking-wider text-gray-400';
                @endphp

                <div class="w-full sm:w-48">
                    <label class="{{ $filterLabel }}">Search Name</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search name..." class="{{ $filterInput }} w-full">
                </div>

                <div class="w-1/2 sm:w-36">
                    <label class="{{ $filterLabel }}">From Date</label>
                    <input type="text" id="date_from" name="date_from" value="{{ $dateFrom }}" placeholder="YYYY-MM-DD" autocomplete="off" class="{{ $filterInput }} w-full">
                </div>

                <div class="w-1/2 sm:w-36">
                    <label class="{{ $filterLabel }}">To Date</label>
                    <input type="text" id="date_to" name="date_to" value="{{ $dateTo }}" placeholder="YYYY-MM-DD" autocomplete="off" class="{{ $filterInput }} w-full">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="h-9 inline-flex items-center justify-center rounded-lg bg-brand-500 px-4 text-xs font-semibold text-white hover:bg-brand-600 transition-colors shadow-sm cursor-pointer">
                        Filter Report
                    </button>
                    @if($search || $dateFrom || $dateTo)
                        <a href="{{ route('reports.receivables-payables') }}" class="h-9 inline-flex items-center justify-center rounded-lg border border-gray-300 px-3 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            {{-- PDF Export action --}}
            <div class="flex items-center gap-2">
                <a href="{{ route('reports.receivables-payables.pdf', request()->query()) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-red-300 bg-red-50 px-4 py-2 text-xs font-semibold text-red-700 hover:bg-red-100 dark:border-red-900/30 dark:bg-red-950/10 dark:text-red-400 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Export statement PDF
                </a>
            </div>
        </div>

        {{-- Main Content Stack --}}
        <div class="space-y-12">
            
            {{-- SECTION 1: Partner Dues (Managing Owners) --}}
            <div>
                <div class="mb-4 border-b border-gray-100 pb-2 dark:border-gray-800">
                    <h3 class="text-sm font-bold text-gray-850 dark:text-white/90">Section 1: Partners (Managing Owners) Accounts</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Summary of collected earnings, payout distributions, and net balances due for internal stakeholders.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                        <thead class="border-b border-gray-100 bg-gray-50/50 text-[10px] uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-2.5">Partner Name</th>
                                <th class="px-4 py-2.5 text-center">Share Percentage</th>
                                <th class="px-4 py-2.5 text-right">Total Share Earned (All Time)</th>
                                <th class="px-4 py-2.5 text-right">Total Paid (Outflows)</th>
                                <th class="px-4 py-2.5 text-right">Net Owed (Pending Dues)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/80">
                            @forelse($ownerRows as $row)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                    <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">
                                        {{ $row['owner']->name }}
                                    </td>
                                    <td class="px-4 py-3 text-center font-mono">
                                        {{ number_format($row['percentage'], 2) }}%
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-gray-300">
                                        Rs. {{ number_format($row['due'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right text-red-500 dark:text-red-400">
                                        Rs. {{ number_format($row['paid'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">
                                        Rs. {{ number_format($row['pending'], 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-gray-400">
                                        No partner records found matching filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="border-t-2 border-gray-150 bg-gray-50/20 font-bold dark:border-gray-800 dark:bg-gray-900/10">
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-white">Total Stakeholders Dues</td>
                                <td class="px-4 py-3"></td>
                                <td class="px-4 py-3 text-right">Rs. {{ number_format($totalOwnersDue, 2) }}</td>
                                <td class="px-4 py-3 text-right text-red-500">Rs. {{ number_format($totalOwnersPaid, 2) }}</td>
                                <td class="px-4 py-3 text-right text-gray-950 dark:text-white">Rs. {{ number_format($totalOwnersPending, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- SECTION 2: Party Head Summary (Receivables & Payables) --}}
            <div>
                <div class="mb-4 border-b border-gray-100 pb-2 dark:border-gray-800">
                    <h3 class="text-sm font-bold text-gray-850 dark:text-white/90">Section 2: Party Heads Ledger Summaries</h3>
                    <p class="text-xs text-gray-400 mt-0.5">Breakdown of outstanding amounts owed by third parties (Receivables) and to third parties (Payables).</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-gray-500 dark:text-gray-400">
                        <thead class="border-b border-gray-100 bg-gray-50/50 text-[10px] uppercase text-gray-500 dark:border-gray-800 dark:bg-gray-900/50">
                            <tr>
                                <th class="px-4 py-2.5" rowspan="2">Party Head Name</th>
                                <th class="px-4 py-2.5 text-center border-b border-gray-100 dark:border-gray-800" colspan="3">Receivables (Owed to Mall)</th>
                                <th class="px-4 py-2.5 text-center border-b border-gray-100 dark:border-gray-800" colspan="3">Payables (Owed by Mall)</th>
                            </tr>
                            <tr>
                                <th class="px-4 py-2 text-right">Due / Invoiced</th>
                                <th class="px-4 py-2 text-right">Received / Collected</th>
                                <th class="px-4 py-2 text-right">Net Receivable</th>
                                <th class="px-4 py-2 text-right">Due / Owed</th>
                                <th class="px-4 py-2 text-right">Paid / Outflow</th>
                                <th class="px-4 py-2 text-right">Net Payable</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800/80">
                            @forelse($partyRows as $row)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                    <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">
                                        {{ $row['party']->name }}
                                    </td>
                                    <!-- Receivables -->
                                    <td class="px-4 py-3 text-right">Rs. {{ number_format($row['rec_due'], 2) }}</td>
                                    <td class="px-4 py-3 text-right text-green-600 dark:text-green-400">Rs. {{ number_format($row['rec_paid'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                        {{ $row['net_rec'] > 0.01 ? 'Rs. ' . number_format($row['net_rec'], 2) : '—' }}
                                    </td>
                                    <!-- Payables -->
                                    <td class="px-4 py-3 text-right">Rs. {{ number_format($row['pay_due'], 2) }}</td>
                                    <td class="px-4 py-3 text-right text-red-500 dark:text-red-400">Rs. {{ number_format($row['pay_paid'], 2) }}</td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                        {{ $row['net_pay'] > 0.01 ? 'Rs. ' . number_format($row['net_pay'], 2) : '—' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-gray-400">
                                        No active party balances matching filters.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="border-t-2 border-gray-150 bg-gray-50/20 font-bold dark:border-gray-800 dark:bg-gray-900/10">
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-white">Total Party Balances</td>
                                <td class="px-4 py-3 text-right">Rs. {{ number_format($partyTotals['rec_due'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-green-600">Rs. {{ number_format($partyTotals['rec_paid'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-gray-950 dark:text-white">Rs. {{ number_format($partyTotals['net_rec'], 2) }}</td>
                                <td class="px-4 py-3 text-right">Rs. {{ number_format($partyTotals['pay_due'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-red-500">Rs. {{ number_format($partyTotals['pay_paid'], 2) }}</td>
                                <td class="px-4 py-3 text-right text-gray-950 dark:text-white">Rs. {{ number_format($partyTotals['net_pay'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
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
