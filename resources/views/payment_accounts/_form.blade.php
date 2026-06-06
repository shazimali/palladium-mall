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
                    {{ $errors->has('account_holder') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
                @error('account_holder')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
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
