@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Edit Paid Voucher — {{ $voucher->voucher_no }}" />

    <form action="{{ route('payment-vouchers.update', $voucher) }}" method="POST"
        @submit.prevent="handleSubmit($event)"
        x-data="{
            paidToType: '{{ old('paid_to_type', $voucher->paid_to_type) }}',
            tenantId: '{{ old('tenant_id', $voucher->tenant_id) }}',
            unitId: '{{ old('unit_id', $voucher->unit_id) }}',
            partyId: '{{ old('party_id', $voucher->party_id) }}',
            landlordId: '{{ old('landlord_id', $voucher->landlord_id) }}',
            ownerId: '{{ old('owner_id', $voucher->owner_id) }}',
            toAccountId: '{{ old('to_payment_account_id', $voucher->to_payment_account_id) }}',
            paymentAccountId: '{{ old('payment_account_id', $voucher->payment_account_id) }}',
            voucherAmount: '{{ old('amount', $voucher->amount) }}',
            displayAmount: '',

            openTenant: false,
            searchTenant: '',
            highlightedTenantIndex: -1,

            openParty: false,
            searchParty: '',
            highlightedPartyIndex: -1,

            openLandlord: false,
            searchLandlord: '',
            highlightedLandlordIndex: -1,

            openOwner: false,
            searchOwner: '',
            highlightedOwnerIndex: -1,

            openToAccount: false,
            searchToAccount: '',
            highlightedToAccountIndex: -1,

            isNotesManuallyEdited: {{ (old('notes', $voucher->notes ?? '') && !str_starts_with(old('notes', $voucher->notes ?? ''), 'Payment paid')) ? 'true' : 'false' }},
            notes: '{{ old('notes', addslashes($voucher->notes ?? '')) }}',

            tenantOptions: [
                @foreach($tenants as $tenant)
                @php
                    $effUnit = $tenant->effective_unit;
                    $uNum = $effUnit ? $effUnit->unit_number : ($tenant->unit ? $tenant->unit->unit_number : '');
                    $effUnitId = $effUnit?->id ?? $tenant->unit_id ?? '';
                    $hasKeyword = preg_match('/(flat|shop)/i', $uNum);
                    $uLabel = $uNum ? ($hasKeyword ? "($uNum)" : "(Flat/Shop $uNum)") : '';
                @endphp
                {
                    id: '{{ $tenant->id }}',
                    unitId: '{{ $effUnitId }}',
                    name: '{{ addslashes($tenant->name) }}',
                    unit: '{{ addslashes($uNum) }}',
                    label: '{{ addslashes($tenant->name) }} {{ addslashes($uLabel) }}'
                },
                @endforeach
            ],

            partyOptions: [
                @foreach($parties as $party)
                { id: '{{ $party->id }}', name: '{{ addslashes($party->name) }}' },
                @endforeach
            ],

            landlordOptions: [
                @foreach($landlords as $landlord)
                { id: '{{ $landlord->id }}', name: '{{ addslashes($landlord->name) }}' },
                @endforeach
            ],

            ownerOptions: [
                @foreach($owners as $owner)
                { id: '{{ $owner->id }}', name: '{{ addslashes($owner->name) }}' },
                @endforeach
            ],

            accountOptions: [
                @foreach($paymentAccounts as $account)
                { id: '{{ $account->id }}', name: '{{ addslashes($account->name) }} ({{ ucfirst($account->type) }})' },
                @endforeach
            ],

            updateAutoRemarks() {
                if (this.isNotesManuallyEdited) return;

                let recipientName = '';
                if (this.paidToType === 'tenant') recipientName = this.selectedTenantLabel;
                else if (this.paidToType === 'other') recipientName = this.selectedPartyName;
                else if (this.paidToType === 'landlord') recipientName = this.selectedLandlordName;
                else if (this.paidToType === 'owner') recipientName = this.selectedOwnerName;
                else if (this.paidToType === 'account') recipientName = this.selectedToAccountName;

                let amtNum = parseFloat(this.voucherAmount || 0);
                let amtStr = amtNum > 0 ? 'Rs. ' + Math.round(amtNum).toLocaleString() : '';

                if (!amtStr && !recipientName) {
                    this.notes = '';
                    return;
                }

                let remarks = 'Payment paid';
                if (amtStr) remarks += ' of ' + amtStr;
                if (recipientName) remarks += ' to ' + recipientName;
                this.notes = remarks;
            },

            get filteredTenants() {
                if (!this.searchTenant) return this.tenantOptions;
                let s = this.searchTenant.toLowerCase();
                return this.tenantOptions.filter(t => t.label.toLowerCase().includes(s));
            },
            get selectedTenantLabel() {
                let selected = this.tenantOptions.find(t => t.id == this.tenantId);
                return selected ? selected.label : '';
            },

            get filteredParties() {
                if (!this.searchParty) return this.partyOptions;
                let s = this.searchParty.toLowerCase();
                return this.partyOptions.filter(p => p.name.toLowerCase().includes(s));
            },
            get selectedPartyName() {
                let selected = this.partyOptions.find(p => p.id == this.partyId);
                return selected ? selected.name : '';
            },

            get filteredLandlords() {
                if (!this.searchLandlord) return this.landlordOptions;
                let s = this.searchLandlord.toLowerCase();
                return this.landlordOptions.filter(l => l.name.toLowerCase().includes(s));
            },
            get selectedLandlordName() {
                let selected = this.landlordOptions.find(l => l.id == this.landlordId);
                return selected ? selected.name : '';
            },

            get filteredOwners() {
                if (!this.searchOwner) return this.ownerOptions;
                let s = this.searchOwner.toLowerCase();
                return this.ownerOptions.filter(o => o.name.toLowerCase().includes(s));
            },
            get selectedOwnerName() {
                let selected = this.ownerOptions.find(o => o.id == this.ownerId);
                return selected ? selected.name : '';
            },

            get filteredToAccounts() {
                if (!this.searchToAccount) return this.accountOptions;
                let s = this.searchToAccount.toLowerCase();
                return this.accountOptions.filter(a => a.name.toLowerCase().includes(s));
            },
            get selectedToAccountName() {
                let selected = this.accountOptions.find(a => a.id == this.toAccountId);
                return selected ? selected.name : '';
            },

            selectTenant(opt) {
                this.tenantId = opt.id;
                this.unitId = opt.unitId;
                this.openTenant = false;
                this.searchTenant = '';
                this.highlightedTenantIndex = -1;
                this.updateAutoRemarks();
            },
            selectParty(opt) {
                this.partyId = opt.id;
                this.openParty = false;
                this.searchParty = '';
                this.highlightedPartyIndex = -1;
                this.updateAutoRemarks();
            },
            selectLandlord(opt) {
                this.landlordId = opt.id;
                this.openLandlord = false;
                this.searchLandlord = '';
                this.highlightedLandlordIndex = -1;
                this.updateAutoRemarks();
            },
            selectOwner(opt) {
                this.ownerId = opt.id;
                this.openOwner = false;
                this.searchOwner = '';
                this.highlightedOwnerIndex = -1;
                this.updateAutoRemarks();
            },
            selectToAccount(opt) {
                this.toAccountId = opt.id;
                this.openToAccount = false;
                this.searchToAccount = '';
                this.highlightedToAccountIndex = -1;
                this.updateAutoRemarks();
            },

            init() {
                if (this.voucherAmount) {
                    this.formatAmount(String(this.voucherAmount));
                }
                this.$watch('voucherAmount', () => this.updateAutoRemarks());
                this.$watch('paidToType', () => this.updateAutoRemarks());
                if (!this.notes) {
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

            handleSubmit(event) {
                if (this.paidToType === 'tenant' && (!this.tenantId || !this.unitId)) {
                    Swal.fire({
                        title: 'Tenant Required',
                        text: 'Please select a Tenant & Unit.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                if (this.paidToType === 'other' && !this.partyId) {
                    Swal.fire({
                        title: 'Party Required',
                        text: 'Please select a Registered Party Head.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                if (this.paidToType === 'landlord' && !this.landlordId) {
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

                if (this.paidToType === 'owner' && !this.ownerId) {
                    Swal.fire({
                        title: 'Managing Owner Required',
                        text: 'Please select a Managing Owner.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                if (this.paidToType === 'account' && !this.toAccountId) {
                    Swal.fire({
                        title: 'Destination Account Required',
                        text: 'Please select a Destination Account.',
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

                if (!this.paymentAccountId) {
                    Swal.fire({
                        title: 'Withdrawal Account Required',
                        text: 'Please select a Paid From Account.',
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
                    title: 'Confirm Update Payment Voucher',
                    text: 'Are you sure you want to update and print this payment voucher for Rs. ' + amt.toLocaleString('en-US', {minimumFractionDigits: 2}) + '?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Update & Print',
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
        @method('PUT')

        {{-- FORM CONTAINER WITH NO OUTER BG COLOR, CENTERED TITLE, ADAPTED TO THEME --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm text-gray-900 dark:text-white font-sans relative">

            {{-- FORM HEADER WITH CENTERED TITLE & RIGHT CORNER VOUCHER NUMBER --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
                <div class="hidden sm:block w-36"></div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase text-center">
                    Paid Voucher
                </h2>
                <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-2 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Voucher No:</span>
                    <span class="text-base sm:text-lg font-black font-mono text-brand-600 dark:text-brand-400">{{ $voucher->voucher_no }}</span>
                </div>
            </div>

            {{-- FORM GRID CONTAINER --}}
            <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-visible mb-6 border border-gray-200 dark:border-gray-700">
                
                {{-- ROW 1: Voucher Date & Paid To Type --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-40 rounded-t-2xl">
                    {{-- Field 1: Voucher Date --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide rounded-tl-2xl">Voucher Date</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="text" id="voucher_date" name="date" value="{{ old('date', $voucher->date->format('Y-m-d')) }}" required autocomplete="off"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all cursor-pointer">
                        </div>
                    </div>

                    {{-- Field 2: Paid To Type --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">Paid To <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center md:rounded-tr-2xl">
                            <select name="paid_to_type" x-model="paidToType" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all">
                                <option value="tenant">Tenant / Unit</option>
                                <option value="other">Registered Party Head</option>
                                <option value="landlord">Landlord</option>
                                <option value="owner">Managing Owner Withdrawal</option>
                                <option value="account">Account Transfer</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ROW 2: Searchable Dynamic Entity Selection (Full Row Width) --}}
                <div class="grid grid-cols-1 md:grid-cols-6 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-50">
                    <div class="md:col-span-6 grid grid-cols-1 md:grid-cols-6 min-h-[52px] relative z-50">
                        <div class="md:col-span-2 bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">
                            <span x-text="paidToType === 'tenant' ? 'Select Tenant' : (paidToType === 'other' ? 'Select Party' : (paidToType === 'landlord' ? 'Select Landlord' : (paidToType === 'owner' ? 'Select Managing Owner' : 'To Account')))"></span>
                            <span class="text-rose-300 ml-1">*</span>
                        </div>
                        <div class="md:col-span-4 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">

                            {{-- Case 5: Searchable Managing Owner Dropdown --}}
                            <div x-show="paidToType === 'owner'" class="w-full relative" @click.away="openOwner = false; highlightedOwnerIndex = -1">
                                <input type="hidden" name="owner_id" x-model="ownerId" :required="paidToType === 'owner'">
                                <button type="button" @click="openOwner = !openOwner; if(openOwner) { $nextTick(() => $refs.ownerSearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span x-text="selectedOwnerName ? selectedOwnerName : 'Select Managing Owner...'" class="truncate"></span>
                                    <span class="ml-2 text-xs opacity-60">▼</span>
                                </button>

                                <div x-show="openOwner" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-ref="ownerSearchInput" x-model="searchOwner" placeholder="Type managing owner name to search..."
                                            @keydown.arrow-down.prevent="highlightedOwnerIndex = (highlightedOwnerIndex + 1) % filteredOwners.length"
                                            @keydown.arrow-up.prevent="highlightedOwnerIndex = (highlightedOwnerIndex - 1 + filteredOwners.length) % filteredOwners.length"
                                            @keydown.enter.prevent="if(highlightedOwnerIndex >= 0 && filteredOwners[highlightedOwnerIndex]) selectOwner(filteredOwners[highlightedOwnerIndex])"
                                            @keydown.escape="openOwner = false; highlightedOwnerIndex = -1"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-base font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filteredOwners" :key="opt.id">
                                            <button type="button" @click="selectOwner(opt)"
                                                @mouseenter="highlightedOwnerIndex = index"
                                                class="w-full text-left px-3.5 py-2.5 rounded-xl transition-colors flex items-center justify-between"
                                                :class="ownerId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedOwnerIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <span x-text="opt.name" class="font-black text-base sm:text-lg truncate"></span>
                                                <span x-show="ownerId == opt.id" class="font-black text-base">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filteredOwners.length === 0" class="px-4 py-3 text-center text-xs font-semibold text-gray-400">
                                            No matching Managing Owner found
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            {{-- Case 1: Searchable Tenant Dropdown --}}
                            <div x-show="paidToType === 'tenant'" class="w-full relative" @click.away="openTenant = false; highlightedTenantIndex = -1">
                                <input type="hidden" name="tenant_id" x-model="tenantId" :required="paidToType === 'tenant'">
                                <input type="hidden" name="unit_id" x-model="unitId" :required="paidToType === 'tenant'">
                                <button type="button" @click="openTenant = !openTenant; if(openTenant) { $nextTick(() => $refs.tenantSearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span x-text="selectedTenantLabel ? selectedTenantLabel : 'Select Tenant / Unit...'" class="truncate"></span>
                                    <span class="ml-2 text-xs opacity-60">▼</span>
                                </button>

                                <div x-show="openTenant" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-ref="tenantSearchInput" x-model="searchTenant" placeholder="Type tenant or unit to search..."
                                            @keydown.arrow-down.prevent="highlightedTenantIndex = (highlightedTenantIndex + 1) % filteredTenants.length"
                                            @keydown.arrow-up.prevent="highlightedTenantIndex = (highlightedTenantIndex - 1 + filteredTenants.length) % filteredTenants.length"
                                            @keydown.enter.prevent="if(highlightedTenantIndex >= 0 && filteredTenants[highlightedTenantIndex]) selectTenant(filteredTenants[highlightedTenantIndex])"
                                            @keydown.escape="openTenant = false; highlightedTenantIndex = -1"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-base font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filteredTenants" :key="opt.id">
                                            <button type="button" @click="selectTenant(opt)"
                                                @mouseenter="highlightedTenantIndex = index"
                                                class="w-full text-left px-3.5 py-2.5 rounded-xl transition-colors flex items-center justify-between"
                                                :class="tenantId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedTenantIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <span x-text="opt.label" class="font-black text-base sm:text-lg truncate"></span>
                                                <span x-show="tenantId == opt.id" class="font-black text-base">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filteredTenants.length === 0" class="px-4 py-3 text-center text-xs font-semibold text-gray-400">
                                            No matching Tenant found
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Case 2: Searchable Party Dropdown --}}
                            <div x-show="paidToType === 'other'" class="w-full relative" @click.away="openParty = false; highlightedPartyIndex = -1">
                                <input type="hidden" name="party_id" x-model="partyId" :required="paidToType === 'other'">
                                <button type="button" @click="openParty = !openParty; if(openParty) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span x-text="selectedPartyName ? selectedPartyName : 'Select Registered Party Head...'" class="truncate"></span>
                                    <span class="ml-2 text-xs opacity-60">▼</span>
                                </button>

                                <div x-show="openParty" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-ref="partySearchInput" x-model="searchParty" placeholder="Type party name to search..."
                                            @keydown.arrow-down.prevent="highlightedPartyIndex = (highlightedPartyIndex + 1) % filteredParties.length"
                                            @keydown.arrow-up.prevent="highlightedPartyIndex = (highlightedPartyIndex - 1 + filteredParties.length) % filteredParties.length"
                                            @keydown.enter.prevent="if(highlightedPartyIndex >= 0 && filteredParties[highlightedPartyIndex]) selectParty(filteredParties[highlightedPartyIndex])"
                                            @keydown.escape="openParty = false; highlightedPartyIndex = -1"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-base font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filteredParties" :key="opt.id">
                                            <button type="button" @click="selectParty(opt)"
                                                @mouseenter="highlightedPartyIndex = index"
                                                class="w-full text-left px-3.5 py-2.5 rounded-xl transition-colors flex items-center justify-between"
                                                :class="partyId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedPartyIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <span x-text="opt.name" class="font-black text-base sm:text-lg truncate"></span>
                                                <span x-show="partyId == opt.id" class="font-black text-base">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filteredParties.length === 0" class="px-4 py-3 text-center text-xs font-semibold text-gray-400">
                                            No matching Party found
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Case 3: Searchable Landlord Dropdown --}}
                            <div x-show="paidToType === 'landlord'" class="w-full relative" @click.away="openLandlord = false; highlightedLandlordIndex = -1">
                                <input type="hidden" name="landlord_id" x-model="landlordId" :required="paidToType === 'landlord'">
                                <button type="button" @click="openLandlord = !openLandlord; if(openLandlord) { $nextTick(() => $refs.landlordSearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span x-text="selectedLandlordName ? selectedLandlordName : 'Select Landlord / Owner...'" class="truncate"></span>
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

                            {{-- Case 4: Searchable Destination Account Dropdown --}}
                            <div x-show="paidToType === 'account'" class="w-full relative" @click.away="openToAccount = false; highlightedToAccountIndex = -1">
                                <input type="hidden" name="to_payment_account_id" x-model="toAccountId" :required="paidToType === 'account'">
                                <button type="button" @click="openToAccount = !openToAccount; if(openToAccount) { $nextTick(() => $refs.toAccountSearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span x-text="selectedToAccountName ? selectedToAccountName : 'Select Destination Account...'" class="truncate"></span>
                                    <span class="ml-2 text-xs opacity-60">▼</span>
                                </button>

                                <div x-show="openToAccount" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-ref="toAccountSearchInput" x-model="searchToAccount" placeholder="Type account name to search..."
                                            @keydown.arrow-down.prevent="highlightedToAccountIndex = (highlightedToAccountIndex + 1) % filteredToAccounts.length"
                                            @keydown.arrow-up.prevent="highlightedToAccountIndex = (highlightedToAccountIndex - 1 + filteredToAccounts.length) % filteredToAccounts.length"
                                            @keydown.enter.prevent="if(highlightedToAccountIndex >= 0 && filteredToAccounts[highlightedToAccountIndex]) selectToAccount(filteredToAccounts[highlightedToAccountIndex])"
                                            @keydown.escape="openToAccount = false; highlightedToAccountIndex = -1"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-base font-bold text-gray-900 dark:text-white focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filteredToAccounts" :key="opt.id">
                                            <button type="button" @click="selectToAccount(opt)"
                                                @mouseenter="highlightedToAccountIndex = index"
                                                class="w-full text-left px-3.5 py-2.5 rounded-xl transition-colors flex items-center justify-between"
                                                :class="toAccountId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedToAccountIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <span x-text="opt.name" class="font-black text-base sm:text-lg truncate"></span>
                                                <span x-show="toAccountId == opt.id" class="font-black text-base">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filteredToAccounts.length === 0" class="px-4 py-3 text-center text-xs font-semibold text-gray-400">
                                            No matching Account found
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ROW 3: Payment Amount & Paid From Payment Account --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-10 rounded-b-2xl">
                    {{-- Field 1: Payment Amount --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide rounded-bl-2xl md:rounded-bl-none">Payment Amount <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="text" x-model="displayAmount" @input="formatAmount($event.target.value)" required placeholder="0.00"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2 text-lg sm:text-xl font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            <input type="hidden" name="amount" x-model="voucherAmount">
                        </div>
                    </div>

                    {{-- Field 2: Paid From Account --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-extrabold text-sm sm:text-base tracking-wide">Paid From <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center md:rounded-br-2xl">
                            <select name="payment_account_id" x-model="paymentAccountId" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3 py-2 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                <option value="">Select Account...</option>
                                @foreach($paymentAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('payment_account_id', $voucher->payment_account_id) == $account->id ? 'selected' : '' }}>
                                        {{ $account->name }} ({{ ucfirst($account->type) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            {{-- ROW 4 / BOTTOM SECTION: Approved by & Description --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">
                {{-- Left Box: Approved by Box --}}
                <div class="bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white rounded-2xl p-4 flex flex-col justify-center border border-gray-200 dark:border-gray-700 shadow-xs">
                    <p class="text-xs sm:text-sm font-bold text-gray-700 dark:text-gray-300">
                        Approved by: <span class="text-brand-600 dark:text-brand-400 font-extrabold ml-1">{{ $voucher->user->name ?? auth()->user()->name }}</span>
                    </p>
                </div>

                {{-- Right Box: Remarks --}}
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 shadow-xs">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                        Remarks:
                    </label>
                    <textarea name="notes" rows="2" placeholder="Payment voucher remarks or notes..."
                        x-model="notes" @input="isNotesManuallyEdited = true"
                        class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl p-3.5 text-base sm:text-lg font-black text-gray-900 dark:text-white placeholder-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all"></textarea>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('payment-vouchers.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold transition-all text-xs sm:text-sm">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 rounded-xl bg-brand-500 hover:bg-brand-600 text-white font-black text-xs sm:text-sm shadow-md transition-all cursor-pointer">
                    Update Voucher
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
                    defaultDate: '{{ old('date', $voucher->date->format('Y-m-d')) }}'
                });
            }

            @if ($errors->any())
                Swal.fire({
                    title: 'Form Validation Error',
                    text: "{{ $errors->first() }}",
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