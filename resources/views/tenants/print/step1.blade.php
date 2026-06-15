<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant & Emergency Contacts Print - {{ $tenant->name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 30px; font-size: 14px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header-info { flex-grow: 1; text-align: left; }
        .header-info.centered { text-align: center; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; }
        .tenant-photo { width: 90px; height: 90px; border-radius: 4px; object-fit: cover; border: 1px solid #ccc; margin-left: 20px; }
        .section-title { font-size: 16px; font-weight: bold; margin-top: 25px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; text-transform: uppercase; page-break-after: avoid; }
        .grid { display: grid; grid-template-cols: 1fr 1fr; gap: 15px; margin-bottom: 20px; page-break-inside: avoid; }
        .item { display: flex; border-bottom: 1px dashed #eee; padding-bottom: 5px; }
        .label { font-weight: bold; width: 180px; color: #555; }
        .value { flex-grow: 1; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .signature-area { margin-top: 60px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sig-box { border-top: 1px solid #333; width: 250px; text-align: center; padding-top: 5px; }
        .cnic-container { display: flex; gap: 20px; margin-top: 10px; margin-bottom: 20px; page-break-inside: avoid; }
        .cnic-box { flex: 1; border: 1px solid #ccc; border-radius: 4px; padding: 10px; text-align: center; background-color: #fafafa; min-height: 120px; display: flex; flex-direction: column; justify-content: center; align-items: center; page-break-inside: avoid; }
        .cnic-box.dashed { border-style: dashed; }
        .cnic-title { font-weight: bold; margin-bottom: 8px; color: #555; font-size: 13px; }
        .cnic-img { max-width: 100%; max-height: 110px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px; }
        .cnic-warning { color: #d9534f; font-weight: bold; font-size: 13px; }
        @media print {
            body { margin: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
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
            <div class="section-title">Partner #{{ $index + 1 }} Details</div>
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
</body>
</html>
