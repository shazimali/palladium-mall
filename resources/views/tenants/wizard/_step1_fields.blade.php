@php $t = $tenant ?? null; @endphp

{{-- Helper class string --}}
@php
$input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
$select = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
$label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
$error = 'mt-1 text-xs text-red-500';
@endphp

{{-- ── Section: Basic Identity ─────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Personal Details</h4>

    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        {{-- Full Name --}}
        <div>
            <label class="{{ $label }}">Full Name <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $t?->name ?? '') }}"
                   placeholder="e.g. Ahmed Raza" class="{{ $input }} {{ $errors->has('name') ? 'border-red-400' : '' }}">
            @error('name') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        {{-- Father / Husband Name --}}
        <div>
            <label class="{{ $label }}">Father / Husband Name</label>
            <input type="text" name="father_name" value="{{ old('father_name', $t?->father_name ?? '') }}"
                   placeholder="e.g. Muhammad Ali" class="{{ $input }}">
        </div>

        {{-- CNIC --}}
        <div>
            <label class="{{ $label }}">CNIC Number <span class="text-red-500">*</span></label>
            <input type="text" name="cnic" id="cnic_input" value="{{ old('cnic', $t?->cnic ?? '') }}"
                   placeholder="35201-1234567-1" maxlength="15"
                   class="{{ $input }} {{ $errors->has('cnic') ? 'border-red-400' : '' }}"
                   pattern="\d{5}-\d{7}-\d{1}">
            <p class="mt-1 text-xs text-gray-400">Format: 35201-1234567-1</p>
            @error('cnic') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        {{-- Date of Birth --}}
        <div>
            <label class="{{ $label }}">Date of Birth</label>
            <div class="relative">
                <input type="text" name="date_of_birth" id="dob_picker"
                       value="{{ old('date_of_birth', $t?->date_of_birth?->format('Y-m-d') ?? '') }}"
                       placeholder="Select date of birth"
                       class="{{ $input }} pr-10" readonly>
                <span class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </span>
            </div>
        </div>

        {{-- Gender --}}
        <div>
            <label class="{{ $label }}">Gender</label>
            <select name="gender" class="{{ $select }}">
                <option value="">Select gender</option>
                @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('gender', $t?->gender ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        {{-- Marital Status --}}
        <div>
            <label class="{{ $label }}">Marital Status</label>
            <select name="marital_status" class="{{ $select }}">
                <option value="">Select status</option>
                @foreach(['single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('marital_status', $t?->marital_status ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>

    </div>
</div>

{{-- ── Section: Contact ─────────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Contact Information</h4>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">

        <div>
            <label class="{{ $label }}">Phone <span class="text-red-500">*</span></label>
            <input type="text" name="phone" value="{{ old('phone', $t?->phone ?? '') }}"
                   placeholder="0300-1234567" class="{{ $input }} {{ $errors->has('phone') ? 'border-red-400' : '' }}">
            @error('phone') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="{{ $label }}">WhatsApp Number</label>
            <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $t?->whatsapp_number ?? '') }}"
                   placeholder="0300-1234567" class="{{ $input }}">
        </div>

        <div>
            <label class="{{ $label }}">Email Address</label>
            <input type="email" name="email" value="{{ old('email', $t?->email ?? '') }}"
                   placeholder="tenant@email.com" class="{{ $input }}">
        </div>

        <div>
            <label class="{{ $label }}">Occupation</label>
            <input type="text" name="occupation" value="{{ old('occupation', $t?->occupation ?? '') }}"
                   placeholder="e.g. Businessman" class="{{ $input }}">
        </div>

        <div class="sm:col-span-2">
            <label class="{{ $label }}">Permanent Address <span class="text-red-500">*</span></label>
            <input type="text" name="address" value="{{ old('address', $t?->address ?? '') }}"
                   placeholder="e.g. House 12, Street 4, Johar Town, Lahore"
                   class="{{ $input }} {{ $errors->has('address') ? 'border-red-400' : '' }}">
            @error('address') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

    </div>
</div>

{{-- ── Section: Additional ──────────────────────────────────────────── --}}
<div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]">
    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Additional Details</h4>
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">

        <div>
            <label class="{{ $label }}">Monthly Income (PKR)</label>
            <input type="number" name="monthly_income" value="{{ old('monthly_income', $t?->monthly_income ?? '') }}"
                   placeholder="e.g. 80000" min="0" class="{{ $input }}">
        </div>

        <div>
            <label class="{{ $label }}">Adults in Family</label>
            <input type="number" name="adults_count" value="{{ old('adults_count', $t?->adults_count ?? 1) }}"
                   min="1" max="20" class="{{ $input }}">
        </div>

        <div>
            <label class="{{ $label }}">Children in Family</label>
            <input type="number" name="children_count" value="{{ old('children_count', $t?->children_count ?? 0) }}"
                   min="0" max="20" class="{{ $input }}">
        </div>

        <div>
            <label class="{{ $label }}">Tenancy Type</label>
            <select name="tenancy_type" class="{{ $select }}">
                @foreach(['residential' => 'Residential Family', 'commercial' => 'Commercial', 'student' => 'Student'] as $val => $lbl)
                    <option value="{{ $val }}" {{ old('tenancy_type', $t?->tenancy_type ?? 'residential') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </div>

        <div class="sm:col-span-2">
            <label class="{{ $label }}">Passport Photo (optional)</label>
            @if(isset($t) && $t?->passport_photo)
                <div class="mb-2 flex items-center gap-3">
                    <img src="{{ $t->passport_photo_url }}" class="h-12 w-12 rounded-full object-cover border border-gray-200">
                    <span class="text-xs text-gray-500">Current photo</span>
                </div>
            @endif
            <input type="file" name="passport_photo" accept="image/*"
                   class="{{ $input }} file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-brand-600 hover:file:bg-brand-100">
            @error('passport_photo') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

    </div>
</div>

{{-- ── Scripts (Flatpickr + CNIC Mask) ─────────────────────────────── --}}
@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Flatpickr — Date of Birth ──────────────────────────────────────
    const dobEl = document.getElementById('dob_picker');
    if (dobEl && typeof flatpickr !== 'undefined') {
        flatpickr(dobEl, {
            dateFormat: 'Y-m-d',
            altInput: true,
            altFormat: 'd M Y',          // Shows "04 Jun 1990" to user
            maxDate: 'today',
            disableMobile: true,
            allowInput: false,
            yearRange: [1940, new Date().getFullYear() - 10],
        });
    }

    // ── CNIC Auto-Mask (XXXXX-XXXXXXX-X) ──────────────────────────────
    const cnicEl = document.getElementById('cnic_input');
    if (!cnicEl) return;

    function formatCnic(raw) {
        // Strip everything except digits
        const digits = raw.replace(/\D/g, '').slice(0, 13);
        if (digits.length <= 5)  return digits;
        if (digits.length <= 12) return digits.slice(0, 5) + '-' + digits.slice(5);
        return digits.slice(0, 5) + '-' + digits.slice(5, 12) + '-' + digits.slice(12, 13);
    }

    cnicEl.addEventListener('input', function (e) {
        const pos   = this.selectionStart;
        const old   = this.value;
        const fresh = formatCnic(this.value);
        this.value  = fresh;

        // Keep caret in sensible position after re-format
        const added = fresh.length - old.length;
        this.setSelectionRange(pos + added, pos + added);

        // Live validity feedback
        const valid = /^\d{5}-\d{7}-\d$/.test(fresh);
        this.classList.toggle('border-red-400',   !valid && fresh.length > 0);
        this.classList.toggle('border-green-400', valid);
    });

    cnicEl.addEventListener('keydown', function (e) {
        // Allow: backspace, delete, tab, arrows
        const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'];
        if (allowed.includes(e.key)) return;
        // Block non-digit keys
        if (!/^\d$/.test(e.key)) e.preventDefault();
    });

    // Format any pre-filled value on page load
    if (cnicEl.value) {
        cnicEl.value = formatCnic(cnicEl.value);
    }

    @if(!$t)
    // ── AJAX Lookup for Existing Tenant by CNIC ─────────────────────
    const form = cnicEl.closest('form');
    let notificationContainer = document.getElementById('cnic_exists_alert');
    if (!notificationContainer && form) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'cnic_exists_alert';
        notificationContainer.className = 'mt-3 hidden rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 transition-all duration-300';
        cnicEl.parentNode.appendChild(notificationContainer);
    }

    const originalAction = form ? form.action : '';
    let methodInput = null;

    cnicEl.addEventListener('input', function () {
        const cnic = this.value;
        if (/^\d{5}-\d{7}-\d$/.test(cnic)) {
            fetch(`/ajax/tenant-by-cnic?cnic=${encodeURIComponent(cnic)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.found && form && notificationContainer) {
                        notificationContainer.innerHTML = `
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="font-semibold text-brand-600 dark:text-brand-400">Existing Tenant Found:</span> ${data.tenant.name}
                                </div>
                                <button type="button" id="btn_autofill" class="ml-3 rounded-lg bg-brand-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-600 transition-colors">
                                    Load Old Profile
                                </button>
                            </div>
                        `;
                        notificationContainer.className = 'mt-3 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 transition-all duration-300';
                        notificationContainer.classList.remove('hidden');

                        document.getElementById('btn_autofill').addEventListener('click', function () {
                            form.querySelector('input[name="name"]').value = data.tenant.name || '';
                            form.querySelector('input[name="father_name"]').value = data.tenant.father_name || '';
                            
                            if (data.tenant.date_of_birth) {
                                const dobEl = document.getElementById('dob_picker');
                                if (dobEl && dobEl._flatpickr) {
                                    dobEl._flatpickr.setDate(data.tenant.date_of_birth);
                                } else if (dobEl) {
                                    dobEl.value = data.tenant.date_of_birth;
                                }
                            }

                            if (data.tenant.gender) {
                                form.querySelector('select[name="gender"]').value = data.tenant.gender;
                            }
                            if (data.tenant.marital_status) {
                                form.querySelector('select[name="marital_status"]').value = data.tenant.marital_status;
                            }

                            form.querySelector('input[name="phone"]').value = data.tenant.phone || '';
                            form.querySelector('input[name="whatsapp_number"]').value = data.tenant.whatsapp_number || '';
                            form.querySelector('input[name="email"]').value = data.tenant.email || '';
                            form.querySelector('input[name="occupation"]').value = data.tenant.occupation || '';
                            form.querySelector('input[name="address"]').value = data.tenant.address || '';
                            form.querySelector('input[name="monthly_income"]').value = data.tenant.monthly_income || '';
                            form.querySelector('input[name="adults_count"]').value = data.tenant.adults_count || 1;
                            form.querySelector('input[name="children_count"]').value = data.tenant.children_count || 0;
                            if (data.tenant.tenancy_type) {
                                form.querySelector('select[name="tenancy_type"]').value = data.tenant.tenancy_type;
                            }

                            // Update form action and method to target update route
                            form.action = `/tenants/${data.tenant.id}`;
                            if (!methodInput) {
                                methodInput = document.createElement('input');
                                methodInput.type = 'hidden';
                                methodInput.name = '_method';
                                methodInput.value = 'PUT';
                                form.appendChild(methodInput);
                            }

                            // Show a success message
                            notificationContainer.className = 'mt-3 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400 transition-all duration-300';
                            notificationContainer.innerHTML = `<span class="font-semibold">Success:</span> Existing details loaded! Submitting will update their profile and begin a new agreement wizard.`;
                        });
                    } else {
                        resetForm();
                    }
                })
                .catch(err => {
                    console.error('Error fetching tenant details:', err);
                });
        } else {
            resetForm();
        }
    });

    function resetForm() {
        if (notificationContainer) {
            notificationContainer.classList.add('hidden');
        }
        if (form) {
            form.action = originalAction;
        }
        if (methodInput) {
            methodInput.remove();
            methodInput = null;
        }
    }
    @endif

});
</script>
@endpush
@endonce
