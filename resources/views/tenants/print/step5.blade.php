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

    @if($inspectionHeads->isEmpty())
        <div class="section-title">🏠 Flat Inspection Checklist</div>
        <p style="color:#b45309; font-size:12px;">⚠️ No inspection heads configured. Please add Flat Inspection heads via the admin panel.</p>
    @else
        <div class="section-title">🏠 Flat Inspection Checklist</div>
        <table style="width:100%; border-collapse: collapse; font-size: 12px; margin-bottom: 15px;">
            <thead>
                <tr style="background:#f3f4f6; border-bottom: 2px solid #d1d5db; font-weight: bold; text-transform: uppercase; font-size: 10px;">
                    <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: left; width: 32px;">#</th>
                    <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: left;">Inspection Item</th>
                    <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: center; width: 70px;">Result</th>
                    <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: left;">Comment</th>
                    <th style="padding: 6px 8px; border: 1px solid #e5e7eb; text-align: center; width: 60px;">Photo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($inspectionHeads as $head)
                    @php
                        $item   = $flatInspectionReport?->items->firstWhere('inspection_head_id', $head->id);
                        $status = $item?->status;
                        $rowBg  = $status === true ? '#f0fdf4' : ($status === false ? '#fff1f2' : '#ffffff');
                    @endphp
                    <tr style="background: {{ $rowBg }}; border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 5px 8px; border: 1px solid #e5e7eb; color: #9ca3af; font-size: 10px;">{{ $loop->iteration }}</td>
                        <td style="padding: 5px 8px; border: 1px solid #e5e7eb; font-weight: 600;">{{ $head->name }}</td>
                        <td style="padding: 5px 8px; border: 1px solid #e5e7eb; text-align: center; font-weight: bold;">
                            @if($status === true)
                                <span style="color: #16a34a;">✅ PASS</span>
                            @elseif($status === false)
                                <span style="color: #dc2626;">❌ FAIL</span>
                            @else
                                <span style="color: #9ca3af;">—</span>
                            @endif
                        </td>
                        <td style="padding: 5px 8px; border: 1px solid #e5e7eb; color: #4b5563; font-size: 11px;">{{ $item?->remarks ?? '' }}</td>
                        <td style="padding: 5px 8px; border: 1px solid #e5e7eb; text-align: center;">
                            @if($item?->image_path)
                                <img src="{{ Storage::url($item->image_path) }}"
                                     alt="Photo"
                                     style="height: 40px; width: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #d1d5db;">
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            @if($flatInspectionReport)
                <tfoot>
                    <tr style="background: #f9fafb; font-weight: bold; font-size: 10px; border-top: 2px solid #d1d5db;">
                        <td colspan="2" style="padding: 5px 8px; border: 1px solid #e5e7eb; text-align: right; text-transform: uppercase;">Summary:</td>
                        <td style="padding: 5px 8px; border: 1px solid #e5e7eb; text-align: center;">
                            <span style="color: #16a34a;">✅ {{ $flatInspectionReport->passCount() }}</span>
                            &nbsp;
                            <span style="color: #dc2626;">❌ {{ $flatInspectionReport->failCount() }}</span>
                        </td>
                        <td colspan="2" style="padding: 5px 8px; border: 1px solid #e5e7eb;"></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    @endif

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

    @php
        $latestBreakerInsp = $tenant->unit ? $tenant->unit->breakerInspections()->where('breaker_status', 'on')->first() : null;
    @endphp
    @if($latestBreakerInsp)
        <div class="section-title">⚡ Electricity Breaker &amp; Initial Meter Handover Verification</div>
        <div class="info-grid">
            <div class="info-item"><span class="info-label">Breaker Status:</span><span class="info-value" style="font-weight: bold; color: #16a34a;">ON (Breaker Switched ON for Move-In)</span></div>
            <div class="info-item"><span class="info-label">Initial Meter Reading:</span><span class="info-value" style="font-weight: bold;">{{ number_format($latestBreakerInsp->meter_reading, 2) }} kWh</span></div>
            <div class="info-item"><span class="info-label">Inspection Officer:</span><span class="info-value">{{ $latestBreakerInsp->inspection_officer_name }}</span></div>
            <div class="info-item" style="grid-column: span 2;"><span class="info-label">Officer Statement:</span><span class="info-value">"{{ $latestBreakerInsp->officer_statement }}"</span></div>
        </div>
        @if($latestBreakerInsp->meter_image)
            <div style="margin-top: 10px; margin-bottom: 15px;">
                <span class="info-label" style="display: block; margin-bottom: 5px;">Initial Meter Photo Proof:</span>
                <img src="{{ $latestBreakerInsp->meter_image_url }}" alt="Meter Photo Proof" style="max-height: 140px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
        @endif
    @endif

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
