@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('general-receiving-vouchers.index') }}" class="hover:text-brand-500">General Receiving Vouchers</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Edit General Voucher</span>
        </div>

        <x-common.component-card :title="'Edit General Receiving Voucher: ' . $voucher->voucher_no" desc="Modify details of this general receipt voucher. Restricted to Super Administrators only.">
            <form action="{{ route('general-receiving-vouchers.update', $voucher) }}" method="POST" class="space-y-6"
                x-data="{
                    receivedFromType: '{{ old('received_from_type', $voucher->received_from_type ?? 'party') }}',
                    amount: '{{ old('amount', $voucher->amount) }}',
                    displayAmount: '',
                    landlordReceivables: null,
                    landlordLoading: false,
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
                @landlord-selected.window="fetchLandlordReceivables($event.detail.id)">
                @csrf
                @method('PUT')

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
                    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
                @endphp

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
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
                    <div x-show="receivedFromType === 'party'" x-transition x-cloak
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
                        <input type="hidden" name="party_id" :value="selectedId" required>

                        <div class="relative">
                            {{-- Trigger --}}
                            <div tabindex="0"
                                 @click="open = !open; if(open) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                 @keydown.space.prevent="open = !open; if(open) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                 @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.partySearchInput.focus()) }"
                                 @click.outside="open = false"
                                 class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 cursor-pointer flex justify-between items-center {{ $errors->has('party_id') ? 'border-red-400 focus-within:ring-red-400' : 'border-gray-300 focus-within:border-brand-500 focus-within:ring-brand-500 dark:border-gray-700' }}">
                                <span x-text="selectedLabel || 'Select Party Head'" :class="selectedLabel ? '' : 'text-gray-400 dark:text-gray-600'"></span>
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
                                $dispatch('landlord-selected', { id: l.id });
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

                        <div class="relative">
                            <div tabindex="0"
                                 @click="open = !open; if(open) { $nextTick(() => $refs.landlordSearchInputEdit.focus()) }"
                                 @keydown.space.prevent="open = !open; if(open) { $nextTick(() => $refs.landlordSearchInputEdit.focus()) }"
                                 @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.landlordSearchInputEdit.focus()) }"
                                 @click.outside="open = false"
                                 class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 cursor-pointer flex justify-between items-center border-gray-300 focus-within:border-brand-500 focus-within:ring-brand-500 dark:border-gray-700">
                                <span x-text="selectedLabel || 'Select Landlord'" :class="selectedLabel ? '' : 'text-gray-400 dark:text-gray-600'"></span>
                                <svg class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                                 class="absolute left-0 z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 py-2"
                                 style="display: none;">
                                <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700">
                                    <input x-ref="landlordSearchInputEdit"
                                           x-model="search"
                                           @keydown.arrow-down.prevent="moveHighlight(1)"
                                           @keydown.arrow-up.prevent="moveHighlight(-1)"
                                           @keydown.enter.prevent="selectHighlighted()"
                                           @keydown.escape.prevent="open = false; highlightedIndex = -1"
                                           type="text"
                                           placeholder="Type to search landlord..."
                                           class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                                </div>
                                <ul class="max-h-60 overflow-y-auto mt-1">
                                    <template x-if="filteredLandlords.length === 0">
                                        <li class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">No matching landlords found.</li>
                                    </template>
                                    <template x-for="(l, index) in filteredLandlords" :key="l.id">
                                        <li @click="selectLandlord(l)"
                                            @mouseenter="highlightedIndex = index"
                                            :class="{
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
                    </div>

                    {{-- Landlord Pending Receivables Panel --}}
                    <div class="sm:col-span-2" x-show="receivedFromType === 'landlord'" x-cloak>
                        <div x-show="landlordLoading" class="flex items-center gap-2 py-3 text-sm text-gray-500 dark:text-gray-400">
                            <svg class="h-4 w-4 animate-spin text-brand-500" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            Loading receivables...
                        </div>

                        <div x-show="!landlordLoading && !landlordReceivables"
                             class="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 py-4 px-5 text-xs text-gray-400 dark:text-gray-600 text-center">
                            Select a landlord above to view their pending receivables.
                        </div>

                        <div x-show="!landlordLoading && landlordReceivables" x-transition>
                            <div class="rounded-xl border border-amber-200 bg-amber-50/60 dark:border-amber-800/40 dark:bg-amber-950/10 p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <h5 class="text-xs font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400 flex items-center gap-1.5">
                                        <span>⚠️</span> Pending Receivables
                                    </h5>
                                    <a x-bind:href="landlordReceivables ? `{{ url('landlord-ledgers') }}?landlord_id=${landlordReceivables.landlord_id ?? ''}` : '#'"
                                       class="text-[10px] text-brand-500 hover:underline" target="_blank">View Ledger →</a>
                                </div>

                                <div class="grid grid-cols-3 gap-3 mb-4">
                                    <div class="rounded-lg bg-white dark:bg-gray-900 p-3 text-center shadow-theme-xs">
                                        <p class="text-[10px] uppercase font-bold text-gray-400">Total Owed</p>
                                        <p class="mt-0.5 text-sm font-bold font-mono text-gray-800 dark:text-white" x-text="landlordReceivables ? fmt(landlordReceivables.total_owed) : ''"></p>
                                    </div>
                                    <div class="rounded-lg bg-white dark:bg-gray-900 p-3 text-center shadow-theme-xs">
                                        <p class="text-[10px] uppercase font-bold text-gray-400">Total Received</p>
                                        <p class="mt-0.5 text-sm font-bold font-mono text-green-600" x-text="landlordReceivables ? fmt(landlordReceivables.total_received) : ''"></p>
                                    </div>
                                    <div class="rounded-lg bg-white dark:bg-gray-900 p-3 text-center shadow-theme-xs">
                                        <p class="text-[10px] uppercase font-bold text-gray-400">Pending Balance</p>
                                        <p class="mt-0.5 text-sm font-bold font-mono"
                                           :class="landlordReceivables && landlordReceivables.pending_balance > 0 ? 'text-red-500' : 'text-gray-500 dark:text-gray-400'"
                                           x-text="landlordReceivables ? fmt(landlordReceivables.pending_balance) : ''"></p>
                                    </div>
                                </div>

                                <template x-if="landlordReceivables && landlordReceivables.units && landlordReceivables.units.length > 0">
                                    <div>
                                        <p class="text-[10px] uppercase font-bold text-gray-500 dark:text-gray-400 mb-2">Unit-wise Breakdown</p>
                                        <div class="overflow-hidden rounded-lg border border-amber-200 dark:border-amber-800/30">
                                            <table class="w-full text-xs">
                                                <thead class="bg-amber-100/60 dark:bg-amber-900/20 text-gray-600 dark:text-gray-400">
                                                    <tr>
                                                        <th class="px-3 py-2 text-left font-semibold">Unit</th>
                                                        <th class="px-3 py-2 text-right font-semibold">Total Value</th>
                                                        <th class="px-3 py-2 text-right font-semibold">Received</th>
                                                        <th class="px-3 py-2 text-right font-semibold">Remaining</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-amber-100 dark:divide-amber-800/20 bg-white dark:bg-gray-900">
                                                    <template x-for="unit in landlordReceivables.units" :key="unit.unit_number">
                                                        <tr>
                                                            <td class="px-3 py-2 font-medium text-gray-800 dark:text-white/90" x-text="unit.unit_number"></td>
                                                            <td class="px-3 py-2 text-right font-mono text-gray-600 dark:text-gray-400" x-text="fmt(unit.total_amount)"></td>
                                                            <td class="px-3 py-2 text-right font-mono text-green-600" x-text="fmt(unit.received_amount)"></td>
                                                            <td class="px-3 py-2 text-right font-mono font-bold"
                                                                :class="unit.credit_amount > 0 ? 'text-red-500' : 'text-gray-400'"
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
                    <div x-show="receivedFromType === 'account'" x-transition x-cloak>
                        <label class="{{ $label }}">Source Payment Account (Transfer From) <span class="text-red-500">*</span></label>
                        <select name="from_payment_account_id" class="{{ $input }} {{ $errors->has('from_payment_account_id') ? 'border-red-400' : '' }}" :required="receivedFromType === 'account'">
                            <option value="">Select Source Account...</option>
                            @foreach($paymentAccounts as $account)
                                <option value="{{ $account->id }}" {{ old('from_payment_account_id', $voucher->from_payment_account_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} (Balance: Rs. {{ number_format($account->current_balance, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('from_payment_account_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="{{ $label }}">Voucher Date <span class="text-red-500">*</span></label>
                        <input type="text" id="voucher_date" name="date" value="{{ old('date', $voucher->date ? $voucher->date->format('Y-m-d') : '') }}" 
                               placeholder="YYYY-MM-DD" autocomplete="off"
                               class="{{ $input }} {{ $errors->has('date') ? 'border-red-400' : '' }}" required>
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

                    {{-- Paid To (Payment Account) --}}
                    <div>
                        <label class="{{ $label }}">Received In (Payment Account) <span class="text-red-500">*</span></label>
                        <select name="payment_account_id" class="{{ $input }} {{ $errors->has('payment_account_id') ? 'border-red-400' : '' }}" required>
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
                               class="{{ $input }} {{ $errors->has('reference') ? 'border-red-400' : '' }}">
                        @error('reference') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notes/Description --}}
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Description / Notes</label>
                        <textarea name="notes" placeholder="Enter voucher details, remarks, or breakdown here..." rows="3"
                                  class="{{ $input }} {{ $errors->has('notes') ? 'border-red-400' : '' }}">{{ old('notes', $voucher->notes) }}</textarea>
                        @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('general-receiving-vouchers.index') }}"
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
