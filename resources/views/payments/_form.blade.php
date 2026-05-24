{{-- ── Tenant & Agreement ──────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        Tenant & Agreement
    </h4>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        {{-- Tenant --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Tenant <span class="text-red-500">*</span>
            </label>
            <select id="tenant_id" name="tenant_id"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('tenant_id') ? 'border-red-400' : '' }}">
                <option value="">Select tenant</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ old('tenant_id', $payment->tenant_id ?? '') == $tenant->id ? 'selected' : '' }}>
                        {{ $tenant->name }} — {{ $tenant->unit->unit_number ?? '' }}
                    </option>
                @endforeach
            </select>
            @error('tenant_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Unit (auto-filled) --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Unit</label>
            <div id="unit_display"
                class="w-full rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                {{ isset($payment) ? $payment->unit->unit_number : 'Auto-filled when tenant is selected' }}
            </div>
            <input type="hidden" id="unit_id" name="unit_id" value="{{ old('unit_id', $payment->unit_id ?? '') }}">
            <input type="hidden" id="agreement_id" name="agreement_id"
                value="{{ old('agreement_id', $payment->agreement_id ?? '') }}">
        </div>

    </div>
</div>

{{-- ── Payment Details ─────────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        Payment Details
    </h4>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        {{-- Type --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Type <span class="text-red-500">*</span>
            </label>
            <select id="type" name="type"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('type') ? 'border-red-400' : '' }}">
                <option value="">Select type</option>
                <option value="rent" {{ old('type', $payment->type ?? '') === 'rent' ? 'selected' : '' }}>Rent</option>
                <option value="maintenance" {{ old('type', $payment->type ?? '') === 'maintenance' ? 'selected' : '' }}>
                    Maintenance</option>
                <option value="fine" {{ old('type', $payment->type ?? '') === 'fine' ? 'selected' : '' }}>Fine</option>
                <option value="other" {{ old('type', $payment->type ?? '') === 'other' ? 'selected' : '' }}>Other</option>
            </select>
            @error('type')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Month --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Month <span class="text-red-500">*</span>
            </label>
            <input type="text" id="month" name="month"
                value="{{ old('month', isset($payment) ? $payment->month->format('Y-m-d') : '') }}"
                placeholder="Select month" autocomplete="off"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('month') ? 'border-red-400' : '' }}">
            @error('month')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Add after Monthly Rent field --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Maintenance Charge (Rs.)
            </label>
            <input type="number" name="maintenance_charge" min="0" step="0.01"
                value="{{ old('maintenance_charge', $agreement->maintenance_charge ?? 0) }}" placeholder="e.g. 2000"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
        </div>

        {{-- Amount --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Amount (Rs.) <span class="text-red-500">*</span>
            </label>
            <input type="number" id="amount" name="amount" value="{{ old('amount', $payment->amount ?? '') }}" min="0"
                step="0.01" placeholder="Auto-filled from agreement"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('amount') ? 'border-red-400' : '' }}">
            <p class="mt-1 text-xs text-gray-400">Auto-filled from active agreement. Override if needed.</p>
            @error('amount')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Due Date --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Due Date <span class="text-red-500">*</span>
            </label>
            <input type="text" id="due_date" name="due_date"
                value="{{ old('due_date', isset($payment) ? $payment->due_date->format('Y-m-d') : '') }}"
                placeholder="Select due date" autocomplete="off"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('due_date') ? 'border-red-400' : '' }}">
            @error('due_date')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

    </div>

    {{-- Notes --}}
    <div class="mt-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
        <input type="text" name="notes" value="{{ old('notes', $payment->notes ?? '') }}"
            placeholder="Any additional notes..."
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
    </div>
</div>