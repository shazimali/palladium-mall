{{-- ── Unit / Flat-Shop Selection (First) ─────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        Flat / Shop &amp; Tenant
    </h4>

    @php
        $tenantUnitsJson = ($tenantUnits ?? collect())->map(fn($u) => [
            'id'           => $u->id,
            'unit_number'  => $u->unit_number,
            'tenant_id'    => $u->agreements->first()?->tenant_id,
            'tenant_name'  => $u->agreements->first()?->tenant?->name ?? '—',
            'landlord_name'=> $u->landlord?->name ?? '—',
            'monthly_rent'       => $u->agreements->first()?->monthly_rent ?? 0,
            'maintenance_charge' => $u->agreements->first()?->maintenance_charge ?? 0,
            'security_deposit'   => $u->agreements->first()?->security_deposit ?? 0,
            'agreement_id'       => $u->agreements->first()?->id,
        ])->values()->toJson();
    @endphp

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3"
         x-data="{
            units: {{ $tenantUnitsJson }},
            selectedUnitId: '{{ old('unit_id', isset($payment) ? $payment->unit_id : '') }}',
            tenantName: '',
            tenantId: '{{ old('tenant_id', isset($payment) ? $payment->tenant_id : '') }}',
            landlordName: '',
            agreementId: '{{ old('agreement_id', isset($payment) ? $payment->agreement_id : '') }}',
            // Combobox state
            cbOpen: false,
            cbSearch: '',
            cbSelectedLabel: '',
            cbActiveIndex: 0,
            get filteredUnits() {
                if (!this.cbSearch) return this.units;
                const q = this.cbSearch.toLowerCase();
                return this.units.filter(u => u.unit_number.toLowerCase().includes(q));
            },
            init() {
                if (this.selectedUnitId) {
                    const u = this.units.find(x => x.id == this.selectedUnitId);
                    if (u) {
                        this.cbSelectedLabel = u.unit_number;
                        this.tenantName      = u.tenant_name;
                        this.tenantId        = u.tenant_id;
                        this.landlordName    = u.landlord_name;
                        this.agreementId     = u.agreement_id;
                    }
                }
                @if(isset($payment) && $payment->unit)
                    this.tenantName   = '{{ $payment->tenant?->name ?? '—' }}';
                    this.landlordName = '{{ $payment->unit->landlord?->name ?? '—' }}';
                @endif
            },
            pickUnit(u) {
                if (!u) return;
                this.cbSelectedLabel = u.unit_number;
                this.cbOpen = false;
                this.cbSearch = '';
                this.selectUnit(u.id);
            },
            cbScrollIntoView() {
                const el = this.$refs.optionsList?.querySelector(`[data-index='${this.cbActiveIndex}']`);
                if (el) el.scrollIntoView({ block: 'nearest' });
            },
            selectUnit(id) {
                this.selectedUnitId = id;
                const u = this.units.find(x => x.id == id);
                if (u) {
                    this.tenantName   = u.tenant_name;
                    this.tenantId     = u.tenant_id;
                    this.landlordName = u.landlord_name;
                    this.agreementId  = u.agreement_id;
                    const typeEl   = document.getElementById('type');
                    const amountEl = document.getElementById('amount');
                    if (typeEl && amountEl) {
                        const t = typeEl.value;
                        if (t === 'rent')                 amountEl.value = u.monthly_rent;
                        else if (t === 'maintenance')      amountEl.value = u.maintenance_charge;
                        else if (t === 'security_deposit') amountEl.value = u.security_deposit;
                        else                               amountEl.value = '';
                    }
                } else {
                    this.tenantName = this.tenantId = this.landlordName = this.agreementId = '';
                }
            }
         }">

        {{-- Hidden inputs --}}
        <input type="hidden" id="unit_id"      name="unit_id"      :value="selectedUnitId">
        <input type="hidden" id="tenant_id"    name="tenant_id"    :value="tenantId">
        <input type="hidden" id="agreement_id" name="agreement_id" :value="agreementId">

        {{-- Flat/Shop Searchable Combobox --}}
        <div class="relative"
             @keydown.escape.stop="cbOpen = false; $refs.cbTrigger.focus()"
             @keydown.arrow-down.prevent="if (!cbOpen) { cbOpen = true; cbActiveIndex = 0; } else if (filteredUnits.length > 0) { cbActiveIndex = (cbActiveIndex + 1) % filteredUnits.length; $nextTick(() => cbScrollIntoView()); }"
             @keydown.arrow-up.prevent="if (cbOpen && filteredUnits.length > 0) { cbActiveIndex = (cbActiveIndex - 1 + filteredUnits.length) % filteredUnits.length; $nextTick(() => cbScrollIntoView()); }"
             @keydown.enter.prevent="if (cbOpen && filteredUnits.length > 0) { pickUnit(filteredUnits[cbActiveIndex]); }">

            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Select Flat / Shop <span class="text-red-500">*</span>
            </label>

            {{-- Trigger --}}
            <div x-ref="cbTrigger"
                 tabindex="0"
                 @click="cbOpen = !cbOpen; if(cbOpen) { $nextTick(() => $refs.cbSearch.focus()) }"
                 @click.outside="cbOpen = false"
                 @keydown.enter.prevent.stop="cbOpen = !cbOpen; if(cbOpen) { $nextTick(() => $refs.cbSearch.focus()) }"
                 @keydown.space.prevent.stop="cbOpen = !cbOpen; if(cbOpen) { $nextTick(() => $refs.cbSearch.focus()) }"
                 class="w-full cursor-pointer flex justify-between items-center rounded-lg border bg-white px-4 py-2.5 text-sm dark:bg-gray-900 {{ $errors->has('unit_id') ? 'border-red-400' : 'border-gray-300 dark:border-gray-700' }}">
                <span x-text="cbSelectedLabel || 'Select Flat/Shop…'"
                      :class="cbSelectedLabel ? 'text-gray-800 dark:text-white/90' : 'text-gray-400 dark:text-gray-600'"></span>
                <svg class="h-4 w-4 text-gray-500 flex-shrink-0 transition-transform duration-200"
                     :class="cbOpen ? 'rotate-180' : ''"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            {{-- Dropdown --}}
            <div x-show="cbOpen"
                 x-transition:enter="transition ease-out duration-100"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="absolute left-0 z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 py-2"
                 style="display:none;">

                <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700">
                    <input x-ref="cbSearch"
                           x-model="cbSearch"
                           type="text"
                           placeholder="Type to search flat/shop…"
                           class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>

                <ul class="max-h-60 overflow-y-auto mt-1" x-ref="optionsList">
                    <template x-if="filteredUnits.length === 0">
                        <li class="px-4 py-2 text-xs text-gray-400 dark:text-gray-500">No matching flats/shops found.</li>
                    </template>
                    <template x-for="(u, index) in filteredUnits" :key="u.id">
                        <li @click="pickUnit(u)"
                            @mouseenter="cbActiveIndex = index"
                            :data-index="index"
                            :class="cbActiveIndex === index ? 'bg-brand-50 dark:bg-brand-900/20 text-brand-700 dark:text-brand-400' : 'text-gray-800 dark:text-white/90'"
                            class="px-4 py-2 text-sm cursor-pointer flex items-center justify-between gap-3 transition-colors">
                            <span x-text="u.unit_number" class="font-medium"></span>
                            <span x-text="u.tenant_name !== '—' ? u.tenant_name : ''"
                                  class="text-xs text-gray-400 dark:text-gray-500 truncate"></span>
                        </li>
                    </template>
                </ul>
            </div>

            @error('unit_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Tenant (auto-filled) --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Tenant</label>
            <div class="w-full rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800">
                <span x-text="tenantName || 'Auto-filled when flat/shop is selected'"
                      :class="tenantName ? 'text-gray-800 dark:text-white/90 font-medium' : 'text-gray-400 dark:text-gray-500'">
                </span>
            </div>
        </div>

        {{-- Landlord (auto-filled) --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Landlord</label>
            <div class="w-full rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-800">
                <span x-text="landlordName || 'Auto-filled when flat/shop is selected'"
                      :class="landlordName ? 'text-gray-800 dark:text-white/90 font-medium' : 'text-gray-400 dark:text-gray-500'">
                </span>
            </div>
        </div>

    </div>
</div>

{{-- ── Payment Details ─────────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        Billing Details
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
                <option value="security_deposit" {{ old('type', $payment->type ?? '') === 'security_deposit' ? 'selected' : '' }}>
                    Security Deposit</option>
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
                value="{{ old('month', isset($payment) ? $payment->month->format('Y-m-d') : now()->format('Y-m-01')) }}"
                placeholder="Select month" autocomplete="off"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('month') ? 'border-red-400' : '' }}">
            @error('month')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Others --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Others
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