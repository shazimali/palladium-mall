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
</div>

@once
    @push('scripts')
        <script>
            window._csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

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