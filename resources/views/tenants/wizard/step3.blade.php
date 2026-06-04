@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">

    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants and Agreements</a>
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
        <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800 flex justify-between items-center">
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white/90">Step 3 — Unit & Agreement Terms</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Assign a unit and set the tenancy agreement terms.</p>
            </div>
            @if($agreement)
                <a href="{{ route('tenants.printStep', [$tenant, 3]) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print
                </a>
            @endif
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
                        <div class="relative">
                            <input type="text" name="start_date" id="start_date"
                                   value="{{ old('start_date', optional($a?->start_date)->format('Y-m-d') ?? '') }}"
                                   placeholder="Select start date"
                                   class="{{ $input }} pr-10 {{ $errors->has('start_date') ? 'border-red-400' : '' }}" readonly>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                        </div>
                        @error('start_date') <p class="{{ $error }}">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="{{ $label }}">End Date <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" name="end_date" id="end_date"
                                   value="{{ old('end_date', optional($a?->end_date)->format('Y-m-d') ?? '') }}"
                                   placeholder="Select end date"
                                   class="{{ $input }} pr-10 {{ $errors->has('end_date') ? 'border-red-400' : '' }}" readonly>
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </span>
                        </div>
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

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof flatpickr !== 'undefined') {
        const startEl = document.getElementById('start_date');
        const endEl = document.getElementById('end_date');

        if (startEl) {
            flatpickr(startEl, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                disableMobile: true,
                allowInput: false,
                onChange: function(selectedDates, dateStr, instance) {
                    if (endEl && endEl._flatpickr) {
                        endEl._flatpickr.set('minDate', dateStr);
                    }
                }
            });
        }

        if (endEl) {
            flatpickr(endEl, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd M Y',
                disableMobile: true,
                allowInput: false,
            });
        }
    }
});
</script>
@endpush
@endonce
