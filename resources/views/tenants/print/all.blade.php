<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Global Profile & Agreement - {{ $tenant->name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 30px; font-size: 13px; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header-info { flex-grow: 1; text-align: left; }
        .header-info.centered { text-align: center; }
        .header h1 { margin: 0; font-size: 22px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; font-size: 12px; }
        .tenant-photo { width: 80px; height: 80px; border-radius: 4px; object-fit: cover; border: 1px solid #ccc; margin-left: 20px; }
        .section-title { font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 4px; text-transform: uppercase; page-break-after: avoid; }
        .grid { display: grid; grid-template-cols: 1fr 1fr; gap: 15px; margin-bottom: 20px; page-break-inside: avoid; }
        .item { display: flex; border-bottom: 1px dashed #eee; padding-bottom: 5px; }
        .label { font-weight: bold; width: 180px; color: #555; }
        .value { flex-grow: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; page-break-inside: avoid; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; font-size: 12px; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .signature-area { margin-top: 50px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sig-box { border-top: 1px solid #333; width: 220px; text-align: center; padding-top: 5px; }
        .cnic-container { display: flex; gap: 20px; margin-top: 10px; margin-bottom: 20px; page-break-inside: avoid; }
        .cnic-box { flex: 1; border: 1px solid #ccc; border-radius: 4px; padding: 10px; text-align: center; background-color: #fafafa; min-height: 120px; display: flex; flex-direction: column; justify-content: center; align-items: center; page-break-inside: avoid; }
        .cnic-box.dashed { border-style: dashed; }
        .cnic-title { font-weight: bold; margin-bottom: 8px; color: #555; font-size: 13px; }
        .cnic-img { max-width: 100%; max-height: 110px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px; }
        .cnic-warning { color: #d9534f; font-weight: bold; font-size: 13px; }
        .guarantor-card { margin-bottom: 30px; page-break-inside: avoid; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .guarantor-card:last-child { border-bottom: none; }
        .terms-box { border: 1px solid #ddd; padding: 15px; background-color: #f9f9f9; min-height: 100px; white-space: pre-line; line-height: 1.6; }
        .info-grid { display: grid; grid-template-cols: 1fr 1fr; gap: 15px; margin-bottom: 15px; background: #f9f9f9; padding: 12px; border-radius: 6px; border: 1px solid #eee; }
        .info-item { display: flex; }
        .info-label { font-weight: bold; width: 150px; color: #555; }
        .info-value { flex-grow: 1; }
        .checklist-grid { display: grid; grid-template-cols: 1fr 1fr; gap: 10px; margin-bottom: 15px; }
        .checklist-item { display: flex; align-items: center; }
        .check-box { font-size: 16px; margin-right: 8px; font-weight: bold; color: #555; width: 18px; }
        .notes-area { display: grid; grid-template-cols: 1fr; gap: 10px; margin-top: 15px; }
        .notes-box { border: 1px solid #ddd; border-radius: 6px; padding: 10px; min-height: 60px; background: #fff; }
        .notes-title { font-weight: bold; margin-bottom: 5px; color: #555; }
        .status-badge { font-size: 11px; font-weight: bold; padding: 2px 6px; border-radius: 3px; display: inline-block; }
        .status-submitted { background-color: #e6f4ea; color: #137333; border: 1px solid #c2e7c9; }
        .status-pending { background-color: #fce8e6; color: #c5221f; border: 1px solid #fad2cf; }
        .checkbox-cell { text-align: center; width: 40px; font-size: 16px; }
        .page-break { page-break-before: always; }
        @media print {
            body { margin: 15px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- PAGE 1: TENANT PERSONAL INFORMATION -->
    <div class="header">
        <div class="header-info {{ $tenant->passport_photo ? '' : 'centered' }}">
            <h1>Tenant & Emergency Contacts Information</h1>
            <p>Palladium Mall Tenant Management System</p>
        </div>
        @if($tenant->passport_photo)
            <img src="{{ $tenant->passport_photo_url }}" class="tenant-photo" alt="Tenant Photo">
        @endif
    </div>

    <div class="section-title">Personal Details</div>
    <div class="grid">
        <div class="item"><span class="label">Full Name:</span><span class="value">{{ $tenant->name }}</span></div>
        <div class="item"><span class="label">Father/Husband Name:</span><span class="value">{{ $tenant->father_name ?? 'N/A' }}</span></div>
        <div class="item"><span class="label">CNIC Number:</span><span class="value">{{ $tenant->cnic }}</span></div>
        <div class="item"><span class="label">Date of Birth:</span><span class="value">{{ optional($tenant->date_of_birth)->format('d M Y') ?? 'N/A' }}</span></div>
        <div class="item"><span class="label">Gender:</span><span class="value">{{ ucfirst($tenant->gender ?? 'N/A') }}</span></div>
        <div class="item"><span class="label">Marital Status:</span><span class="value">{{ ucfirst($tenant->marital_status ?? 'N/A') }}</span></div>
    </div>

    <div class="section-title">Contact Information</div>
    <div class="grid">
        <div class="item"><span class="label">Phone:</span><span class="value">{{ $tenant->phone }}</span></div>
        <div class="item"><span class="label">WhatsApp:</span><span class="value">{{ $tenant->whatsapp_number ?? 'N/A' }}</span></div>
        <div class="item"><span class="label">Email:</span><span class="value">{{ $tenant->email ?? 'N/A' }}</span></div>
        <div class="item"><span class="label">Occupation:</span><span class="value">{{ $tenant->occupation ?? 'N/A' }}</span></div>
        <div class="item" style="grid-column: span 2;"><span class="label">Permanent Address:</span><span class="value">{{ $tenant->address }}</span></div>
    </div>

    <div class="section-title">Additional Details</div>
    <div class="grid">
        <div class="item"><span class="label">Monthly Income:</span><span class="value">{{ $tenant->monthly_income ? number_format($tenant->monthly_income) . ' PKR' : 'N/A' }}</span></div>
        <div class="item"><span class="label">Tenancy Type:</span><span class="value">{{ ucfirst($tenant->tenancy_type ?? 'N/A') }}</span></div>
        <div class="item"><span class="label">Adults in Family:</span><span class="value">{{ $tenant->adults_count ?? 1 }}</span></div>
        <div class="item"><span class="label">Children in Family:</span><span class="value">{{ $tenant->children_count ?? 0 }}</span></div>
    </div>

    <div class="section-title">Assigned Flat / Shop Details</div>
    <div class="grid">
        <div class="item"><span class="label">Flat/Shop Number:</span><span class="value"><strong>{{ $tenant->unit ? $tenant->unit->unit_number : 'Not Assigned' }}</strong></span></div>
        <div class="item"><span class="label">Type:</span><span class="value">{{ $tenant->unit ? ucfirst($tenant->unit->type) : 'N/A' }}</span></div>
        <div class="item"><span class="label">Floor:</span><span class="value">{{ $tenant->unit?->floor?->name ?? 'N/A' }}</span></div>
        <div class="item"><span class="label">Block:</span><span class="value">{{ $tenant->unit?->block?->name ?? 'N/A' }}</span></div>
        @if($tenant->unit?->area)
            <div class="item"><span class="label">Area / Zone:</span><span class="value">{{ $tenant->unit->area->name }}</span></div>
        @endif
        @if($tenant->unit?->area_sqft)
            <div class="item"><span class="label">Size (sqft):</span><span class="value">{{ number_format($tenant->unit->area_sqft, 2) }}</span></div>
        @endif
    </div>

    <div class="section-title">Tenant CNIC Documents</div>
    <div class="cnic-container">
        <div class="cnic-box">
            <div class="cnic-title">CNIC Front</div>
            @if($tenant->cnic_front_image)
                <img src="{{ $tenant->cnic_front_url }}" class="cnic-img" alt="CNIC Front">
            @else
                <div class="cnic-warning">⚠️ CNIC Front Image Needed</div>
            @endif
        </div>
        <div class="cnic-box">
            <div class="cnic-title">CNIC Back</div>
            @if($tenant->cnic_back_image)
                <img src="{{ $tenant->cnic_back_url }}" class="cnic-img" alt="CNIC Back">
            @else
                <div class="cnic-warning">⚠️ CNIC Back Image Needed</div>
            @endif
        </div>
    </div>

    @if(($partners ?? $tenant->partners) && ($partners ?? $tenant->partners)->isNotEmpty())
        @foreach(($partners ?? $tenant->partners) as $index => $partner)
            <div class="page-break"></div>
            <div class="header">
                <div class="header-info {{ $partner->passport_photo ? '' : 'centered' }}">
                    <h1>Partner #{{ $index + 1 }} Details</h1>
                    <p>Tenant: {{ $tenant->name }} | Palladium Mall</p>
                </div>
                @if($partner->passport_photo)
                    <img src="{{ $partner->passport_photo_url }}" class="tenant-photo" alt="Partner Photo">
                @endif
            </div>
            <div class="grid">
                <div class="item"><span class="label">Full Name:</span><span class="value">{{ $partner->name }}</span></div>
                <div class="item"><span class="label">Father/Husband Name:</span><span class="value">{{ $partner->father_name ?? 'N/A' }}</span></div>
                <div class="item"><span class="label">CNIC Number:</span><span class="value">{{ $partner->cnic }}</span></div>
                <div class="item"><span class="label">Gender:</span><span class="value">{{ ucfirst($partner->gender ?? 'N/A') }}</span></div>
                <div class="item"><span class="label">Marital Status:</span><span class="value">{{ ucfirst($partner->marital_status ?? 'N/A') }}</span></div>
                <div class="item"><span class="label">Phone:</span><span class="value">{{ $partner->phone }}</span></div>
                <div class="item"><span class="label">WhatsApp:</span><span class="value">{{ $partner->whatsapp_number ?? 'N/A' }}</span></div>
                <div class="item"><span class="label">Email:</span><span class="value">{{ $partner->email ?? 'N/A' }}</span></div>
                <div class="item"><span class="label">Occupation:</span><span class="value">{{ $partner->occupation ?? 'N/A' }}</span></div>
                <div class="item"><span class="label">Monthly Income:</span><span class="value">{{ $partner->monthly_income ? number_format($partner->monthly_income) . ' PKR' : 'N/A' }}</span></div>
                <div class="item" style="grid-column: span 2;"><span class="label">Permanent Address:</span><span class="value">{{ $partner->address ?? 'N/A' }}</span></div>
            </div>

            <div class="cnic-container">
                <div class="cnic-box dashed">
                    <div class="cnic-title">CNIC Front</div>
                    @if($partner->cnic_front_image)
                        <img src="{{ $partner->cnic_front_url }}" class="cnic-img" alt="Partner CNIC Front">
                    @else
                        <div class="cnic-warning">⚠️ CNIC Front Image Needed</div>
                    @endif
                </div>
                <div class="cnic-box dashed">
                    <div class="cnic-title">CNIC Back</div>
                    @if($partner->cnic_back_image)
                        <img src="{{ $partner->cnic_back_url }}" class="cnic-img" alt="Partner CNIC Back">
                    @else
                        <div class="cnic-warning">⚠️ CNIC Back Image Needed</div>
                    @endif
                </div>
            </div>
        @endforeach
    @endif

    <div class="section-title">Emergency Contacts</div>
    @if(($emergencyContacts ?? $tenant->emergencyContacts) && ($emergencyContacts ?? $tenant->emergencyContacts)->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Relation</th>
                    <th>Phone</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
                @foreach(($emergencyContacts ?? $tenant->emergencyContacts) as $index => $contact)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $contact->name }}</td>
                        <td>{{ ucfirst($contact->relation) }}</td>
                        <td>{{ $contact->phone }}</td>
                        <td>{{ $contact->address ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="margin-bottom: 20px;">No emergency contacts provided.</p>
    @endif

    <div class="signature-area">
        <div class="sig-box">
            Tenant's Signature
        </div>
        <div class="sig-box">
            Authorized Officer
        </div>
    </div>


    <!-- PAGE 2: GUARANTORS -->
    <div class="page-break"></div>
    <div class="header">
        <div class="header-info {{ $tenant->passport_photo ? '' : 'centered' }}">
            <h1>Guarantor Information</h1>
            <p>Tenant: {{ $tenant->name }} | Palladium Mall</p>
        </div>
        @if($tenant->passport_photo)
            <img src="{{ $tenant->passport_photo_url }}" class="tenant-photo" alt="Tenant Photo">
        @endif
    </div>

    <div class="section-title">Assigned Flat / Shop Details</div>
    <div class="grid" style="margin-bottom: 15px;">
        <div class="item"><span class="label">Flat/Shop Number:</span><span class="value"><strong>{{ $tenant->unit ? $tenant->unit->unit_number : 'Not Assigned' }}</strong></span></div>
        <div class="item"><span class="label">Type:</span><span class="value">{{ $tenant->unit ? ucfirst($tenant->unit->type) : 'N/A' }}</span></div>
        <div class="item"><span class="label">Floor:</span><span class="value">{{ $tenant->unit?->floor?->name ?? 'N/A' }}</span></div>
        <div class="item"><span class="label">Block:</span><span class="value">{{ $tenant->unit?->block?->name ?? 'N/A' }}</span></div>
    </div>

    <div class="section-title">Guarantors Details</div>
    @forelse($guarantors as $index => $g)
        <div class="guarantor-card">
            <div style="font-weight: bold; margin-bottom: 10px; text-transform: uppercase; font-size: 12px; color: #555;">Guarantor #{{ $index + 1 }}</div>
            <div style="display: flex; gap: 20px; align-items: start;">
                @if($g->photo)
                    <img src="{{ $g->photo_url }}" style="width: 100px; height: 100px; border-radius: 4px; object-fit: cover; border: 1px solid #ccc; flex-shrink: 0;" alt="Guarantor Photo">
                @else
                    <div style="width: 100px; height: 100px; border-radius: 4px; border: 1px dashed #ccc; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: #888; font-size: 11px; flex-shrink: 0;">
                        <span>⚠️ Photo<br>Needed</span>
                    </div>
                @endif
                <div style="flex-grow: 1;">
                    <div class="grid">
                        <div class="item"><span class="label">Guarantor Name:</span><span class="value">{{ $g->name }}</span></div>
                        <div class="item"><span class="label">CNIC Number:</span><span class="value">{{ $g->cnic }}</span></div>
                        <div class="item"><span class="label">Relation to Tenant:</span><span class="value">{{ ucfirst($g->relation) }}</span></div>
                        <div class="item"><span class="label">Phone:</span><span class="value">{{ $g->phone }}</span></div>
                        <div class="item"><span class="label">Occupation:</span><span class="value">{{ $g->occupation ?? 'N/A' }}</span></div>
                        @if($g->shop_name)
                            <div class="item"><span class="label">Shop Name:</span><span class="value">{{ $g->shop_name }}</span></div>
                        @endif
                        <div class="item" style="grid-column: span 2;"><span class="label">Address:</span><span class="value">{{ $g->address }}</span></div>
                    </div>
                </div>
            </div>

            <div class="cnic-container">
                <div class="cnic-box">
                    <div class="cnic-title">CNIC Front</div>
                    @if($g->cnic_front)
                        <img src="{{ $g->cnic_front_url }}" class="cnic-img" alt="CNIC Front">
                    @else
                        <div class="cnic-warning">⚠️ CNIC Front Image Needed</div>
                    @endif
                </div>
                <div class="cnic-box">
                    <div class="cnic-title">CNIC Back</div>
                    @if($g->cnic_back)
                        <img src="{{ $g->cnic_back_url }}" class="cnic-img" alt="CNIC Back">
                    @else
                        <div class="cnic-warning">⚠️ CNIC Back Image Needed</div>
                    @endif
                </div>
                <div class="cnic-box">
                    <div class="cnic-title">Visiting Card</div>
                    @if($g->visiting_card)
                        <img src="{{ $g->visiting_card_url }}" class="cnic-img" alt="Visiting Card">
                    @else
                        <div class="cnic-warning">⚠️ Visiting Card Needed</div>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <p>No guarantor details provided.</p>
    @endforelse

    <div class="signature-area">
        <div class="sig-box">
            Guarantor's Signature
        </div>
        <div class="sig-box">
            Tenant's Signature
        </div>
    </div>


    <!-- PAGE 3: TENANCY AGREEMENT TERMS -->
    <div class="page-break"></div>
    <div class="header">
        <div class="header-info {{ $tenant->passport_photo ? '' : 'centered' }}">
            <h1>Tenancy Agreement Terms</h1>
            <p>Palladium Mall Rent Management System</p>
        </div>
        @if($tenant->passport_photo)
            <img src="{{ $tenant->passport_photo_url }}" class="tenant-photo" alt="Tenant Photo">
        @endif
    </div>

    <div class="section-title">Tenant & Unit Details</div>
    <div class="grid">
        <div class="item"><span class="label">Tenant Name:</span><span class="value">{{ $tenant->name }}</span></div>
        <div class="item"><span class="label">CNIC Number:</span><span class="value">{{ $tenant->cnic }}</span></div>
        <div class="item"><span class="label">Assigned Unit:</span><span class="value"><strong>{{ $tenant->unit ? $tenant->unit->unit_number : 'N/A' }}</strong></span></div>
        <div class="item"><span class="label">Unit Type:</span><span class="value">{{ $tenant->unit ? ucfirst($tenant->unit->type) : 'N/A' }}</span></div>
        <div class="item"><span class="label">Floor:</span><span class="value">{{ $tenant->unit?->floor?->name ?? 'N/A' }}</span></div>
        <div class="item"><span class="label">Block:</span><span class="value">{{ $tenant->unit?->block?->name ?? 'N/A' }}</span></div>
    </div>

    @if($agreement)
        <div class="section-title">Agreement & Financial Terms</div>
        <div class="grid">
            <div class="item"><span class="label">Start Date:</span><span class="value">{{ optional($agreement->start_date)->format('d M Y') }}</span></div>
            <div class="item"><span class="label">End Date:</span><span class="value">{{ optional($agreement->end_date)->format('d M Y') }}</span></div>
            <div class="item"><span class="label">Monthly Rent:</span><span class="value">{{ number_format($agreement->monthly_rent) }} PKR</span></div>
            <div class="item"><span class="label">Security Deposit:</span><span class="value">{{ number_format($agreement->security_deposit) }} PKR</span></div>
            <div class="item"><span class="label">Maintenance Charge:</span><span class="value">{{ $agreement->maintenance_charge ? number_format($agreement->maintenance_charge) . ' PKR' : 'N/A' }}</span></div>
            <div class="item"><span class="label">Payment Due Day:</span><span class="value">{{ $agreement->payment_due_day }}th of each month</span></div>
            <div class="item"><span class="label">Grace Period:</span><span class="value">{{ $agreement->grace_period_days ?? 0 }} days</span></div>
            <div class="item"><span class="label">Fine Per Day:</span><span class="value">{{ $agreement->fine_per_day ? number_format($agreement->fine_per_day) . ' PKR' : '0 PKR' }}</span></div>
            <div class="item"><span class="label">Notice Period:</span><span class="value">{{ $agreement->notice_period_months ?? 0 }} month(s)</span></div>
        </div>

        @if($agreement->terms)
            <div class="section-title">Special Terms & Conditions</div>
            <div class="terms-box">
                {{ $agreement->terms }}
            </div>
        @endif
    @else
        <p>No active/draft agreement details exist for this tenant.</p>
    @endif

    <div class="signature-area">
        <div class="sig-box">
            Tenant's Signature
        </div>
        <div class="sig-box">
            Guarantor's Signature
        </div>
        <div class="sig-box">
            Owner / Representative
        </div>
    </div>


    <!-- PAGE 4: DOCUMENTS CHECKLIST -->
    <div class="page-break"></div>
    <div class="header">
        <div class="header-info">
            <h1>Required Documents Checklist</h1>
            <p>Tenant: <strong>{{ $tenant->name }}</strong> | Unit: <strong>{{ $tenant->unit ? $tenant->unit->unit_number . ($tenant->unit->floor ? ' (' . $tenant->unit->floor->name . ')' : '') . ($tenant->unit->block ? ' - ' . $tenant->unit->block->name : '') : 'N/A' }}</strong></p>
        </div>
        <div style="text-align: right; font-size: 12px; color: #666;">
            Date: {{ now()->format('d M Y') }}
        </div>
    </div>

    <div class="intro-text">
        Dear Client, please ensure the following documents are submitted to the company to complete your onboarding process. All required documents are checked below with their current verification status.
    </div>

    @php
    // Group all document keys, their labels, and their database status flag
    $docSections = [
        'Basic Identity Docs' => [
            ['field' => 'cnic_copy_tenant_front', 'label' => 'CNIC Copy — Tenant (Front)', 'desc' => 'Required for identity verification'],
            ['field' => 'cnic_copy_tenant_back', 'label' => 'CNIC Copy — Tenant (Back)', 'desc' => 'Required for identity verification'],
            ['field' => 'cnic_copy_father', 'label' => 'CNIC Copy — Father / Husband', 'desc' => 'Required relationship verification'],
            ['field' => 'cnic_copy_guarantor', 'label' => 'CNIC Copy — Guarantor', 'desc' => 'Verification for guarantor identity'],
            ['field' => 'passport_photo', 'label' => 'Passport Size Photograph', 'desc' => 'Two fresh color photographs'],
            ['field' => 'nikah_nama', 'label' => 'Nikah Nama (Computerized)', 'desc' => 'For family registration check (if married)'],
            ['field' => 'frc_form_b', 'label' => 'FRC / Form-B', 'desc' => 'Family registration certificate / Form-B'],
            ['field' => 'police_verification', 'label' => 'Police Verification Certificate', 'desc' => 'Local police verification check document'],
        ],
        'Application & Tenancy Agreement' => [
            ['field' => 'tenant_application_form', 'label' => 'Tenant Application Form', 'desc' => 'Fully filled application form'],
            ['field' => 'tenancy_agreement_copy', 'label' => 'Tenancy Agreement Scan', 'desc' => 'Signed copy of tenancy agreement'],
            ['field' => 'rules_acknowledgment', 'label' => 'Rules Acknowledgment Signed', 'desc' => 'Signed rules and regulation handbook'],
        ],
        'Property Handover & Security' => [
            ['field' => 'inspection_report', 'label' => 'Inspection Report', 'desc' => 'Unit pre-inspection check list report'],
            ['field' => 'property_handover_form', 'label' => 'Property Handover Form', 'desc' => 'Signed unit key handover slip'],
            ['field' => 'security_deposit_receipt', 'label' => 'Security Deposit / Voucher', 'desc' => 'Bank deposit receipt copy of security amount'],
            ['field' => 'meter_picture', 'label' => 'Meter Picture', 'desc' => 'Utility meter reading photo scan'],
        ],
        'Contacts & References' => [
            ['field' => 'emergency_contacts_added', 'label' => 'Emergency Contacts Added', 'desc' => 'Valid emergency contact details'],
            ['field' => 'guarantor_info_added', 'label' => 'Guarantor Info Added', 'desc' => 'Valid guarantor contact info'],
            ['field' => 'guarantor_business_card', 'label' => 'Guarantor Business Card', 'desc' => 'Official card / job badge card'],
            ['field' => 'tenant_business_card', 'label' => 'Tenant Business Card', 'desc' => 'Official card / business card'],
            ['field' => 'property_advisor_card', 'label' => 'Property Advisor Card', 'desc' => 'Card of dealing broker (if applicable)'],
            ['field' => 'old_tenant_verification', 'label' => 'Old Tenant Verification', 'desc' => 'Clearance document of prior occupancy'],
        ],
        'Commercial Only' => [
            ['field' => 'business_license', 'label' => 'Business License', 'desc' => 'Company registration/NTN certificates'],
            ['field' => 'utility_bills_clearance', 'label' => 'Utility Bills Clearance', 'desc' => 'Utility bill clearance certificates from prior owners'],
        ]
    ];
    @endphp

    @foreach($docSections as $title => $items)
        @php
            if ($title === 'Commercial Only' && $tenant->tenancy_type === 'residential') {
                continue;
            }
        @endphp
        <div class="section-title">{{ $title }}</div>
        <table>
            <thead>
                <tr>
                    <th class="checkbox-cell">✔</th>
                    <th>Document Description</th>
                    <th>Requirement Details</th>
                    <th style="width: 120px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                    @php
                        $isUploaded = $checklist?->{$item['field']} ?? false;
                    @endphp
                    <tr>
                        <td class="checkbox-cell">
                            {!! $isUploaded ? '&#x2611;' : '&#x2610;' !!}
                        </td>
                        <td style="font-weight: bold;">{{ $item['label'] }}</td>
                        <td style="color: #666;">{{ $item['desc'] }}</td>
                        <td>
                            @if($isUploaded)
                                <span class="status-badge status-submitted">Submitted</span>
                            @else
                                <span class="status-badge status-pending">Pending</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach

    @if($checklist?->notes)
        <div class="section-title">Notes / Special Instructions</div>
        <p style="font-size: 13px; color: #555; background: #fafafa; border: 1px solid #eee; padding: 10px; border-radius: 4px;">
            {{ $checklist->notes }}
        </p>
    @endif

    <div class="signature-area">
        <div class="sig-box">
            Tenant's Signature
        </div>
        <div class="sig-box">
            Authorized Officer's Signature
        </div>
    </div>


    <!-- PAGE 5: MOVE-IN CHECKLIST -->
    <div class="page-break"></div>
    <div class="header">
        <div class="header-info {{ $tenant->passport_photo ? '' : 'centered' }}">
            <h1>Move-in Inspection Checklist</h1>
            <p>Palladium Mall Tenant Management System</p>
        </div>
        @if($tenant->passport_photo)
            <img src="{{ $tenant->passport_photo_url }}" class="tenant-photo" alt="Tenant Photo">
        @endif
    </div>

    <div class="info-grid">
        <div class="info-item"><span class="info-label">Tenant Name:</span><span class="info-value">{{ $tenant->name }}</span></div>
        <div class="info-item"><span class="info-label">Unit / Shop Number:</span><span class="info-value">{{ $tenant->unit ? $tenant->unit->unit_number . ($tenant->unit->floor ? ' (' . $tenant->unit->floor->name . ')' : '') . ($tenant->unit->block ? ' - ' . $tenant->unit->block->name : '') : 'N/A' }}</span></div>
        <div class="info-item"><span class="info-label">Inspection Date:</span><span class="info-value">{{ optional($moveInChecklist?->checklist_date)->format('d M Y') ?? now()->format('d M Y') }}</span></div>
        <div class="info-item"><span class="info-label">Inspector Name:</span><span class="info-value">{{ $moveInChecklist?->inspection_member ?? 'N/A' }}</span></div>
        <div class="info-item"><span class="info-label">Flat Condition:</span><span class="info-value"><strong>{{ $moveInChecklist?->flat_condition ? ucfirst($moveInChecklist->flat_condition) : 'N/A' }}</strong></span></div>
    </div>

    @php
    $moveInSections = [
        '1. General Cleanliness' => [
            'rooms_cleaned'     => 'All rooms cleaned (floors, walls, ceilings)',
            'kitchen_cleaned'   => 'Kitchen cleaned (sink, counters, cabinets)',
            'bathrooms_cleaned' => 'Bathrooms cleaned (toilet, shower, tiles)',
            'no_garbage'        => 'No garbage left inside unit',
        ],
        '2. Walls, Paint & Fixtures' => [
            'no_wall_damage'    => 'No damage to walls (holes, cracks, stains)',
            'paint_condition_ok'=> 'Paint condition acceptable',
            'light_fixtures_ok' => 'Light fixtures, switches, sockets working',
            'electric_wiring_ok'=> 'Electric cables and wiring in good condition',
            'no_breaker_issues' => 'No issues with electricity breakers',
        ],
        '3. Furniture, Appliances & Kitchen' => [
            'furniture_ok'           => 'Furniture present and in good condition (if provided)',
            'ac_working'             => 'Air-conditioners working',
            'kitchen_appliances_ok'  => 'Kitchen appliances working (stove, hob, oven, fridge)',
            'stove_clean'            => 'Stove / Hob clean and in working condition',
            'keys_returned'          => 'Keys for all doors, cupboards, mailbox handed over',
        ],
        '4. Doors, Windows & Locks' => [
            'doors_locks_ok'   => 'All doors and locks working properly',
            'windows_ok'       => 'Windows not broken, open/close properly',
            'balcony_doors_ok' => 'Balcony doors / windows secured properly',
        ],
        '5. Utilities & Dues' => [
            'water_supply_ok'          => 'Water supply working',
            'electricity_supply_ok'    => 'Electricity supply working',
            'gas_supply_ok'            => 'Gas supply checked',
            'no_pending_utility_bills' => 'No pending electricity, water or gas bills',
            'no_pending_maintenance'   => 'No pending maintenance dues',
            'no_pending_rent'          => 'No pending rent payments',
        ],
        '6. Stock & Inventory' => [
            'fixtures_available' => 'All original flat fittings and fixtures available',
            'no_missing_items'   => 'No missing inventory items',
        ],
        '7. Final Actions' => [
            'access_cards_returned'  => 'All access cards, parking stickers handed over',
            'no_pending_requests'    => 'No pending service requests or complaints',
            'move_out_form_signed'   => 'Tenant signed move-in form',
        ],
    ];
    @endphp

    @foreach($moveInSections as $title => $items)
        <div class="section-title">{{ $title }}</div>
        <div class="checklist-grid">
            @foreach($items as $field => $itemLabel)
                <div class="checklist-item">
                    <span class="check-box">{!! ($moveInChecklist && $moveInChecklist->{$field}) ? '&#9745;' : '&#9744;' !!}</span>
                    <span>{{ $itemLabel }}</span>
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="section-title">Inspection Remarks & Notes</div>
    <div class="notes-area">
        <div class="notes-box">
            <div class="notes-title">Damage or Maintenance Notes:</div>
            <div>{{ $moveInChecklist?->damage_notes ?? 'No issues reported.' }}</div>
        </div>
        <div class="notes-box">
            <div class="notes-title">Inventory Notes:</div>
            <div>{{ $moveInChecklist?->inventory_notes ?? 'No inventory notes.' }}</div>
        </div>
        @if($moveInChecklist?->final_remarks)
            <div class="notes-box">
                <div class="notes-title">Final Remarks:</div>
                <div>{{ $moveInChecklist->final_remarks }}</div>
            </div>
        @endif
    </div>

    <div class="signature-area">
        <div class="sig-box">
            Tenant's Signature
        </div>
        <div class="sig-box">
            Inspector's Signature
        </div>
    </div>

</body>
</html>
