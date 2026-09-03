<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Move-in Inspection Checklist - {{ $tenant->name }}</title>
    <style>
        @page { size: A4 portrait; margin: 8mm 10mm; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #111; margin: 0; padding: 0; font-size: 11px; line-height: 1.3; }
        .header { text-align: center; border-bottom: 2px solid #111; padding-bottom: 8px; margin-bottom: 10px; }
        .header h2 { margin: 0 0 2px; font-size: 24px; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; color: #111; }
        .header h1 { margin: 0 0 6px; font-size: 18px; font-weight: 800; text-transform: uppercase; color: #333; }
        .header p { margin: 4px 0 0; font-size: 15px; font-weight: bold; color: #111; }
        
        .meta-bar { display: flex; justify-content: space-between; background: #f3f4f6; border: 1px solid #d1d5db; padding: 6px 12px; border-radius: 4px; font-size: 11px; margin-bottom: 10px; font-weight: 600; }
        .meta-bar span { font-weight: normal; color: #4b5563; }

        .section-title { font-size: 11px; font-weight: bold; margin-top: 8px; margin-bottom: 4px; border-bottom: 1px solid #ccc; padding-bottom: 2px; text-transform: uppercase; color: #111; }
        
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 3px 6px; text-align: left; }
        th { background-color: #f3f4f6; font-weight: bold; font-size: 10px; text-transform: uppercase; }

        .notes-grid { display: grid; grid-template-cols: 1fr 1fr; gap: 8px; margin-bottom: 8px; }
        .notes-box { border: 1px solid #d1d5db; border-radius: 4px; padding: 6px; background: #fafafa; font-size: 10px; }
        .notes-title { font-weight: bold; color: #374151; margin-bottom: 2px; text-transform: uppercase; font-size: 9px; }

        .breaker-box { border: 1px solid #bbf7d0; background: #f0fdf4; border-radius: 4px; padding: 6px 10px; margin-bottom: 8px; font-size: 10px; }
        .breaker-title { font-weight: bold; color: #166534; font-size: 10px; margin-bottom: 4px; text-transform: uppercase; }
        
        .signature-area { margin-top: 20px; display: flex; justify-content: space-between; gap: 10px; page-break-inside: avoid; }
        .sig-box { border-top: 1px solid #111; flex: 1; text-align: center; padding-top: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header" style="position: relative; text-align: center; border-bottom: 2px solid #111; padding-bottom: 8px; margin-bottom: 10px; min-height: 55px;">
        @if($tenant->passport_photo)
            <img src="{{ $tenant->passport_photo_url }}" alt="{{ $tenant->name }}" style="position: absolute; right: 0; top: 0; width: 55px; height: 55px; object-fit: cover; border-radius: 5px; border: 1.5px solid #111;">
        @endif
        <h2>PALLADIUM MALL</h2>
        <h1>Move-in Inspection Checklist</h1>
        <p>
            Tenant: <span style="font-size: 16px; font-weight: 900;">{{ $tenant->name }}</span> | Unit: <span style="font-size: 16px; font-weight: 900;">{{ $tenant->unit ? $tenant->unit->unit_number . ($tenant->unit->floor ? ' (' . $tenant->unit->floor->name . ')' : '') . ($tenant->unit->block ? ' - ' . $tenant->unit->block->name : '') : 'N/A' }}</span>
        </p>
    </div>

    <div class="meta-bar">
        <div><span>Inspection Date:</span> <strong>{{ optional($checklist?->checklist_date)->format('d M Y') ?? now()->format('d M Y') }}</strong></div>
        <div><span>Inspector:</span> <strong>{{ $checklist?->inspection_member ?? 'N/A' }}</strong></div>
        <div><span>Flat Condition:</span> <strong>{{ $checklist?->flat_condition ? ucfirst($checklist->flat_condition) : 'N/A' }}</strong></div>
    </div>

    @if($inspectionHeads->isEmpty())
        <div class="section-title">Flat Inspection Checklist</div>
        <p style="color:#b45309; font-size:11px; margin: 4px 0;">⚠️ No inspection heads configured.</p>
    @else
        <div class="section-title">Flat Inspection Checklist</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 24px; text-align: center;">#</th>
                    <th>Inspection Item</th>
                    <th style="width: 65px; text-align: center;">Result</th>
                    <th>Comment / Remarks</th>
                    <th style="width: 50px; text-align: center;">Photo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inspectionHeads as $head)
                    @php
                        $item   = $flatInspectionReport?->items->firstWhere('inspection_head_id', $head->id);
                        $status = $item?->status;
                        $rowBg  = $status === true ? '#f0fdf4' : ($status === false ? '#fff1f2' : '#ffffff');
                    @endphp
                    <tr style="background: {{ $rowBg }};">
                        <td style="text-align: center; color: #6b7280; font-size: 9px;">{{ $loop->iteration }}</td>
                        <td style="font-weight: 600;">{{ $head->name }}</td>
                        <td style="text-align: center; font-weight: bold;">
                            @if($status === true)
                                <span style="color: #16a34a;">✅ PASS</span>
                            @elseif($status === false)
                                <span style="color: #dc2626;">❌ FAIL</span>
                            @else
                                <span style="color: #9ca3af;">—</span>
                            @endif
                        </td>
                        <td style="color: #4b5563; font-size: 10px;">{{ $item?->remarks ?? '' }}</td>
                        <td style="text-align: center;">
                            @if($item?->image_path)
                                <img src="{{ Storage::url($item->image_path) }}"
                                     alt="Photo"
                                     style="height: 26px; width: 26px; object-fit: cover; border-radius: 3px; border: 1px solid #d1d5db;">
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            @if($flatInspectionReport)
                <tfoot>
                    <tr style="background: #f9fafb; font-weight: bold; font-size: 9px;">
                        <td colspan="2" style="text-align: right; text-transform: uppercase;">Summary:</td>
                        <td style="text-align: center;">
                            <span style="color: #16a34a;">✅ {{ $flatInspectionReport->passCount() }}</span>
                            &nbsp;
                            <span style="color: #dc2626;">❌ {{ $flatInspectionReport->failCount() }}</span>
                        </td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif

    <div class="notes-grid">
        <div class="notes-box">
            <div class="notes-title">Damage or Maintenance Notes:</div>
            <div>{{ $checklist?->damage_notes ?? 'No issues reported.' }}</div>
        </div>
        <div class="notes-box">
            <div class="notes-title">Inventory Notes:</div>
            <div>{{ $checklist?->inventory_notes ?? 'No inventory notes.' }}</div>
        </div>
    </div>

    @php
        $latestBreakerInsp = $tenant->unit ? $tenant->unit->breakerInspections()->where('breaker_status', 'on')->first() : null;
    @endphp
    @if($latestBreakerInsp)
        <div class="breaker-box">
            <div class="breaker-title">⚡ Electricity Breaker &amp; Meter Verification</div>
            <div style="display: flex; justify-content: space-between; align-items: center; gap: 10px;">
                <div>
                    <div><strong>Breaker Status:</strong> <span style="color: #16a34a; font-weight: bold;">ON</span> | <strong>Initial Reading:</strong> {{ number_format($latestBreakerInsp->meter_reading, 2) }} kWh</div>
                    <div><strong>Officer:</strong> {{ $latestBreakerInsp->inspection_officer_name }} — <em>"{{ $latestBreakerInsp->officer_statement }}"</em></div>
                </div>
                @if($latestBreakerInsp->meter_image)
                    <img src="{{ $latestBreakerInsp->meter_image_url }}" alt="Meter Photo" style="max-height: 40px; border: 1px solid #9ca3af; border-radius: 3px;">
                @endif
            </div>
        </div>
    @endif

    <div class="signature-area">
        <div class="sig-box">Tenant</div>
        <div class="sig-box">Dealer</div>
        <div class="sig-box">ASM</div>
        <div class="sig-box">Accountant</div>
        <div class="sig-box">MD</div>
    </div>
</body>
</html>
