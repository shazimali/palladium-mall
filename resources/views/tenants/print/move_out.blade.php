<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Move-Out Inspection Report - {{ $tenant->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 30px;
            font-size: 13px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }

        .header-info {
            flex-grow: 1;
            text-align: left;
        }

        .header-info.centered {
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            color: #666;
        }

        .tenant-photo {
            width: 90px;
            height: 90px;
            border-radius: 4px;
            object-fit: cover;
            border: 1px solid #ccc;
            margin-left: 20px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            text-transform: uppercase;
        }

        .grid {
            display: grid;
            grid-template-cols: 1fr 1fr;
            gap: 12px;
            margin-bottom: 15px;
        }

        .item {
            display: flex;
            border-bottom: 1px dashed #eee;
            padding-bottom: 4px;
        }

        .label {
            font-weight: bold;
            width: 180px;
            color: #555;
        }

        .value {
            flex-grow: 1;
        }

        .checklist-grid {
            display: grid;
            grid-template-cols: 1fr 1fr;
            gap: 8px;
            margin-bottom: 15px;
        }

        .check-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
        }

        .check-box {
            font-family: monospace;
            font-size: 13px;
            font-weight: bold;
        }

        .check-yes {
            color: #16a34a;
        }

        .check-no {
            color: #999;
        }

        .notes-box {
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #f9f9f9;
            min-height: 40px;
            white-space: pre-line;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .signature-area {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }

        .sig-box {
            border-top: 1px solid #333;
            width: 220px;
            text-align: center;
            padding-top: 5px;
        }

        @media print {
            body {
                margin: 20px;
            }

            .no-print {
                display: none;
            }
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
        <div class="item"><span class="label">Assigned Unit:</span><span
                class="value"><strong>{{ $tenant->unit ? $tenant->unit->unit_number : ($agreement?->unit?->unit_number ?? 'N/A') }}</strong></span>
        </div>
        @php
            $u = $tenant->unit ?? $agreement?->unit;
        @endphp
        @if($u)
            <div class="item"><span class="label">Unit Type:</span><span class="value">{{ ucfirst($u->type) }}</span></div>
            <div class="item"><span class="label">Floor:</span><span class="value">{{ $u->floor?->name ?? 'N/A' }}</span>
            </div>
            <div class="item"><span class="label">Block:</span><span class="value">{{ $u->block?->name ?? 'N/A' }}</span>
            </div>
            @if($u->area)
                <div class="item"><span class="label">Area / Zone:</span><span class="value">{{ $u->area->name }}</span></div>
            @endif
            @if($u->area_sqft)
                <div class="item"><span class="label">Size (sqft):</span><span
                        class="value">{{ number_format($u->area_sqft, 2) }}</span></div>
            @endif
        @endif
        <div class="item"><span class="label">Inspection Date:</span><span
                class="value">{{ optional($moveOut->checklist_date)->format('d M Y') ?? 'N/A' }}</span></div>
        <div class="item"><span class="label">Inspection Member:</span><span
                class="value">{{ $moveOut->inspection_member }}</span></div>
        <div class="item" style="grid-column: span 2;"><span class="label">Agreement Period:</span><span
                class="value">{{ optional($agreement?->start_date)->format('d M Y') }} →
                {{ optional($agreement?->end_date)->format('d M Y') }}</span></div>
    </div>

    @if($inspectionHeads->isEmpty())
        <div class="section-title">🏠 Flat Move-Out Inspection Checklist</div>
        <p style="color:#b45309; font-size:12px;">⚠️ No inspection heads configured. Please add Flat Inspection heads via
            the admin panel.</p>
    @else
        <div class="section-title">🏠 Flat Move-Out Inspection Checklist</div>
        <table style="width:100%; border-collapse: collapse; font-size: 12px; margin-bottom: 15px;">
            <thead>
                <tr
                    style="background:#f3f4f6; border-bottom: 2px solid #d1d5db; font-weight: bold; text-transform: uppercase; font-size: 10px;">
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
                        $item = $flatInspectionReport?->items->firstWhere('inspection_head_id', $head->id);
                        $status = $item?->status;
                        $rowBg = $status === true ? '#f0fdf4' : ($status === false ? '#fff1f2' : '#ffffff');
                    @endphp
                    <tr style="background: {{ $rowBg }}; border-bottom: 1px solid #e5e7eb;">
                        <td style="padding: 5px 8px; border: 1px solid #e5e7eb; color: #9ca3af; font-size: 10px;">
                            {{ $loop->iteration }}</td>
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
                        <td style="padding: 5px 8px; border: 1px solid #e5e7eb; color: #4b5563; font-size: 11px;">
                            {{ $item?->remarks ?? '' }}</td>
                        <td style="padding: 5px 8px; border: 1px solid #e5e7eb; text-align: center;">
                            @if($item?->image_path)
                                <img src="{{ Storage::url($item->image_path) }}" alt="Photo"
                                    style="height: 40px; width: 40px; object-fit: cover; border-radius: 4px; border: 1px solid #d1d5db;">
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            @if($flatInspectionReport)
                <tfoot>
                    <tr style="background: #f9fafb; font-weight: bold; font-size: 10px; border-top: 2px solid #d1d5db;">
                        <td colspan="2"
                            style="padding: 5px 8px; border: 1px solid #e5e7eb; text-align: right; text-transform: uppercase;">
                            Summary:</td>
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
        <div class="item"><span class="label">Property Handover Condition:</span><span class="value"
                style="font-weight: bold; color: {{ $moveOut->flat_condition === 'good' ? '#16a34a' : '#ea580c' }}">{{ ucfirst($moveOut->flat_condition ?? 'N/A') }}</span>
        </div>
        <div class="item"><span class="label">Initial Security Deposit:</span><span
                class="value">{{ number_format($agreement?->security_deposit ?? 0) }} PKR</span></div>

        @php
            $payments = $agreement ? $agreement->payments()->orderBy('month')->get() : collect();
            $totalBilled = $payments->sum('amount');
            $totalPaid = $payments->sum('amount_paid');
            $outstanding = max(0, $totalBilled - $totalPaid);
            $damageDeduction = $moveOut->deposit_deduction ?? 0;
            $netRefund = max(0, ($agreement?->security_deposit ?? 0) - $outstanding - $damageDeduction);
        @endphp

        <div class="item"><span class="label">Outstanding Dues Deducted:</span><span class="value"
                style="color: #dc2626;">- {{ number_format($outstanding) }} PKR</span></div>
        <div class="item"><span class="label">Damage / Repair Deductions:</span><span class="value"
                style="color: #dc2626;">- {{ number_format($damageDeduction) }} PKR</span></div>
        <div class="item"
            style="grid-column: span 2; font-size: 14px; border-bottom: 2px double #333; padding-bottom: 5px;"><span
                class="label">Estimated Net Refund:</span><span class="value"
                style="font-weight: bold; color: #16a34a;">{{ number_format($netRefund) }} PKR</span></div>
        @if($moveOut->final_remarks)
            <div class="item" style="grid-column: span 2;"><span class="label">Final Remarks:</span><span
                    class="value">{{ $moveOut->final_remarks }}</span></div>
        @endif
    </div>

    @php
        $latestBreakerInsp = $tenant->unit ? $tenant->unit->breakerInspections()->where('breaker_status', 'off')->first() : null;
    @endphp
    @if($latestBreakerInsp)
        <div class="section-title">⚡ Electricity Breaker &amp; Meter Off Verification</div>
        <div class="grid">
            <div class="item"><span class="label">Breaker Status:</span><span class="value"
                    style="font-weight: bold; color: #dc2626;">OFF (Safely Switched Off)</span></div>
            <div class="item"><span class="label">Final Meter Reading:</span><span class="value"
                    style="font-weight: bold;">{{ number_format($latestBreakerInsp->meter_reading, 2) }} kWh</span></div>
            <div class="item"><span class="label">Inspection Officer:</span><span
                    class="value">{{ $latestBreakerInsp->inspection_officer_name }}</span></div>
            <div class="item" style="grid-column: span 2;"><span class="label">Officer Statement:</span><span
                    class="value">"{{ $latestBreakerInsp->officer_statement }}"</span></div>
        </div>
        @if($latestBreakerInsp->meter_image)
            <div style="margin-top: 10px; margin-bottom: 15px;">
                <span class="label" style="display: block; margin-bottom: 5px;">Meter Reading Photo Proof:</span>
                <img src="{{ $latestBreakerInsp->meter_image_url }}" alt="Meter Photo Proof"
                    style="max-height: 140px; border: 1px solid #ccc; border-radius: 4px;">
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
        <div class="sig-box">
            Authorized Mall Manager
        </div>
    </div>
</body>

</html>