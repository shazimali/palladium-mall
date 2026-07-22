@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Managing Owner Dues" />

    {{-- Filter Bar --}}
    <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-xs dark:border-gray-800 dark:bg-white/[0.03]">
        <form action="{{ route('owners.dues') }}" method="GET" class="flex flex-wrap items-end justify-between gap-4">
            <div class="flex flex-wrap items-end gap-4">
                {{-- Date From --}}
                <div class="w-full sm:w-44">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">From Date</label>
                    <x-form.date-picker 
                        id="date_from" 
                        name="date_from"
                        placeholder="Select From Date" 
                        defaultDate="{{ $dateFrom }}" 
                    />
                </div>

                {{-- Date To --}}
                <div class="w-full sm:w-44">
                    <label class="mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">To Date</label>
                    <x-form.date-picker 
                        id="date_to" 
                        name="date_to"
                        placeholder="Select To Date" 
                        defaultDate="{{ $dateTo }}" 
                    />
                </div>

                <button type="submit"
                    class="h-10 rounded-lg bg-brand-500 px-5 text-sm font-semibold text-white shadow-xs hover:bg-brand-600 transition-colors cursor-pointer">
                    Apply Filter
                </button>

                @if(request()->filled('date_from') || request()->filled('date_to'))
                    <a href="{{ route('owners.dues') }}"
                        class="h-10 inline-flex items-center rounded-lg border border-gray-300 px-4 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Reset to Current Month
                    </a>
                @endif
            </div>

            <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                Period: <span class="font-bold text-gray-800 dark:text-gray-200">{{ \Carbon\Carbon::parse($dateFrom)->format('d M Y') }}</span> to <span class="font-bold text-gray-800 dark:text-gray-200">{{ \Carbon\Carbon::parse($dateTo)->format('d M Y') }}</span>
            </div>
        </form>
    </div>

    {{-- Summary Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-gray-250 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Gross Mall Revenue</p>
            <p class="mt-2 text-2xl font-bold text-green-600">Rs. {{ number_format($totalIncome, 2) }}</p>
            <p class="text-[10px] text-gray-400 mt-1">Tenant collections & general receipts in period</p>
        </div>
        <div class="rounded-xl border border-gray-250 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Mall Expenses</p>
            <p class="mt-2 text-2xl font-bold text-red-500">Rs. {{ number_format($totalExpenses, 2) }}</p>
            <p class="text-[10px] text-gray-400 mt-1">Operational expenses in period</p>
        </div>
        <div class="rounded-xl border border-gray-250 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Net Distributable Profit</p>
            <p class="mt-2 text-2xl font-bold text-brand-600">
                Rs. {{ number_format($netProfit, 2) }}
            </p>
            <p class="text-[10px] text-gray-400 mt-1">Net profit pool for managing owners in period</p>
        </div>
    </div>

    {{-- Main Table Card --}}
    <x-common.component-card title="Dues Statement of Managing Owners" desc="Partnership profit distributions, withdrawals, and remaining balances">
        <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Owner / Partner</th>
                        <th class="px-4 py-3 text-center">Share %</th>
                        <th class="px-4 py-3 text-right">Profit Share (Rs.)</th>
                        <th class="px-4 py-3 text-right">Withdrawn to Date (Rs.)</th>
                        <th class="px-4 py-3 text-right">Remaining Dues (Rs.)</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($ownersData as $owner)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90">
                                {{ $owner['name'] }}
                            </td>
                            <td class="px-4 py-3 text-center font-medium text-gray-700 dark:text-gray-300">
                                {{ number_format($owner['partnership_percentage'], 2) }}%
                            </td>
                            <td class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300">
                                {{ number_format($owner['profit_share'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-right text-orange-600 font-semibold">
                                {{ number_format($owner['total_paid'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold {{ $owner['due_amount'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-gray-450 dark:text-gray-600' }}">
                                {{ number_format($owner['due_amount'], 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Ledger Link --}}
                                    <a href="/ledgers/owner?owner_id={{ $owner['id'] }}" 
                                       class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-850/50 transition-colors"
                                       title="View Transaction Ledger">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        Ledger
                                    </a>

                                    {{-- Withdraw Button --}}
                                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('payment_vouchers.create'))
                                        @if($owner['due_amount'] > 0.01)
                                            <a href="{{ route('withdrawals.create', ['owner_id' => $owner['id']]) }}"
                                               class="inline-flex items-center gap-1 rounded-lg bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-green-700 transition-colors"
                                               title="Initiate payout / withdrawal">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Withdraw
                                            </a>
                                        @else
                                            <button disabled
                                                    class="inline-flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800/60 px-3 py-1.5 text-xs font-semibold text-gray-400 dark:text-gray-600 cursor-not-allowed border border-gray-200 dark:border-gray-700/30"
                                                    title="No dues available to withdraw">
                                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                                </svg>
                                                Withdraw
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                                No managing owners found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-common.component-card>
@endsection
