<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Guarantor Information Print - {{ $tenant->name }}</title>
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
        .cnic-container { display: flex; gap: 20px; margin-top: 15px; margin-bottom: 20px; page-break-inside: avoid; }
        .cnic-box { flex: 1; border: 1px solid #ccc; border-radius: 4px; padding: 10px; text-align: center; background-color: #fafafa; min-height: 120px; display: flex; flex-direction: column; justify-content: center; align-items: center; page-break-inside: avoid; }
        .cnic-box.dashed { border-style: dashed; }
        .cnic-title { font-weight: bold; margin-bottom: 8px; color: #555; font-size: 13px; }
        .cnic-img { max-width: 100%; max-height: 110px; object-fit: contain; border: 1px solid #ddd; border-radius: 4px; }
        .cnic-warning { color: #d9534f; font-weight: bold; font-size: 13px; }
        .guarantor-card { margin-bottom: 30px; page-break-inside: avoid; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .guarantor-card:last-child { border-bottom: none; }
        @media print {
            body { margin: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="header-info {{ $tenant->passport_photo ? '' : 'centered' }}">
            <h1>Guarantor Information</h1>
            <p>Tenant: {{ $tenant->name }} | Palladium Mall</p>
        </div>
        @if($tenant->passport_photo)
            <img src="{{ $tenant->passport_photo_url }}" class="tenant-photo" alt="Tenant Photo">
        @endif
    </div>

    <div class="section-title">Guarantors Information</div>
    @forelse($guarantors as $index => $g)
        <div class="guarantor-card">
            <div style="font-weight: bold; margin-bottom: 10px; text-transform: uppercase; font-size: 12px; color: #555;">Guarantor #{{ $index + 1 }}</div>
            <div style="display: flex; gap: 20px; align-items: start;">
                <!-- Photo -->
                @if($g->photo)
                    <img src="{{ $g->photo_url }}" style="width: 100px; height: 100px; border-radius: 4px; object-fit: cover; border: 1px solid #ccc; flex-shrink: 0;" alt="Guarantor Photo">
                @else
                    <div style="width: 100px; height: 100px; border-radius: 4px; border: 1px dashed #ccc; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: #888; font-size: 11px; flex-shrink: 0;">
                        <span>⚠️ Photo<br>Needed</span>
                    </div>
                @endif
                <!-- Grid Info -->
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

            <!-- Documents -->
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
</body>
</html>
