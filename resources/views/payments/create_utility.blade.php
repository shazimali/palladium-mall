@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Record Utility Reading" />

    <x-common.component-card title="Record Utility Reading" desc="Record meter readings and generate a utility bill payment record">
        <form action="{{ route('payments.utilities.store') }}" method="POST">
            @csrf

            <div class="space-y-6">
                {{-- Unit & Tenant details --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                        Unit & Tenant Info
                    </h4>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                        {{-- Unit --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Unit / Shop <span class="text-red-500">*</span>
                            </label>
                            <select id="unit_id" name="unit_id" required
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('unit_id') ? 'border-red-400' : '' }}">
                                <option value="">Select rented unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ old('unit_id') == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->unit_number }} ({{ ucfirst($unit->type) }})
                                    </option>
                                @endforeach
                            </select>
                            @error('unit_id')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Tenant Display --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Active Tenant</label>
                            <div id="tenant_display"
                                class="w-full rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                Select a unit first
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Meter Reading Details --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                        Reading Details
                    </h4>

                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        {{-- Utility Type --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Utility Type <span class="text-red-500">*</span>
                            </label>
                            <select id="type" name="type" required
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('type') ? 'border-red-400' : '' }}">
                                <option value="">Select type</option>
                                <option value="electricity" {{ old('type') === 'electricity' ? 'selected' : '' }}>Electricity ⚡</option>
                                <option value="water" {{ old('type') === 'water' ? 'selected' : '' }}>Water 💧</option>
                                <option value="gas" {{ old('type') === 'gas' ? 'selected' : '' }}>Gas 🔥</option>
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
                            <input type="text" id="month" name="month" required
                                value="{{ old('month') }}"
                                placeholder="Select month" autocomplete="off"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('month') ? 'border-red-400' : '' }}">
                            @error('month')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Due Date --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Due Date <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="due_date" name="due_date" required
                                value="{{ old('due_date') }}"
                                placeholder="Select due date" autocomplete="off"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('due_date') ? 'border-red-400' : '' }}">
                            @error('due_date')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-5 grid grid-cols-1 gap-5 sm:grid-cols-4">
                        {{-- Previous Reading --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Previous Reading <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="previous_reading" name="previous_reading" required
                                value="{{ old('previous_reading') }}" min="0" step="0.01" placeholder="0.00"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('previous_reading') ? 'border-red-400' : '' }}">
                            @error('previous_reading')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Current Reading --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Current Reading <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="current_reading" name="current_reading" required
                                value="{{ old('current_reading') }}" min="0" step="0.01" placeholder="0.00"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('current_reading') ? 'border-red-400' : '' }}">
                            @error('current_reading')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Units Consumed --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Units Consumed</label>
                            <div id="units_consumed_display"
                                class="w-full rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 font-semibold">
                                0.00
                            </div>
                        </div>

                        {{-- Rate per Unit --}}
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Rate per Unit (Rs.) <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="rate_per_unit" name="rate_per_unit" required
                                value="{{ old('rate_per_unit', 15.00) }}" min="0" step="0.01" placeholder="e.g. 18.50"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('rate_per_unit') ? 'border-red-400' : '' }}">
                            @error('rate_per_unit')
                                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Bill amount preview --}}
                    <div class="mt-5 rounded-lg border border-brand-100 bg-brand-50/50 p-4 dark:border-brand-900/30 dark:bg-brand-950/10">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Estimated Bill Amount:</span>
                            <span id="amount_preview" class="text-lg font-bold text-brand-600 dark:text-brand-400">Rs. 0.00</span>
                        </div>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                    <input type="text" name="notes" value="{{ old('notes') }}"
                        placeholder="Any comments, meter serial number details, etc..."
                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
            </div>

            <div class="mt-6 flex items-center gap-3">
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    Save & Generate Bill
                </button>
                <a href="{{ route('payments.index') }}"
                    class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                    Cancel
                </a>
            </div>
        </form>
    </x-common.component-card>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Month Picker
            flatpickr('#month', {
                dateFormat: 'Y-m-01',
                altInput: true,
                altFormat: 'F Y',
                disableMobile: true,
                plugins: [
                    new monthSelectPlugin({
                        shorthand: false,
                        dateFormat: 'Y-m-01',
                        altFormat: 'F Y',
                        theme: 'light',
                    })
                ],
            });

            // Due Date Picker
            flatpickr('#due_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            });

            const unitSelect = document.getElementById('unit_id');
            const typeSelect = document.getElementById('type');
            const previousInput = document.getElementById('previous_reading');
            const currentInput = document.getElementById('current_reading');
            const rateInput = document.getElementById('rate_per_unit');
            
            const tenantDisplay = document.getElementById('tenant_display');
            const unitsConsumedDisplay = document.getElementById('units_consumed_display');
            const amountPreview = document.getElementById('amount_preview');

            // Handle Unit selection -> fetch tenant
            unitSelect.addEventListener('change', function() {
                const unitId = this.value;
                if (!unitId) {
                    tenantDisplay.textContent = 'Select a unit first';
                    return;
                }

                fetch(`/ajax/tenant-by-unit?unit_id=${unitId}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.tenant) {
                            tenantDisplay.textContent = data.tenant.name;
                        } else {
                            tenantDisplay.textContent = 'No active tenant found';
                        }
                    });
                
                fetchPreviousReading();
            });

            // Handle Type selection -> fetch previous reading
            typeSelect.addEventListener('change', fetchPreviousReading);

            function fetchPreviousReading() {
                const unitId = unitSelect.value;
                const type = typeSelect.value;

                if (!unitId || !type) return;

                fetch(`/ajax/previous-reading?unit_id=${unitId}&type=${type}`)
                    .then(r => r.json())
                    .then(data => {
                        previousInput.value = data.previous_reading.toFixed(2);
                        calculateBill();
                    });
            }

            // Calculation logic
            function calculateBill() {
                const prev = parseFloat(previousInput.value) || 0;
                const curr = parseFloat(currentInput.value) || 0;
                const rate = parseFloat(rateInput.value) || 0;

                const consumed = Math.max(0, curr - prev);
                unitsConsumedDisplay.textContent = consumed.toFixed(2);

                const total = consumed * rate;
                amountPreview.textContent = 'Rs. ' + total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            previousInput.addEventListener('input', calculateBill);
            currentInput.addEventListener('input', calculateBill);
            rateInput.addEventListener('input', calculateBill);
            
            // Initial call if old values exist
            if (unitSelect.value) {
                unitSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endpush
