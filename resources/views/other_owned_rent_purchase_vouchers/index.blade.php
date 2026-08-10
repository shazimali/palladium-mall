@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Other Owned Rent Purchase (ORP) Vouchers" />

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
    @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
            class="mb-4 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                    clip-rule="evenodd" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div x-data="{ 
            showTable: {{ (request()->anyFilled(['search', 'landlord_id', 'start_date', 'end_date', 'month']) || request()->has('show_search')) ? 'true' : 'false' }}
        }">
        <x-common.component-card title="" desc="">

            {{-- Top bar matching JV Vouchers --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
                <div class="flex flex-wrap items-center gap-3">
                    {{-- 1. New ORP Voucher Button --}}
                    @if(auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.create') || auth()->user()->isSuperAdmin())
                        <a href="{{ route('other-owned-rent-purchase-vouchers.create') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-4xl font-bold text-white hover:bg-brand-600 transition-all shadow-md">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            New ORP Voucher
                        </a>
                    @endif

                    {{-- 2. Search Button --}}
                    @if(auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.view') || auth()->user()->isSuperAdmin())
                        <button type="button" @click="showTable = !showTable"
                            :class="showTable ? 'bg-brand-500 text-white' : 'bg-brand-500 text-white hover:bg-brand-600'"
                            class="inline-flex items-center gap-2 rounded-xl px-5 py-2.5 text-4xl font-bold transition-all shadow-md cursor-pointer">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <span x-text="showTable ? 'Hide Search' : 'Search'"></span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Filter & Table Panel --}}
            <div x-show="showTable" x-cloak class="mt-6">

                {{-- Summary Cards Row matching JV Vouchers --}}
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total ORP Amount</p>
                                <h4 class="mt-1 text-xl font-black text-gray-900 dark:text-white">
                                    Rs. {{ number_format($totalAmount, 2) }}
                                </h4>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950/20 dark:text-purple-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Search & Filter Form matching JV Vouchers --}}
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <span>🔍 Search & Filter ORP Vouchers</span>
                        </h3>
                        <div class="flex items-center gap-2">
                            @if(request()->anyFilled(['search', 'landlord_id', 'start_date', 'end_date', 'month']))
                                <a href="{{ route('other-owned-rent-purchase-vouchers.index', ['show_search' => 1]) }}"
                                    class="rounded-lg border border-brand-500 px-3 py-1.5 text-2xl font-semibold text-gray-700 hover:bg-brand-500 hover:text-white dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-all">
                                    Clear Filters
                                </a>
                            @endif
                        </div>
                    </div>

                    <form x-ref="filterForm" action="{{ route('other-owned-rent-purchase-vouchers.index') }}" method="GET"
                        class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">
                        <input type="hidden" name="show_search" value="1">

                        <!-- Search Input -->
                        <div class="relative col-span-1 lg:col-span-2">
                            <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                                <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20" fill="none">
                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                        d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                                </svg>
                            </span>
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Search voucher #, landlord, unit..."
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>

                        <!-- Landlord Filter -->
                        <div>
                            <select name="landlord_id" @change="$refs.filterForm.submit()"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">All Landlords</option>
                                @foreach($landlords as $ll)
                                    <option value="{{ $ll->id }}" {{ request('landlord_id') == $ll->id ? 'selected' : '' }}>
                                        {{ $ll->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Month Filter -->
                        <div>
                            <input type="month" name="month" value="{{ request('month') }}" @change="$refs.filterForm.submit()"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>

                        <!-- Date Range -->
                        <div class="flex items-center gap-2">
                            <input type="date" name="start_date" value="{{ request('start_date') }}"
                                placeholder="Start Date" @change="$refs.filterForm.submit()"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                            <span class="text-xs text-gray-400">to</span>
                            <input type="date" name="end_date" value="{{ request('end_date') }}"
                                placeholder="End Date" @change="$refs.filterForm.submit()"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-2 py-2 text-xs text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>

                        <button type="submit" class="hidden">Submit</button>
                    </form>
                </div>

                {{-- DataTable matching JV Vouchers --}}
                <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Voucher #</th>
                                <th class="px-4 py-3">Date</th>
                                <th class="px-4 py-3">Landlord</th>
                                <th class="px-4 py-3">Unit</th>
                                <th class="px-4 py-3">Other Tenant</th>
                                <th class="px-4 py-3">Billing Month</th>
                                <th class="px-4 py-3">Amount</th>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse($vouchers as $voucher)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                    <td class="px-4 py-3 font-mono font-bold text-brand-600 dark:text-brand-400">
                                        <a href="{{ route('other-owned-rent-purchase-vouchers.show', $voucher->id) }}">
                                            {{ $voucher->voucher_no }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                        {{ $voucher->date ? $voucher->date->format('M. d, Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                        {{ $voucher->landlord->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $voucher->unit?->unit_number ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-700 dark:text-gray-300">
                                        {{ $voucher->otherTenant?->name ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-800 dark:text-gray-200">
                                        {{ $voucher->month ? $voucher->month->format('F Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-black font-mono text-emerald-600 dark:text-emerald-400">
                                        Rs. {{ number_format($voucher->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ $voucher->user->name ?? 'System' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <a href="{{ route('other-owned-rent-purchase-vouchers.show', $voucher->id) }}"
                                                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-all">
                                                View
                                            </a>

                                            @if(auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.edit') || auth()->user()->isSuperAdmin())
                                                <a href="{{ route('other-owned-rent-purchase-vouchers.edit', $voucher->id) }}"
                                                    class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-700 transition-all shadow-xs">
                                                    Edit
                                                </a>
                                            @endif

                                            <a href="{{ route('other-owned-rent-purchase-vouchers.print', $voucher->id) }}" target="_blank"
                                                class="rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-gray-800 transition-all shadow-xs">
                                                Print
                                            </a>

                                            @if(auth()->user()->hasPermission('other_owned_rent_purchase_vouchers.delete') || auth()->user()->isSuperAdmin())
                                                <form action="{{ route('other-owned-rent-purchase-vouchers.destroy', $voucher->id) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete ORP Voucher {{ $voucher->voucher_no }}?');"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-red-700 transition-all shadow-xs">
                                                        Delete
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                        No Other Owned Rent Purchase Vouchers found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="mt-4">
                    {{ $vouchers->links() }}
                </div>

            </div>
        </x-common.component-card>
    </div>
@endsection
