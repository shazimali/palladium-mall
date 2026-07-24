@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-7xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm font-semibold text-gray-500 dark:text-gray-400">
            <a href="{{ route('expenses.index') }}" class="hover:text-brand-500">Expense Vouchers</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">Record Expense Voucher</span>
        </div>

        <x-common.component-card title="Record Expense Voucher" desc="Log a new operational or business payment into the ledger book">
            <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6"
                @submit.prevent="handleSubmit($event)"
                x-data="{
                    expenseHeadId: '{{ old('expense_head_id') }}',
                    paymentAccountId: '{{ old('payment_account_id') }}',
                    amount: '{{ old('amount') }}',
                    displayAmount: '',
                    selectedBalance: null,
                    selectedAccountName: '',
                    expenseHeads: [
                        @foreach($expenseHeads as $h)
                            { id: '{{ $h->id }}', name: '{{ addslashes($h->name) }}' },
                        @endforeach
                    ],
                    accounts: [
                        @foreach($paymentAccounts as $acc)
                            { id: '{{ $acc->id }}', name: '{{ addslashes($acc->name) }} ({{ $acc->bank_name ?? 'Cash' }})' },
                        @endforeach
                    ],
                    get selectedExpenseHeadName() {
                        let head = this.expenseHeads.find(h => h.id == this.expenseHeadId);
                        return head ? head.name : 'Select Expense Category';
                    },
                    get selectedPaymentAccountName() {
                        let acc = this.accounts.find(a => a.id == this.paymentAccountId);
                        return acc ? acc.name : '';
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
                                        confirmButton: 'inline-flex items-center justify-center rounded-xl bg-brand-600 px-6 py-3 text-base font-extrabold text-white shadow-md hover:bg-brand-700 transition-colors mx-2 cursor-pointer focus:outline-none'
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
                    $input = 'w-full rounded-2xl border-2 border-gray-300 bg-white px-5 py-3.5 text-lg sm:text-xl font-bold text-gray-900 shadow-xs focus:border-brand-500 focus:outline-none focus:ring-4 focus:ring-brand-500/20 dark:border-gray-700 dark:bg-gray-900 dark:text-white';
                    $label = 'mb-2 block text-xs sm:text-sm font-black uppercase tracking-wider text-gray-700 dark:text-gray-300';
                @endphp

                {{-- STICKY BIG HEADING BANNER --}}
                <div class="sticky mb-6 rounded-2xl border-2 border-amber-500 bg-white dark:bg-gray-900 p-5 shadow-xl backdrop-blur-md"
                    style="position: sticky; top: 72px; z-index: 990;">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-amber-600 text-white shadow-md text-3xl font-black">
                                🧾
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs font-extrabold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                                    Record New Expense Voucher
                                </p>
                                <div class="flex flex-wrap items-baseline gap-2 mt-0.5">
                                    <h2 class="text-2xl sm:text-3xl font-black tracking-tight text-gray-900 dark:text-white"
                                        x-text="selectedExpenseHeadName"></h2>
                                </div>
                                <div class="flex items-center gap-2 mt-1 text-xs font-bold text-gray-600 dark:text-gray-300">
                                    <span>Paid From Account:</span>
                                    <span class="text-brand-600 dark:text-brand-400 font-extrabold" x-text="selectedPaymentAccountName || 'Not Selected'"></span>
                                </div>
                            </div>
                        </div>

                        <div class="text-right">
                            <span class="text-xs font-extrabold uppercase tracking-wider text-gray-400 block">Voucher Amount</span>
                            <span class="text-2xl sm:text-3xl font-black font-mono text-amber-600 dark:text-amber-400"
                                  x-text="displayAmount ? 'Rs. ' + displayAmount : 'Rs. 0.00'"></span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                    {{-- Expense Head (Category) --}}
                    <div>
                        <label class="{{ $label }}">Category (Expense Head) <span class="text-red-500">*</span></label>
                        <select name="expense_head_id" x-model="expenseHeadId" class="{{ $input }}" required>
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
                               class="{{ $input }}" required>
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
                        <select name="payment_method" class="{{ $input }}" required>
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
                        <select name="payment_account_id" x-model="paymentAccountId" class="{{ $input }}" required
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
                            <div class="mt-2 text-sm font-bold text-gray-700 dark:text-gray-300 bg-amber-50 dark:bg-amber-950/20 p-3.5 rounded-xl border border-amber-200 dark:border-amber-800/40 flex justify-between items-center">
                                <span>Available Account Balance:</span>
                                <span :class="parseFloat(selectedBalance) >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" class="font-black text-lg font-mono" x-text="'Rs. ' + Number(selectedBalance).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})"></span>
                            </div>
                        </template>
                    </div>

                    {{-- Reference --}}
                    <div>
                        <label class="{{ $label }}">Reference / Cheque #</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g. Voucher 1045, Cheque 4893" 
                               class="{{ $input }}">
                        @error('reference') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notes/Description --}}
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Description / Notes</label>
                        <textarea name="notes" placeholder="Enter specific transaction details here..." rows="3"
                                  class="{{ $input }} font-medium text-base">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Attachment / Receipt --}}
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Receipt / Invoice Attachment (Max 5MB)</label>
                        <input type="file" name="receipt" accept="image/*,application/pdf"
                               class="w-full text-base text-gray-500 file:mr-4 file:py-3 file:px-5 file:rounded-xl file:border-0 file:text-base file:font-bold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300">
                        @error('receipt') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center justify-end gap-4 border-t border-gray-100 pt-6 dark:border-gray-800">
                    <a href="{{ route('expenses.index') }}"
                       class="rounded-2xl border-2 border-gray-300 bg-white px-6 py-4 text-base font-bold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center gap-3 rounded-2xl bg-brand-600 px-8 py-4 text-lg font-extrabold text-white shadow-lg hover:bg-brand-700 transition-all cursor-pointer">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Expense Voucher
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
