{{-- ── Tenant & Agreement ──────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        Tenant & Agreement
    </h4>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        {{-- Tenant --}}
        @php
            $tenantsJson = $tenants->map(fn($t) => [
                'id' => $t->id,
                'name' => $t->name,
                'unit' => $t->unit->unit_number ?? '',
                'label' => $t->name . ($t->unit ? ' — ' . $t->unit->unit_number : ''),
            ])->values()->toJson();
        @endphp
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Tenant <span class="text-red-500">*</span>
            </label>
            <div x-data="{
                open: false,
                search: '',
                selectedId: '{{ old('tenant_id', $payment->tenant_id ?? '') }}',
                selectedLabel: '',
                tenants: {{ $tenantsJson }},
                init() {
                    let match = this.tenants.find(t => t.id == this.selectedId);
                    if (match) {
                        this.selectedLabel = match.label;
                    }
                },
                get filteredTenants() {
                    if (!this.search) return this.tenants;
                    let q = this.search.toLowerCase();
                    return this.tenants.filter(t => t.label.toLowerCase().includes(q));
                },
                selectTenant(t) {
                    this.selectedId = t.id;
                    this.selectedLabel = t.label;
                    this.open = false;
                    this.search = '';
                    this.$nextTick(() => {
                        let el = document.getElementById('tenant_id');
                        el.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                }
            }" class="relative">
                <!-- Hidden Input for Form Submission -->
                <input type="hidden" id="tenant_id" name="tenant_id" :value="selectedId">

                <!-- Trigger Button -->
                <div @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                     @click.outside="open = false"
                     class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 cursor-pointer flex justify-between items-center {{ $errors->has('tenant_id') ? 'border-red-400 focus-within:ring-red-400' : 'border-gray-300 focus-within:border-brand-500 focus-within:ring-brand-500 dark:border-gray-700' }}">
                    <span x-text="selectedLabel || 'Select tenant'" :class="selectedLabel ? '' : 'text-gray-400 dark:text-gray-600'"></span>
                    <svg class="h-4 w-4 text-gray-500 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <!-- Dropdown Menu -->
                <div x-show="open"
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="opacity-0 transform scale-95"
                     x-transition:enter-end="opacity-100 transform scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="opacity-100 transform scale-100"
                     x-transition:leave-end="opacity-0 transform scale-95"
                     class="absolute left-0 z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg dark:border-gray-700 dark:bg-gray-800 py-2"
                     style="display: none;">
                    
                    <!-- Search Input -->
                    <div class="px-3 pb-2 pt-1 border-b border-gray-100 dark:border-gray-700">
                        <input x-ref="searchInput"
                               x-model="search"
                               type="text"
                               placeholder="Type to search tenant name or unit number..."
                               class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    <!-- Options List -->
                    <ul class="max-h-60 overflow-y-auto mt-1">
                        <template x-if="filteredTenants.length === 0">
                            <li class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">No matching tenants found.</li>
                        </template>
                        <template x-for="t in filteredTenants" :key="t.id">
                            <li @click="selectTenant(t)"
                                class="px-4 py-2 text-sm text-gray-800 dark:text-white/90 hover:bg-brand-50 dark:hover:bg-brand-900/20 cursor-pointer flex justify-between items-center transition-colors">
                                <span x-text="t.label"></span>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>
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
                value="{{ old('month', isset($payment) ? $payment->month->format('Y-m-d') : now()->format('Y-m-01')) }}"
                placeholder="Select month" autocomplete="off"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('month') ? 'border-red-400' : '' }}">
            @error('month')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Add after Monthly Rent field --}}
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