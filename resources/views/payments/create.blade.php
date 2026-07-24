@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Add Billing Record" />

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
                        extraAmount: '{{ old('amount', '') }}',
                        extraUnits: {{ $allUnits->map(fn($u) => [
                            'id'           => $u->id,
                            'label'        => $u->unit_number,
                            'landlord_id'  => $u->landlord_id,
                            'landlord_name'=> $u->landlord?->name ?? null,
                            // Use Other Tenant monthly_rent if attached, else unit default_monthly_rent
                            'default_rent' => ($u->otherTenant && (float)$u->otherTenant->monthly_rent > 0)
                                                ? (float) $u->otherTenant->monthly_rent
                                                : (float) ($u->default_monthly_rent ?? 0),
                        ])->values()->toJson() }},
                        extraCbOpen: false,
                        extraCbSearch: '',
                        extraCbSelectedLabel: '',
                        extraCbActiveIndex: 0,
                        get filteredExtraUnits() {
                            if (!this.extraCbSearch) return this.extraUnits;
                            const q = this.extraCbSearch.toLowerCase();
                            return this.extraUnits.filter(u => u.label.toLowerCase().includes(q));
                        },
                        init() {
                            if (this.selfUnitId) {
                                const u = this.selfUnits.find(x => x.id == this.selfUnitId);
                                if (u) {
                                    this.selfLandlordName = u.landlord_name;
                                }
                            }
                            if (this.extraUnitId) {
                                const u = this.extraUnits.find(x => x.id == this.extraUnitId);
                                if (u) {
                                    this.extraCbSelectedLabel = u.label;
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
                        },
                        pickExtraUnit(u) {
                            if (!u) return;
                            this.extraCbSelectedLabel = u.label;
                            this.extraCbOpen = false;
                            this.extraCbSearch = '';
                            this.extraUnitId = u.id;
                        },
                        extraCbScrollIntoView() {
                            const el = this.$refs.extraOptionsList?.querySelector(`[data-index='${this.extraCbActiveIndex}']`);
                            if (el) el.scrollIntoView({ block: 'nearest' });
                        },
                        getExtraSplit() {
                            const amt = parseFloat(this.extraAmount) || 0;
                            const unit = this.extraUnits.find(x => x.id == this.extraUnitId);
                            if (!unit || !unit.landlord_id || unit.default_rent <= 0) {
                                return { has_split: false, landlord_share: 0, pm_mall_share: amt, landlord_name: '' };
                            }
                            const landlordShare = Math.min(amt, unit.default_rent);
                            const pmMallShare = Math.max(0, amt - landlordShare);
                            return {
                                has_split: true,
                                landlord_share: landlordShare,
                                pm_mall_share: pmMallShare,
                                landlord_name: unit.landlord_name,
                                default_rent: unit.default_rent
                            };
                        }
                    }">

        {{-- ── Mode Tabs — Segmented Control ─────────────────────────── --}}
        <div class="mb-6">
            <div
                class="inline-flex rounded-xl border border-gray-200 bg-gray-100 p-1 dark:border-gray-700 dark:bg-gray-800">

                {{-- Tenant Billing --}}
                <button type="button" @click="mode = 'tenant'" :class="mode === 'tenant'
                                        ? 'bg-white text-gray-900 shadow-md dark:bg-gray-900 dark:text-white font-black'
                                        : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-bold'"
                    class="relative inline-flex items-center gap-2.5 rounded-xl px-6 py-3 text-base sm:text-lg transition-all duration-200 focus:outline-none cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Tenant Billing
                </button>

                {{-- Maintenance-Only (Other-Owned) --}}
                <button type="button" @click="mode = 'self'" :class="mode === 'self'
                                        ? 'bg-white text-gray-900 shadow-md dark:bg-gray-900 dark:text-white font-black'
                                        : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-bold'"
                    class="relative inline-flex items-center gap-2.5 rounded-xl px-6 py-3 text-base sm:text-lg transition-all duration-200 focus:outline-none cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    Maintenance (Other-Owned)
                </button>

                {{-- Extra Billing --}}
                <button type="button" @click="mode = 'extra'" :class="mode === 'extra'
                                        ? 'bg-white text-gray-900 shadow-md dark:bg-gray-900 dark:text-white font-black'
                                        : 'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 font-bold'"
                    class="relative inline-flex items-center gap-2.5 rounded-xl px-6 py-3 text-base sm:text-lg transition-all duration-200 focus:outline-none cursor-pointer">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Extra Billing
                </button>
            </div>
        </div>

        {{-- ── TENANT BILLING FORM ─────────────────────────────────────── --}}
        <div x-show="mode === 'tenant'" x-cloak>
            <x-common.component-card title="Tenant Billing"
                desc="Create a rent or maintenance billing linked to a tenant's agreement">
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
                            Maintenance Billing Details
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
                            Create Maintenance Billing
                        </button>
                        <a href="{{ route('payments.index') }}"
                            class="inline-flex items-center rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 dark:hover:bg-white/[0.05] transition-colors">
                            Cancel
                        </a>
                    </div>
                </form>
            </x-common.component-card>
        </div>

        {{-- ── EXTRA BILLING FORM ─────────────────────────────────────────── --}}
        <div x-show="mode === 'extra'" x-cloak>
            <x-common.component-card title="Extra Billing"
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
                            Extra billings are free-form charges. Select any unit, enter the amount, and optionally add a
                            note to identify the charge. Multiple extra billings can be recorded for the same unit and
                            month.
                        </p>
                    </div>

                    {{-- Unit Selection --}}
                    <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                            Unit Selection
                        </h4>
                        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <input type="hidden" name="unit_id" :value="extraUnitId">

                                <div class="relative"
                                     @keydown.escape.stop="extraCbOpen = false; $refs.extraCbTrigger.focus()"
                                     @keydown.arrow-down.prevent="if (!extraCbOpen) { extraCbOpen = true; extraCbActiveIndex = 0; } else if (filteredExtraUnits.length > 0) { extraCbActiveIndex = (extraCbActiveIndex + 1) % filteredExtraUnits.length; $nextTick(() => extraCbScrollIntoView()); }"
                                     @keydown.arrow-up.prevent="if (extraCbOpen && filteredExtraUnits.length > 0) { extraCbActiveIndex = (extraCbActiveIndex - 1 + filteredExtraUnits.length) % filteredExtraUnits.length; $nextTick(() => extraCbScrollIntoView()); }"
                                     @keydown.enter.prevent="if (extraCbOpen && filteredExtraUnits.length > 0) { pickExtraUnit(filteredExtraUnits[extraCbActiveIndex]); }">

                                    {{-- Trigger --}}
                                    <div x-ref="extraCbTrigger"
                                         tabindex="0"
                                         @click="extraCbOpen = !extraCbOpen; if(extraCbOpen) { $nextTick(() => $refs.extraCbSearch.focus()) }"
                                         @click.outside="extraCbOpen = false"
                                         @keydown.enter.prevent.stop="extraCbOpen = !extraCbOpen; if(extraCbOpen) { $nextTick(() => $refs.extraCbSearch.focus()) }"
                                         @keydown.space.prevent.stop="extraCbOpen = !extraCbOpen; if(extraCbOpen) { $nextTick(() => $refs.extraCbSearch.focus()) }"
                                         class="w-full cursor-pointer flex justify-between items-center rounded-2xl border-2 bg-white px-5 py-3.5 text-lg font-bold dark:bg-gray-900 {{ $errors->has('unit_id') && old('payment_mode') === 'extra' ? 'border-red-400' : 'border-gray-300 dark:border-gray-700' }}">
                                        <span x-text="extraCbSelectedLabel || 'Select Flat/Shop…'"
                                              :class="extraCbSelectedLabel ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-600'"></span>
                                        <svg class="h-5 w-5 text-gray-500 flex-shrink-0 transition-transform duration-200"
                                             :class="extraCbOpen ? 'rotate-180' : ''"
                                             fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </div>

                                    {{-- Dropdown --}}
                                    <div x-show="extraCbOpen"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute left-0 z-[99999] mt-2 w-full rounded-2xl border-2 border-brand-500 bg-white shadow-2xl dark:border-brand-500 dark:bg-gray-900 overflow-hidden py-2"
                                         style="display:none;">

                                        <div class="px-3.5 pb-2.5 pt-1.5 border-b border-gray-100 dark:border-gray-800">
                                            <input x-ref="extraCbSearch"
                                                   x-model="extraCbSearch"
                                                   type="text"
                                                   placeholder="Type to search flat/shop…"
                                                   class="w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-base sm:text-lg text-gray-900 font-bold focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-950 dark:text-white">
                                        </div>

                                        <ul class="max-h-72 overflow-y-auto mt-1 p-1 space-y-1" x-ref="extraOptionsList">
                                            <template x-if="filteredExtraUnits.length === 0">
                                                <li class="px-5 py-3 text-sm font-semibold text-gray-400 dark:text-gray-500">No matching flats/shops found.</li>
                                            </template>
                                            <template x-for="(u, index) in filteredExtraUnits" :key="u.id">
                                                <li @click="pickExtraUnit(u)"
                                                    @mouseenter="extraCbActiveIndex = index"
                                                    :data-index="index"
                                                    :class="extraCbActiveIndex === index ? 'bg-brand-600 text-white font-black' : 'text-gray-900 dark:text-white font-bold hover:bg-gray-100 dark:hover:bg-white/5'"
                                                    class="px-5 py-3.5 text-base sm:text-lg cursor-pointer flex items-center justify-between gap-3 rounded-xl transition-colors">
                                                    <span x-text="u.label" class="font-black text-lg sm:text-xl"></span>
                                                    <span x-text="u.landlord_name ? u.landlord_name : ''"
                                                          class="text-base font-bold opacity-90 truncate"></span>
                                                </li>
                                            </template>
                                        </ul>
                                    </div>

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
                                    Extra Billing
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Billing Details --}}
                    <div
                        class="mt-5 rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                            Billing Details
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
                                <input type="number" id="extra_amount" name="amount" x-model="extraAmount" min="0"
                                    step="0.01" placeholder="e.g. 25000"
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

                        {{-- Dynamic Split Preview --}}
                        <template x-if="getExtraSplit().has_split">
                            <div class="mt-5 rounded-xl border border-brand-100 bg-brand-50/30 p-4 dark:border-brand-900/30 dark:bg-brand-950/10">
                                <h5 class="mb-3 text-xs font-bold uppercase tracking-wider text-brand-600 dark:text-brand-400">
                                    Billing Allocation Split (Automatic)
                                </h5>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="rounded-lg border border-gray-100 bg-white p-3 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                                        <div class="text-xs text-gray-400 dark:text-gray-500">Landlord Share (Rent Target)</div>
                                        <div class="mt-1 text-lg font-bold text-gray-800 dark:text-white" x-text="'Rs. ' + getExtraSplit().landlord_share.toLocaleString()"></div>
                                        <div class="mt-1 text-xs text-brand-500" x-text="'Receiver: ' + getExtraSplit().landlord_name"></div>
                                    </div>
                                    <div class="rounded-lg border border-gray-100 bg-white p-3 shadow-xs dark:border-gray-800 dark:bg-gray-900">
                                        <div class="text-xs text-gray-400 dark:text-gray-500">PM Mall Share (Excess Surplus)</div>
                                        <div class="mt-1 text-lg font-bold text-gray-800 dark:text-white" x-text="'Rs. ' + getExtraSplit().pm_mall_share.toLocaleString()"></div>
                                        <div class="mt-1 text-xs text-gray-500">Receiver: PM Mall Management</div>
                                    </div>
                                </div>
                                <div class="mt-3 text-xs text-gray-400 dark:text-gray-500">
                                    Calculated automatically based on the Unit's Rent Limit of <span class="font-semibold text-gray-700 dark:text-gray-300" x-text="'Rs. ' + getExtraSplit().default_rent.toLocaleString()"></span>.
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="mt-5 flex items-center gap-3">
                        <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                            Save Extra Billing
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

            // ── Type change → re-fill amount from selected unit's agreement data ──
            const typeSelect = document.getElementById('type');
            if (typeSelect) {
                typeSelect.addEventListener('change', function () {
                    const unitSelect = document.getElementById('unit_select');
                    if (!unitSelect || !unitSelect.value) return;

                    const alpineEl = unitSelect.closest('[x-data]');
                    if (!alpineEl) return;
                    const alpine = Alpine.$data(alpineEl);
                    if (!alpine || !alpine.units) return;

                    const unit = alpine.units.find(u => u.id == unitSelect.value);
                    if (!unit) return;

                    const amountEl = document.getElementById('amount');
                    if (!amountEl) return;

                    const t = this.value;
                    if (t === 'rent')                  amountEl.value = unit.monthly_rent;
                    else if (t === 'maintenance')       amountEl.value = unit.maintenance_charge;
                    else if (t === 'security_deposit')  amountEl.value = unit.security_deposit;
                    else                                amountEl.value = '';
                });
            }
        });
    </script>
@endpush