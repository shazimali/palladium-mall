@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add Payment Record" />

    <div x-data="{
                        mode: '{{ old('payment_mode', 'tenant') }}',
                        selfUnitId: '{{ old('unit_id', '') }}',
                        selfAmount: '{{ old('amount', '') }}',
                        selfLandlordName: '',
                        selfUnits: {{ $selfUnits->map(fn($u) => [
        'id' => $u->id,
        'label' => $u->unit_number,
        'charge' => $u->default_maintenance_charge ?? 0,
        'landlord_name' => $u->landlord?->name ?? '—',
    ])->values()->toJson() }},
                        extraUnitId: '{{ old('unit_id', '') }}',
                        init() {
                            if (this.selfUnitId) {
                                const u = this.selfUnits.find(x => x.id == this.selfUnitId);
                                if (u) {
                                    this.selfLandlordName = u.landlord_name;
                                }
                            }
                        },
                        selectSelfUnit(id) {
                            this.selfUnitId = id;
                            const u = this.selfUnits.find(x => x.id == id);
                            if (u) {
                                if (u.charge) this.selfAmount = u.charge;
                                this.selfLandlordName = u.landlord_name;
                            } else {
                                this.selfLandlordName = '';
                            }
                        }
                    }">

        {{-- ── Mode Tabs — Segmented Control ─────────────────────────── --}}
        <div class="mb-6">
            <div
                class="inline-flex rounded-xl border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-gray-800">

                {{-- Tenant Payment --}}
                <button type="button" @click="mode = 'tenant'" :class="mode === 'tenant'
                                        ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-900 dark:text-white'
                                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="relative inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold transition-all duration-200 focus:outline-none">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Tenant Payment
                </button>

                {{-- External Owner --}}
                <button type="button" @click="mode = 'self'" :class="mode === 'self'
                                        ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-900 dark:text-white'
                                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="relative inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold transition-all duration-200 focus:outline-none">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Other-Owned Flat/Shop
                </button>

                {{-- Extra Payment --}}
                <button type="button" @click="mode = 'extra'" :class="mode === 'extra'
                                        ? 'bg-white text-gray-800 shadow-sm dark:bg-gray-900 dark:text-white'
                                        : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200'"
                    class="relative inline-flex items-center gap-2 rounded-lg px-5 py-2.5 text-sm font-semibold transition-all duration-200 focus:outline-none">
                    <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                        stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Extra Payment
                </button>

            </div>
        </div>

        {{-- ── TENANT PAYMENT FORM ─────────────────────────────────────── --}}
        <div x-show="mode === 'tenant'" x-cloak>
            <x-common.component-card title="Tenant Payment"
                desc="Create a rent or maintenance payment linked to a tenant's agreement">
                <form action="{{ route('payments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="payment_mode" value="tenant">
                    @include('payments._form')

                    <div class="mt-5 flex items-center gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Record
                        </button>
                        <a href="{{ route('payments.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </x-common.component-card>
        </div>

        {{-- ── SELF-UNIT MAINTENANCE FORM ───────────────────────────────── --}}
        <div x-show="mode === 'self'" x-cloak>
            <x-common.component-card title="Other-Owned Flat/Shop — Maintenance Payment"
                desc="Generate a maintenance-only payment for an other-owned flat/shop. No tenant or agreement required.">
                <form action="{{ route('payments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="payment_mode" value="self">

                    {{-- Flat/Shop Selection --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                            Flat/Shop Selection
                        </h4>

                        @if($selfUnits->isEmpty())
                            <div
                                class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-400">
                                No other-owned flats/shops found. Go to a Landlord and toggle <strong>Other-Owned</strong> on a
                                flat/shop first.
                            </div>
                        @else
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                                {{-- Flat/Shop dropdown --}}
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Select Flat/Shop <span class="text-red-500">*</span>
                                    </label>
                                    <select name="unit_id" id="self_unit_id" @change="selectSelfUnit($event.target.value)"
                                        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('unit_id') ? 'border-red-400' : '' }}">
                                        <option value="">Select Flat/Shop</option>
                                        @foreach($selfUnits as $su)
                                            <option value="{{ $su->id }}" data-charge="{{ $su->default_maintenance_charge ?? 0 }}"
                                                {{ old('unit_id') == $su->id ? 'selected' : '' }}>
                                                {{ $su->unit_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('unit_id')
                                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Landlord name --}}
                                <div>
                                    <label
                                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Landlord</label>
                                    <div
                                        class="w-full rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                        <span x-text="selfLandlordName || 'Select a flat/shop to see landlord'"></span>
                                    </div>
                                </div>

                                {{-- Fixed charge info --}}
                                <div>
                                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Fixed Monthly Charge
                                    </label>
                                    <div x-show="selfUnitId && selfAmount"
                                        class="flex items-center gap-2 rounded-lg border border-green-200 bg-green-50 px-4 py-2.5 dark:border-green-800/40 dark:bg-green-900/20">
                                        <svg class="h-4 w-4 text-green-500" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <p class="text-sm font-semibold text-green-700 dark:text-green-300">
                                            Rs. <span x-text="Number(selfAmount).toLocaleString('en-PK')"></span>
                                            <span class="text-xs font-normal text-green-500">/ month</span>
                                        </p>
                                    </div>
                                    <div x-show="!selfUnitId || !selfAmount"
                                        class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-400 dark:border-gray-700 dark:bg-gray-800">
                                        Select a flat/shop to see the charge
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Payment Details --}}
                    <div
                        class="mt-5 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                            Maintenance Payment Details
                        </h4>

                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                            {{-- Type — locked --}}
                            <div>
                                <label
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Type</label>
                                <div
                                    class="flex items-center gap-2 rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm font-medium text-gray-600 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Maintenance
                                </div>
                                <input type="hidden" name="type" value="maintenance">
                            </div>

                            {{-- Month --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Month <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="self_month" name="month"
                                    value="{{ old('month', now()->format('Y-m-01')) }}" placeholder="Select month"
                                    autocomplete="off"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('month') ? 'border-red-400' : '' }}">
                                @error('month')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Amount --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Amount (Rs.) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="self_amount" name="amount" x-model="selfAmount" min="0" step="0.01"
                                    placeholder="Auto-filled from unit charge"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('amount') ? 'border-red-400' : '' }}">
                                <p class="mt-1 text-xs text-gray-400">Auto-filled from the unit's fixed charge. Override if
                                    needed.</p>
                                @error('amount')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Due Date --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Due Date <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="self_due_date" name="due_date"
                                    value="{{ old('due_date', now()->format('Y-m-10')) }}" placeholder="Select due date"
                                    autocomplete="off"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('due_date') ? 'border-red-400' : '' }}">
                                @error('due_date')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>

                        {{-- Notes --}}
                        <div class="mt-5">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
                            <input type="text" name="notes" value="{{ old('notes') }}" placeholder="Any additional notes..."
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                    </div>

                    <div class="mt-5 flex items-center gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Create Maintenance Payment
                        </button>
                        <a href="{{ route('payments.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </x-common.component-card>
        </div>

        {{-- ── EXTRA PAYMENT FORM ─────────────────────────────────────────── --}}
        <div x-show="mode === 'extra'" x-cloak>
            <x-common.component-card title="Extra Payment"
                desc="Collect any ad-hoc charge against any unit. No tenant or agreement required. Appears in the EXTRA column of the Monthly Matrix report.">
                <form action="{{ route('payments.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="payment_mode" value="extra">
                    <input type="hidden" name="type" value="extra_payment">

                    {{-- Info banner --}}
                    <div
                        class="mb-5 flex items-start gap-3 rounded-xl border border-teal-200 bg-teal-50 px-4 py-3 dark:border-teal-800/50 dark:bg-teal-900/20">
                        <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-teal-500" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-sm text-teal-700 dark:text-teal-300">
                            Extra payments are free-form charges. Select any unit, enter the amount, and optionally add a
                            note to identify the charge. Multiple extra payments can be recorded for the same unit and
                            month.
                        </p>
                    </div>

                    {{-- Unit Selection --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                            Unit Selection
                        </h4>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                            <div>
                                <label for="extra_unit_id"
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Select Flat/Shop <span class="text-red-500">*</span>
                                </label>
                                <select name="unit_id" id="extra_unit_id" x-model="extraUnitId"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('unit_id') && old('payment_mode') === 'extra' ? 'border-red-400' : '' }}">
                                    <option value="">Select flat/shop...</option>
                                    @foreach($allUnits as $u)
                                        <option value="{{ $u->id }}" {{ old('unit_id') == $u->id && old('payment_mode') === 'extra' ? 'selected' : '' }}>
                                            {{ $u->unit_number }}
                                        </option>
                                    @endforeach
                                </select>
                                @if($errors->has('unit_id') && old('payment_mode') === 'extra')
                                    <p class="mt-1 text-xs text-red-500">{{ $errors->first('unit_id') }}</p>
                                @endif
                            </div>

                            {{-- Type badge -- locked --}}
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Payment
                                    Type</label>
                                <div
                                    class="flex items-center gap-2 rounded-lg border border-teal-200 bg-teal-50 px-4 py-2.5 text-sm font-medium text-teal-700 dark:border-teal-800/40 dark:bg-teal-900/20 dark:text-teal-300">
                                    <span>💰</span>
                                    Extra Payment
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Payment Details --}}
                    <div
                        class="mt-5 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                            Payment Details
                        </h4>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

                            {{-- Month --}}
                            <div>
                                <label for="extra_month"
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Month <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="extra_month" name="month"
                                    value="{{ old('month', now()->format('Y-m-01')) }}" placeholder="Select month"
                                    autocomplete="off"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('month') && old('payment_mode') === 'extra' ? 'border-red-400' : '' }}">
                                @if($errors->has('month') && old('payment_mode') === 'extra')
                                    <p class="mt-1 text-xs text-red-500">{{ $errors->first('month') }}</p>
                                @endif
                            </div>

                            {{-- Amount --}}
                            <div>
                                <label for="extra_amount"
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Amount (Rs.) <span class="text-red-500">*</span>
                                </label>
                                <input type="number" id="extra_amount" name="amount" value="{{ old('amount') }}" min="0"
                                    step="0.01" placeholder="e.g. 5000"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('amount') && old('payment_mode') === 'extra' ? 'border-red-400' : '' }}">
                                @if($errors->has('amount') && old('payment_mode') === 'extra')
                                    <p class="mt-1 text-xs text-red-500">{{ $errors->first('amount') }}</p>
                                @endif
                            </div>

                            {{-- Due Date --}}
                            <div>
                                <label for="extra_due_date"
                                    class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Due Date <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="extra_due_date" name="due_date"
                                    value="{{ old('due_date', now()->format('Y-m-10')) }}" placeholder="Select due date"
                                    autocomplete="off"
                                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('due_date') && old('payment_mode') === 'extra' ? 'border-red-400' : '' }}">
                                @if($errors->has('due_date') && old('payment_mode') === 'extra')
                                    <p class="mt-1 text-xs text-red-500">{{ $errors->first('due_date') }}</p>
                                @endif
                            </div>

                        </div>

                        {{-- Notes --}}
                        <div class="mt-5">
                            <label for="extra_notes"
                                class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Description / Notes
                                <span class="ml-1 text-xs font-normal text-gray-400">(optional — helps identify the
                                    charge)</span>
                            </label>
                            <input type="text" id="extra_notes" name="notes" value="{{ old('notes') }}"
                                placeholder="e.g. Generator fee, Parking charge, Repair cost..."
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                        </div>
                    </div>

                    <div class="mt-5 flex items-center gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Extra Payment
                        </button>
                        <a href="{{ route('payments.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </x-common.component-card>
        </div>

    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // ── Tenant form pickers ──────────────────────────────────────────
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

            flatpickr('#due_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            });

            // ── Self-unit form pickers ───────────────────────────────────────
            flatpickr('#self_month', {
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

            flatpickr('#self_due_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            });

            // ── Extra Payment form pickers ───────────────────────────────────
            flatpickr('#extra_month', {
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

            flatpickr('#extra_due_date', {
                dateFormat: 'Y-m-d',
                allowInput: true,
                disableMobile: true,
            });

            // ── Tenant auto-fill ─────────────────────────────────────────────
            const tenantSelect = document.getElementById('tenant_id');
            const typeSelect = document.getElementById('type');

            if (tenantSelect) {
                tenantSelect.addEventListener('change', function () {
                    const tenantId = this.value;
                    if (!tenantId) {
                        document.getElementById('unit_display').textContent = 'Auto-filled when tenant is selected';
                        document.getElementById('landlord_display').textContent = 'Auto-filled when tenant is selected';
                        document.getElementById('unit_id').value = '';
                        document.getElementById('agreement_id').value = '';
                        document.getElementById('amount').value = '';
                        return;
                    }

                    fetch(`/ajax/agreement-by-tenant?tenant_id=${tenantId}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.agreement) {
                                document.getElementById('unit_display').textContent = data.agreement.unit_number;
                                document.getElementById('landlord_display').textContent = data.agreement.landlord_name;
                                document.getElementById('unit_id').value = data.agreement.unit_id;
                                document.getElementById('agreement_id').value = data.agreement.id;
                                fillAmount(data.agreement);
                            } else {
                                document.getElementById('unit_display').textContent = 'No active agreement found';
                                document.getElementById('landlord_display').textContent = 'No active agreement found';
                                document.getElementById('unit_id').value = '';
                                document.getElementById('agreement_id').value = '';
                            }
                        });
                });
            }

            if (typeSelect) {
                typeSelect.addEventListener('change', function () {
                    const tenantId = document.getElementById('tenant_id')?.value;
                    if (!tenantId) return;

                    fetch(`/ajax/agreement-by-tenant?tenant_id=${tenantId}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.agreement) fillAmount(data.agreement);
                        });
                });
            }

            function fillAmount(agreement) {
                const type = document.getElementById('type').value;
                const amountInput = document.getElementById('amount');

                if (type === 'rent') {
                    amountInput.value = agreement.monthly_rent;
                } else if (type === 'maintenance') {
                    amountInput.value = agreement.maintenance_charge;
                } else if (type === 'security_deposit') {
                    amountInput.value = agreement.security_deposit;
                } else {
                    amountInput.value = '';
                }
            }
        });
    </script>
@endpush