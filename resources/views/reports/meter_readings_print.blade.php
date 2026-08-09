<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meter Reading Report - {{ $selectedUnit ? 'Unit ' . $selectedUnit->unit_number : 'All Units' }} — Palladium Mall</title>
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html {
            font-size: 14px;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            color: #000;
            background: #fff;
            padding: 24px 32px;
            line-height: 1.5;
            font-weight: 700;
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 3px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-text {
            font-size: 1.4rem;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
        }

        .doc-title {
            text-align: right;
        }

        .doc-title h2 {
            font-size: 1.15rem;
            font-weight: 900;
            color: #0f172a;
            text-transform: uppercase;
        }

        .doc-title p {
            font-size: 0.85rem;
            font-weight: 700;
            color: #475569;
            margin-top: 2px;
        }

        .party-info {
            margin-bottom: 16px;
            font-size: 0.95rem;
            font-weight: 800;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 10px 14px;
            background: #f8fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .party-info strong {
            color: #0f172a;
            font-weight: 900;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            font-weight: 700;
            margin-bottom: 24px;
        }

        thead tr {
            background: #2563eb;
            color: #ffffff;
        }

        thead th {
            padding: 10px;
            font-weight: 900;
            text-transform: uppercase;
            font-size: 0.8rem;
            border: 1px solid #1d4ed8;
            color: #ffffff;
        }

        tbody td {
            padding: 8px 10px;
            border: 1px solid #cbd5e1;
        }

        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        tfoot td {
            padding: 10px;
            font-weight: 900;
            border: 1px solid #cbd5e1;
            background: #f1f5f9;
        }

        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: monospace; }
        .font-black { font-weight: 900; }

        .footer-sigs {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #cbd5e1;
            font-size: 0.85rem;
            font-weight: 800;
        }

        .sig-box {
            text-align: center;
            width: 200px;
        }

        .sig-line {
            border-bottom: 1px solid #000;
            margin-bottom: 6px;
            height: 30px;
        }

        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>

<body>

    <!-- ACTION BUTTONS (NO-PRINT) -->
    <div class="no-print" style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
        <button onclick="window.close()" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #ccc; background: #fff; cursor: pointer; font-weight: bold;">Close</button>
        <button onclick="window.print()" style="padding: 8px 20px; border-radius: 8px; border: none; background: #2563eb; color: #fff; cursor: pointer; font-weight: bold;">🖨️ Print Report</button>
    </div>

    <!-- HEADER MATCHING LEDGERS PRINT UI -->
    <div class="header">
        <div class="logo-section">
            <div class="logo-text">PALLADIUM MALL</div>
        </div>
        <div class="doc-title">
            <h2>GEPCO METER READING REPORT</h2>
            <p>
                Period: {{ !empty($dateFrom) ? date('d M Y', strtotime($dateFrom)) : 'Beginning' }} —
                {{ !empty($dateTo) ? date('d M Y', strtotime($dateTo)) : 'Present' }}
            </p>
        </div>
    </div>

    <!-- UNIT / METADATA INFO BOX -->
    <div class="party-info">
        <div>
            @if($selectedUnit)
                <strong>Flat / Shop:</strong> Unit {{ $selectedUnit->unit_number }} ({{ $selectedUnit->floor?->name ?? '—' }} / {{ $selectedUnit->block?->name ?? '—' }})
                &nbsp;|&nbsp;
                <strong>Tenant:</strong> {{ $selectedUnit->tenant?->name ?? ($selectedUnit->otherTenant?->name ?? 'Vacant / Self') }}
                &nbsp;|&nbsp;
                <strong>GEPCO Ref #:</strong> {{ $selectedUnit->meters->where('type', 'electricity')->first()?->meter_ref_no ?? ($selectedUnit->meters->first()?->meter_ref_no ?? '—') }}
            @else
                <strong>Scope:</strong> All Flats / Shops
            @endif
        </div>
        <div>
            <strong>Printed On:</strong> {{ now()->format('d M Y, h:i A') }}
        </div>
    </div>

    <!-- MAIN TABLE MATCHING LEDGERS PRINT UI -->
    <table>
        <thead>
            <tr>
                <th class="text-center" style="width: 35px;">#</th>
                <th>Voucher Date</th>
                <th>Due Date</th>
                <th>Voucher #</th>
                <th>Flat / Shop</th>
                <th>Tenant Name</th>
                <th>GEPCO Ref #</th>
                <th class="text-right">Prev. kWh</th>
                <th class="text-right">Curr. kWh</th>
                <th class="text-right">Consumed</th>
                <th class="text-right">Bill Amount</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($voucherData as $index => $item)
                @php
                    $v = $item['voucher'];
                    $consumption = $item['consumption'];
                    $prevReading = $item['prev_reading'];
                @endphp
                <tr>
                    <td class="text-center font-mono">{{ $index + 1 }}</td>
                    <td class="font-mono">{{ $v->date ? $v->date->format('d M Y') : '—' }}</td>
                    <td class="font-mono">{{ $v->due_date ? $v->due_date->format('d M Y') : '—' }}</td>
                    <td class="font-mono font-black" style="color: #2563eb;">{{ $v->voucher_no }}</td>
                    <td class="font-black">{{ $v->unit ? 'Unit ' . $v->unit->unit_number : '—' }}</td>
                    <td>{{ $v->unit?->tenant?->name ?? ($v->unit?->otherTenant?->name ?? 'Vacant / Self') }}</td>
                    <td class="font-mono">{{ $v->meter_ref_no ?? '—' }}</td>
                    <td class="text-right font-mono">{{ $prevReading !== null ? number_format($prevReading, 2) : '—' }}</td>
                    <td class="text-right font-mono">{{ $v->current_reading !== null ? number_format($v->current_reading, 2) : '—' }}</td>
                    <td class="text-right font-mono font-black">{{ $consumption !== null ? number_format($consumption, 2) . ' kWh' : '—' }}</td>
                    <td class="text-right font-mono font-black">Rs. {{ number_format($v->amount, 2) }}</td>
                    <td class="text-center font-black" style="text-transform: uppercase;">{{ strtoupper($v->status) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center" style="padding: 20px;">No Meter Reading records found.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($voucherData) > 0)
            <tfoot>
                <tr>
                    <td colspan="9" class="text-right" style="text-transform: uppercase;">Total Consumed & Billed:</td>
                    <td class="text-right font-mono font-black" style="color: #b45309;">{{ number_format($totalConsumption, 2) }} kWh</td>
                    <td class="text-right font-mono font-black" style="font-size: 1rem;">Rs. {{ number_format($totalBilledAmount, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- SIGNATURES -->
    <div class="footer-sigs">
        <div class="sig-box">
            <div class="sig-line"></div>
            <div>Prepared By (Officer)</div>
        </div>
        <div class="sig-box">
            <div class="sig-line"></div>
            <div>Authorized Signature</div>
        </div>
    </div>

</body>

</html>
