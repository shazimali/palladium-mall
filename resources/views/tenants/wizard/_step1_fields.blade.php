@php $t = $tenant ?? null; @endphp

{{-- Helper class string --}}
@php
$input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
$select = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
$label = 'mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300';
$error = 'mt-1 text-xs text-red-500';
@endphp

@php
    $oldInput = old('partners');
    if (is_array($oldInput)) {
        $initialPartners = [];
        foreach ($oldInput as $index => $pData) {
            $existingP = null;
            if (isset($t) && !empty($pData['cnic'])) {
                $existingP = $t->partners->firstWhere('cnic', $pData['cnic']);
            }
            $initialPartners[] = [
                'name' => $pData['name'] ?? '',
                'father_name' => $pData['father_name'] ?? '',
                'cnic' => $pData['cnic'] ?? '',
                'gender' => $pData['gender'] ?? '',
                'marital_status' => $pData['marital_status'] ?? '',
                'phone' => $pData['phone'] ?? '',
                'whatsapp_number' => $pData['whatsapp_number'] ?? '',
                'email' => $pData['email'] ?? '',
                'address' => $pData['address'] ?? '',
                'occupation' => $pData['occupation'] ?? '',
                'monthly_income' => $pData['monthly_income'] ?? '',
                'passport_photo_url' => $existingP?->passport_photo_url ?? '',
                'cnic_front_url' => $existingP?->cnic_front_url ?? '',
                'cnic_back_url' => $existingP?->cnic_back_url ?? '',
            ];
        }
    } else {
        $initialPartners = (isset($t) && $t->partners->isNotEmpty()) ? $t->partners->map(fn($p) => [
            'name' => $p->name,
            'father_name' => $p->father_name,
            'cnic' => $p->cnic,
            'gender' => $p->gender,
            'marital_status' => $p->marital_status,
            'phone' => $p->phone,
            'whatsapp_number' => $p->whatsapp_number,
            'email' => $p->email,
            'address' => $p->address,
            'occupation' => $p->occupation,
            'monthly_income' => $p->monthly_income,
            'passport_photo_url' => $p->passport_photo_url,
            'cnic_front_url' => $p->cnic_front_url,
            'cnic_back_url' => $p->cnic_back_url,
        ])->toArray() : [];
    }
    $initialRentedByMultiple = old('rented_by_multiple', (isset($t) && $t->partners->isNotEmpty()) ? '1' : '0');
@endphp
<div x-data="partnerManager({{ json_encode($initialPartners) }}, '{{ $initialRentedByMultiple }}')">

    {{-- ── Section: Flat/Unit Assignment ───────────────────────────────── --}}
    <div class="rounded-xl border border-brand-100 bg-brand-50 p-5 dark:border-brand-900/30 dark:bg-brand-900/10 mb-6">
        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-brand-700 dark:text-brand-400">Flat / Shop Assignment</h4>
        <div>
            <label class="{{ $label }}">Select Flat / Shop <span class="text-red-500">*</span></label>
            <select name="unit_id" class="{{ $select }} {{ $errors->has('unit_id') ? 'border-red-400' : '' }}">
                <option value="">— Select a flat/shop —</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" {{ old('unit_id', $t?->unit_id ?? '') == $unit->id ? 'selected' : '' }}>
                        {{ $unit->unit_number }}
                        {{ $unit->floor ? '— ' . $unit->floor->name : '' }}
                        {{ $unit->block ? '/ ' . $unit->block->name : '' }}
                        ({{ ucfirst($unit->type) }})
                        — <span class="{{ $unit->status === 'vacant' ? 'text-green-600' : 'text-orange-500' }}">{{ ucfirst($unit->status) }}</span>
                    </option>
                @endforeach
            </select>
            @error('unit_id') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- ── Section: Basic Identity ─────────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02] mb-6">
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
    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02] mb-6">
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
    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02] mb-6">
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
                <label class="{{ $label }}">Employees / Staff</label>
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
        </div>

        {{-- Row for Tenant uploads --}}
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mt-5 pt-4 border-t border-gray-100 dark:border-gray-800">
            {{-- Passport Photo --}}
            <div class="space-y-2">
                <label class="{{ $label }}">Passport Photo (optional)</label>
                
                {{-- Preview Card --}}
                <div x-show="tenantPassportPreview" class="relative group w-32 h-32 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                    <img :src="tenantPassportPreview" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity gap-2">
                        <button type="button" @click="startCamera('passport_photo', 'face')" class="p-1.5 bg-white text-gray-800 rounded-md hover:bg-gray-100 transition-colors" title="Retake Photo">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            </svg>
                        </button>
                        <button type="button" @click="clearTenantFile('passport_photo', 'tenantPassportPreview')" class="p-1.5 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors" title="Delete">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                {{-- Control Buttons (when no preview exists) --}}
                <div x-show="!tenantPassportPreview" class="flex gap-2">
                    <button type="button" @click="startCamera('passport_photo', 'face')" 
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        </svg>
                        Take Photo
                    </button>
                    <button type="button" @click="document.getElementById('file_tenant_passport').click()" 
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Upload File
                    </button>
                </div>
                
                <input type="file" name="passport_photo" id="file_tenant_passport" accept="image/*" @change="updateTenantPreview($event, 'tenantPassportPreview')" class="hidden">
                <input type="hidden" name="delete_passport_photo" id="delete_passport_photo" value="0">
                @error('passport_photo') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
            
            {{-- CNIC Front Image --}}
            <div class="space-y-2">
                <label class="{{ $label }}">CNIC Front Image (optional)</label>
                
                {{-- Preview Card --}}
                <div x-show="tenantCnicFrontPreview" class="relative group w-full h-32 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                    <img :src="tenantCnicFrontPreview" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity gap-2">
                        <a :href="tenantCnicFrontPreview" target="_blank" class="p-1.5 bg-white text-gray-800 rounded-md hover:bg-gray-100 transition-colors" title="View Large">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <button type="button" @click="startCamera('cnic_front_image', 'card')" class="p-1.5 bg-white text-gray-800 rounded-md hover:bg-gray-100 transition-colors" title="Retake Photo">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            </svg>
                        </button>
                        <button type="button" @click="clearTenantFile('cnic_front_image', 'tenantCnicFrontPreview')" class="p-1.5 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors" title="Delete">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                {{-- Control Buttons (when no preview exists) --}}
                <div x-show="!tenantCnicFrontPreview" class="flex gap-2">
                    <button type="button" @click="startCamera('cnic_front_image', 'card')" 
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        </svg>
                        Scan CNIC
                    </button>
                    <button type="button" @click="document.getElementById('file_tenant_cnic_front').click()" 
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Upload File
                    </button>
                </div>
                
                <input type="file" name="cnic_front_image" id="file_tenant_cnic_front" accept="image/*" @change="updateTenantPreview($event, 'tenantCnicFrontPreview')" class="hidden">
                <input type="hidden" name="delete_cnic_front_image" id="delete_cnic_front_image" value="0">
                @error('cnic_front_image') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
            
            {{-- CNIC Back Image --}}
            <div class="space-y-2">
                <label class="{{ $label }}">CNIC Back Image (optional)</label>
                
                {{-- Preview Card --}}
                <div x-show="tenantCnicBackPreview" class="relative group w-full h-32 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                    <img :src="tenantCnicBackPreview" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity gap-2">
                        <a :href="tenantCnicBackPreview" target="_blank" class="p-1.5 bg-white text-gray-800 rounded-md hover:bg-gray-100 transition-colors" title="View Large">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </a>
                        <button type="button" @click="startCamera('cnic_back_image', 'card')" class="p-1.5 bg-white text-gray-800 rounded-md hover:bg-gray-100 transition-colors" title="Retake Photo">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            </svg>
                        </button>
                        <button type="button" @click="clearTenantFile('cnic_back_image', 'tenantCnicBackPreview')" class="p-1.5 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors" title="Delete">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                {{-- Control Buttons (when no preview exists) --}}
                <div x-show="!tenantCnicBackPreview" class="flex gap-2">
                    <button type="button" @click="startCamera('cnic_back_image', 'card')" 
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        </svg>
                        Scan CNIC
                    </button>
                    <button type="button" @click="document.getElementById('file_tenant_cnic_back').click()" 
                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Upload File
                    </button>
                </div>
                
                <input type="file" name="cnic_back_image" id="file_tenant_cnic_back" accept="image/*" @change="updateTenantPreview($event, 'tenantCnicBackPreview')" class="hidden">
                <input type="hidden" name="delete_cnic_back_image" id="delete_cnic_back_image" value="0">
                @error('cnic_back_image') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- ── Section: Partners / Co-Tenants ────────────────────────────────── --}}
    <div class="rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02] mb-6">
        <div class="mb-4">
            <label class="{{ $label }}">Is this flat/shop rented by more than one person? <span class="text-red-500">*</span></label>
            <div class="flex items-center gap-6 mt-2">
                <label class="inline-flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="radio" name="rented_by_multiple" value="0" x-model="rentedByMultiple"
                           class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600">
                    <span class="ml-2 text-gray-700 dark:text-gray-300">No, rented by a single person</span>
                </label>
                <label class="inline-flex items-center text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="radio" name="rented_by_multiple" value="1" x-model="rentedByMultiple"
                           class="h-4 w-4 border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600">
                    <span class="ml-2 text-gray-700 dark:text-gray-300">Yes, rented by multiple persons</span>
                </label>
            </div>
            @error('rented_by_multiple') <p class="{{ $error }}">{{ $message }}</p> @enderror
        </div>

        <div x-show="rentedByMultiple === '1'" x-transition class="border-t border-gray-200 dark:border-gray-800 pt-4">
            
            <div class="mb-4 rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-xs text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                <div class="flex items-start gap-2">
                    <svg class="h-4 w-4 text-blue-500 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <span class="font-bold">Notice:</span> If the flat is rented by more than one person, the main person listed under <strong>Personal Details</strong> will be registered as the primary <strong>Tenant</strong>, and the 2nd or 3rd person(s) listed below will be registered as <strong>Partners</strong>.
                    </div>
                </div>
            </div>

            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300">Partners / Co-Tenants</h4>
                    <p class="text-xs text-gray-400 mt-0.5">Add partner details below. At least one partner is required.</p>
                </div>
                <button type="button" @click="addPartner()"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-brand-300 bg-white px-3 py-1.5 text-xs font-semibold text-brand-600 hover:bg-brand-50 dark:border-gray-700 dark:bg-gray-800 dark:text-brand-400 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Partner
                </button>
            </div>

            <div class="space-y-4">
                <template x-for="(p, index) in partners" :key="index">
                    <div class="p-5 rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900/40 relative mb-4">
                        <button type="button" @click="removePartner(index)"
                                class="absolute top-5 right-5 text-xs text-red-500 hover:text-red-600 font-medium">
                            Remove Partner
                        </button>

                        <h5 class="text-xs font-bold uppercase tracking-wider text-gray-400 mb-4" x-text="'Partner / Co-Tenant #' + (index + 1)"></h5>

                        {{-- Partner Row 1: Name, Father's Name, CNIC --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-4">
                            <div>
                                <label class="{{ $label }}">Partner Name <span class="text-red-500">*</span></label>
                                <input type="text" :name="'partners[' + index + '][name]'" x-model="p.name"
                                       placeholder="Full name" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">Father / Husband Name</label>
                                <input type="text" :name="'partners[' + index + '][father_name]'" x-model="p.father_name"
                                       placeholder="Father's name" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">Partner CNIC <span class="text-red-500">*</span></label>
                                <input type="text" :name="'partners[' + index + '][cnic]'" x-model="p.cnic"
                                       placeholder="35201-1234567-1" maxlength="15"
                                       @input="p.cnic = formatPartnerCnic($event.target.value)"
                                       class="{{ $input }}">
                            </div>
                        </div>

                        {{-- Partner Row 2: Gender, Marital Status, Phone --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-4">
                            <div>
                                <label class="{{ $label }}">Gender</label>
                                <select :name="'partners[' + index + '][gender]'" x-model="p.gender" class="{{ $select }}">
                                    <option value="">Select gender</option>
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="{{ $label }}">Marital Status</label>
                                <select :name="'partners[' + index + '][marital_status]'" x-model="p.marital_status" class="{{ $select }}">
                                    <option value="">Select status</option>
                                    <option value="single">Single</option>
                                    <option value="married">Married</option>
                                    <option value="divorced">Divorced</option>
                                    <option value="widowed">Widowed</option>
                                </select>
                            </div>
                            <div>
                                <label class="{{ $label }}">Partner Phone <span class="text-red-500">*</span></label>
                                <input type="text" :name="'partners[' + index + '][phone]'" x-model="p.phone"
                                       placeholder="03001234567" class="{{ $input }}">
                            </div>
                        </div>

                        {{-- Partner Row 3: WhatsApp, Email, Occupation --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-4">
                            <div>
                                <label class="{{ $label }}">WhatsApp Number</label>
                                <input type="text" :name="'partners[' + index + '][whatsapp_number]'" x-model="p.whatsapp_number"
                                       placeholder="e.g. 03001234567" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">Email Address</label>
                                <input type="email" :name="'partners[' + index + '][email]'" x-model="p.email"
                                       placeholder="email@address.com" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">Occupation</label>
                                <input type="text" :name="'partners[' + index + '][occupation]'" x-model="p.occupation"
                                       placeholder="e.g. Job, Business" class="{{ $input }}">
                            </div>
                        </div>

                        {{-- Partner Row 4: Permanent Address, Monthly Income --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 mb-4">
                            <div class="sm:col-span-2">
                                <label class="{{ $label }}">Permanent Address <span class="text-red-500">*</span></label>
                                <input type="text" :name="'partners[' + index + '][address]'" x-model="p.address"
                                       placeholder="Johar Town, Lahore" class="{{ $input }}">
                            </div>
                            <div>
                                <label class="{{ $label }}">Monthly Income (PKR)</label>
                                <input type="number" :name="'partners[' + index + '][monthly_income]'" x-model="p.monthly_income"
                                       placeholder="e.g. 50000" min="0" class="{{ $input }}">
                            </div>
                        </div>

                        {{-- Partner Row 5: File uploads (Passport Photo, CNIC Front, CNIC Back) --}}
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3 pt-3 border-t border-gray-100 dark:border-gray-800">
                            {{-- Passport Photo --}}
                            <div class="space-y-2">
                                <label class="{{ $label }}">Passport Photo</label>
                                
                                {{-- Preview Card --}}
                                <div x-show="p.passport_photo_url" class="relative group w-32 h-32 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                                    <img :src="p.passport_photo_url" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity gap-2">
                                        <button type="button" @click="startCamera('partners[' + index + '][passport_photo]', 'face')" class="p-1.5 bg-white text-gray-800 rounded-md hover:bg-gray-100 transition-colors" title="Retake Photo">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            </svg>
                                        </button>
                                        <button type="button" @click="clearPartnerFile(index, 'passport_photo', 'passport_photo_url')" class="p-1.5 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors" title="Delete">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- Control Buttons (when no preview exists) --}}
                                <div x-show="!p.passport_photo_url" class="flex gap-2">
                                    <button type="button" @click="startCamera('partners[' + index + '][passport_photo]', 'face')" 
                                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        </svg>
                                        Take Photo
                                    </button>
                                    <button type="button" @click="document.getElementById('file_partner_' + index + '_passport').click()" 
                                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        Upload
                                    </button>
                                </div>
                                
                                <input type="file" :name="'partners[' + index + '][passport_photo]'" :id="'file_partner_' + index + '_passport'" accept="image/*" @change="updatePartnerPreview($event, index, 'passport_photo_url')" class="hidden">
                                <input type="hidden" :name="'partners[' + index + '][delete_passport_photo]'" :value="p.delete_passport_photo ? '1' : '0'">
                            </div>
                            
                            {{-- CNIC Front Image --}}
                            <div class="space-y-2">
                                <label class="{{ $label }}">CNIC Front Image</label>
                                
                                {{-- Preview Card --}}
                                <div x-show="p.cnic_front_url" class="relative group w-full h-32 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                                    <img :src="p.cnic_front_url" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity gap-2">
                                        <a :href="p.cnic_front_url" target="_blank" class="p-1.5 bg-white text-gray-800 rounded-md hover:bg-gray-100 transition-colors" title="View Large">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <button type="button" @click="startCamera('partners[' + index + '][cnic_front_image]', 'card')" class="p-1.5 bg-white text-gray-800 rounded-md hover:bg-gray-100 transition-colors" title="Retake Photo">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            </svg>
                                        </button>
                                        <button type="button" @click="clearPartnerFile(index, 'cnic_front_image', 'cnic_front_url')" class="p-1.5 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors" title="Delete">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- Control Buttons (when no preview exists) --}}
                                <div x-show="!p.cnic_front_url" class="flex gap-2">
                                    <button type="button" @click="startCamera('partners[' + index + '][cnic_front_image]', 'card')" 
                                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        </svg>
                                        Scan CNIC
                                    </button>
                                    <button type="button" @click="document.getElementById('file_partner_' + index + '_cnic_front').click()" 
                                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        Upload
                                    </button>
                                </div>
                                
                                <input type="file" :name="'partners[' + index + '][cnic_front_image]'" :id="'file_partner_' + index + '_cnic_front'" accept="image/*" @change="updatePartnerPreview($event, index, 'cnic_front_url')" class="hidden">
                                <input type="hidden" :name="'partners[' + index + '][delete_cnic_front_image]'" :value="p.delete_cnic_front_image ? '1' : '0'">
                            </div>
                            
                            {{-- CNIC Back Image --}}
                            <div class="space-y-2">
                                <label class="{{ $label }}">CNIC Back Image</label>
                                
                                {{-- Preview Card --}}
                                <div x-show="p.cnic_back_url" class="relative group w-full h-32 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                                    <img :src="p.cnic_back_url" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity gap-2">
                                        <a :href="p.cnic_back_url" target="_blank" class="p-1.5 bg-white text-gray-800 rounded-md hover:bg-gray-100 transition-colors" title="View Large">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <button type="button" @click="startCamera('partners[' + index + '][cnic_back_image]', 'card')" class="p-1.5 bg-white text-gray-800 rounded-md hover:bg-gray-100 transition-colors" title="Retake Photo">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                            </svg>
                                        </button>
                                        <button type="button" @click="clearPartnerFile(index, 'cnic_back_image', 'cnic_back_url')" class="p-1.5 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors" title="Delete">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                                
                                {{-- Control Buttons (when no preview exists) --}}
                                <div x-show="!p.cnic_back_url" class="flex gap-2">
                                    <button type="button" @click="startCamera('partners[' + index + '][cnic_back_image]', 'card')" 
                                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                        </svg>
                                        Scan CNIC
                                    </button>
                                    <button type="button" @click="document.getElementById('file_partner_' + index + '_cnic_back').click()" 
                                            class="flex-1 inline-flex items-center justify-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                        </svg>
                                        Upload
                                    </button>
                                </div>
                                
                                <input type="file" :name="'partners[' + index + '][cnic_back_image]'" :id="'file_partner_' + index + '_cnic_back'" accept="image/*" @change="updatePartnerPreview($event, index, 'cnic_back_url')" class="hidden">
                                <input type="hidden" :name="'partners[' + index + '][delete_cnic_back_image]'" :value="p.delete_cnic_back_image ? '1' : '0'">
                            </div>
                        </div>

                    </div>
                </template>
                <div x-show="partners.length === 0" class="text-center py-6 border border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                    <p class="text-sm text-gray-500 dark:text-gray-400">At least one partner is required when rented by multiple persons. Click "Add Partner" above.</p>
                </div>
                <div id="partners_error_container" class="mt-2"></div>
                @error('partners') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- ── Section: Emergency Contact ────────────────────────────────────── --}}
    @php
        $ec = isset($t) ? $t?->emergencyContacts?->first() : null;
    @endphp
    <div class="rounded-xl border border-orange-100 bg-orange-50 p-5 dark:border-orange-900/30 dark:bg-orange-900/10 mb-6">
        <h4 class="mb-4 text-sm font-semibold uppercase tracking-wide text-orange-700 dark:text-orange-400">Emergency Contact <span class="text-red-500">*</span></h4>
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div>
                <label class="{{ $label }}">Contact Name <span class="text-red-500">*</span></label>
                <input type="text" name="ec_name" value="{{ old('ec_name', $ec?->name ?? '') }}"
                       placeholder="Full name"
                       class="{{ $input }} {{ $errors->has('ec_name') ? 'border-red-400' : '' }}">
                @error('ec_name') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $label }}">Relation <span class="text-red-500">*</span></label>
                <select name="ec_relation" class="{{ $select }} {{ $errors->has('ec_relation') ? 'border-red-400' : '' }}">
                    <option value="">Select relation</option>
                    @foreach(['father' => 'Father', 'mother' => 'Mother', 'brother' => 'Brother', 'sister' => 'Sister', 'wife' => 'Wife', 'husband' => 'Husband', 'son' => 'Son', 'daughter' => 'Daughter', 'other' => 'Other'] as $val => $lbl)
                        <option value="{{ $val }}" {{ old('ec_relation', $ec?->relation ?? '') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                @error('ec_relation') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="{{ $label }}">Phone <span class="text-red-500">*</span></label>
                <input type="text" name="ec_phone" value="{{ old('ec_phone', $ec?->phone ?? '') }}"
                       placeholder="03001234567"
                       class="{{ $input }} {{ $errors->has('ec_phone') ? 'border-red-400' : '' }}">
                @error('ec_phone') <p class="{{ $error }}">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- Reusable Camera Scanner Overlay Modal --}}
    <div x-show="showCameraModal" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/75 backdrop-blur-sm"
         style="display: none;">
        
        <div class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-gray-900 border border-gray-200 dark:border-gray-800">
            {{-- Modal Header --}}
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white" 
                    x-text="activeGuide === 'face' ? 'Live Passport Photo Capture' : 'Scan CNIC Document'">
                </h3>
                <button type="button" @click="closeCamera()" class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            {{-- Camera Viewport --}}
            <div class="relative bg-black aspect-[4/3] flex items-center justify-center overflow-hidden">
                {{-- Video element --}}
                <video id="camera_preview_video" 
                       class="w-full h-full object-cover" 
                       :class="activeGuide === 'face' ? '-scale-x-100' : ''"
                       autoplay playsinline></video>
                
                {{-- Guide Overlay Silhouettes --}}
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    {{-- Face oval guide --}}
                    <template x-if="activeGuide === 'face'">
                        <div class="w-[60%] h-[75%] border-2 border-dashed border-brand-500 rounded-[50%] shadow-[0_0_0_9999px_rgba(0,0,0,0.5)]"></div>
                    </template>
                    
                    {{-- Card rectangle guide --}}
                    <template x-if="activeGuide === 'card'">
                        <div class="w-[75%] h-[63%] border-2 border-dashed border-brand-500 rounded-xl shadow-[0_0_0_9999px_rgba(0,0,0,0.5)] flex items-center justify-center">
                            <span class="text-xs text-white/70 bg-black/40 px-3 py-1 rounded-full border border-white/20">Align CNIC within border</span>
                        </div>
                    </template>
                </div>
                
                {{-- Loading and Error Indicators --}}
                <div x-show="cameraLoading" class="absolute inset-0 flex flex-col items-center justify-center bg-gray-950/80 text-white">
                    <svg class="animate-spin h-8 w-8 text-brand-500 mb-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm">Initializing camera...</span>
                </div>
                
                <div x-show="cameraError" class="absolute inset-0 p-6 flex flex-col items-center justify-center bg-gray-950/90 text-center text-white">
                    <svg class="h-10 w-10 text-red-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <p class="text-sm font-medium" x-text="cameraError"></p>
                </div>
            </div>
            
            {{-- Modal Footer --}}
            <div class="flex items-center justify-between bg-gray-50 px-5 py-4 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800">
                <button type="button" @click="closeCamera()" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors">
                    Cancel
                </button>
                
                <div class="flex gap-2">
                    {{-- Toggle camera button (if multiple video inputs exist) --}}
                    <button type="button" x-show="cameraDevices.length > 1" @click="toggleCamera()" 
                            class="rounded-lg border border-gray-300 bg-white p-2 text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 transition-colors"
                            title="Switch Camera">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 8H17"/>
                        </svg>
                    </button>
                    
                    <button type="button" @click="takeSnapshot()" 
                            class="inline-flex items-center gap-1.5 rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-semibold text-white shadow hover:bg-brand-600 transition-colors">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Capture Image
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Scripts (CNIC Mask & AJAX Form Submit) ──────────────────────── --}}
@once
@push('scripts')
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('partnerManager', (initialPartners, initialRentedByMultiple) => {
        const partnersArray = Array.isArray(initialPartners) ? initialPartners : Object.values(initialPartners || {});
        return {
            rentedByMultiple: String(initialRentedByMultiple !== undefined ? initialRentedByMultiple : (partnersArray.length > 0 ? '1' : '0')),
            partners: partnersArray,
            
            // Tenant Previews
            tenantPassportPreview: '{{ isset($t) && $t->passport_photo ? $t->passport_photo_url : "" }}',
            tenantCnicFrontPreview: '{{ isset($t) && $t->cnic_front_image ? $t->cnic_front_url : "" }}',
            tenantCnicBackPreview: '{{ isset($t) && $t->cnic_back_image ? $t->cnic_back_url : "" }}',
            
            // Camera State
            cameraStream: null,
            activeInputName: '',
            activeGuide: 'face',
            showCameraModal: false,
            cameraDevices: [],
            currentCameraId: null,
            cameraLoading: false,
            cameraError: '',
            
            init() {
                this.partners = this.partners.map(p => ({
                    ...p,
                    delete_passport_photo: p.delete_passport_photo || false,
                    delete_cnic_front_image: p.delete_cnic_front_image || false,
                    delete_cnic_back_image: p.delete_cnic_back_image || false
                }));
                this.$watch('rentedByMultiple', value => {
                    if (value === '0') {
                        this.partners = [];
                    } else if (value === '1' && this.partners.length === 0) {
                        this.addPartner();
                    }
                });
            },
            
            // Previews Helpers
            updateTenantPreview(event, field) {
                const file = event.target.files[0];
                if (file) {
                    this[field] = URL.createObjectURL(file);
                    
                    // Clear the deletion flag
                    const inputName = event.target.name;
                    const deleteEl = document.getElementById(`delete_${inputName}`);
                    if (deleteEl) {
                        deleteEl.value = '0';
                    }
                }
            },
            
            clearTenantFile(inputName, previewField) {
                const inputEl = document.querySelector(`[name="${inputName}"]`);
                if (inputEl) {
                    inputEl.value = '';
                }
                this[previewField] = '';
                
                // Set deletion flag to 1
                const deleteEl = document.getElementById(`delete_${inputName}`);
                if (deleteEl) {
                    deleteEl.value = '1';
                }
            },
            
            updatePartnerPreview(event, index, field) {
                const file = event.target.files[0];
                if (file) {
                    this.partners[index][field] = URL.createObjectURL(file);
                    
                    // Clear deletion flag
                    if (field === 'passport_photo_url') {
                        this.partners[index].delete_passport_photo = false;
                    } else if (field === 'cnic_front_url') {
                        this.partners[index].delete_cnic_front_image = false;
                    } else if (field === 'cnic_back_url') {
                        this.partners[index].delete_cnic_back_image = false;
                    }
                }
            },
            
            clearPartnerFile(index, inputName, field) {
                const inputEl = document.querySelector(`[name="partners[${index}][${inputName}]"]`);
                if (inputEl) {
                    inputEl.value = '';
                }
                this.partners[index][field] = '';
                
                // Set deletion flag
                if (inputName === 'passport_photo') {
                    this.partners[index].delete_passport_photo = true;
                } else if (inputName === 'cnic_front_image') {
                    this.partners[index].delete_cnic_front_image = true;
                } else if (inputName === 'cnic_back_image') {
                    this.partners[index].delete_cnic_back_image = true;
                }
            },
            
            // Camera Scanner Methods
            async startCamera(inputName, guideType) {
                this.activeInputName = inputName;
                this.activeGuide = guideType;
                this.showCameraModal = true;
                this.cameraLoading = true;
                this.cameraError = '';
                
                if (this.cameraStream) {
                    this.cameraStream.getTracks().forEach(track => track.stop());
                    this.cameraStream = null;
                }
                
                try {
                    const devices = await navigator.mediaDevices.enumerateDevices();
                    this.cameraDevices = devices.filter(d => d.kind === 'videoinput');
                    
                    let constraints = {
                        video: {
                            width: { ideal: 1280 },
                            height: { ideal: 720 }
                        }
                    };
                    
                    if (this.currentCameraId) {
                        constraints.video.deviceId = { exact: this.currentCameraId };
                    } else {
                        constraints.video.facingMode = guideType === 'card' ? 'environment' : 'user';
                    }
                    
                    this.cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
                    
                    const activeTrack = this.cameraStream.getVideoTracks()[0];
                    if (activeTrack && activeTrack.getSettings) {
                        const settings = activeTrack.getSettings();
                        if (settings.deviceId && !this.currentCameraId) {
                            this.currentCameraId = settings.deviceId;
                        }
                    }
                    
                    const videoEl = document.getElementById('camera_preview_video');
                    if (videoEl) {
                        videoEl.srcObject = this.cameraStream;
                        videoEl.play().catch(e => console.error("Error playing video:", e));
                    }
                    this.cameraLoading = false;
                } catch (err) {
                    console.error("Error accessing camera:", err);
                    this.cameraError = "Could not access camera. Please verify camera permissions and secure context (HTTPS).";
                    this.cameraLoading = false;
                }
            },
            
            async toggleCamera() {
                if (this.cameraDevices.length <= 1) return;
                const currentIndex = this.cameraDevices.findIndex(d => d.deviceId === this.currentCameraId);
                const nextIndex = (currentIndex + 1) % this.cameraDevices.length;
                this.currentCameraId = this.cameraDevices[nextIndex].deviceId;
                await this.startCamera(this.activeInputName, this.activeGuide);
            },
            
            takeSnapshot() {
                try {
                    console.log("Capture button clicked");
                    const videoEl = document.getElementById('camera_preview_video');
                    if (!videoEl) {
                        alert("Error: Video preview element not found!");
                        return;
                    }
                    
                    const videoWidth = videoEl.videoWidth;
                    const videoHeight = videoEl.videoHeight;
                    if (!videoWidth || !videoHeight) {
                        alert("Error: Video stream is not fully loaded yet.");
                        return;
                    }
                    console.log("Original video dimensions:", videoWidth, videoHeight);
                    
                    // Get the actual display size of the video element inside the container
                    const displayWidth = videoEl.offsetWidth;
                    const displayHeight = videoEl.offsetHeight;
                    console.log("Display dimensions:", displayWidth, displayHeight);
                    
                    // Calculate scale and offsets caused by object-fit: cover
                    const scale = Math.max(displayWidth / videoWidth, displayHeight / videoHeight);
                    const renderedWidth = videoWidth * scale;
                    const renderedHeight = videoHeight * scale;
                    
                    const offsetX = (renderedWidth - displayWidth) / 2;
                    const offsetY = (renderedHeight - displayHeight) / 2;
                    
                    // Guide proportions on the screen
                    const pctW = this.activeGuide === 'face' ? 0.60 : 0.75;
                    const pctH = this.activeGuide === 'face' ? 0.75 : 0.63;
                    
                    const guideWidth = displayWidth * pctW;
                    const guideHeight = displayHeight * pctH;
                    
                    const guideX = (displayWidth - guideWidth) / 2;
                    const guideY = (displayHeight - guideHeight) / 2;
                    
                    // Map the screen guide coordinates back to the raw/intrinsic video resolution coordinates
                    let cropX = (guideX + offsetX) / scale;
                    let cropY = (guideY + offsetY) / scale;
                    let cropW = guideWidth / scale;
                    let cropH = guideHeight / scale;
                    
                    // Constrain the coordinates to prevent out-of-bounds Canvas drawing
                    cropX = Math.max(0, Math.min(videoWidth, cropX));
                    cropY = Math.max(0, Math.min(videoHeight, cropY));
                    cropW = Math.max(1, Math.min(videoWidth - cropX, cropW));
                    cropH = Math.max(1, Math.min(videoHeight - cropY, cropH));
                    
                    console.log("Crop bounds in intrinsic coordinates:", cropX, cropY, cropW, cropH);
                    
                    const canvas = document.createElement('canvas');
                    canvas.width = cropW;
                    canvas.height = cropH;
                    const ctx = canvas.getContext('2d');
                    
                    // Mirror horizontally if front face camera is active
                    if (this.activeGuide === 'face') {
                        ctx.translate(cropW, 0);
                        ctx.scale(-1, 1);
                    }
                    
                    // Draw only the cropped section of the video stream onto the canvas
                    ctx.drawImage(
                        videoEl, 
                        cropX, cropY, cropW, cropH,  // Source sub-rectangle
                        0, 0, cropW, cropH           // Destination canvas rectangle
                    );
                    
                    canvas.toBlob((blob) => {
                        try {
                            if (!blob) {
                                alert("Error: Failed to capture camera image blob.");
                                            return;
                                        }
                                        
                            console.log("Blob created successfully, size:", blob.size);
                            const cleanInputName = this.activeInputName.replace(/[^a-zA-Z0-9_]/g, '_');
                            
                            let file;
                            try {
                                file = new File([blob], `${cleanInputName}_captured.jpg`, { type: 'image/jpeg' });
                            } catch (fileErr) {
                                console.warn("File constructor failed, falling back to Blob:", fileErr);
                                file = blob;
                                file.name = `${cleanInputName}_captured.jpg`;
                            }
                            
                            console.log("Looking up target file input: ", `[name="${this.activeInputName}"]`);
                            const inputEl = document.querySelector(`[name="${this.activeInputName}"]`);
                            if (!inputEl) {
                                alert(`Error: Target input element [name="${this.activeInputName}"] not found on the page!`);
                                return;
                            }
                            
                            try {
                                const dt = new DataTransfer();
                                dt.items.add(file);
                                inputEl.files = dt.files;
                            } catch (dtErr) {
                                alert("This browser does not support programmatic file inputs (DataTransfer API error: " + dtErr.message + "). Please use the 'Upload File' option.");
                                return;
                            }
                            
                            // Clear the deletion flag
                            if (this.activeInputName.startsWith('partners[')) {
                                const match = this.activeInputName.match(/partners\[(\d+)\]\[(\w+)\]/);
                                if (match) {
                                    const index = parseInt(match[1]);
                                    const field = match[2];
                                    if (field === 'passport_photo') {
                                        this.partners[index].delete_passport_photo = false;
                                    } else if (field === 'cnic_front_image') {
                                        this.partners[index].delete_cnic_front_image = false;
                                    } else if (field === 'cnic_back_image') {
                                        this.partners[index].delete_cnic_back_image = false;
                                    }
                                }
                            } else {
                                const deleteEl = document.getElementById(`delete_${this.activeInputName}`);
                                if (deleteEl) {
                                    deleteEl.value = '0';
                                }
                            }
                            
                            inputEl.dispatchEvent(new Event('change', { bubbles: true }));
                            this.closeCamera();
                        } catch (innerErr) {
                            alert("Error inside camera callback: " + innerErr.message + "\nStack: " + innerErr.stack);
                        }
                    }, 'image/jpeg', 0.9);
                } catch (outerErr) {
                    alert("Error during capture: " + outerErr.message + "\nStack: " + outerErr.stack);
                }
            },
            
            closeCamera() {
                if (this.cameraStream) {
                    this.cameraStream.getTracks().forEach(track => track.stop());
                    this.cameraStream = null;
                }
                this.showCameraModal = false;
            },
            
            addPartner() {
                if (this.partners.length < 5) {
                    this.partners.push({
                        name: '',
                        father_name: '',
                        cnic: '',
                        gender: '',
                        marital_status: '',
                        phone: '',
                        whatsapp_number: '',
                        email: '',
                        address: '',
                        occupation: '',
                        monthly_income: '',
                        passport_photo_url: '',
                        cnic_front_url: '',
                        cnic_back_url: '',
                        delete_passport_photo: false,
                        delete_cnic_front_image: false,
                        delete_cnic_back_image: false
                    });
                }
            },
            
            removePartner(index) {
                this.partners.splice(index, 1);
            },
            
            formatPartnerCnic(value) {
                const digits = value.replace(/\D/g, '').slice(0, 13);
                if (digits.length <= 5)  return digits;
                if (digits.length <= 12) return digits.slice(0, 5) + '-' + digits.slice(5);
                return digits.slice(0, 5) + '-' + digits.slice(5, 12) + '-' + digits.slice(12, 13);
            }
        };
    });
});

document.addEventListener('DOMContentLoaded', function () {

    // ── CNIC Auto-Mask (XXXXX-XXXXXXX-X) ──────────────────────────────
    const cnicEl = document.getElementById('cnic_input');
    if (!cnicEl) return;

    function formatCnic(raw) {
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

        const added = fresh.length - old.length;
        this.setSelectionRange(pos + added, pos + added);

        const valid = /^\d{5}-\d{7}-\d$/.test(fresh);
        this.classList.toggle('border-red-400',   !valid && fresh.length > 0);
        this.classList.toggle('border-green-400', valid);
    });

    cnicEl.addEventListener('keydown', function (e) {
        const allowed = ['Backspace','Delete','Tab','ArrowLeft','ArrowRight','Home','End'];
        if (allowed.includes(e.key)) return;
        if (!/^\d$/.test(e.key)) e.preventDefault();
    });

    if (cnicEl.value) {
        cnicEl.value = formatCnic(cnicEl.value);
    }

    // ── AJAX Lookup for Existing Tenant by CNIC ─────────────────────
    const form = cnicEl.closest('form');
    if (!form) return;

    let notificationContainer = document.getElementById('cnic_exists_alert');
    if (!notificationContainer) {
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'cnic_exists_alert';
        notificationContainer.className = 'mt-3 hidden rounded-xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-400 transition-all duration-300';
        cnicEl.parentNode.appendChild(notificationContainer);
    }

    const originalAction = form.action;
    let methodInput = null;

    cnicEl.addEventListener('input', function () {
        const cnic = this.value;
        if (/^\d{5}-\d{7}-\d$/.test(cnic)) {
            fetch(`/ajax/tenant-by-cnic?cnic=${encodeURIComponent(cnic)}`)
                .then(res => res.json())
                .then(data => {
                    if (data.found && notificationContainer) {
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

                            form.action = `/tenants/${data.tenant.id}`;
                            if (!methodInput) {
                                methodInput = document.createElement('input');
                                methodInput.type = 'hidden';
                                methodInput.name = '_method';
                                methodInput.value = 'PUT';
                                form.appendChild(methodInput);
                            }

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

    // ── AJAX Form Submission Interceptor ────────────────────────────
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const submitter = e.submitter;
        const isSaveOnly = submitter && submitter.getAttribute('name') === 'save_only';

        const submitBtns = form.querySelectorAll('button[type="submit"]');
        submitBtns.forEach(btn => {
            btn.disabled = true;
            btn.dataset.originalText = btn.innerHTML;
            btn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-current inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Saving...`;
        });

        form.querySelectorAll('.validation-error-msg').forEach(el => el.remove());
        form.querySelectorAll('.border-red-400').forEach(el => el.classList.remove('border-red-400'));
        form.querySelectorAll('.border-green-400').forEach(el => el.classList.remove('border-green-400'));
        
        const oldAlert = document.getElementById('ajax_general_alert');
        if (oldAlert) oldAlert.remove();

        const formData = new FormData(form);
        if (isSaveOnly) {
            formData.append('save_only', '1');
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.status === 422) {
                return response.json().then(data => {
                    submitBtns.forEach(btn => {
                        btn.disabled = false;
                        btn.innerHTML = btn.dataset.originalText;
                    });

                    let firstErrEl = null;
                    Object.keys(data.errors).forEach(key => {
                        let inputSelector = `[name="${key}"]`;
                        if (key.includes('.')) {
                            const parts = key.split('.');
                            if (parts.length === 3) {
                                inputSelector = `[name="${parts[0]}[${parts[1]}][${parts[2]}]"]`;
                            }
                        }

                        let inputEl = form.querySelector(inputSelector);
                        if (!inputEl && key === 'partners') {
                            inputEl = document.getElementById('partners_error_container');
                        }

                        if (inputEl) {
                            let parentEl = inputEl.parentNode;
                            if (inputEl.type === 'radio') {
                                parentEl = inputEl.closest('.flex.items-center.gap-6') || inputEl.parentNode.parentNode;
                            } else {
                                inputEl.classList.add('border-red-400');
                            }
                            
                            const errPara = document.createElement('p');
                            errPara.className = 'mt-1 text-xs text-red-500 validation-error-msg';
                            errPara.innerText = data.errors[key][0];
                            
                            parentEl.appendChild(errPara);

                            if (!firstErrEl) {
                                firstErrEl = inputEl;
                            }
                        }
                    });

                    if (firstErrEl) {
                        firstErrEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                });
            } else if (!response.ok) {
                throw new Error('Server returned error');
            }

            return response.json().then(data => {
                if (data.success) {
                    if (isSaveOnly) {
                        submitBtns.forEach(btn => {
                            btn.disabled = false;
                            btn.innerHTML = btn.dataset.originalText;
                        });

                        const successAlert = document.createElement('div');
                        successAlert.id = 'ajax_general_alert';
                        successAlert.className = 'mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400';
                        successAlert.innerText = data.message;
                        form.parentNode.insertBefore(successAlert, form);
                        successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });

                        if (data.tenant && data.tenant.id) {
                            form.action = `/tenants/${data.tenant.id}`;
                            if (!form.querySelector('input[name="_method"]')) {
                                const methodInput = document.createElement('input');
                                methodInput.type = 'hidden';
                                methodInput.name = '_method';
                                methodInput.value = 'PUT';
                                form.appendChild(methodInput);
                            }
                            
                            window.history.pushState(null, '', `/tenants/${data.tenant.id}/step/1`);

                            const breadcrumbs = document.querySelector('.mb-6.flex.items-center.gap-2.text-sm');
                            if (breadcrumbs) {
                                breadcrumbs.innerHTML = `
                                    <a href="/tenants" class="hover:text-brand-500">Tenants and Agreements</a>
                                    <span>/</span>
                                    <a href="/tenants/${data.tenant.id}" class="hover:text-brand-500">${data.tenant.name}</a>
                                    <span>/</span>
                                    <span class="text-gray-800 dark:text-white/90">Add Tenant — Step 1</span>
                                `;
                            }

                            let bannerContainer = document.getElementById('tenant_photo_banner_container');
                            if (!bannerContainer) {
                                const progressNav = document.querySelector('[class*="flex items-center justify-between rounded-xl"]');
                                bannerContainer = document.createElement('div');
                                bannerContainer.id = 'tenant_photo_banner_container';
                                if (progressNav && progressNav.parentNode) {
                                    progressNav.parentNode.insertBefore(bannerContainer, progressNav.nextSibling);
                                } else {
                                    form.parentNode.insertBefore(bannerContainer, form);
                                }
                            }

                            const photoSrc = data.tenant.passport_photo_url || '';
                            const initials = data.tenant.name.substring(0, 1).toUpperCase();
                            const unitText = data.tenant.unit 
                                ? `📍 ${data.tenant.unit.unit_number} — ${data.tenant.unit.floor_name || ''} / ${data.tenant.unit.block_name || ''}`
                                : '';

                            bannerContainer.innerHTML = `
                                <div class="mb-5 flex items-center gap-4 rounded-xl border border-gray-100 bg-gray-50 px-5 py-4 dark:border-gray-800 dark:bg-white/[0.02]">
                                    ${photoSrc 
                                        ? `<img src="${photoSrc}" class="h-14 w-14 rounded-full object-cover border-2 border-brand-200 shadow flex-shrink-0" alt="${data.tenant.name}">`
                                        : `<div class="h-14 w-14 rounded-full bg-brand-100 dark:bg-brand-900/30 flex items-center justify-center flex-shrink-0 border-2 border-brand-200"><span class="text-xl font-bold text-brand-600 dark:text-brand-400">${initials}</span></div>`
                                    }
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white/90">${data.tenant.name}</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">CNIC: ${data.tenant.cnic}</p>
                                        ${unitText ? `<p class="text-xs text-brand-600 dark:text-brand-400 mt-0.5">${unitText}</p>` : ''}
                                    </div>
                                </div>
                            `;

                            const cardHeader = form.parentNode.querySelector('.border-b h1');
                            if (cardHeader) cardHeader.innerText = 'Tenant Application Form';
                        }
                    } else {
                        window.location.href = data.redirect_url;
                    }
                }
            });
        })
        .catch(err => {
            console.error('Error submitting form via AJAX:', err);
            submitBtns.forEach(btn => {
                btn.disabled = false;
                btn.innerHTML = btn.dataset.originalText;
            });

            const errorAlert = document.createElement('div');
            errorAlert.id = 'ajax_general_alert';
            errorAlert.className = 'mb-6 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400';
            errorAlert.innerText = 'An unexpected error occurred while saving the form. Please try again.';
            form.parentNode.insertBefore(errorAlert, form);
            errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
    });

});
</script>
@endpush
@endonce
