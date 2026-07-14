@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Party Receivables & Payables Summary" />

    {{-- Filter Panel --}}
    <x-common.component-card title="Statement Filters" desc="Filter party dues and transactions by date range and name">
        <form method="GET" action="{{ route('reports.party-dues') }}">
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 items-end">
                {{-- Search --}}
                <div>
                    <label for="search" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Search Party Name
                    </label>
                    <input type="text" id="search" name="search"
                        value="{{ request('search') }}"
                        placeholder="Search by name or phone..."
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                {{-- Date From --}}
                <div>
                    <label for="date_from" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Date From
                    </label>
                    <x-form.date-picker 
                        id="date_from" 
                        name="date_from"
                        placeholder="Start date" 
                        defaultDate="{{ request('date_from') }}" 
                    />
                </div>

                {{-- Date To --}}
                <div>
                    <label for="date_to" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Date To
                    </label>
                    <x-form.date-picker 
                        id="date_to" 
                        name="date_to"
                        placeholder="End date" 
                        defaultDate="{{ request('date_to') }}" 
                    />
                </div>

                {{-- Actions --}}
                <div class="flex gap-2">
                    <button type="submit"
                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors w-full sm:w-auto">
                        🔍 Filter Dues
                    </button>
                    <a href="{{ route('reports.party-dues') }}"
                        class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors w-full sm:w-auto">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </x-common.component-card>

    <div class="my-6"></div>

    {{-- Dues Summary Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Receivable Logged</p>
            <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">Rs. {{ number_format($totals['rec_due'], 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">Dues we raised to external parties</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Received (Inflows)</p>
            <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">Rs. {{ number_format($totals['rec_paid'], 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">Amount collected via GRVs</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Payable Logged</p>
            <p class="mt-2 text-2xl font-bold text-indigo-600 dark:text-indigo-400">Rs. {{ number_format($totals['pay_due'], 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">Dues we owe to suppliers/contractors</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Paid (Outflows)</p>
            <p class="mt-2 text-2xl font-bold text-emerald-600 dark:text-emerald-400">Rs. {{ number_format($totals['pay_paid'], 2) }}</p>
            <p class="mt-1 text-[10px] text-gray-400">Paid out via Payment Vouchers</p>
        </div>
    </div>

    {{-- Net Balances Card + Export --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.02] p-4">
        <div class="text-sm text-gray-600 dark:text-gray-400 space-y-1">
            <p class="font-semibold text-gray-800 dark:text-white/90">Net Party Balances Overview</p>
            <p>Net Receivable (Owed to Us): <strong class="text-green-600">Rs. {{ number_format($totals['net_rec'], 2) }}</strong></p>
            <p>Net Payable (We Owe Them): <strong class="text-red-500">Rs. {{ number_format($totals['net_pay'], 2) }}</strong></p>
        </div>
        <div class="flex gap-2 shrink-0">
            <a href="{{ route('reports.party-dues.pdf', ['search' => request('search'), 'date_from' => request('date_from'), 'date_to' => request('date_to')]) }}"
               class="inline-flex items-center gap-2 rounded-lg border border-red-400 px-4 py-2.5 text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export PDF
            </a>
        </div>
    </div>

    {{-- All Parties Summary Table --}}
    <x-common.component-card title="Party Dues Overview" desc="Running balances for all registered parties with outstanding balances (receivables vs payables)">
        
        
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="border-b border-gray-100 dark:border-gray-800">
                    <tr>
                        <th rowspan="2" class="pb-3 pr-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider align-bottom">Party / Recipient</th>
                        <th colspan="3" class="pb-2 text-center font-bold text-xs uppercase tracking-wider text-green-700 dark:text-green-400 border-b border-green-100 dark:border-green-950">Receivable (Owed to Us)</th>
                        <th colspan="3" class="pb-2 text-center font-bold text-xs uppercase tracking-wider text-red-700 dark:text-red-400 border-b border-red-100 dark:border-red-950">Payable (We Owe)</th>
                        <th rowspan="2" class="pb-3 pl-4 font-semibold text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider text-center align-bottom">Statement</th>
                    </tr>
                    <tr>
                        <th class="py-2 px-3 text-right font-semibold text-gray-400 text-[10px] uppercase">Gross Due</th>
                        <th class="py-2 px-3 text-right font-semibold text-gray-400 text-[10px] uppercase">Received</th>
                        <th class="py-2 px-3 text-right font-bold text-green-700 dark:text-green-400 text-[10px] uppercase">Net Owed</th>
                        
                        <th class="py-2 px-3 text-right font-semibold text-gray-400 text-[10px] uppercase">Gross Due</th>
                        <th class="py-2 px-3 text-right font-semibold text-gray-400 text-[10px] uppercase">Paid</th>
                        <th class="py-2 px-3 text-right font-bold text-red-700 dark:text-red-400 text-[10px] uppercase">Net Owed</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-gray-800/60">
                    @forelse($rows as $row)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="py-3.5 pr-4">
                                <div class="font-semibold text-gray-800 dark:text-white/90">{{ $row['party']->name }}</div>
                                @if($row['party']->phone)
                                    <div class="text-xs text-gray-400">{{ $row['party']->phone }}</div>
                                @endif
                            </td>
                            
                            {{-- Receivables --}}
                            <td class="py-3.5 px-3 text-right text-gray-600 dark:text-gray-400">
                                {{ $row['rec_due'] > 0 ? 'Rs. ' . number_format($row['rec_due'], 2) : '—' }}
                            </td>
                            <td class="py-3.5 px-3 text-right text-gray-600 dark:text-gray-400">
                                {{ $row['rec_paid'] > 0 ? 'Rs. ' . number_format($row['rec_paid'], 2) : '—' }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-bold {{ $row['net_rec'] > 0 ? 'text-green-600' : ($row['net_rec'] < 0 ? 'text-blue-500' : 'text-gray-400') }}">
                                {{ $row['net_rec'] != 0 ? 'Rs. ' . number_format($row['net_rec'], 2) : '—' }}
                            </td>
                            
                            {{-- Payables --}}
                            <td class="py-3.5 px-3 text-right text-gray-600 dark:text-gray-400">
                                {{ $row['pay_due'] > 0 ? 'Rs. ' . number_format($row['pay_due'], 2) : '—' }}
                            </td>
                            <td class="py-3.5 px-3 text-right text-gray-600 dark:text-gray-400">
                                {{ $row['pay_paid'] > 0 ? 'Rs. ' . number_format($row['pay_paid'], 2) : '—' }}
                            </td>
                            <td class="py-3.5 px-3 text-right font-bold {{ $row['net_pay'] > 0 ? 'text-red-500' : ($row['net_pay'] < 0 ? 'text-blue-500' : 'text-gray-400') }}">
                                {{ $row['net_pay'] != 0 ? 'Rs. ' . number_format($row['net_pay'], 2) : '—' }}
                            </td>
                            
                            {{-- Action to ledger --}}
                            <td class="py-3.5 pl-4 text-center">
                                <a href="{{ route('ledgers.party', ['party_id' => $row['party']->id]) }}"
                                   class="inline-flex items-center gap-1 rounded-md border border-brand-500/25 bg-brand-50/50 px-2.5 py-1.5 text-xs font-semibold text-brand-700 hover:bg-brand-500 hover:text-white dark:border-brand-500/10 dark:bg-brand-950/20 dark:text-brand-400 dark:hover:bg-brand-500 dark:hover:text-white transition-all duration-200">
                                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    Ledger
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-400">
                                No party head profiles found matching search criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($rows) > 0)
                <tfoot class="border-t-2 border-gray-200 dark:border-gray-700 font-bold text-gray-800 dark:text-white/90">
                    <tr>
                        <td class="pt-3 pr-4 text-sm">TOTALS</td>
                        
                        {{-- Receivables --}}
                        <td class="pt-3 px-3 text-right">Rs. {{ number_format($totals['rec_due'], 2) }}</td>
                        <td class="pt-3 px-3 text-right">Rs. {{ number_format($totals['rec_paid'], 2) }}</td>
                        <td class="pt-3 px-3 text-right text-green-600">Rs. {{ number_format($totals['net_rec'], 2) }}</td>
                        
                        {{-- Payables --}}
                        <td class="pt-3 px-3 text-right">Rs. {{ number_format($totals['pay_due'], 2) }}</td>
                        <td class="pt-3 px-3 text-right">Rs. {{ number_format($totals['pay_paid'], 2) }}</td>
                        <td class="pt-3 px-3 text-right text-red-500">Rs. {{ number_format($totals['net_pay'], 2) }}</td>
                        
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </x-common.component-card>

    <div class="mt-6 text-xs text-gray-400">Report generated at: {{ $generatedAt->format('d M Y, h:i A') }}</div>
@endsection
