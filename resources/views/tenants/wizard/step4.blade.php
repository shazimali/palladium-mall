@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">

    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants</a>
        <span>/</span>
        <span class="text-gray-800 dark:text-white/90">{{ $title }}</span>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @include('tenants.wizard._progress', ['currentStep' => $step, 'tenantId' => $tenant->id])

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800">
            <h1 class="text-lg font-semibold text-gray-900 dark:text-white/90">Step 4 — Documents Checklist</h1>
            <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Tick documents received and upload key files.</p>
        </div>

        <form method="POST" action="{{ route('tenants.saveStep', [$tenant, 4]) }}" enctype="multipart/form-data" class="px-6 py-6 space-y-6">
            @csrf

            @php
            $cl = $checklist;
            $checkboxClass = 'h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600';
            $sectionClass = 'rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]';
            $sectionTitle = 'mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300';
            $checkLabel = 'flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer';
            $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';
            @endphp

            {{-- ── Basic Identity ─────────────────────────────────────────── --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Basic Identity</h4>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach([
                        'cnic_copy_tenant'    => 'CNIC Copy — Tenant',
                        'cnic_copy_father'    => 'CNIC Copy — Father / Husband',
                        'cnic_copy_guarantor' => 'CNIC Copy — Guarantor',
                        'passport_photo'      => 'Passport Size Photograph (2 photos)',
                        'nikah_nama'          => 'Nikah Nama (Computerized)',
                        'frc_form_b'          => 'Family Registration Certificate (FRC / Form-B)',
                        'police_verification' => 'Police Verification Certificate',
                    ] as $field => $label)
                        <label class="{{ $checkLabel }}">
                            <input type="checkbox" name="{{ $field }}" value="1" class="{{ $checkboxClass }}"
                                   {{ old($field, $cl->{$field} ?? false) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ── Application & Agreement ──────────────────────────────── --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Application & Agreement</h4>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach([
                        'tenant_application_form' => 'Tenant Application Form — Filled & Signed',
                        'tenancy_agreement_copy'  => 'Tenancy Agreement / Lease Affidavit — Signed',
                        'rules_acknowledgment'    => 'Rules & Regulations Acknowledgment — Signed',
                    ] as $field => $label)
                        <label class="{{ $checkLabel }}">
                            <input type="checkbox" name="{{ $field }}" value="1" class="{{ $checkboxClass }}"
                                   {{ old($field, $cl->{$field} ?? false) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ── Property & Security ──────────────────────────────────── --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Property & Security</h4>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach([
                        'inspection_report'       => 'Flat / Shop Inspection Report',
                        'property_handover_form'  => 'Property Handover Form (with condition noted)',
                        'security_deposit_receipt'=> 'Security Deposit Receipt (with amount and date)',
                        'meter_picture'           => 'Meter Picture — Before Possession',
                    ] as $field => $label)
                        <label class="{{ $checkLabel }}">
                            <input type="checkbox" name="{{ $field }}" value="1" class="{{ $checkboxClass }}"
                                   {{ old($field, $cl->{$field} ?? false) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ── Contact & References ─────────────────────────────────── --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Contact & References</h4>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach([
                        'emergency_contacts_added'=> 'Emergency Contacts Added (at least 2)',
                        'guarantor_info_added'    => 'Guarantor Information Added',
                        'guarantor_business_card' => 'Guarantor Business Card',
                        'tenant_business_card'    => 'Tenant Business Card',
                        'property_advisor_card'   => 'Property Advisor Visiting Card',
                        'old_tenant_verification' => 'Old Tenant Verification Report',
                    ] as $field => $label)
                        <label class="{{ $checkLabel }}">
                            <input type="checkbox" name="{{ $field }}" value="1" class="{{ $checkboxClass }}"
                                   {{ old($field, $cl->{$field} ?? false) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ── Commercial Only ──────────────────────────────────────── --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Commercial Units Only</h4>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    @foreach([
                        'business_license'       => 'Business Registration / License',
                        'utility_bills_clearance'=> 'Utility Bills Clearance (shifting from another unit)',
                    ] as $field => $label)
                        <label class="{{ $checkLabel }}">
                            <input type="checkbox" name="{{ $field }}" value="1" class="{{ $checkboxClass }}"
                                   {{ old($field, $cl->{$field} ?? false) ? 'checked' : '' }}>
                            {{ $label }}
                        </label>
                    @endforeach
                </div>
            </div>

            {{-- ── File Uploads ──────────────────────────────────────────── --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">File Uploads</h4>
                <p class="mb-4 text-xs text-gray-400">JPEG, PNG, or PDF. Max 5MB each.</p>
                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                    @foreach([
                        'cnic_front_image'      => ['label' => 'CNIC Front Image', 'url' => $cl?->cnic_front_url ?? null],
                        'cnic_back_image'       => ['label' => 'CNIC Back Image',  'url' => $cl?->cnic_back_url ?? null],
                        'signed_agreement_scan' => ['label' => 'Signed Agreement Scan', 'url' => null],
                        'bank_voucher'          => ['label' => 'Bank / Cash Voucher', 'url' => null],
                    ] as $field => $meta)
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">{{ $meta['label'] }}</label>
                            @if($meta['url'])
                                <div class="mb-2">
                                    <a href="{{ $meta['url'] }}" target="_blank" class="text-xs text-brand-500 hover:underline">View current file</a>
                                </div>
                            @endif
                            <input type="file" name="{{ $field }}" accept="image/jpeg,image/png,application/pdf"
                                   class="{{ $input }} file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-1 file:text-xs file:font-medium file:text-brand-600 hover:file:bg-brand-100">
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ── Notes ────────────────────────────────────────────────── --}}
            <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-300">Notes / Remarks</label>
                <textarea name="notes" rows="3" placeholder="Any additional notes about documents..."
                    class="{{ $input }}">{{ old('notes', $cl?->notes ?? '') }}</textarea>
            </div>

            {{-- Nav --}}
            <div class="flex items-center justify-between pt-2">
                <a href="{{ route('tenants.showStep', [$tenant, 3]) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back
                </a>
                <button type="submit"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                    Continue — Step 5
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

        </form>
    </div>
</div>
@endsection
