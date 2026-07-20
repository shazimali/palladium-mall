@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Payment Vouchers" />

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

    {{-- Summary Widget Cards --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
        <!-- Total Paid Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Payouts (Filtered)</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white/90">
                        Rs. {{ number_format($totalAmount, 2) }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-red-50 text-red-500 dark:bg-red-950/20 dark:text-red-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Count Card -->
        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Vouchers</p>
                    <h4 class="mt-2 text-2xl font-bold text-gray-900 dark:text-white/90">
                        {{ $vouchers->total() }}
                    </h4>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-500 dark:bg-blue-950/20 dark:text-blue-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <x-common.component-card title="Payment Vouchers List" desc="Manage cash/bank outflows paid out to partners, owners, or other miscellaneous payees">

        {{-- Top bar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-6">
            <div class="flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Showing Page: {{ $vouchers->currentPage() }} of {{ $vouchers->lastPage() }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'paid_to_type', 'is_advance', 'payment_method', 'start_date', 'end_date']))
                    <a href="{{ route('payment-vouchers.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5">
                        Clear Filters
                    </a>
                @endif
                @if(auth()->user()->hasPermission('payment_vouchers.create') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('payment-vouchers.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Create Payment Voucher
                    </a>
                @endif
            </div>
        </div>

        <!-- Filters & Search Form -->
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form action="{{ route('payment-vouchers.index') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-6 items-end">
                @php
                    $filterInput = 'dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
                    $filterLabel = 'mb-1 block text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400';
                @endphp

                <!-- Search Input -->
                <div class="sm:col-span-2 relative">
                    <label class="{{ $filterLabel }}">Search Voucher / Details</label>
                    <div class="relative">
                        <span class="absolute -translate-y-1/2 pointer-events-none left-3.5 top-1/2">
                            <svg class="fill-gray-500 dark:fill-gray-400" width="16" height="16" viewBox="0 0 20 20" fill="none">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                            </svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Voucher #, name, notes..."
                            class="{{ $filterInput }} pl-10" />
                    </div>
                </div>

                <!-- Payee Type -->
                <div>
                    <label class="{{ $filterLabel }}">Payee Type</label>
                    <select name="paid_to_type" class="{{ $filterInput }}">
                        <option value="">All Types</option>
                        <option value="owner" {{ request('paid_to_type') === 'owner' ? 'selected' : '' }}>Managing Owner</option>
                        <option value="tenant" {{ request('paid_to_type') === 'tenant' ? 'selected' : '' }}>Tenant (Refund)</option>
                        <option value="other" {{ request('paid_to_type') === 'other' ? 'selected' : '' }}>Other (Misc)</option>
                    </select>
                </div>

                <!-- Is Advance -->
                <div>
                    <label class="{{ $filterLabel }}">Advance Status</label>
                    <select name="is_advance" class="{{ $filterInput }}">
                        <option value="">All</option>
                        <option value="1" {{ request('is_advance') === '1' ? 'selected' : '' }}>Advance Only</option>
                        <option value="0" {{ request('is_advance') === '0' ? 'selected' : '' }}>Standard Only</option>
                    </select>
                </div>

                <!-- Start Date -->
                <div>
                    <label class="{{ $filterLabel }}">From Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="{{ $filterInput }}" />
                </div>

                <!-- End Date -->
                <div>
                    <label class="{{ $filterLabel }}">To Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="{{ $filterInput }}" />
                </div>

                <!-- Filter Action Button -->
                <div class="sm:col-span-1 md:col-span-6 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto px-6 flex justify-center items-center h-10 rounded-lg bg-gray-100 hover:bg-gray-200 dark:bg-gray-800 dark:hover:bg-gray-700 text-sm font-semibold text-gray-800 dark:text-gray-200 transition-colors">
                        Apply Filters
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
                        <th class="px-4 py-3">Paid To</th>
                        <th class="px-4 py-3">Type</th>
                        <th class="px-4 py-3 text-right">Amount</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3">Account</th>
                        <th class="px-4 py-3">Ref/Cheque</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($vouchers as $voucher)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 font-mono font-bold text-gray-900 dark:text-white/90">
                                {{ $voucher->voucher_no }}
                            </td>
                            <td class="px-4 py-3">
                                {{ $voucher->date->format('d M Y') }}
                            </td>
                             <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white">
                                @if($voucher->paid_to_type === 'owner')
                                    👤 {{ $voucher->owner->name ?? 'Partner' }}
                                @elseif($voucher->paid_to_type === 'tenant')
                                    👤 {{ $voucher->tenant->name ?? $voucher->other_name }} @if($voucher->unit) <span class="text-xs font-normal text-gray-500">({{ $voucher->unit->unit_number }})</span> @endif
                                @elseif($voucher->paid_to_type === 'landlord')
                                    👤 {{ $voucher->landlord->name ?? $voucher->other_name }}
                                @else
                                    👤 {{ $voucher->other_name }}
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($voucher->paid_to_type === 'owner')
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-950/20 dark:text-indigo-400">Partner</span>
                                @elseif($voucher->paid_to_type === 'tenant')
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold bg-teal-50 text-teal-700 dark:bg-teal-950/20 dark:text-teal-400">Tenant (Refund)</span>
                                @elseif($voucher->paid_to_type === 'landlord')
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold bg-orange-50 text-orange-700 dark:bg-orange-950/20 dark:text-orange-400">Landlord (Payout)</span>
                                @else
                                    <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">Other</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-bold text-red-600 dark:text-red-400 text-right">
                                Rs. {{ number_format($voucher->amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($voucher->is_advance)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-800 dark:bg-amber-950/25 dark:text-amber-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span>
                                        Advance
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-1 text-xs font-semibold text-green-800 dark:bg-green-950/25 dark:text-green-400">
                                        <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                        Standard
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $voucher->paymentAccount->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 font-mono text-xs">{{ $voucher->reference ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- Print --}}
                                    <a href="{{ route('payment-vouchers.print', $voucher) }}" target="_blank"
                                        class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors"
                                        title="Print Invoice">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                                        </svg>
                                    </a>

                                    {{-- Show --}}
                                    <a href="{{ route('payment-vouchers.show', $voucher) }}"
                                        class="inline-flex items-center rounded-lg p-1.5 text-brand-500 hover:bg-brand-50 hover:text-brand-700 dark:hover:bg-brand-950/20 transition-colors"
                                        title="View Details">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    {{-- Edit --}}
                                    @if(auth()->user()->isSuperAdmin())
                                        <a href="{{ route('payment-vouchers.edit', $voucher) }}"
                                            class="inline-flex items-center rounded-lg p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-900/20 transition-colors"
                                            title="Edit Voucher">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- Delete/Cancel --}}
                                    @if(auth()->user()->hasPermission('payment_vouchers.delete') || auth()->user()->isSuperAdmin())
                                        <form action="{{ route('payment-vouchers.destroy', $voucher) }}" method="POST" x-data
                                            @submit.prevent="confirmAction($el, 'Are you sure you want to delete and cancel this payment voucher of Rs. {{ number_format($voucher->amount) }}? This will undo any balance changes.', 'Cancel / Delete?', 'Yes, Delete')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center rounded-lg p-1.5 text-red-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 transition-colors"
                                                title="Cancel / Delete">
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
                            <td colspan="9" class="px-4 py-12 text-center text-gray-400 dark:text-gray-600">
                                <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                No payment vouchers found. <a href="{{ route('payment-vouchers.create') }}" class="text-brand-500 hover:underline">Create first one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($vouchers->hasPages())
            <div class="border-t border-gray-100 p-4 dark:border-gray-800">
                {{ $vouchers->links() }}
            </div>
        @endif

    </x-common.component-card>
@endsection
