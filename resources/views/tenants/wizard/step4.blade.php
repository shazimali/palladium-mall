@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-4 py-6">

    <div class="mb-6 flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
        <a href="{{ route('tenants.index') }}" class="hover:text-brand-500">Tenants and Agreements</a>
        <span>/</span>
        <span class="text-gray-800 dark:text-white/90">{{ $title }}</span>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    @include('tenants.wizard._progress', ['currentStep' => $step, 'tenantId' => $tenant->id])
    @include('tenants.wizard._tenant_banner')

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
        <div class="border-b border-gray-100 px-6 py-5 dark:border-gray-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-lg font-semibold text-gray-900 dark:text-white/90">Step 4 — Documents Checklist</h1>
                <p class="mt-0.5 text-sm text-gray-500 dark:text-gray-400">Upload documents to verify checklist items. Checkboxes are checked automatically when a file is uploaded.</p>
            </div>
            <div>
                <a href="{{ route('tenants.printStep', [$tenant, 4]) }}" target="_blank"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Print Checklist for Client
                </a>
            </div>
        </div>

        <form method="POST" action="{{ route('tenants.saveStep', [$tenant, 4]) }}" enctype="multipart/form-data" class="px-6 py-6 space-y-6">
            @csrf

            @php
            $cl = $checklist;
            $checkboxClass = 'h-4 w-4 rounded border-gray-300 text-brand-500 focus:ring-brand-500 dark:border-gray-600';
            $sectionClass = 'rounded-xl border border-gray-100 bg-gray-50 p-5 dark:border-gray-800 dark:bg-white/[0.02]';
            $sectionTitle = 'mb-4 text-sm font-semibold uppercase tracking-wide text-gray-700 dark:text-gray-300';
            $input = 'w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm text-gray-800 placeholder-gray-400 focus:border-brand-500 focus:outline-none focus:ring-1 focus:ring-brand-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder-gray-600';

            // Define the items for each section
            $basicIdentity = [
                ['field' => 'cnic_copy_tenant_front', 'file' => 'cnic_front_image', 'url' => $cl?->cnic_front_url, 'label' => 'CNIC Copy — Tenant (Front)'],
                ['field' => 'cnic_copy_tenant_back', 'file' => 'cnic_back_image', 'url' => $cl?->cnic_back_url, 'label' => 'CNIC Copy — Tenant (Back)'],
                ['field' => 'cnic_copy_father', 'file' => 'cnic_copy_father_file', 'url' => $cl?->cnic_copy_father_file_url, 'label' => 'CNIC Copy — Father / Husband'],
                ['field' => 'cnic_copy_guarantor', 'file' => 'cnic_copy_guarantor_file', 'url' => $cl?->cnic_copy_guarantor_file_url, 'label' => 'CNIC Copy — Guarantor'],
                ['field' => 'passport_photo', 'file' => 'passport_photo_file', 'url' => $cl?->passport_photo_file_url, 'label' => 'Passport Size Photograph'],
                ['field' => 'nikah_nama', 'file' => 'nikah_nama_file', 'url' => $cl?->nikah_nama_file_url, 'label' => 'Nikah Nama (Computerized)'],
                ['field' => 'frc_form_b', 'file' => 'frc_form_b_file', 'url' => $cl?->frc_form_b_file_url, 'label' => 'FRC / Form-B'],
                ['field' => 'police_verification', 'file' => 'police_verification_file', 'url' => $cl?->police_verification_file_url, 'label' => 'Police Verification Certificate'],
            ];

            $applicationAgreement = [
                ['field' => 'tenant_application_form', 'file' => 'tenant_application_form_file', 'url' => $cl?->tenant_application_form_file_url, 'label' => 'Tenant Application Form'],
                ['field' => 'tenancy_agreement_copy', 'file' => 'signed_agreement_scan', 'url' => $cl?->signed_agreement_scan_url, 'label' => 'Tenancy Agreement Scan'],
                ['field' => 'rules_acknowledgment', 'file' => 'rules_acknowledgment_file', 'url' => $cl?->rules_acknowledgment_file_url, 'label' => 'Rules Acknowledgment Signed'],
            ];

            $propertySecurity = [
                ['field' => 'inspection_report', 'file' => 'inspection_report_file', 'url' => $cl?->inspection_report_file_url, 'label' => 'Inspection Report'],
                ['field' => 'property_handover_form', 'file' => 'property_handover_form_file', 'url' => $cl?->property_handover_form_file_url, 'label' => 'Property Handover Form'],
                ['field' => 'security_deposit_receipt', 'file' => 'bank_voucher', 'url' => $cl?->bank_voucher_url, 'label' => 'Security Deposit / Voucher'],
                ['field' => 'meter_picture', 'file' => 'meter_picture_file', 'url' => $cl?->meter_picture_file_url, 'label' => 'Meter Picture'],
            ];

            $contactReferences = [
                ['field' => 'emergency_contacts_added', 'file' => 'emergency_contacts_added_file', 'url' => $cl?->emergency_contacts_added_file_url, 'label' => 'Emergency Contacts Added'],
                ['field' => 'guarantor_info_added', 'file' => 'guarantor_info_added_file', 'url' => $cl?->guarantor_info_added_file_url, 'label' => 'Guarantor Info Added'],
                ['field' => 'guarantor_business_card', 'file' => 'guarantor_business_card_file', 'url' => $cl?->guarantor_business_card_file_url, 'label' => 'Guarantor Business Card'],
                ['field' => 'tenant_business_card', 'file' => 'tenant_business_card_file', 'url' => $cl?->tenant_business_card_file_url, 'label' => 'Tenant Business Card'],
                ['field' => 'property_advisor_card', 'file' => 'property_advisor_card_file', 'url' => $cl?->property_advisor_card_file_url, 'label' => 'Property Advisor Card'],
                ['field' => 'old_tenant_verification', 'file' => 'old_tenant_verification_file', 'url' => $cl?->old_tenant_verification_file_url, 'label' => 'Old Tenant Verification'],
            ];

            $commercialUnits = [
                ['field' => 'business_license', 'file' => 'business_license_file', 'url' => $cl?->business_license_file_url, 'label' => 'Business License'],
                ['field' => 'utility_bills_clearance', 'file' => 'utility_bills_clearance_file', 'url' => $cl?->utility_bills_clearance_file_url, 'label' => 'Utility Bills Clearance'],
            ];
            @endphp

            {{-- Helper function to render a section's items --}}
            @php
            $renderChecklistGroup = function($items) use ($checkboxClass, $cl) {
                echo '<div class="grid grid-cols-1 gap-4 md:grid-cols-2">';
                foreach ($items as $item) {
                    $isChecked = old($item['field'], $cl?->{$item['field']} ?? false);
                    $fileUrl = $item['url'];

                    echo '<div class="flex flex-col justify-between gap-3 p-4 rounded-xl border border-gray-200 bg-white shadow-theme-xs dark:border-gray-800 dark:bg-gray-900/40">';
                    
                    // Top: Checkbox & Label (fully visible, wraps naturally)
                    echo '    <div class="flex items-start gap-3">';
                    echo '        <input type="checkbox" onclick="return false;" name="' . $item['field'] . '" value="1" class="' . $checkboxClass . ' mt-0.5 flex-shrink-0" id="check_' . $item['field'] . '" ' . ($isChecked ? 'checked' : '') . '>';
                    echo '        <label for="check_' . $item['field'] . '" class="text-sm font-semibold text-gray-700 dark:text-gray-300 cursor-pointer leading-tight">';
                    echo '            ' . $item['label'];
                    echo '        </label>';
                    echo '    </div>';
                    
                    // Bottom: File Status and Upload Input
                    echo '    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mt-1 pt-3 border-t border-gray-100 dark:border-gray-800/60">';
                    echo '        <div class="flex-shrink-0">';
                    if ($fileUrl) {
                        echo '            <a href="' . $fileUrl . '" target="_blank" ';
                        echo '               class="inline-flex h-7 items-center gap-1 rounded-md border border-gray-200 bg-white px-2.5 text-xs font-medium text-brand-600 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-brand-400 dark:hover:bg-gray-700 transition-colors">';
                        echo '                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">';
                        echo '                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>';
                        echo '                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>';
                        echo '                </svg>';
                        echo '                View File';
                        echo '            </a>';
                    } else {
                        echo '            <span class="inline-flex items-center gap-1 rounded bg-red-50 px-1.5 py-0.5 text-xs font-medium text-red-600 dark:bg-red-900/20 dark:text-red-400">';
                        echo '                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">';
                        echo '                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>';
                        echo '                </svg>';
                        echo '                Missing File';
                        echo '            </span>';
                    }
                    echo '        </div>';
                    echo '        <div class="relative">';
                    echo '            <input type="file" name="' . $item['file'] . '" accept="image/jpeg,image/png,application/pdf"';
                    echo '                   class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100 dark:file:bg-gray-800 dark:file:text-gray-300">';
                    echo '        </div>';
                    echo '    </div>';
                    
                    echo '</div>';
                }
                echo '</div>';
            };
            @endphp

            {{-- ── Basic Identity ─────────────────────────────────────────── --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Basic Identity</h4>
                @php $renderChecklistGroup($basicIdentity); @endphp
            </div>

            {{-- ── Application & Agreement ──────────────────────────────── --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Application & Agreement</h4>
                @php $renderChecklistGroup($applicationAgreement); @endphp
            </div>

            {{-- ── Property & Security ──────────────────────────────────── --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Property & Security</h4>
                @php $renderChecklistGroup($propertySecurity); @endphp
            </div>

            {{-- ── Contact & References ─────────────────────────────────── --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Contact & References</h4>
                @php $renderChecklistGroup($contactReferences); @endphp
            </div>

            {{-- ── Commercial Only ──────────────────────────────────────── --}}
            <div class="{{ $sectionClass }}">
                <h4 class="{{ $sectionTitle }}">Commercial Units Only</h4>
                @php $renderChecklistGroup($commercialUnits); @endphp
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
                <div class="flex items-center gap-3">
                    {{-- Save Only --}}
                    <button type="submit" name="save_only" value="1"
                        class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700 transition-colors">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Only
                    </button>
                    <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 transition-colors">
                        Continue — Step 5
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@once
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select all file inputs inside checklist items
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function () {
            if (this.files && this.files.length > 0) {
                // Find the corresponding checkbox in the same checklist item card
                const container = this.closest('.rounded-xl');
                if (container) {
                    const checkbox = container.querySelector('input[type="checkbox"]');
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                }
            }
        });
    });
});
</script>
@endpush
@endonce
