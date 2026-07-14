@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('payment-vouchers.index') }}" class="hover:text-brand-500">Payment Vouchers</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Edit Payment Voucher</span>
        </div>

        <x-common.component-card :title="'Edit Payment Voucher: ' . $voucher->voucher_no" desc="Modify payout details. Restricted to Super Administrators only.">
            <form action="{{ route('payment-vouchers.update', $voucher) }}" method="POST" class="space-y-6" 
                x-data="{ 
                    paidToType: '{{ old('paid_to_type', $voucher->paid_to_type) }}',
                    selectedBalance: null,
                    selectedAccountName: '',
                    amount: '{{ old('amount', $voucher->amount) }}',
                    originalAccountId: '{{ $voucher->payment_account_id }}',
                    originalAmount: '{{ $voucher->amount }}',
                    ownerPendingBalance: null,
                    ownerName: '',
                    fetchOwnerBalance(ownerId) {
                        if (!ownerId) { this.ownerPendingBalance = null; this.ownerName = ''; return; }
                        fetch('{{ route('ajax.owner-pending-balance') }}?owner_id=' + ownerId)
                            .then(r => r.json())
                            .then(d => {
                                // Add back current voucher amount so edit does not block
                                this.ownerPendingBalance = parseFloat(d.pending_balance) + parseFloat(this.originalAmount);
                                this.ownerName = d.owner_name;
                            });
                    },
                    get adjustedBalance() {
                        if (this.selectedBalance === null || this.selectedBalance === '') return 0;
                        let balance = parseFloat(this.selectedBalance);
                        let opt = document.querySelector('select[name=payment_account_id]').selectedOptions[0];
                        if (opt && opt.value === this.originalAccountId) {
                            balance += parseFloat(this.originalAmount);
                        }
                        return balance;
                    }
                }"
                @submit="
                    if (adjustedBalance !== null && parseFloat(amount) > adjustedBalance) {
                        if (!confirm('This account (' + selectedAccountName + ') has insufficient funds. \n\nAvailable balance (adjusted): Rs. ' + adjustedBalance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '\nPayment amount: Rs. ' + Number(amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '\n\nThe account balance will become negative. Do you want to proceed?')) {
                            $event.preventDefault();
                        }
                    }
                ">
                @csrf
                @method('PUT')

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
                    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
                @endphp

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    {{-- Paid To Type --}}
                    <div>
                        <label class="{{ $label }}">Paid To Type <span class="text-red-500">*</span></label>
                        <select name="paid_to_type" x-model="paidToType" class="{{ $input }} {{ $errors->has('paid_to_type') ? 'border-red-400' : '' }}" required>
                            <option value="owner" {{ old('paid_to_type', $voucher->paid_to_type) === 'owner' ? 'selected' : '' }}>Managing Owner (Partner)</option>
                            <option value="other" {{ old('paid_to_type', $voucher->paid_to_type) === 'other' ? 'selected' : '' }}>Other (Miscellaneous)</option>
                        </select>
                        @error('paid_to_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Owner Selection --}}
                    <div x-show="paidToType === 'owner'" x-transition
                         x-init="if ('{{ old('paid_to_type', $voucher->paid_to_type) }}' === 'owner' && '{{ old('owner_id', $voucher->owner_id) }}') { fetchOwnerBalance('{{ old('owner_id', $voucher->owner_id) }}') }">
                        <label class="{{ $label }}">Managing Owner / Partner <span class="text-red-500">*</span></label>
                        <select name="owner_id" class="{{ $input }} {{ $errors->has('owner_id') ? 'border-red-400' : '' }}" :required="paidToType === 'owner'"
                            @change="fetchOwnerBalance($event.target.value)">
                            <option value="">Select Owner</option>
                            @foreach($owners as $owner)
                                <option value="{{ $owner->id }}" {{ old('owner_id', $voucher->owner_id) == $owner->id ? 'selected' : '' }}>
                                    {{ $owner->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('owner_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                        {{-- Pending Balance Info Box --}}
                        <template x-if="ownerPendingBalance !== null">
                            <div class="mt-2 rounded-lg border p-2.5 text-xs font-semibold flex justify-between items-center"
                                 :class="ownerPendingBalance > 0
                                     ? 'border-orange-200 bg-orange-50 dark:border-orange-800 dark:bg-orange-900/10'
                                     : 'border-green-200 bg-green-50 dark:border-green-800 dark:bg-green-900/10'">
                                <span :class="ownerPendingBalance > 0 ? 'text-orange-700 dark:text-orange-400' : 'text-green-700 dark:text-green-400'">
                                    Available Balance (incl. this voucher):
                                </span>
                                <span class="font-bold text-sm"
                                      :class="ownerPendingBalance > 0 ? 'text-orange-700 dark:text-orange-400' : 'text-green-600 dark:text-green-400'"
                                      x-text="'Rs. ' + Number(ownerPendingBalance).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                                </span>
                            </div>
                        </template>
                    </div>

                    {{-- Other Payee Name (Searchable Party Head Dropdown) --}}
                    <div x-show="paidToType === 'other'" x-transition x-cloak
                         x-data="{
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
                            defaultDate="{{ old('date', $voucher->date ? $voucher->date->format('Y-m-d') : '') }}" 
                        />
                        @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="{{ $label }}">Amount (Rs.) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" x-model="amount" placeholder="0.00" 
                               class="{{ $input }} {{ $errors->has('amount') ? 'border-red-400' : '' }}" required>
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
                                <option value="{{ $account->id }}" data-balance="{{ $account->current_balance }}" data-name="{{ $account->name }}" {{ old('payment_account_id', $voucher->payment_account_id) == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ $account->bank_name ?? 'Cash' }}) — Type: {{ ucfirst($account->type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_account_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                        <template x-if="selectedBalance !== null && selectedBalance !== ''">
                            <div class="mt-2 text-xs font-semibold text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-850/30 p-2.5 rounded-lg border border-gray-200 dark:border-gray-700/60 flex justify-between items-center">
                                <span>Available Balance:</span>
                                <span :class="adjustedBalance >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="font-bold text-sm" x-text="'Rs. ' + adjustedBalance.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Reference/Cheque Number --}}
                    <div>
                        <label class="{{ $label }}">Reference / Cheque Number</label>
                        <input type="text" name="reference" value="{{ old('reference', $voucher->reference) }}" placeholder="e.g. Online Ref #, Cheque #01848" 
                               class="{{ $input }} {{ $errors->has('reference') ? 'border-red-400' : '' }}">
                        @error('reference') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Is Advance Payment Checkbox --}}
                    <div class="sm:col-span-2 flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex h-5 items-center">
                            <input id="is_advance" name="is_advance" type="checkbox" value="1" {{ old('is_advance', $voucher->is_advance) ? 'checked' : '' }}
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
