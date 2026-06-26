@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-3xl px-4 py-6">
        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('payment-vouchers.index') }}" class="hover:text-brand-500">Payment Vouchers</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">New Payment Voucher</span>
        </div>

        <x-common.component-card title="Record Payment Voucher" desc="Record a payout from mall accounts to a managing owner/partner or a miscellaneous recipient">
            <form action="{{ route('payment-vouchers.store') }}" method="POST" class="space-y-6" x-data="{ paidToType: '{{ old('paid_to_type', 'owner') }}' }">
                @csrf

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
                    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
                @endphp

                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    {{-- Paid To Type --}}
                    <div>
                        <label class="{{ $label }}">Paid To Type <span class="text-red-500">*</span></label>
                        <select name="paid_to_type" x-model="paidToType" class="{{ $input }} {{ $errors->has('paid_to_type') ? 'border-red-400' : '' }}" required>
                            <option value="owner" {{ old('paid_to_type', 'owner') === 'owner' ? 'selected' : '' }}>Managing Owner (Partner)</option>
                            <option value="other" {{ old('paid_to_type') === 'other' ? 'selected' : '' }}>Other (Miscellaneous)</option>
                        </select>
                        @error('paid_to_type') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Owner Selection --}}
                    <div x-show="paidToType === 'owner'" x-transition>
                        <label class="{{ $label }}">Managing Owner / Partner <span class="text-red-500">*</span></label>
                        <select name="owner_id" class="{{ $input }} {{ $errors->has('owner_id') ? 'border-red-400' : '' }}" :required="paidToType === 'owner'">
                            <option value="">Select Owner</option>
                            @foreach($owners as $owner)
                                <option value="{{ $owner->id }}" {{ old('owner_id') == $owner->id ? 'selected' : '' }}>
                                    {{ $owner->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('owner_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Other Payee Name --}}
                    <div x-show="paidToType === 'other'" x-transition x-cloak>
                        <label class="{{ $label }}">Payee Name <span class="text-red-500">*</span></label>
                        <input type="text" name="other_name" value="{{ old('other_name') }}" placeholder="e.g. Contractor Name, Vendor" 
                               class="{{ $input }} {{ $errors->has('other_name') ? 'border-red-400' : '' }}" :required="paidToType === 'other'">
                        @error('other_name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Date --}}
                    <div>
                        <label class="{{ $label }}">Voucher Date <span class="text-red-500">*</span></label>
                        <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" 
                               class="{{ $input }} {{ $errors->has('date') ? 'border-red-400' : '' }}" required>
                        @error('date') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Amount --}}
                    <div>
                        <label class="{{ $label }}">Amount (Rs.) <span class="text-red-500">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="amount" value="{{ old('amount') }}" placeholder="0.00" 
                               class="{{ $input }} {{ $errors->has('amount') ? 'border-red-400' : '' }}" required>
                        @error('amount') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Paid From (Payment Account) --}}
                    <div>
                        <label class="{{ $label }}">Paid From (Payment Account) <span class="text-red-500">*</span></label>
                        <select name="payment_account_id" class="{{ $input }} {{ $errors->has('payment_account_id') ? 'border-red-400' : '' }}" required>
                            <option value="">Select Account</option>
                            @foreach($paymentAccounts as $account)
                                <option value="{{ $account->id }}" {{ old('payment_account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->name }} ({{ $account->bank_name ?? 'Cash' }}) — Type: {{ ucfirst($account->type) }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_account_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Reference/Cheque Number --}}
                    <div>
                        <label class="{{ $label }}">Reference / Cheque Number</label>
                        <input type="text" name="reference" value="{{ old('reference') }}" placeholder="e.g. Online Ref #, Cheque #01848" 
                               class="{{ $input }} {{ $errors->has('reference') ? 'border-red-400' : '' }}">
                        @error('reference') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>

                    {{-- Is Advance Payment Checkbox --}}
                    <div class="sm:col-span-2 flex items-start gap-3 rounded-lg border border-gray-200 bg-gray-50/50 p-4 dark:border-gray-800 dark:bg-white/[0.02]">
                        <div class="flex h-5 items-center">
                            <input id="is_advance" name="is_advance" type="checkbox" value="1" {{ old('is_advance') ? 'checked' : '' }}
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
                                  class="{{ $input }} {{ $errors->has('notes') ? 'border-red-400' : '' }}">{{ old('notes') }}</textarea>
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
                        Save Payment Voucher
                    </button>
                </div>
            </form>
        </x-common.component-card>
    </div>
@endsection
