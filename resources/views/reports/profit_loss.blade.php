@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Profit & Loss Statement" />

    @php
        $thisMonthFrom = date('Y-m-01');
        $thisMonthTo = date('Y-m-t');

        $lastMonthFrom = date('Y-m-01', strtotime('-1 month'));
        $lastMonthTo = date('Y-m-t', strtotime('-1 month'));

        $thisYearFrom = date('Y-01-01');
        $thisYearTo = date('Y-12-31');

        $curFrom = $filters['date_from'] ?? '';
        $curTo = $filters['date_to'] ?? '';

        $isThisMonth = ($curFrom === $thisMonthFrom && $curTo === $thisMonthTo);
        $isLastMonth = ($curFrom === $lastMonthFrom && $curTo === $lastMonthTo);
        $isThisYear = ($curFrom === $thisYearFrom && $curTo === $thisYearTo);
    @endphp

    {{-- Filter Panel --}}
    <x-common.component-card title="Statement Filters" desc="Select date range to analyze profit & loss distribution">
        <form method="GET" action="{{ route('reports.profit-loss') }}">
            <div class="flex flex-col gap-5 sm:flex-row sm:items-end justify-between">
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 flex-1">
                    {{-- Date From --}}
                    <div>
                        <label for="date_from" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Date From
                        </label>
                        <input type="text" id="date_from" name="date_from" value="{{ $filters['date_from'] }}"
                            placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    {{-- Date To --}}
                    <div>
                        <label for="date_to" class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Date To
                        </label>
                        <input type="text" id="date_to" name="date_to" value="{{ $filters['date_to'] }}"
                            placeholder="YYYY-MM-DD" autocomplete="off"
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-end gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors w-full sm:w-auto cursor-pointer">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z" />
                            </svg>
                            Filter Statement
                        </button>
                        <a href="{{ route('reports.profit-loss') }}"
                            class="inline-flex items-center justify-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors w-full sm:w-auto">
                            Reset
                        </a>
                    </div>
                </div>

                {{-- Quick Presets --}}
                <div class="flex gap-2 items-end">
                    <a href="{{ route('reports.profit-loss', ['date_from' => $thisMonthFrom, 'date_to' => $thisMonthTo]) }}"
                        class="rounded-lg px-3 py-2.5 text-xs font-semibold transition-all shadow-2xs {{ $isThisMonth ? 'bg-brand-500 text-white border border-brand-500' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        This Month
                    </a>
                    <a href="{{ route('reports.profit-loss', ['date_from' => $lastMonthFrom, 'date_to' => $lastMonthTo]) }}"
                        class="rounded-lg px-3 py-2.5 text-xs font-semibold transition-all shadow-2xs {{ $isLastMonth ? 'bg-brand-500 text-white border border-brand-500' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        Last Month
                    </a>
                    <a href="{{ route('reports.profit-loss', ['date_from' => $thisYearFrom, 'date_to' => $thisYearTo]) }}"
                        class="rounded-lg px-3 py-2.5 text-xs font-semibold transition-all shadow-2xs {{ $isThisYear ? 'bg-brand-500 text-white border border-brand-500' : 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700' }}">
                        This Year
                    </a>
                </div>
            </div>
        </form>
    </x-common.component-card>

    {{-- Statement Metadata & Actions --}}
    <div
        class="my-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between bg-white dark:bg-white/[0.02] border border-gray-250 dark:border-gray-800 p-4 rounded-xl">
        <div>
            <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Palladium Mall Statement Period</h3>
            <p class="text-xs text-gray-500 mt-0.5">Showing accounts breakdown from <strong
                    class="text-gray-700 dark:text-gray-350">{{ Carbon\Carbon::parse($filters['date_from'])->format('d M Y') }}</strong>
                to <strong
                    class="text-gray-700 dark:text-gray-350">{{ Carbon\Carbon::parse($filters['date_to'])->format('d M Y') }}</strong>
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('reports.profit-loss.excel', $filters) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-green-500 px-4 py-2.5 text-sm font-medium text-green-600 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export Excel
            </a>
            <a href="{{ route('reports.profit-loss.pdf', $filters) }}"
                class="inline-flex items-center gap-2 rounded-lg border border-red-400 px-4 py-2.5 text-sm font-medium text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
                Export PDF
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Income</p>
            <p class="mt-2 text-2xl font-bold text-green-600">Rs. {{ number_format($totalIncome, 2) }}</p>
            <p class="text-[10px] text-gray-400 mt-1">Tenant collections + Misc vouchers</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Expenses</p>
            <p class="mt-2 text-2xl font-bold text-red-500">Rs. {{ number_format($totalExpenses, 2) }}</p>
            <p class="text-[10px] text-gray-400 mt-1">Recorded operating expenses</p>
        </div>
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Net Profit / Loss</p>
            <p class="mt-2 text-2xl font-bold {{ $netProfitLoss >= 0 ? 'text-brand-600' : 'text-red-600' }}">
                Rs. {{ number_format($netProfitLoss, 2) }}
            </p>
            <p class="text-[10px] text-gray-400 mt-1">Distributable net earnings</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Income Details --}}
        <x-common.component-card title="Income Breakdown" desc="Revenue collected from billing & other sources">
            <div class="space-y-4">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                        <tr>
                            <th class="px-4 py-2.5">Revenue Category</th>
                            <th class="px-4 py-2.5 text-right">Collected Amount (Rs.)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        {{-- Tenant Collections --}}
                        @foreach($incomeBreakdown as $type => $amount)
                            @if($amount > 0 || in_array($type, ['rent_pm_mall', 'maint_pm_mall']))
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                        @switch($type)
                                            @case('rent_pm_mall')
                                                🏠 Rent Collected (PM Mall Units)
                                                @break
                                            @case('maint_pm_mall')
                                                🛠️ Maintenance Charges (PM Mall Units)
                                                @break
                                            @case('extra_pm_mall')
                                                💵 Extra Payments (PM Mall Units)
                                                @break
                                            @case('rent_other_owned')
                                                🏠 Rent Collected (Landlord / Other-Owned Units)
                                                @break
                                            @case('maint_other_owned')
                                                🛠️ Maintenance Charges (Landlord / Other-Owned Units)
                                                @break
                                            @case('extra_other_owned')
                                                💵 Extra Payments (Landlord / Other-Owned Units)
                                                @break
                                            @case('other')
                                                📑 Other Tenant Receipts (Unallocated Vouchers)
                                                @break
                                            @default
                                                ⚡ Utility: {{ ucfirst($type) }}
                                        @endswitch
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-800 dark:text-white">
                                        {{ number_format($amount, 2) }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach

                        {{-- Miscellaneous Income --}}
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-semibold">
                                💵 Miscellaneous Receipts (Vouchers)
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-800 dark:text-white">
                                {{ number_format($miscIncome, 2) }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="font-bold text-sm bg-gray-50 dark:bg-gray-800">
                            <td class="px-4 py-3 text-gray-850 dark:text-white">Total Revenue:</td>
                            <td class="px-4 py-3 text-right text-green-600">Rs. {{ number_format($totalIncome, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </x-common.component-card>

        {{-- Expense Details --}}
        <x-common.component-card title="Expense Breakdown" desc="Summary of expenses grouped by head">
            @if(empty($expensesByHead))
                <div
                    class="py-12 text-center text-gray-400 dark:text-gray-600 border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                    <p class="text-xs">No operating expenses recorded for this period.</p>
                </div>
            @else
                <div class="space-y-4">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-2.5">Expense Category (Head)</th>
                                <th class="px-4 py-2.5 text-right">Spent Amount (Rs.)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($expensesByHead as $expense)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.01]">
                                    <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                        ❌ {{ $expense['name'] }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-850 dark:text-white">
                                        {{ number_format($expense['amount'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-bold text-sm bg-gray-50 dark:bg-gray-800">
                                <td class="px-4 py-3 text-gray-850 dark:text-white">Total Operating Expenses:</td>
                                <td class="px-4 py-3 text-right text-red-500">Rs. {{ number_format($totalExpenses, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </x-common.component-card>

    </div>

    {{-- Partnership Distribution Table --}}
    <div class="mt-6">
        <x-common.component-card title="Managing Partners Earnings Distribution"
            desc="Splitting net profit/loss based on partner profiles percentage shares">

            @if(abs($totalOwnerSharePct - 100.00) > 0.01)
                <div
                    class="mb-4 rounded-xl bg-orange-50 border border-orange-200 p-4 text-xs text-orange-700 dark:bg-orange-950/10 dark:border-orange-850 dark:text-orange-400">
                    ⚠️ The active owners partnership percentages sum is
                    <strong>{{ number_format($totalOwnerSharePct, 2) }}%</strong>, not 100.00%. Correct calculations require
                    partnership shares to distribute fully to 100%. Adjust shares in <a href="{{ route('owners.index') }}"
                        class="underline font-bold">Owners Profile settings</a>.
                </div>
            @endif

            @if(empty($distribution))
                <div
                    class="py-12 text-center text-gray-400 dark:text-gray-600 border-2 border-dashed border-gray-250 dark:border-gray-800 rounded-xl bg-white dark:bg-gray-900">
                    <p class="text-xs">No partners registered. Add mall partners in <a href="{{ route('owners.index') }}"
                            class="text-brand-500 hover:underline">Owners Profile</a> to calculate profit splits.</p>
                </div>
            @else
                <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800 bg-white dark:bg-gray-900">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Partner Name</th>
                                <th class="px-4 py-3">Partnership Share (%)</th>
                                <th class="px-4 py-3 text-right">Earning / Loss Split (Rs.)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @foreach($distribution as $row)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02]">
                                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white">
                                        👤 {{ $row['name'] }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-brand-600 dark:text-brand-400">
                                        {{ number_format($row['percentage'], 2) }}%
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-bold {{ $row['share'] >= 0 ? 'text-green-600' : 'text-red-500' }}">
                                        Rs. {{ number_format($row['share'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 dark:bg-gray-800 font-bold text-sm">
                                <td class="px-4 py-3">Totals</td>
                                <td class="px-4 py-3 text-brand-600">{{ number_format($totalOwnerSharePct, 2) }}%</td>
                                <td class="px-4 py-3 text-right {{ $netProfitLoss >= 0 ? 'text-green-600' : 'text-red-500' }}">
                                    Rs. {{ number_format($netProfitLoss, 2) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
        </x-common.component-card>
    </div>
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