{{-- Shared form for create & edit --}}

@php
    $activeHistory = null;
    if (isset($otherTenant) && $otherTenant->unit_id) {
        $activeHistory = $otherTenant->unitHistory()->whereNull('detached_at')->first();
    }
    $attachmentDate = $activeHistory ? $activeHistory->attached_at?->toDateString() : old('attached_at', today()->toDateString());

    $selfUnitsJson = $selfUnits->map(fn($u) => [
        'id' => $u->id,
        'unit_number' => $u->unit_number,
        'landlord_name' => $u->landlord?->name ?? '—',
        'occupied' => (bool) ($u->otherTenant && $u->otherTenant->id !== ($otherTenant->id ?? null)),
        'occupant_name' => $u->otherTenant?->name ?? '',
    ])->values()->toJson();
@endphp

@if($errors->any())
    <div
        class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-800 dark:bg-red-900/20 dark:text-red-400">
        <ul class="list-disc pl-4 space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 gap-6 md:grid-cols-2">    {{-- 1st: Attach to Other-Owned Unit with Searchable Select and Readonly Landlord --}}
    @if(!empty($selfUnits) && $selfUnits->isNotEmpty())
        <div x-data="{
            open: false,
            search: '',
            selectedId: '{{ old('unit_id', $otherTenant->unit_id ?? '') }}',
            selectedLabel: '',
            landlordName: '—',
            units: {{ $selfUnitsJson }},
            highlightedIndex: -1,
            init() {
                let match = this.units.find(u => u.id == this.selectedId);
                if (match) {
                    this.selectedLabel = match.unit_number;
                    this.landlordName = match.landlord_name;
                }
                this.$watch('highlightedIndex', val => {
                    if (val >= 0) {
                        this.$nextTick(() => {
                            let el = this.$el.querySelector('.highlighted-option');
                            if (el) el.scrollIntoView({ block: 'nearest' });
                        });
                    }
                });
                this.$watch('open', val => {
                    if (!val) this.highlightedIndex = -1;
                });
            },
            get filteredUnits() {
                let emptyOpt = {id: '', unit_number: '— No Flat/Shop Attached', landlord_name: '—', occupied: false, occupant_name: ''};
                if (!this.search) {
                    return [emptyOpt, ...this.units];
                }
                let q = this.search.toLowerCase();
                let filtered = this.units.filter(u => u.unit_number.toLowerCase().includes(q));
                if (emptyOpt.unit_number.toLowerCase().includes(q)) {
                    return [emptyOpt, ...filtered];
                }
                return filtered;
            },
            selectUnit(u) {
                if (u.occupied) return;
                this.selectedId = u.id;
                this.selectedLabel = u.id ? u.unit_number : '';
                this.landlordName = u.landlord_name;
                this.open = false;
                this.search = '';
                this.highlightedIndex = -1;
            },
            moveHighlight(dir) {
                let list = this.filteredUnits;
                if (list.length === 0) return;
                
                let hasUnoccupied = list.some(u => !u.occupied);
                if (!hasUnoccupied) return;

                let nextIndex = this.highlightedIndex;
                do {
                    nextIndex += dir;
                    if (nextIndex < 0) {
                        nextIndex = list.length - 1;
                    } else if (nextIndex >= list.length) {
                        nextIndex = 0;
                    }
                } while (list[nextIndex].occupied);
                
                this.highlightedIndex = nextIndex;
            },
            selectHighlighted() {
                let list = this.filteredUnits;
                if (this.highlightedIndex >= 0 && this.highlightedIndex < list.length) {
                    this.selectUnit(list[this.highlightedIndex]);
                }
            }
        }" class="col-span-1 md:col-span-2 grid grid-cols-1 md:grid-cols-3 gap-6 rounded-xl border border-gray-150 bg-gray-50/50 p-5 dark:border-gray-800 dark:bg-white/[0.01]">
            
            <div class="relative">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Attach Flat/Shop <span class="text-xs text-gray-400">(Optional)</span>
                </label>
                <!-- Hidden Input for Form Submission -->
                <input type="hidden" id="unit_id" name="unit_id" :value="selectedId">

                <!-- Trigger Button -->
                <div tabindex="0"
                     @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                     @keydown.space.prevent="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                     @keydown.enter.prevent="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                     @click.outside="open = false"
                     class="w-full rounded-lg border bg-white px-4 py-2.5 text-sm text-gray-800 dark:bg-gray-900 dark:text-white/90 cursor-pointer flex justify-between items-center {{ $errors->has('unit_id') ? 'border-red-400 focus-within:ring-red-400' : 'border-gray-300 focus-within:border-brand-500 focus-within:ring-brand-500 dark:border-gray-700' }}">
                    <span x-text="selectedLabel || '— No Flat/Shop Attached'" :class="selectedLabel ? '' : 'text-gray-400 dark:text-gray-600'"></span>
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
                               @keydown.arrow-down.prevent="moveHighlight(1)"
                               @keydown.arrow-up.prevent="moveHighlight(-1)"
                               @keydown.enter.prevent="selectHighlighted()"
                               @keydown.escape.prevent="open = false; highlightedIndex = -1"
                               type="text"
                               placeholder="Type to search flat/shop number..."
                               class="w-full rounded-md border border-gray-300 bg-gray-50 px-3 py-1.5 text-xs text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
                    </div>

                    <!-- Options List -->
                    <ul class="max-h-60 overflow-y-auto mt-1">
                        <template x-if="filteredUnits.length === 0">
                            <li class="px-4 py-2 text-xs text-gray-500 dark:text-gray-400">No matching flats/shops found.</li>
                        </template>
                        <template x-for="(u, index) in filteredUnits" :key="u.id">
                            <li @click="if(!u.occupied) selectUnit(u)"
                                @mouseenter="if(!u.occupied) highlightedIndex = index"
                                :class="{
                                    'opacity-50 cursor-not-allowed': u.occupied,
                                    'cursor-pointer': !u.occupied,
                                    'bg-brand-50 dark:bg-brand-900/20 font-bold highlighted-option': highlightedIndex === index
                                }"
                                class="px-4 py-2 text-sm text-gray-800 dark:text-white/90 flex justify-between items-center transition-colors">
                                <span :class="u.id ? '' : 'text-gray-500 dark:text-gray-400'" x-text="u.unit_number"></span>
                                <template x-if="u.occupied">
                                    <span class="text-xs text-red-500" x-text="'(Occupied by ' + u.occupant_name + ')'"></span>
                                </template>
                            </li>
                        </template>
                    </ul>
                </div>
                @error('unit_id')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>


            {{-- Landlord Name --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Landlord Name <span class="text-xs text-gray-400">(Read-Only)</span>
                </label>
                <div class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-100 px-4 py-2.5 dark:border-gray-800 dark:bg-gray-950/50">
                    <span class="text-xs text-gray-400">🔒</span>
                    <span class="text-sm font-bold text-gray-800 dark:text-white/90" x-text="landlordName"></span>
                </div>
            </div>

            {{-- Attachment Date --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Attachment Date
                </label>
                <input type="text" name="attached_at" id="attached_at" value="{{ $attachmentDate }}"
                    class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('attached_at') border-red-400 @enderror" />
                @error('attached_at')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <p class="col-span-1 md:col-span-3 text-[11px] text-gray-400 leading-snug">
                * Note: Only unattached, self-owned flats or shops are available for selection.
            </p>
        </div>
    @endif

    {{-- Name --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Full Name <span class="text-red-500">*</span>
        </label>
        <input type="text" name="name" value="{{ old('name', $otherTenant->name ?? '') }}" placeholder="Enter full name"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('name') border-red-400 @enderror"
            required />
        @error('name')
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
        <input type="text" name="whatsapp_number"
            value="{{ old('whatsapp_number', $otherTenant->whatsapp_number ?? '') }}" placeholder="e.g. 0300-1234567"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('whatsapp_number') border-red-400 @enderror" />
        @error('whatsapp_number')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- CNIC --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            CNIC <span class="text-gray-400">(optional)</span>
        </label>
        <input type="text" name="cnic" id="cnic_input" value="{{ old('cnic', $otherTenant->cnic ?? '') }}"
            placeholder="35201-1234567-1" maxlength="15"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('cnic') border-red-400 @enderror" />
        <p class="mt-1 text-xs text-gray-400">Format: 35201-1234567-1</p>
        @error('cnic')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Monthly Rent --}}
    <div x-data="{
        rentRaw: '{{ old('monthly_rent', isset($otherTenant) ? ($otherTenant->monthly_rent > 0 ? (int)$otherTenant->monthly_rent : '') : '') }}',
        formatNumber(val) {
            if (val === undefined || val === null || val === '') return '';
            const clean = val.toString().replace(/,/g, '');
            if (isNaN(clean) || clean === '') return '';
            const parts = clean.split('.');
            parts[0] = parseInt(parts[0], 10).toLocaleString('en-US');
            return parts.join('.');
        },
        onInput(e) {
            let clean = e.target.value.replace(/[^\d.]/g, '');
            const parts = clean.split('.');
            if (parts.length > 2) clean = parts[0] + '.' + parts.slice(1).join('');
            this.rentRaw = clean;
            e.target.value = this.formatNumber(clean);
        },
        init() {
            if (this.rentRaw) {
                this.$nextTick(() => {
                    const el = this.$refs.rentDisplay;
                    if (el) el.value = this.formatNumber(this.rentRaw);
                });
            }
        }
    }">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Monthly Rent (Rs.) <span class="text-xs text-gray-400">(Landlord's Share)</span>
        </label>
        {{-- Hidden raw value submitted to backend --}}
        <input type="hidden" name="monthly_rent" :value="rentRaw">
        {{-- Visible formatted display input --}}
        <input type="text" x-ref="rentDisplay"
            @input="onInput($event)"
            placeholder="e.g. 10,000"
            class="dark:bg-dark-900 shadow-theme-xs focus:border-brand-300 focus:ring-brand-500/10 dark:focus:border-brand-800 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 placeholder:text-gray-400 focus:ring-3 focus:outline-hidden dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 @error('monthly_rent') border-red-400 @enderror" />
        <p class="mt-1 text-xs text-gray-400">When an extra payment is created for the attached unit, this amount will be the landlord's share.</p>
        @error('monthly_rent')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- Photo Upload --}}
    <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
            Tenant Photo <span class="text-gray-400">(optional)</span>
        </label>
        @if(isset($otherTenant) && $otherTenant->photo)
            <div class="mb-3 flex items-center gap-3">
                <img src="{{ $otherTenant->photo_url }}" alt="Tenant Photo" class="h-14 w-14 rounded-full object-cover border border-gray-200 shadow-sm">
                <label class="flex items-center gap-2 text-xs text-red-500 cursor-pointer">
                    <input type="checkbox" name="delete_photo" value="1" class="rounded border-gray-300 text-red-500 focus:ring-red-500">
                    Delete Photo
                </label>
            </div>
        @endif
        <input type="file" name="photo" accept="image/*"
            class="w-full text-xs text-gray-500 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-950/20 dark:file:text-brand-400 cursor-pointer" />
        @error('photo')
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
                // Flatpickr for attached_at picker
                if (typeof flatpickr !== 'undefined') {
                    flatpickr('#attached_at', {
                        dateFormat: 'Y-m-d',
                        defaultDate: '{{ $attachmentDate ?? today()->toDateString() }}',
                    });
                }

                const cnicEl = document.getElementById('cnic_input');
                if (cnicEl) {
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
                }
            });

        </script>
    @endpush
@endonce