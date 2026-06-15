<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Move-in Inspection Checklist - {{ $tenant->name }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; margin: 30px; font-size: 13px; line-height: 1.5; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header-info { flex-grow: 1; text-align: left; }
        .header-info.centered { text-align: center; }
        .header h1 { margin: 0; font-size: 22px; text-transform: uppercase; }
        .header p { margin: 5px 0 0; color: #666; font-size: 12px; }
        .tenant-photo { width: 80px; height: 80px; border-radius: 4px; object-fit: cover; border: 1px solid #ccc; margin-left: 20px; }
        .section-title { font-size: 14px; font-weight: bold; margin-top: 20px; margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 4px; text-transform: uppercase; }
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
        
        .signature-area { margin-top: 60px; display: flex; justify-content: space-between; page-break-inside: avoid; }
        .sig-box { border-top: 1px solid #333; width: 220px; text-align: center; padding-top: 5px; }
        @media print {
            body { margin: 15px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
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
        <div class="info-item"><span class="info-label">Inspection Date:</span><span class="info-value">{{ optional($checklist?->checklist_date)->format('d M Y') ?? now()->format('d M Y') }}</span></div>
        <div class="info-item"><span class="info-label">Inspector Name:</span><span class="info-value">{{ $checklist?->inspection_member ?? 'N/A' }}</span></div>
        <div class="info-item"><span class="info-label">Flat Condition:</span><span class="info-value"><strong>{{ $checklist?->flat_condition ? ucfirst($checklist->flat_condition) : 'N/A' }}</strong></span></div>
    </div>

    @php
    $sections = [
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

    @foreach($sections as $title => $items)
        <div class="section-title">{{ $title }}</div>
        <div class="checklist-grid">
            @foreach($items as $field => $itemLabel)
                <div class="checklist-item">
                    <span class="check-box">{!! ($checklist && $checklist->{$field}) ? '&#9745;' : '&#9744;' !!}</span>
                    <span>{{ $itemLabel }}</span>
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="section-title">Inspection Remarks & Notes</div>
    <div class="notes-area">
        <div class="notes-box">
            <div class="notes-title">Damage or Maintenance Notes:</div>
            <div>{{ $checklist?->damage_notes ?? 'No issues reported.' }}</div>
        </div>
        <div class="notes-box">
            <div class="notes-title">Inventory Notes:</div>
            <div>{{ $checklist?->inventory_notes ?? 'No inventory notes.' }}</div>
        </div>
        @if($checklist?->final_remarks)
            <div class="notes-box">
                <div class="notes-title">Final Remarks:</div>
                <div>{{ $checklist->final_remarks }}</div>
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
