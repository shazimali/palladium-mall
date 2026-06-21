{{-- Shared form for create & edit --}}

@if($errors->any())
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
        <ul class="list-disc pl-4 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">

    {{-- Name --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Full Name <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" value="{{ old('name', $otherTenant->name ?? '') }}"
            placeholder="Enter full name"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('name') border-red-400 @enderror"
            required />
        @error('name')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- CNIC --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            CNIC <span class="text-red-500">*</span>
        </label>
        <input type="text" name="cnic" id="cnic_input" value="{{ old('cnic', $otherTenant->cnic ?? '') }}"
            placeholder="35201-1234567-1" maxlength="15" required
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('cnic') border-red-400 @enderror"
            pattern="\d{5}-\d{7}-\d{1}" />
        <p class="mt-1 text-xs text-gray-400">Format: 35201-1234567-1</p>
        @error('cnic')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Phone --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Phone Number
        </label>
        <input type="text" name="phone" value="{{ old('phone', $otherTenant->phone ?? '') }}"
            placeholder="e.g. 0300-1234567"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('phone') border-red-400 @enderror" />
        @error('phone')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- WhatsApp --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            WhatsApp Number
        </label>
        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $otherTenant->whatsapp_number ?? '') }}"
            placeholder="e.g. 0300-1234567"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('whatsapp_number') border-red-400 @enderror" />
        @error('whatsapp_number')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>


    {{-- Status --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Status <span class="text-red-500">*</span>
        </label>
        <select name="status"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('status') border-red-400 @enderror">
            <option value="active"   {{ old('status', $otherTenant->status ?? 'active') === 'active'   ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ old('status', $otherTenant->status ?? '')       === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Attach to Other-Owned Unit --}}
    @if(!empty($selfUnits) && $selfUnits->isNotEmpty())
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Attach to Unit
            <span class="ml-1 text-xs font-normal text-gray-400">(optional — other-owned units only)</span>
        </label>
        <select name="unit_id"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('unit_id') border-red-400 @enderror">
            <option value="">— No unit / Detach —</option>
            @foreach($selfUnits as $unit)
                @php
                    $currentOccupant = $unit->otherTenant;
                    $isSelf = isset($otherTenant) && $currentOccupant && $currentOccupant->id === ($otherTenant->id ?? null);
                    $selected = old('unit_id', $otherTenant->unit_id ?? null) == $unit->id;
                @endphp
                <option value="{{ $unit->id }}" {{ $selected ? 'selected' : '' }} {{ $currentOccupant && !$isSelf ? 'disabled' : '' }}>
                    Unit {{ $unit->unit_number }}
                    — {{ $unit->floor?->name }} / {{ $unit->block?->name }}
                    @if($currentOccupant && !$isSelf)
                        (Occupied by {{ $currentOccupant->name }} — Locked)
                    @endif
                </option>
            @endforeach
        </select>
        @error('unit_id')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
        <p class="mt-1 text-xs text-gray-400">Only unattached units can be selected. You cannot select a unit already attached to another tenant.</p>
    </div>
    @endif

    {{-- Address --}}
    <div class="md:col-span-2">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Address
        </label>
        <textarea name="address" rows="3"
            placeholder="Residential address..."
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('address') border-red-400 @enderror">{{ old('address', $otherTenant->address ?? '') }}</textarea>
        @error('address')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

</div>

{{-- Submit --}}
<div class="mt-6 flex items-center justify-end gap-3">
    <a href="{{ route('other-tenants.index') }}"
        class="rounded-lg border border-gray-300 px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400 dark:hover:bg-white/5 transition-colors">
        Cancel
    </a>
    <button type="submit"
        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600 transition-colors">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        {{ $submitLabel ?? 'Save' }}
    </button>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const cnicEl = document.getElementById('cnic_input');
                if (!cnicEl) return;

                function formatCnic(raw) {
                    const digits = raw.replace(/\D/g, '').slice(0, 13);
                    if (digits.length <= 5) return digits;
                    if (digits.length <= 12) return digits.slice(0, 5) + '-' + digits.slice(5);
                    return digits.slice(0, 5) + '-' + digits.slice(5, 12) + '-' + digits.slice(12, 13);
                }

                cnicEl.addEventListener('input', function (e) {
                    const pos = this.selectionStart;
                    const old = this.value;
                    const fresh = formatCnic(this.value);
                    this.value = fresh;

                    const added = fresh.length - old.length;
                    this.setSelectionRange(pos + added, pos + added);

                    const valid = /^\d{5}-\d{7}-\d$/.test(fresh);
                    this.classList.toggle('border-red-400', !valid && fresh.length > 0);
                    this.classList.toggle('border-green-400', valid);
                });

                cnicEl.addEventListener('keydown', function (e) {
                    const allowed = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
                    if (allowed.includes(e.key)) return;
                    if (!/^\d$/.test(e.key)) e.preventDefault();
                });

                if (cnicEl.value) {
                    cnicEl.value = formatCnic(cnicEl.value);
                }
            });
        </script>
    @endpush
@endonce
