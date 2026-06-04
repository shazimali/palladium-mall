<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TenantDocumentChecklist extends Model
{
    protected $table = 'tenant_document_checklists';

    protected $fillable = [
        'tenant_id',
        // Basic Identity
        'cnic_copy_tenant_front', 'cnic_copy_tenant_back', 'cnic_copy_father', 'cnic_copy_guarantor',
        'passport_photo', 'nikah_nama', 'frc_form_b', 'police_verification',
        // Application & Agreement
        'tenant_application_form', 'tenancy_agreement_copy', 'rules_acknowledgment',
        // Property & Security
        'inspection_report', 'property_handover_form', 'security_deposit_receipt', 'meter_picture',
        // Contact & References
        'emergency_contacts_added', 'guarantor_info_added', 'guarantor_business_card',
        'tenant_business_card', 'property_advisor_card', 'old_tenant_verification',
        // Commercial Only
        'business_license', 'utility_bills_clearance',
        // File uploads
        'cnic_front_image', 'cnic_back_image', 'signed_agreement_scan', 'bank_voucher',
        'cnic_copy_father_file', 'cnic_copy_guarantor_file', 'passport_photo_file',
        'nikah_nama_file', 'frc_form_b_file', 'police_verification_file',
        'tenant_application_form_file', 'rules_acknowledgment_file',
        'inspection_report_file', 'property_handover_form_file', 'meter_picture_file',
        'emergency_contacts_added_file', 'guarantor_info_added_file',
        'guarantor_business_card_file', 'tenant_business_card_file',
        'property_advisor_card_file', 'old_tenant_verification_file',
        'business_license_file', 'utility_bills_clearance_file',
        'notes',
    ];

    protected $casts = [
        'cnic_copy_tenant_front'   => 'boolean',
        'cnic_copy_tenant_back'    => 'boolean',
        'cnic_copy_father'         => 'boolean',
        'cnic_copy_guarantor'      => 'boolean',
        'passport_photo'           => 'boolean',
        'nikah_nama'               => 'boolean',
        'frc_form_b'               => 'boolean',
        'police_verification'      => 'boolean',
        'tenant_application_form'  => 'boolean',
        'tenancy_agreement_copy'   => 'boolean',
        'rules_acknowledgment'     => 'boolean',
        'inspection_report'        => 'boolean',
        'property_handover_form'   => 'boolean',
        'security_deposit_receipt' => 'boolean',
        'meter_picture'            => 'boolean',
        'emergency_contacts_added' => 'boolean',
        'guarantor_info_added'     => 'boolean',
        'guarantor_business_card'  => 'boolean',
        'tenant_business_card'     => 'boolean',
        'property_advisor_card'    => 'boolean',
        'old_tenant_verification'  => 'boolean',
        'business_license'         => 'boolean',
        'utility_bills_clearance'  => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Count how many boolean checklist items are ticked.
     */
    public function countChecked(): int
    {
        $booleans = array_keys(array_filter($this->casts, fn($t) => $t === 'boolean'));
        return collect($booleans)->filter(fn($col) => (bool) $this->{$col})->count();
    }

    public function countTotal(): int
    {
        return count(array_filter($this->casts, fn($t) => $t === 'boolean'));
    }

    /**
     * Get the file URL for a given file attribute.
     */
    public function getFileUrl(string $attribute): ?string
    {
        $path = $this->{$attribute};
        return $path ? Storage::disk('public')->url($path) : null;
    }

    public function getCnicFrontUrlAttribute(): ?string
    {
        return $this->getFileUrl('cnic_front_image');
    }

    public function getCnicBackUrlAttribute(): ?string
    {
        return $this->getFileUrl('cnic_back_image');
    }

    public function getSignedAgreementScanUrlAttribute(): ?string
    {
        return $this->getFileUrl('signed_agreement_scan');
    }

    public function getBankVoucherUrlAttribute(): ?string
    {
        return $this->getFileUrl('bank_voucher');
    }

    public function getCnicCopyFatherFileUrlAttribute(): ?string { return $this->getFileUrl('cnic_copy_father_file'); }
    public function getCnicCopyGuarantorFileUrlAttribute(): ?string { return $this->getFileUrl('cnic_copy_guarantor_file'); }
    public function getPassportPhotoFileUrlAttribute(): ?string { return $this->getFileUrl('passport_photo_file'); }
    public function getNikahNamaFileUrlAttribute(): ?string { return $this->getFileUrl('nikah_nama_file'); }
    public function getFrcFormBFileUrlAttribute(): ?string { return $this->getFileUrl('frc_form_b_file'); }
    public function getPoliceVerificationFileUrlAttribute(): ?string { return $this->getFileUrl('police_verification_file'); }
    public function getTenantApplicationFormFileUrlAttribute(): ?string { return $this->getFileUrl('tenant_application_form_file'); }
    public function getRulesAcknowledgmentFileUrlAttribute(): ?string { return $this->getFileUrl('rules_acknowledgment_file'); }
    public function getInspectionReportFileUrlAttribute(): ?string { return $this->getFileUrl('inspection_report_file'); }
    public function getPropertyHandoverFormFileUrlAttribute(): ?string { return $this->getFileUrl('property_handover_form_file'); }
    public function getMeterPictureFileUrlAttribute(): ?string { return $this->getFileUrl('meter_picture_file'); }
    public function getEmergencyContactsAddedFileUrlAttribute(): ?string { return $this->getFileUrl('emergency_contacts_added_file'); }
    public function getGuarantorInfoAddedFileUrlAttribute(): ?string { return $this->getFileUrl('guarantor_info_added_file'); }
    public function getGuarantorBusinessCardFileUrlAttribute(): ?string { return $this->getFileUrl('guarantor_business_card_file'); }
    public function getTenantBusinessCardFileUrlAttribute(): ?string { return $this->getFileUrl('tenant_business_card_file'); }
    public function getPropertyAdvisorCardFileUrlAttribute(): ?string { return $this->getFileUrl('property_advisor_card_file'); }
    public function getOldTenantVerificationFileUrlAttribute(): ?string { return $this->getFileUrl('old_tenant_verification_file'); }
    public function getBusinessLicenseFileUrlAttribute(): ?string { return $this->getFileUrl('business_license_file'); }
    public function getUtilityBillsClearanceFileUrlAttribute(): ?string { return $this->getFileUrl('utility_bills_clearance_file'); }
}
