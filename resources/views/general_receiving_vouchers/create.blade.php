@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="New General Receiving Voucher" />

    <form action="{{ route('general-receiving-vouchers.store') }}" method="POST"
        @submit.prevent="handleSubmit($event)"
        x-data="{
            receivedFromType: '{{ old('received_from_type', 'party') }}',
            partyId: '{{ old('party_id', '') }}',
            landlordId: '{{ old('landlord_id', '') }}',
            fromAccountId: '{{ old('from_payment_account_id', '') }}',
            paymentAccountId: '{{ old('payment_account_id', '') }}',
            voucherAmount: '{{ old('amount', '') }}',
            displayAmount: '',

            openParty: false,
            searchParty: '',
            highlightedPartyIndex: -1,

            openAccount: false,
            searchAccount: '',
            highlightedAccountIndex: -1,

            openLandlord: false,
            searchLandlord: '',
            highlightedLandlordIndex: -1,

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

            accountOptions: [
                @foreach($paymentAccounts as $account)
                { id: '{{ $account->id }}', name: '{{ addslashes($account->name) }} ({{ ucfirst($account->type) }})' },
                @endforeach
            ],

            get filteredParties() {
                if (!this.searchParty) return this.partyOptions;
                let s = this.searchParty.toLowerCase();
                return this.partyOptions.filter(p => p.name.toLowerCase().includes(s));
            },
            get selectedPartyName() {
                let selected = this.partyOptions.find(p => p.id == this.partyId);
                return selected ? selected.name : '';
            },

            get filteredSourceAccounts() {
                if (!this.searchAccount) return this.accountOptions;
                let s = this.searchAccount.toLowerCase();
                return this.accountOptions.filter(a => a.name.toLowerCase().includes(s));
            },
            get selectedSourceAccountName() {
                let selected = this.accountOptions.find(a => a.id == this.fromAccountId);
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

            notes: @js(old('notes', '')),
            isNotesManuallyEdited: {{ old('notes') ? 'true' : 'false' }},

            updateAutoRemarks() {
                if (this.isNotesManuallyEdited) return;

                let entityName = '';
                if (this.receivedFromType === 'party') entityName = this.selectedPartyName;
                else if (this.receivedFromType === 'account') entityName = this.selectedSourceAccountName;
                else if (this.receivedFromType === 'landlord') entityName = this.selectedLandlordName;

                let amtNum = parseFloat(this.voucherAmount || 0);
                let amtStr = amtNum > 0 ? 'Rs. ' + Math.round(amtNum).toLocaleString() : '';

                if (!amtStr && !entityName) {
                    this.notes = '';
                    return;
                }

                let remarks = 'Payment received';
                if (amtStr) remarks += ' of ' + amtStr;
                if (entityName) remarks += ' from ' + entityName;
                this.notes = remarks;
            },

            selectParty(opt) {
                this.partyId = opt.id;
                this.openParty = false;
                this.searchParty = '';
                this.highlightedPartyIndex = -1;
                this.updateAutoRemarks();
            },
            selectSourceAccount(opt) {
                this.fromAccountId = opt.id;
                this.openAccount = false;
                this.searchAccount = '';
                this.highlightedAccountIndex = -1;
                this.updateAutoRemarks();
            },
            selectLandlord(opt) {
                this.landlordId = opt.id;
                this.openLandlord = false;
                this.searchLandlord = '';
                this.highlightedLandlordIndex = -1;
                this.updateAutoRemarks();
            },

            init() {
                this.$watch('voucherAmount', () => this.updateAutoRemarks());
                this.$watch('partyId', () => this.updateAutoRemarks());
                this.$watch('fromAccountId', () => this.updateAutoRemarks());
                this.$watch('landlordId', () => this.updateAutoRemarks());
                this.$watch('receivedFromType', () => {
                    this.partyId = '';
                    this.fromAccountId = '';
                    this.landlordId = '';
                    this.updateAutoRemarks();
                });

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

            handleSubmit(event) {
                if (this.receivedFromType === 'party' && !this.partyId) {
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

                if (this.receivedFromType === 'account' && !this.fromAccountId) {
                    Swal.fire({
                        title: 'Source Account Required',
                        text: 'Please select a Source Transfer Account.',
                        icon: 'warning',
                        confirmButtonText: 'OK',
                        customClass: {
                            confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-2.5 text-sm font-bold text-white shadow-md hover:bg-brand-700 transition-colors cursor-pointer'
                        },
                        buttonsStyling: false
                    });
                    return;
                }

                if (this.receivedFromType === 'landlord' && !this.landlordId) {
                    Swal.fire({
                        title: 'Landlord Required',
                        text: 'Please select a Landlord / Owner.',
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
                        title: 'Payment Account Required',
                        text: 'Please select a Deposit Payment Account.',
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
                    title: 'Confirm General Receiving Voucher',
                    text: 'Are you sure you want to save and generate this general receiving voucher for Rs. ' + amt.toLocaleString('en-US', {minimumFractionDigits: 2}) + '?',
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

        {{-- FORM CONTAINER WITH NO OUTER BG COLOR, CENTERED TITLE, ADAPTED TO THEME --}}
        <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-200 dark:border-gray-800 p-6 sm:p-8 shadow-sm text-gray-900 dark:text-white font-sans relative">

            {{-- FORM HEADER WITH CENTERED TITLE & RIGHT CORNER VOUCHER NUMBER --}}
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 mb-6 pb-4 border-b border-gray-200 dark:border-gray-800">
                <div class="hidden sm:block w-36"></div>
                <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-brand-600 dark:text-brand-400 uppercase text-center">
                    General Receiving Voucher
                </h2>
                <div class="inline-flex items-center gap-2 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 px-4 py-2 shadow-2xs">
                    <span class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Voucher No:</span>
                    <span class="text-base sm:text-lg font-black font-mono text-brand-600 dark:text-brand-400">{{ $nextVoucherNo }}</span>
                </div>
            </div>

            {{-- FORM GRID CONTAINER --}}
            <div class="flex flex-col gap-[2px] bg-gray-200 dark:bg-gray-700 rounded-2xl overflow-visible mb-6 border border-gray-200 dark:border-gray-700">
                
                {{-- ROW 1: Voucher Date & Received From Type --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-40 rounded-t-2xl">
                    {{-- Field 1: Voucher Date --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide rounded-tl-2xl">Voucher Date</div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="text" id="voucher_date" name="date" value="{{ old('date', now()->toDateString()) }}" required autocomplete="off"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all cursor-pointer">
                        </div>
                    </div>

                    {{-- Field 2: Received From Type --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Received From <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center md:rounded-tr-2xl">
                            <select name="received_from_type" x-model="receivedFromType" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all">
                                <option value="party">Registered Party Head</option>
                                <option value="account">Transfer From Account</option>
                                <option value="landlord">Landlord / Owner</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- ROW 2: Searchable Dynamic Entity Selection --}}
                <div class="grid grid-cols-1 min-h-[52px] bg-gray-200 dark:bg-gray-700 relative z-50">
                    <div class="grid grid-cols-3 min-h-[52px] relative z-50">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">
                            <span x-text="receivedFromType === 'party' ? 'Select Party' : (receivedFromType === 'account' ? 'Source Account' : 'Select Landlord')"></span>
                            <span class="text-rose-300 ml-1">*</span>
                        </div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            
                            {{-- Case 1: Searchable Party Dropdown --}}
                            <div x-show="receivedFromType === 'party'" class="w-full relative" @click.away="openParty = false; highlightedPartyIndex = -1">
                                <input type="hidden" name="party_id" x-model="partyId" :required="receivedFromType === 'party'">
                                <button type="button" @click="openParty = !openParty; if(openParty) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span x-text="selectedPartyName ? selectedPartyName : 'Select Registered Party Head...'" class="truncate"></span>
                                    <span class="ml-2 text-sm opacity-60">▼</span>
                                </button>

                                <div x-show="openParty" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-ref="partySearchInput" x-model="searchParty" placeholder="Type party name to search..."
                                            @keydown.arrow-down.prevent="highlightedPartyIndex = (highlightedPartyIndex + 1) % filteredParties.length"
                                            @keydown.arrow-up.prevent="highlightedPartyIndex = (highlightedPartyIndex - 1 + filteredParties.length) % filteredParties.length"
                                            @keydown.enter.prevent="if(highlightedPartyIndex >= 0 && filteredParties[highlightedPartyIndex]) selectParty(filteredParties[highlightedPartyIndex])"
                                            @keydown.escape="openParty = false; highlightedPartyIndex = -1"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-base sm:text-lg text-gray-900 dark:text-white font-black focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filteredParties" :key="opt.id">
                                            <button type="button" @click="selectParty(opt)"
                                                @mouseenter="highlightedPartyIndex = index"
                                                class="w-full text-left px-4 py-3 rounded-xl transition-colors flex items-center justify-between"
                                                :class="partyId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedPartyIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <span x-text="opt.name" class="font-black text-base truncate"></span>
                                                <span x-show="partyId == opt.id" class="font-black text-lg">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filteredParties.length === 0" class="px-4 py-3 text-center text-xs sm:text-sm font-semibold text-gray-400">
                                            No matching Party found
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Case 2: Searchable Source Account Dropdown --}}
                            <div x-show="receivedFromType === 'account'" class="w-full relative" @click.away="openAccount = false; highlightedAccountIndex = -1">
                                <input type="hidden" name="from_payment_account_id" x-model="fromAccountId" :required="receivedFromType === 'account'">
                                <button type="button" @click="openAccount = !openAccount; if(openAccount) { $nextTick(() => $refs.accountSearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span x-text="selectedSourceAccountName ? selectedSourceAccountName : 'Select Source Account...'" class="truncate"></span>
                                    <span class="ml-2 text-sm opacity-60">▼</span>
                                </button>

                                <div x-show="openAccount" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-ref="accountSearchInput" x-model="searchAccount" placeholder="Type account name to search..."
                                            @keydown.arrow-down.prevent="highlightedAccountIndex = (highlightedAccountIndex + 1) % filteredSourceAccounts.length"
                                            @keydown.arrow-up.prevent="highlightedAccountIndex = (highlightedAccountIndex - 1 + filteredSourceAccounts.length) % filteredSourceAccounts.length"
                                            @keydown.enter.prevent="if(highlightedAccountIndex >= 0 && filteredSourceAccounts[highlightedAccountIndex]) selectSourceAccount(filteredSourceAccounts[highlightedAccountIndex])"
                                            @keydown.escape="openAccount = false; highlightedAccountIndex = -1"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-base sm:text-lg text-gray-900 dark:text-white font-black focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filteredSourceAccounts" :key="opt.id">
                                            <button type="button" @click="selectSourceAccount(opt)"
                                                @mouseenter="highlightedAccountIndex = index"
                                                class="w-full text-left px-4 py-3 rounded-xl transition-colors flex items-center justify-between"
                                                :class="fromAccountId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedAccountIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <span x-text="opt.name" class="font-black text-base truncate"></span>
                                                <span x-show="fromAccountId == opt.id" class="font-black text-lg">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filteredSourceAccounts.length === 0" class="px-4 py-3 text-center text-xs sm:text-sm font-semibold text-gray-400">
                                            No matching Account found
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Case 3: Searchable Landlord Dropdown --}}
                            <div x-show="receivedFromType === 'landlord'" class="w-full relative" @click.away="openLandlord = false; highlightedLandlordIndex = -1">
                                <input type="hidden" name="landlord_id" x-model="landlordId" :required="receivedFromType === 'landlord'">
                                <button type="button" @click="openLandlord = !openLandlord; if(openLandlord) { $nextTick(() => $refs.landlordSearchInput.focus()) }"
                                    class="w-full text-left bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white flex items-center justify-between shadow-2xs transition-all hover:border-brand-400">
                                    <span x-text="selectedLandlordName ? selectedLandlordName : 'Select Landlord / Owner...'" class="truncate"></span>
                                    <span class="ml-2 text-sm opacity-60">▼</span>
                                </button>

                                <div x-show="openLandlord" x-transition x-cloak
                                    class="absolute left-0 right-0 top-full z-[999999] mt-2 min-w-[300px] sm:min-w-[400px] rounded-2xl border-2 border-brand-500 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden text-gray-900 dark:text-white">
                                    <div class="p-3 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-950">
                                        <input type="text" x-ref="landlordSearchInput" x-model="searchLandlord" placeholder="Type landlord name to search..."
                                            @keydown.arrow-down.prevent="highlightedLandlordIndex = (highlightedLandlordIndex + 1) % filteredLandlords.length"
                                            @keydown.arrow-up.prevent="highlightedLandlordIndex = (highlightedLandlordIndex - 1 + filteredLandlords.length) % filteredLandlords.length"
                                            @keydown.enter.prevent="if(highlightedLandlordIndex >= 0 && filteredLandlords[highlightedLandlordIndex]) selectLandlord(filteredLandlords[highlightedLandlordIndex])"
                                            @keydown.escape="openLandlord = false; highlightedLandlordIndex = -1"
                                            class="w-full rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 px-4 py-3 text-base sm:text-lg text-gray-900 dark:text-white font-black focus:border-brand-500 focus:outline-none">
                                    </div>
                                    <div class="max-h-[300px] overflow-y-auto p-1.5 space-y-1 text-sm">
                                        <template x-for="(opt, index) in filteredLandlords" :key="opt.id">
                                            <button type="button" @click="selectLandlord(opt)"
                                                @mouseenter="highlightedLandlordIndex = index"
                                                class="w-full text-left px-4 py-3 rounded-xl transition-colors flex items-center justify-between"
                                                :class="landlordId == opt.id ? 'bg-brand-600 text-white font-black' : (highlightedLandlordIndex === index ? 'bg-brand-50 text-brand-950 dark:bg-brand-950/50 dark:text-brand-200 font-bold' : 'text-gray-800 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/5 font-semibold')">
                                                <span x-text="opt.name" class="font-black text-base truncate"></span>
                                                <span x-show="landlordId == opt.id" class="font-black text-lg">✓</span>
                                            </button>
                                        </template>
                                        <div x-show="filteredLandlords.length === 0" class="px-4 py-3 text-center text-xs sm:text-sm font-semibold text-gray-400">
                                            No matching Landlord found
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ROW 3: Payment Amount & Payment Method / Account --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-[2px] bg-gray-200 dark:bg-gray-700 relative z-10 rounded-b-2xl">
                    {{-- Field 1: Payment Amount --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide rounded-bl-2xl md:rounded-bl-none">Payment Amount <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center">
                            <input type="text" x-model="displayAmount" @input="formatAmount($event.target.value)" required placeholder="0.00"
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                            <input type="hidden" name="amount" x-model="voucherAmount">
                        </div>
                    </div>

                    {{-- Field 2: Deposit Payment Account --}}
                    <div class="grid grid-cols-3 min-h-[52px]">
                        <div class="bg-brand-600 dark:bg-brand-900 text-white px-4 py-3 flex items-center font-bold text-sm tracking-wide">Deposit Account <span class="text-rose-300 ml-1">*</span></div>
                        <div class="col-span-2 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-3 py-2 flex items-center md:rounded-br-2xl">
                            <select name="payment_account_id" x-model="paymentAccountId" required
                                class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl px-3.5 py-2.5 text-base sm:text-lg font-black text-gray-900 dark:text-white focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none">
                                <option value="">Select Deposit Account...</option>
                                @foreach($paymentAccounts as $account)
                                    <option value="{{ $account->id }}" {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
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
                        Approved by: <span class="text-brand-600 dark:text-brand-400 font-extrabold ml-1">{{ auth()->user()->name }}</span>
                    </p>
                </div>

                {{-- Right Box: Remarks --}}
                <div class="md:col-span-2 bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl p-4 shadow-xs">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                        Remarks:
                    </label>
                    <textarea name="notes" x-model="notes" @input="isNotesManuallyEdited = true" rows="2" placeholder="General receipt remarks or notes..."
                        class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-xl p-3.5 text-base sm:text-lg font-black text-gray-900 dark:text-white placeholder-gray-400 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 focus:outline-none transition-all"></textarea>
                </div>
            </div>

            {{-- ACTION BUTTONS --}}
            <div class="flex items-center justify-end gap-3 mt-6 pt-4 border-t border-gray-200 dark:border-gray-800">
                <a href="{{ route('general-receiving-vouchers.index') }}" class="px-5 py-2.5 rounded-xl border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 font-bold transition-all text-xs sm:text-sm">
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
