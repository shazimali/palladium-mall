<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Move-Out Inspection Report - {{ $tenant->name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 30px; font-size: 13px; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header-info { flex-grow: 1; text-align: left; }
        .header-info.centered { text-align: center; }
        .header h1 { margin: 0; font-size: 22px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; }
        .tenant-photo { width: 90px; height: 90px; border-radius: 4px; object-fit: cover; border: 1px solid #ccc; margin-left: 20px; }
        .section-title { font-size: 14px; font-weight: bold; margin-top: 25px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px; text-transform: uppercase; }
        .grid { display: grid; grid-template-cols: 1fr 1fr; gap: 12px; margin-bottom: 15px; }
        .item { display: flex; border-bottom: 1px dashed #eee; padding-bottom: 4px; }
        .label { font-weight: bold; width: 180px; color: #555; }
        .value { flex-grow: 1; }
        .checklist-grid { display: grid; grid-template-cols: 1fr 1fr; gap: 8px; margin-bottom: 15px; }
        .check-item { display: flex; align-items: center; gap: 8px; font-size: 12px; }
        .check-box { font-family: monospace; font-size: 13px; font-weight: bold; }
        .check-yes { color: #16a34a; }
        .check-no { color: #999; }
        .notes-box { border: 1px solid #ddd; padding: 10px; background-color: #f9f9f9; min-height: 40px; white-space: pre-line; line-height: 1.5; margin-bottom: 15px; }
        .signature-area { margin-top: 60px; display: flex; justify-content: space-between; }
        .sig-box { border-top: 1px solid #333; width: 220px; text-align: center; padding-top: 5px; }
        @media print {
            body { margin: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="header-info {{ $tenant->passport_photo ? '' : 'centered' }}">
            <h1>Tenant Move-Out Inspection Report</h1>
            <p>Palladium Mall Tenant Management System</p>
        </div>
        @if($tenant->passport_photo)
            <img src="{{ $tenant->passport_photo_url }}" class="tenant-photo" alt="Tenant Photo">
        @endif
    </div>

    <div class="section-title">Tenant & Unit Details</div>
    <div class="grid">
        <div class="item"><span class="label">Tenant Name:</span><span class="value">{{ $tenant->name }}</span></div>
        <div class="item"><span class="label">CNIC Number:</span><span class="value">{{ $tenant->cnic }}</span></div>
        <div class="item"><span class="label">Assigned Unit:</span><span class="value">{{ $tenant->unit ? $tenant->unit->unit_number : ($agreement?->unit?->unit_number ?? 'N/A') }}</span></div>
        <div class="item"><span class="label">Inspection Date:</span><span class="value">{{ optional($moveOut->checklist_date)->format('d M Y') ?? 'N/A' }}</span></div>
        <div class="item"><span class="label">Inspection Member:</span><span class="value">{{ $moveOut->inspection_member }}</span></div>
        <div class="item"><span class="label">Agreement Period:</span><span class="value">{{ optional($agreement?->start_date)->format('d M Y') }} → {{ optional($agreement?->end_date)->format('d M Y') }}</span></div>
    </div>

    <div class="section-title">Inspection Checklist</div>
    <div class="checklist-grid">
        @php
        $checks = [
            'rooms_cleaned' => 'All rooms swept, mopped & cleaned',
            'kitchen_cleaned' => 'Kitchen counters, sink & cabinets cleaned',
            'bathrooms_cleaned' => 'Bathrooms thoroughly cleaned & sanitized',
            'no_garbage' => 'All garbage and personal items removed',
            'no_wall_damage' => 'No wall cracks, excessive holes or damage',
            'paint_condition_ok' => 'Paint condition acceptable/normal wear',
            'light_fixtures_ok' => 'All light bulbs & switches functional',
            'electric_wiring_ok' => 'Electrical sockets & wiring safe',
            'no_breaker_issues' => 'Breaker panel clean & functional',
            'furniture_ok' => 'Provided furniture in good condition',
            'ac_working' => 'Air conditioning units functional',
            'kitchen_appliances_ok' => 'Kitchen appliances in good working order',
            'stove_clean' => 'Stove & exhaust hoods clean and functional',
            'keys_returned' => 'All sets of property keys returned',
            'doors_locks_ok' => 'All doors, locks & handles functional',
            'windows_ok' => 'Windows & glass panes intact and clean',
            'balcony_doors_ok' => 'Balcony doors & railings secure',
            'water_supply_ok' => 'Water taps, mixers & drains working',
            'electricity_supply_ok' => 'Main electricity supply connection functional',
            'gas_supply_ok' => 'Gas supply line connection functional',
            'no_pending_utility_bills' => 'All utility bills cleared',
            'no_pending_maintenance' => 'No pending maintenance charges',
            'no_pending_rent' => 'No pending monthly rent dues',
            'fixtures_available' => 'All original fixtures present and intact',
            'no_missing_items' => 'No missing inventory items',
            'access_cards_returned' => 'All parking & access cards returned',
            'no_pending_requests' => 'No open requests/complaints',
            'move_out_form_signed' => 'Move-out inspection document signed by tenant',
        ];
        @endphp

        @foreach($checks as $col => $label)
            <div class="check-item">
                @if($moveOut->{$col})
                    <span class="check-box check-yes">[✓]</span>
                @else
                    <span class="check-box check-no">[ ]</span>
                @endif
                <span>{{ $label }}</span>
            </div>
        @endforeach
    </div>

    @if($moveOut->damage_notes)
        <div class="section-title">Damage Report & Remarks</div>
        <div class="notes-box">{{ $moveOut->damage_notes }}</div>
    @endif

    @if($moveOut->inventory_notes)
        <div class="section-title">Inventory Notes</div>
        <div class="notes-box">{{ $moveOut->inventory_notes }}</div>
    @endif

    <div class="section-title">Final Clearance Assessment</div>
    <div class="grid">
        <div class="item"><span class="label">Property Handover Condition:</span><span class="value" style="font-weight: bold; color: {{ $moveOut->flat_condition === 'good' ? '#16a34a' : '#ea580c' }}">{{ ucfirst($moveOut->flat_condition ?? 'N/A') }}</span></div>
        <div class="item"><span class="label">Deposit Deduction (PKR):</span><span class="value" style="font-weight: bold;">{{ number_format($moveOut->deposit_deduction ?? 0) }} PKR</span></div>
        @if($moveOut->final_remarks)
            <div class="item" style="grid-column: span 2;"><span class="label">Final Remarks:</span><span class="value">{{ $moveOut->final_remarks }}</span></div>
        @endif
    </div>

    <div class="signature-area">
        <div class="sig-box">
            Tenant's Signature
        </div>
        <div class="sig-box">
            Inspector's Signature
        </div>
        <div class="sig-box">
            Authorized Mall Manager
        </div>
    </div>
</body>
</html>
