<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guarantor & Emergency Contacts Print - {{ $tenant->name }}</title>
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
        table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
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
            <h1>Guarantor & Emergency Contacts</h1>
            <p>Tenant: {{ $tenant->name }} | Palladium Mall</p>
        </div>
        @if($tenant->passport_photo)
            <img src="{{ $tenant->passport_photo_url }}" class="tenant-photo" alt="Tenant Photo">
        @endif
    </div>

    <div class="section-title">Guarantor Information</div>
    @if($guarantor)
        <div class="grid">
            <div class="item"><span class="label">Guarantor Name:</span><span class="value">{{ $guarantor->name }}</span></div>
            <div class="item"><span class="label">CNIC Number:</span><span class="value">{{ $guarantor->cnic }}</span></div>
            <div class="item"><span class="label">Relation to Tenant:</span><span class="value">{{ ucfirst($guarantor->relation) }}</span></div>
            <div class="item"><span class="label">Phone:</span><span class="value">{{ $guarantor->phone }}</span></div>
            <div class="item"><span class="label">Occupation:</span><span class="value">{{ $guarantor->occupation ?? 'N/A' }}</span></div>
            <div class="item" style="grid-column: span 2;"><span class="label">Address:</span><span class="value">{{ $guarantor->address }}</span></div>
        </div>
    @else
        <p>No guarantor details provided.</p>
    @endif

    <div class="section-title">Emergency Contacts</div>
    @if($emergencyContacts && $emergencyContacts->count() > 0)
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
                @foreach($emergencyContacts as $index => $contact)
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
        <p>No emergency contacts provided.</p>
    @endif

    <div class="signature-area">
        <div class="sig-box">
            Guarantor's Signature
        </div>
        <div class="sig-box">
            Tenant's Signature
        </div>
    </div>
</body>
</html>
