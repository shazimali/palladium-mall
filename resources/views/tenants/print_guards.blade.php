<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }} — Palladium Mall</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 18px; }
        body { font-family: 'Segoe UI', Arial, sans-serif; color: #000; background: #fff; padding: 20px 24px; line-height: 1.4; font-weight: 800; }
        .pm-header { display: flex; align-items: center; justify-content: space-between; border-bottom: 4px solid #111827; padding-bottom: 12px; margin-bottom: 16px; }
        .pm-header-left { display: flex; align-items: center; gap: 12px; }
        .pm-logo-icon { width: 44px; height: 44px; background: #111827; border-radius: 8px; display: flex; align-items: flex-end; justify-content: center; gap: 3px; padding: 6px; }
        .pm-logo-icon span { display: block; background: #fff; border-radius: 2px; width: 6px; }
        .pm-logo-icon span:nth-child(1) { height: 100%; }
        .pm-logo-icon span:nth-child(2) { height: 70%; opacity: .9; }
        .pm-logo-icon span:nth-child(3) { height: 82%; opacity: .7; }
        .pm-name { font-size: 2.2rem; font-weight: 900; color: #000; letter-spacing: -.5px; }
        .pm-header-right { text-align: right; }
        .pm-header-right .doc-title { font-size: 1.8rem; font-weight: 900; color: #000; }
        .pm-header-right .doc-date { font-size: 1.1rem; font-weight: 800; color: #111827; margin-top: 2px; }
        
        table { width: 100%; border-collapse: collapse; font-size: 1.1rem; font-weight: 800; }
        thead tr { background: #111827; color: #fff; }
        thead th { padding: 12px 14px; text-align: left; font-weight: 900; font-size: 1.1rem; text-transform: uppercase; letter-spacing: .05em; color: #fff; border-bottom: 3px solid #000; white-space: nowrap; }
        tbody tr { border-bottom: 2px solid #e5e7eb; }
        tbody td { padding: 10px 14px; color: #000; font-weight: 800; vertical-align: middle; }
        
        .tenant-img { width: 65px; height: 65px; object-fit: cover; border-radius: 10px; border: 2px solid #111827; background: #f3f4f6; }
        .no-photo { width: 65px; height: 65px; border-radius: 10px; border: 2px dashed #9ca3af; background: #f3f4f6; display: flex; align-items: center; justify-content: center; font-size: 1.6rem; color: #6b7280; font-weight: 900; }
        
        .unit-badge { display: inline-block; background: #111827; color: #fff; font-weight: 900; font-size: 1.2rem; padding: 4px 10px; border-radius: 8px; font-family: monospace; }
        .other-badge { display: inline-block; background: #8b5cf6; color: #fff; font-weight: 900; font-size: .75rem; padding: 2px 6px; border-radius: 4px; margin-left: 6px; text-transform: uppercase; }
        
        .contact-box { display: flex; flex-direction: column; gap: 3px; }
        .primary-phone { font-family: monospace; font-size: 1.15rem; font-weight: 900; color: #111827; }
        .emergency-contact { font-size: .95rem; font-weight: 800; color: #dc2626; }
        
        .pm-footer { margin-top: 24px; border-top: 3px solid #000; padding-top: 10px; display: flex; justify-content: space-between; font-size: 1.1rem; font-weight: 900; color: #111827; }
        .no-print { text-align: center; margin-bottom: 20px; }
        .print-btn { display: inline-flex; align-items: center; gap: 8px; background: #111827; color: #fff; border: none; border-radius: 10px; padding: 12px 28px; font-size: 1.2rem; font-weight: 900; cursor: pointer; }
        @media print { body { padding: 0; } .no-print { display: none !important; } }
        @page { size: A4 portrait; margin: 10mm; }
    </style>
</head>
<body>
    <div class="no-print">
        <button class="print-btn" onclick="window.print()">🖨️ Print / Save as PDF</button>
    </div>

    <div class="pm-header">
        <div class="pm-header-left">
            <div class="pm-logo-icon"><span></span><span></span><span></span></div>
            <span class="pm-name">Palladium Mall</span>
        </div>
        <div class="pm-header-right">
            <div class="doc-title">Active Tenants Directory (For Guards)</div>
            <div class="doc-date">Printed: {{ now()->format('d M Y, h:i A') }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 75px;">Photo</th>
                <th>Flat / Shop</th>
                <th>Tenant Name</th>
                <th>Contact Details</th>
                <th>Landlord</th>
                <th>Start Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($occupants as $occupant)
                <tr>
                    <td>
                        @if(!empty($occupant['photo_url']))
                            <img src="{{ $occupant['photo_url'] }}" alt="{{ $occupant['tenant_name'] }}" class="tenant-img">
                        @else
                            <div class="no-photo">👤</div>
                        @endif
                    </td>
                    <td>
                        <span class="unit-badge">{{ $occupant['unit_number'] }}</span>
                    </td>
                    <td style="font-size: 1.2rem; font-weight: 900;">
                        {{ $occupant['tenant_name'] }}
                        @if($occupant['is_other_owned'])
                            <span class="other-badge">Other-Owned</span>
                        @endif
                    </td>
                    <td>
                        <div class="contact-box">
                            <span class="primary-phone">📞 {{ $occupant['phone'] }}</span>
                            @if(!empty($occupant['emergency_contact']) && $occupant['emergency_contact'] !== '—')
                                <span class="emergency-contact">🚨 Emer: {{ $occupant['emergency_contact'] }}</span>
                            @endif
                        </div>
                    </td>
                    <td style="font-weight: 800;">
                        {{ $occupant['landlord_name'] }}
                    </td>
                    <td style="font-family: monospace; font-weight: 800;">
                        {{ $occupant['start_date'] }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; font-size: 1.3rem; font-weight: 900; color: #6b7280;">
                        No active tenants found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="pm-footer">
        <span>Palladium Mall Security & Gate Control</span>
        <span>Total Active Occupants: {{ count($occupants) }}</span>
    </div>

    <script>
        window.addEventListener('load', function () {
            if (window.opener) { setTimeout(function () { window.print(); }, 400); }
        });
    </script>
</body>
</html>
