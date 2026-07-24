@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm font-semibold text-gray-500 dark:text-gray-400">
            <a href="{{ route('payment-vouchers.index') }}" class="hover:text-brand-500">Paid Vouchers</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Edit Paid Voucher: {{ $voucher->voucher_no }}</span>
        </div>

        <x-common.component-card :title="'Edit Paid Voucher: ' . $voucher->voucher_no"
            desc="Modify payout details. Restricted to Super Administrators only.">
            <form action="{{ route('payment-vouchers.update', $voucher) }}" method="POST" class="space-y-6"
                @submit.prevent="handleSubmit($event)" x-data="{ 
                                paidToType: '{{ old('paid_to_type', $voucher->paid_to_type) }}',
                                selectedBalance: null,
                                selectedAccountName: '',
                                amount: '{{ old('amount', $voucher->amount) }}',
                                displayAmount: '',
                                originalAccountId: '{{ $voucher->payment_account_id }}',
                                originalAmount: '{{ $voucher->amount }}',
                                selectedTenantId: '{{ old('tenant_id', $voucher->tenant_id) }}',
                                tenantDeposits: [],
                                selectedUnitId: '{{ old('unit_id', $voucher->unit_id) }}',
                                selectedUnitDeposit: null,
                                selectedLandlordId: '{{ old('landlord_id', $voucher->landlord_id) }}',
                                landlordBalanceInfo: null,
                                handleSubmit(event) {
                                    if (this.selectedBalance !== null && this.selectedBalance !== '' && this.amount !== '') {
                                        let amt = parseFloat(this.amount);
                                        let bal = this.adjustedBalance;
                                        if (amt > bal) {
                                            Swal.fire({
                                                title: 'Insufficient Balance',
                                                text: 'The selected Payment Account does not have sufficient balance. Available balance: Rs. ' + bal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}),
                                                icon: 'error',
                                                confirmButtonText: 'OK',
                                                customClass: {
                                                    confirmButton: 'inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors mx-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2'
                                                },
                                                buttonsStyling: false
                                            });
                                            return;
                                        }
                                    }

                                    if (this.paidToType === 'tenant' && this.selectedUnitDeposit !== null && this.amount !== '') {
                                        let amt = parseFloat(this.amount);
                                        let bal = parseFloat(this.selectedUnitDeposit.pending_refund);
                                        if (amt > bal) {
                                            Swal.fire({
                                                title: 'Limit Exceeded',
                                                text: 'Payment amount exceeds security deposit refund limit of Rs. ' + bal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '.',
                                                icon: 'error',
                                                confirmButtonText: 'OK',
                                                customClass: {
                                                    confirmButton: 'inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors mx-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2'
                                                },
                                                buttonsStyling: false
                                            });
                                            return;
                                        }
                                    }

                                    if (this.paidToType === 'landlord' && this.landlordBalanceInfo !== null && this.amount !== '') {
                                        let amt = parseFloat(this.amount);
                                        let bal = parseFloat(this.landlordBalanceInfo.payable_amount);
                                        // Adjust for original amount if updating same landlord
                                        if (this.selectedLandlordId == '{{ $voucher->landlord_id }}') {
                                            bal += parseFloat(this.originalAmount);
                                        }
                                        if (amt > bal) {
                                            Swal.fire({
                                                title: 'Limit Exceeded',
                                                text: 'Payment amount exceeds the landlord\'s available negative balance of Rs. ' + bal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '.',
                                                icon: 'error',
                                                confirmButtonText: 'OK',
                                                customClass: {
                                                    confirmButton: 'inline-flex items-center justify-center rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors mx-2 cursor-pointer focus:outline-none focus:ring-2 focus:ring-brand-500 focus:ring-offset-2'
                                                },
                                                buttonsStyling: false
                                            });
                                            return;
                                        }
                                    }

                                    event.target.submit();
                                },
                                get adjustedBalance() {
                                    if (this.selectedBalance === null || this.selectedBalance === '') return 0;
                                    let balance = parseFloat(this.selectedBalance);
                                    let opt = document.querySelector('select[name=payment_account_id]').selectedOptions[0];
                                    if (opt && opt.value === this.originalAccountId) {
                                        balance += parseFloat(this.originalAmount);
                                    }
                                    return balance;
                                },
                                fetchTenantDeposits(tenantId) {
                                    this.selectedTenantId = tenantId;
                                    this.tenantDeposits = [];
                                    this.selectedUnitDeposit = null;
                                    if (!tenantId) return;
                                    fetch('{{ route('ajax.tenant-security-deposits') }}?tenant_id=' + tenantId + '&voucher_id={{ $voucher->id }}')
                                        .then(r => r.json())
                                        .then(d => { 
                                            this.tenantDeposits = d.security_deposits || []; 
                                            if (this.selectedUnitId) {
                                                this.selectedUnitDeposit = this.tenantDeposits.find(x => x.unit_id == this.selectedUnitId) || null;
                                            }
                                        });
                                },
                                fetchLandlordBalance(landlordId) {
                                    this.selectedLandlordId = landlordId;
                                    this.landlordBalanceInfo = null;
                                    if (!landlordId) return;
                                    fetch('{{ route('ajax.landlord-pending-balance') }}?landlord_id=' + landlordId)
                                        .then(r => r.json())
                                        .then(d => {
                                            this.landlordBalanceInfo = d;
                                            // If same landlord and no old/input change, add back original amount to payable_amount
                                            if (landlordId == '{{ $voucher->landlord_id }}') {
                                                this.landlordBalanceInfo.payable_amount = parseFloat(d.payable_amount) + parseFloat(this.originalAmount);
                                            }
                                        });
                                },
                                selectUnit(unitId) {
                                    this.selectedUnitId = unitId;
                                    this.selectedUnitDeposit = this.tenantDeposits.find(x => x.unit_id == unitId) || null;
                                    if (this.selectedUnitDeposit) {
                                        this.formatAmount(String(this.selectedUnitDeposit.pending_refund));
                                    }
                                },
                                formatAmount(val) {
                                    let clean = val.replace(/[^\d.]/g, '');
                                    let parts = clean.split('.');
                                    if (parts.length > 2) {
                                        parts = [parts[0], parts.slice(1).join('')];
                                    }
                                    parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
                                    this.displayAmount = parts.join('.');
                                    this.amount = clean;
                                },
                                init() {
                                    if (this.amount) {
                                        this.formatAmount(String(this.amount));
                                    }
                                    if (this.selectedTenantId) {
                                        this.fetchTenantDeposits(this.selectedTenantId);
                                    }
                                    if (this.selectedLandlordId) {
                                        this.fetchLandlordBalance(this.selectedLandlordId);
                                    }
                                }
                            }">
                @csrf
                @method('PUT')

                @php
                    $input = 'w-full rounded-xl border border-gray-300 bg-white px-4 py-3 text-base sm:text-lg font-semibold text-gray-900 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-600';
                    $label = 'mb-1.5 block text-xs sm:text-sm font-extrabold uppercase tracking-wider text-gray-700 dark:text-gray-300';
                @endphp

                {{-- STICKY BIG HEADING BANNER --}}
                <div class="sticky mb-6 rounded-2xl border-2 border-red-500 bg-white dark:bg-gray-900 p-5 shadow-xl backdrop-blur-md"
                    style="position: sticky; top: 72px; z-index: 990;">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-red-600 text-white shadow-md text-3xl font-black">
                                💸
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold uppercase tracking-wider text-red-600 dark:text-red-400">
                                    Edit Payment Voucher: {{ $voucher->voucher_no }}
                                </p>
                                <div class="flex flex-wrap items-baseline gap-2 mt-0.5">
                                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-white"
                                        x-text="paidToType === 'tenant' ? (selectedLabel ? 'Tenant: ' + selectedLabel : 'Select Tenant...') : (paidToType === 'landlord' ? 'Landlord Payout' : (paidToType === 'account' ? 'Account Transfer' : 'Party / Supplier'))"></h2>
                                </div>
                                <div class="flex items-center gap-2 mt-1 text-xs font-bold text-gray-600 dark:text-gray-300">
                                    <span>Paid From Account:</span>
                                    <span class="text-brand-600 dark:text-brand-400 font-extrabold" x-text="selectedAccountName || 'Not Selected'"></span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-gray-400 block">Voucher Amount</span>
                            <span class="text-2xl sm:text-3xl font-black font-mono text-red-600 dark:text-red-400"
                                  x-text="displayAmount ? 'Rs. ' + displayAmount : 'Rs. 0.00'"></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    {{-- Paid To Type --}}
                    <div>
                        <label class="{{ $label }}">Paid To Type <span class="text-red-500">*</span></label>
                        <select name="paid_to_type" x-model="paidToType"
                            class="{{ $input }} {{ $errors->has('paid_to_type') ? 'border-red-400' : '' }}" required>
                            <option value="tenant" {{ old('paid_to_type', $voucher->paid_to_type) === 'tenant' ? 'selected' : '' }}>Tenant (Refund Security Deposit)</option>
                            <option value="landlord" {{ old('paid_to_type', $voucher->paid_to_type) === 'landlord' ? 'selected' : '' }}>Landlord (Pay Negative Balance)</option>
                            <option value="other" {{ old('paid_to_type', $voucher->paid_to_type) === 'other' ? 'selected' : '' }}>Party (Suppliers/Contractors)</option>
                            <option value="account" {{ old('paid_to_type', $voucher->paid_to_type) === 'account' ? 'selected' : '' }}>Payment Account (Inter-Account Transfer)</option>
                        </select>
                        @error('paid_to_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Destination Payment Account Selection (when paidToType === 'account') --}}
                    <div x-show="paidToType === 'account'" x-transition x-cloak>
                        <label class="{{ $label }}">Destination Account (Transfer To) <span
                                class="text-red-500">*</span></label>
                        <select name="to_payment_account_id"
                            class="{{ $input }} {{ $errors->has('to_payment_account_id') ? 'border-red-400' : '' }}"
                            :required="paidToType === 'account'">
                            <option value="">Select Destination Account...</option>
                            @foreach($paymentAccounts as $account)
                                <option value="{{ $account->id }}" {{ old('to_payment_account_id', $voucher->to_payment_account_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} (Balance: Rs. {{ number_format($account->current_balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('to_payment_account_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tenant Selection --}}
                    <div x-show="paidToType === 'tenant'" x-transition x-cloak x-data="{
                                        open: false,
                                        search: '',
                                        selectedId: '{{ old('tenant_id', $voucher->tenant_id) }}',
                                        selectedLabel: '{{ old('tenant_name', $voucher->tenant ? $voucher->tenant->name . ($voucher->tenant->unit ? ' (' . $voucher->tenant->unit->unit_number . ')' : '') : '') }}',
                                        highlightedIndex: -1,
                                        tenants: [
                                            @foreach($tenants as $tenant)
                                                { id: {{ $tenant->id }}, name: '{{ addslashes($tenant->name) }}', phone: '{{ addslashes($tenant->phone ?? '—') }}', unit_number: '{{ $tenant->unit ? addslashes($tenant->unit->unit_number) : '—' }}' },
                                            @endforeach
                                        ],
                                        get filteredTenants() {
                                            if (!this.search) return this.tenants;
                                            let q = this.search.toLowerCase();
                                            return this.tenants.filter(t => t.name.toLowerCase().includes(q) || t.phone.toLowerCase().includes(q) || t.unit_number.toLowerCase().includes(q));
                                        },
                                        selectTenant(t) {
                                            this.selectedId = t.id;
                                            this.selectedLabel = t.name + (t.unit_number && t.unit_number !== '—' ? ' (' + t.unit_number + ')' : '');
                                            this.open = false;
                                            this.search = '';
                                            this.highlightedIndex = -1;
                                            fetchTenantDeposits(t.id);
                                        },
                                        moveHighlight(direction) {
                                            let list = this.filteredTenants;
                                            if (list.length === 0) return;
                                            this.highlightedIndex = (this.highlightedIndex + direction + list.length) % list.length;
                                        },
                                        selectHighlighted() {
                                            let list = this.filteredTenants;
                                            if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
                                                this.selectTenant(list[this.highlightedIndex]);
                                            }
                                        }
                                     }">
                        <label class="{{ $label }}">Select Tenant <span class="text-red-500">*</span></label>
                        <input type="hidden" name="tenant_id" :value="selectedId" :required="paidToType === 'tenant'">
                        <input type="hidden" name="tenant_name" :value="selectedLabel">

                        <div class="relative" :class="open ? 'relative z-[99999]' : 'relative'">
                            <div tabindex="0"
                                @click="open = !open; if(open) { $nextTick(() => $refs.tenantSearchInput.focus()) }"
                                @keydown.space.prevent="open = !open; if(open) { $nextTick(() => $refs.tenantSearchInput.focus()) }"
                                @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.tenantSearchInput.focus()) }"
                                @click.outside="open = false"
                                class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 cursor-pointer flex justify-between items-center {{ $errors->has('tenant_id') ? 'border-red-400 focus-within:ring-red-400' : 'border-gray-300 focus-within:border-brand-500 focus-within:ring-brand-500 dark:border-gray-700' }}">
                                <span x-text="selectedLabel || 'Select Tenant'"
                                    :class="selectedLabel ? '' : 'text-gray-400 dark:text-gray-600'"></span>
                                <svg class="h-4 w-4 text-gray-500 transition-transform duration-200"
                                    :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            <div x-show="open"
                                class="absolute left-0 z-[99999] mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 py-2">
                                <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700">
                                    <input x-ref="tenantSearchInput" x-model="search"
                                        @keydown.arrow-down.prevent="moveHighlight(1)"
                                        @keydown.arrow-up.prevent="moveHighlight(-1)"
                                        @keydown.enter.prevent="selectHighlighted()"
                                        @keydown.escape.prevent="open = false; highlightedIndex = -1" type="text"
                                        placeholder="Type to search tenant..."
                                        class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <ul class="max-h-60 overflow-y-auto mt-1">
                                    <template x-if="filteredTenants.length === 0">
                                        <li class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">No matching tenants
                                            found.</li>
                                    </template>
                                    <template x-for="(t, index) in filteredTenants" :key="t.id">
                                        <li @click="selectTenant(t)" @mouseenter="highlightedIndex = index" :class="{
                                                            'bg-brand-50 text-brand-900 dark:bg-brand-950/20 dark:text-brand-400': highlightedIndex === index,
                                                            'text-gray-800 dark:text-gray-200': highlightedIndex !== index
                                                        }"
                                            class="px-4 py-2 text-xs cursor-pointer hover:bg-brand-50 dark:hover:bg-brand-950/20 transition-colors flex justify-between items-center">
                                            <span
                                                x-text="t.name + (t.unit_number && t.unit_number !== '—' ? ' (' + t.unit_number + ')' : '')"
                                                class="font-medium"></span>
                                            <span x-text="t.phone" class="text-[10px] text-gray-400"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        @error('tenant_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tenant Unit Selection --}}
                    <div x-show="paidToType === 'tenant' && tenantDeposits.length > 0" x-transition x-cloak>
                        <label class="{{ $label }}">Select Flat/Shop (Security Deposit Refund) <span
                                class="text-red-500">*</span></label>
                        <select name="unit_id" class="{{ $input }} {{ $errors->has('unit_id') ? 'border-red-400' : '' }}"
                            :required="paidToType === 'tenant'" @change="selectUnit($event.target.value)">
                            <option value="">Select Flat/Shop</option>
                            <template x-for="d in tenantDeposits" :key="d.unit_id">
                                <option :value="d.unit_id" :selected="d.unit_id == selectedUnitId"
                                    x-text="d.unit_number + ' (Pending: Rs. ' + Number(d.pending_refund).toLocaleString(undefined, {minimumFractionDigits: 2}) + ')'">
                                </option>
                            </template>
                        </select>
                        @error('unit_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                        {{-- Deposit balance box --}}
                        <template x-if="selectedUnitDeposit !== null">
                            <div
                                class="mt-2 rounded-lg border border-teal-200 bg-teal-50 dark:border-teal-800 dark:bg-teal-900/10 p-2.5 text-xs font-semibold flex flex-col gap-1">
                                <div class="flex justify-between items-center text-teal-700 dark:text-teal-400">
                                    <span>Total Deposit Paid by Tenant:</span>
                                    <span class="font-bold text-sm text-teal-800 dark:text-teal-300"
                                        x-text="'Rs. ' + Number(selectedUnitDeposit.total_collected).toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                                </div>
                                <div class="flex justify-between items-center text-gray-500 dark:text-gray-400">
                                    <span>Previously Refunded:</span>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300"
                                        x-text="'Rs. ' + Number(selectedUnitDeposit.total_refunded).toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                                </div>
                                <div
                                    class="flex justify-between items-center text-brand-600 dark:text-brand-400 pt-1 border-t border-teal-100 dark:border-teal-900/30">
                                    <span>Maximum Refund Allowed:</span>
                                    <span class="font-bold text-sm text-brand-700 dark:text-brand-300"
                                        x-text="'Rs. ' + Number(selectedUnitDeposit.pending_refund).toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Warning if no deposit records found --}}
                    <div x-show="paidToType === 'tenant' && tenantDeposits.length === 0 && selectedTenantId" x-cloak
                        class="rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/10 p-4 text-xs text-amber-700 dark:text-amber-400 sm:col-span-2">
                        No active security deposit records found for this tenant. They must have a paid security deposit
                        payment record first.
                    </div>


                    {{-- Landlord Selection --}}
                    <div x-show="paidToType === 'landlord'" x-transition x-cloak x-data="{
                                        open: false,
                                        search: '',
                                        selectedId: '{{ old('landlord_id', $voucher->landlord_id) }}',
                                        selectedLabel: '{{ old('landlord_name', $voucher->landlord ? $voucher->landlord->name : '') }}',
                                        highlightedIndex: -1,
                                        landlords: [
                                            @foreach($landlords as $landlord)
                                                { id: {{ $landlord->id }}, name: '{{ addslashes($landlord->name) }}', phone: '{{ addslashes($landlord->phone ?? '—') }}' },
                                            @endforeach
                                        ],
                                        get filteredLandlords() {
                                            if (!this.search) return this.landlords;
                                            let q = this.search.toLowerCase();
                                            return this.landlords.filter(l => l.name.toLowerCase().includes(q) || l.phone.toLowerCase().includes(q));
                                        },
                                        selectLandlord(l) {
                                            this.selectedId = l.id;
                                            this.selectedLabel = l.name;
                                            this.open = false;
                                            this.search = '';
                                            this.highlightedIndex = -1;
                                            fetchLandlordBalance(l.id);
                                        },
                                        moveHighlight(direction) {
                                            let list = this.filteredLandlords;
                                            if (list.length === 0) return;
                                            this.highlightedIndex = (this.highlightedIndex + direction + list.length) % list.length;
                                        },
                                        selectHighlighted() {
                                            let list = this.filteredLandlords;
                                            if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
                                                this.selectLandlord(list[this.highlightedIndex]);
                                            }
                                        }
                                     }">
                        <label class="{{ $label }}">Select Landlord <span class="text-red-500">*</span></label>
                        <input type="hidden" name="landlord_id" :value="selectedId" :required="paidToType === 'landlord'">
                        <input type="hidden" name="landlord_name" :value="selectedLabel">

                        <div class="relative" :class="open ? 'relative z-[99999]' : 'relative'">
                            <div tabindex="0"
                                @click="open = !open; if(open) { $nextTick(() => $refs.landlordSearchInput.focus()) }"
                                @keydown.space.prevent="open = !open; if(open) { $nextTick(() => $refs.landlordSearchInput.focus()) }"
                                @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.landlordSearchInput.focus()) }"
                                @click.outside="open = false"
                                class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 cursor-pointer flex justify-between items-center {{ $errors->has('landlord_id') ? 'border-red-400 focus-within:ring-red-400' : 'border-gray-300 focus-within:border-brand-500 focus-within:ring-brand-500 dark:border-gray-700' }}">
                                <span x-text="selectedLabel || 'Select Landlord'"
                                    :class="selectedLabel ? '' : 'text-gray-400 dark:text-gray-600'"></span>
                                <svg class="h-4 w-4 text-gray-500 transition-transform duration-200"
                                    :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            <div x-show="open"
                                class="absolute left-0 z-[99999] mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 py-2">
                                <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700">
                                    <input x-ref="landlordSearchInput" x-model="search"
                                        @keydown.arrow-down.prevent="moveHighlight(1)"
                                        @keydown.arrow-up.prevent="moveHighlight(-1)"
                                        @keydown.enter.prevent="selectHighlighted()"
                                        @keydown.escape.prevent="open = false; highlightedIndex = -1" type="text"
                                        placeholder="Type to search landlord..."
                                        class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <ul class="max-h-60 overflow-y-auto mt-1">
                                    <template x-if="filteredLandlords.length === 0">
                                        <li class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">No matching landlords
                                            found.</li>
                                    </template>
                                    <template x-for="(l, index) in filteredLandlords" :key="l.id">
                                        <li @click="selectLandlord(l)" @mouseenter="highlightedIndex = index" :class="{
                                                            'bg-brand-50 text-brand-900 dark:bg-brand-950/20 dark:text-brand-400': highlightedIndex === index,
                                                            'text-gray-800 dark:text-gray-200': highlightedIndex !== index
                                                        }"
                                            class="px-4 py-2 text-xs cursor-pointer hover:bg-brand-50 dark:hover:bg-brand-950/20 transition-colors flex justify-between items-center">
                                            <span x-text="l.name" class="font-medium"></span>
                                            <span x-text="l.phone" class="text-[10px] text-gray-400"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        @error('landlord_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                        {{-- Landlord Balance Information --}}
                        <template x-if="landlordBalanceInfo !== null">
                            <div
                                class="mt-2 rounded-lg border border-teal-200 bg-teal-50 dark:border-teal-800 dark:bg-teal-900/10 p-2.5 text-xs font-semibold flex flex-col gap-1">
                                <div class="flex justify-between items-center text-teal-700 dark:text-teal-400">
                                    <span>Net Ledger Balance:</span>
                                    <span class="font-bold text-sm text-teal-800 dark:text-teal-300"
                                        x-text="'Rs. ' + Number(landlordBalanceInfo.current_balance).toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                                </div>
                                <div
                                    class="flex justify-between items-center text-brand-600 dark:text-brand-400 pt-1 border-t border-teal-100 dark:border-teal-900/30">
                                    <span>Payable Amount (Mall owes Landlord):</span>
                                    <span class="font-bold text-sm text-brand-700 dark:text-brand-300"
                                        x-text="'Rs. ' + Number(landlordBalanceInfo.payable_amount).toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Other Payee Name (Searchable Party Head Dropdown) --}}
                    <div x-show="paidToType === 'other'" x-transition x-cloak x-data="{
                                        open: false,
                                        search: '',
                                        selectedId: '{{ old('party_id', $voucher->party_id) }}',
                                        selectedLabel: '{{ old('party_name', $voucher->other_name) }}',
                                        highlightedIndex: -1,
                                        parties: [
                                            @foreach($parties as $party)
                                                { id: {{ $party->id }}, name: '{{ addslashes($party->name) }}', phone: '{{ addslashes($party->phone ?? '—') }}' },
                                            @endforeach
                                        ],
                                        get filteredParties() {
                                            if (!this.search) return this.parties;
                                            let q = this.search.toLowerCase();
                                            return this.parties.filter(p => p.name.toLowerCase().includes(q) || p.phone.toLowerCase().includes(q));
                                        },
                                        selectParty(p) {
                                            this.selectedId = p.id;
                                            this.selectedLabel = p.name;
                                            this.open = false;
                                            this.search = '';
                                            this.highlightedIndex = -1;
                                        },
                                        moveHighlight(direction) {
                                            let list = this.filteredParties;
                                            if (list.length === 0) return;
                                                this.highlightedIndex = (this.highlightedIndex + direction + list.length) % list.length;
                                        },
                                        selectHighlighted() {
                                            let list = this.filteredParties;
                                            if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
                                                this.selectParty(list[this.highlightedIndex]);
                                            }
                                        }
                                     }">
                        <label class="{{ $label }}">Party Head <span class="text-red-500">*</span></label>

                        {{-- Hidden form field --}}
                        <input type="hidden" name="party_id" :value="selectedId" :required="paidToType === 'other'">
                        <input type="hidden" name="party_name" :value="selectedLabel">

                        <div class="relative" :class="open ? 'relative z-[99999]' : 'relative'">
                            {{-- Trigger --}}
                            <div tabindex="0"
                                @click="open = !open; if(open) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                @keydown.space.prevent="open = !open; if(open) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                @click.outside="open = false"
                                class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 cursor-pointer flex justify-between items-center {{ $errors->has('party_id') ? 'border-red-400 focus-within:ring-red-400' : 'border-gray-300 focus-within:border-brand-500 focus-within:ring-brand-500 dark:border-gray-700' }}">
                                <span x-text="selectedLabel || 'Select Registered Party Head'"
                                    :class="selectedLabel ? '' : 'text-gray-400 dark:text-gray-600'"></span>
                                <svg class="h-4 w-4 text-gray-500 transition-transform duration-200"
                                    :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            {{-- Dropdown --}}
                            <div x-show="open" x-transition:enter="transition ease-out duration-100"
                                x-transition:enter-start="opacity-0 transform scale-95"
                                x-transition:enter-end="opacity-100 transform scale-100"
                                x-transition:leave="transition ease-in duration-75"
                                x-transition:leave-start="opacity-100 transform scale-100"
                                x-transition:leave-end="opacity-0 transform scale-95"
                                class="absolute left-0 z-[99999] mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 py-2"
                                style="display: none;">

                                <!-- Search Input -->
                                <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700">
                                    <input x-ref="partySearchInput" x-model="search"
                                        @keydown.arrow-down.prevent="moveHighlight(1)"
                                        @keydown.arrow-up.prevent="moveHighlight(-1)"
                                        @keydown.enter.prevent="selectHighlighted()"
                                        @keydown.escape.prevent="open = false; highlightedIndex = -1" type="text"
                                        placeholder="Type to search party head..."
                                        class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>

                                <!-- Options List -->
                                <ul class="max-h-60 overflow-y-auto mt-1">
                                    <template x-if="filteredParties.length === 0">
                                        <li class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">No matching party
                                            heads found.</li>
                                    </template>
                                    <template x-for="(p, index) in filteredParties" :key="p.id">
                                        <li @click="selectParty(p)" @mouseenter="highlightedIndex = index" :class="{
                                                            'bg-brand-50 text-brand-900 dark:bg-brand-950/20 dark:text-brand-400': highlightedIndex === index,
                                                            'text-gray-800 dark:text-gray-200': highlightedIndex !== index
                                                        }"
                                            class="px-4 py-2 text-xs cursor-pointer hover:bg-brand-50 dark:hover:bg-brand-950/20 transition-colors flex justify-between items-center">
                                            <span x-text="p.name" class="font-medium"></span>
                                            <span x-text="p.phone" class="text-[10px] text-gray-400"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                        @error('party_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="{{ $label }}">Voucher Date <span class="text-red-500">*</span></label>
                        <x-form.date-picker id="date" name="date" placeholder="Select Date"
                            defaultDate="{{ old('date', $voucher->date ? $voucher->date->format('Y-m-d') : '') }}" />
                        @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="{{ $label }}">Amount (Rs.) <span class="text-red-500">*</span></label>
                        <input type="text" x-model="displayAmount" @input="formatAmount($event.target.value)"
                            placeholder="0.00" class="{{ $input }} {{ $errors->has('amount') ? 'border-red-400' : '' }}"
                            required>
                        <input type="hidden" name="amount" x-model="amount">
                        @error('amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Paid From (Payment Account) --}}
                    <div>
                        <label class="{{ $label }}">Paid From (Payment Account) <span class="text-red-500">*</span></label>
                        <select name="payment_account_id"
                            class="{{ $input }} {{ $errors->has('payment_account_id') ? 'border-red-400' : '' }}" required
                            x-init="
                                            $nextTick(() => {
                                                let opt = $el.selectedOptions[0];
                                                if (opt) {
                                                    selectedBalance = opt.getAttribute('data-balance');
                                                    selectedAccountName = opt.getAttribute('data-name');
                                                }
                                            })
                                        " @change="
                                            let opt = $event.target.selectedOptions[0];
                                            selectedBalance = opt.getAttribute('data-balance');
                                            selectedAccountName = opt.getAttribute('data-name');
                                        ">
                            <option value="" data-balance="" data-name="">Select Account</option>
                            @foreach($paymentAccounts as $account)
                                <option value="{{ $account->id }}" data-balance="{{ $account->current_balance }}"
                                    data-name="{{ $account->name }}" {{ old('payment_account_id', $voucher->payment_account_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ $account->bank_name ?? 'Cash' }}) — Type:
                                    {{ ucfirst($account->type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_account_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                        <template x-if="selectedBalance !== null && selectedBalance !== ''">
                            <div
                                class="mt-2 text-xs font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-850/30 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700/60 flex justify-between items-center">
                                <span>Available Balance:</span>
                                <span
                                    :class="adjustedBalance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                                    class="font-bold text-sm"
                                    x-text="'Rs. ' + adjustedBalance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                            </div>
                        </template>

                    </div>

                    {{-- Reference/Cheque Number --}}
                    <div>
                        <label class="{{ $label }}">Reference / Cheque Number</label>
                        <input type="text" name="reference" value="{{ old('reference', $voucher->reference) }}"
                            placeholder="e.g. Online Ref #, Cheque #01848"
                            class="{{ $input }} {{ $errors->has('reference') ? 'border-red-400' : '' }}">
                        @error('reference') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Is Advance Payment Checkbox --}}
                    <div
                        class="sm:col-span-2 flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex h-5 items-center">
                            <input id="is_advance" name="is_advance" type="checkbox" value="1" {{ old('is_advance', $voucher->is_advance) ? 'checked' : '' }}
                                class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                        </div>
                        <div class="text-sm">
                            <label for="is_advance" class="font-semibold text-gray-800 dark:text-white/90">Is Advance
                                Payment?</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Check this option if this is an
                                advance payout to the owner or contractor rather than a final distribution/settlement.</p>
                        </div>
                    </div>

                    {{-- Notes/Description --}}
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Description / Notes</label>
                        <textarea name="notes" placeholder="Enter voucher details, breakdown, or reasons here..." rows="3"
                            class="{{ $input }} {{ $errors->has('notes') ? 'border-red-400' : '' }}">{{ old('notes', $voucher->notes) }}</textarea>
                        @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('payment-vouchers.index') }}"
                        class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        Save Changes
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection