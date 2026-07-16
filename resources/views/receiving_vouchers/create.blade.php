@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="New Receiving Voucher" />

    <x-common.component-card title="Record Receipt Voucher" desc="Log payments collected from tenants and allocate them to outstanding flat/shop bills.">
        
        <form action="{{ route('receiving-vouchers.store') }}" method="POST"
            x-data="{
                unitId: '{{ old('unit_id', '') }}',
                voucherAmount: '{{ old('amount', '') }}',
                displayAmount: '',
                pendingPayments: [],
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

                init() {
                    this.$watch('unitId', (val) => {
                        if (val) this.fetchPendingPayments(val);
                        else {
                            this.pendingPayments = [];
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
                    fetch(`/ajax/tenant-pending-payments?unit_id=${unitId}`)
                        .then(res => res.json())
                        .then(data => {
                            this.pendingPayments = data.payments;
                            this.totalBalance = this.pendingPayments.reduce((sum, p) => sum + p.balance, 0);
                            this.loading = false;
                        })
                        .catch(err => {
                            console.error(err);
                            this.loading = false;
                        });
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
                    <div class="rounded-xl border p-5 transition-all shadow-sm"
                        :class="totalBalance > 0 ? 'bg-orange-50/50 border-orange-200 text-orange-850 dark:bg-orange-950/10 dark:border-orange-900/30' : 'bg-green-50/50 border-green-200 text-green-850 dark:bg-green-950/10 dark:border-green-900/30'">
                        <div class="flex items-center justify-between pb-3 border-b border-orange-200/40 dark:border-orange-900/20">
                            <div>
                                <h4 class="text-sm font-semibold uppercase tracking-wide">
                                    Tenant Balance Status
                                </h4>
                                <p class="text-xs opacity-80 mt-1">Pending dues for this tenant in historical records.</p>
                            </div>
                            <div class="text-right flex flex-col items-end">
                                <span class="text-xs text-gray-500 font-medium">Total Outstanding:</span>
                                <span class="text-lg font-bold" x-text="totalBalance > 0 ? 'Rs. ' + Math.round(totalBalance).toLocaleString() : 'No Outstanding Balance'"></span>
                                
                                <template x-if="voucherAmount > 0">
                                    <div class="mt-1 flex flex-col items-end text-xs font-semibold text-green-600 dark:text-green-400">
                                        <span>Remaining Balance:</span>
                                        <span class="text-sm font-bold" x-text="'Rs. ' + Math.round(Math.max(0, totalBalance - voucherAmount)).toLocaleString()"></span>
                                    </div>
                                </template>
                            </div>
                        </div>

                        {{-- Details of Pending Payments --}}
                        <div class="mt-4" x-show="pendingPayments.length > 0">
                            <h5 class="text-xs font-bold uppercase tracking-wider mb-2 opacity-95">Pending Bills Detail</h5>
                            <div class="overflow-x-auto rounded-lg border border-orange-200/50 dark:border-orange-900/30 bg-white/70 dark:bg-gray-900/50">
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="border-b border-orange-200/50 dark:border-orange-900/30 bg-orange-100/30 dark:bg-orange-950/20 text-orange-900 dark:text-orange-350 uppercase font-semibold">
                                            <th class="px-4 py-2.5">Billing Month</th>
                                            <th class="px-4 py-2.5">Bill Type</th>
                                            <th class="px-4 py-2.5 text-right">Amount Due</th>
                                            <th class="px-4 py-2.5 text-right">Paid So Far</th>
                                            <th class="px-4 py-2.5 text-right">Remaining Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-orange-100/50 dark:divide-orange-900/20 text-gray-800 dark:text-gray-200">
                                        <template x-for="p in pendingPayments" :key="p.id">
                                            <tr class="hover:bg-orange-50/20 dark:hover:bg-orange-950/10">
                                                <td class="px-4 py-2.5 font-mono" x-text="p.month"></td>
                                                <td class="px-4 py-2.5 font-semibold" x-text="p.type"></td>
                                                <td class="px-4 py-2.5 text-right" x-text="'Rs. ' + Math.round(p.amount_due).toLocaleString()"></td>
                                                <td class="px-4 py-2.5 text-right" x-text="'Rs. ' + Math.round(p.amount_paid).toLocaleString()"></td>
                                                <td class="px-4 py-2.5 text-right font-bold text-orange-600 dark:text-orange-400" x-text="'Rs. ' + Math.round(p.balance).toLocaleString()"></td>
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

                            {{-- Date --}}
                            <div>
                                <label class="mb-1.5 block text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                                    Voucher Date <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <input type="text" id="voucher_date" name="date" value="{{ old('date', now()->toDateString()) }}" required autocomplete="off"
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
                                        <option value="{{ $account->id }}" {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
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
                                <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g. Bank slip or Cheque #"
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
                            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">{{ old('notes') }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Over-allocation warnings --}}
                    <div x-show="totalBalance > 0 && voucherAmount > totalBalance" x-cloak
                        class="p-4 rounded-xl border border-red-200 bg-red-50 text-red-800 dark:bg-red-950/10 dark:border-red-900/30 text-sm">
                        ❌ <strong>Voucher amount exceeds the outstanding balance</strong> of Rs. <span x-text="Math.round(totalBalance).toLocaleString()"></span>. Please reduce the amount.
                    </div>

                    <div x-show="totalBalance === 0" x-cloak
                        class="p-4 rounded-xl border border-amber-200 bg-amber-50 text-amber-800 dark:bg-amber-950/10 dark:border-amber-900/30 text-sm">
                        ⚠️ <strong>Note:</strong> This tenant has no pending dues.
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                        <button type="submit"
                            :disabled="totalBalance > 0 && (voucherAmount <= 0 || voucherAmount > totalBalance)"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Voucher
                        </button>
                        <a href="{{ route('receiving-vouchers.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
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
                    defaultDate: '{{ old('date', now()->toDateString()) }}'
                });
            }
        });
    </script>
@endpush
