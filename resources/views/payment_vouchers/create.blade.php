@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('payment-vouchers.index') }}" class="hover:text-brand-500">Payment Vouchers</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">New Payment Voucher</span>
        </div>

        <x-common.component-card title="Record Payment Voucher" desc="Record a payout from mall accounts to a managing owner/partner, a tenant (security deposit refund), or a miscellaneous recipient">
            <form action="{{ route('payment-vouchers.store') }}" method="POST" class="space-y-6"
                @submit.prevent="handleSubmit($event)"
                x-data="{ 
                    paidToType: '{{ old('paid_to_type', 'tenant') }}',
                    selectedBalance: null,
                    selectedAccountName: '',
                    amount: '{{ old('amount') }}',
                    displayAmount: '',
                    selectedTenantId: '{{ old('tenant_id') }}',
                    tenantDeposits: [],
                    selectedUnitId: '{{ old('unit_id') }}',
                    selectedUnitDeposit: null,
                    handleSubmit(event) {
                        if (this.selectedBalance !== null && this.selectedBalance !== '' && this.amount !== '') {
                            let amt = parseFloat(this.amount);
                            let bal = parseFloat(this.selectedBalance);
                            if (amt > bal) {
                                Swal.fire({
                                    title: 'Insufficient Balance',
                                    text: 'The selected Payment Account does not have sufficient balance. Current balance: Rs. ' + bal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}),
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

                        event.target.submit();
                    },
                    fetchTenantDeposits(tenantId) {
                        this.selectedTenantId = tenantId;
                        this.tenantDeposits = [];
                        this.selectedUnitDeposit = null;
                        if (!tenantId) return;
                        fetch('{{ route('ajax.tenant-security-deposits') }}?tenant_id=' + tenantId)
                            .then(r => r.json())
                            .then(d => { 
                                this.tenantDeposits = d.security_deposits || []; 
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
                    }
                }">
                @csrf

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
                    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
                @endphp

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    {{-- Paid To Type --}}
                    <div>
                        <label class="{{ $label }}">Paid To Type <span class="text-red-500">*</span></label>
                        <select name="paid_to_type" x-model="paidToType" class="{{ $input }} {{ $errors->has('paid_to_type') ? 'border-red-400' : '' }}" required>
                            <option value="tenant" {{ old('paid_to_type') === 'tenant' ? 'selected' : '' }}>Tenant (Refund Security Deposit)</option>
                            <option value="other" {{ old('paid_to_type') === 'other' ? 'selected' : '' }}>Other (Miscellaneous)</option>
                        </select>
                        @error('paid_to_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Tenant Selection --}}
                    <div x-show="paidToType === 'tenant'" x-transition x-cloak
                         x-data="{
                            open: false,
                            search: '',
                            selectedId: '{{ old('tenant_id') }}',
                            selectedLabel: '{{ old('tenant_name') }}',
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

                        <div class="relative">
                            <div tabindex="0"
                                 @click="open = !open; if(open) { $nextTick(() => $refs.tenantSearchInput.focus()) }"
                                 @keydown.space.prevent="open = !open; if(open) { $nextTick(() => $refs.tenantSearchInput.focus()) }"
                                 @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.tenantSearchInput.focus()) }"
                                 @click.outside="open = false"
                                 class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 cursor-pointer flex justify-between items-center {{ $errors->has('tenant_id') ? 'border-red-400 focus-within:ring-red-400' : 'border-gray-300 focus-within:border-brand-500 focus-within:ring-brand-500 dark:border-gray-700' }}">
                                <span x-text="selectedLabel || 'Select Tenant'" :class="selectedLabel ? '' : 'text-gray-400 dark:text-gray-600'"></span>
                                <svg class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            <div x-show="open"
                                 class="absolute left-0 z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 py-2">
                                <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700">
                                    <input x-ref="tenantSearchInput"
                                           x-model="search"
                                           @keydown.arrow-down.prevent="moveHighlight(1)"
                                           @keydown.arrow-up.prevent="moveHighlight(-1)"
                                           @keydown.enter.prevent="selectHighlighted()"
                                           @keydown.escape.prevent="open = false; highlightedIndex = -1"
                                           type="text"
                                           placeholder="Type to search tenant..."
                                           class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <ul class="max-h-60 overflow-y-auto mt-1">
                                    <template x-if="filteredTenants.length === 0">
                                        <li class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">No matching tenants found.</li>
                                    </template>
                                    <template x-for="(t, index) in filteredTenants" :key="t.id">
                                        <li @click="selectTenant(t)"
                                            @mouseenter="highlightedIndex = index"
                                            :class="{
                                                'bg-brand-50 text-brand-900 dark:bg-brand-950/20 dark:text-brand-400': highlightedIndex === index,
                                                'text-gray-800 dark:text-gray-200': highlightedIndex !== index
                                            }"
                                            class="px-4 py-2 text-xs cursor-pointer hover:bg-brand-50 dark:hover:bg-brand-950/20 transition-colors flex justify-between items-center">
                                            <span x-text="t.name + (t.unit_number && t.unit_number !== '—' ? ' (' + t.unit_number + ')' : '')" class="font-medium"></span>
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
                        <label class="{{ $label }}">Select Flat/Shop (Security Deposit Refund) <span class="text-red-500">*</span></label>
                        <select name="unit_id" class="{{ $input }} {{ $errors->has('unit_id') ? 'border-red-400' : '' }}" :required="paidToType === 'tenant'"
                                @change="selectUnit($event.target.value)">
                            <option value="">Select Flat/Shop</option>
                            <template x-for="d in tenantDeposits" :key="d.unit_id">
                                <option :value="d.unit_id" x-text="d.unit_number + ' (Pending: Rs. ' + Number(d.pending_refund).toLocaleString(undefined, {minimumFractionDigits: 2}) + ')'"></option>
                            </template>
                        </select>
                        @error('unit_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                        {{-- Deposit balance box --}}
                        <template x-if="selectedUnitDeposit !== null">
                            <div class="mt-2 rounded-lg border border-teal-200 bg-teal-50 dark:border-teal-800 dark:bg-teal-900/10 p-2.5 text-xs font-semibold flex flex-col gap-1">
                                <div class="flex justify-between items-center text-teal-700 dark:text-teal-400">
                                    <span>Total Deposit Paid by Tenant:</span>
                                    <span class="font-bold text-sm text-teal-800 dark:text-teal-300" x-text="'Rs. ' + Number(selectedUnitDeposit.total_collected).toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                                </div>
                                <div class="flex justify-between items-center text-gray-500 dark:text-gray-400">
                                    <span>Previously Refunded:</span>
                                    <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="'Rs. ' + Number(selectedUnitDeposit.total_refunded).toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                                </div>
                                <div class="flex justify-between items-center text-brand-600 dark:text-brand-400 pt-1 border-t border-teal-100 dark:border-teal-900/30">
                                    <span>Maximum Refund Allowed:</span>
                                    <span class="font-bold text-sm text-brand-700 dark:text-brand-300" x-text="'Rs. ' + Number(selectedUnitDeposit.pending_refund).toLocaleString(undefined, {minimumFractionDigits: 2})"></span>
                                </div>
                            </div>
                        </template>
                    </div>

                    {{-- Warning if no deposit records found --}}
                    <div x-show="paidToType === 'tenant' && tenantDeposits.length === 0 && selectedTenantId" x-cloak
                         class="rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-800 dark:bg-amber-900/10 p-4 text-xs text-amber-700 dark:text-amber-400 sm:col-span-2">
                        No active security deposit records found for this tenant. They must have a paid security deposit payment record first.
                    </div>


                    {{-- Other Payee Name (Searchable Party Head Dropdown) --}}
                    <div x-show="paidToType === 'other'" x-transition x-cloak
                         x-data="{
                            open: false,
                            search: '',
                            selectedId: '{{ old('party_id') }}',
                            selectedLabel: '{{ old('party_name') }}',
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

                        <div class="relative">
                            {{-- Trigger --}}
                            <div tabindex="0"
                                 @click="open = !open; if(open) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                 @keydown.space.prevent="open = !open; if(open) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                 @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                 @click.outside="open = false"
                                 class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 cursor-pointer flex justify-between items-center {{ $errors->has('party_id') ? 'border-red-400 focus-within:ring-red-400' : 'border-gray-300 focus-within:border-brand-500 focus-within:ring-brand-500 dark:border-gray-700' }}">
                                <span x-text="selectedLabel || 'Select Registered Party Head'" :class="selectedLabel ? '' : 'text-gray-400 dark:text-gray-600'"></span>
                                <svg class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            {{-- Dropdown --}}
                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute left-0 z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 py-2"
                                 style="display: none;">
                                
                                <!-- Search Input -->
                                <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700">
                                    <input x-ref="partySearchInput"
                                           x-model="search"
                                           @keydown.arrow-down.prevent="moveHighlight(1)"
                                           @keydown.arrow-up.prevent="moveHighlight(-1)"
                                           @keydown.enter.prevent="selectHighlighted()"
                                           @keydown.escape.prevent="open = false; highlightedIndex = -1"
                                           type="text"
                                           placeholder="Type to search party head..."
                                           class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>

                                <!-- Options List -->
                                <ul class="max-h-60 overflow-y-auto mt-1">
                                    <template x-if="filteredParties.length === 0">
                                        <li class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">No matching party heads found.</li>
                                    </template>
                                    <template x-for="(p, index) in filteredParties" :key="p.id">
                                        <li @click="selectParty(p)"
                                            @mouseenter="highlightedIndex = index"
                                            :class="{
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
                        <x-form.date-picker 
                            id="date" 
                            name="date"
                            placeholder="Select Date" 
                            defaultDate="{{ old('date', date('Y-m-d')) }}" 
                        />
                        @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="{{ $label }}">Amount (Rs.) <span class="text-red-500">*</span></label>
                        <input type="text" 
                               x-model="displayAmount"
                               @input="formatAmount($event.target.value)"
                               placeholder="0.00" 
                               class="{{ $input }} {{ $errors->has('amount') ? 'border-red-400' : '' }}" required>
                        <input type="hidden" name="amount" x-model="amount">
                        @error('amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Paid From (Payment Account) --}}
                    <div>
                        <label class="{{ $label }}">Paid From (Payment Account) <span class="text-red-500">*</span></label>
                        <select name="payment_account_id" class="{{ $input }} {{ $errors->has('payment_account_id') ? 'border-red-400' : '' }}" required
                            x-init="
                                $nextTick(() => {
                                    let opt = $el.selectedOptions[0];
                                    if (opt) {
                                        selectedBalance = opt.getAttribute('data-balance');
                                        selectedAccountName = opt.getAttribute('data-name');
                                    }
                                })
                            "
                            @change="
                                let opt = $event.target.selectedOptions[0];
                                selectedBalance = opt.getAttribute('data-balance');
                                selectedAccountName = opt.getAttribute('data-name');
                            ">
                            <option value="" data-balance="" data-name="">Select Account</option>
                            @foreach($paymentAccounts as $account)
                                <option value="{{ $account->id }}" data-balance="{{ $account->current_balance }}" data-name="{{ $account->name }}" {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ $account->bank_name ?? 'Cash' }}) — Type: {{ ucfirst($account->type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_account_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                        <template x-if="selectedBalance !== null && selectedBalance !== ''">
                            <div class="mt-2 text-xs font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-850/30 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700/60 flex justify-between items-center">
                                <span>Available Balance:</span>
                                <span :class="parseFloat(selectedBalance) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="font-bold text-sm" x-text="'Rs. ' + Number(selectedBalance).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                            </div>
                        </template>

                    </div>

                    {{-- Reference/Cheque Number --}}
                    <div>
                        <label class="{{ $label }}">Reference / Cheque Number</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g. Online Ref #, Cheque #01848" 
                               class="{{ $input }} {{ $errors->has('reference') ? 'border-red-400' : '' }}">
                        @error('reference') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Is Advance Payment Checkbox --}}
                    <div class="sm:col-span-2 flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex h-5 items-center">
                            <input id="is_advance" name="is_advance" type="checkbox" value="1" {{ old('is_advance') ? 'checked' : '' }}
                                   class="h-4 w-4 rounded-sm border-gray-300 text-brand-600 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800">
                        </div>
                        <div class="text-sm">
                            <label for="is_advance" class="font-semibold text-gray-800 dark:text-white/90">Is Advance Payment?</label>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Check this option if this is an advance payout to the owner or contractor rather than a final distribution/settlement.</p>
                        </div>
                    </div>

                    {{-- Notes/Description --}}
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Description / Notes</label>
                        <textarea name="notes" placeholder="Enter voucher details, breakdown, or reasons here..." rows="3"
                                  class="{{ $input }} {{ $errors->has('notes') ? 'border-red-400' : '' }}">{{ old('notes') }}</textarea>
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
                        Save Payment Voucher
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
