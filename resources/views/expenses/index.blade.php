@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Expense Vouchers" />

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Summary Widget Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
        <!-- Total Expense Card -->
        <!-- <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Expenses (Filtered)</p>
                                <h4 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white/90">
                                    Rs. {{ number_format($totalExpenses, 2) }}
                                </h4>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-500 dark:bg-red-950/20 dark:text-red-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div> -->

        <!-- Count Card -->
        <!-- <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Vouchers Count</p>
                                <h4 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white/90">
                                    {{ $expenses->total() }}
                                </h4>
                            </div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-500 dark:bg-blue-950/20 dark:text-blue-400">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                        </div>
                    </div> -->
    </div>

    <div
        x-data="{ showTable: {{ (request()->anyFilled(['search', 'expense_head_id', 'payment_method', 'start_date', 'end_date']) || request()->has('show_search')) ? 'true' : 'false' }} }">
        <x-common.component-card title="" desc="">

            {{-- Top bar --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
                <div class="flex flex-wrap items-center gap-3">
                    {{-- 1. New Expense Voucher Button --}}
                    @if(auth()->user()->hasPermission('expenses.create') || auth()->user()->isSuperAdmin())
                        <a href="{{ route('expenses.create') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-bold text-white hover:bg-brand-600 transition-all shadow-md">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            New Expense Voucher
                        </a>
                    @endif

                    {{-- 2. Search Button --}}
                    @if(auth()->user()->hasPermission('expenses.search') || auth()->user()->hasPermission('expenses.view') || auth()->user()->isSuperAdmin())
                        <button type="button" @click="showTable = !showTable"
                            :class="showTable ? 'bg-brand-500 text-white' : 'bg-brand-500 text-white hover:bg-brand-600'"
                            class="inline-flex items-center gap-2 rounded-xl px-4 py-2 text-sm font-bold transition-all shadow-md cursor-pointer">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span x-text="showTable ? 'Hide Search' : 'Search'"></span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Filter & Table Panel (Hidden by Default) --}}
            <div x-show="showTable" x-cloak class="mt-6">
                <form action="{{ route('expenses.index') }}" method="GET"
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-5 items-end mb-6">
                    <input type="hidden" name="show_search" value="1">
                    @php
                        $filterInput = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
                        $filterLabel = 'mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400';
                    @endphp

                    <!-- Search Input -->
                    <div class="sm:col-span-2 relative">
                        <label class="{{ $filterLabel }}">Search details / ref</label>
                        <div class="relative">
                            <span class="absolute -translate-y-1/2 pointer-events-none left-3.5 top-1/2">
                                <svg class="fill-gray-500 dark:fill-gray-400" width="16" height="16" viewBox="0 0 20 20"
                                    fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                                </svg>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Voucher #, description, ref..." class="{{ $filterInput }} pl-10" />
                        </div>
                    </div>

                    <!-- Expense Head Filter -->
                    <div>
                        <label class="{{ $filterLabel }}">Category</label>
                        <select name="expense_head_id" class="{{ $filterInput }}">
                            <option value="">All Categories</option>
                            @foreach($expenseHeads as $head)
                                <option value="{{ $head->id }}" {{ request('expense_head_id') == $head->id ? 'selected' : '' }}>
                                    {{ $head->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Payment Method Filter -->
                    <div>
                        <label class="{{ $filterLabel }}">Payment Method</label>
                        <select name="payment_method" class="{{ $filterInput }}">
                            <option value="">All Methods</option>
                            <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank" {{ request('payment_method') === 'bank' ? 'selected' : '' }}>Bank Transfer
                            </option>
                            <option value="cheque" {{ request('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque
                            </option>
                            <option value="other" {{ request('payment_method') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- Start Date -->
                    <div>
                        <label class="{{ $filterLabel }}">From Date</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}"
                            class="{{ $filterInput }}" />
                    </div>

                    <!-- End Date -->
                    <div>
                        <label class="{{ $filterLabel }}">To Date</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="{{ $filterInput }}" />
                    </div>

                    <!-- Filter Action Buttons -->
                    <div class="sm:col-span-1 md:col-span-5 flex flex-wrap items-center justify-end gap-3">
                        <a href="{{ route('expenses.print-list', request()->query()) }}" target="_blank"
                            class="px-5 flex items-center gap-2 h-10 rounded-lg bg-brand-500 hover:bg-brand-600 text-sm font-bold text-white transition-colors shadow-sm cursor-pointer">
                            🖨️ Print List Report
                        </a>
                        @if(request()->anyFilled(['search', 'expense_head_id', 'payment_method', 'start_date', 'end_date']))
                            <a href="{{ route('expenses.index', ['show_search' => 1]) }}"
                                class="px-4 flex items-center h-10 rounded-lg border border-gray-300 dark:border-gray-700 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                Clear Filters
                            </a>
                        @endif
                        <button type="submit"
                            class="px-6 flex items-center h-10 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-sm font-semibold text-gray-800 dark:text-gray-200 transition-colors">
                            Apply Filters
                        </button>
                    </div>
                </form>

                {{-- DataTable --}}
                <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                    <table class="w-full text-xs text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-[11px] uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-2.5">Voucher #</th>
                                <th class="px-3 py-2.5">Date</th>
                                <th class="px-3 py-2.5">Category (Head)</th>
                                <th class="px-3 py-2.5 text-right">Amount</th>
                                <th class="px-3 py-2.5">Paid From (Account)</th>
                                <th class="px-3 py-2.5 w-64 min-w-[16rem] max-w-[16rem]">Remarks</th>
                                <th class="px-3 py-2.5 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-xs">
                            @forelse($expenses as $expense)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-3 py-2 font-mono font-bold text-gray-900 dark:text-white/90 text-xs">
                                        {{ $expense->voucher_no }}
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $expense->date->format('d M Y') }}
                                    </td>
                                    <td class="px-3 py-2 font-semibold text-gray-700 dark:text-gray-300 text-xs">
                                        {{ $expense->expenseHead->name }}
                                    </td>
                                    <td class="px-3 py-2 font-bold text-red-600 dark:text-red-400 text-right text-xs">
                                        Rs. {{ number_format($expense->amount, 2) }}
                                    </td>
                                    <td class="px-3 py-2 text-xs">
                                        {{ $expense->paymentAccount ? $expense->paymentAccount->name : '—' }}
                                    </td>
                                    <td class="px-3 py-2 w-64 min-w-[16rem] max-w-[16rem] truncate text-xs"
                                        title="{{ $expense->notes }}">
                                        {{ $expense->notes ?? '—' }}
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        <div class="flex items-center justify-end gap-1.5 text-xs">
                                            <a href="{{ route('expenses.show', $expense) }}"
                                                class="rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                                View
                                            </a>
                                            <a href="{{ route('expenses.print', $expense) }}"
                                                onclick="window.open(this.href,'_blank','width=800,height=800,scrollbars=yes'); return false;"
                                                class="rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-[11px] font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                                                Print
                                            </a>
                                            @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('expenses.edit'))
                                                <a href="{{ route('expenses.edit', $expense) }}"
                                                    class="rounded-lg border border-blue-200 bg-blue-50 px-2.5 py-1 text-[11px] font-semibold text-blue-600 hover:bg-blue-100 dark:border-blue-900/30 dark:bg-blue-950/20 dark:text-blue-400 dark:hover:bg-blue-900/30 transition-colors">
                                                    Edit
                                                </a>
                                            @endif
                                            @if(auth()->user()->hasPermission('expenses.delete') || auth()->user()->isSuperAdmin())
                                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" x-data
                                                    @submit.prevent="confirmAction($el, 'Are you sure you want to cancel and delete this Expense Voucher of Rs. {{ number_format($expense->amount) }}? This will reverse any balances.', 'Cancel / Delete?', 'Yes, Delete')"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-100 dark:border-red-900/30 dark:bg-red-950/20 dark:text-red-400 dark:hover:bg-red-900/30 transition-colors">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-3 py-10 text-center text-xs text-gray-400 dark:text-gray-600">
                                        <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor"
                                            stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                        No expense vouchers found. <a href="{{ route('expenses.create') }}"
                                            class="text-brand-500 hover:underline">Create first one.</a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($expenses->hasPages())
                    <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                        {{ $expenses->links() }}
                    </div>
                @endif
            </div> {{-- End showTable --}}

        </x-common.component-card>
    </div>
@endsection