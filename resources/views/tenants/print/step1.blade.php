<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Information Print - {{ $tenant->name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 30px; font-size: 14px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header-info { flex-grow: 1; text-align: left; }
        .header-info.centered { text-align: center; }
        .header h1 { margin: 0; font-size: 24px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; }
        .tenant-photo { width: 90px; height: 90px; border-radius: 4px; object-fit: cover; border: 1px solid #ccc; margin-left: 20px; }
        .section-title { font-size: 16px; font-weight: bold; margin-top: 25px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; text-transform: uppercase; }
        .grid { display: grid; grid-template-cols: 1fr 1fr; gap: 15px; margin-bottom: 20px; }
        .item { display: flex; border-bottom: 1px dashed #eee; padding-bottom: 5px; }
        .label { font-weight: bold; width: 180px; color: #555; }
        .value { flex-grow: 1; }
        .signature-area { margin-top: 80px; display: flex; justify-content: space-between; }
        .sig-box { border-top: 1px solid #333; width: 250px; text-align: center; padding-top: 5px; }
        @media print {
            body { margin: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="header-info {{ $tenant->passport_photo ? '' : 'centered' }}">
            <h1>Tenant Personal Information</h1>
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
