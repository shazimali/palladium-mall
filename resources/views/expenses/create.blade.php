@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('expenses.index') }}" class="hover:text-brand-500">Expense Vouchers</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Record Expense Voucher</span>
        </div>

        <x-common.component-card title="Record Expense Voucher" desc="Log a new operational or business payment into the ledger book">
            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
                @submit.prevent="handleSubmit($event)"
                x-data="{
                    amount: '{{ old('amount') }}',
                    displayAmount: '',
                    selectedBalance: null,
                    selectedAccountName: '',
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
                    },
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
                        event.target.submit();
                    }
                }">
                @csrf

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
                    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
                @endphp

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    {{-- Expense Head (Category) --}}
                    <div>
                        <label class="{{ $label }}">Category (Expense Head) <span class="text-red-500">*</span></label>
                        <select name="expense_head_id" class="{{ $input }} {{ $errors->has('expense_head_id') ? 'border-red-400' : '' }}" required>
                            <option value="">Select Category</option>
                            @foreach($expenseHeads as $head)
                                <option value="{{ $head->id }}" {{ old('expense_head_id') == $head->id ? 'selected' : '' }}>
                                    {{ $head->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('expense_head_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
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

                    {{-- Date --}}
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

                    {{-- Payment Method --}}
                    <div>
                        <label class="{{ $label }}">Payment Method <span class="text-red-500">*</span></label>
                        <select name="payment_method" class="{{ $input }} {{ $errors->has('payment_method') ? 'border-red-400' : '' }}" required>
                            <option value="">Select Method</option>
                            <option value="cash" {{ old('payment_method', 'cash') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="bank" {{ old('payment_method') === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                            <option value="cheque" {{ old('payment_method') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                            <option value="other" {{ old('payment_method') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('payment_method') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Payment Account --}}
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
                                    {{ $account->name }} ({{ $account->bank_name ?? 'Cash' }}) — Current Balance: Rs. {{ number_format($account->current_balance, 2) }}
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

                    {{-- Reference --}}
                    <div>
                        <label class="{{ $label }}">Reference / Cheque #</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g. Voucher 1045, Cheque 4893" 
                               class="{{ $input }} {{ $errors->has('reference') ? 'border-red-400' : '' }}">
                        @error('reference') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notes/Description --}}
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Description / Notes</label>
                        <textarea name="notes" placeholder="Enter specific transaction details here..." rows="3"
                                  class="{{ $input }} {{ $errors->has('notes') ? 'border-red-400' : '' }}">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Attachment / Receipt --}}
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Receipt / Invoice Attachment (Max 5MB)</label>
                        <input type="file" name="receipt" accept="image/*,application/pdf"
                               class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300">
                        @error('receipt') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-3 border-t border-gray-100 pt-5 dark:border-gray-800">
                    <a href="{{ route('expenses.index') }}"
                       class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        Save Expense Voucher
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
