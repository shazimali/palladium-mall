@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="New ORP Voucher" />

    <div class="mb-6 flex items-center justify-between no-print max-w-4xl mx-auto">
        <a href="{{ route('other-owned-rent-purchase-vouchers.index') }}"
            class="inline-flex items-center gap-2 rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-all">
            ← Back to List
        </a>
    </div>

    <form action="{{ route('other-owned-rent-purchase-vouchers.store') }}" method="POST"
        @submit.prevent="handleSubmit($event)"
        x-data="orpVoucherCreateForm">

        @csrf

        {{-- Form Container matching JV Vouchers --}}
        <div class="mx-auto max-w-4xl bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm text-gray-900 dark:text-white font-sans relative">

            {{-- Form Header matching JV Vouchers --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
                <div class="hidden sm:block w-36"></div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase text-center">
                    Rent Purchase Voucher (ORP)
                </h2>
                <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-2 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Voucher No:</span>
                    <span class="text-base sm:text-lg font-black font-mono text-brand-600 dark:text-brand-400">{{ $nextVoucherNo }}</span>
                </div>
            </div>

            {{-- Form Grid Container matching JV Vouchers --}}
            <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-visible mb-6 border border-gray-200 dark:border-gray-700">

                {{-- Row 1: Voucher Date & Billing Month --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-40 rounded-t-2xl">
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide rounded-tl-2xl">Voucher Date <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="text" id="voucher_date" name="date" value="{{ old('date', now()->toDateString()) }}" required autocomplete="off"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all cursor-pointer">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">Billing Month <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center md:rounded-tr-2xl">
                            <input type="text" id="billing_month" name="month" value="{{ old('month', now()->startOfMonth()->toDateString()) }}" required autocomplete="off"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all cursor-pointer">
                        </div>
                    </div>
                </div>

                {{-- Row 2: Landlord (Searchable Dropdown) & Self Unit --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-30">
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">Landlord <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <div class="w-full relative" @click.away="openLandlord = false; highlightedLandlordIndex = -1">
                                <input type="hidden" name="landlord_id" x-model="landlordId" required>
                                <button type="button" @click="openLandlord = !openLandlord; if(openLandlord) { $nextTick(() => $refs.landlordSearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span x-text="selectedLandlordName ? selectedLandlordName : 'Select Landlord...'" class="truncate"></span>
                                    <span class="ml-2 text-xs opacity-60">▼</span>
                                </button>

                                <div x-show="openLandlord" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-ref="landlordSearchInput" x-model="searchLandlord" placeholder="Type landlord name to search..."
                                            @keydown.arrow-down.prevent="highlightedLandlordIndex = (highlightedLandlordIndex + 1) % filteredLandlords.length"
                                            @keydown.arrow-up.prevent="highlightedLandlordIndex = (highlightedLandlordIndex - 1 + filteredLandlords.length) % filteredLandlords.length"
                                            @keydown.enter.prevent="if(highlightedLandlordIndex >= 0 && filteredLandlords[highlightedLandlordIndex]) selectLandlord(filteredLandlords[highlightedLandlordIndex])"
                                            @keydown.escape="openLandlord = false; highlightedLandlordIndex = -1"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-base font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filteredLandlords" :key="opt.id">
                                            <button type="button" @click="selectLandlord(opt)"
                                                @mouseenter="highlightedLandlordIndex = index"
                                                class="w-full text-left px-3.5 py-2.5 rounded-xl transition-colors flex items-center justify-between"
                                                :class="landlordId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedLandlordIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <span x-text="opt.name" class="font-black text-base sm:text-lg truncate"></span>
                                                <span x-show="landlordId == opt.id" class="font-black text-base">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filteredLandlords.length === 0" class="px-4 py-3 text-center text-xs font-semibold text-gray-400">
                                            No matching Landlord found
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">Self Unit</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <select name="unit_id" x-model="unitId" x-ref="unitSelect" @change="onUnitChange()"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                <option value="">Select Self Unit...</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Row 3: Other Tenant & Purchase Amount --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-20 rounded-b-2xl">
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide rounded-bl-2xl">Other Tenant</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="text" :value="otherTenantName" readonly placeholder="Auto-populated from Unit"
                                class="w-full bg-gray-100 dark:bg-gray-900/50 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base font-bold text-gray-700 dark:text-gray-300">
                            <input type="hidden" name="other_tenant_id" x-model="otherTenantId">
                        </div>
                    </div>

                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">Purchase Amount <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center rounded-br-2xl">
                            <input type="text" x-model="displayAmount" @input="formatAmount($event.target.value)" required placeholder="0.00"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-lg sm:text-xl font-black font-mono text-emerald-600 dark:text-emerald-400 focus:border-brand-500 focus:outline-none">
                            <input type="hidden" name="amount" x-model="voucherAmount">
                        </div>
                    </div>
                </div>

            </div>

            {{-- Bottom Section matching JV Vouchers --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">
                <div class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl p-4 flex flex-col justify-center border border-gray-200 dark:border-gray-700 shadow-xs">
                    <p class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                        Approved by: <span class="text-brand-600 dark:text-brand-400 font-extrabold ml-1">{{ auth()->user()->name }}</span>
                    </p>
                </div>

                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 shadow-xs">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                        Remarks:
                    </label>
                    <textarea name="notes" x-model="notes" @input="isNotesEdited = true" rows="2" placeholder="Auto-generated remarks or enter notes..."
                        class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl p-3.5 text-base sm:text-lg font-black text-gray-900 dark:text-white placeholder-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all"></textarea>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('other-owned-rent-purchase-vouchers.index') }}"
                    class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold transition-all text-xs sm:text-sm">
                    Cancel
                </a>
                <button type="submit"
                    class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-black text-xs sm:text-sm shadow-md transition-all cursor-pointer">
                    Save Voucher
                </button>
            </div>

        </div>

    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', function () {
            Alpine.data('orpVoucherCreateForm', () => ({
                landlordId: '{{ old('landlord_id', '') }}',
                unitId: '{{ old('unit_id', '') }}',
                otherTenantId: '{{ old('other_tenant_id', '') }}',
                otherTenantName: '',
                voucherAmount: '{{ old('amount', '') }}',
                displayAmount: '{{ old('amount') ? number_format((float)old('amount'), 2) : '' }}',
                month: '{{ old('month', now()->startOfMonth()->toDateString()) }}',
                date: '{{ old('date', now()->toDateString()) }}',
                notes: @js(old('notes', '')),
                isNotesEdited: false,

                openLandlord: false,
                searchLandlord: '',
                highlightedLandlordIndex: -1,

                landlordOptions: @js($landlords->map(fn($l) => ['id' => (string)$l->id, 'name' => $l->name])),

                get filteredLandlords() {
                    if (!this.searchLandlord) return this.landlordOptions;
                    let s = this.searchLandlord.toLowerCase();
                    return this.landlordOptions.filter(l => l.name.toLowerCase().includes(s));
                },

                get selectedLandlordName() {
                    let selected = this.landlordOptions.find(l => l.id == this.landlordId);
                    return selected ? selected.name : '';
                },

                selectLandlord(opt) {
                    this.landlordId = opt.id;
                    this.openLandlord = false;
                    this.searchLandlord = '';
                    this.highlightedLandlordIndex = -1;
                    this.onLandlordChange();
                },

                unitsList: [],

                onLandlordChange() {
                    this.unitId = '';
                    this.otherTenantId = '';
                    this.otherTenantName = '';
                    this.voucherAmount = '';
                    this.displayAmount = '';

                    let select = this.$refs.unitSelect;
                    if (select) select.innerHTML = '<option value="">Select Self Unit...</option>';

                    if (!this.landlordId) return;

                    fetch('{{ route('ajax.landlord-self-units') }}?landlord_id=' + this.landlordId)
                        .then(res => res.json())
                        .then(data => {
                            this.unitsList = data.units || [];
                            if (select) {
                                data.units.forEach(u => {
                                    let opt = document.createElement('option');
                                    opt.value = u.id;
                                    opt.textContent = u.unit_number + (u.other_tenant ? ' (Tenant: ' + u.other_tenant.name + ')' : ' (No Tenant)');
                                    select.appendChild(opt);
                                });
                            }
                        })
                        .catch(err => console.error(err));
                },

                onUnitChange() {
                    let u = this.unitsList.find(item => item.id == this.unitId);
                    if (u && u.other_tenant) {
                        this.otherTenantId = u.other_tenant.id;
                        this.otherTenantName = u.other_tenant.name;
                        let rent = u.other_tenant.monthly_rent || 0;
                        this.voucherAmount = rent;
                        this.displayAmount = rent > 0 ? rent.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}) : '';
                    } else {
                        this.otherTenantId = '';
                        this.otherTenantName = u ? 'No Other Tenant attached' : '';
                        this.voucherAmount = '';
                        this.displayAmount = '';
                    }
                    this.updateAutoNotes();
                },

                formatAmount(val) {
                    let clean = String(val).replace(/[^0-9.]/g, '');
                    let parts = clean.split('.');
                    if (parts.length > 2) clean = parts[0] + '.' + parts.slice(1).join('');
                    this.voucherAmount = clean;
                    if (!clean) {
                        this.displayAmount = '';
                        return;
                    }
                    let num = parseFloat(clean);
                    if (isNaN(num)) return;
                    if (clean.endsWith('.')) {
                        this.displayAmount = num.toLocaleString('en-US') + '.';
                    } else if (parts.length === 2 && parts[1].length > 0) {
                        this.displayAmount = parseFloat(parts[0]).toLocaleString('en-US') + '.' + parts[1].substring(0, 2);
                    } else {
                        this.displayAmount = num.toLocaleString('en-US');
                    }
                },

                updateAutoNotes() {
                    if (this.isNotesEdited) return;
                    let u = this.unitsList.find(item => item.id == this.unitId);
                    if (u) {
                        this.notes = 'Other Owned Rent Purchase for Unit ' + u.unit_number + (this.otherTenantName ? ' (' + this.otherTenantName + ')' : '');
                    }
                },

                handleSubmit(event) {
                    if (!this.landlordId) {
                        Swal.fire({
                            title: 'Landlord Required',
                            text: 'Please select a Landlord.',
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
                            text: 'Please enter a valid amount greater than zero.',
                            icon: 'warning',
                            confirmButtonText: 'OK',
                            customClass: {
                                confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                            },
                            buttonsStyling: false
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Confirm Create ORP Voucher',
                        text: 'Are you sure you want to save and generate this Other Owned Rent Purchase Voucher for Rs. ' + amt.toLocaleString('en-US', {minimumFractionDigits: 2}) + '?',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, Save Voucher',
                        cancelButtonText: 'Cancel',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer mr-2',
                            cancelButton: 'inline-flex items-center justify-center rounded-xl bg-gray-200 dark:bg-gray-700 px-6 py-2.5 text-sm font-bold text-gray-700 dark:text-gray-200 shadow-md hover:bg-gray-300 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    }).then((res) => {
                        if (res.isConfirmed) {
                            event.target.submit();
                        }
                    });
                }
            }));
        });

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

                let monthPlugins = [];
                if (typeof monthSelectPlugin !== 'undefined') {
                    monthPlugins.push(new monthSelectPlugin({
                        shorthand: false,
                        dateFormat: 'Y-m-01',
                        altFormat: 'F Y',
                        theme: 'light',
                    }));
                }

                flatpickr('#billing_month', {
                    dateFormat: 'Y-m-01',
                    altInput: true,
                    altFormat: 'F Y',
                    allowInput: false,
                    disableMobile: true,
                    plugins: monthPlugins,
                    defaultDate: '{{ old('month', now()->startOfMonth()->toDateString()) }}'
                });
            }

            @if ($errors->any() || session('error'))
                Swal.fire({
                    title: 'Form Error',
                    text: "{{ $errors->first() ?: session('error') }}",
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
