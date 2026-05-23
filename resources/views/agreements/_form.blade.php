{{-- ── Tenant & Unit ──────────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        Tenant & Unit
    </h4>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        {{-- Tenant --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Tenant <span class="text-red-500">*</span>
            </label>
            <select name="tenant_id"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('tenant_id') ? 'border-red-400' : '' }}">
                <option value="">Select tenant</option>
                @foreach($tenants as $tenant)
                    <option value="{{ $tenant->id }}" {{ old('tenant_id', $agreement->tenant_id ?? $selectedTenantId ?? '') == $tenant->id ? 'selected' : '' }}>
                        {{ $tenant->name }} — {{ $tenant->cnic }}
                    </option>
                @endforeach
            </select>
            @error('tenant_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Unit --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Unit <span class="text-red-500">*</span>
            </label>
            <select name="unit_id"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('unit_id') ? 'border-red-400' : '' }}">
                <option value="">Select unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ old('unit_id', $agreement->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->unit_number }}
                        {{ $unit->floor ? '— ' . $unit->floor : '' }}
                        {{ $unit->block ? '/ ' . $unit->block : '' }}
                        ({{ ucfirst($unit->type) }})
                    </option>
                @endforeach
            </select>
            @error('unit_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

{{-- ── Agreement Dates & Rent ─────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        Dates & Financials
    </h4>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        {{-- Start Date --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Start Date <span class="text-red-500">*</span>
            </label>
            <input type="text" id="start_date" name="start_date"
                value="{{ old('start_date', isset($agreement) ? $agreement->start_date->format('Y-m-d') : '') }}"
                placeholder="Select start date" autocomplete="off"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('start_date') ? 'border-red-400' : '' }}">
            @error('start_date')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- End Date --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                End Date <span class="text-red-500">*</span>
            </label>
            <input type="text" id="end_date" name="end_date"
                value="{{ old('end_date', isset($agreement) ? $agreement->end_date->format('Y-m-d') : '') }}"
                placeholder="Select end date" autocomplete="off"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('end_date') ? 'border-red-400' : '' }}">
            @error('end_date')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Monthly Rent --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Monthly Rent (Rs.) <span class="text-red-500">*</span>
            </label>
            <input type="number" name="monthly_rent" min="0" step="0.01"
                value="{{ old('monthly_rent', $agreement->monthly_rent ?? '') }}" placeholder="e.g. 45000"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('monthly_rent') ? 'border-red-400' : '' }}">
            @error('monthly_rent')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Security Deposit --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Security Deposit (Rs.)
            </label>
            <input type="number" name="security_deposit" min="0" step="0.01"
                value="{{ old('security_deposit', $agreement->security_deposit ?? '') }}" placeholder="e.g. 90000"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
        </div>

        {{-- Grace Period --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Grace Period (days) <span class="text-red-500">*</span>
            </label>
            <input type="number" name="grace_period_days" min="0" max="30"
                value="{{ old('grace_period_days', $agreement->grace_period_days ?? 10) }}"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('grace_period_days') ? 'border-red-400' : '' }}">
            <p class="mt-1 text-xs text-gray-400">Days after due date before fine starts.</p>
            @error('grace_period_days')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Fine Per Day --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Fine Per Day (Rs.) <span class="text-red-500">*</span>
            </label>
            <input type="number" name="fine_per_day" min="0" step="0.01"
                value="{{ old('fine_per_day', $agreement->fine_per_day ?? 0) }}" placeholder="e.g. 500"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('fine_per_day') ? 'border-red-400' : '' }}">
            @error('fine_per_day')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Status --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Status <span class="text-red-500">*</span>
            </label>
            <select name="status"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('status') ? 'border-red-400' : '' }}">
                <option value="active" {{ old('status', $agreement->status ?? 'active') === 'active' ? 'selected' : '' }}>
                    Active</option>
                <option value="expired" {{ old('status', $agreement->status ?? 'active') === 'expired' ? 'selected' : '' }}>Expired</option>
                <option value="terminated" {{ old('status', $agreement->status ?? 'active') === 'terminated' ? 'selected' : '' }}>Terminated</option>
            </select>
            @error('status')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>

{{-- ── Terms & Document ────────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        Terms & Document
    </h4>

    {{-- Terms --}}
    <div class="mb-5">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Agreement Terms
        </label>
        <textarea name="terms" rows="4"
            placeholder="e.g. No subletting allowed. Tenant responsible for utility bills..."
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">{{ old('terms', $agreement->terms ?? '') }}</textarea>
        @error('terms')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Document Upload --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Signed Agreement Document
        </label>

        @if(isset($agreement) && $agreement->document)
            <div class="mb-2 flex items-center gap-3">
                <span class="text-xs text-gray-500">Current document uploaded</span>
                <a href="{{ $agreement->document_url }}" target="_blank" class="text-xs text-brand-500 hover:underline">View
                    Document</a>
            </div>
        @endif

        <input type="file" name="document" accept="image/jpeg,image/jpg,image/png,application/pdf"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-brand-600 hover:file:bg-brand-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('document') ? 'border-red-400' : '' }}">
        <p class="mt-1 text-xs text-gray-400">JPEG, PNG or PDF. Max 5MB.</p>
        @error('document')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>