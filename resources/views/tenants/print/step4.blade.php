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
    <div style="position: relative; text-align: center; margin-bottom: 25px; border-bottom: 2px solid #111; padding-bottom: 15px; min-height: 65px;">
        @if($tenant->passport_photo)
            <img src="{{ $tenant->passport_photo_url }}" alt="{{ $tenant->name }}" style="position: absolute; right: 0; top: 0; width: 65px; height: 65px; object-fit: cover; border-radius: 6px; border: 1.5px solid #111;">
        @endif
        <h2 style="margin: 0 0 4px; font-size: 26px; font-weight: 900; letter-spacing: 1.5px; text-transform: uppercase; color: #111;">PALLADIUM MALL</h2>
        <h1 style="margin: 0 0 10px; font-size: 20px; font-weight: 800; text-transform: uppercase; color: #333;">Required Documents Checklist</h1>
        <p style="margin: 8px 0 0; font-size: 16px; font-weight: bold; color: #111;">
            Tenant: <span style="font-size: 18px; font-weight: 900; color: #000;">{{ $tenant->name }}</span> | Unit: <span style="font-size: 18px; font-weight: 900; color: #000;">{{ $tenant->unit ? $tenant->unit->unit_number . ($tenant->unit->floor ? ' (' . $tenant->unit->floor->name . ')' : '') . ($tenant->unit->block ? ' - ' . $tenant->unit->block->name : '') : 'N/A' }}</span>
        </p>
        <div style="margin-top: 6px; font-size: 12px; color: #666;">
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
        'Property Handover & Security' => [
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
            ['field' => 'business_license', 'label' => 'Tenant Utility Bill', 'desc' => 'Electricity/utility bill copy of tenant'],
            ['field' => 'utility_bills_clearance', 'label' => 'Palladium Mall Utility Bill', 'desc' => 'Palladium Mall utility bill clearance copy'],
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
                        $fileUrl = $cl?->getFileUrlForField($item['field']);
                        $filePath = $cl?->getFilePathForField($item['field']);
                        $fileAttrVal = $filePath ? $cl?->{$filePath} : null;

                        if (!$fileUrl) {
                            if ($item['field'] === 'cnic_copy_tenant_front' && $tenant?->cnic_front_image) {
                                $fileUrl = $tenant->cnic_front_url;
                                $fileAttrVal = $tenant->cnic_front_image;
                                $isUploaded = true;
                            } elseif ($item['field'] === 'cnic_copy_tenant_back' && $tenant?->cnic_back_image) {
                                $fileUrl = $tenant->cnic_back_url;
                                $fileAttrVal = $tenant->cnic_back_image;
                                $isUploaded = true;
                            } elseif ($item['field'] === 'passport_photo' && $tenant?->passport_photo) {
                                $fileUrl = $tenant->passport_photo_url;
                                $fileAttrVal = $tenant->passport_photo;
                                $isUploaded = true;
                            }
                        }

                        $isImage = false;
                        if ($fileAttrVal) {
                            $ext = strtolower(pathinfo($fileAttrVal, PATHINFO_EXTENSION));
                            $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                        }
                    @endphp
                    <tr>
                        <td class="checkbox-cell">
                            {!! $isUploaded ? '&#x2611;' : '&#x2610;' !!}
                        </td>
                        <td style="font-weight: bold;">
                            <div>{{ $item['label'] }}</div>
                            @if($isImage && $fileUrl)
                                <div style="margin-top: 6px;">
                                    <img src="{{ $fileUrl }}" style="max-width: 220px; max-height: 160px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px; padding: 2px;" alt="{{ $item['label'] }}">
                                </div>
                            @endif
                        </td>
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
