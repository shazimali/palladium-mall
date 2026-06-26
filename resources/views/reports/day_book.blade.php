@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Day Book Report" />

    {{-- Date Range Selector Panel --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <form action="{{ route('reports.day-book') }}" method="GET" class="flex flex-col gap-4 sm:flex-row sm:items-end justify-between">
            <div class="flex flex-col sm:flex-row gap-4 flex-1">
                @php
                    $dateInput = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 w-full sm:w-48';
                    $dateLabel = 'mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400';
                @endphp
                <div>
                    <label class="{{ $dateLabel }}">Start Date</label>
                    <input type="date" name="start_date" value="{{ $startDate }}" class="{{ $dateInput }}" />
                </div>
                <div>
                    <label class="{{ $dateLabel }}">End Date</label>
                    <input type="date" name="end_date" value="{{ $endDate }}" class="{{ $dateInput }}" />
                </div>
                <div>
                    <label class="hidden sm:block {{ $dateLabel }}">&nbsp;</label>
                    <button type="submit" class="h-10 rounded-lg bg-brand-500 px-5 text-sm font-semibold text-white hover:bg-brand-600 transition-colors w-full sm:w-auto">
                        Apply Filter
                    </button>
                </div>
            </div>

            <div class="flex gap-2">
                <a href="{{ route('reports.day-book', ['start_date' => date('Y-m-d'), 'end_date' => date('Y-m-d')]) }}"
                   class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    Today
                </a>
                <a href="{{ route('reports.day-book', ['start_date' => date('Y-m-d', strtotime('-1 day')), 'end_date' => date('Y-m-d', strtotime('-1 day'))]) }}"
                   class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    Yesterday
                </a>
                <a href="{{ route('reports.day-book', ['start_date' => date('Y-m-01'), 'end_date' => date('Y-m-t')]) }}"
                   class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    This Month
                </a>
            </div>
        </form>
    </div>

    {{-- Metrics Summary Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <!-- Inflows (Receipts) -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Inflows (Receipts)</p>
                    <h4 class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">
                        Rs. {{ number_format($totalInflows, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-50 text-green-500 dark:bg-green-950/20 dark:text-green-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Outflows (Expenses) -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Outflows (Expenses)</p>
                    <h4 class="mt-2 text-2xl font-bold text-red-600 dark:text-red-400">
                        Rs. {{ number_format($totalOutflows, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-500 dark:bg-red-950/20 dark:text-red-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Net Balance -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Net Position / Balance</p>
                    <h4 class="mt-2 text-2xl font-bold {{ $netFlow >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                        Rs. {{ number_format($netFlow, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-500 dark:bg-blue-950/20 dark:text-blue-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    {{-- Inflows & Outflows Tables Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- INFLOWS (RECEIPTS) --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white/90 flex items-center gap-2">
                    <span class="inline-block h-2 w-2 rounded-full bg-green-500"></span>
                    Cash Inflow (Receipts)
                </h3>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                    {{ $inflows->count() }} Transactions
                </span>
            </div>

            <div class="overflow-x-auto border border-gray-100 rounded-lg dark:border-gray-800">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2">Time/Date</th>
                            <th class="px-3 py-2">Details</th>
                            <th class="px-3 py-2">Method</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($inflows as $inflow)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-3 py-2 text-xs">
                                    {{ $inflow->date ? $inflow->date->format('d M y') : $inflow->created_at->format('d M y') }}
                                </td>
                                <td class="px-3 py-2">
                                    @if($inflow->received_from_type === 'tenant')
                                        <div class="font-semibold text-gray-800 dark:text-white/90">
                                            👤 {{ $inflow->tenant ? $inflow->tenant->name : 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            Voucher: {{ $inflow->voucher_no }} • Units: {{ $inflow->payments->map(fn($p) => $p->unit?->unit_number)->filter()->unique()->implode(', ') ?: 'N/A' }}
                                        </div>
                                    @elseif($inflow->received_from_type === 'owner')
                                        <div class="font-semibold text-gray-800 dark:text-white/90">
                                            👤 Partner: {{ $inflow->owner ? $inflow->owner->name : 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            Voucher: {{ $inflow->voucher_no }} • Partnership Contribution
                                        </div>
                                    @else
                                        <div class="font-semibold text-gray-800 dark:text-white/90">
                                            👤 Misc: {{ $inflow->other_name ?: 'N/A' }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            Voucher: {{ $inflow->voucher_no }} • {{ $inflow->notes ?? 'Other Income' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-xs">
                                    <span class="capitalize font-mono text-gray-700 dark:text-gray-300">
                                        {{ $inflow->payment_method }}
                                    </span>
                                    @if($inflow->paymentAccount)
                                        <div class="text-[10px] text-gray-400">{{ $inflow->paymentAccount->name }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right font-bold text-green-600 dark:text-green-400">
                                    Rs. {{ number_format($inflow->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-8 text-center text-gray-400 dark:text-gray-600">
                                    No receipts recorded for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($inflows->isNotEmpty())
                        <tfoot class="bg-gray-50 dark:bg-gray-800 font-bold">
                            <tr>
                                <td colspan="3" class="px-3 py-2 text-gray-700 dark:text-gray-300 text-right">Total Inflow:</td>
                                <td class="px-3 py-2 text-right text-green-600 dark:text-green-400">
                                    Rs. {{ number_format($totalInflows, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        {{-- OUTFLOWS (PAYMENTS & EXPENSES) --}}
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white/90 flex items-center gap-2">
                    <span class="inline-block h-2 w-2 rounded-full bg-red-500"></span>
                    Cash Outflow (Payments & Expenses)
                </h3>
                <span class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                    {{ $outflows->count() }} Transactions
                </span>
            </div>

            <div class="overflow-x-auto border border-gray-100 rounded-lg dark:border-gray-800">
                <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Details</th>
                            <th class="px-3 py-2">Method</th>
                            <th class="px-3 py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($outflows as $outflow)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                <td class="px-3 py-2 text-xs">
                                    {{ $outflow->date->format('d M y') }}
                                </td>
                                <td class="px-3 py-2">
                                    @if($outflow instanceof \App\Models\Expense)
                                        <div class="font-semibold text-gray-800 dark:text-white/90">
                                            💸 {{ $outflow->expenseHead?->name ?? 'Expense' }}
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            Voucher: {{ $outflow->voucher_no }} • {{ $outflow->notes ?? 'No description' }}
                                        </div>
                                    @else
                                        <div class="font-semibold text-gray-800 dark:text-white/90">
                                            @if($outflow->is_advance)
                                                ⚠️ Advance Payout to: {{ $outflow->paid_to_type === 'owner' ? ($outflow->owner?->name ?? 'Partner') : ($outflow->other_name ?? 'N/A') }}
                                            @else
                                                📤 Payout to: {{ $outflow->paid_to_type === 'owner' ? ($outflow->owner?->name ?? 'Partner') : ($outflow->other_name ?? 'N/A') }}
                                            @endif
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            Voucher: {{ $outflow->voucher_no }} • {{ $outflow->notes ?? 'No description' }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-xs">
                                    <span class="capitalize font-mono text-gray-700 dark:text-gray-300">
                                        {{ $outflow->payment_method }}
                                    </span>
                                    @if($outflow->paymentAccount)
                                        <div class="text-[10px] text-gray-400">{{ $outflow->paymentAccount->name }}</div>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-right font-bold text-red-600 dark:text-red-400">
                                    Rs. {{ number_format($outflow->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-3 py-8 text-center text-gray-400 dark:text-gray-600">
                                    No outflows logged for this period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($outflows->isNotEmpty())
                        <tfoot class="bg-gray-50 dark:bg-gray-800 font-bold">
                            <tr>
                                <td colspan="3" class="px-3 py-2 text-gray-700 dark:text-gray-300 text-right">Total Outflow:</td>
                                <td class="px-3 py-2 text-right text-red-600 dark:text-red-400">
                                    Rs. {{ number_format($totalOutflows, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
@endsection
