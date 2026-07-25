<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenancy Agreement Summary Print - {{ $tenant->name }}</title>
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
        .terms-box { border: 1px solid #ddd; padding: 15px; background-color: #f9f9f9; min-height: 100px; white-space: pre-line; line-height: 1.6; }
        .signature-area { margin-top: 80px; display: flex; justify-content: space-between; flex-wrap: wrap; gap: 30px; }
        .sig-box { border-top: 1px solid #333; width: 200px; text-align: center; padding-top: 5px; }
        @media print {
            body { margin: 20px; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="header-info {{ $tenant->passport_photo ? '' : 'centered' }}">
            <h1>Tenancy Agreement Terms</h1>
            <p>Palladium Mall Rent Management System</p>
        </div>
        @if($tenant->passport_photo)
            <img src="{{ $tenant->passport_photo_url }}" class="tenant-photo" alt="Tenant Photo">
        @endif
    </div>

    <div class="section-title">Tenant & Unit Details</div>
    <div class="grid">
        <div class="item"><span class="label">Tenant Name:</span><span class="value">{{ $tenant->name }}</span></div>
        <div class="item"><span class="label">CNIC Number:</span><span class="value">{{ $tenant->cnic }}</span></div>
        <div class="item"><span class="label">Assigned Unit:</span><span class="value"><strong>{{ $tenant->unit ? $tenant->unit->unit_number : 'N/A' }}</strong></span></div>
        <div class="item"><span class="label">Unit Type:</span><span class="value">{{ $tenant->unit ? ucfirst($tenant->unit->type) : 'N/A' }}</span></div>
        <div class="item"><span class="label">Floor:</span><span class="value">{{ $tenant->unit?->floor?->name ?? 'N/A' }}</span></div>
        <div class="item"><span class="label">Block:</span><span class="value">{{ $tenant->unit?->block?->name ?? 'N/A' }}</span></div>
    </div>

    @if($agreement)
        <div class="section-title">Agreement & Financial Terms</div>
        <div class="grid">
            <div class="item"><span class="label">Start Date:</span><span class="value">{{ optional($agreement->start_date)->format('d M Y') }}</span></div>
            <div class="item"><span class="label">End Date:</span><span class="value">{{ optional($agreement->end_date)->format('d M Y') }}</span></div>
            <div class="item"><span class="label">Monthly Rent:</span><span class="value">{{ number_format($agreement->monthly_rent) }} PKR</span></div>
            <div class="item"><span class="label">Security Deposit:</span><span class="value">{{ number_format($agreement->security_deposit) }} PKR</span></div>
            <div class="item"><span class="label">Maintenance Charge:</span><span class="value">{{ $agreement->maintenance_charge ? number_format($agreement->maintenance_charge) . ' PKR' : 'N/A' }}</span></div>
            <div class="item"><span class="label">Payment Due Day:</span><span class="value">{{ $agreement->payment_due_day }}th of each month</span></div>
            <div class="item"><span class="label">Grace Period:</span><span class="value">{{ $agreement->grace_period_days ?? 0 }} days</span></div>
            <div class="item"><span class="label">Fine Per Day:</span><span class="value">{{ $agreement->fine_per_day ? number_format($agreement->fine_per_day) . ' PKR' : '0 PKR' }}</span></div>
            <div class="item"><span class="label">Notice Period:</span><span class="value">{{ $agreement->notice_period_months ?? 0 }} month(s)</span></div>
        </div>

        @if($agreement->terms)
            <div class="section-title">Special Terms & Conditions</div>
            <div class="terms-box">
                {{ $agreement->terms }}
            </div>
        @endif
    @else
        <p>No active/draft agreement details exist for this tenant.</p>
    @endif

    @php
        $latestBreakerInsp = $tenant->unit ? $tenant->unit->breakerInspections()->where('breaker_status', 'on')->first() : null;
        $initialMeter = $agreement?->initial_meter_reading ?? $latestBreakerInsp?->meter_reading;
    @endphp
    @if($initialMeter || $latestBreakerInsp)
        <div class="section-title">⚡ Electricity Breaker &amp; Initial Meter Handover Verification</div>
        <div class="grid">
            <div class="item"><span class="label">Breaker Status:</span><span class="value" style="font-weight: bold; color: #16a34a;">ON (Switched ON for Move-In Handover)</span></div>
            <div class="item"><span class="label">Initial Meter Reading:</span><span class="value" style="font-weight: bold;">{{ number_format($initialMeter ?? 0, 2) }} kWh</span></div>
            @if($latestBreakerInsp)
                <div class="item"><span class="label">Inspection Officer:</span><span class="value">{{ $latestBreakerInsp->inspection_officer_name }}</span></div>
                <div class="item" style="grid-column: span 2;"><span class="label">Officer Statement:</span><span class="value">"{{ $latestBreakerInsp->officer_statement }}"</span></div>
            @endif
        </div>
        @if($latestBreakerInsp?->meter_image)
            <div style="margin-top: 10px; margin-bottom: 15px;">
                <span class="label" style="display: block; margin-bottom: 5px;">Initial Meter Photo Proof:</span>
                <img src="{{ $latestBreakerInsp->meter_image_url }}" alt="Meter Photo Proof" style="max-height: 140px; border: 1px solid #ccc; border-radius: 4px;">
            </div>
        @endif
    @endif

    <div class="signature-area">
        <div class="sig-box">
            Tenant's Signature
        </div>
        <div class="sig-box">
            Inspection Officer's Signature
        </div>
        <div class="sig-box">
            Owner / Representative
        </div>
    </div>
</body>
</html>
