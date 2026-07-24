@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm font-semibold text-gray-500 dark:text-gray-400">
            <a href="{{ route('general-receiving-vouchers.index') }}" class="hover:text-brand-500">General Receiving Vouchers</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Edit General Voucher: {{ $voucher->voucher_no }}</span>
        </div>

        <x-common.component-card :title="'Edit General Receiving Voucher: ' . $voucher->voucher_no" desc="Modify details of this general receipt voucher. Restricted to Super Administrators only.">
            <form action="{{ route('general-receiving-vouchers.update', $voucher) }}" method="POST" class="space-y-6"
                x-data="{
                    receivedFromType: '{{ old('received_from_type', $voucher->received_from_type ?? 'party') }}',
                    partyId: '{{ old('party_id', $voucher->party_id) }}',
                    partyName: '{{ old('party_name', $voucher->party?->name) }}',
                    landlordId: '{{ old('landlord_id', $voucher->landlord_id) }}',
                    landlordName: '{{ addslashes($voucher->landlord?->name ?? '') }}',
                    fromAccountId: '{{ old('from_payment_account_id', $voucher->from_payment_account_id) }}',
                    paymentAccountId: '{{ old('payment_account_id', $voucher->payment_account_id) }}',
                    amount: '{{ old('amount', $voucher->amount) }}',
                    displayAmount: '',
                    landlordReceivables: null,
                    landlordLoading: false,
                    accounts: [
                        @foreach($paymentAccounts as $acc)
                            { id: '{{ $acc->id }}', name: '{{ addslashes($acc->name) }} ({{ strtoupper($acc->type) }})' },
                        @endforeach
                    ],
                    get selectedDepositAccountName() {
                        let acc = this.accounts.find(a => a.id == this.paymentAccountId);
                        return acc ? acc.name : '';
                    },
                    get receivedFromDetail() {
                        if (this.receivedFromType === 'party') {
                            return this.partyName ? 'Party: ' + this.partyName : 'Select Registered Party Head';
                        } else if (this.receivedFromType === 'landlord') {
                            return this.landlordName ? 'Landlord: ' + this.landlordName : 'Select Landlord / Owner';
                        } else if (this.receivedFromType === 'account') {
                            let srcAcc = this.accounts.find(a => a.id == this.fromAccountId);
                            return srcAcc ? 'Transfer From: ' + srcAcc.name : 'Select Source Account';
                        }
                        return 'General Voucher';
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
                    fmt(n) {
                        return 'Rs. ' + Number(n).toLocaleString('en-PK', { maximumFractionDigits: 0 });
                    },
                    async fetchLandlordReceivables(landlordId) {
                        if (!landlordId) { this.landlordReceivables = null; return; }
                        this.landlordLoading = true;
                        this.landlordReceivables = null;
                        try {
                            const res = await fetch(`{{ route('ajax.landlord-receivables') }}?landlord_id=${landlordId}`);
                            this.landlordReceivables = await res.json();
                        } catch(e) {
                            this.landlordReceivables = null;
                        }
                        this.landlordLoading = false;
                    },
                    init() {
                        if (this.amount) {
                            this.formatAmount(String(this.amount));
                        }
                        @if(old('received_from_type', $voucher->received_from_type) === 'landlord')
                            this.fetchLandlordReceivables('{{ old('landlord_id', $voucher->landlord_id) }}');
                        @endif
                    }
                }"
                @party-selected.window="partyId = $event.detail.id; partyName = $event.detail.name"
                @landlord-selected.window="landlordId = $event.detail.id; landlordName = $event.detail.name; fetchLandlordReceivables($event.detail.id)">
                @csrf
                @method('PUT')

                @php
                    $input = 'w-full rounded-2xl border-2 border-gray-300 bg-white px-5 py-3.5 text-lg sm:text-xl font-bold text-gray-900 shadow-xs focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white';
                    $label = 'mb-2 block text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-300';
                @endphp

                {{-- STICKY BIG HEADING BANNER --}}
                <div class="sticky mb-6 rounded-2xl border-2 border-emerald-500 bg-white dark:bg-gray-900 p-5 shadow-xl backdrop-blur-md"
                    style="position: sticky; top: 72px; z-index: 990;">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-emerald-600 text-white shadow-md text-3xl font-black">
                                📥
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">
                                    Edit General Receiving Voucher: {{ $voucher->voucher_no }}
                                </p>
                                <div class="flex flex-wrap items-baseline gap-2 mt-0.5">
                                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-white"
                                        x-text="receivedFromDetail"></h2>
                                </div>
                                <div class="flex items-center gap-2 mt-1 text-xs font-bold text-gray-600 dark:text-gray-300">
                                    <span>Deposit Account:</span>
                                    <span class="text-brand-600 dark:text-brand-400 font-extrabold" x-text="selectedDepositAccountName || 'Not Selected'"></span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-gray-400 block">Voucher Amount</span>
                            <span class="text-2xl sm:text-3xl font-black font-mono text-emerald-600 dark:text-emerald-400"
                                  x-text="displayAmount ? 'Rs. ' + displayAmount : 'Rs. 0.00'"></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    {{-- Received From Type --}}
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Received From Type <span class="text-red-500">*</span></label>
                        <select name="received_from_type" x-model="receivedFromType" class="{{ $input }}" required>
                            <option value="party" {{ old('received_from_type', $voucher->received_from_type ?? 'party') === 'party' ? 'selected' : '' }}>Party / Vendor Head</option>
                            <option value="landlord" {{ old('received_from_type', $voucher->received_from_type) === 'landlord' ? 'selected' : '' }}>Landlord / Property Owner</option>
                            <option value="account" {{ old('received_from_type', $voucher->received_from_type) === 'account' ? 'selected' : '' }}>Payment Account (Inter-Account Transfer In)</option>
                        </select>
                    </div>

                    {{-- Party Dropdown (Searchable via Alpine) --}}
                    <div x-show="receivedFromType === 'party'" x-transition x-cloak class="sm:col-span-2"
                         x-data="{
                            open: false,
                            search: '',
                            selectedId: '{{ old('party_id', $voucher->party_id) }}',
                            selectedLabel: '{{ old('party_name', $voucher->party?->name) }}',
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
                                $dispatch('party-selected', { id: p.id, name: p.name });
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
                        <input type="hidden" name="party_id" :value="selectedId">

                        <div class="relative" :class="open ? 'relative z-[99999]' : 'relative'">
                            {{-- Trigger --}}
                            <div tabindex="0"
                                 @click="open = !open; if(open) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                 @keydown.space.prevent="open = !open; if(open) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                 @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                 @click.outside="open = false"
                                 class="{{ $input }} cursor-pointer flex justify-between items-center">
                                <span x-text="selectedLabel || 'Select Party Head'" :class="selectedLabel ? 'text-gray-900 dark:text-white font-bold' : 'text-gray-400 dark:text-gray-500 font-normal'"></span>
                                <svg class="h-6 w-6 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
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
                                 class="absolute left-0 z-[99999] mt-2 w-full rounded-2xl border-2 border-brand-500 bg-white shadow-2xl dark:border-brand-500 dark:bg-gray-900 overflow-hidden"
                                 style="display: none;">
                                
                                <!-- Search Input -->
                                <div class="p-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.02]">
                                    <input x-ref="partySearchInput"
                                           x-model="search"
                                           @keydown.arrow-down.prevent="moveHighlight(1)"
                                           @keydown.arrow-up.prevent="moveHighlight(-1)"
                                           @keydown.enter.prevent="selectHighlighted()"
                                           @keydown.escape.prevent="open = false; highlightedIndex = -1"
                                           type="text"
                                           placeholder="Type to search party head..."
                                           class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-base text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white font-medium">
                                </div>

                                <!-- Options List -->
                                <ul class="max-h-64 overflow-y-auto p-2 space-y-1">
                                    <template x-if="filteredParties.length === 0">
                                        <li class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 font-medium">No matching party heads found.</li>
                                    </template>
                                    <template x-for="(p, index) in filteredParties" :key="p.id">
                                        <li @click="selectParty(p)"
                                            @mouseenter="highlightedIndex = index"
                                            :class="{
                                                'bg-brand-500 text-white font-bold shadow-xs': highlightedIndex === index,
                                                'text-gray-800 dark:text-gray-200': highlightedIndex !== index
                                            }"
                                            class="px-4 py-3 text-base rounded-xl cursor-pointer hover:bg-brand-500 hover:text-white transition-colors flex justify-between items-center">
                                            <span x-text="p.name" class="font-bold"></span>
                                            <span x-text="p.phone" class="text-xs opacity-75"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Searchable Landlord Dropdown (when receivedFromType === 'landlord') --}}
                    <div class="sm:col-span-2" x-show="receivedFromType === 'landlord'" x-transition x-cloak
                         x-data="{
                            open: false,
                            search: '',
                            selectedId: '{{ old('landlord_id', $voucher->landlord_id) }}',
                            selectedLabel: '{{ old('landlord_name', $voucher->landlord?->name) }}',
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
                                $dispatch('landlord-selected', { id: l.id, name: l.name });
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
                        <label class="{{ $label }}">Landlord <span class="text-red-500">*</span></label>
                        <input type="hidden" name="landlord_id" :value="selectedId">

                        <div class="relative" :class="open ? 'relative z-[99999]' : 'relative'">
                            <div tabindex="0"
                                 @click="open = !open; if(open) { $nextTick(() => $refs.landlordSearchInputEdit.focus()) }"
                                 @keydown.space.prevent="open = !open; if(open) { $nextTick(() => $refs.landlordSearchInputEdit.focus()) }"
                                 @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.landlordSearchInputEdit.focus()) }"
                                 @click.outside="open = false"
                                 class="{{ $input }} cursor-pointer flex justify-between items-center">
                                <span x-text="selectedLabel || 'Select Landlord'" :class="selectedLabel ? 'text-gray-900 dark:text-white font-bold' : 'text-gray-400 dark:text-gray-500 font-normal'"></span>
                                <svg class="h-6 w-6 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>

                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-100"
                                 x-transition:enter-start="opacity-0 transform scale-95"
                                 x-transition:enter-end="opacity-100 transform scale-100"
                                 x-transition:leave="transition ease-in duration-75"
                                 x-transition:leave-start="opacity-100 transform scale-100"
                                 x-transition:leave-end="opacity-0 transform scale-95"
                                 class="absolute left-0 z-[99999] mt-2 w-full rounded-2xl border-2 border-brand-500 bg-white shadow-2xl dark:border-brand-500 dark:bg-gray-900 overflow-hidden"
                                 style="display: none;">
                                <div class="p-3 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-white/[0.02]">
                                    <input x-ref="landlordSearchInputEdit"
                                           x-model="search"
                                           @keydown.arrow-down.prevent="moveHighlight(1)"
                                           @keydown.arrow-up.prevent="moveHighlight(-1)"
                                           @keydown.enter.prevent="selectHighlighted()"
                                           @keydown.escape.prevent="open = false; highlightedIndex = -1"
                                           type="text"
                                           placeholder="Type to search landlord..."
                                           class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-base text-gray-900 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white font-medium">
                                </div>
                                <ul class="max-h-64 overflow-y-auto p-2 space-y-1">
                                    <template x-if="filteredLandlords.length === 0">
                                        <li class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 font-medium">No matching landlords found.</li>
                                    </template>
                                    <template x-for="(l, index) in filteredLandlords" :key="l.id">
                                        <li @click="selectLandlord(l)"
                                            @mouseenter="highlightedIndex = index"
                                            :class="{
                                                'bg-brand-500 text-white font-bold shadow-xs': highlightedIndex === index,
                                                'text-gray-800 dark:text-gray-200': highlightedIndex !== index
                                            }"
                                            class="px-4 py-3 text-base rounded-xl cursor-pointer hover:bg-brand-500 hover:text-white transition-colors flex justify-between items-center">
                                            <span x-text="l.name" class="font-bold"></span>
                                            <span x-text="l.phone" class="text-xs opacity-75"></span>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>

                    {{-- Landlord Pending Receivables Panel --}}
                    <div class="sm:col-span-2" x-show="receivedFromType === 'landlord'" x-cloak>
                        <!-- Loading state -->
                        <div x-show="landlordLoading" class="flex items-center gap-3 py-6 px-4 rounded-2xl bg-amber-50 dark:bg-amber-950/20 text-base font-bold text-amber-800 dark:text-amber-300">
                            <svg class="h-6 w-6 animate-spin text-amber-600" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            Loading pending receivables calculation...
                        </div>

                        <!-- No data / not selected yet -->
                        <div x-show="!landlordLoading && !landlordReceivables"
                             class="rounded-2xl border-2 border-dashed border-amber-300 dark:border-amber-800 py-6 px-5 text-base font-bold text-amber-700 dark:text-amber-400 text-center bg-amber-50/50 dark:bg-amber-950/10">
                            📌 Select a landlord above to view their complete pending receivables breakdown.
                        </div>

                        <!-- Receivables Summary -->
                        <div x-show="!landlordLoading && landlordReceivables" x-transition>
                            <div class="rounded-2xl border-2 border-amber-400 bg-amber-50/80 dark:border-amber-700/60 dark:bg-amber-950/20 p-6 shadow-md">
                                <div class="flex items-center justify-between mb-4">
                                    <h5 class="text-base sm:text-lg font-black uppercase tracking-wider text-amber-900 dark:text-amber-300 flex items-center gap-2">
                                        <span class="text-2xl">⚠️</span> Landlord Pending Receivables
                                    </h5>
                                    <a x-bind:href="landlordReceivables ? `{{ url('landlord-ledgers') }}?landlord_id=${landlordReceivables.landlord_id ?? ''}` : '#'"
                                       class="inline-flex items-center gap-1.5 rounded-xl bg-amber-200/80 px-3.5 py-1.5 text-xs sm:text-sm font-extrabold text-amber-900 hover:bg-amber-300 dark:bg-amber-900/60 dark:text-amber-200 transition-colors" target="_blank">
                                        View Full Ledger →
                                    </a>
                                </div>

                                <!-- Summary Cards -->
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                                    <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-xs dark:border-gray-800 dark:bg-gray-900">
                                        <p class="text-xs font-black uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Owed</p>
                                        <p class="mt-1 text-xl sm:text-2xl font-black font-mono text-gray-900 dark:text-white" x-text="landlordReceivables ? fmt(landlordReceivables.total_owed) : ''"></p>
                                    </div>
                                    <div class="rounded-xl border border-emerald-200 bg-white p-4 text-center shadow-xs dark:border-gray-800 dark:bg-gray-900">
                                        <p class="text-xs font-black uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Total Received</p>
                                        <p class="mt-1 text-xl sm:text-2xl font-black font-mono text-emerald-600 dark:text-emerald-400" x-text="landlordReceivables ? fmt(landlordReceivables.total_received) : ''"></p>
                                    </div>
                                    <div class="rounded-xl border-2 border-red-400 bg-white p-4 text-center shadow-xs dark:border-red-800 dark:bg-gray-900">
                                        <p class="text-xs font-black uppercase tracking-wider text-red-600 dark:text-red-400">Pending Balance</p>
                                        <p class="mt-1 text-2xl sm:text-3xl font-black font-mono text-red-600 dark:text-red-400"
                                           x-text="landlordReceivables ? fmt(landlordReceivables.pending_balance) : ''"></p>
                                    </div>
                                </div>

                                <!-- Per-unit Breakdown -->
                                <template x-if="landlordReceivables && landlordReceivables.units && landlordReceivables.units.length > 0">
                                    <div>
                                        <p class="text-xs font-black uppercase tracking-wider text-amber-900 dark:text-amber-300 mb-3 flex items-center gap-1.5">
                                            <span>🏢</span> Unit-wise Pending Breakdown
                                        </p>
                                        <div class="overflow-hidden rounded-xl border-2 border-amber-300 dark:border-amber-800/40 bg-white dark:bg-gray-900 shadow-xs">
                                            <table class="w-full text-base">
                                                <thead class="bg-amber-100 dark:bg-amber-950/50 text-amber-950 dark:text-amber-200">
                                                    <tr>
                                                        <th class="px-4 py-3 text-left font-extrabold uppercase tracking-wider text-xs sm:text-sm">Unit #</th>
                                                        <th class="px-4 py-3 text-right font-extrabold uppercase tracking-wider text-xs sm:text-sm">Total Value</th>
                                                        <th class="px-4 py-3 text-right font-extrabold uppercase tracking-wider text-xs sm:text-sm">Received</th>
                                                        <th class="px-4 py-3 text-right font-extrabold uppercase tracking-wider text-xs sm:text-sm">Remaining Pending</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                                                    <template x-for="unit in landlordReceivables.units" :key="unit.unit_number">
                                                        <tr class="hover:bg-amber-50/50 dark:hover:bg-amber-950/10 transition-colors">
                                                            <td class="px-4 py-3.5 font-black text-brand-600 dark:text-brand-400 text-base sm:text-lg" x-text="unit.unit_number"></td>
                                                            <td class="px-4 py-3.5 text-right font-mono text-gray-700 dark:text-gray-300 font-bold text-base sm:text-lg" x-text="fmt(unit.total_amount)"></td>
                                                            <td class="px-4 py-3.5 text-right font-mono text-emerald-600 dark:text-emerald-400 font-bold text-base sm:text-lg" x-text="fmt(unit.received_amount)"></td>
                                                            <td class="px-4 py-3.5 text-right font-mono font-black text-lg sm:text-xl"
                                                                :class="unit.credit_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-400'"
                                                                x-text="fmt(unit.credit_amount)"></td>
                                                        </tr>
                                                    </template>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>

                    {{-- Source Payment Account (when receivedFromType === 'account') --}}
                    <div x-show="receivedFromType === 'account'" x-transition x-cloak class="sm:col-span-2">
                        <label class="{{ $label }}">Source Payment Account (Transfer From) <span class="text-red-500">*</span></label>
                        <select name="from_payment_account_id" x-model="fromAccountId" class="{{ $input }}" :required="receivedFromType === 'account'">
                            <option value="">Select Source Account...</option>
                            @foreach($paymentAccounts as $account)
                                <option value="{{ $account->id }}" {{ old('from_payment_account_id', $voucher->from_payment_account_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} (Balance: Rs. {{ number_format($account->current_balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="{{ $label }}">Voucher Date <span class="text-red-500">*</span></label>
                        <input type="text" id="voucher_date" name="date" value="{{ old('date', $voucher->date ? $voucher->date->format('Y-m-d') : '') }}" 
                               placeholder="YYYY-MM-DD" autocomplete="off"
                               class="{{ $input }}" required>
                        @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="{{ $label }}">Amount (Rs.) <span class="text-red-500">*</span></label>
                        <input type="text" 
                               x-model="displayAmount"
                               @input="formatAmount($event.target.value)"
                               placeholder="0.00" 
                               class="{{ $input }}" required>
                        <input type="hidden" name="amount" x-model="amount">
                        @error('amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Paid To (Payment Account) --}}
                    <div>
                        <label class="{{ $label }}">Deposit Payment Account <span class="text-red-500">*</span></label>
                        <select name="payment_account_id" x-model="paymentAccountId" class="{{ $input }}" required>
                            <option value="">Select Account</option>
                            @foreach($paymentAccounts as $account)
                                <option value="{{ $account->id }}" {{ old('payment_account_id', $voucher->payment_account_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ $account->bank_name ?? 'Cash' }}) — Type: {{ ucfirst($account->type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_account_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Reference/Cheque Number --}}
                    <div>
                        <label class="{{ $label }}">Reference / Instrument Number</label>
                        <input type="text" name="reference" value="{{ old('reference', $voucher->reference) }}" placeholder="e.g. Online Ref #, Cheque #01848" 
                               class="{{ $input }}">
                        @error('reference') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notes/Description --}}
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Description / Notes</label>
                        <textarea name="notes" placeholder="Enter voucher details, remarks, or breakdown here..." rows="3"
                                  class="{{ $input }} font-medium text-base">{{ old('notes', $voucher->notes) }}</textarea>
                        @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <a href="{{ route('general-receiving-vouchers.index') }}"
                       class="rounded-2xl border-2 border-gray-300 bg-white px-6 py-4 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-3 rounded-2xl bg-brand-600 px-8 py-4 text-lg font-extrabold text-white shadow-lg hover:bg-brand-700 transition-all cursor-pointer">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Changes
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
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
                    disableMobile: true
                });
            }
        });
    </script>
@endpush
