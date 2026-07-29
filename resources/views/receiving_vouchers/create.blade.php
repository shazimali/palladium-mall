@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="New Tenant Receiving Voucher" />

    <form action="{{ route('receiving-vouchers.store') }}" method="POST"
        @submit.prevent="handleSubmit($event)"
        x-data="{
            unitId: '{{ old('unit_id', '') }}',
            voucherAmount: '{{ old('amount', '') }}',
            displayAmount: '',
            selectedPaymentIds: [],
            allPayments: @js($allPayments),
            open: false,
            search: '',
            highlightedIndex: -1,

            options: [
                @foreach($units as $unit)
                {
                    id: '{{ $unit->id }}',
                    unit: '{{ addslashes($unit->unit_number) }}',
                    tenantName: '{{ $unit->tenant ? addslashes($unit->tenant->name) : ($unit->otherTenant ? addslashes($unit->otherTenant->name) : "Vacant") }}',
                    tenant: '{{ $unit->tenant ? "(Tenant: " . addslashes($unit->tenant->name) . ")" : ($unit->otherTenant ? "(Other Tenant: " . addslashes($unit->otherTenant->name) . ")" : "(Vacant)") }}',
                    searchLabel: '{{ strtolower($unit->unit_number . " " . ($unit->tenant?->name ?? ($unit->otherTenant?->name ?? "vacant"))) }}'
                },
                @endforeach
            ],

            get filteredOptions() {
                if (!this.search) return this.options;
                let s = this.search.toLowerCase();
                return this.options.filter(opt => opt.searchLabel.includes(s));
            },

            get selectedUnit() {
                let selected = this.options.find(opt => opt.id == this.unitId);
                return selected ? selected.unit : '';
            },

            get selectedTenantName() {
                if (!this.unitId) return 'Select a Flat/Shop...';
                let selected = this.options.find(opt => opt.id == this.unitId);
                return selected ? selected.tenantName : 'N/A';
            },

            get availablePayments() {
                if (!this.unitId) return [];
                return this.allPayments.filter(p => String(p.unit_id) === String(this.unitId));
            },

            get selectedPaymentsTotal() {
                return this.availablePayments
                    .filter(p => this.selectedPaymentIds.includes(String(p.id)))
                    .reduce((sum, p) => sum + p.balance, 0);
            },

            notes: @js(old('notes', '')),
            isNotesManuallyEdited: {{ old('notes') ? 'true' : 'false' }},

            updateAutoRemarks() {
                if (this.isNotesManuallyEdited) return;

                let selected = this.availablePayments.filter(p => this.selectedPaymentIds.includes(String(p.id)));
                if (selected.length === 0) {
                    this.notes = '';
                    return;
                }

                let rawUnit = (this.selectedUnit || '').trim();
                let hasFlatOrShop = /flat|shop/i.test(rawUnit);
                let unitStr = rawUnit ? (hasFlatOrShop ? rawUnit : 'Flat/Shop ' + rawUnit) : '';
                let itemsStr = selected.map(p => p.month + ' - ' + p.type).join(', ');
                this.notes = 'Received payment' + (unitStr ? ' for ' + unitStr : '') + ' (' + itemsStr + ')';
            },

            selectOption(opt) {
                this.unitId = opt.id;
                this.open = false;
                this.search = '';
                this.highlightedIndex = -1;
                this.selectedPaymentIds = [];
                this.displayAmount = '';
                this.voucherAmount = '';
                if (!this.isNotesManuallyEdited) {
                    this.notes = '';
                }
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
                this.selectedPaymentIds = [];
                this.displayAmount = '';
                this.voucherAmount = '';
                this.isNotesManuallyEdited = false;
                this.notes = '';
            },

            init() {
                this.$watch('unitId', () => {
                    this.selectedPaymentIds = [];
                    if (!this.isNotesManuallyEdited) {
                        this.notes = '';
                    }
                });

                this.$watch('selectedPaymentIds', () => {
                    this.updateAutoRemarks();
                });

                if (this.voucherAmount) {
                    this.formatAmount(String(this.voucherAmount));
                }

                if (!this.notes && this.selectedPaymentIds.length > 0) {
                    this.updateAutoRemarks();
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

            useSelectedTotal() {
                let total = this.selectedPaymentsTotal;
                if (total > 0) {
                    this.formatAmount(String(total));
                }
            },

            handleSubmit(event) {
                if (!this.unitId) {
                    Swal.fire({
                        title: 'Flat/Shop Required',
                        text: 'Please select a Flat / Shop.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                if (this.selectedPaymentIds.length === 0) {
                    Swal.fire({
                        title: 'Payment Selection Required',
                        text: 'Please select at least one payment checkbox to receive amount against.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

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

                if (amt > this.selectedPaymentsTotal + 0.01) {
                    Swal.fire({
                        title: 'Exceed Amount Error',
                        text: 'The voucher amount (Rs. ' + amt.toLocaleString('en-US', {minimumFractionDigits: 2}) + ') exceeds the total balance of selected payments (Rs. ' + this.selectedPaymentsTotal.toLocaleString('en-US', {minimumFractionDigits: 2}) + ').',
                        icon: 'error',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                Swal.fire({
                    title: 'Confirm Create Voucher',
                    text: 'Are you sure you want to save and generate this receiving voucher for Rs. ' + amt.toLocaleString('en-US', {minimumFractionDigits: 2}) + '?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Save & Print',
                    cancelButtonText: 'Cancel',
                    customClass: {
                        confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer mr-2',
                        cancelButton: 'inline-flex items-center justify-center rounded-xl bg-gray-200 dark:bg-gray-700 px-6 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-200 shadow-md hover:bg-gray-300 transition-colors cursor-pointer'
                    },
                    buttonsStyling: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        event.target.submit();
                    }
                });
            }
        }">

        @csrf
        <input type="hidden" name="received_from_type" value="tenant">
        <input type="hidden" name="unit_id" x-model="unitId" required>

        {{-- FORM CONTAINER WITH NO OUTER BG COLOR, CENTERED TITLE, ADAPTED TO THEME --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm text-gray-900 dark:text-white font-sans relative">

            {{-- FORM HEADER WITH CENTERED TITLE & RIGHT CORNER VOUCHER NUMBER --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
                <div class="hidden sm:block w-36"></div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase text-center">
                    Tenant Receiving Voucher
                </h2>
                <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-2 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Voucher No:</span>
                    <span class="text-base sm:text-lg font-black font-mono text-brand-600 dark:text-brand-400">{{ $nextVoucherNo }}</span>
                </div>
            </div>

            {{-- 2-COLUMN SIDE-BY-SIDE FORM GRID LAYOUT --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6 items-start">
                
                {{-- LEFT COLUMN: Flat / Shop, Date, Tenant Name, Payment Amount, Payment Method --}}
                <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-visible border border-gray-200 dark:border-gray-700">
                    
                    {{-- Field 1: Voucher Date --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide rounded-tl-2xl">Voucher Date</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center rounded-tr-2xl">
                            <input type="text" id="voucher_date" name="date" value="{{ old('date', now()->toDateString()) }}" required autocomplete="off"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all cursor-pointer">
                        </div>
                    </div>

                    {{-- Field 2: Searchable Flat / Shop Dropdown --}}
                    <div class="grid grid-cols-3 min-h-[52px] relative z-50" @click.away="open = false; highlightedIndex = -1">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Flat / Shop <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <button type="button" @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                                class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                <span x-text="selectedUnit ? 'Flat/Shop ' + selectedUnit : 'Select Flat / Shop...'" class="truncate"></span>
                                <span class="ml-2 text-sm opacity-60">▼</span>
                            </button>

                            {{-- Expanded Searchable Dropdown Modal Floating Above All Content --}}
                            <div x-show="open" x-transition x-cloak
                                class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                
                                <div class="p-3.5 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                    <input type="text" x-ref="searchInput" x-model="search" placeholder="Type flat number or tenant name..."
                                        @keydown.arrow-down.prevent="moveHighlight(1)"
                                        @keydown.arrow-up.prevent="moveHighlight(-1)"
                                        @keydown.enter.prevent="selectHighlighted()"
                                        @keydown.escape="open = false; highlightedIndex = -1"
                                        class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-base sm:text-lg text-gray-900 dark:text-white font-black focus:border-brand-500 focus:outline-none">
                                </div>

                                <div class="max-h-[360px] overflow-y-auto p-2 space-y-1 text-sm">
                                    <button type="button" @click="clearSelection()"
                                        class="w-full text-left px-4 py-2.5 font-black text-red-600 text-sm sm:text-base hover:bg-red-50 dark:hover:bg-red-950/20 rounded-xl transition-colors">
                                        ❌ Clear Selection
                                    </button>
                                    
                                    <template x-for="(opt, index) in filteredOptions" :key="opt.id">
                                        <button type="button" @click="selectOption(opt)"
                                            @mouseenter="highlightedIndex = index"
                                            class="w-full text-left px-4 py-3.5 rounded-xl transition-colors flex items-center justify-between"
                                            :class="unitId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                            <span class="flex items-center gap-2.5 truncate">
                                                <span x-text="'Flat/Shop ' + opt.unit" class="font-black text-base sm:text-lg"></span>
                                                <span x-text="opt.tenant" class="opacity-80 truncate text-xs sm:text-sm font-bold"></span>
                                            </span>
                                            <span x-show="unitId == opt.id" class="font-black text-lg">✓</span>
                                        </button>
                                    </template>

                                    <div x-show="filteredOptions.length === 0" class="px-4 py-4 text-center text-xs sm:text-sm font-semibold text-gray-400">
                                        No matching Flat/Shop found
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Field 3: Tenant Name --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Tenant Name</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base">
                            <span x-text="selectedTenantName"></span>
                        </div>
                    </div>

                    {{-- Field 4: Payment Amount --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Payment Amount <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex flex-col justify-center">
                            <input type="text" x-model="displayAmount" @input="formatAmount($event.target.value)" required placeholder="0.00"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            <input type="hidden" name="amount" x-model="voucherAmount">
                        </div>
                    </div>

                    {{-- Field 5: Payment Method / Account --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide rounded-bl-2xl">Payment Method <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center rounded-br-2xl">
                            <select name="payment_account_id" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                <option value="">Select Account...</option>
                                @foreach($paymentAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} ({{ ucfirst($account->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                {{-- RIGHT COLUMN: ONLY Payments List Checkboxes --}}
                <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden min-h-[260px] self-stretch">
                    <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center justify-between font-bold text-sm tracking-wide rounded-t-2xl">
                        <span>Payments List <span class="text-rose-300 ml-1">*</span></span>
                        <template x-if="selectedPaymentIds.length > 0">
                            <button type="button" @click="useSelectedTotal()" class="text-xs sm:text-sm bg-white text-brand-700 hover:bg-gray-100 font-black px-3.5 py-1.5 rounded-lg transition-all cursor-pointer shadow-2xs">
                                Set Amount (Rs. <span x-text="Math.round(selectedPaymentsTotal).toLocaleString()"></span>)
                            </button>
                        </template>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white p-4 flex-1 flex flex-col justify-start rounded-b-2xl">
                        
                        <div x-show="!unitId" class="text-base sm:text-lg font-black text-brand-600 dark:text-brand-400 my-auto text-center p-6 leading-relaxed">
                            👈 Select a Flat / Shop on the left to load pending payments...
                        </div>

                        <div x-show="unitId && availablePayments.length === 0" class="text-base sm:text-lg font-black text-emerald-600 dark:text-emerald-400 my-auto text-center p-6 leading-relaxed">
                            ✓ No outstanding dues for this Flat/Shop.
                        </div>

                        <div x-show="unitId && availablePayments.length > 0" class="space-y-2.5 max-h-[300px] overflow-y-auto pr-1">
                            <template x-for="p in availablePayments" :key="p.id">
                                <label class="flex items-center justify-between p-3.5 rounded-xl border transition-all cursor-pointer select-none bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-850 shadow-2xs"
                                    :class="selectedPaymentIds.includes(String(p.id)) ? 'border-brand-500 ring-2 ring-brand-500/20 bg-brand-50/30' : 'border-gray-200 dark:border-gray-700'">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <input type="checkbox" name="payment_ids[]" :value="String(p.id)" x-model="selectedPaymentIds" @change="useSelectedTotal()"
                                            class="h-5 w-5 rounded border-gray-300 text-brand-600 focus:ring-brand-500 cursor-pointer">
                                        <div class="truncate">
                                            <span class="font-black text-sm sm:text-base text-gray-900 dark:text-white" x-text="p.month + ' - ' + p.type"></span>
                                        </div>
                                    </div>
                                    <div class="text-right shrink-0 ml-2">
                                        <span class="text-sm sm:text-base font-black text-brand-600 dark:text-brand-400 font-mono" x-text="'Rs. ' + Math.round(p.balance).toLocaleString()"></span>
                                    </div>
                                </label>
                            </template>
                        </div>

                    </div>
                </div>

            </div>

            {{-- ROW 5 / BOTTOM SECTION: Approved by & Description --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">
                {{-- Left Box: Approved by Box --}}
                <div class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl p-4 flex flex-col justify-center border border-gray-200 dark:border-gray-700 shadow-xs">
                    <p class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                        Approved by: <span class="text-brand-600 dark:text-brand-400 font-extrabold ml-1">{{ auth()->user()->name }}</span>
                    </p>
                </div>

                {{-- Right Box: Remarks --}}
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 shadow-xs">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                        Remarks:
                    </label>
                    <textarea name="notes" x-model="notes" @input="isNotesManuallyEdited = true" rows="2" placeholder="Auto-generated remarks or enter notes..."
                        class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl p-3.5 text-base sm:text-lg font-black text-gray-900 dark:text-white placeholder-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all"></textarea>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('receiving-vouchers.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold transition-all text-xs sm:text-sm">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-black text-xs sm:text-sm shadow-md transition-all cursor-pointer">
                    Save Voucher
                </button>
            </div>

        </div>

    </form>
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
