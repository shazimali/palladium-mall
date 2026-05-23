{{-- Unit Number + Type --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Unit Number <span class="text-red-500">*</span>
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
            <option value="shop" {{ old('type', $unit->type ?? '') === 'shop' ? 'selected' : '' }}>Shop</option>
        </select>
        @error('type')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Floor + Block --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Floor</label>
        <input type="text" name="floor" value="{{ old('floor', $unit->floor ?? '') }}"
            placeholder="e.g. Floor 1, Ground"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
        @error('floor')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Block</label>
        <input type="text" name="block" value="{{ old('block', $unit->block ?? '') }}"
            placeholder="e.g. Block A, Block B"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
        @error('block')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Status + Area --}}
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Status <span class="text-red-500">*</span>
        </label>
        <select name="status" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90
                {{ $errors->has('status') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
            <option value="">Select status</option>
            <option value="vacant" {{ old('status', $unit->status ?? '') === 'vacant' ? 'selected' : '' }}>Vacant</option>
            <option value="occupied" {{ old('status', $unit->status ?? '') === 'occupied' ? 'selected' : '' }}>Occupied
            </option>
            <option value="sold" {{ old('status', $unit->status ?? '') === 'sold' ? 'selected' : '' }}>Sold</option>
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Area (sq. ft.)</label>
        <input type="number" name="area_sqft" step="0.01" min="0" value="{{ old('area_sqft', $unit->area_sqft ?? '') }}"
            placeholder="e.g. 850"
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
        @error('area_sqft')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>
</div>

{{-- Meter IDs --}}
<div>
    <p class="mb-3 text-sm font-medium text-gray-700 dark:text-gray-300">Utility Meter IDs
        <span class="ml-1 text-xs font-normal text-gray-400">(as printed on the physical meter)</span>
    </p>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
        <div>
            <label class="mb-1.5 block text-xs text-gray-500 dark:text-gray-400">Electricity Meter</label>
            <input type="text" name="elec_meter_id" value="{{ old('elec_meter_id', $unit->elec_meter_id ?? '') }}"
                placeholder="e.g. LHR-2024-00123"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
            @error('elec_meter_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-xs text-gray-500 dark:text-gray-400">Water Meter</label>
            <input type="text" name="water_meter_id" value="{{ old('water_meter_id', $unit->water_meter_id ?? '') }}"
                placeholder="e.g. WASA-45892"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
            @error('water_meter_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="mb-1.5 block text-xs text-gray-500 dark:text-gray-400">Gas Meter</label>
            <input type="text" name="gas_meter_id" value="{{ old('gas_meter_id', $unit->gas_meter_id ?? '') }}"
                placeholder="e.g. SNGPL-78234"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
            @error('gas_meter_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

{{-- Notes --}}
<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
    <textarea name="notes" rows="3" placeholder="Any additional details about this unit..."
        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">{{ old('notes', $unit->notes ?? '') }}</textarea>
    @error('notes')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>