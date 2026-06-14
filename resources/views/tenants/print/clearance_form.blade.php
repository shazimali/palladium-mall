<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tenant Clearance Form - {{ $tenant->name }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #111;
            margin: 40px;
            font-size: 14px;
            line-height: 1.5;
            background: #fff;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #111;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h1 {
            margin: 0;
            font-size: 26px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header p {
            margin: 5px 0 0;
            color: #555;
            font-size: 14px;
            font-weight: 500;
        }
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .meta-info span {
            font-weight: bold;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
            background: #f3f4f6;
            padding: 6px 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-left: 4px solid #111;
        }
        .grid {
            display: grid;
            grid-template-cols: 1fr 1fr;
            gap: 12px 25px;
            margin-bottom: 20px;
        }
        .item {
            display: flex;
            border-bottom: 1px dashed #e5e7eb;
            padding-bottom: 4px;
        }
        .label {
            font-weight: 600;
            width: 160px;
            color: #4b5563;
        }
        .value {
            flex-grow: 1;
            color: #111;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .table th, .table td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
        }
        .table th {
            background-color: #f9fafb;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            font-size: 11px;
        }
        .table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: 600;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-paid {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        .badge-pending {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }
        .badge-partial {
            background-color: #fef3c7;
            color: #92400e;
            border: 1px solid #fde68a;
        }
        .summary-box {
            border: 1px solid #111;
            background: #f9fafb;
            padding: 15px;
            margin-top: 20px;
            margin-bottom: 25px;
        }
        .summary-grid {
            display: grid;
            grid-template-cols: repeat(3, 1fr);
            gap: 15px;
            text-align: center;
        }
        .summary-item h3 {
            margin: 0;
            font-size: 12px;
            color: #4b5563;
            text-transform: uppercase;
        }
        .summary-item p {
            margin: 5px 0 0;
            font-size: 18px;
            font-weight: bold;
            color: #111;
        }
        .declaration {
            font-size: 12px;
            color: #374151;
            margin-top: 25px;
            margin-bottom: 40px;
            text-align: justify;
            border-top: 1px solid #e5e7eb;
            padding-top: 15px;
        }
        .signature-area {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
            gap: 40px;
        }
        .sig-box {
            border-top: 1px solid #111;
            width: 200px;
            text-align: center;
            padding-top: 6px;
            font-size: 12px;
            font-weight: 600;
        }
        .no-print-btn {
            background-color: #111827;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
            transition: background-color 0.2s;
        }
        .no-print-btn:hover {
            background-color: #374151;
        }
        @media print {
            body {
                margin: 20px;
                color: #000;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid #e5e7eb; padding-bottom: 15px;">
        <a href="{{ route('tenants.show', $tenant) }}" class="no-print-btn" style="background-color: #6b7280;">&larr; Back to Profile</a>
        <button onclick="window.print()" class="no-print-btn">Print Clearance Form</button>
    </div>

    <div class="header">
        <h1>Tenant Clearance Form</h1>
        <p>Palladium Mall Tenant Management System</p>
    </div>

    <div class="meta-info">
        <div>Form Gen Date: <span>{{ now()->format('d M Y') }}</span></div>
        <div>Tenant Code: <span>PM-T-{{ str_pad($tenant->id, 4, '0', STR_PAD_LEFT) }}</span></div>
    </div>

    <div class="section-title">Tenant & Unit Information</div>
    <div class="grid">
        <div class="item"><span class="label">Tenant Name:</span><span class="value">{{ $tenant->name }}</span></div>
        <div class="item"><span class="label">Father's Name:</span><span class="value">{{ $tenant->father_name ?? 'N/A' }}</span></div>
        <div class="item"><span class="label">CNIC Number:</span><span class="value">{{ $tenant->cnic }}</span></div>
        <div class="item"><span class="label">Phone / WhatsApp:</span><span class="value">{{ $tenant->phone }} {{ $tenant->whatsapp_number ? '/ ' . $tenant->whatsapp_number : '' }}</span></div>
        <div class="item"><span class="label">Unit Assigned:</span><span class="value">{{ $tenant->unit ? $tenant->unit->unit_number . ($tenant->unit->floor ? ' (' . $tenant->unit->floor->name . ')' : '') . ($tenant->unit->block ? ' - ' . $tenant->unit->block->name : '') : 'N/A' }}</span></div>
        <div class="item"><span class="label">Unit Status:</span><span class="value">{{ $tenant->unit ? ucfirst($tenant->unit->status) : 'N/A' }}</span></div>
    </div>

    @if($tenant->partners->isNotEmpty())
    <div class="section-title">Partners / Co-Tenants</div>
    <div class="grid">
        @foreach($tenant->partners as $index => $partner)
            <div class="item" style="grid-column: span 2; display: flex; border-bottom: 1px dashed #e5e7eb; padding-bottom: 4px;">
                <span class="label" style="width: 120px;">Partner #{{ $index + 1 }}:</span>
                <span class="value">{{ $partner->name }} &nbsp;&nbsp;|&nbsp;&nbsp; CNIC: {{ $partner->cnic }} &nbsp;&nbsp;|&nbsp;&nbsp; Phone: {{ $partner->phone }}</span>
            </div>
        @endforeach
    </div>
    @endif

    @if($agreement)
    <div class="section-title">Agreement & Lease Terms</div>
    <div class="grid">
        <div class="item"><span class="label">Agreement Period:</span><span class="value">{{ \Carbon\Carbon::parse($agreement->start_date)->format('d M Y') }} to {{ \Carbon\Carbon::parse($agreement->end_date)->format('d M Y') }}</span></div>
        <div class="item"><span class="label">Monthly Rent:</span><span class="value">{{ number_format($agreement->monthly_rent) }} PKR</span></div>
        <div class="item"><span class="label">Security Deposit:</span><span class="value">{{ number_format($agreement->security_deposit) }} PKR</span></div>
        <div class="item"><span class="label">Maintenance Charge:</span><span class="value">{{ number_format($agreement->maintenance_charge ?? 0) }} PKR</span></div>
        <div class="item"><span class="label">Fine Per Day (Late):</span><span class="value">{{ number_format($agreement->fine_per_day) }} PKR</span></div>
        <div class="item"><span class="label">Agreement Status:</span><span class="value" style="font-weight: 600; color: #d97706;">{{ ucfirst($agreement->status) }}</span></div>
    </div>
    @endif

    <div class="section-title">Settlement & Payment Details</div>
    <table class="table">
        <thead>
            <tr>
                <th>Billing Period (Month)</th>
                <th>Payment Type</th>
                <th>Due Date</th>
                <th class="text-right">Billed Amount</th>
                <th class="text-right">Paid Amount</th>
                <th class="text-right">Outstanding</th>
                <th>Payment Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $payment)
            <tr>
                <td>{{ \Carbon\Carbon::parse($payment->month . '-01')->format('M Y') }}</td>
                <td>
                    {{ ucfirst(str_replace('_', ' ', $payment->type)) }}
                    @if($payment->type === 'meter' && $payment->meter)
                        ({{ ucfirst($payment->meter->type) }} - {{ $payment->units_consumed ?? 0 }} units)
                    @endif
                </td>
                <td>{{ $payment->due_date ? \Carbon\Carbon::parse($payment->due_date)->format('d M Y') : 'N/A' }}</td>
                <td class="text-right">{{ number_format($payment->amount) }} PKR</td>
                <td class="text-right">{{ number_format($payment->amount_paid) }} PKR</td>
                <td class="text-right font-bold">{{ number_format(max(0, $payment->amount - $payment->amount_paid)) }} PKR</td>
                <td>{{ $payment->payment_date ? \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') : '-' }}</td>
                <td>
                    @if($payment->status === 'paid')
                        <span class="badge badge-paid">Paid</span>
                    @elseif($payment->status === 'partial')
                        <span class="badge badge-partial">Partial</span>
                    @else
                        <span class="badge badge-pending">Unpaid</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; color: #666;">No payment history found for this agreement.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-grid">
            <div class="summary-item">
                <h3>Total Billed</h3>
                <p>{{ number_format($totalBilled) }} PKR</p>
            </div>
            <div class="summary-item">
                <h3>Total Settled</h3>
                <p style="color: #059669;">{{ number_format($totalPaid) }} PKR</p>
            </div>
            <div class="summary-item">
                <h3>Net Outstanding Dues</h3>
                <p style="color: {{ $outstanding > 0 ? '#dc2626' : '#111' }};">{{ number_format($outstanding) }} PKR</p>
            </div>
        </div>
    </div>

    @if($agreement && $agreement->security_deposit)
    <div class="section-title">Security Deposit Refund Estimate</div>
    <div class="grid" style="margin-bottom: 30px;">
        <div class="item"><span class="label">Initial Security Deposit:</span><span class="value">{{ number_format($agreement->security_deposit) }} PKR</span></div>
        <div class="item"><span class="label">Outstanding Dues Deducted:</span><span class="value" style="color: #dc2626;">- {{ number_format($outstanding) }} PKR</span></div>
        <div class="item" style="grid-column: span 2; font-size: 15px; border-bottom: 2px solid #111; padding-bottom: 8px;"><span class="label">Estimated Net Refundable:</span><span class="value font-bold" style="color: #059669;">{{ number_format(max(0, $agreement->security_deposit - $outstanding)) }} PKR</span></div>
    </div>
    @endif

    <div class="declaration">
        <strong>Declaration:</strong> I, the undersigned tenant, hereby declare that I am vacating Unit {{ $tenant->unit ? $tenant->unit->unit_number : '' }} at Palladium Mall. I confirm that I have cleared all utilities, gas, water, maintenance, and rent dues as listed above, and that the unit's inventory has been handed back to the management. The security deposit refund calculations are agreed upon, and no further claims remain pending against either party.
    </div>

    <div class="signature-area">
        <div class="sig-box">
            Tenant Signature & Date
        </div>
        <div class="sig-box">
            Inspector Signature & Date
        </div>
        <div class="sig-box">
            Finance Manager Signature
        </div>
    </div>

</body>
</html>
