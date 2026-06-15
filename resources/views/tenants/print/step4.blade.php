<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Required Documents Checklist - {{ $tenant->name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 30px; font-size: 14px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header-info { flex-grow: 1; text-align: left; }
        .header h1 { margin: 0; font-size: 22px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { margin: 5px 0 0; color: #666; font-size: 13px; }
        .section-title { font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 4px; text-transform: uppercase; page-break-after: avoid; color: #111; }
        .intro-text { margin-bottom: 20px; font-size: 13px; color: #555; line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; page-break-inside: avoid; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; font-size: 13px; }
        th { background-color: #f5f5f5; font-weight: bold; color: #333; }
        .status-badge { font-size: 11px; font-weight: bold; padding: 2px 6px; border-radius: 3px; display: inline-block; }
        .status-submitted { background-color: #e6f4ea; color: #137333; border: 1px solid #c2e7c9; }
        .status-pending { background-color: #fce8e6; color: #c5221f; border: 1px solid #fad2cf; }
        .checkbox-cell { text-align: center; width: 40px; font-size: 16px; }
        .signature-area { margin-top: 50px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sig-box { border-top: 1px solid #333; width: 230px; text-align: center; padding-top: 5px; font-size: 12px; }
        @media print {
            body { margin: 15px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
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
    $cl = $checklist;
    
    // Group all document keys, their labels, and their database status flag
    $sections = [
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

    @foreach($sections as $title => $items)
        @php
            // Skip commercial section if tenancy type is residential
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
                        $isUploaded = $cl?->{$item['field']} ?? false;
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

    @if($cl?->notes)
        <div class="section-title">Notes / Special Instructions</div>
        <p style="font-size: 13px; color: #555; background: #fafafa; border: 1px solid #eee; padding: 10px; border-radius: 4px;">
            {{ $cl->notes }}
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
</body>
</html>
