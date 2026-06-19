{{-- ══════════════════════════════════════════════════════════════
Landlord Contact Info Card
═══════════════════════════════════════════════════════════════ --}}
<div class="space-y-6">
    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
            Landlord Contact Info
        </h4>

        {{-- Passport Photo + Basic Info --}}
        <div class="mb-5 flex items-start gap-5">
            {{-- Photo Upload --}}
            <div x-data="{
                    preview: '{{ $landlord->photo_url ?? '' }}',
                    change(e) {
                        const f = e.target.files[0];
                        if (f) this.preview = URL.createObjectURL(f);
                    }
                }" class="flex-shrink-0">
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    Passport Photo
                </label>
                <div class="relative">
                    <template x-if="preview">
                        <img :src="preview" alt="Landlord photo"
                            class="h-24 w-24 rounded-xl object-cover border-2 border-brand-200 dark:border-brand-700 shadow-sm">
                    </template>
                    <template x-if="!preview">
                        <div
                            class="flex h-24 w-24 items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-white text-gray-400 dark:border-gray-700 dark:bg-gray-900">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" stroke-width="1.5"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                        </div>
                    </template>
                </div>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" @change="change($event)"
                    class="mt-2 block w-24 text-xs text-gray-500 file:mr-0 file:cursor-pointer file:rounded-lg file:border-0 file:bg-brand-50 file:px-2 file:py-1 file:text-xs file:font-medium file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-brand-900/30 dark:file:text-brand-400">
                @error('photo')
                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>

            {{-- Name + Phone + Email + CNIC --}}
            <div class="flex-1 grid grid-cols-1 gap-4 sm:grid-cols-2">
                {{-- Name --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Full Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', $landlord->name ?? '') }}" required
                        placeholder="e.g. Muhammad Ali" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600
                        {{ $errors->has('name') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
                    @error('name')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Phone --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Phone Number <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="phone" value="{{ old('phone', $landlord->phone ?? '') }}" required
                        placeholder="e.g. +92 300 1234567" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600
                        {{ $errors->has('phone') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
                    @error('phone')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Email
                        Address</label>
                    <input type="email" name="email" value="{{ old('email', $landlord->email ?? '') }}"
                        placeholder="e.g. owner@example.com" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600
                        {{ $errors->has('email') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}">
                    @error('email')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                {{-- CNIC --}}
                <div>
                    <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">
                        CNIC / National ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="cnic" id="cnic_input" value="{{ old('cnic', $landlord->cnic ?? '') }}"
                        required placeholder="35201-1234567-1" maxlength="15" class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600
                        {{ $errors->has('cnic') ? 'border-red-400 focus:border-red-400 focus:ring-red-400' : '' }}"
                        pattern="\d{5}-\d{7}-\d{1}">
                    <p class="mt-1 text-xs text-gray-400">Format: 35201-1234567-1</p>
                    @error('cnic')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Address --}}
        <div>
            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Mailing Address</label>
            <input type="text" name="address" value="{{ old('address', $landlord->address ?? '') }}"
                placeholder="Mailing address details..."
                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
            @error('address')
                <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- Notes Card --}}
    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
        <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes / Remarks</label>
        <textarea name="notes" rows="2" placeholder="Any additional remarks..."
            class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600">{{ old('notes', $landlord->notes ?? '') }}</textarea>
        @error('notes')
            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
        @enderror
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
    OWNED UNITS PANEL
    ════════════════════════════════════════════════════════════════ --}}
    @php $landlordId = $landlord->id ?? null; @endphp

    <div x-data="landlordUnitsPanel({{ $landlordId ?? 'null' }}, {{ Js::from($landlord->units ?? collect()) }}, {{ Js::from($floors ?? collect()) }}, {{ Js::from($blocks ?? collect()) }}, {{ Js::from($areas ?? collect()) }}, {{ Js::from($allLandlords ?? collect()) }})"
        class="rounded-xl border border-gray-100 bg-gray-50 dark:border-gray-800 dark:bg-white/[0.02]">

        {{-- Panel Header --}}
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-gray-800">
            <div>
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-200">🏢 Owned Flat/shops</h4>
                <p class="text-xs text-gray-400 mt-0.5">Flats and shops owned by this landlord</p>
            </div>
            @if($landlordId)
                <button type="button" @click="openAdd()"
                    class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Flat/Shops
                </button>
            @else
                <span class="text-xs font-medium text-amber-500">⚠ Save landlord first to manage flat/shops</span>
            @endif
        </div>

        @unless($landlordId)
            {{-- Locked state for CREATE page --}}
            <div class="px-5 py-10 text-center text-gray-400">
                <span class="text-3xl">🏗️</span>
                <p class="text-sm mt-2 font-medium">Save the landlord profile above, then come back to add flat/shops.</p>
            </div>
        @else
            {{-- ── Feedback Toast (shows above table) ────────────────────── --}}
        <div x-show="toast" x-cloak x-transition class="mx-5 mb-4 mt-4 rounded-lg px-4 py-2.5 text-sm font-medium"
            :class="toastType === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-green-50 text-green-700 border border-green-200'"
            x-text="toast">
        </div>

        {{-- Units Table --}}
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-600 dark:text-gray-400">
                <thead class="text-xs uppercase bg-gray-100/60 text-gray-500 dark:bg-gray-800/60 dark:text-gray-400">
                    <tr>
                        <th class="px-4 py-2.5">Flat/Shops #</th>
                        <th class="px-4 py-2.5">Type</th>
                        <th class="px-4 py-2.5">Floor / Block / Area</th>
                        <th class="px-4 py-2.5">File No.</th>
                        <th class="px-4 py-2.5 text-right">Total</th>
                        <th class="px-4 py-2.5 text-right">Received</th>
                        <th class="px-4 py-2.5 text-right">Credit</th>
                        <th class="px-4 py-2.5 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    <template x-for="u in units" :key="u.id">
                        <tr class="hover:bg-white dark:hover:bg-white/[0.02] transition-colors">
                            <td class="px-4 py-2.5 font-semibold text-gray-800 dark:text-white/90">
                                <span x-text="u.unit_number"></span>
                                <template x-if="u.is_self">
                                    <span class="ml-1.5 inline-flex items-center rounded-full bg-indigo-100 px-1.5 py-0.5 text-[10px] font-semibold text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300">SELF</span>
                                </template>
                            </td>
                            <td class="px-4 py-2.5">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                    :class="u.type==='flat' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' :
                                                          u.type==='shop' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400' :
                                                          'bg-gray-100 text-gray-600'" x-text="u.type">
                                </span>
                            </td>
                            <td class="px-4 py-2.5 text-xs text-gray-500">
                                <span x-text="u.floor_name + ' / ' + u.block_name + ' / ' + u.area_name"></span>
                            </td>
                            <td class="px-4 py-2.5 text-xs" x-text="u.file_no || '—'"></td>
                            <td class="px-4 py-2.5 text-right text-xs font-medium"
                                x-text="u.total_amount ? 'Rs. ' + Number(u.total_amount).toLocaleString('en-PK') : '—'">
                            </td>
                            <td class="px-4 py-2.5 text-right text-xs text-green-600 font-medium"
                                x-text="u.received_amount ? 'Rs. ' + Number(u.received_amount).toLocaleString('en-PK') : '—'">
                            </td>
                            <td class="px-4 py-2.5 text-right text-xs text-red-500 font-medium"
                                x-text="u.credit_amount ? 'Rs. ' + Number(u.credit_amount).toLocaleString('en-PK') : '—'">
                            </td>
                            <td class="px-4 py-2.5 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <button type="button" @click="openEdit(u)"
                                        class="rounded-md p-1.5 text-gray-400 hover:bg-brand-50 hover:text-brand-600 dark:hover:bg-brand-900/20 transition-colors"
                                        title="Edit unit">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        </svg>
                                    </button>
                                    <button type="button" @click="openTransfer(u)"
                                        x-show="landlordId"
                                        class="rounded-md p-1.5 text-gray-400 hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-900/20 transition-colors"
                                        title="Transfer ownership">
                                        🔁
                                    </button>
                                    <button type="button" @click="deleteUnit(u)"
                                        class="rounded-md p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 transition-colors"
                                        title="Delete unit">
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                    <template x-if="units.length === 0">
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-400 text-xs">No flat/shops assigned
                                to
                                this landlord yet.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        {{-- ── Add / Edit Unit Modal ───────────────────────────── --}}
        <div x-show="formOpen" x-cloak class="fixed inset-0 z-50 flex items-start md:items-center justify-center overflow-y-auto p-2 sm:p-4 lg:p-6"
            style="background-color: rgba(15, 23, 42, 0.65); backdrop-filter: blur(4px);"
            x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click.self="closeForm()">
            <div class="w-full max-w-full sm:max-w-2xl lg:max-w-4xl rounded-2xl bg-white p-3 sm:p-5 lg:p-6 shadow-2xl dark:bg-gray-900 my-2 sm:my-4 lg:my-8 max-h-[96vh] sm:max-h-[92vh] lg:max-h-[90vh] overflow-y-auto relative z-50"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                x-transition:leave-end="opacity-0 scale-95 translate-y-4" @keydown.escape.window="closeForm()">

                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4 dark:border-gray-800">
                    <h5 class="text-base font-bold text-gray-800 dark:text-white"
                        x-text="editingId ? '✏️ Edit Flat/Shops' : '➕ Add New Flat/Shops'">
                    </h5>
                    <button type="button" @click="closeForm()"
                        class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300">
                        ✕
                    </button>
                </div>

                {{-- Section 1: Unit Identity --}}
                <div class="mb-4 rounded-lg bg-gray-50 p-4 dark:bg-white/[0.02]">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-gray-400">Flat/Shop Identity</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Flat/Shop No.
                                <span class="text-red-400">*</span></label>
                            <input type="text" x-model="form.unit_number" placeholder="e.g. A-101"
                                @input="errors.unit_number = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <p x-show="errors.unit_number" x-text="errors.unit_number"
                                class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Type <span
                                    class="text-red-400">*</span></label>
                            <select x-model="form.type" @change="errors.type = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                <option value="">Select</option>
                                <option value="flat">Flat</option>
                                <option value="shop">Shop</option>
                                <option value="office">Office</option>
                            </select>
                            <p x-show="errors.type" x-text="errors.type" class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Size
                                (sqft)</label>
                            <input type="number" x-model="form.area_sqft" placeholder="e.g. 1200" step="0.01"
                                @input="errors.area_sqft = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <p x-show="errors.area_sqft" x-text="errors.area_sqft"
                                class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Date</label>
                            <div class="relative">
                                <input type="text" x-init="
                                                $nextTick(() => {
                                                    if (typeof flatpickr !== 'undefined') {
                                                        const fp = flatpickr($el, {
                                                            dateFormat: 'Y-m-d',
                                                            altInput: true,
                                                            altFormat: 'd M Y',
                                                            defaultDate: form.date,
                                                            disableMobile: true,
                                                            onChange: (selectedDates, dateStr) => {
                                                                form.date = dateStr;
                                                                errors.date = '';
                                                            }
                                                        });
                                                        $watch('form.date', value => {
                                                            if (fp && value !== fp.currentDateStr) {
                                                                fp.setDate(value, false);
                                                            }
                                                        });
                                                    }
                                                });
                                            "
                                    class="w-full rounded-lg border border-gray-300 bg-white pl-3 pr-10 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                <span
                                    class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                            </div>
                            <p x-show="errors.date" x-text="errors.date" class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                    </div>
                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Floor <span
                                    class="text-red-400">*</span></label>
                            <select x-model="form.floor_id" @change="errors.floor_id = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                <option value="">Select Floor</option>
                                <template x-for="f in floors" :key="f.id">
                                    <option :value="f.id" x-text="f.name"></option>
                                </template>
                            </select>
                            <p x-show="errors.floor_id" x-text="errors.floor_id" class="mt-1 text-[11px] text-red-500">
                            </p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Block <span
                                    class="text-red-400">*</span></label>
                            <select x-model="form.block_id" @change="errors.block_id = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                <option value="">Select Block</option>
                                <template x-for="b in blocks" :key="b.id">
                                    <option :value="b.id" x-text="b.name"></option>
                                </template>
                            </select>
                            <p x-show="errors.block_id" x-text="errors.block_id" class="mt-1 text-[11px] text-red-500">
                            </p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Area /
                                Zone</label>
                            <select x-model="form.area_id" @change="errors.area_id = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                <option value="">Select Area</option>
                                <template x-for="a in areas" :key="a.id">
                                    <option :value="a.id" x-text="a.name"></option>
                                </template>
                            </select>
                            <p x-show="errors.area_id" x-text="errors.area_id" class="mt-1 text-[11px] text-red-500">
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Section 2: Nominee --}}
                <div class="mb-4 rounded-lg bg-blue-50/50 p-4 dark:bg-blue-900/10">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-blue-400">Nominee</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                        <div class="sm:col-span-1">
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Nominee
                                Name</label>
                            <input type="text" x-model="form.nominee_name" placeholder="Full name"
                                @input="errors.nominee_name = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <p x-show="errors.nominee_name" x-text="errors.nominee_name"
                                class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                        <div>
                            <label
                                class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Relation</label>
                            <select x-model="form.nominee_relation_type" @change="errors.nominee_relation_type = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                <option value="">Select</option>
                                <option value="son_of">S/O (Son of)</option>
                                <option value="daughter_of">D/O (Daughter of)</option>
                                <option value="wife_of">W/O (Wife of)</option>
                            </select>
                            <p x-show="errors.nominee_relation_type" x-text="errors.nominee_relation_type"
                                class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Of (Father /
                                Husband)</label>
                            <input type="text" x-model="form.nominee_relation_name" placeholder="Parent/Spouse name"
                                @input="errors.nominee_relation_name = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <p x-show="errors.nominee_relation_name" x-text="errors.nominee_relation_name"
                                class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                    </div>
                </div>

                {{-- Section 3: Financial --}}
                <div class="mb-4 rounded-lg bg-green-50/50 p-4 dark:bg-green-900/10">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-green-500">Financial Summary</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Total
                                Amount</label>
                            <input type="number" x-model="form.total_amount"
                                @input="errors.total_amount = ''; updateCredit()" placeholder="0.00" step="0.01"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <p x-show="errors.total_amount" x-text="errors.total_amount"
                                class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Received
                                Amount</label>
                            <input type="number" x-model="form.received_amount"
                                @input="errors.received_amount = ''; updateCredit()" placeholder="0.00" step="0.01"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <p x-show="errors.received_amount" x-text="errors.received_amount"
                                class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Credit /
                                Balance</label>
                            <div
                                class="flex items-center gap-1.5 rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 dark:border-gray-700 dark:bg-gray-700">
                                <span class="text-xs text-gray-400">🔒</span>
                                <span class="text-sm font-semibold text-red-500" x-text="'Rs. ' + creditAmount"></span>
                            </div>
                            <p x-show="errors.credit_amount" x-text="errors.credit_amount"
                                class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Received
                                From</label>
                            <input type="text" x-model="form.received_from" placeholder="e.g. Cash / Bank"
                                @input="errors.received_from = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <p x-show="errors.received_from" x-text="errors.received_from"
                                class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                    </div>
                </div>

                {{-- Section 4: Office Record --}}
                <div class="mb-4 rounded-lg bg-amber-50/40 p-4 dark:bg-amber-900/10">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wide text-amber-500">Office Record</p>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">File
                                No.</label>
                            <input type="text" x-model="form.file_no" placeholder="e.g. PMM-2026-042"
                                @input="errors.file_no = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <p x-show="errors.file_no" x-text="errors.file_no" class="mt-1 text-[11px] text-red-500">
                            </p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Approved
                                By</label>
                            <input type="text" x-model="form.approved_by" placeholder="e.g. Director"
                                @input="errors.approved_by = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <p x-show="errors.approved_by" x-text="errors.approved_by"
                                class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Received
                                By</label>
                            <input type="text" x-model="form.received_by" placeholder="e.g. Kamran (Accounts)"
                                @input="errors.received_by = ''"
                                class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <p x-show="errors.received_by" x-text="errors.received_by"
                                class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Approved
                                Date</label>
                            <div class="relative">
                                <input type="text" x-init="
                                                $nextTick(() => {
                                                    if (typeof flatpickr !== 'undefined') {
                                                        const fp = flatpickr($el, {
                                                            dateFormat: 'Y-m-d',
                                                            altInput: true,
                                                            altFormat: 'd M Y',
                                                            defaultDate: form.approved_date,
                                                            disableMobile: true,
                                                            onChange: (selectedDates, dateStr) => {
                                                                form.approved_date = dateStr;
                                                                errors.approved_date = '';
                                                            }
                                                        });
                                                        $watch('form.approved_date', value => {
                                                            if (fp && value !== fp.currentDateStr) {
                                                                fp.setDate(value, false);
                                                            }
                                                        });
                                                    }
                                                });
                                            "
                                    class="w-full rounded-lg border border-gray-300 bg-white pl-3 pr-10 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                                <span
                                    class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                        stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </span>
                            </div>
                            <p x-show="errors.approved_date" x-text="errors.approved_date"
                                class="mt-1 text-[11px] text-red-500"></p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Notes</label>
                        <textarea x-model="form.notes" rows="2" placeholder="Any additional details..."
                            @input="errors.notes = ''"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-800 focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"></textarea>
                        <p x-show="errors.notes" x-text="errors.notes" class="mt-1 text-[11px] text-red-500"></p>
                    </div>
                </div>

                {{-- Section 5: Self-Owned Unit (is_self) --}}
                <div class="mb-4 overflow-hidden rounded-xl border transition-all duration-300"
                    :class="form.is_self
                        ? 'border-blue-400 bg-gradient-to-br from-blue-50 to-sky-50 shadow-sm dark:border-blue-600 dark:from-blue-950/30 dark:to-sky-950/20'
                        : 'border-gray-200 bg-gray-50 dark:border-gray-700 dark:bg-gray-800/40'">

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
                                Self-Owned Unit
                            </p>
                            <p class="mt-0.5 text-xs leading-snug"
                                :class="form.is_self ? 'text-blue-500 dark:text-blue-400' : 'text-gray-400 dark:text-gray-500'">
                                <span x-show="!form.is_self">Toggle ON to mark this unit as self-owned.</span>
                                <span x-show="form.is_self">This unit is marked as self-owned — no rent will be generated.</span>
                            </p>
                        </div>

                        {{-- Toggle switch --}}
                        <button type="button"
                            x-on:click="form.is_self = !form.is_self"
                            :style="'background-color:' + (form.is_self ? '#2563eb' : '#d1d5db')"
                            style="transition: background-color 0.3s ease"
                            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500/40 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                            role="switch"
                            :aria-checked="form.is_self.toString()">
                            <span
                                :style="'transform: translateX(' + (form.is_self ? '20px' : '1px') + ') translateY(1px)'"
                                style="transition: transform 0.3s ease"
                                class="pointer-events-none inline-block h-5 w-5 rounded-full bg-white shadow-md">
                            </span>
                        </button>
                    </div>

                    {{-- Info banner when self is toggled on --}}
                    <div x-show="form.is_self"
                        x-transition:enter="transition ease-out duration-250"
                        x-transition:enter-start="opacity-0 max-h-0"
                        x-transition:enter-end="opacity-100 max-h-20"
                        x-transition:leave="transition ease-in duration-200"
                        x-transition:leave-start="opacity-100 max-h-20"
                        x-transition:leave-end="opacity-0 max-h-0"
                        class="border-t border-blue-200/60 bg-white/70 px-5 py-3 dark:border-blue-700/40 dark:bg-gray-900/50">

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
                    </div>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center gap-3">
                    <button type="button" @click="saveUnit()" :disabled="saving"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 transition-colors">
                        <svg x-show="!saving" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg x-show="saving" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="saving ? 'Saving...' : (editingId ? 'Update Unit' : 'Save Unit')"></span>
                    </button>
                    <button type="button" @click="closeForm()"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        {{-- ── Transfer Ownership Modal ─────────────────────────────── --}}
        <div x-show="transferOpen" x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm"
            @click.self="transferOpen = false">
            <div class="w-full max-w-md rounded-2xl bg-white p-6 shadow-2xl dark:bg-gray-900 mx-4"
                x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100">
                <h4 class="text-base font-bold text-gray-800 dark:text-white mb-1">🔁 Transfer Ownership</h4>
                <p class="text-xs text-gray-500 mb-4">Transfer <strong x-text="transferUnit?.unit_number"></strong> to a
                    new landlord. All current ownership data will be archived.</p>

                <div class="space-y-3">
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">New Landlord
                            <span class="text-red-400">*</span></label>
                        <select x-model="transfer.new_landlord_id"
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <option value="">Select new landlord</option>
                            <template x-for="l in allLandlords" :key="l.id">
                                <option :value="l.id" x-text="l.name"></option>
                            </template>
                        </select>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Transfer Date
                            <span class="text-red-400">*</span></label>
                        <div class="relative">
                            <input type="text" x-init="
                                            $nextTick(() => {
                                                if (typeof flatpickr !== 'undefined') {
                                                    const fp = flatpickr($el, {
                                                        dateFormat: 'Y-m-d',
                                                        altInput: true,
                                                        altFormat: 'd M Y',
                                                        defaultDate: transfer.transfer_date,
                                                        disableMobile: true,
                                                        onChange: (selectedDates, dateStr) => {
                                                            transfer.transfer_date = dateStr;
                                                        }
                                                    });
                                                    $watch('transfer.transfer_date', value => {
                                                        if (fp && value !== fp.currentDateStr) {
                                                            fp.setDate(value, false);
                                                        }
                                                    });
                                                }
                                            });
                                        "
                                class="w-full rounded-lg border border-gray-300 bg-white pl-3 pr-10 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90">
                            <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                    stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </span>
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-xs font-medium text-gray-600 dark:text-gray-400">Transfer
                            Notes</label>
                        <textarea x-model="transfer.notes" rows="2" placeholder="Reason for transfer..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white/90"></textarea>
                    </div>
                </div>

                <div class="mt-5 flex items-center gap-3">
                    <button type="button" @click="confirmTransfer()" :disabled="transferring"
                        class="flex-1 rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-600 disabled:opacity-50 transition-colors">
                        <span x-text="transferring ? 'Transferring...' : 'Confirm Transfer'"></span>
                    </button>
                    <button type="button" @click="transferOpen = false"
                        class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-300 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endunless
</div>

@once
    @push('scripts')
        <script>
            window._csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            function landlordUnitsPanel(landlordId, initialUnits, floors, blocks, areas, allLandlords) {
                const mappedUnits = (initialUnits || []).map(u => {
                    const o = u.current_ownership;
                    let relationLabel = '';
                    if (o) {
                        if (o.nominee_relation_type === 'son_of') relationLabel = 'S/O';
                        else if (o.nominee_relation_type === 'daughter_of') relationLabel = 'D/O';
                        else if (o.nominee_relation_type === 'wife_of') relationLabel = 'W/O';
                    }
                    return {
                        id: u.id,
                        unit_number: u.unit_number,
                        type: u.type,
                        status: u.status,
                        is_self: u.is_self ?? false,
                        area_sqft: u.area_sqft,
                        date: u.date ? (typeof u.date === 'string' ? u.date.split('T')[0] : u.date) : '',
                        floor_id: u.floor_id,
                        block_id: u.block_id,
                        area_id: u.area_id,
                        floor_name: u.floor?.name ?? '—',
                        block_name: u.block?.name ?? '—',
                        area_name: u.area?.name ?? '—',
                        // Ownership fields
                        ownership_id: o?.id,
                        nominee_name: o?.nominee_name ?? '',
                        nominee_relation_type: o?.nominee_relation_type ?? '',
                        nominee_relation_name: o?.nominee_relation_name ?? '',
                        relation_label: relationLabel,
                        total_amount: o?.total_amount ?? '',
                        received_amount: o?.received_amount ?? '',
                        credit_amount: o?.credit_amount ?? '',
                        received_from: o?.received_from ?? '',
                        file_no: u.file_no ?? '',
                        approved_by: o?.approved_by ?? '',
                        received_by: o?.received_by ?? '',
                        approved_date: o?.approved_date ? (typeof o.approved_date === 'string' ? o.approved_date.split('T')[0] : o.approved_date) : '',
                        notes: o?.notes ?? '',
                        validation_errors: {},
                    };
                });

                return {
                    landlordId,
                    units: mappedUnits,
                    floors: floors || [],
                    blocks: blocks || [],
                    areas: areas || [],
                    allLandlords: allLandlords || [],

                    // Form state
                    formOpen: false,
                    editingId: null,
                    saving: false,
                    toast: '',
                    toastType: 'success',
                    errors: {},

                    // Transfer state
                    transferOpen: false,
                    transferUnit: null,
                    transferring: false,
                    transfer: { new_landlord_id: '', transfer_date: '', notes: '' },

                    form: {
                        unit_number: '', type: '', floor_id: '', block_id: '', area_id: '',
                        area_sqft: '', date: '',
                        is_self: false,
                        nominee_name: '', nominee_relation_type: '', nominee_relation_name: '',
                        total_amount: '', received_amount: '', received_from: '',
                        file_no: '', approved_by: '', received_by: '', approved_date: '',
                        notes: '',
                    },

                    get creditAmount() {
                        const t = parseFloat(this.form.total_amount) || 0;
                        const r = parseFloat(this.form.received_amount) || 0;
                        return (t - r).toLocaleString('en-PK');
                    },

                    updateCredit() { /* reactivity triggers creditAmount getter */ },

                    openAdd() {
                        this.editingId = null;
                        this.errors = {};
                        this.form = {
                            unit_number: '', type: '', floor_id: '', block_id: '', area_id: '',
                            area_sqft: '', date: new Date().toISOString().split('T')[0],
                            is_self: false,
                            nominee_name: '', nominee_relation_type: '', nominee_relation_name: '',
                            total_amount: '', received_amount: '', received_from: '',
                            file_no: '', approved_by: '', received_by: '', approved_date: '',
                            notes: '',
                        };
                        this.formOpen = true;
                    },

                    openEdit(unit) {
                        this.editingId = unit.id;
                        this.errors = { ...(unit.validation_errors || {}) };
                        this.form = {
                            unit_number: unit.unit_number || '',
                            type: unit.type || '',
                            floor_id: unit.floor_id || '',
                            block_id: unit.block_id || '',
                            area_id: unit.area_id || '',
                            area_sqft: unit.area_sqft || '',
                            date: unit.date || '',
                            is_self: unit.is_self ?? false,
                            nominee_name: unit.nominee_name || '',
                            nominee_relation_type: unit.nominee_relation_type || '',
                            nominee_relation_name: unit.nominee_relation_name || '',
                            total_amount: unit.total_amount || '',
                            received_amount: unit.received_amount || '',
                            received_from: unit.received_from || '',
                            file_no: unit.file_no || '',
                            approved_by: unit.approved_by || '',
                            received_by: unit.received_by || '',
                            approved_date: unit.approved_date || '',
                            notes: unit.notes || '',
                        };
                        this.formOpen = true;
                    },

                    closeForm() {
                        this.formOpen = false;
                        this.editingId = null;
                        this.errors = {};
                    },

                    async saveUnit() {
                        this.errors = {};
                        if (!this.form.unit_number) this.errors.unit_number = 'Flat/Shop No. is required.';
                        if (!this.form.type) this.errors.type = 'Type is required.';
                        if (!this.form.floor_id) this.errors.floor_id = 'Floor is required.';
                        if (!this.form.block_id) this.errors.block_id = 'Block is required.';


                        if (Object.keys(this.errors).length > 0) {
                            return;
                        }

                        this.saving = true;
                        const url = this.editingId
                            ? `/ajax/landlord-units/${this.editingId}`
                            : '/ajax/landlord-units';

                        const body = { ...this.form, landlord_id: this.landlordId };
                        if (this.editingId) body._method = 'PUT';

                        try {
                            const r = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': window._csrf,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(body),
                            });
                            const d = await r.json();
                            if (!r.ok) {
                                if (r.status === 422 && d.errors) {
                                    this.errors = {};
                                    for (const key in d.errors) {
                                        this.errors[key] = d.errors[key].join(' ');
                                    }
                                    throw new Error('Validation failed.');
                                }
                                const errorMsg = d.message || 'Error saving unit.';
                                throw new Error(errorMsg);
                            }
                            if (!d.success) throw new Error(d.message || 'Error saving unit.');

                            if (this.editingId) {
                                const idx = this.units.findIndex(u => u.id === d.unit.id);
                                if (idx !== -1) this.units[idx] = d.unit;
                            } else {
                                this.units.push(d.unit);
                                alert("Flat/shop is added.");
                            }

                            this.showToast(d.message, 'success');
                            this.closeForm();
                        } catch (e) {
                            if (e.message !== 'Validation failed.') {
                                this.showToast(e.message, 'error');
                            }
                        } finally {
                            this.saving = false;
                        }
                    },

                    async deleteUnit(unit) {
                        if (!confirm(`Delete unit ${unit.unit_number}? This action can be undone by an administrator.`)) return;

                        try {
                            const r = await fetch(`/ajax/landlord-units/${unit.id}`, {
                                method: 'DELETE',
                                headers: { 'X-CSRF-TOKEN': window._csrf, 'Accept': 'application/json' },
                            });
                            const d = await r.json();
                            if (!d.success) throw new Error(d.message || 'Error deleting unit.');
                            this.units = this.units.filter(u => u.id !== unit.id);
                            this.showToast(d.message, 'success');
                        } catch (e) {
                            this.showToast(e.message, 'error');
                        }
                    },

                    openTransfer(unit) {
                        this.transferUnit = unit;
                        this.transfer = { new_landlord_id: '', transfer_date: new Date().toISOString().split('T')[0], notes: '' };
                        this.transferOpen = true;
                    },

                    async confirmTransfer() {
                        if (!this.transfer.new_landlord_id || !this.transfer.transfer_date) {
                            alert('Please select a new landlord and transfer date.');
                            return;
                        }
                        this.transferring = true;
                        try {
                            const r = await fetch(`/ajax/landlord-units/${this.transferUnit.id}/transfer`, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': window._csrf,
                                    'Accept': 'application/json',
                                },
                                body: JSON.stringify(this.transfer),
                            });
                            const d = await r.json();
                            if (!d.success) throw new Error(d.message || 'Transfer failed.');
                            // Remove from current landlord's list after transfer
                            this.units = this.units.filter(u => u.id !== this.transferUnit.id);
                            this.transferOpen = false;
                            this.showToast(d.message, 'success');
                        } catch (e) {
                            alert(e.message);
                        } finally {
                            this.transferring = false;
                        }
                    },

                    showToast(msg, type = 'success') {
                        this.toast = msg;
                        this.toastType = type;
                        setTimeout(() => this.toast = '', 4000);
                    },
                };
            }

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