@extends('layouts.app')

@section('content')
    <div class="mx-auto max-w-4xl px-4 py-6">

        <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
            <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants and Agreements</a>
            <span>/</span>
            <span class="text-gray-800 dark:text-white/90">{{ $title }}</span>
        </div>

        @if(session('success'))
            <div
                class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
                {{ session('success') }}
            </div>
        @endif

        @include('tenants.wizard._progress', ['currentStep' => $step, 'tenantId' => $tenant->id])

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800 flex justify-between items-center">
                <div>
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white/90">Step 2 — Guarantor & Emergency Contacts</h1>
                    <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Add a guarantor and at least 2 emergency contacts.</p>
                </div>
                @if($tenant->guarantor)
                    <a href="{{ route('tenants.printStep', [$tenant, 2]) }}" target="_blank"
                       class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                        </svg>
                        Print
                    </a>
                @endif
            </div>

            <form method="POST" action="{{ route('tenants.saveStep', [$tenant, 2]) }}" class="px-6 py-6 space-y-6">
                @csrf

                @php
                    $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
                    $select = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
                    $label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
                    $error = 'mt-1 text-xs text-red-500';
                    $g = $guarantor;
                @endphp

                {{-- ── Guarantor ─────────────────────────────────────────────── --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
                    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">
                        Guarantor (Mandatory)</h4>
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

                        <div>
                            <label class="{{ $label }}">Guarantor Name <span class="text-red-500">*</span></label>
                            <input type="text" name="guarantor_name" value="{{ old('guarantor_name', $g->name ?? '') }}"
                                placeholder="Full name"
                                class="{{ $input }} {{ $errors->has('guarantor_name') ? 'border-red-400' : '' }}">
                            @error('guarantor_name') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">CNIC <span class="text-red-500">*</span></label>
                            <input type="text" name="guarantor_cnic" id="cnic_input"
                                value="{{ old('guarantor_cnic', $g->cnic ?? '') }}" placeholder="35201-1234567-1"
                                pattern="\d{5}-\d{7}-\d{1}" maxlength="15"
                                class="{{ $input }} {{ $errors->has('guarantor_cnic') ? 'border-red-400' : '' }}">
                            <p class="mt-1 text-xs text-gray-400">Format: 35201-1234567-1</p>
                            @error('guarantor_cnic') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Relation <span class="text-red-500">*</span></label>
                            <select name="guarantor_relation" class="{{ $select }}">
                                <option value="">Select relation</option>
                                @foreach(['dealer' => 'Dealer', 'friend' => 'Friend', 'relative' => 'Relative', 'employer' => 'Employer', 'other' => 'Other'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('guarantor_relation', $g->relation ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                            @error('guarantor_relation') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Phone <span class="text-red-500">*</span></label>
                            <input type="text" name="guarantor_phone" value="{{ old('guarantor_phone', $g->phone ?? '') }}"
                                placeholder="03001234567"
                                class="{{ $input }} {{ $errors->has('guarantor_phone') ? 'border-red-400' : '' }}">
                            @error('guarantor_phone') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="{{ $label }}">Occupation</label>
                            <input type="text" name="guarantor_occupation"
                                value="{{ old('guarantor_occupation', $g->occupation ?? '') }}"
                                placeholder="e.g. Businessman" class="{{ $input }}">
                        </div>

                        <div class="sm:col-span-2">
                            <label class="{{ $label }}">Address <span class="text-red-500">*</span></label>
                            <input type="text" name="guarantor_address"
                                value="{{ old('guarantor_address', $g->address ?? '') }}" placeholder="Full address"
                                class="{{ $input }} {{ $errors->has('guarantor_address') ? 'border-red-400' : '' }}">
                            @error('guarantor_address') <p class="{{ $error }}">{{ $message }}</p> @enderror
                        </div>

                    </div>
                </div>

                {{-- ── Emergency Contacts ────────────────────────────────────── --}}
                <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]"
                    x-data="{ contacts: {{ json_encode(
        old(
            'contacts',
            $emergencyContacts->count() > 0
            ? $emergencyContacts->map(fn($c) => ['name' => $c->name, 'relation' => $c->relation, 'phone' => $c->phone, 'address' => $c->address ?? ''])->toArray()
            : [['name' => '', 'relation' => '', 'phone' => '', 'address' => ''], ['name' => '', 'relation' => '', 'phone' => '', 'address' => '']]
        )
    ) }} }">

                    <div class="mb-4 flex items-center justify-between">
                        <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Emergency
                            Contacts (min. 2)</h4>
                        <button type="button" @click="contacts.push({name:'',relation:'',phone:'',address:''})"
                            class="text-xs font-medium text-brand-500 hover:text-brand-600">+ Add Contact</button>
                    </div>

                    @error('contacts') <p class="mb-3 text-xs text-red-500">{{ $message }}</p> @enderror

                    <div class="space-y-4">
                        <template x-for="(contact, index) in contacts" :key="index">
                            <div
                                class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800/50">
                                <div class="mb-3 flex items-center justify-between">
                                    <span class="text-xs font-semibold text-gray-500 uppercase"
                                        x-text="'Contact ' + (index + 1)"></span>
                                    <button type="button" x-show="contacts.length > 2" @click="contacts.splice(index, 1)"
                                        class="text-xs text-red-400 hover:text-red-500">Remove</button>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <label class="{{ $label }}">Name <span class="text-red-500">*</span></label>
                                        <input type="text" :name="'contacts[' + index + '][name]'" x-model="contact.name"
                                            placeholder="Full name" class="{{ $input }}">
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">Relation <span class="text-red-500">*</span></label>
                                        <select :name="'contacts[' + index + '][relation]'" x-model="contact.relation"
                                            class="{{ $select }}">
                                            <option value="">Select relation</option>
                                            <option value="father">Father</option>
                                            <option value="mother">Mother</option>
                                            <option value="brother">Brother</option>
                                            <option value="sister">Sister</option>
                                            <option value="wife">Wife</option>
                                            <option value="husband">Husband</option>
                                            <option value="son">Son</option>
                                            <option value="daughter">Daughter</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">Phone <span class="text-red-500">*</span></label>
                                        <input type="text" :name="'contacts[' + index + '][phone]'" x-model="contact.phone"
                                            placeholder="03001234567" class="{{ $input }}">
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">Address</label>
                                        <input type="text" :name="'contacts[' + index + '][address]'"
                                            x-model="contact.address" placeholder="Optional" class="{{ $input }}">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Nav --}}
                <div class="flex items-center justify-between pt-2">
                    <a href="{{ route('tenants.showStep', [$tenant, 1]) }}"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back
                    </a>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        Continue — Step 3
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {

                // ── CNIC Auto-Mask (XXXXX-XXXXXXX-X) ──────────────────────────────
                const cnicEl = document.getElementById('cnic_input');
                if (!cnicEl) return;

                function formatCnic(raw) {
                    // Strip everything except digits
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

                    // Keep caret in sensible position after re-format
                    const added = fresh.length - old.length;
                    this.setSelectionRange(pos + added, pos + added);

                    // Live validity feedback
                    const valid = /^\d{5}-\d{7}-\d$/.test(fresh);
                    this.classList.toggle('border-red-400', !valid && fresh.length > 0);
                    this.classList.toggle('border-green-400', valid);
                });

                cnicEl.addEventListener('keydown', function (e) {
                    // Allow: backspace, delete, tab, arrows
                    const allowed = ['Backspace', 'Delete', 'Tab', 'ArrowLeft', 'ArrowRight', 'Home', 'End'];
                    if (allowed.includes(e.key)) return;
                    // Block non-digit keys
                    if (!/^\d$/.test(e.key)) e.preventDefault();
                });

                // Format any pre-filled value on page load
                if (cnicEl.value) {
                    cnicEl.value = formatCnic(cnicEl.value);
                }

            });
        </script>
    @endpush
@endonce