@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="JV Vouchers" />

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

    <div
        x-data="{ 
            showTable: {{ (request()->anyFilled(['search', 'expense_head_id', 'status', 'payment_account_id', 'start_date', 'end_date']) || request()->has('show_search')) ? 'true' : 'false' }},
            payModalOpen: false,
            payVoucherId: null,
            payVoucherNo: '',
            payAmount: '',

            payAccountId: '',
            openPayAccount: false,
            searchPayAccount: '',
            highlightedPayAccountIndex: -1,

            payAccountOptions: [
                @foreach($paymentAccounts as $account)
                { id: '{{ $account->id }}', name: '{{ addslashes($account->name) }} (Available: Rs. {{ number_format($account->current_balance, 2) }})' },
                @endforeach
            ],

            get filteredPayAccounts() {
                if (!this.searchPayAccount) return this.payAccountOptions;
                let s = this.searchPayAccount.toLowerCase();
                return this.payAccountOptions.filter(a => a.name.toLowerCase().includes(s));
            },

            get selectedPayAccountName() {
                let selected = this.payAccountOptions.find(a => a.id == this.payAccountId);
                return selected ? selected.name : '';
            },

            selectPayAccount(opt) {
                this.payAccountId = opt.id;
                this.openPayAccount = false;
                this.searchPayAccount = '';
                this.highlightedPayAccountIndex = -1;
            }
        }">
        <x-common.component-card title="" desc="">

            {{-- Top bar --}}
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-center">
                <div class="flex flex-wrap items-center gap-3">
                    {{-- 1. New JV Voucher Button --}}
                    @if(auth()->user()->hasPermission('jv_vouchers.create') || auth()->user()->isSuperAdmin())
                        <a href="{{ route('jv-vouchers.create') }}"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-4xl font-bold text-white hover:bg-brand-600 transition-all shadow-md">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            New JV Voucher
                        </a>
                    @endif

                    {{-- 2. Search Button --}}
                    @if(auth()->user()->hasPermission('jv_vouchers.view') || auth()->user()->isSuperAdmin())
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

            {{-- Filter & Table Panel (Hidden by Default) --}}
            <div x-show="showTable" x-cloak class="mt-6">

                <!-- Summary Cards Row -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <!-- Total JV Amount Card -->
                    <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total JV Amount</p>
                                <h4 class="mt-1 text-xl font-black text-gray-900 dark:text-white">
                                    Rs. {{ number_format($totalJvAmount, 2) }}
                                </h4>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600 dark:bg-purple-950/20 dark:text-purple-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Unpaid Card -->
                    <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-4 shadow-theme-xs dark:border-amber-900/40 dark:bg-amber-950/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">Unpaid (Accrued)</p>
                                <h4 class="mt-1 text-xl font-black text-amber-900 dark:text-amber-300">
                                    Rs. {{ number_format($totalUnpaidAmount, 2) }}
                                </h4>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-amber-600 dark:bg-amber-900/40 dark:text-amber-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Total Paid Card -->
                    <div class="rounded-xl border border-green-200 bg-green-50/40 p-4 shadow-theme-xs dark:border-green-900/40 dark:bg-green-950/20">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-green-700 dark:text-green-400">Paid Amount</p>
                                <h4 class="mt-1 text-xl font-black text-green-900 dark:text-green-300">
                                    Rs. {{ number_format($totalPaidAmount, 2) }}
                                </h4>
                            </div>
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-green-600 dark:bg-green-900/40 dark:text-green-400">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters & Search -->
                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-100 dark:border-gray-800">
                        <h3 class="text-sm font-bold text-gray-800 dark:text-white flex items-center gap-2">
                            <span>🔍 Search & Filter JV Vouchers</span>
                        </h3>
                        <div class="flex items-center gap-2">
                            @if(auth()->user()->hasPermission('jv_vouchers.view') || auth()->user()->isSuperAdmin())
                                <a href="{{ route('jv-vouchers.print-list', request()->query()) }}" target="_blank"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-2xl font-bold text-white hover:bg-brand-600 transition-all shadow-sm cursor-pointer">
                                    🖨️ Print List
                                </a>
                            @endif
                            @if(request()->anyFilled(['search', 'status', 'expense_head_id', 'start_date', 'end_date']))
                                <a href="{{ route('jv-vouchers.index', ['show_search' => 1]) }}"
                                    class="rounded-lg border border-brand-500 px-3 py-1.5 text-2xl font-semibold text-gray-700 hover:bg-brand-500 hover:text-white dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-all">
                                    Clear Filters
                                </a>
                            @endif
                        </div>
                    </div>

                    <form x-ref="filterForm" action="{{ route('jv-vouchers.index') }}" method="GET"
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
                                placeholder="Search voucher #, expense head, ref..."
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <select name="status" @change="$refs.filterForm.submit()"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">All Statuses</option>
                                <option value="unpaid" {{ request('status') == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>

                        <!-- Expense Head Filter -->
                        <div>
                            <select name="expense_head_id" @change="$refs.filterForm.submit()"
                                class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <option value="">All Expense Categories</option>
                                @foreach($expenseHeads as $head)
                                    <option value="{{ $head->id }}" {{ request('expense_head_id') == $head->id ? 'selected' : '' }}>
                                        {{ $head->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Picker Fields -->
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

                {{-- DataTable --}}
                <div class="overflow-hidden border border-gray-200 rounded-xl dark:border-gray-800">
                    <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                        <thead class="text-xs uppercase bg-gray-50 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <tr>
                                <th class="px-4 py-3">Voucher #</th>
                                <th class="px-4 py-3">Voucher Date</th>
                                <th class="px-4 py-3">Expense Head</th>
                                <th class="px-4 py-3">Amount</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Payment Info</th>
                                <th class="px-4 py-3">User</th>
                                <th class="px-4 py-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse($vouchers as $voucher)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/50">
                                    <td class="px-4 py-3 font-mono font-bold text-brand-600 dark:text-brand-400">
                                        <a href="{{ route('jv-vouchers.show', $voucher->id) }}">
                                            {{ $voucher->voucher_no }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap font-medium text-gray-900 dark:text-white">
                                        {{ $voucher->date ? $voucher->date->format('M. d, Y') : '—' }}
                                    </td>
                                    <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white">
                                        {{ $voucher->expenseHead->name ?? 'Uncategorized' }}
                                    </td>
                                    <td class="px-4 py-3 font-black font-mono text-emerald-600 dark:text-emerald-400">
                                        Rs. {{ number_format($voucher->amount, 2) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($voucher->status === 'paid')
                                            <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2.5 py-1 text-xs font-bold text-green-800 dark:bg-green-900/40 dark:text-green-300">
                                                ● Paid
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                                                ● Unpaid
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs">
                                        @if($voucher->status === 'paid')
                                            <div class="font-bold text-gray-800 dark:text-gray-200">
                                                {{ $voucher->paymentAccount->name ?? 'Cash/Bank' }}
                                            </div>
                                            <div class="text-gray-500">
                                                {{ $voucher->paid_date ? $voucher->paid_date->format('M. d, Y') : '' }} {{ $voucher->payment_method ? '(' . $voucher->payment_method . ')' : '' }}
                                            </div>
                                        @else
                                            <span class="text-gray-400 italic">Not settled</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-xs text-gray-500">
                                        {{ $voucher->user->name ?? 'System' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            @if($voucher->status === 'unpaid' && (auth()->user()->hasPermission('jv_vouchers.pay') || auth()->user()->isSuperAdmin()))
                                                <button type="button"
                                                    @click="payVoucherId = {{ $voucher->id }}; payVoucherNo = '{{ $voucher->voucher_no }}'; payAmount = '{{ number_format($voucher->amount, 2) }}'; payAccountId = ''; openPayAccount = false; searchPayAccount = ''; payModalOpen = true"
                                                    class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-green-700 transition-all shadow-sm">
                                                    Mark Paid
                                                </button>
                                            @endif

                                            <a href="{{ route('jv-vouchers.show', $voucher->id) }}"
                                                class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-all">
                                                View
                                            </a>

                                            @if(auth()->user()->hasPermission('jv_vouchers.edit') || auth()->user()->isSuperAdmin())
                                                <a href="{{ route('jv-vouchers.edit', $voucher->id) }}"
                                                    class="rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-brand-700 transition-all shadow-xs">
                                                    Edit
                                                </a>
                                            @endif

                                            <a href="{{ route('jv-vouchers.print', $voucher->id) }}" target="_blank"
                                                class="rounded-lg bg-gray-900 px-3 py-1.5 text-xs font-bold text-white hover:bg-gray-800 transition-all shadow-xs">
                                                Print
                                            </a>

                                            @if(auth()->user()->hasPermission('jv_vouchers.delete') || auth()->user()->isSuperAdmin())
                                                <form action="{{ route('jv-vouchers.destroy', $voucher->id) }}" method="POST"
                                                    onsubmit="return confirm('Are you sure you want to delete JV Voucher {{ $voucher->voucher_no }}?');"
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
                                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                        No JV Vouchers found.
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

        {{-- Settle JV Voucher Modal (Big Font & Searchable Payment Account) --}}
        <div x-show="payModalOpen" x-cloak
            class="fixed inset-0 z-99999 flex items-center justify-center bg-black/50 p-4">
            <div @click.away="payModalOpen = false"
                class="w-full max-w-lg rounded-3xl bg-white p-6 sm:p-8 shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800 text-gray-900 dark:text-white">
                
                <div class="flex items-center justify-between border-b border-gray-200 pb-4 dark:border-gray-800">
                    <h3 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tight">
                        Settle JV Voucher: <span x-text="payVoucherNo" class="text-brand-600 dark:text-brand-400 font-mono"></span>
                    </h3>
                    <button @click="payModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-2xl font-bold cursor-pointer">
                        &times;
                    </button>
                </div>

                <form :action="'{{ url('jv-vouchers') }}/' + payVoucherId + '/pay'" method="POST" class="mt-5 space-y-5">
                    @csrf
                    
                    <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-900/40 flex items-center justify-between">
                        <span class="text-xs sm:text-sm font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">Amount to Settle</span>
                        <span class="font-black text-xl sm:text-2xl text-emerald-600 dark:text-emerald-400 font-mono" x-text="'Rs. ' + payAmount"></span>
                    </div>

                    <!-- Searchable Payment Account Dropdown -->
                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Payment Account (Bank/Cash) <span class="text-rose-500">*</span>
                        </label>
                        <div class="w-full relative" @click.away="openPayAccount = false; highlightedPayAccountIndex = -1">
                            <input type="hidden" name="payment_account_id" x-model="payAccountId" required>
                            <button type="button" @click="openPayAccount = !openPayAccount; if(openPayAccount) { $nextTick(() => $refs.payAccountSearchInput.focus()) }"
                                class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                <span x-text="selectedPayAccountName ? selectedPayAccountName : 'Select Payment Account...'" class="truncate"></span>
                                <span class="ml-2 text-xs opacity-60">▼</span>
                            </button>

                            <div x-show="openPayAccount" x-transition x-cloak
                                class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                    <input type="text" x-ref="payAccountSearchInput" x-model="searchPayAccount" placeholder="Type account name to search..."
                                        @keydown.arrow-down.prevent="highlightedPayAccountIndex = (highlightedPayAccountIndex + 1) % filteredPayAccounts.length"
                                        @keydown.arrow-up.prevent="highlightedPayAccountIndex = (highlightedPayAccountIndex - 1 + filteredPayAccounts.length) % filteredPayAccounts.length"
                                        @keydown.enter.prevent="if(highlightedPayAccountIndex >= 0 && filteredPayAccounts[highlightedPayAccountIndex]) selectPayAccount(filteredPayAccounts[highlightedPayAccountIndex])"
                                        @keydown.escape="openPayAccount = false; highlightedPayAccountIndex = -1"
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-base font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                </div>
                                <div class="max-h-[260px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                    <template x-for="(opt, index) in filteredPayAccounts" :key="opt.id">
                                        <button type="button" @click="selectPayAccount(opt)"
                                            @mouseenter="highlightedPayAccountIndex = index"
                                            class="w-full text-left px-3.5 py-2.5 rounded-xl transition-colors flex items-center justify-between"
                                            :class="payAccountId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedPayAccountIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                            <span x-text="opt.name" class="font-black text-base sm:text-lg truncate"></span>
                                            <span x-show="payAccountId == opt.id" class="font-black text-base">✓</span>
                                        </button>
                                    </template>
                                    <div x-show="filteredPayAccounts.length === 0" class="px-4 py-3 text-center text-xs font-semibold text-gray-400">
                                        No matching Account found
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Payment Method <span class="text-rose-500">*</span>
                        </label>
                        <select name="payment_method" required
                            class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Online">Online</option>
                        </select>
                    </div>

                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Payment Date <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="paid_date" value="{{ date('Y-m-d') }}" required
                            class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="mb-2 block text-xs sm:text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">
                            Reference / Cheque #
                        </label>
                        <input type="text" name="reference" placeholder="Optional cheque/ref #"
                            class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-4 py-3 text-base sm:text-lg font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
                        <button type="button" @click="payModalOpen = false"
                            class="rounded-xl border border-gray-300 dark:border-gray-700 px-5 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-gray-800 transition-all">
                            Cancel
                        </button>
                        <button type="submit"
                            class="rounded-xl bg-green-600 px-6 py-3 text-sm sm:text-base font-black text-white hover:bg-green-700 shadow-md transition-all cursor-pointer">
                            Confirm Settlement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
