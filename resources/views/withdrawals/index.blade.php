@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Owner Withdrawals" />

    {{-- Flash Messages --}}
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    <x-common.component-card title="Withdrawals Log" desc="Track withdrawals made by managing owners/partners from their profit share">

        {{-- Top bar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Showing Page: {{ $withdrawals->currentPage() }} of {{ $withdrawals->lastPage() }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                @if(request()->filled('search'))
                    <a href="{{ route('withdrawals.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                        Clear Filters
                    </a>
                @endif
                @if(auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('owners.create') || auth()->user()->hasPermission('payment_vouchers.create'))
                    <a href="{{ route('withdrawals.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Record Withdrawal
                    </a>
                @endif
            </div>
        </div>

        <!-- Filters & Search Form -->
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('withdrawals.index') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-4 items-end">
                @php
                    $filterInput = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
                    $filterLabel = 'mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400';
                @endphp

                <!-- Search Input -->
                <div class="sm:col-span-2 relative">
                    <label class="{{ $filterLabel }}">Search Withdrawal</label>
                    <div class="relative">
                        <span class="absolute -translate-y-1/2 pointer-events-none left-3.5 top-1/2">
                            <svg class="fill-gray-500 dark:fill-gray-400" width="16" height="16" viewBox="0 0 20 20" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Voucher #, owner, reference..."
                            class="{{ $filterInput }} pl-10" />
                    </div>
                </div>

                <!-- Filter Action Button -->
                <div class="sm:col-span-1 md:col-span-2 flex justify-end">
                    <button type="submit" class="w-full px-6 flex justify-center items-center h-10 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-sm font-semibold text-gray-800 dark:text-gray-200 transition-colors">
                        Apply Search
                    </button>
                </div>
            </form>
        </div>

        {{-- DataTable --}}
        <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-3">Voucher #</th>
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Owner (Partner)</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3">Paid From Account</th>
                        <th class="px-4 py-3">Ref/Cheque</th>
                        <th class="px-4 py-3">Recorded By</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($withdrawals as $wd)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 font-mono font-bold text-gray-900 dark:text-white/90">
                                {{ $wd->voucher_no }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ \Carbon\Carbon::parse($wd->date)->format('d M Y') }}
                            </td>
                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white">
                                👤 {{ $wd->owner->name ?? 'Partner' }}
                            </td>
                            <td class="px-4 py-3 font-bold text-red-650 dark:text-red-400 text-right">
                                Rs. {{ number_format($wd->amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $wd->paymentAccount->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $wd->reference ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $wd->user->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Edit --}}
                                    @if(auth()->user()->isSuperAdmin())
                                        <a href="{{ route('withdrawals.edit', $wd) }}"
                                            class="inline-flex items-center rounded-lg p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-900/20 transition-colors"
                                            title="Edit Withdrawal">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- Delete/Cancel --}}
                                    @if(auth()->user()->isSuperAdmin())
                                        <form action="{{ route('withdrawals.destroy', $wd) }}" method="POST" x-data
                                            @submit.prevent="confirmAction($el, 'Are you sure you want to delete this withdrawal of Rs. {{ number_format($wd->amount) }}? This will restore the owner\'s due balance.', 'Delete Withdrawal?', 'Yes, Delete')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center rounded-lg p-1.5 text-red-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 transition-colors"
                                                title="Delete Withdrawal">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center justify-center gap-2">
                                    <svg class="h-8 w-8 text-gray-300 dark:text-gray-700" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0a2 2 0 01-2 2H6a2 2 0 01-2-2m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5" />
                                    </svg>
                                    <span>No withdrawals recorded yet.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $withdrawals->links() }}
        </div>
    </x-common.component-card>
@endsection
