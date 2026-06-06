{{-- Unit Number + Type --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Flat/Shop Number <span class="text-red-500">*</span>
        </label>
        <input type="text" name="unit_number" value="{{ old('unit_number', $unit->unit_number ?? '') }}"
            placeholder="e.g. A-101, S-G01" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600
                {{ $errors->has('unit_number') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
        @error('unit_number')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Type <span class="text-red-500">*</span>
        </label>
        <select name="type" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90
                {{ $errors->has('type') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
            <option value="">Select type</option>
            <option value="flat" {{ old('type', $unit->type ?? '') === 'flat' ? 'selected' : '' }}>Flat</option>
            <option value="office" {{ old('type', $unit->type ?? '') === 'office' ? 'selected' : '' }}>Office</option>
            <option value="shop" {{ old('type', $unit->type ?? '') === 'shop' ? 'selected' : '' }}>Shop</option>
        </select>
        @error('type')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Floor + Block + Area + Landlord --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-4">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Floor</label>
        <select name="floor_id" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            <option value="">Select Floor</option>
            @foreach($floors as $floor)
                <option value="{{ $floor->id }}" {{ old('floor_id', $unit->floor_id ?? '') == $floor->id ? 'selected' : '' }}>
                    {{ $floor->name }}
                </option>
            @endforeach
        </select>
        @error('floor_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Block</label>
        <select name="block_id" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            <option value="">Select Block</option>
            @foreach($blocks as $block)
                <option value="{{ $block->id }}" {{ old('block_id', $unit->block_id ?? '') == $block->id ? 'selected' : '' }}>
                    {{ $block->name }}
                </option>
            @endforeach
        </select>
        @error('block_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Area / Zone</label>
        <select name="area_id" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            <option value="">Select Area / Zone</option>
            @foreach($areas as $area)
                <option value="{{ $area->id }}" {{ old('area_id', $unit->area_id ?? '') == $area->id ? 'selected' : '' }}>
                    {{ $area->name }}
                </option>
            @endforeach
        </select>
        @error('area_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Landlord <span class="text-red-500">*</span>
        </label>
        <select name="landlord_id" required class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90
                {{ $errors->has('landlord_id') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
            <option value="">Select Landlord</option>
            @foreach($landlords as $landlord)
                <option value="{{ $landlord->id }}" {{ old('landlord_id', $unit->landlord_id ?? '') == $landlord->id ? 'selected' : '' }}>
                    {{ $landlord->name }}
                </option>
            @endforeach
        </select>
        @error('landlord_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Status --}}
<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
        Status <span class="text-red-500">*</span>
    </label>
    <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90
            {{ $errors->has('status') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
        <option value="">Select status</option>
        <option value="vacant" {{ old('status', $unit->status ?? '') === 'vacant' ? 'selected' : '' }}>Vacant</option>
        <option value="occupied" {{ old('status', $unit->status ?? '') === 'occupied' ? 'selected' : '' }}>Occupied</option>
    </select>
    @error('status')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>

{{-- Utility Meters --}}
@php
    $meterTypes = [
        'electricity' => ['label' => 'Electricity', 'icon' => '⚡', 'placeholder' => 'e.g. LHR-2024-00123'],
        'water'       => ['label' => 'Water',       'icon' => '💧', 'placeholder' => 'e.g. WASA-45892'],
        'gas'         => ['label' => 'Gas',         'icon' => '🔥', 'placeholder' => 'e.g. SNGPL-78234'],
    ];
    $existingMeters = $existingMeters ?? collect();
    $unitId = $unit->id ?? null;
@endphp

<div>
    <div class="mb-3 flex items-center justify-between">
        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
            Utility Meters
        </p>
        @unless($unitId)
            <span class="text-xs font-medium text-amber-500">⚠ Save unit first, then add meters</span>
        @endunless
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        @foreach($meterTypes as $mType => $meta)
            @php
                $existing = $existingMeters->get($mType);
                $hasMeter = (bool) $existing;
            @endphp

            <div x-data="{
                    saving: false,
                    meterId: '{{ $existing->id ?? '' }}',
                    unitId: '{{ $unitId ?? '' }}',
                    mType: '{{ $mType }}',
                    imagePreview: '{{ $existing && $existing->meter_image ? Storage::url($existing->meter_image) : '' }}',
                    get isSaved() { return !!this.meterId; },
                    async save() {
                        if (!this.unitId) { alert('Save the unit first, then add meters.'); return; }
                        const refNo = document.getElementById('meter-ref-{{ $mType }}').value.trim();
                        if (!refNo) { alert('Meter Ref No is required.'); return; }
                        this.saving = true;
                        const fd = new FormData();
                        fd.append('meter_ref_no',      refNo);
                        fd.append('meter_consumer_id', document.getElementById('meter-consumer-{{ $mType }}').value.trim());
                        fd.append('is_active', document.getElementById('meter-status-active-' + this.mType)?.checked ? '1' : '0');
                        const imgInput = document.getElementById('meter-img-{{ $mType }}');
                        if (imgInput && imgInput.files[0]) fd.append('meter_image', imgInput.files[0]);
                        if (!this.meterId) {
                            fd.append('unit_id', this.unitId);
                            fd.append('type', this.mType);
                        } else {
                            fd.append('_method', 'PUT');
                        }
                        const url = this.meterId ? '/ajax/meters/' + this.meterId : '/ajax/meters';
                        try {
                            const r = await fetch(url, {
                                method: 'POST',
                                headers: { 'X-CSRF-TOKEN': window._csrf, 'Accept': 'application/json' },
                                body: fd
                            });
                            const d = await r.json();
                            if (!d.success) throw new Error(d.message ?? 'Error');
                            this.meterId = d.meter.id;
                            if (d.image_url) this.imagePreview = d.image_url;
                            this.$dispatch('meter-saved', { type: this.mType, label: d.message });
                        } catch(e) {
                            alert(e.message);
                        } finally {
                            this.saving = false;
                        }
                    }
                }"
                 class="rounded-xl border p-4 transition-all duration-200"
                 :class="isSaved ? 'border-brand-400 bg-brand-50 dark:bg-brand-900/20' : 'border-blue-light-200 bg-blue-light-50 dark:border-blue-light-800 dark:bg-blue-light-950/30'">

                {{-- Card header --}}
                <div class="mb-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="text-lg">{{ $meta['icon'] }}</span>
                        <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $meta['label'] }}</span>
                    </div>
                    <span x-show="isSaved" x-cloak
                          class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-400">
                        Saved
                    </span>
                    <span x-show="!isSaved" x-cloak
                          class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-400 dark:bg-gray-800">
                        Not saved
                    </span>
                </div>

                {{-- Fields --}}
                <div class="space-y-3">

                    {{-- Meter Ref No --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">
                            Meter Ref No <span class="text-red-400">*</span>
                        </label>
                        <input type="text"
                               id="meter-ref-{{ $mType }}"
                               placeholder="{{ $meta['placeholder'] }}"
                               value="{{ $existing->meter_ref_no ?? '' }}"
                               {{ $unitId ? '' : 'disabled' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white/90">
                    </div>

                    {{-- Consumer ID --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Consumer ID</label>
                        <input type="text"
                               id="meter-consumer-{{ $mType }}"
                               placeholder="e.g. CON-00123"
                               value="{{ $existing->meter_consumer_id ?? '' }}"
                               {{ $unitId ? '' : 'disabled' }}
                               class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 disabled:cursor-not-allowed disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-white/90">
                    </div>

                    {{-- Status radios --}}
                    <div class="flex items-center gap-4">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Status:</span>
                        <label class="flex cursor-pointer items-center gap-1.5 text-xs">
                            <input type="radio" id="meter-status-active-{{ $mType }}"
                                   name="meter_status_{{ $mType }}" value="1"
                                   class="accent-brand-500"
                                   {{ (!$hasMeter || ($existing && $existing->is_active)) ? 'checked' : '' }}
                                   {{ $unitId ? '' : 'disabled' }}>
                            <span class="text-green-600 dark:text-green-400">Active</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-1.5 text-xs">
                            <input type="radio" name="meter_status_{{ $mType }}" value="0"
                                   class="accent-brand-500"
                                   {{ ($hasMeter && $existing && !$existing->is_active) ? 'checked' : '' }}
                                   {{ $unitId ? '' : 'disabled' }}>
                            <span class="text-gray-500">Inactive</span>
                        </label>
                    </div>

                    {{-- Meter Image --}}
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Meter Image</label>
                        <template x-if="imagePreview">
                            <div class="mb-2">
                                <img :src="imagePreview" class="h-20 w-auto rounded-lg border border-gray-200 object-cover dark:border-gray-700" alt="Meter image">
                            </div>
                        </template>
                        <input type="file"
                               id="meter-img-{{ $mType }}"
                               accept="image/*"
                               {{ $unitId ? '' : 'disabled' }}
                               class="block w-full text-xs text-gray-600 file:mr-3 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-brand-700 hover:file:bg-brand-100 disabled:opacity-50 dark:text-gray-400 dark:file:bg-brand-900/30 dark:file:text-brand-400">
                    </div>

                    {{-- Save button --}}
                    <div class="flex items-center gap-3 pt-1">
                        @if($unitId)
                            <button type="button"
                                    @click="save()"
                                    :disabled="saving"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-medium text-white transition-colors hover:bg-brand-600 disabled:opacity-50">
                                <svg x-show="!saving" class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg x-show="saving" x-cloak class="h-3 w-3 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                                <span x-show="!saving">Save</span>
                                <span x-show="saving" x-cloak>Saving...</span>
                            </button>
                        @else
                            <span class="text-xs italic text-gray-400">Save unit first to manage meters</span>
                        @endif

                        <span x-on:meter-saved.window="
                                if ($event.detail.type === mType) {
                                    $el.textContent = '✓ ' + $event.detail.label;
                                    $el.classList.remove('hidden');
                                    setTimeout(() => $el.classList.add('hidden'), 3000);
                                }"
                              class="hidden text-xs text-green-600 dark:text-green-400">
                        </span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

@once
@push('scripts')
<script>
    window._csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
</script>
@endpush
@endonce

{{-- Notes --}}
<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
    <textarea name="notes" rows="3" placeholder="Any additional details about this unit..."
        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">{{ old('notes', $unit->notes ?? '') }}</textarea>
    @error('notes')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>