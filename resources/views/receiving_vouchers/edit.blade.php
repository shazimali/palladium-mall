@extends('layouts.app')

@section('content')
    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('receiving-vouchers.index') }}" class="hover:text-brand-500">Receiving Vouchers</a>
        <span>/</span>
        <span class="text-gray-800 dark:text-white/90">Edit Receiving Voucher</span>
    </div>

    <x-common.component-card :title="'Edit Receiving Voucher: ' . $voucher->voucher_no" desc="Modify payments collected from tenants. Restricted to Super Administrators only.">
        
        <form action="{{ route('receiving-vouchers.update', $voucher) }}" method="POST"
            @submit.prevent="handleSubmit($event)"
            x-data="{
                unitId: '{{ old('unit_id', $voucherUnitId) }}',
                voucherAmount: '{{ old('amount', $voucher->amount) }}',
                displayAmount: '',
                pendingPayments: [],
                selectedPaymentIds: @js(old('payment_ids', $voucher->payments->pluck('id')->map(fn($id) => (string)$id)->toArray())),
                totalBalance: 0,
                loading: false,
                search: '',
                open: false,
                highlightedIndex: -1,
                options: [
                    @foreach($units as $unit)
                    {
                        id: '{{ $unit->id }}',
                        unit: 'Flat/Shop: {{ addslashes($unit->unit_number) }}',
                        tenant: '{{ $unit->tenant ? "(Tenant: " . addslashes($unit->tenant->name) . ")" : ($unit->otherTenant ? "(Other Tenant: " . addslashes($unit->otherTenant->name) . ")" : "(Vacant)") }}',
                        text: 'Flat/Shop: {{ addslashes($unit->unit_number) }} {{ $unit->tenant ? "(Tenant: " . addslashes($unit->tenant->name) . ")" : ($unit->otherTenant ? "(Other Tenant: " . addslashes($unit->otherTenant->name) . ")" : "(Vacant)") }}',
                        searchLabel: '{{ strtolower($unit->unit_number . " " . ($unit->tenant?->name ?? ($unit->otherTenant?->name ?? "vacant"))) }}'
                    },
                    @endforeach
                ],

                handleSubmit(event) {
                    let amt = parseFloat(this.voucherAmount || 0);
                    if (isNaN(amt) || amt <= 0) {
                        Swal.fire({
                            title: 'Invalid Amount',
                            text: 'Please enter a valid voucher amount greater than zero.',
                            icon: 'warning',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                            },
                            buttonsStyling: false
                        });
                        return;
                    }

                    let maxAllowed = (this.selectedPaymentIds.length > 0 && this.selectedPaymentsTotal > 0) ? this.selectedPaymentsTotal : this.totalBalance;
                    if (amt > maxAllowed + 0.01) {
                        Swal.fire({
                            title: 'Exceed Amount Error',
                            text: 'The voucher amount (Rs. ' + amt.toLocaleString('en-US', {minimumFractionDigits: 2}) + ') exceeds the total outstanding balance of selected payments (Rs. ' + maxAllowed.toLocaleString('en-US', {minimumFractionDigits: 2}) + ').',
                            icon: 'error',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                            },
                            buttonsStyling: false
                        });
                        return;
                    }

                    event.target.submit();
                },

                init() {
                    this.$watch('unitId', (val) => {
                        if (val) this.fetchPendingPayments(val);
                        else {
                            this.pendingPayments = [];
                            this.selectedPaymentIds = [];
                            this.totalBalance = 0;
                        }
                    });

                    if (this.unitId) {
                        this.fetchPendingPayments(this.unitId);
                    }

                    if (this.voucherAmount) {
                        this.formatAmount(String(this.voucherAmount));
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
                    this.voucherAmount = clean ? parseFloat(clean) : '';
                },

                fetchPendingPayments(unitId) {
                    this.loading = true;
                    fetch(`/ajax/tenant-pending-payments?unit_id=${unitId}&exclude_voucher_id={{ $voucher->id }}`)
                        .then(res => res.json())
                        .then(data => {
                            this.pendingPayments = data.payments;
                            if (this.selectedPaymentIds.length === 0) {
                                this.selectedPaymentIds = this.pendingPayments.map(p => String(p.id));
                            }
                            this.totalBalance = this.pendingPayments.reduce((sum, p) => sum + p.balance, 0);
                            this.loading = false;
                        })
                        .catch(err => {
                            console.error(err);
                            this.loading = false;
                        });
                },

                get selectedPaymentsTotal() {
                    return this.pendingPayments
                        .filter(p => this.selectedPaymentIds.includes(String(p.id)))
                        .reduce((sum, p) => sum + p.balance, 0);
                },

                toggleAllPayments(event) {
                    if (event.target.checked) {
                        this.selectedPaymentIds = this.pendingPayments.map(p => String(p.id));
                    } else {
                        this.selectedPaymentIds = [];
                    }
                },

                useSelectedTotal() {
                    let total = this.selectedPaymentsTotal;
                    if (total > 0) {
                        this.formatAmount(String(total));
                    }
                },

                get filteredOptions() {
                    if (!this.search) return this.options;
                    let s = this.search.toLowerCase();
                    return this.options.filter(opt => opt.searchLabel.includes(s));
                },

                get selectedUnit() {
                    let selected = this.options.find(opt => opt.id == this.unitId);
                    return selected ? selected.unit : '';
                },

                get selectedTenant() {
                    let selected = this.options.find(opt => opt.id == this.unitId);
                    return selected ? selected.tenant : '';
                },

                get selectedText() {
                    let selected = this.options.find(opt => opt.id == this.unitId);
                    return selected ? selected.text : 'Choose a Flat / Shop';
                },

                selectOption(opt) {
                    this.unitId = opt.id;
                    this.open = false;
                    this.search = '';
                    this.highlightedIndex = -1;
                },

                moveHighlight(dir) {
                    let list = this.filteredOptions;
                    if (list.length === 0) return;
                    this.highlightedIndex = (this.highlightedIndex + dir + list.length) % list.length;
                },

                selectHighlighted() {
                    let list = this.filteredOptions;
                    if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
                        this.selectOption(list[this.highlightedIndex]);
                    }
                },

                clearSelection() {
                    this.unitId = '';
                    this.open = false;
                    this.search = '';
                    this.highlightedIndex = -1;
                }
            }">

            @csrf
            @method('PUT')

            <div class="space-y-6">
                
                {{-- Flat / Shop Selector Card (Always Visible) --}}
                <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                    <div class="mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                        <span class="text-lg">🏢</span>
                        <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                            Select Flat / Shop
                        </h4>
                    </div>

                    <div class="relative" @click.away="open = false; highlightedIndex = -1">
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Flat / Shop (Tenant) <span class="text-red-500">*</span>
                        </label>
                        
                        {{-- Trigger Button --}}
                        <button type="button" @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                            class="w-full flex items-center justify-between rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 text-left focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                            <template x-if="unitId">
                                <span class="flex items-center gap-1.5">
                                    <span x-text="selectedUnit" class="font-bold text-gray-900 dark:text-white"></span>
                                    <span x-text="selectedTenant" class="text-gray-500 dark:text-gray-400 font-normal"></span>
                                </span>
                            </template>
                            <template x-if="!unitId">
                                <span class="text-gray-400 dark:text-gray-500">Choose a Flat / Shop</span>
                            </template>
                            <svg class="h-4 w-4 text-gray-500 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>

                        {{-- Hidden Input for HTML5 native validation and submission --}}
                        <input type="text" name="unit_id" x-model="unitId" required
                            class="absolute inset-0 -z-10 w-0 h-0 opacity-0 pointer-events-none"
                            @focus="open = true">

                        {{-- Dropdown Container --}}
                        <div x-show="open" x-transition x-cloak
                            class="absolute left-0 right-0 z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-800 dark:bg-gray-955">
                            
                            {{-- Search field --}}
                            <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                                <div class="relative">
                                    <input type="text" x-ref="searchInput" x-model="search" placeholder="Type to search..."
                                        @keydown.arrow-down.prevent="moveHighlight(1)"
                                        @keydown.arrow-up.prevent="moveHighlight(-1)"
                                        @keydown.enter.prevent="selectHighlighted()"
                                        @keydown.escape="open = false; highlightedIndex = -1"
                                        class="w-full rounded-md border border-gray-200 bg-gray-50 px-3 py-1.5 pl-8 text-xs text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:bg-white focus:outline-none dark:border-gray-850 dark:bg-gray-900/50 dark:text-white/90">
                                    <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400">
                                        🔍
                                    </span>
                                    <button type="button" x-show="search" @click="search = ''; highlightedIndex = -1" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-white text-xs">
                                        Clear
                                    </button>
                                </div>
                            </div>

                            {{-- Options --}}
                            <div class="max-h-60 overflow-y-auto p-1">
                                <button type="button" @click="clearSelection()"
                                    class="w-full text-left px-3 py-2 text-xs text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-md">
                                    Clear Selection
                                </button>
                                
                                <template x-for="(opt, index) in filteredOptions" :key="opt.id">
                                    <button type="button" @click="selectOption(opt)"
                                        @mouseenter="highlightedIndex = index"
                                        class="w-full text-left px-3 py-2 text-xs rounded-md transition-colors flex items-center justify-between"
                                        :class="unitId == opt.id ? 'bg-brand-500 text-white font-semibold' : (highlightedIndex === index ? 'bg-brand-50 text-brand-900 dark:bg-brand-950/20 dark:text-brand-400' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5')">
                                    <template x-if="true">
                                        <span class="flex items-center gap-1.5 flex-1 min-w-0">
                                            <span x-text="opt.unit" class="font-bold"></span>
                                            <span x-text="opt.tenant" class="font-normal opacity-75 truncate"></span>
                                        </span>
                                    </template>
                                        <span x-show="unitId == opt.id" class="text-[10px]">✔️</span>
                                    </button>
                                </template>

                                <div x-show="filteredOptions.length === 0" class="px-3 py-4 text-center text-xs text-gray-400 dark:text-gray-500">
                                    No matching Flat / Shop found
                                </div>
                            </div>
                        </div>

                        @error('unit_id')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Loading Indicator --}}
                <div x-show="loading" class="py-8 text-center text-gray-400">
                    <svg class="animate-spin mx-auto h-8 w-8 opacity-60" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="mt-2 text-xs">Fetching tenant balance...</p>
                </div>

                {{-- Form fields shown only when Flat is selected and NOT loading --}}
                <div x-show="unitId && !loading" x-transition x-cloak class="space-y-6">
                    
                    {{-- Hidden Category Input --}}
                    <input type="hidden" name="received_from_type" value="tenant">

                    {{-- Dynamic Outstanding Balance Badge & Details --}}
                    <div class="rounded-2xl border-2 p-6 transition-all shadow-md"
                        :class="totalBalance > 0 ? 'bg-orange-50/70 border-orange-300 text-orange-950 dark:bg-orange-950/20 dark:border-orange-800' : 'bg-emerald-50/70 border-emerald-300 text-emerald-950 dark:bg-emerald-950/20 dark:border-emerald-800'">
                        <div class="flex flex-wrap items-center justify-between gap-4 pb-4 border-b border-orange-200 dark:border-orange-800/50">
                            <div>
                                <h3 class="text-base sm:text-lg font-black uppercase tracking-wide flex items-center gap-2">
                                    <span>📊</span> Tenant Balance Status
                                </h3>
                                <p class="text-xs sm:text-sm font-medium opacity-90 mt-1">Pending dues for this tenant in historical records (including this voucher's allocations).</p>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                <span class="text-xs sm:text-sm text-gray-700 dark:text-gray-300 font-extrabold uppercase tracking-wider">Total Outstanding (Pre-voucher):</span>
                                <span class="text-xl sm:text-2xl font-black font-mono" :class="totalBalance > 0 ? 'text-orange-600 dark:text-orange-400' : 'text-emerald-600 dark:text-emerald-400'" x-text="totalBalance > 0 ? 'Rs. ' + Math.round(totalBalance).toLocaleString() : 'No Outstanding Balance'"></span>
                                
                                <template x-if="voucherAmount > 0">
                                    <div class="mt-1.5 flex flex-col items-end text-xs sm:text-sm font-extrabold text-green-700 dark:text-green-400">
                                        <span>After Payment Balance:</span>
                                        <span class="text-base sm:text-lg font-black font-mono" x-text="'Rs. ' + Math.round(Math.max(0, totalBalance - voucherAmount)).toLocaleString()"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Details of Pending Payments --}}
                        <div class="mt-5" x-show="pendingPayments.length > 0">
                            <h4 class="text-xs sm:text-sm font-extrabold uppercase tracking-wider mb-3 opacity-95 flex items-center gap-1.5">
                                <span>📋</span> Pending Bills Detail
                            </h4>
                            <div class="overflow-x-auto rounded-xl border-2 border-orange-200 dark:border-orange-800/60 bg-white dark:bg-gray-900 shadow-2xs">
                                <table class="w-full text-left text-xs sm:text-sm">
                                    <thead>
                                        <tr class="border-b-2 border-orange-200 dark:border-orange-800/60 bg-orange-100/60 dark:bg-orange-950/40 text-orange-950 dark:text-orange-200 uppercase font-black tracking-wider">
                                            <th class="px-4 py-3">Billing Month</th>
                                            <th class="px-4 py-3">Bill Type</th>
                                            <th class="px-4 py-3 text-right">Amount Due</th>
                                            <th class="px-4 py-3 text-right">Paid So Far (Prior to Voucher)</th>
                                            <th class="px-4 py-3 text-right">Remaining Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-orange-100 dark:divide-orange-900/30 text-gray-900 dark:text-gray-100 font-semibold">
                                        <template x-for="p in pendingPayments" :key="p.id">
                                            <tr class="hover:bg-orange-50/50 dark:hover:bg-orange-950/20">
                                                <td class="px-4 py-3 font-mono font-bold" x-text="p.month"></td>
                                                <td class="px-4 py-3 font-extrabold text-brand-700 dark:text-brand-300" x-text="p.type"></td>
                                                <td class="px-4 py-3 text-right font-mono" x-text="'Rs. ' + Math.round(p.amount_due).toLocaleString()"></td>
                                                <td class="px-4 py-3 text-right font-mono text-gray-500" x-text="'Rs. ' + Math.round(p.amount_paid).toLocaleString()"></td>
                                                <td class="px-4 py-3 text-right font-mono font-black text-orange-600 dark:text-orange-400 text-sm" x-text="'Rs. ' + Math.round(p.balance).toLocaleString()"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Input details card --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="mb-4 flex items-center gap-2 border-b border-gray-100 pb-3 dark:border-gray-800">
                            <span class="text-lg">⚙️</span>
                            <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                                Voucher Details & Amount
                            </h4>
                        </div>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            {{-- Voucher Amount --}}
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Voucher Amount (Rs.) <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       x-model="displayAmount"
                                       @input="formatAmount($event.target.value)"
                                       required
                                       placeholder="0.00"
                                       class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                <input type="hidden" name="amount" x-model="voucherAmount">
                                @error('amount')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Payment Selection Checkboxes (Positioned directly after Voucher Amount field) --}}
                            <div class="col-span-full rounded-2xl border-2 border-brand-300 bg-brand-50/30 p-5 dark:border-brand-800 dark:bg-brand-950/20 shadow-sm"
                                x-show="pendingPayments.length > 0">
                                <div class="flex flex-wrap items-center justify-between gap-3 pb-3.5 mb-4 border-b border-brand-200 dark:border-brand-800/60">
                                    <div class="flex items-center gap-2.5">
                                        <span class="text-xl">☑️</span>
                                        <span class="text-base sm:text-lg font-extrabold uppercase tracking-wide text-brand-950 dark:text-brand-200">
                                            Select Payment(s) To Receive Amount Against:
                                        </span>
                                        <span class="rounded-full bg-brand-100 text-brand-800 dark:bg-brand-900/80 dark:text-brand-200 px-3 py-1 text-xs font-extrabold font-mono shadow-2xs"
                                            x-text="selectedPaymentIds.length + ' / ' + pendingPayments.length + ' Selected'"></span>
                                    </div>
                                    <div class="flex items-center gap-4 text-sm">
                                        <label class="inline-flex items-center gap-2 cursor-pointer text-gray-800 dark:text-gray-200 font-bold select-none hover:text-brand-600">
                                            <input type="checkbox"
                                                :checked="selectedPaymentIds.length === pendingPayments.length && pendingPayments.length > 0"
                                                @change="toggleAllPayments($event)"
                                                class="h-5 w-5 rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                                            <span>Select All</span>
                                        </label>
                                        <template x-if="selectedPaymentIds.length > 0 && selectedPaymentsTotal > 0">
                                            <button type="button" @click="useSelectedTotal()" class="text-sm text-brand-600 hover:text-brand-700 dark:text-brand-400 font-extrabold underline cursor-pointer">
                                                Set Amount to Selected (Rs. <span x-text="Math.round(selectedPaymentsTotal).toLocaleString()"></span>)
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                {{-- Individual Payment Checkboxes --}}
                                <div class="space-y-2.5 max-h-72 overflow-y-auto pr-1">
                                    <template x-for="p in pendingPayments" :key="p.id">
                                        <label class="flex items-center justify-between p-3.5 rounded-xl border-2 transition-all cursor-pointer select-none"
                                            :class="selectedPaymentIds.includes(String(p.id)) 
                                                ? 'border-brand-500 bg-white dark:bg-gray-850 dark:border-brand-500 ring-2 ring-brand-500/20 shadow-xs' 
                                                : 'border-gray-200 bg-white/70 dark:border-gray-800/80 dark:bg-gray-900/40 opacity-75 hover:opacity-100'">
                                            <div class="flex items-center gap-3.5">
                                                <input type="checkbox" name="payment_ids[]" :value="p.id" x-model="selectedPaymentIds"
                                                    class="h-5 w-5 rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer shrink-0">
                                                <div>
                                                    <div class="flex items-center gap-2.5">
                                                        <span class="font-extrabold text-sm sm:text-base text-gray-900 dark:text-white font-mono" x-text="p.month"></span>
                                                        <span class="px-2.5 py-0.5 rounded-md text-xs font-extrabold uppercase tracking-wider border"
                                                            :class="p.type.toLowerCase().includes('rent') 
                                                                ? 'bg-blue-50 text-blue-700 border-blue-200/80 dark:bg-blue-950/60 dark:text-blue-300 dark:border-blue-800/60' 
                                                                : 'bg-emerald-50 text-emerald-700 border-emerald-200/80 dark:bg-emerald-950/60 dark:text-emerald-300 dark:border-emerald-800/60'"
                                                            x-text="p.type"></span>
                                                    </div>
                                                    <div class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                                                        Bill Amount: <span class="font-bold text-gray-900 dark:text-gray-100">Rs. <span x-text="Math.round(p.amount_due).toLocaleString()"></span></span>
                                                        <template x-if="p.amount_paid > 0">
                                                            <span class="ml-1.5 text-gray-500">(Paid So Far: Rs. <span x-text="Math.round(p.amount_paid).toLocaleString()"></span>)</span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <span class="text-sm sm:text-base font-black text-brand-600 dark:text-brand-400 font-mono" x-text="'Rs. ' + Math.round(p.balance).toLocaleString()"></span>
                                                <div class="text-xs text-gray-500 uppercase font-bold tracking-wide">Pending Balance</div>
                                            </div>
                                        </label>
                                    </template>
                                </div>
                            </div>

                            {{-- Date --}}
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Voucher Date <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="voucher_date" name="date" value="{{ old('date', $voucher->date ? $voucher->date->format('Y-m-d') : '') }}" required autocomplete="off"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                @error('date')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Payment Account --}}
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Deposit Account <span class="text-red-500">*</span>
                                </label>
                                <select name="payment_account_id" required
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                    <option value="">Select Account</option>
                                    @foreach($paymentAccounts as $account)
                                        <option value="{{ $account->id }}" {{ old('payment_account_id', $voucher->payment_account_id) == $account->id ? 'selected' : '' }}>
                                            {{ $account->name }} ({{ ucfirst($account->type) }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_account_id')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Reference --}}
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Cheque / Transaction Ref
                                </label>
                                <input type="text" name="reference" value="{{ old('reference', $voucher->reference) }}" placeholder="e.g. Bank slip or Cheque #"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                @error('reference')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- General Notes Card --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Voucher Notes / Remarks</label>
                        <textarea name="notes" rows="2" placeholder="Write any specific details or remarks..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">{{ old('notes', $voucher->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Over-allocation warnings --}}
                    <div x-show="totalBalance > 0 && voucherAmount > totalBalance" x-cloak
                        class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-800 dark:bg-red-955/10 dark:border-red-900/30 text-sm">
                        ❌ <strong>Voucher amount exceeds the outstanding balance</strong> of Rs. <span x-text="Math.round(totalBalance).toLocaleString()"></span>. Please reduce the amount.
                    </div>

                    <div x-show="totalBalance === 0" x-cloak
                        class="p-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-800 dark:bg-amber-955/10 dark:border-amber-900/30 text-sm">
                        ⚠️ <strong>Note:</strong> This tenant has no pending dues.
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Changes
                        </button>
                        <a href="{{ route('receiving-vouchers.index') }}"
                            class="inline-flex items-center rounded-xl border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                            Cancel
                        </a>
                    </div>

                </div>

            </div>
        </form>
    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr !== 'undefined') {
                flatpickr('#voucher_date', {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd M Y',
                    allowInput: true,
                    disableMobile: true,
                    defaultDate: '{{ old('date', $voucher->date ? $voucher->date->format('Y-m-d') : now()->toDateString()) }}'
                });
            }

            @if ($errors->has('amount') || session('error'))
                Swal.fire({
                    title: 'Exceed Amount Error',
                    text: "{{ $errors->first('amount') ?: session('error') }}",
                    icon: 'error',
                    confirmButtonText: 'OK',
                    customClass: {
                        confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                    },
                    buttonsStyling: false
                });
            @endif
        });
    </script>
@endpush
