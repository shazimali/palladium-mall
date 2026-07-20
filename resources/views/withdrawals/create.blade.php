@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Record Owner Withdrawal" />

    <div class="mx-auto max-w-3xl">
        <x-common.component-card title="Withdrawal Details" desc="Record a new profit-share withdrawal from a managing owner's dues">
            
            <form action="{{ route('withdrawals.store') }}" method="POST" class="space-y-6"
                @submit.prevent="handleSubmit($event)"
                x-data="{ 
                    selectedBalance: null,
                    selectedAccountName: '',
                    amount: '{{ old('amount') }}',
                    displayAmount: '',
                    ownerId: '{{ old('owner_id', request('owner_id')) }}',
                    ownerPendingBalance: null,
                    ownerName: '',
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

                        if (this.ownerId !== '' && this.ownerPendingBalance !== null && this.amount !== '') {
                            let amt = parseFloat(this.amount);
                            let bal = parseFloat(this.ownerPendingBalance);
                            if (amt > bal) {
                                Swal.fire({
                                    title: 'Limit Exceeded',
                                    text: 'Withdrawal amount exceeds ' + this.ownerName + '\'s available remaining dues of Rs. ' + bal.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '.',
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
                    fetchOwnerBalance(val) {
                        this.ownerId = val;
                        if (!val) { this.ownerPendingBalance = null; this.ownerName = ''; return; }
                        fetch('{{ route('ajax.owner-pending-balance') }}?owner_id=' + val)
                            .then(r => r.json())
                            .then(d => { this.ownerPendingBalance = d.pending_balance; this.ownerName = d.owner_name; });
                    },
                    updateAccount(event) {
                        const opt = event.target.selectedOptions[0];
                        if (opt && opt.value) {
                            this.selectedBalance = opt.dataset.balance;
                            this.selectedAccountName = opt.dataset.name;
                        } else {
                            this.selectedBalance = null;
                            this.selectedAccountName = '';
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
                        if (this.ownerId) {
                            this.fetchOwnerBalance(this.ownerId);
                        }
                        const sel = document.querySelector('select[name=payment_account_id]');
                        if (sel) {
                            this.updateAccount({ target: sel });
                        }
                    }
                }">
                @csrf

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
                    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
                @endphp

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    {{-- Owner Selection --}}
                    <div>
                        <label class="{{ $label }}">Managing Owner / Partner <span class="text-red-500">*</span></label>
                        <select name="owner_id" class="{{ $input }} {{ $errors->has('owner_id') ? 'border-red-400' : '' }}" required
                            @change="fetchOwnerBalance($event.target.value)">
                            <option value="">Select Owner</option>
                            @foreach($owners as $owner)
                                <option value="{{ $owner->id }}" {{ old('owner_id', request('owner_id')) == $owner->id ? 'selected' : '' }}>
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
                                    Remaining Dues (Available):
                                </span>
                                <span class="font-bold text-sm"
                                      :class="ownerPendingBalance > 0 ? 'text-orange-700 dark:text-orange-400' : 'text-green-600 dark:text-green-400'"
                                      x-text="'Rs. ' + Number(ownerPendingBalance).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                                </span>
                            </div>
                        </template>
                    </div>

                    {{-- Withdrawal Date --}}
                    <div>
                        <label class="{{ $label }}">Date <span class="text-red-500">*</span></label>
                        <x-form.date-picker 
                            id="date" 
                            name="date"
                            placeholder="Select Date" 
                            defaultDate="{{ old('date', date('Y-m-d')) }}" 
                        />
                        @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Paid From Payment Account --}}
                    <div>
                        <label class="{{ $label }}">Paid From (Payment Account) <span class="text-red-500">*</span></label>
                        <select name="payment_account_id" class="{{ $input }} {{ $errors->has('payment_account_id') ? 'border-red-400' : '' }}" required
                            @change="updateAccount($event)">
                            <option value="">Select Account</option>
                            @foreach($paymentAccounts as $account)
                                @php
                                    $bal = (float)$account->current_balance;
                                @endphp
                                <option value="{{ $account->id }}" data-balance="{{ $bal }}" data-name="{{ $account->name }}"
                                    {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} (Rs. {{ number_format($bal, 2) }})
                                </option>
                            @endforeach
                        </select>
                        @error('payment_account_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror

                        {{-- Balance Info Box --}}
                        <template x-if="selectedBalance !== null">
                            <div class="mt-2 rounded-lg border p-2.5 text-xs font-semibold flex justify-between items-center bg-gray-50 border-gray-200 dark:bg-gray-800/40 dark:border-gray-700">
                                <span class="text-gray-600 dark:text-gray-400" x-text="selectedAccountName + ' Balance:'"></span>
                                <span class="font-bold text-sm text-gray-800 dark:text-white"
                                      x-text="'Rs. ' + Number(selectedBalance).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})">
                                </span>
                            </div>
                        </template>
                    </div>

                    {{-- Withdrawal Amount --}}
                    <div>
                        <label class="{{ $label }}">Amount (Rs.) <span class="text-red-500">*</span></label>
                        <input type="text" x-model="displayAmount" @input="formatAmount($event.target.value)" required
                            placeholder="Enter amount"
                            class="{{ $input }} {{ $errors->has('amount') ? 'border-red-400' : '' }}" />
                        <input type="hidden" name="amount" x-model="amount" />
                        @error('amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Reference --}}
                    <div>
                        <label class="{{ $label }}">Ref / Cheque #</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g. Chq-88219"
                            class="{{ $input }} {{ $errors->has('reference') ? 'border-red-400' : '' }}" />
                        @error('reference') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Notes --}}
                <div>
                    <label class="{{ $label }}">Notes / Details</label>
                    <textarea name="notes" rows="3" placeholder="Enter transaction details..."
                        class="{{ $input }} {{ $errors->has('notes') ? 'border-red-400' : '' }}">{{ old('notes') }}</textarea>
                    @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center justify-end gap-3 border-t border-gray-150 pt-5 dark:border-gray-800">
                    <a href="{{ route('withdrawals.index') }}"
                        class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                        class="rounded-lg bg-green-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-700 transition-colors">
                        Save Withdrawal
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
