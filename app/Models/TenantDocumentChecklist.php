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
        'cnic_copy_tenant', 'cnic_copy_father', 'cnic_copy_guarantor',
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
        'notes',
    ];

    protected $casts = [
        'cnic_copy_tenant'         => 'boolean',
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

    public function getCnicFrontUrlAttribute(): ?string
    {
        return $this->cnic_front_image ? Storage::disk('public')->url($this->cnic_front_image) : null;
    }

    public function getCnicBackUrlAttribute(): ?string
    {
        return $this->cnic_back_image ? Storage::disk('public')->url($this->cnic_back_image) : null;
    }
}
