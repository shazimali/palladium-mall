@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">

    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants</a>
        <span>/</span>
        <span class="text-gray-800 dark:text-white/90">{{ $title }}</span>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @include('tenants.wizard._progress', ['currentStep' => $step, 'tenantId' => $tenant->id])

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white/90">Step 3 — Unit & Agreement Terms</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Assign a unit and set the tenancy agreement terms.</p>
        </div>

        <form method="POST" action="{{ route('tenants.saveStep', [$tenant, 3]) }}" class="px-6 py-6 space-y-6">
            @csrf

            @php
            $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
            $select = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
            $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
            $error = 'mt-1 text-xs text-red-500';
            $a = $agreement;
            @endphp

            {{-- ── Unit Assignment ───────────────────────────────────────── --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Unit Assignment</h4>
                <div>
                    <label class="{{ $label }}">Select Flat/Shop <span class="text-red-500">*</span></label>
                    <select name="unit_id" class="{{ $select }} {{ $errors->has('unit_id') ? 'border-red-400' : '' }}">
                        <option value="">— Select a vacant unit —</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ old('unit_id', $a->unit_id ?? $tenant->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                                {{ $unit->unit_number }}
                                {{ $unit->floor ? '— ' . $unit->floor->name : '' }}
                                {{ $unit->block ? '/ ' . $unit->block->name : '' }}
                                ({{ ucfirst($unit->type) }})
                                — {{ ucfirst($unit->status) }}
                            </option>
                        @endforeach
                    </select>
                    @error('unit_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- ── Agreement Terms ───────────────────────────────────────── --}}
            <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Agreement Terms</h4>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                    <div>
                        <label class="{{ $label }}">Start Date <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date', optional($a?->start_date)->format('Y-m-d') ?? '') }}"
                               class="{{ $input }} {{ $errors->has('start_date') ? 'border-red-400' : '' }}">
                        @error('start_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">End Date <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date', optional($a?->end_date)->format('Y-m-d') ?? '') }}"
                               class="{{ $input }} {{ $errors->has('end_date') ? 'border-red-400' : '' }}">
                        @error('end_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Monthly Rent (PKR) <span class="text-red-500">*</span></label>
                        <input type="number" name="monthly_rent" value="{{ old('monthly_rent', $a?->monthly_rent ?? '') }}"
                               placeholder="e.g. 25000" min="0" step="0.01" class="{{ $input }} {{ $errors->has('monthly_rent') ? 'border-red-400' : '' }}">
                        @error('monthly_rent') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Maintenance Charge (PKR)</label>
                        <input type="number" name="maintenance_charge" value="{{ old('maintenance_charge', $a?->maintenance_charge ?? '') }}"
                               placeholder="e.g. 2000" min="0" step="0.01" class="{{ $input }}">
                    </div>

                    <div>
                        <label class="{{ $label }}">Security Deposit (PKR) <span class="text-red-500">*</span></label>
                        <input type="number" name="security_deposit" value="{{ old('security_deposit', $a?->security_deposit ?? '') }}"
                               placeholder="e.g. 50000" min="0" step="0.01" class="{{ $input }} {{ $errors->has('security_deposit') ? 'border-red-400' : '' }}">
                        @error('security_deposit') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">Payment Due Day <span class="text-red-500">*</span></label>
                        <input type="number" name="payment_due_day" value="{{ old('payment_due_day', $a?->payment_due_day ?? 5) }}"
                               min="1" max="31" placeholder="5" class="{{ $input }}">
                        <p class="mt-1 text-xs text-gray-400">Day of month rent is due</p>
                    </div>

                    <div>
                        <label class="{{ $label }}">Grace Period (days)</label>
                        <input type="number" name="grace_period_days" value="{{ old('grace_period_days', $a?->grace_period_days ?? 10) }}"
                               min="0" placeholder="10" class="{{ $input }}">
                    </div>

                    <div>
                        <label class="{{ $label }}">Fine Per Day (PKR)</label>
                        <input type="number" name="fine_per_day" value="{{ old('fine_per_day', $a?->fine_per_day ?? '') }}"
                               placeholder="e.g. 500" min="0" step="0.01" class="{{ $input }}">
                    </div>

                    <div>
                        <label class="{{ $label }}">Notice Period (months)</label>
                        <input type="number" name="notice_period_months" value="{{ old('notice_period_months', $a?->notice_period_months ?? 1) }}"
                               min="0" placeholder="1" class="{{ $input }}">
                    </div>

                </div>
            </div>

            {{-- ── Special Terms ─────────────────────────────────────────── --}}
            <div>
                <label class="{{ $label }}">Special Terms & Conditions</label>
                <textarea name="terms" rows="4" placeholder="Any special terms or conditions..."
                    class="{{ $input }}">{{ old('terms', $a?->terms ?? '') }}</textarea>
            </div>

            {{-- Nav --}}
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('tenants.showStep', [$tenant, 2]) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                    Continue — Step 4
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
