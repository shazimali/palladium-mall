@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Receiving Vouchers" />

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
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <x-common.component-card title="All Cash/Bank Receipts" desc="Manage cash and bank receiving vouchers">

        {{-- Top bar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap gap-2">
                <span
                    class="inline-flex items-center rounded-lg bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                    Total: {{ $vouchers->total() }}
                </span>
            </div>

            <div class="flex items-center gap-2">
                @if(request()->anyFilled(['search', 'payment_account_id', 'date_from', 'date_to']))
                    <a href="{{ route('receiving-vouchers.index') }}"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Clear
                    </a>
                @endif
                @if(auth()->user()->hasPermission('receiving_vouchers.create') || auth()->user()->isSuperAdmin())
                    <a href="{{ route('receiving-vouchers.create') }}"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        New Receiving Voucher
                    </a>
                @endif
            </div>
        </div>

        <!-- Filters & Search -->
        <div
            class="my-6 rounded-xl border border-gray-200 bg-white p-4 shadow-theme-xs dark:border-gray-800 dark:bg-white/[0.03]">
            <form x-ref="filterForm" action="{{ route('receiving-vouchers.index') }}" method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5">

                <!-- Search Input -->
                <div class="relative col-span-1 lg:col-span-2">
                    <span class="absolute -translate-y-1/2 pointer-events-none left-4 top-1/2">
                        <svg class="fill-gray-500 dark:fill-gray-400" width="18" height="18" viewBox="0 0 20 20" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd" d="M3.04175 9.37363C3.04175 5.87693 5.87711 3.04199 9.37508 3.04199C12.8731 3.04199 15.7084 5.87693 15.7084 9.37363C15.7084 12.8703 12.8731 15.7053 9.37508 15.7053C5.87711 15.7053 3.04175 12.8703 3.04175 9.37363ZM9.37508 1.54199C5.04902 1.54199 1.54175 5.04817 1.54175 9.37363C1.54175 13.6991 5.04902 17.2053 9.37508 17.2053C11.2674 17.2053 13.003 16.5344 14.357 15.4176L17.177 18.238C17.4699 18.5309 17.9448 18.5309 18.2377 18.238C18.5306 17.9451 18.5306 17.4703 18.2377 17.1774L15.418 14.3573C16.5365 13.0033 17.2084 11.2669 17.2084 9.37363C17.2084 5.04817 13.7011 1.54199 9.37508 1.54199Z" />
                        </svg>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search voucher #, Flat/Shop, tenant, ref..."
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent py-2 pl-11 pr-4 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                </div>

                <!-- Flat/Shop Filter (Searchable Dropdown) -->
                <div x-data="{
                    open: false,
                    search: '',
                    selectedId: '{{ request('unit_id') }}',
                    selectedLabel: '{{ request('unit_id') ? ($units->firstWhere('id', request('unit_id'))?->unit_number ?? 'All Flats/Shops') : 'All Flats/Shops' }}',
                    highlightedIndex: -1,
                    units: [
                        { id: '', label: 'All Flats/Shops' },
                        @foreach($units as $unit)
                            { id: '{{ $unit->id }}', label: '{{ addslashes($unit->unit_number) }}' },
                        @endforeach
                    ],
                    get filteredUnits() {
                        if (!this.search) return this.units;
                        let q = this.search.toLowerCase();
                        return this.units.filter(u => u.label.toLowerCase().includes(q));
                    },
                    selectUnit(u) {
                        this.selectedId = u.id;
                        this.selectedLabel = u.label;
                        this.open = false;
                        this.search = '';
                        this.highlightedIndex = -1;
                        this.$nextTick(() => {
                            $refs.filterForm.submit();
                        });
                    },
                    moveHighlight(direction) {
                        let list = this.filteredUnits;
                        if (list.length === 0) return;
                        this.highlightedIndex = (this.highlightedIndex + direction + list.length) % list.length;
                    },
                    selectHighlighted() {
                        let list = this.filteredUnits;
                        if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
                            this.selectUnit(list[this.highlightedIndex]);
                        }
                    }
                }" class="relative" @click.outside="open = false; highlightedIndex = -1">
                    <input type="hidden" name="unit_id" :value="selectedId">
                    <div tabindex="0"
                         @click="open = !open; if(open) { $nextTick(() => $refs.unitSearchInput.focus()) }"
                         @keydown.space.prevent="open = !open; if(open) { $nextTick(() => $refs.unitSearchInput.focus()) }"
                         @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.unitSearchInput.focus()) }"
                         class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 cursor-pointer flex justify-between items-center border-gray-300 dark:border-gray-700 h-10">
                        <span x-text="selectedLabel" class="truncate"></span>
                        <svg class="h-4 w-4 text-gray-500 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <div x-show="open" x-cloak
                         class="absolute left-0 z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 py-2">
                        <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700">
                            <input x-ref="unitSearchInput"
                                   x-model="search"
                                   @keydown.arrow-down.prevent="moveHighlight(1)"
                                   @keydown.arrow-up.prevent="moveHighlight(-1)"
                                   @keydown.enter.prevent="selectHighlighted()"
                                   @keydown.escape.prevent="open = false; highlightedIndex = -1"
                                   type="text"
                                   placeholder="Search flat/shop..."
                                   class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <ul class="max-h-60 overflow-y-auto mt-1">
                            <template x-if="filteredUnits.length === 0">
                                <li class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">No matches found.</li>
                            </template>
                            <template x-for="(u, index) in filteredUnits" :key="u.id">
                                <li @click="selectUnit(u)"
                                    @mouseenter="highlightedIndex = index"
                                    :class="{
                                        'bg-brand-50 text-brand-900 dark:bg-brand-950/20 dark:text-brand-400': highlightedIndex === index,
                                        'text-gray-800 dark:text-gray-200': highlightedIndex !== index
                                    }"
                                    class="px-4 py-2 text-xs cursor-pointer hover:bg-brand-50 dark:hover:bg-brand-950/20 transition-colors flex justify-between items-center">
                                    <span x-text="u.label" class="font-medium"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <!-- Account Filter (Searchable Dropdown) -->
                <div x-data="{
                    open: false,
                    search: '',
                    selectedId: '{{ request('payment_account_id') }}',
                    selectedLabel: '{{ request('payment_account_id') ? ($paymentAccounts.firstWhere('id', request('payment_account_id'))?->name ?? 'All Accounts') : 'All Accounts' }}',
                    highlightedIndex: -1,
                    accounts: [
                        { id: '', label: 'All Accounts' },
                        @foreach($paymentAccounts as $account)
                            { id: '{{ $account->id }}', label: '{{ addslashes($account->name) }}' },
                        @endforeach
                    ],
                    get filteredAccounts() {
                        if (!this.search) return this.accounts;
                        let q = this.search.toLowerCase();
                        return this.accounts.filter(a => a.label.toLowerCase().includes(q));
                    },
                    selectAccount(a) {
                        this.selectedId = a.id;
                        this.selectedLabel = a.label;
                        this.open = false;
                        this.search = '';
                        this.highlightedIndex = -1;
                        this.$nextTick(() => {
                            $refs.filterForm.submit();
                        });
                    },
                    moveHighlight(direction) {
                        let list = this.filteredAccounts;
                        if (list.length === 0) return;
                        this.highlightedIndex = (this.highlightedIndex + direction + list.length) % list.length;
                    },
                    selectHighlighted() {
                        let list = this.filteredAccounts;
                        if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
                            this.selectAccount(list[this.highlightedIndex]);
                        }
                    }
                }" class="relative" @click.outside="open = false; highlightedIndex = -1">
                    <input type="hidden" name="payment_account_id" :value="selectedId">
                    <div tabindex="0"
                         @click="open = !open; if(open) { $nextTick(() => $refs.accountSearchInput.focus()) }"
                         @keydown.space.prevent="open = !open; if(open) { $nextTick(() => $refs.accountSearchInput.focus()) }"
                         @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.accountSearchInput.focus()) }"
                         class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 cursor-pointer flex justify-between items-center border-gray-300 dark:border-gray-700 h-10">
                        <span x-text="selectedLabel" class="truncate"></span>
                        <svg class="h-4 w-4 text-gray-500 transition-transform duration-200 flex-shrink-0" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <div x-show="open" x-cloak
                         class="absolute left-0 z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 py-2">
                        <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700">
                            <input x-ref="accountSearchInput"
                                   x-model="search"
                                   @keydown.arrow-down.prevent="moveHighlight(1)"
                                   @keydown.arrow-up.prevent="moveHighlight(-1)"
                                   @keydown.enter.prevent="selectHighlighted()"
                                   @keydown.escape.prevent="open = false; highlightedIndex = -1"
                                   type="text"
                                   placeholder="Search account..."
                                   class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                        <ul class="max-h-60 overflow-y-auto mt-1">
                            <template x-if="filteredAccounts.length === 0">
                                <li class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">No matches found.</li>
                            </template>
                            <template x-for="(a, index) in filteredAccounts" :key="a.id">
                                <li @click="selectAccount(a)"
                                    @mouseenter="highlightedIndex = index"
                                    :class="{
                                        'bg-brand-50 text-brand-900 dark:bg-brand-950/20 dark:text-brand-400': highlightedIndex === index,
                                        'text-gray-800 dark:text-gray-200': highlightedIndex !== index
                                    }"
                                    class="px-4 py-2 text-xs cursor-pointer hover:bg-brand-50 dark:hover:bg-brand-950/20 transition-colors flex justify-between items-center">
                                    <span x-text="a.label" class="font-medium"></span>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

                <!-- Date Picker Fields -->
                <div class="flex items-center gap-2">
                    <input type="text" id="date_from" name="date_from" value="{{ request('date_from') }}" placeholder="Date From" autocomplete="off"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-xs text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
                    <span class="text-xs text-gray-400">to</span>
                    <input type="text" id="date_to" name="date_to" value="{{ request('date_to') }}" placeholder="Date To" autocomplete="off"
                        class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-10 w-full rounded-lg border border-gray-300 bg-transparent px-3 py-2 text-xs text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90" />
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
                        <th class="px-4 py-3">Date</th>
                        <th class="px-4 py-3">Flat / Shop</th>
                        <th class="px-4 py-3">Payment Account</th>
                        <th class="px-4 py-3">Method / Ref</th>
                        <th class="px-4 py-3">Amount</th>
                        <th class="px-4 py-3">Recorded By</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($vouchers as $index => $voucher)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-3 font-semibold text-gray-800 dark:text-white/90">
                                {{ $voucher->voucher_no }}
                            </td>
                            <td class="px-4 py-3 text-xs">{{ $voucher->date->format('d M Y') }}</td>
                            <td class="px-4 py-3 text-xs font-semibold text-gray-700 dark:text-gray-300">
                                @if($voucher->received_from_type === 'tenant')
                                    @php
                                        $unitNumber = '—';
                                        $tenantName = '';
                                        if ($voucher->tenant) {
                                            $unitNumber = $voucher->tenant->unit->unit_number ?? '—';
                                            $tenantName = $voucher->tenant->name;
                                        } else {
                                            $firstPayment = $voucher->payments->first();
                                            if ($firstPayment) {
                                                $unitNumber = $firstPayment->unit->unit_number ?? '—';
                                                $tenantName = $firstPayment->otherTenant->name ?? '—';
                                            }
                                        }
                                    @endphp
                                    <div class="unit-badge-lg">{{ $unitNumber }}</div>
                                    @if($tenantName)
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 font-normal">{{ $tenantName }}</div>
                                    @endif
                                @elseif($voucher->received_from_type === 'owner')
                                    <div>—</div>
                                    <div class="text-[11px] text-brand-600 dark:text-brand-400 mt-0.5 font-normal">👤 {{ $voucher->owner->name ?? 'Deleted Owner' }} (Owner)</div>
                                @else
                                    <div>—</div>
                                    <div class="text-[11px] text-gray-500 mt-0.5 font-normal">{{ $voucher->other_name }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-xs">
                                {{ $voucher->paymentAccount->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-xs">
                                <div>{{ $voucher->payment_method ? ucfirst(str_replace('_', ' ', $voucher->payment_method)) : '—' }}</div>
                                @if($voucher->reference)
                                    <div class="text-[10px] text-gray-400 mt-0.5">Ref: {{ $voucher->reference }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-bold text-green-600">
                                Rs. {{ number_format($voucher->amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">
                                {{ $voucher->user->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    {{-- View --}}
                                    <a href="{{ route('receiving-vouchers.show', $voucher) }}"
                                        class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white transition-colors"
                                        title="View Details">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                        </svg>
                                    </a>

                                    {{-- Print --}}
                                    <a href="{{ route('receiving-vouchers.print', $voucher) }}" target="_blank"
                                        class="inline-flex items-center rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white transition-colors"
                                        title="Print Receipt Voucher">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4" />
                                        </svg>
                                    </a>

                                    {{-- Edit --}}
                                    @if(auth()->user()->isSuperAdmin())
                                        <a href="{{ route('receiving-vouchers.edit', $voucher) }}"
                                            class="inline-flex items-center rounded-lg p-1.5 text-blue-500 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-900/20 transition-colors"
                                            title="Edit Voucher">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </a>
                                    @endif

                                    {{-- Delete/Cancel --}}
                                    @if(auth()->user()->hasPermission('receiving_vouchers.delete') || auth()->user()->isSuperAdmin())
                                        <form action="{{ route('receiving-vouchers.destroy', $voucher) }}" method="POST" x-data
                                            @submit.prevent="confirmAction($el, 'Are you sure you want to cancel and delete this receiving voucher? This will roll back any tenant paid balances associated with it.', 'Cancel / Delete?', 'Yes, Delete')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="inline-flex items-center rounded-lg p-1.5 text-red-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 transition-colors"
                                                title="Cancel Voucher">
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
                            <td colspan="8" class="px-4 py-12 text-center text-gray-400 dark:text-gray-600">
                                <svg class="mx-auto mb-3 h-10 w-10 opacity-40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                No receiving vouchers found. <a href="{{ route('receiving-vouchers.create') }}" class="text-brand-500 hover:underline">Record one now.</a>
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

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#date_from', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    onChange: function(selectedDates, dateStr, instance) {
                        if (dateStr) {
                            instance.element.form.submit();
                        }
                    }
                });

                flatpickr('#date_to', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    onChange: function(selectedDates, dateStr, instance) {
                        if (dateStr) {
                            instance.element.form.submit();
                        }
                    }
                });
            }
        });
    </script>
@endpush
