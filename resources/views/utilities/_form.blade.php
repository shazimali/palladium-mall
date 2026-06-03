{{-- ── Unit & Tenant ──────────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        Unit & Tenant
    </h4>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        {{-- Unit --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Unit <span class="text-red-500">*</span>
            </label>
            <select id="unit_id" name="unit_id"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('unit_id') ? 'border-red-400' : '' }}">
                <option value="">Select unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ old('unit_id', $reading->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->unit_number }}
                        {{ $unit->floor ? '— ' . $unit->floor->name : '' }}
                        {{ $unit->block ? '/ ' . $unit->block->name : '' }}
                    </option>
                @endforeach
            </select>
            @error('unit_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tenant (auto-filled) --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Tenant
            </label>
            <div id="tenant_display"
                class="w-full rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                {{ isset($reading) ? $reading->tenant->name : 'Auto-filled when unit is selected' }}
            </div>
            <input type="hidden" id="tenant_id" name="tenant_id"
                value="{{ old('tenant_id', $reading->tenant_id ?? '') }}">
        </div>

    </div>
</div>

{{-- ── Reading Details ─────────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        Reading Details
    </h4>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        {{-- Type --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Utility Type <span class="text-red-500">*</span>
            </label>
            <select id="type" name="type"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('type') ? 'border-red-400' : '' }}">
                <option value="">Select type</option>
                <option value="electricity" {{ old('type', $reading->type ?? '') === 'electricity' ? 'selected' : '' }}>⚡
                    Electricity</option>
                <option value="water" {{ old('type', $reading->type ?? '') === 'water' ? 'selected' : '' }}>💧 Water
                </option>
                <option value="gas" {{ old('type', $reading->type ?? '') === 'gas' ? 'selected' : '' }}>🔥 Gas</option>
            </select>
            @error('type')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Month --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Billing Month <span class="text-red-500">*</span>
            </label>
            <input type="text" id="month" name="month"
                value="{{ old('month', isset($reading) ? $reading->month->format('Y-m-d') : '') }}"
                placeholder="Select month" autocomplete="off"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('month') ? 'border-red-400' : '' }}">
            @error('month')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Previous Reading --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Previous Reading
            </label>
            <input type="number" id="previous_reading" name="previous_reading"
                value="{{ old('previous_reading', $reading->previous_reading ?? 0) }}" min="0" step="0.01"
                class="w-full rounded-lg border border-gray-300 bg-gray-100 px-4 py-2.5 text-sm text-gray-600 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 {{ $errors->has('previous_reading') ? 'border-red-400' : '' }}">
            <p class="mt-1 text-xs text-gray-400">Auto-filled from last saved reading.</p>
            @error('previous_reading')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Current Reading --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Current Reading <span class="text-red-500">*</span>
            </label>
            <input type="number" id="current_reading" name="current_reading"
                value="{{ old('current_reading', $reading->current_reading ?? '') }}" min="0" step="0.01"
                placeholder="Enter current meter reading"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('current_reading') ? 'border-red-400' : '' }}">
            @error('current_reading')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Units Consumed (read only) --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Units Consumed
            </label>
            <div id="units_consumed_display"
                class="w-full rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                {{ isset($reading) ? $reading->units_consumed : '0' }}
            </div>
            <p class="mt-1 text-xs text-gray-400">Current reading minus previous reading.</p>
        </div>

        {{-- Rate Per Unit --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Rate Per Unit (Rs.) <span class="text-red-500">*</span>
            </label>
            <input type="number" id="rate_per_unit" name="rate_per_unit"
                value="{{ old('rate_per_unit', $reading->rate_per_unit ?? '') }}" min="0" step="0.01"
                placeholder="e.g. 25"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('rate_per_unit') ? 'border-red-400' : '' }}">
            @error('rate_per_unit')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Calculated amount (read only) --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Calculated Amount (Rs.)
            </label>
            <div id="calculated_amount_display"
                class="w-full rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300">
                {{ isset($reading) ? number_format($reading->units_consumed * $reading->rate_per_unit, 2) : '0.00' }}
            </div>
            <p class="mt-1 text-xs text-gray-400">Units consumed × rate per unit.</p>
        </div>

        {{-- Bill Amount --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Actual Bill Amount (Rs.) <span class="text-red-500">*</span>
            </label>
            <input type="number" id="bill_amount" name="bill_amount"
                value="{{ old('bill_amount', $reading->bill_amount ?? '') }}" min="0" step="0.01"
                placeholder="Override if different from calculated"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('bill_amount') ? 'border-red-400' : '' }}">
            <p class="mt-1 text-xs text-gray-400">Auto-filled from calculated amount. Override if needed.</p>
            @error('bill_amount')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Due Date --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Due Date <span class="text-red-500">*</span>
            </label>
            <input type="text" id="due_date" name="due_date"
                value="{{ old('due_date', isset($reading) ? $reading->due_date->format('Y-m-d') : '') }}"
                placeholder="Select due date" autocomplete="off"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('due_date') ? 'border-red-400' : '' }}">
            @error('due_date')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>

{{-- ── Notes ──────────────────────────────────────────────────────── --}}
<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
    <input type="text" name="notes" value="{{ old('notes', $reading->notes ?? '') }}"
        placeholder="Any additional notes..."
        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
</div>