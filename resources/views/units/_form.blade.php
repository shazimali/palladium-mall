{{-- ══════════════════════════════════════════════════════════════
Unit Form: Create / Edit
Structural and ownership fields managed directly in the Units module.
═══════════════════════════════════════════════════════════════ --}}

<div x-data="{
    totalRaw: '{{ old('total_amount', $unit->currentOwnership?->total_amount ?? '') }}',
    receivedRaw: '{{ old('received_amount', $unit->currentOwnership?->received_amount ?? '') }}',
    rentRaw: '{{ old('default_monthly_rent', $unit->default_monthly_rent ?? '') }}',
    maintenanceRaw: '{{ old('default_maintenance_charge', $unit->default_maintenance_charge ?? '') }}',
    isSelf: {{ old('is_self', $unit->is_self ? 'true' : 'false') }},
    formatNumber(val) {
        if (val === undefined || val === null || val === '') return '';
        const clean = val.toString().replace(/,/g, '');
        if (isNaN(clean)) return '';
        const parts = clean.split('.');
        parts[0] = parseInt(parts[0], 10).toLocaleString('en-US');
        return parts.join('.');
    },
    onInput(field, e) {
        let value = e.target.value;
        let clean = value.replace(/[^\d.]/g, '');
        const parts = clean.split('.');
        if (parts.length > 2) {
            clean = parts[0] + '.' + parts.slice(1).join('');
        }
        this[field] = clean;
        e.target.value = this.formatNumber(clean);
    },
    get credit() {
        const t = parseFloat(this.totalRaw) || 0;
        const r = parseFloat(this.receivedRaw) || 0;
        return (t - r).toLocaleString('en-US');
    }
}" class="space-y-6">

    {{-- Section 0: Landlord Selection (Optional) --}}
    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
            Landlord Association
        </h4>
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Landlord / Owner <span class="text-xs text-gray-400">(Optional)</span>
            </label>
            <select name="landlord_id" 
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                <option value="">No Landlord (Unassigned)</option>
                @foreach($landlords as $l)
                    <option value="{{ $l->id }}" 
                        {{ old('landlord_id', $unit->landlord_id ?? $selectedLandlordId ?? '') == $l->id ? 'selected' : '' }}>
                        {{ $l->name }}
                    </option>
                @endforeach
            </select>
            @error('landlord_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Section 1: Unit Identity --}}
    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
            Flat/Shop Specification
        </h4>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Flat/Shop Number --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Flat/Shop No. <span class="text-red-500">*</span>
                </label>
                <input type="text" name="unit_number" required
                    value="{{ old('unit_number', $unit->unit_number) }}"
                    placeholder="e.g. A-101"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('unit_number')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Type --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Type <span class="text-red-500">*</span>
                </label>
                <select name="type" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">Select Type</option>
                    <option value="flat" {{ old('type', $unit->type) === 'flat' ? 'selected' : '' }}>Flat</option>
                    <option value="shop" {{ old('type', $unit->type) === 'shop' ? 'selected' : '' }}>Shop</option>
                    <option value="office" {{ old('type', $unit->type) === 'office' ? 'selected' : '' }}>Office</option>
                </select>
                @error('type')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Area Size --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Size (sqft)
                </label>
                <input type="number" name="area_sqft" step="0.01"
                    value="{{ old('area_sqft', $unit->area_sqft) }}"
                    placeholder="e.g. 1200"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('area_sqft')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Registration Date --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Date
                </label>
                <input type="date" name="date"
                    value="{{ old('date', $unit->date ? $unit->date->format('Y-m-d') : now()->format('Y-m-d')) }}"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('date')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
            {{-- Floor --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Floor <span class="text-red-500">*</span>
                </label>
                <select name="floor_id" required
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">Select Floor</option>
                    @foreach($floors as $f)
                        <option value="{{ $f->id }}" {{ old('floor_id', $unit->floor_id) == $f->id ? 'selected' : '' }}>
                            {{ $f->name }}
                        </option>
                    @endforeach
                </select>
                @error('floor_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Block --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Block
                </label>
                <select name="block_id"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">Select Block</option>
                    @foreach($blocks as $b)
                        <option value="{{ $b->id }}" {{ old('block_id', $unit->block_id) == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>
                @error('block_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Area / Zone --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Area / Zone
                </label>
                <select name="area_id"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">Select Area/Zone</option>
                    @foreach($areas as $a)
                        <option value="{{ $a->id }}" {{ old('area_id', $unit->area_id) == $a->id ? 'selected' : '' }}>
                            {{ $a->name }}
                        </option>
                    @endforeach
                </select>
                @error('area_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Section: Default Pricing & Estimates --}}
    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
            Default Pricing & Estimates (For Projections)
        </h4>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 max-w-2xl">
            {{-- Default Monthly Rent --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Default Monthly Rent (PKR)
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rs.</span>
                    <input type="hidden" name="default_monthly_rent" :value="rentRaw">
                    <input type="text"
                        :value="formatNumber(rentRaw)"
                        @input="onInput('rentRaw', $event)"
                        placeholder="0.00"
                        class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                @error('default_monthly_rent')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Default Maintenance Charge --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Default Maintenance Charge (PKR)
                </label>
                <div class="relative">
                    <span class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rs.</span>
                    <input type="hidden" name="default_maintenance_charge" :value="maintenanceRaw">
                    <input type="text"
                        :value="formatNumber(maintenanceRaw)"
                        @input="onInput('maintenanceRaw', $event)"
                        placeholder="0.00"
                        class="w-full rounded-lg border border-gray-300 bg-white pl-10 pr-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                </div>
                @error('default_maintenance_charge')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Nominee Section --}}
    <div class="rounded-xl border border-blue-100 bg-blue-50/20 p-5 dark:border-blue-950/40 dark:bg-white/[0.01]">
        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-blue-600 dark:text-blue-400">
            Nominee Details
        </h4>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            {{-- Nominee Name --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Nominee Name</label>
                <input type="text" name="nominee_name"
                    value="{{ old('nominee_name', $unit->currentOwnership?->nominee_name ?? '') }}"
                    placeholder="Full Name"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('nominee_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Relation Type --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Relation</label>
                <select name="nominee_relation_type"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    <option value="">Select Relation</option>
                    <option value="son_of" {{ old('nominee_relation_type', $unit->currentOwnership?->nominee_relation_type ?? '') === 'son_of' ? 'selected' : '' }}>S/O (Son of)</option>
                    <option value="daughter_of" {{ old('nominee_relation_type', $unit->currentOwnership?->nominee_relation_type ?? '') === 'daughter_of' ? 'selected' : '' }}>D/O (Daughter of)</option>
                    <option value="wife_of" {{ old('nominee_relation_type', $unit->currentOwnership?->nominee_relation_type ?? '') === 'wife_of' ? 'selected' : '' }}>W/O (Wife of)</option>
                </select>
                @error('nominee_relation_type')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Relation Name --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Of (Father/Husband Name)</label>
                <input type="text" name="nominee_relation_name"
                    value="{{ old('nominee_relation_name', $unit->currentOwnership?->nominee_relation_name ?? '') }}"
                    placeholder="Parent / Spouse Name"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('nominee_relation_name')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Financial Summary Section --}}
    <div class="rounded-xl border border-green-100 bg-green-50/20 p-5 dark:border-green-950/40 dark:bg-white/[0.01]">
        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-green-600 dark:text-green-400">
            Financial Summary
        </h4>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Total Amount --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Total Amount</label>
                <input type="hidden" name="total_amount" :value="totalRaw">
                <input type="text"
                    :value="formatNumber(totalRaw)"
                    @input="onInput('totalRaw', $event)"
                    placeholder="0.00"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('total_amount')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Received Amount --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Received Amount</label>
                <input type="hidden" name="received_amount" :value="receivedRaw">
                <input type="text"
                    :value="formatNumber(receivedRaw)"
                    @input="onInput('receivedRaw', $event)"
                    placeholder="0.00"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('received_amount')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Credit / Balance --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Credit / Balance</label>
                <div class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 dark:border-gray-800 dark:bg-gray-950">
                    <span class="text-xs text-gray-400">🔒</span>
                    <span class="text-sm font-bold text-red-500" x-text="'Rs. ' + credit"></span>
                </div>
            </div>

            {{-- Received From --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Received From</label>
                <input type="text" name="received_from"
                    value="{{ old('received_from', $unit->currentOwnership?->received_from ?? '') }}"
                    placeholder="e.g. Cash, Bank Transfer"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('received_from')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- Office Record Section --}}
    <div class="rounded-xl border border-amber-100 bg-amber-50/20 p-5 dark:border-amber-950/40 dark:bg-white/[0.01]">
        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-amber-600 dark:text-amber-400">
            Office Record
        </h4>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
            {{-- File No. --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">File No.</label>
                <input type="text" name="file_no"
                    value="{{ old('file_no', $unit->file_no) }}"
                    placeholder="e.g. PMM-2026-042"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('file_no')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Approved By --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Approved By</label>
                <input type="text" name="approved_by"
                    value="{{ old('approved_by', $unit->currentOwnership?->approved_by ?? '') }}"
                    placeholder="Approving Officer"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('approved_by')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Received By --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Received By</label>
                <input type="text" name="received_by"
                    value="{{ old('received_by', $unit->currentOwnership?->received_by ?? '') }}"
                    placeholder="Receiving Officer"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('received_by')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Approved Date --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Approved Date</label>
                <input type="date" name="approved_date"
                    value="{{ old('approved_date', $unit->currentOwnership?->approved_date?->format('Y-m-d') ?? '') }}"
                    class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                @error('approved_date')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Notes / Remarks --}}
        <div class="mt-4">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Billing Notes / Remarks</label>
            <textarea name="notes" rows="3"
                placeholder="Remarks, billing logs or structural information about this unit..."
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">{{ old('notes', $unit->notes) }}</textarea>
            @error('notes')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Section 5: Self-Owned Unit (is_self) --}}
    <div class="overflow-hidden rounded-xl border transition-all duration-300"
        :class="isSelf
            ? 'border-blue-400 bg-gradient-to-br from-blue-50 to-sky-50 shadow-sm dark:border-blue-600 dark:from-blue-950/30 dark:to-sky-950/20'
            : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-850'">

        {{-- Header row with toggle --}}
        <div class="flex items-center gap-4 px-5 py-4">
            {{-- Icon --}}
            <div class="flex h-10 w-10 flex-shrink-0 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-700">
                <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>

            {{-- Label --}}
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold leading-tight text-blue-600 dark:text-blue-400">
                    Other-Owned Unit
                </p>
                <p class="mt-0.5 text-xs leading-snug"
                    :class="isSelf ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500'">
                    <span x-show="!isSelf">Toggle ON to mark this unit as other-owned.</span>
                    <span x-show="isSelf">This unit is marked as other-owned — no rent will be generated.</span>
                </p>
            </div>

            {{-- Toggle switch --}}
            <input type="hidden" name="is_self" :value="isSelf ? 1 : 0">
            <button type="button"
                x-on:click="isSelf = !isSelf"
                :style="'background-color:' + (isSelf ? '#2563eb' : '#d1d5db')"
                style="transition: background-color 0.3s ease"
                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                role="switch"
                :aria-checked="isSelf.toString()">
                <span
                    :style="'transform: translateX(' + (isSelf ? '20px' : '1px') + ') translateY(1px)'"
                    style="transition: transform 0.3s ease"
                    class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-md">
                </span>
            </button>
        </div>

        {{-- Info banner and maintenance charge input --}}
        <div x-show="isSelf"
            x-transition:enter="transition ease-out duration-250"
            x-transition:enter-start="opacity-0 max-h-0"
            x-transition:enter-end="opacity-100 max-h-40"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 max-h-40"
            x-transition:leave-end="opacity-0 max-h-0"
            class="border-t border-blue-200/60 bg-white/70 px-5 py-4 dark:border-blue-700/40 dark:bg-gray-900/50 space-y-3">

            <div class="flex items-center gap-2.5">
                <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-md bg-blue-100 dark:bg-blue-900/40">
                    <svg class="h-3.5 w-3.5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-xs text-blue-600 dark:text-blue-300">
                    Manage tenants & maintenance charges via the
                    <a href="{{ route('other-tenants.index') }}" class="font-semibold underline decoration-blue-400/50 underline-offset-2 hover:text-blue-800 dark:hover:text-blue-200 transition-colors">
                        Other Tenants
                    </a>
                    module.
                </p>
            </div>

            <div class="rounded-lg bg-blue-50 border border-blue-100 p-3.5 dark:bg-blue-950/20 dark:border-blue-900/40">
                <p class="text-xs text-blue-800 dark:text-blue-300 leading-relaxed font-medium">
                    ℹ️ <strong>Monthly Maintenance Billing:</strong> Since this unit is marked as other-owned, monthly maintenance payments will automatically be generated using the <strong>Default Maintenance Charge</strong> specified in the Default Pricing section above.
                </p>
            </div>
        </div>
    </div>

    @if($unit->exists)
        {{-- Utility Meters Section --}}
        @include('meters._panel', ['unit' => $unit, 'existingMeters' => $existingMeters])
    @endif

</div>