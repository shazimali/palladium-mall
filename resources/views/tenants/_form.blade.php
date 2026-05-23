{{-- ── Personal Details ───────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        Personal Details
    </h4>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        {{-- Name --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Full Name <span class="text-red-500">*</span>
            </label>
            <input type="text" name="name" value="{{ old('name', $tenant->name ?? '') }}" placeholder="e.g. Ahmed Raza"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('name') ? 'border-red-400' : '' }}">
            @error('name')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- CNIC --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                CNIC <span class="text-red-500">*</span>
            </label>
            <input type="text" name="cnic" value="{{ old('cnic', $tenant->cnic ?? '') }}" placeholder="35201-1234567-1"
                maxlength="15"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('cnic') ? 'border-red-400' : '' }}">
            @error('cnic')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Phone --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Phone <span class="text-red-500">*</span>
            </label>
            <input type="text" name="phone" value="{{ old('phone', $tenant->phone ?? '') }}" placeholder="0300-1234567"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('phone') ? 'border-red-400' : '' }}">
            @error('phone')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Email --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Email
            </label>
            <input type="email" name="email" value="{{ old('email', $tenant->email ?? '') }}"
                placeholder="tenant@email.com"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('email') ? 'border-red-400' : '' }}">
            @error('email')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Occupation --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Occupation
            </label>
            <input type="text" name="occupation" value="{{ old('occupation', $tenant->occupation ?? '') }}"
                placeholder="e.g. Businessman, Teacher"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
        </div>

        {{-- Dependents --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Dependents
            </label>
            <input type="number" name="dependents" min="0" max="20"
                value="{{ old('dependents', $tenant->dependents ?? '') }}" placeholder="e.g. 3"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600 {{ $errors->has('dependents') ? 'border-red-400' : '' }}">
            @error('dependents')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Address --}}
        <div class="sm:col-span-2">
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Address
            </label>
            <input type="text" name="address" value="{{ old('address', $tenant->address ?? '') }}"
                placeholder="e.g. House 12, Street 4, Johar Town, Lahore"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">
        </div>

    </div>
</div>

{{-- ── Unit Assignment ────────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        Unit Assignment
    </h4>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        {{-- Unit --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Assign Unit <span class="text-red-500">*</span>
            </label>
            <select name="unit_id"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('unit_id') ? 'border-red-400' : '' }}">
                <option value="">Select a vacant unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ old('unit_id', $tenant->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->unit_number }}
                        {{ $unit->floor ? '— ' . $unit->floor : '' }}
                        {{ $unit->block ? '/ ' . $unit->block : '' }}
                        ({{ ucfirst($unit->type) }})
                    </option>
                @endforeach
            </select>
            @error('unit_id')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- Status --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                Status <span class="text-red-500">*</span>
            </label>
            <select name="status"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('status') ? 'border-red-400' : '' }}">
                <option value="active" {{ old('status', $tenant->status ?? 'active') === 'active' ? 'selected' : '' }}>
                    Active</option>
                <option value="inactive" {{ old('status', $tenant->status ?? 'active') === 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            @error('status')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>

{{-- ── CNIC Images ────────────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-1 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
        CNIC Images
    </h4>
    <p class="mb-4 text-xs text-gray-400">JPEG or PNG only. Max 2MB each.</p>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        {{-- CNIC Front --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                CNIC Front
            </label>

            {{-- Show existing image on edit --}}
            @if(isset($tenant) && $tenant->cnic_front_image)
                <div class="mb-2 flex items-center gap-2">
                    <span class="text-xs text-gray-500">Current image saved</span>
                    <a href="{{ $tenant->cnic_front_url }}" target="_blank"
                        class="text-xs text-brand-500 hover:underline">View</a>
                </div>
            @endif

            <input type="file" name="cnic_front_image" accept="image/jpeg,image/jpg,image/png"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-brand-600 hover:file:bg-brand-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('cnic_front_image') ? 'border-red-400' : '' }}">
            @error('cnic_front_image')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        {{-- CNIC Back --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                CNIC Back
            </label>

            @if(isset($tenant) && $tenant->cnic_back_image)
                <div class="mb-2 flex items-center gap-2">
                    <span class="text-xs text-gray-500">Current image saved</span>
                    <a href="{{ $tenant->cnic_back_url }}" target="_blank"
                        class="text-xs text-brand-500 hover:underline">View</a>
                </div>
            @endif

            <input type="file" name="cnic_back_image" accept="image/jpeg,image/jpg,image/png"
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-brand-600 hover:file:bg-brand-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 {{ $errors->has('cnic_back_image') ? 'border-red-400' : '' }}">
            @error('cnic_back_image')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

    </div>
</div>

{{-- ── Notes ──────────────────────────────────────────────────────── --}}
<div>
    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes</label>
    <textarea name="notes" rows="3" placeholder="Any additional notes about this tenant..."
        class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">{{ old('notes', $tenant->notes ?? '') }}</textarea>
    @error('notes')
        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
    @enderror
</div>