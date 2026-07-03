<div class="space-y-6">
    {{-- Account Info Card --}}
    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
            Account Details
        </h4>
 
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            {{-- Account Name --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Account Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" value="{{ old('name', $paymentAccount->name ?? '') }}" required
                    placeholder="e.g. HBL Main Branch or JazzCash Business"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600
                    {{ $errors->has('name') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
                @error('name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Account Type --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Account Type <span class="text-red-500">*</span>
                </label>
                <select name="type" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90
                    {{ $errors->has('type') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
                    <option value="" disabled {{ !isset($paymentAccount->type) && !old('type') ? 'selected' : '' }}>Select Type</option>
                    <option value="cash" {{ old('type', $paymentAccount->type ?? '') === 'cash' ? 'selected' : '' }}>Cash</option>
                    <option value="bank_transfer" {{ old('type', $paymentAccount->type ?? '') === 'bank_transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    <option value="cheque" {{ old('type', $paymentAccount->type ?? '') === 'cheque' ? 'selected' : '' }}>Cheque</option>
                    <option value="other" {{ old('type', $paymentAccount->type ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                </select>
                @error('type')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
 
            {{-- Bank Name --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Bank Name (If applicable)
                </label>
                <input type="text" name="bank_name" value="{{ old('bank_name', $paymentAccount->bank_name ?? '') }}"
                    placeholder="e.g. Habib Bank Limited, Mobilink Microfinance"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600
                    {{ $errors->has('bank_name') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
                @error('bank_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
 
            {{-- Account Number --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Account / Wallet Number
                </label>
                <input type="text" name="account_number" value="{{ old('account_number', $paymentAccount->account_number ?? '') }}"
                    placeholder="e.g. 1234567890123 or 03001234567"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600
                    {{ $errors->has('account_number') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
                @error('account_number')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
 
            {{-- Account Holder --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Account Holder Name
                </label>
                <input type="text" name="account_holder" value="{{ old('account_holder', $paymentAccount->account_holder ?? '') }}"
                    placeholder="e.g. Palladium Mall PVT LTD"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600
                    {{ $errors->has('account_holder') ? 'border-red-400 focus-within:border-red-400 focus-within:ring-red-400' : '' }}">
                @error('account_holder')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Opening Balance --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Opening Balance (Rs.)
                </label>
                <input type="number" step="0.01" min="0" name="opening_balance" value="{{ old('opening_balance', isset($paymentAccount) ? $paymentAccount->opening_balance : '0.00') }}"
                    placeholder="0.00"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600
                    {{ $errors->has('opening_balance') ? 'border-red-400 focus-within:border-red-400 focus-within:ring-red-400' : '' }}">
                @error('opening_balance')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
                @if(isset($paymentAccount))
                    <p class="mt-1.5 text-xs text-amber-600 dark:text-amber-400 font-semibold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        Warning: Changing the opening balance will affect all historical running balances.
                    </p>
                @endif
            </div>
        </div>
 
        {{-- Status Checkbox --}}
        <div class="mt-5 flex items-center">
            <label class="relative flex cursor-pointer items-center gap-2">
                <input type="checkbox" name="is_active" value="1"
                    {{ old('is_active', $paymentAccount->is_active ?? true) ? 'checked' : '' }}
                    class="rounded text-brand-500 focus:ring-brand-500 h-4 w-4 border-gray-300 dark:border-gray-700 dark:bg-gray-900">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">This account is active for recording new payments</span>
            </label>
        </div>
    </div>
 
    {{-- Notes Card --}}
    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes / Remarks</label>
        <textarea name="notes" rows="3" placeholder="Any additional payment details or instructions..."
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">{{ old('notes', $paymentAccount->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>
